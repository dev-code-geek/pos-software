@extends('layouts.admin')

@section('title', 'Leave Requests Report')
@section('page-title', 'Leave Requests Report')

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
            <label class="form-label fw-bold small">Employee</label>
            <select id="employeeFilter" class="form-select form-select-sm">
                <option value="">All Employees</option>
                @foreach($employees ?? [] as $employee)
                    <option value="{{ $employee->id }}">{{ $employee->first_name }} {{ $employee->last_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label fw-bold small">Leave Type</label>
            <select id="leaveTypeFilter" class="form-select form-select-sm">
                <option value="">All</option>
                @foreach($leaveTypes ?? [] as $type)
                    <option value="{{ $type->id }}">{{ $type->leave_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label fw-bold small">Status</label>
            <select id="statusFilter" class="form-select form-select-sm">
                <option value="">All</option>
                @foreach($statuses ?? [] as $status)
                    <option value="{{ $status }}">{{ $status }}</option>
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
            <div class="label">Total Requests</div>
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
            <div class="number text-success" id="approvedCount">0</div>
            <div class="label">Approved</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-summary-card text-center">
            <div class="number text-danger" id="rejectedCount">0</div>
            <div class="label">Rejected</div>
        </div>
    </div>
</div>

<!-- MAIN TABLE -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fas fa-calendar-alt text-primary me-2"></i>Leave Requests Report</h6>
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
                        <th>Employee</th>
                        <th>Leave Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Days</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <tr>
                        <td colspan="7" class="text-center py-5">
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
        const params = new URLSearchParams({
            date_from: $('#dateFrom').val(),
            date_to: $('#dateTo').val(),
            employee_id: $('#employeeFilter').val(),
            leave_type_id: $('#leaveTypeFilter').val(),
            approval_status: $('#statusFilter').val()
        });

        $('#tableBody').html(`
            <tr>
                <td colspan="7" class="text-center py-5">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary mb-3 d-block"></i>
                    <p class="text-muted">Loading data...</p>
                </td>
            </tr>
        `);

        $.ajax({
            url: '{{ route("admin.reports.api.leave-requests") }}?' + params.toString(),
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
                            <td colspan="7" class="text-center py-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                <p class="text-muted">No leave requests found.</p>
                            </td>
                        </tr>
                    `);
                }
            },
            error: function(xhr) {
                console.error('Error loading leave requests:', xhr);
                var errorMsg = 'Failed to load report data. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg = xhr.responseJSON.error;
                }
                showToast('error', errorMsg);
                $('#tableBody').html(`
                    <tr>
                        <td colspan="7" class="text-center py-5 text-danger">
                            <i class="fas fa-exclamation-circle fa-3x mb-3 d-block"></i>
                            <p>${errorMsg}</p>
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
                    <td colspan="7" class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                        <p class="text-muted">No leave requests found.</p>
                    </td>
                </tr>
            `);
            return;
        }

        const statusColors = {
            'Pending': 'warning text-dark',
            'Approved': 'success',
            'Rejected': 'danger',
            'Cancelled': 'secondary'
        };

        let html = '';
        data.forEach(function(item, index) {
            const color = statusColors[item.status] || 'secondary';
            html += `
                <tr>
                    <td>${index + 1}</td>
                    <td><strong>${item.employee}</strong><br><small class="text-muted">${item.employee_code}</small></td>
                    <td>${item.leave_type}</td>
                    <td>${item.start_date}</td>
                    <td>${item.end_date}</td>
                    <td>${item.total_days}</td>
                    <td><span class="badge bg-${color}">${item.status}</span></td>
                </tr>
            `;
        });
        body.html(html);
    }

    function updateStats(response) {
        const stats = response.stats || {};
        $('#totalRecords').text(stats.total || 0);
        $('#pendingCount').text(stats.pending || 0);
        $('#approvedCount').text(stats.approved || 0);
        $('#rejectedCount').text(stats.rejected || 0);
    }

    function exportToExcel() {
        if (currentData.length === 0) { showToast('warning', 'No data to export.'); return; }
        try {
            const excelData = currentData.map(function(item, index) {
                return {
                    '#': index + 1,
                    'Employee': item.employee,
                    'Employee Code': item.employee_code,
                    'Leave Type': item.leave_type,
                    'Start Date': item.start_date,
                    'End Date': item.end_date,
                    'Days': item.total_days,
                    'Status': item.status
                };
            });
            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.json_to_sheet(excelData);
            XLSX.utils.book_append_sheet(wb, ws, 'Leave Requests');
            XLSX.writeFile(wb, `leave_requests_report_${new Date().toISOString().slice(0,10)}.xlsx`);
            showToast('success', 'Export successful!');
        } catch (error) {
            showToast('error', 'Failed to export.');
        }
    }

    function exportToCSV() {
        if (currentData.length === 0) { showToast('warning', 'No data to export.'); return; }
        try {
            const headers = ['#', 'Employee', 'Employee Code', 'Leave Type', 'Start Date', 'End Date', 'Days', 'Status'];
            let csv = headers.join(',') + '\n';
            currentData.forEach(function(item, index) {
                const row = [index + 1, item.employee, item.employee_code, item.leave_type, item.start_date, item.end_date, item.total_days, item.status];
                csv += row.map(val => `"${val}"`).join(',') + '\n';
            });
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `leave_requests_report_${new Date().toISOString().slice(0,10)}.csv`;
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
        setTimeout(() => toast.remove(), 5000);
    }

    $('#applyFilters').on('click', loadReportData);
    $('#dateFrom, #dateTo').on('keypress', function(e) { if (e.which === 13) loadReportData(); });
    $('#exportExcel').on('click', exportToExcel);
    $('#exportCSV').on('click', exportToCSV);
    $('#printReport').on('click', printReport);

    loadReportData();
});
</script>
@endpush
@endsection