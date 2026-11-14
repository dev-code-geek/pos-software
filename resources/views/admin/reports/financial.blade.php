@extends('layouts.admin')

@section('title', 'Financial Report')
@section('page-title', 'Financial Report')

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
        <div class="col-md-3">
            <label class="form-label fw-bold small">Date From</label>
            <input type="date" id="dateFrom" class="form-control form-control-sm" value="{{ now()->startOfYear()->format('Y-m-d') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-bold small">Date To</label>
            <input type="date" id="dateTo" class="form-control form-control-sm" value="{{ now()->format('Y-m-d') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label fw-bold small">Report Type</label>
            <select id="reportType" class="form-select form-select-sm">
                <option value="all">All Financial</option>
                <option value="purchase_orders">Purchase Orders</option>
                <option value="procurements">Procurements</option>
                <option value="maintenance">Maintenance</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label fw-bold small">Status</label>
            <select id="statusFilter" class="form-select form-select-sm">
                <option value="">All Status</option>
                <option value="paid">Paid</option>
                <option value="pending">Pending</option>
                <option value="received">Received</option>
                <option value="cancelled">Cancelled</option>
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
            <div class="label">Total Records</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-summary-card text-center">
            <div class="number text-success" id="totalAmount">$0</div>
            <div class="label">Total Amount</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-summary-card text-center">
            <div class="number text-info" id="averageAmount">$0</div>
            <div class="label">Average Amount</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-summary-card text-center">
            <div class="number text-warning" id="totalCount">0</div>
            <div class="label">Total Transactions</div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- MAIN CARD -->
<!-- ============================================ -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fas fa-coins text-primary me-2"></i>Financial Report Data</h6>
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
                        <th>Reference</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Supplier/User</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="fas fa-spinner fa-spin fa-2x text-primary mb-3 d-block"></i>
                            <p class="text-muted">Loading data...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="toastContainer" class="position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script>
$(document).ready(function() {
    let currentData = [];

    function loadReportData() {
        const dateFrom = $('#dateFrom').val();
        const dateTo = $('#dateTo').val();
        const type = $('#reportType').val();
        const status = $('#statusFilter').val();

        $('#tableBody').html(`
            <tr>
                <td colspan="8" class="text-center py-5">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary mb-3 d-block"></i>
                    <p class="text-muted">Loading data...</p>
                </td>
            </tr>
        `);

        const params = new URLSearchParams({
            date_from: dateFrom,
            date_to: dateTo,
            type: type,
            status: status
        });

        $.ajax({
            url: '{{ route("admin.reports.financial") }}?' + params.toString(),
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response && response.data) {
                    currentData = response.data;
                    renderTable(response);
                    updateStats(response);
                } else {
                    showToast('error', 'No data found for the selected filters.');
                }
            },
            error: function() {
                showToast('error', 'Failed to load report data.');
                $('#tableBody').html(`
                    <tr>
                        <td colspan="8" class="text-center py-5 text-danger">
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
                    <td colspan="8" class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                        <p class="text-muted">No financial data found.</p>
                    </td>
                </tr>
            `);
            return;
        }

        let html = '';
        data.forEach(function(item, index) {
            const statusColors = {
                'paid': 'success',
                'pending': 'warning text-dark',
                'received': 'info',
                'cancelled': 'danger'
            };
            const badgeClass = statusColors[item.status] || 'secondary';

            html += `
                <tr>
                    <td>${index + 1}</td>
                    <td><strong>${item.reference}</strong></td>
                    <td><span class="badge bg-${item.type == 'purchase_order' ? 'primary' : (item.type == 'procurement' ? 'info' : 'warning')}">${item.type}</span></td>
                    <td>${item.description}</td>
                    <td>${item.supplier || item.user || 'N/A'}</td>
                    <td class="fw-bold">$${parseFloat(item.amount).toFixed(2)}</td>
                    <td><span class="badge bg-${badgeClass}">${item.status}</span></td>
                    <td>${item.date}</td>
                </tr>
            `;
        });

        body.html(html);
    }

    function updateStats(response) {
        const stats = response.stats || {};
        $('#totalRecords').text(stats.total || 0);
        $('#totalAmount').text('$' + (stats.total_amount || 0).toFixed(2));
        $('#averageAmount').text('$' + (stats.average_amount || 0).toFixed(2));
        $('#totalCount').text(stats.transactions || 0);
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
                    'Reference': item.reference,
                    'Type': item.type,
                    'Description': item.description,
                    'Supplier/User': item.supplier || item.user || 'N/A',
                    'Amount': parseFloat(item.amount).toFixed(2),
                    'Status': item.status,
                    'Date': item.date
                };
            });

            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.json_to_sheet(excelData);
            ws['!cols'] = [
                { wch: 5 }, { wch: 20 }, { wch: 15 },
                { wch: 30 }, { wch: 20 }, { wch: 15 },
                { wch: 15 }, { wch: 15 }
            ];

            XLSX.utils.book_append_sheet(wb, ws, 'Financial Report');
            const fileName = `financial_report_${new Date().toISOString().slice(0,10)}.xlsx`;
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
            const headers = ['#', 'Reference', 'Type', 'Description', 'Supplier/User', 'Amount', 'Status', 'Date'];
            const rows = currentData.map(function(item, index) {
                return [
                    index + 1,
                    item.reference,
                    item.type,
                    item.description,
                    item.supplier || item.user || 'N/A',
                    parseFloat(item.amount).toFixed(2),
                    item.status,
                    item.date
                ];
            });

            let csv = headers.join(',') + '\n';
            rows.forEach(function(row) {
                csv += row.map(val => `"${val}"`).join(',') + '\n';
            });

            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.setAttribute('href', URL.createObjectURL(blob));
            link.setAttribute('download', `financial_report_${new Date().toISOString().slice(0,10)}.csv`);
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
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { padding: 8px; border: 1px solid #ddd; text-align: left; font-size: 12px; }
                th { background: #f5f5f5; font-weight: bold; }
                .badge { padding: 2px 6px; border-radius: 4px; color: #fff; font-size: 11px; }
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
                .stats-summary { display: flex; justify-content: space-around; margin-bottom: 20px; }
                .stat-item { text-align: center; }
                .stat-item .number { font-size: 1.5rem; font-weight: 700; }
                .stat-item .label { color: #64748b; }
            </style>
            <div class="header">
                <h2>Financial Report</h2>
                <p>Generated on: ${new Date().toLocaleString()}</p>
            </div>
            <div class="date-range">
                Period: ${$('#dateFrom').val()} to ${$('#dateTo').val()}
            </div>
            <div class="stats-summary">
                <div class="stat-item"><div class="number">${$('#totalRecords').text()}</div><div class="label">Total Records</div></div>
                <div class="stat-item"><div class="number">${$('#totalAmount').text()}</div><div class="label">Total Amount</div></div>
                <div class="stat-item"><div class="number">${$('#averageAmount').text()}</div><div class="label">Average Amount</div></div>
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