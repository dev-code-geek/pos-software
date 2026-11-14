@extends('layouts.admin')

@section('title', 'Quotations Report')
@section('page-title', 'Quotations Report')

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
            <label class="form-label fw-bold small">Customer</label>
            <select id="customerFilter" class="form-select form-select-sm">
                <option value="">All Customers</option>
                @foreach($customers ?? [] as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label fw-bold small">Status</label>
            <select id="statusFilter" class="form-select form-select-sm">
                <option value="">All</option>
                <option value="draft">Draft</option>
                <option value="sent">Sent</option>
                <option value="accepted">Accepted</option>
                <option value="rejected">Rejected</option>
                <option value="expired">Expired</option>
                <option value="converted">Converted</option>
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
            <div class="label">Total Quotations</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-summary-card text-center">
            <div class="number text-success" id="convertedCount">0</div>
            <div class="label">Converted to Sales</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-summary-card text-center">
            <div class="number text-warning" id="pendingCount">0</div>
            <div class="label">Pending (Draft/Sent)</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-summary-card text-center">
            <div class="number text-info" id="conversionRate">0%</div>
            <div class="label">Conversion Rate</div>
        </div>
    </div>
</div>

<!-- MAIN TABLE -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fas fa-file-invoice text-primary me-2"></i>Quotations Report</h6>
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
                        <th>Quotation #</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Valid Until</th>
                        <th class="text-end">Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <tr><td colspan="7" class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x text-primary mb-3 d-block"></i><p class="text-muted">Loading data...</p></td></tr>
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
            date_from: $('#dateFrom').val(),
            date_to: $('#dateTo').val(),
            customer_id: $('#customerFilter').val(),
            status: $('#statusFilter').val()
        });

        $('#tableBody').html(`<tr><td colspan="7" class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x text-primary mb-3 d-block"></i><p class="text-muted">Loading data...</p></td></tr>`);

        $.ajax({
            url: '{{ route("admin.reports.api.quotations") }}?' + params.toString(),
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response && response.data) {
                    currentData = response.data;
                    renderTable(response);
                    updateStats(response);
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr);
                showToast('error', 'Failed to load report data.');
                $('#tableBody').html(`<tr><td colspan="7" class="text-center py-5 text-danger"><i class="fas fa-exclamation-circle fa-3x mb-3 d-block"></i><p>Failed to load data.</p></td></tr>`);
            }
        });
    }

    function renderTable(response) {
        const data = response.data || [];
        const body = $('#tableBody');

        if (data.length === 0) {
            body.html(`<tr><td colspan="7" class="text-center py-5"><i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i><p class="text-muted">No quotations found.</p></td></tr>`);
            return;
        }

        const statusColors = {
            'draft': 'secondary',
            'sent': 'info',
            'accepted': 'success',
            'rejected': 'danger',
            'expired': 'warning text-dark',
            'converted': 'primary'
        };

        let html = '';
        data.forEach(function(item, index) {
            const color = statusColors[item.status] || 'secondary';
            html += `<tr>
                <td>${index + 1}</td>
                <td><span class="fw-bold">${item.quotation_number}</span></td>
                <td>${item.customer}</td>
                <td>${item.quotation_date}</td>
                <td>${item.valid_until || 'N/A'}</td>
                <td class="text-end">$${parseFloat(item.total).toFixed(2)}</td>
                <td><span class="badge bg-${color}">${item.status}</span></td>
            </tr>`;
        });
        body.html(html);
    }

    function updateStats(response) {
        const stats = response.stats || {};
        $('#totalRecords').text(stats.total || 0);
        $('#convertedCount').text(stats.converted || 0);
        $('#pendingCount').text((stats.draft || 0) + (stats.sent || 0));
        $('#conversionRate').text((stats.conversion_rate || 0) + '%');
    }

    function exportToExcel() {
        if (currentData.length === 0) { showToast('warning', 'No data to export.'); return; }
        try {
            const excelData = currentData.map(function(item, index) {
                return {
                    '#': index + 1,
                    'Quotation #': item.quotation_number,
                    'Customer': item.customer,
                    'Date': item.quotation_date,
                    'Valid Until': item.valid_until || 'N/A',
                    'Total': parseFloat(item.total).toFixed(2),
                    'Status': item.status
                };
            });
            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.json_to_sheet(excelData);
            XLSX.utils.book_append_sheet(wb, ws, 'Quotations');
            XLSX.writeFile(wb, `quotations_report_${new Date().toISOString().slice(0,10)}.xlsx`);
            showToast('success', 'Export successful!');
        } catch (error) {
            showToast('error', 'Failed to export.');
        }
    }

    function exportToCSV() {
        if (currentData.length === 0) { showToast('warning', 'No data to export.'); return; }
        try {
            const headers = ['#', 'Quotation #', 'Customer', 'Date', 'Valid Until', 'Total', 'Status'];
            let csv = headers.join(',') + '\n';
            currentData.forEach(function(item, index) {
                const row = [index + 1, item.quotation_number, item.customer, item.quotation_date, item.valid_until || 'N/A', parseFloat(item.total).toFixed(2), item.status];
                csv += row.map(val => `"${val}"`).join(',') + '\n';
            });
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `quotations_report_${new Date().toISOString().slice(0,10)}.csv`;
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