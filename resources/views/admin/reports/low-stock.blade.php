@extends('layouts.admin')

@section('title', 'Low Stock Report')
@section('page-title', 'Low Stock Report')

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
.low-stock-critical {
    background: #fee2e2;
    color: #991b1b;
    padding: 2px 8px;
    border-radius: 4px;
    font-weight: 600;
}
.low-stock-warning {
    background: #fef3c7;
    color: #92400e;
    padding: 2px 8px;
    border-radius: 4px;
    font-weight: 600;
}
</style>

<!-- ============================================ -->
<!-- FILTER SECTION -->
<!-- ============================================ -->
<div class="filter-section">
    <div class="row g-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label fw-bold small">Date From</label>
            <input type="date" id="dateFrom" class="form-control form-control-sm" value="{{ now()->startOfMonth()->format('Y-m-d') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-bold small">Date To</label>
            <input type="date" id="dateTo" class="form-control form-control-sm" value="{{ now()->format('Y-m-d') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label fw-bold small">Category</label>
            <select id="categoryFilter" class="form-select form-select-sm">
                <option value="">All Categories</option>
                @foreach($categories ?? [] as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label fw-bold small">Type</label>
            <select id="typeFilter" class="form-select form-select-sm">
                <option value="">All Types</option>
                <option value="hardware">Hardware</option>
                <option value="software">Software</option>
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
    <div class="col-md-4">
        <div class="stats-summary-card text-center">
            <div class="number text-primary" id="totalRecords">0</div>
            <div class="label">Total Products</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stats-summary-card text-center">
            <div class="number text-warning" id="lowStockCount">0</div>
            <div class="label">Low Stock Items</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stats-summary-card text-center">
            <div class="number text-danger" id="outOfStockCount">0</div>
            <div class="label">Out of Stock</div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- MAIN CARD -->
<!-- ============================================ -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fas fa-exclamation-triangle text-primary me-2"></i>Low Stock Report</h6>
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
                        <th>SKU</th>
                        <th>Category</th>
                        <th>Type</th>
                        <th>Quantity</th>
                        <th>Min Stock</th>
                        <th>Status</th>
                        <th>Supplier</th>
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
        const category = $('#categoryFilter').val();
        const type = $('#typeFilter').val();

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
            category_id: category,
            type: type
        });

        // FIXED: Using the correct route name without 'data' prefix
        $.ajax({
            url: '{{ route("admin.reports.api.low-stock") }}?' + params.toString(),
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
                                <p class="text-muted">No low stock items found.</p>
                            </td>
                        </tr>
                    `);
                }
            },
            error: function(xhr) {
                console.error('Error loading low stock data:', xhr);
                showToast('error', 'Failed to load report data. Please try again.');
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
                        <p class="text-muted">No low stock items found.</p>
                    </td>
                </tr>
            `);
            return;
        }

        let html = '';
        data.forEach(function(item, index) {
            let stockStatus = '';
            let stockClass = '';
            if (item.quantity == 0) {
                stockStatus = 'Out of Stock';
                stockClass = 'low-stock-critical';
            } else if (item.quantity <= item.min_stock * 0.5) {
                stockStatus = 'Critical';
                stockClass = 'low-stock-critical';
            } else {
                stockStatus = 'Low Stock';
                stockClass = 'low-stock-warning';
            }

            const statusColors = {
                'available': 'success',
                'assigned': 'primary',
                'under_repair': 'warning text-dark',
                'retired': 'secondary',
                'expired': 'danger'
            };
            const badgeClass = statusColors[item.status] || 'secondary';

            html += `
                <tr>
                    <td>${index + 1}</td>
                    <td><strong>${item.name}</strong></td>
                    <td><span class="badge bg-secondary">${item.sku}</span></td>
                    <td>${item.category}</td>
                    <td><span class="badge bg-${item.type == 'hardware' ? 'primary' : 'success'}">${item.type}</span></td>
                    <td class="text-center fw-bold">${item.quantity}</td>
                    <td class="text-center">${item.min_stock}</td>
                    <td><span class="${stockClass}">${stockStatus}</span></td>
                    <td>${item.supplier}</td>
                </tr>
            `;
        });

        body.html(html);
    }

    function updateStats(response) {
        const stats = response.stats || {};
        $('#totalRecords').text(stats.total || 0);
        $('#lowStockCount').text(stats.low_stock || 0);
        $('#outOfStockCount').text(stats.out_of_stock || 0);
    }

    function exportToExcel() {
        if (currentData.length === 0) {
            showToast('warning', 'No data to export.');
            return;
        }

        try {
            const excelData = currentData.map(function(item, index) {
                let stockStatus = '';
                if (item.quantity == 0) {
                    stockStatus = 'Out of Stock';
                } else if (item.quantity <= item.min_stock * 0.5) {
                    stockStatus = 'Critical';
                } else {
                    stockStatus = 'Low Stock';
                }

                return {
                    '#': index + 1,
                    'Product': item.name,
                    'SKU': item.sku,
                    'Category': item.category,
                    'Type': item.type,
                    'Quantity': item.quantity,
                    'Min Stock': item.min_stock,
                    'Status': stockStatus,
                    'Supplier': item.supplier
                };
            });

            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.json_to_sheet(excelData);
            ws['!cols'] = [
                { wch: 5 }, { wch: 30 }, { wch: 15 }, { wch: 20 },
                { wch: 10 }, { wch: 10 }, { wch: 10 }, { wch: 15 }, { wch: 20 }
            ];

            XLSX.utils.book_append_sheet(wb, ws, 'Low Stock Report');
            const fileName = `low_stock_report_${new Date().toISOString().slice(0,10)}.xlsx`;
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
            const headers = ['#', 'Product', 'SKU', 'Category', 'Type', 'Quantity', 'Min Stock', 'Status', 'Supplier'];
            const rows = currentData.map(function(item, index) {
                let stockStatus = '';
                if (item.quantity == 0) {
                    stockStatus = 'Out of Stock';
                } else if (item.quantity <= item.min_stock * 0.5) {
                    stockStatus = 'Critical';
                } else {
                    stockStatus = 'Low Stock';
                }

                return [
                    index + 1,
                    item.name,
                    item.sku,
                    item.category,
                    item.type,
                    item.quantity,
                    item.min_stock,
                    stockStatus,
                    item.supplier
                ];
            });

            let csv = headers.join(',') + '\n';
            rows.forEach(function(row) {
                csv += row.map(val => `"${val}"`).join(',') + '\n';
            });

            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.setAttribute('href', URL.createObjectURL(blob));
            link.setAttribute('download', `low_stock_report_${new Date().toISOString().slice(0,10)}.csv`);
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
                .low-stock-critical { background: #fee2e2; color: #991b1b; padding: 2px 8px; border-radius: 4px; font-weight: 600; }
                .low-stock-warning { background: #fef3c7; color: #92400e; padding: 2px 8px; border-radius: 4px; font-weight: 600; }
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
                <h2>Low Stock Report</h2>
                <p>Generated on: ${new Date().toLocaleString()}</p>
            </div>
            <div class="date-range">
                Period: ${$('#dateFrom').val()} to ${$('#dateTo').val()}
            </div>
            <div class="stats-summary">
                <div class="stat-item"><div class="number text-primary">${$('#totalRecords').text()}</div><div class="label">Total</div></div>
                <div class="stat-item"><div class="number text-warning">${$('#lowStockCount').text()}</div><div class="label">Low Stock</div></div>
                <div class="stat-item"><div class="number text-danger">${$('#outOfStockCount').text()}</div><div class="label">Out of Stock</div></div>
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