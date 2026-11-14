@extends('layouts.admin')

@section('title', 'Warehouses Report')
@section('page-title', 'Warehouses Report')

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
        <div class="col-md-4">
            <label class="form-label fw-bold small">Status</label>
            <select id="statusFilter" class="form-select form-select-sm">
                <option value="">All</option>
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-bold small">Search</label>
            <input type="text" id="searchFilter" class="form-control form-control-sm" placeholder="Search warehouses...">
        </div>
        <div class="col-md-4">
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
            <div class="label">Total Warehouses</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-summary-card text-center">
            <div class="number text-success" id="activeCount">0</div>
            <div class="label">Active</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-summary-card text-center">
            <div class="number text-secondary" id="inactiveCount">0</div>
            <div class="label">Inactive</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-summary-card text-center">
            <div class="number text-info" id="productCount">0</div>
            <div class="label">Total Products</div>
        </div>
    </div>
</div>

<!-- MAIN TABLE -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fas fa-warehouse text-primary me-2"></i>Warehouses Report</h6>
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
                        <th>Name</th>
                        <th>Code</th>
                        <th>Manager</th>
                        <th>City</th>
                        <th>Country</th>
                        <th>Products</th>
                        <th>Total Qty</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <tr><td colspan="9" class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x text-primary mb-3 d-block"></i><p class="text-muted">Loading data...</p></td></tr>
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
            is_active: $('#statusFilter').val(),
            search: $('#searchFilter').val()
        });

        $('#tableBody').html(`<tr><td colspan="9" class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x text-primary mb-3 d-block"></i><p class="text-muted">Loading data...</p></td></tr>`);

        $.ajax({
            url: '{{ route("admin.reports.api.warehouses") }}?' + params.toString(),
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
                $('#tableBody').html(`<tr><td colspan="9" class="text-center py-5 text-danger"><i class="fas fa-exclamation-circle fa-3x mb-3 d-block"></i><p>Failed to load data.</p></td></tr>`);
            }
        });
    }

    function renderTable(response) {
        const data = response.data || [];
        const body = $('#tableBody');

        if (data.length === 0) {
            body.html(`<tr><td colspan="9" class="text-center py-5"><i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i><p class="text-muted">No warehouses found.</p></td></tr>`);
            return;
        }

        let html = '';
        data.forEach(function(item, index) {
            html += `<tr>
                <td>${index + 1}</td>
                <td><strong>${item.name}</strong></td>
                <td><span class="badge bg-secondary">${item.code}</span></td>
                <td>${item.manager || 'N/A'}</td>
                <td>${item.city || 'N/A'}</td>
                <td>${item.country || 'N/A'}</td>
                <td><span class="badge bg-info">${item.product_count || 0}</span></td>
                <td>${item.total_quantity || 0}</td>
                <td><span class="badge bg-${item.is_active ? 'success' : 'secondary'}">${item.is_active ? 'Active' : 'Inactive'}</span></td>
            </tr>`;
        });
        body.html(html);
    }

    function updateStats(response) {
        const stats = response.stats || {};
        $('#totalRecords').text(stats.total || 0);
        $('#activeCount').text(stats.active || 0);
        $('#inactiveCount').text(stats.inactive || 0);
        $('#productCount').text(stats.total_products || 0);
    }

    function exportToExcel() {
        if (currentData.length === 0) { showToast('warning', 'No data to export.'); return; }
        try {
            const excelData = currentData.map(function(item, index) {
                return {
                    '#': index + 1,
                    'Name': item.name,
                    'Code': item.code,
                    'Manager': item.manager || 'N/A',
                    'City': item.city || 'N/A',
                    'Country': item.country || 'N/A',
                    'Products': item.product_count || 0,
                    'Total Quantity': item.total_quantity || 0,
                    'Status': item.is_active ? 'Active' : 'Inactive'
                };
            });
            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.json_to_sheet(excelData);
            XLSX.utils.book_append_sheet(wb, ws, 'Warehouses');
            XLSX.writeFile(wb, `warehouses_report_${new Date().toISOString().slice(0,10)}.xlsx`);
            showToast('success', 'Export successful!');
        } catch (error) {
            showToast('error', 'Failed to export.');
        }
    }

    function exportToCSV() {
        if (currentData.length === 0) { showToast('warning', 'No data to export.'); return; }
        try {
            const headers = ['#', 'Name', 'Code', 'Manager', 'City', 'Country', 'Products', 'Total Quantity', 'Status'];
            let csv = headers.join(',') + '\n';
            currentData.forEach(function(item, index) {
                const row = [index + 1, item.name, item.code, item.manager || 'N/A', item.city || 'N/A', item.country || 'N/A', item.product_count || 0, item.total_quantity || 0, item.is_active ? 'Active' : 'Inactive'];
                csv += row.map(val => `"${val}"`).join(',') + '\n';
            });
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `warehouses_report_${new Date().toISOString().slice(0,10)}.csv`;
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
    $('#searchFilter').on('keypress', function(e) { if (e.which === 13) loadReportData(); });

    loadReportData();
});
</script>
@endpush
@endsection