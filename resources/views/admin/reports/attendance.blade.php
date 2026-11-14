@extends('layouts.admin')

@section('title', 'Attendance Report')
@section('page-title', 'Attendance Report')

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
            <label class="form-label fw-bold small">Status</label>
            <select id="statusFilter" class="form-select form-select-sm">
                <option value="">All</option>
                @foreach($statuses ?? [] as $status)
                    <option value="{{ $status }}">{{ $status }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label fw-bold small">Approved</label>
            <select id="approvedFilter" class="form-select form-select-sm">
                <option value="">All</option>
                <option value="1">Approved</option>
                <option value="0">Pending</option>
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
            <div class="label">Total Records</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-summary-card text-center">
            <div class="number text-success" id="presentCount">0</div>
            <div class="label">Present</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-summary-card text-center">
            <div class="number text-danger" id="absentCount">0</div>
            <div class="label">Absent</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-summary-card text-center">
            <div class="number text-warning" id="pendingApproval">0</div>
            <div class="label">Pending Approval</div>
        </div>
    </div>
</div>

<!-- MAIN TABLE -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fas fa-calendar-check text-primary me-2"></i>Attendance Report</h6>
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
                        <th>Date</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                        <th>Total Hours</th>
                        <th>Status</th>
                        <th>Approved</th>
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
        const params = new URLSearchParams({
            date_from: $('#dateFrom').val(),
            date_to: $('#dateTo').val(),
            employee_id: $('#employeeFilter').val(),
            attendance_status: $('#statusFilter').val(),
            is_approved: $('#approvedFilter').val()
        });

        $('#tableBody').html(`
            <tr>
                <td colspan="8" class="text-center py-5">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary mb-3 d-block"></i>
                    <p class="text-muted">Loading data...</p>
                </td>
            </tr>
        `);

        $.ajax({
            url: '{{ route("admin.reports.api.attendance") }}?' + params.toString(),
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
                            <td colspan="8" class="text-center py-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                <p class="text-muted">No attendance records found.</p>
                            </td>
                        </tr>
                    `);
                }
            },
            error: function(xhr) {
                console.error('Error loading attendance data:', xhr);
                var errorMsg = 'Failed to load report data. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg = xhr.responseJSON.error;
                }
                showToast('error', errorMsg);
                $('#tableBody').html(`
                    <tr>
                        <td colspan="8" class="text-center py-5 text-danger">
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
                    <td colspan="8" class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                        <p class="text-muted">No attendance records found.</p>
                    </td>
                </tr>
            `);
            return;
        }

        const statusColors = {
            'Present': 'success',
            'Absent': 'danger',
            'Late': 'warning text-dark',
            'Half Day': 'info text-dark',
            'On Leave': 'primary',
            'On Travel': 'secondary'
        };

        let html = '';
        data.forEach(function(item, index) {
            const color = statusColors[item.status] || 'secondary';
            html += `
                <tr>
                    <td>${index + 1}</td>
                    <td><strong>${item.employee}</strong><br><small class="text-muted">${item.employee_code}</small></td>
                    <td>${item.attendance_date}</td>
                    <td>${item.check_in || '--'}</td>
                    <td>${item.check_out || '--'}</td>
                    <td>${item.total_hours || '0.00'}</td>
                    <td><span class="badge bg-${color}">${item.status}</span></td>
                    <td>${item.is_approved ? '<span class="badge bg-success">Approved</span>' : '<span class="badge bg-warning text-dark">Pending</span>'}</td>
                </tr>
            `;
        });
        body.html(html);
    }

    function updateStats(response) {
        const stats = response.stats || {};
        $('#totalRecords').text(stats.total || 0);
        $('#presentCount').text(stats.present || 0);
        $('#absentCount').text(stats.absent || 0);
        $('#pendingApproval').text(stats.pending_approval || 0);
    }

    function exportToExcel() {
        if (currentData.length === 0) { showToast('warning', 'No data to export.'); return; }
        try {
            const excelData = currentData.map(function(item, index) {
                return {
                    '#': index + 1,
                    'Employee': item.employee,
                    'Employee Code': item.employee_code,
                    'Date': item.attendance_date,
                    'Check In': item.check_in || '--',
                    'Check Out': item.check_out || '--',
                    'Total Hours': item.total_hours || '0.00',
                    'Status': item.status,
                    'Approved': item.is_approved ? 'Yes' : 'No'
                };
            });
            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.json_to_sheet(excelData);
            XLSX.utils.book_append_sheet(wb, ws, 'Attendance Report');
            XLSX.writeFile(wb, `attendance_report_${new Date().toISOString().slice(0,10)}.xlsx`);
            showToast('success', 'Export successful!');
        } catch (error) {
            showToast('error', 'Failed to export.');
        }
    }

    function exportToCSV() {
        if (currentData.length === 0) { showToast('warning', 'No data to export.'); return; }
        try {
            const headers = ['#', 'Employee', 'Employee Code', 'Date', 'Check In', 'Check Out', 'Total Hours', 'Status', 'Approved'];
            let csv = headers.join(',') + '\n';
            currentData.forEach(function(item, index) {
                const row = [index + 1, item.employee, item.employee_code, item.attendance_date, item.check_in || '--', item.check_out || '--', item.total_hours || '0.00', item.status, item.is_approved ? 'Yes' : 'No'];
                csv += row.map(val => `"${val}"`).join(',') + '\n';
            });
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `attendance_report_${new Date().toISOString().slice(0,10)}.csv`;
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