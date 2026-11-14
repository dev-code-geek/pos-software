@extends('layouts.admin')

@section('title', 'Maintenance Report')
@section('page-title', 'Maintenance Report')

@section('admin-content')
<style>
.filter-section {
    background: #f8fafc;
    border-radius: 12px;
    padding: 15px 20px;
    margin-bottom: 20px;
}
.stats-summary-card {
    border-radius: 12px;
    padding: 15px 20px;
    background: #fff;
    border: 1px solid #e2e8f0;
    transition: all 0.2s ease;
}
.stats-summary-card:hover {
    border-color: #4f46e5;
    box-shadow: 0 4px 12px rgba(79,70,229,0.08);
}
.stats-summary-card .number {
    font-size: 1.8rem;
    font-weight: 700;
}
.stats-summary-card .label {
    font-size: 0.8rem;
    color: #64748b;
}
</style>

<!-- ============================================ -->
<!-- FILTER SECTION -->
<!-- ============================================ -->
<div class="filter-section">
    <div class="row g-3 align-items-end">
        <div class="col-md-2">
            <label class="form-label fw-bold small">Date From</label>
            <input type="date" id="dateFrom" class="form-control form-control-sm" value="{{ now()->startOfMonth()->format('Y-m-d') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label fw-bold small">Date To</label>
            <input type="date" id="dateTo" class="form-control form-control-sm" value="{{ now()->format('Y-m-d') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label fw-bold small">Status</label>
            <select id="statusFilter" class="form-select form-select-sm">
                <option value="">All Status</option>
                <option value="reported">Reported</option>
                <option value="in_progress">In Progress</option>
                <option value="fixed">Fixed</option>
                <option value="unrepairable">Unrepairable</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label fw-bold small">Priority</label>
            <select id="priorityFilter" class="form-select form-select-sm">
                <option value="">All Priorities</option>
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
                <option value="critical">Critical</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label fw-bold small">Product</label>
            <select id="productFilter" class="form-select form-select-sm">
                <option value="">All Products</option>
                @foreach($products ?? [] as $product)
                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary btn-sm w-100" id="applyFilters">
                <i class="fas fa-filter me-1"></i> Apply Filters
            </button>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- STATS SUMMARY -->
<!-- ============================================ -->
<div class="row g-3 mb-4" id="statsSummary">
    <div class="col-md-3">
        <div class="stats-summary-card text-center">
            <div class="number text-primary" id="totalRecords">0</div>
            <div class="label">Total Issues</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-summary-card text-center">
            <div class="number text-warning" id="pendingRecords">0</div>
            <div class="label">Pending</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-summary-card text-center">
            <div class="number text-success" id="fixedRecords">0</div>
            <div class="label">Fixed</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-summary-card text-center">
            <div class="number text-danger" id="totalCost">$0</div>
            <div class="label">Total Cost</div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- MAIN CARD -->
<!-- ============================================ -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fas fa-tools text-primary me-2"></i>Maintenance Report</h6>
        <div class="d-flex gap-2">
            <button class="btn btn-success btn-sm" id="exportExcel">
                <i class="fas fa-file-excel me-1"></i> Export Excel
            </button>
            <button class="btn btn-secondary btn-sm" id="exportCSV">
                <i class="fas fa-file-csv me-1"></i> Export CSV
            </button>
            <button class="btn btn-info btn-sm" id="printReport">
                <i class="fas fa-print me-1"></i> Print
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0" id="reportTable">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Issue</th>
                        <th>Reported By</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Cost</th>
                        <th>Repaired By</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <i class="fas fa-spinner fa-spin fa-2x text-primary mb-3 d-block"></i>
                            <p class="text-muted">Loading data...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- TOAST CONTAINER -->
