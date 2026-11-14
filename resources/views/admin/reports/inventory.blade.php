@extends('layouts.admin')

@section('title', 'Inventory Report')
@section('page-title', 'Inventory Report')

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

<!-- FILTER SECTION -->
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
            <label class="form-label fw-bold small">Status</label>
            <select id="statusFilter" class="form-select form-select-sm">
                <option value="">All Status</option>
                <option value="available">Available</option>
                <option value="assigned">Assigned</option>
                <option value="under_repair">Under Repair</option>
                <option value="retired">Retired</option>
                <option value="expired">Expired</option>
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary btn-sm w-100" id="applyFilters">
                <i class="fas fa-filter me-1"></i> Apply Filters
            </button>
        </div>
    </div>
</div>

<!-- STATS SUMMARY -->
<div class="row g-3 mb-4" id="statsSummary">
    <div class="col-md-3">
        <div class="stats-summary-card text-center">
            <div class="number text-primary" id="totalRecords">0</div>
            <div class="label">Total Items</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-summary-card text-center">
            <div class="number text-success" id="totalValue">$0</div>
            <div class="label">Total Value</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-summary-card text-center">
            <div class="number text-warning" id="lowStock">0</div>
            <div class="label">Low Stock Items</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-summary-card text-center">
            <div class="number text-danger" id="outOfStock">0</div>
            <div class="label">Out of Stock</div>
        </div>
    </div>
</div>

<!-- MAIN TABLE -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fas fa-box text-primary me-2"></i>Inventory Report Data</h6>
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
                        <th>Product Name</th>
                        <th>SKU</th>
                        <th>Category</th>
                        <th>Type</th>
                        <th>Quantity</th>
                        <th>Status</th>
                        <th>Price</th>
                        <th>Total Value</th>
                        <th>Purchase Date</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <tr>
                        <td colspan="10" class="text-center py-5">
                            <i class="fas fa-spinner fa-spin fa-2x text-primary mb-3 d-block"></i>
                            <p class="text-muted">Loading data...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- TOAST CONTAINER -->
