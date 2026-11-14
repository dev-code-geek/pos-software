@extends('layouts.admin')

@section('title', 'Sales Report')
@section('page-title', 'Sales Report')

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
            <label class="form-label fw-bold small">Payment Status</label>
            <select id="paymentStatusFilter" class="form-select form-select-sm">
                <option value="">All</option>
                @foreach($paymentStatuses ?? [] as $status)
                    <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label fw-bold small">Payment Method</label>
            <select id="paymentMethodFilter" class="form-select form-select-sm">
                <option value="">All</option>
                @foreach($paymentMethods ?? [] as $method)
                    <option value="{{ $method }}">{{ ucfirst(str_replace('_', ' ', $method)) }}</option>
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

<!-- STATS SUMMARY -->
<div class="row g-3 mb-4" id="statsSummary">
    <div class="col-md-3">
        <div class="stats-summary-card text-center">
            <div class="number text-primary" id="totalRecords">0</div>
            <div class="label">Total Sales</div>
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
            <div class="number text-warning" id="pendingCount">0</div>
            <div class="label">Pending</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-summary-card text-center">
            <div class="number text-info" id="paidCount">0</div>
            <div class="label">Paid</div>
        </div>
    </div>
</div>

<!-- MAIN TABLE -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fas fa-shopping-cart text-primary me-2"></i>Sales Report</h6>
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
                        <th>Invoice</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Paid</th>
                        <th>Balance</th>
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
            date_from: $('#dateFrom').val(),
            date_to: $('#dateTo').val(),
            customer_id: $('#customerFilter').val(),
            payment_status: $('#paymentStatusFilter').val(),
            payment_method: $('#paymentMethodFilter').val()
        });

        $('#tableBody').html(`<tr><td colspan="8" class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x text-primary mb-3 d-block"></i><p class="text-muted">Loading data...</p></td></tr>`);

        $.ajax({
            url: '{{ route("admin.reports.api.sales") }}?' + params.toString(),
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
            body.html(`<tr><td colspan="8" class="text-center py-5"><i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i><p class="text-muted">No sales found.</p></td></tr>`);
            return;
        }

        const statusColors = {
            'pending': 'warning text-dark',
            'partial': 'info text-dark',
            'paid': 'success',
            'cancelled': 'danger'
        };

        let html = '';
        data.forEach(function(item, index) {
            const color = statusColors[item.payment_status] || 'secondary';
            html += `<tr>
                <td>${index + 1}</td>
                <td><strong>${item.invoice_number}</strong></td>
                <td>${item.customer}</td>
                <td>${item.sale_date}</td>
                <td>${parseFloat(item.total).toFixed(2)}</td>
                <td>${parseFloat(item.paid).toFixed(2)}</td>
                <td>${parseFloat(item.balance).toFixed(2)}</td>
                <td><span class="badge bg-${color}">${item.payment_status}</span></td>
            </tr>`;
        });
        body.html(html);
    }

    function updateStats(response) {
        const stats = response.stats || {};
        $('#totalRecords').text(stats.total || 0);
        $('#totalAmount').text('$' + (stats.total_amount || 0).toFixed(2));
        $('#pendingCount').text(stats.pending || 0);
        $('#paidCount').text(stats.paid || 0);
    }

    function exportToExcel() {
        if (currentData.length === 0) { showToast('warning', 'No data to export.'); return; }
        try {
            const excelData = currentData.map(function(item, index) {
                return {
                    '#': index + 1,
                    'Invoice': item.invoice_number,
                    'Customer': item.customer,
                    'Date': item.sale_date,
                    'Total': parseFloat(item.total).toFixed(2),
                    'Paid': parseFloat(item.paid).toFixed(2),
                    'Balance': parseFloat(item.balance).toFixed(2),
                    'Status': item.payment_status
                };
            });
            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.json_to_sheet(excelData);
            XLSX.utils.book_append_sheet(wb, ws, 'Sales Report');
            XLSX.writeFile(wb, `sales_report_${new Date().toISOString().slice(0,10)}.xlsx`);
            showToast('success', 'Export successful!');
        } catch (error) {
            showToast('error', 'Failed to export.');
        }
    }

    function exportToCSV() {
        if (currentData.length === 0) { showToast('warning', 'No data to export.'); return; }
        try {
            const headers = ['#', 'Invoice', 'Customer', 'Date', 'Total', 'Paid', 'Balance', 'Status'];
            let csv = headers.join(',') + '\n';
            currentData.forEach(function(item, index) {
                const row = [index + 1, item.invoice_number, item.customer, item.sale_date, parseFloat(item.total).toFixed(2), parseFloat(item.paid).toFixed(2), parseFloat(item.balance).toFixed(2), item.payment_status];
                csv += row.map(val => `"${val}"`).join(',') + '\n';
            });
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `sales_report_${new Date().toISOString().slice(0,10)}.csv`;
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