<!-- ============================================ -->
<div id="toastContainer" class="position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script>
$(document).ready(function() {
    let currentData = [];

    function loadReportData() {
        const dateFrom = $('#dateFrom').val();
        const dateTo = $('#dateTo').val();
        const status = $('#statusFilter').val();
        const priority = $('#priorityFilter').val();
        const product = $('#productFilter').val();

        $('#tableBody').html(`
            <tr>
                <td colspan="9" class="text-center py-5">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary mb-3 d-block"></i>
                    <p class="text-muted">Loading data...</p>
                </td>
            </tr>
        `);

        const params = new URLSearchParams({
            date_from: dateFrom,
            date_to: dateTo,
            status: status,
            priority: priority,
            product_id: product
        });

        $.ajax({
            url: '{{ route("admin.reports.data.maintenance") }}?' + params.toString(),
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response && response.data) {
                    currentData = response.data;
                    renderTable(response);
                    updateStats(response);
                } else {
                    showToast('error', 'No data found for the selected filters.');
                    $('#tableBody').html(`
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                <p class="text-muted">No maintenance data found.</p>
                            </td>
                        </tr>
                    `);
                }
            },
            error: function() {
                showToast('error', 'Failed to load report data.');
                $('#tableBody').html(`
                    <tr>
                        <td colspan="9" class="text-center py-5 text-danger">
                            <i class="fas fa-exclamation-circle fa-3x mb-3 d-block"></i>
                            <p>Failed to load data. Please try again.</p>
                        </td>
                    </tr>
                `);
            }
        });
    }

    function renderTable(response) {
        const data = response.data || [];
        const body = $('#tableBody');

        if (data.length === 0) {
            body.html(`
                <tr>
                    <td colspan="9" class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                        <p class="text-muted">No maintenance data found.</p>
                    </td>
                </tr>
            `);
            return;
        }

        let html = '';
        data.forEach(function(item, index) {
            const statusColors = {
                'reported': 'warning text-dark',
                'in_progress': 'info',
                'fixed': 'success',
                'unrepairable': 'danger'
            };
            const badgeClass = statusColors[item.status] || 'secondary';

            const priorityColors = {
                'low': 'secondary',
                'medium': 'info',
                'high': 'warning text-dark',
                'critical': 'danger'
            };
            const priorityClass = priorityColors[item.priority] || 'secondary';

            html += `
                <tr>
                    <td>${index + 1}</td>
                    <td><strong>${item.product}</strong><br><small class="text-muted">${item.sku}</small></td>
                    <td>${item.issue}</td>
                    <td>${item.user}</td>
                    <td><span class="badge bg-${priorityClass}">${item.priority}</span></td>
                    <td><span class="badge bg-${badgeClass}">${item.status.replace('_', ' ')}</span></td>
                    <td>$${parseFloat(item.cost).toFixed(2)}</td>
                    <td>${item.repaired_by}</td>
                    <td>${item.created_at}</td>
                </tr>
            `;
        });

        body.html(html);
    }

    function updateStats(response) {
        const stats = response.stats || {};
        $('#totalRecords').text(stats.total || 0);
        $('#pendingRecords').text((stats.reported || 0) + (stats.in_progress || 0));
        $('#fixedRecords').text(stats.fixed || 0);
        $('#totalCost').text('$' + (stats.total_cost || 0).toFixed(2));
    }

    function exportToExcel() {
        if (currentData.length === 0) {
            showToast('warning', 'No data to export.');
            return;
        }

        try {
            const excelData = currentData.map(function(item, index) {
                return {
                    '#': index + 1,
                    'Product': item.product,
                    'SKU': item.sku,
                    'Issue': item.issue,
                    'Reported By': item.user,
                    'Priority': item.priority,
                    'Status': item.status.replace('_', ' '),
                    'Cost': parseFloat(item.cost).toFixed(2),
                    'Repaired By': item.repaired_by,
                    'Date': item.created_at
                };
            });

            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.json_to_sheet(excelData);
            ws['!cols'] = [
                { wch: 5 }, { wch: 25 }, { wch: 15 }, { wch: 30 },
                { wch: 20 }, { wch: 12 }, { wch: 15 }, { wch: 12 },
                { wch: 20 }, { wch: 15 }
            ];

            XLSX.utils.book_append_sheet(wb, ws, 'Maintenance Report');
            const fileName = `maintenance_report_${new Date().toISOString().slice(0,10)}.xlsx`;
            XLSX.writeFile(wb, fileName);
            showToast('success', `Report exported as ${fileName}`);
        } catch (error) {
            showToast('error', 'Failed to export Excel file.');
        }
    }

    function exportToCSV() {
        if (currentData.length === 0) {
            showToast('warning', 'No data to export.');
            return;
        }

        try {
            const headers = ['#', 'Product', 'SKU', 'Issue', 'Reported By', 'Priority', 'Status', 'Cost', 'Repaired By', 'Date'];
            const rows = currentData.map(function(item, index) {
                return [
                    index + 1,
                    item.product,
                    item.sku,
                    item.issue,
                    item.user,
                    item.priority,
                    item.status.replace('_', ' '),
                    parseFloat(item.cost).toFixed(2),
                    item.repaired_by,
                    item.created_at
                ];
            });

            let csv = headers.join(',') + '\n';
            rows.forEach(function(row) {
                csv += row.map(val => `"${val}"`).join(',') + '\n';
            });

            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.setAttribute('href', URL.createObjectURL(blob));
            link.setAttribute('download', `maintenance_report_${new Date().toISOString().slice(0,10)}.csv`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            showToast('success', 'CSV file exported successfully!');
        } catch (error) {
            showToast('error', 'Failed to export CSV file.');
        }
    }

    function printReport() {
        const printContents = document.querySelector('.card:last-child').innerHTML;
        const originalContents = document.body.innerHTML;

        document.body.innerHTML = `
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 11px; }
                th, td { padding: 6px 8px; border: 1px solid #ddd; text-align: left; }
                th { background: #f5f5f5; font-weight: bold; }
                .badge { padding: 2px 6px; border-radius: 4px; color: #fff; font-size: 10px; }
                .bg-success { background: #22c55e; }
                .bg-warning { background: #f59e0b; color: #333; }
                .bg-danger { background: #ef4444; }
                .bg-primary { background: #4f46e5; }
                .bg-info { background: #3b82f6; }
                .bg-secondary { background: #6b7280; }
                .header { text-align: center; margin-bottom: 20px; }
                .header h2 { margin: 0; color: #1e293b; }
                .header p { color: #64748b; margin: 5px 0; }
                .date-range { text-align: center; color: #64748b; margin-bottom: 20px; }
                .stats-summary { display: flex; justify-content: space-around; margin-bottom: 20px; flex-wrap: wrap; }
                .stat-item { text-align: center; margin: 5px 15px; }
                .stat-item .number { font-size: 1.3rem; font-weight: 700; }
                .stat-item .label { color: #64748b; font-size: 0.8rem; }
            </style>
            <div class="header">
                <h2>Maintenance Report</h2>
                <p>Generated on: ${new Date().toLocaleString()}</p>
            </div>
            <div class="date-range">
                Period: ${$('#dateFrom').val()} to ${$('#dateTo').val()}
            </div>
            <div class="stats-summary">
                <div class="stat-item"><div class="number text-primary">${$('#totalRecords').text()}</div><div class="label">Total</div></div>
                <div class="stat-item"><div class="number text-warning">${$('#pendingRecords').text()}</div><div class="label">Pending</div></div>
                <div class="stat-item"><div class="number text-success">${$('#fixedRecords').text()}</div><div class="label">Fixed</div></div>
                <div class="stat-item"><div class="number text-danger">${$('#totalCost').text()}</div><div class="label">Total Cost</div></div>
            </div>
            ${printContents}
        `;

        window.print();
        document.body.innerHTML = originalContents;
        location.reload();
    }

    function showToast(type, message) {
        const icons = { success: 'check-circle', error: 'exclamation-circle', warning: 'exclamation-triangle', info: 'info-circle' };
        const colors = { success: 'success', error: 'danger', warning: 'warning', info: 'info' };
        
        const toast = $(`
            <div class="toast show" role="alert" style="min-width:300px;">
                <div class="toast-header bg-${colors[type]} text-white">
                    <strong class="me-auto"><i class="fas fa-${icons[type]} me-2"></i> ${type.charAt(0).toUpperCase() + type.slice(1)}</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">${message}</div>
            </div>
        `);
        $('#toastContainer').append(toast);
        setTimeout(function() { toast.remove(); }, 5000);
    }

    $('#applyFilters').on('click', loadReportData);
    $('#dateFrom, #dateTo').on('keypress', function(e) {
        if (e.which === 13) loadReportData();
    });
    $('#exportExcel').on('click', exportToExcel);
    $('#exportCSV').on('click', exportToCSV);
    $('#printReport').on('click', printReport);

    loadReportData();
});
</script>
@endpush
@endsection