<div id="toastContainer" class="position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script>
$(document).ready(function() {
    let currentData = [];

    function loadReportData() {
        const params = new URLSearchParams({
            date_from: $('#dateFrom').val(),
            date_to: $('#dateTo').val(),
            category_id: $('#categoryFilter').val(),
            type: $('#typeFilter').val(),
            status: $('#statusFilter').val()
        });

        $('#tableBody').html(`<tr><td colspan="10" class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x text-primary mb-3 d-block"></i><p class="text-muted">Loading data...</p></td></tr>`);

        $.ajax({
            url: '{{ route("admin.reports.api.inventory") }}?' + params.toString(),
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response && response.data) {
                    currentData = response.data;
                    renderTable(response);
                    updateStats(response);
                }
            },
            error: function() {
                showToast('error', 'Failed to load report data.');
                $('#tableBody').html(`<tr><td colspan="10" class="text-center py-5 text-danger"><i class="fas fa-exclamation-circle fa-3x mb-3 d-block"></i><p>Failed to load data.</p></td></tr>`);
            }
        });
    }

    function renderTable(response) {
        const data = response.data || [];
        const body = $('#tableBody');

        if (data.length === 0) {
            body.html(`<tr><td colspan="10" class="text-center py-5"><i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i><p class="text-muted">No inventory items found.</p></td></tr>`);
            return;
        }

        const statusColors = {
            'available': 'success',
            'assigned': 'primary',
            'under_repair': 'warning text-dark',
            'retired': 'secondary',
            'expired': 'danger'
        };

        let html = '';
        data.forEach(function(item, index) {
            const badgeClass = statusColors[item.status] || 'secondary';
            const totalValue = (parseFloat(item.purchase_price) || 0) * (parseInt(item.quantity) || 0);
            html += `<tr>
                <td>${index + 1}</td>
                <td><strong>${item.name}</strong></td>
                <td><span class="badge bg-secondary">${item.sku}</span></td>
                <td>${item.category || 'N/A'}</td>
                <td><span class="badge bg-${item.type == 'hardware' ? 'primary' : 'success'}">${item.type || 'N/A'}</span></td>
                <td class="text-center ${item.quantity == 0 ? 'text-danger' : (item.quantity <= item.min_stock ? 'text-warning' : '')}">
                    ${item.quantity}
                </td>
                <td><span class="badge bg-${badgeClass}">${item.status}</span></td>
                <td>${parseFloat(item.purchase_price).toFixed(2)}</td>
                <td>${totalValue.toFixed(2)}</td>
                <td>${item.purchase_date || 'N/A'}</td>
            </tr>`;
        });
        body.html(html);
    }

    function updateStats(response) {
        const stats = response.stats || {};
        $('#totalRecords').text(stats.total || 0);
        $('#totalValue').text('$' + (stats.total_value || 0).toFixed(2));
        $('#lowStock').text(stats.low_stock || 0);
        $('#outOfStock').text(stats.out_of_stock || 0);
    }

    function exportToExcel() {
        if (currentData.length === 0) { showToast('warning', 'No data to export.'); return; }
        try {
            const excelData = currentData.map(function(item, index) {
                return {
                    '#': index + 1,
                    'Product Name': item.name,
                    'SKU': item.sku,
                    'Category': item.category || 'N/A',
                    'Type': item.type || 'N/A',
                    'Quantity': item.quantity,
                    'Status': item.status,
                    'Price': parseFloat(item.purchase_price).toFixed(2),
                    'Total Value': (parseFloat(item.purchase_price) * parseInt(item.quantity)).toFixed(2),
                    'Purchase Date': item.purchase_date || 'N/A'
                };
            });
            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.json_to_sheet(excelData);
            ws['!cols'] = [{ wch: 5 }, { wch: 30 }, { wch: 15 }, { wch: 20 }, { wch: 10 }, { wch: 10 }, { wch: 15 }, { wch: 12 }, { wch: 15 }, { wch: 15 }];
            XLSX.utils.book_append_sheet(wb, ws, 'Inventory Report');
            XLSX.writeFile(wb, `inventory_report_${new Date().toISOString().slice(0,10)}.xlsx`);
            showToast('success', 'Report exported successfully!');
        } catch (error) {
            showToast('error', 'Failed to export Excel file.');
        }
    }

    function exportToCSV() {
        if (currentData.length === 0) { showToast('warning', 'No data to export.'); return; }
        try {
            const headers = ['#', 'Product Name', 'SKU', 'Category', 'Type', 'Quantity', 'Status', 'Price', 'Total Value', 'Purchase Date'];
            let csv = headers.join(',') + '\n';
            currentData.forEach(function(item, index) {
                const row = [
                    index + 1, item.name, item.sku, item.category || 'N/A', item.type || 'N/A',
                    item.quantity, item.status, parseFloat(item.purchase_price).toFixed(2),
                    (parseFloat(item.purchase_price) * parseInt(item.quantity)).toFixed(2),
                    item.purchase_date || 'N/A'
                ];
                csv += row.map(val => `"${val}"`).join(',') + '\n';
            });
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `inventory_report_${new Date().toISOString().slice(0,10)}.csv`;
            link.click();
            showToast('success', 'CSV exported successfully!');
        } catch (error) {
            showToast('error', 'Failed to export CSV file.');
        }
    }

    function printReport() {
        window.print();
    }

    function showToast(type, message) {
        const icons = { success: 'check-circle', error: 'exclamation-circle', warning: 'exclamation-triangle' };
        const colors = { success: 'success', error: 'danger', warning: 'warning' };
        const toast = $(`<div class="toast show" role="alert" style="min-width:300px;">
            <div class="toast-header bg-${colors[type]} text-white">
                <strong class="me-auto"><i class="fas fa-${icons[type]} me-2"></i> ${type.charAt(0).toUpperCase() + type.slice(1)}</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">${message}</div>
        </div>`);
        $('#toastContainer').append(toast);
        setTimeout(() => toast.remove(), 5000);
    }

    $('#applyFilters').on('click', loadReportData);
    $('#exportExcel').on('click', exportToExcel);
    $('#exportCSV').on('click', exportToCSV);
    $('#printReport').on('click', printReport);
    $('#dateFrom, #dateTo').on('keypress', function(e) { if (e.which === 13) loadReportData(); });

    loadReportData();
});
</script>
@endpush
@endsection