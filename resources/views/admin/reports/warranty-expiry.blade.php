@extends('layouts.admin')

@section('title', 'Warranty Expiry Report')
@section('page-title', 'Warranty Expiry Report')

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
            <label class="form-label fw-bold small">Warranty Status</label>
            <select id="warrantyStatusFilter" class="form-select form-select-sm">
                <option value="">All</option>
                <option value="expired">Expired</option>
                <option value="expiring_30">Expiring in 30 Days</option>
                <option value="expiring_60">Expiring in 60 Days</option>
                <option value="active">Active</option>
            </select>
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
            <label class="form-label fw-bold small">Warehouse</label>
            <select id="warehouseFilter" class="form-select form-select-sm">
                <option value="">All Warehouses</option>
                @foreach($warehouses ?? [] as $warehouse)
                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-bold small">Date Range</label>
            <div class="d-flex gap-2">
                <input type="date" id="dateFrom" class="form-control form-control-sm" value="{{ now()->startOfYear()->format('Y-m-d') }}">
                <input type="date" id="dateTo" class="form-control form-control-sm" value="{{ now()->addYear()->format('Y-m-d') }}">
            </div>
        </div>
        <div class="col-md-3">
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
            <div class="number text-danger" id="expiredCount">0</div>
            <div class="label">Expired</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-summary-card text-center">
            <div class="number text-warning" id="expiring30Count">0</div>
            <div class="label">Expiring in 30 Days</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-summary-card text-center">
            <div class="number text-info" id="expiring60Count">0</div>
            <div class="label">Expiring in 60 Days</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-summary-card text-center">
            <div class="number text-success" id="activeCount">0</div>
            <div class="label">Active</div>
        </div>
    </div>
</div>

<!-- MAIN TABLE -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fas fa-clock text-danger me-2"></i>Warranty Expiry Report</h6>
        <div class="d-flex gap-2">
            <button class="btn btn-success btn-sm" id="exportExcel"><i class="fas fa-file-excel me-1"></i> Export</button>
            <button class="btn btn-secondary btn-sm" id="exportCSV"><i class="fas fa-file-csv me-1"></i> CSV</button>
            <button class="btn btn-info btn-sm" id="printReport"><i class="fas fa-print me-1"></i> Print</button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Product Name</th>
                        <th>SKU</th>
                        <th>Category</th>
                        <th>Supplier</th>
                        <th>Warranty End Date</th>
                        <th>Days Left</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <tr><td colspan="8" class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x text-primary mb-3 d-block"></i><p class="text-muted">Loading data...</p></td></tr>
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
        const params = new URLSearchParams({
            warranty_status: $('#warrantyStatusFilter').val(),
            category_id: $('#categoryFilter').val(),
            warehouse_id: $('#warehouseFilter').val(),
            date_from: $('#dateFrom').val(),
            date_to: $('#dateTo').val()
        });

        $('#tableBody').html(`<tr><td colspan="8" class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x text-primary mb-3 d-block"></i><p class="text-muted">Loading data...</p></td></tr>`);

        $.ajax({
            url: '{{ route("admin.reports.api.warranty") }}?' + params.toString(),
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
                $('#tableBody').html(`<tr><td colspan="8" class="text-center py-5 text-danger"><i class="fas fa-exclamation-circle fa-3x mb-3 d-block"></i><p>Failed to load data.</p></td></tr>`);
            }
        });
    }

    function renderTable(response) {
        const data = response.data || [];
        const body = $('#tableBody');

        if (data.length === 0) {
            body.html(`<tr><td colspan="8" class="text-center py-5"><i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i><p class="text-muted">No products with warranty data found.</p></td></tr>`);
            return;
        }

        const statusColors = { 'expired': 'danger', 'expiring_soon': 'warning text-dark', 'active': 'success' };
        const statusLabels = { 'expired': 'Expired', 'expiring_soon': 'Expiring Soon', 'active': 'Active' };

        let html = '';
        data.forEach(function(item, index) {
            const color = statusColors[item.status] || 'secondary';
            const label = statusLabels[item.status] || item.status;
            html += `<tr>
                <td>${index + 1}</td>
                <td><strong>${item.name}</strong></td>
                <td><span class="badge bg-secondary">${item.sku}</span></td>
                <td>${item.category || 'N/A'}</td>
                <td>${item.supplier || 'N/A'}</td>
                <td>${item.warranty_end_date}</td>
                <td class="${item.days_left < 0 ? 'text-danger' : (item.days_left <= 30 ? 'text-warning' : 'text-success')}">${item.days_left}</td>
                <td><span class="badge bg-${color}">${label}</span></td>
            </tr>`;
        });
        body.html(html);
    }

    function updateStats(response) {
        const stats = response.stats || {};
        $('#expiredCount').text(stats.expired || 0);
        $('#expiring30Count').text(stats.expiring_30 || 0);
        $('#expiring60Count').text(stats.expiring_60 || 0);
        $('#activeCount').text(stats.active || 0);
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
                    'Supplier': item.supplier || 'N/A',
                    'Warranty End Date': item.warranty_end_date,
                    'Days Left': item.days_left,
                    'Status': item.status == 'expired' ? 'Expired' : (item.status == 'expiring_soon' ? 'Expiring Soon' : 'Active')
                };
            });
            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.json_to_sheet(excelData);
            XLSX.utils.book_append_sheet(wb, ws, 'Warranty Report');
            XLSX.writeFile(wb, `warranty_report_${new Date().toISOString().slice(0,10)}.xlsx`);
            showToast('success', 'Export successful!');
        } catch (error) {
            showToast('error', 'Failed to export.');
        }
    }

    function exportToCSV() {
        if (currentData.length === 0) { showToast('warning', 'No data to export.'); return; }
        try {
            const headers = ['#', 'Product Name', 'SKU', 'Category', 'Supplier', 'Warranty End Date', 'Days Left', 'Status'];
            let csv = headers.join(',') + '\n';
            currentData.forEach(function(item, index) {
                const row = [index + 1, item.name, item.sku, item.category || 'N/A', item.supplier || 'N/A', item.warranty_end_date, item.days_left, item.status == 'expired' ? 'Expired' : (item.status == 'expiring_soon' ? 'Expiring Soon' : 'Active')];
                csv += row.map(val => `"${val}"`).join(',') + '\n';
            });
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `warranty_report_${new Date().toISOString().slice(0,10)}.csv`;
            link.click();
            showToast('success', 'CSV exported!');
        } catch (error) {
            showToast('error', 'Failed to export CSV.');
        }
    }

    function printReport() { window.print(); }
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

    loadReportData();
});
</script>
@endpush
@endsection