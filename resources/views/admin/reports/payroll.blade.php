@extends('layouts.admin')

@section('title', 'Payroll Reports')
@section('page-title', 'Payroll Reports')

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
.nav-tabs .nav-link {
    color: #475569;
    font-weight: 500;
}
.nav-tabs .nav-link.active {
    color: #4f46e5;
    border-bottom: 3px solid #4f46e5;
}
.tab-pane {
    padding-top: 20px;
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
            <label class="form-label fw-bold small">Company</label>
            <select id="companyFilter" class="form-select form-select-sm">
                <option value="">All Companies</option>
                @foreach($companies ?? [] as $company)
                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label fw-bold small">Period</label>
            <select id="periodFilter" class="form-select form-select-sm">
                <option value="">All Periods</option>
                @foreach($periods ?? [] as $period)
                    <option value="{{ $period->id }}">{{ $period->period_name }}</option>
                @endforeach
            </select>
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
            <div class="number text-primary" id="totalPeriods">0</div>
            <div class="label">Total Periods</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-summary-card text-center">
            <div class="number text-success" id="totalCalculations">0</div>
            <div class="label">Calculations</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-summary-card text-center">
            <div class="number text-warning" id="totalNetSalary">$0</div>
            <div class="label">Total Net Salary</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-summary-card text-center">
            <div class="number text-danger" id="totalLoans">0</div>
            <div class="label">Active Loans</div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- TABS -->
<!-- ============================================ -->
<ul class="nav nav-tabs" id="payrollTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="summary-tab" data-bs-toggle="tab" data-bs-target="#summary" type="button" role="tab">
            <i class="fas fa-chart-pie me-1"></i> Summary
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="periods-tab" data-bs-toggle="tab" data-bs-target="#periods" type="button" role="tab">
            <i class="fas fa-calendar-alt me-1"></i> Periods
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="calculations-tab" data-bs-toggle="tab" data-bs-target="#calculations" type="button" role="tab">
            <i class="fas fa-calculator me-1"></i> Calculations
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="payslips-tab" data-bs-toggle="tab" data-bs-target="#payslips" type="button" role="tab">
            <i class="fas fa-file-invoice me-1"></i> Payslips
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="loans-tab" data-bs-toggle="tab" data-bs-target="#loans" type="button" role="tab">
            <i class="fas fa-hand-holding-usd me-1"></i> Loans
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="overtime-tab" data-bs-toggle="tab" data-bs-target="#overtime" type="button" role="tab">
            <i class="fas fa-clock me-1"></i> Overtime
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="adjustments-tab" data-bs-toggle="tab" data-bs-target="#adjustments" type="button" role="tab">
            <i class="fas fa-adjust me-1"></i> Adjustments
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="rates-tab" data-bs-toggle="tab" data-bs-target="#rates" type="button" role="tab">
            <i class="fas fa-percent me-1"></i> Rates
        </button>
    </li>
</ul>

<div class="tab-content" id="payrollTabsContent">

    <!-- ============================================ -->
    <!-- TAB 1: SUMMARY -->
    <!-- ============================================ -->
    <div class="tab-pane fade show active" id="summary" role="tabpanel">
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-chart-bar text-primary me-2"></i>Monthly Payroll Trend</h6>
                    </div>
                    <div class="card-body">
                        <div style="height: 300px;">
                            <canvas id="trendChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-chart-pie text-success me-2"></i>Payment Status Distribution</h6>
                    </div>
                    <div class="card-body">
                        <div style="height: 300px;">
                            <canvas id="statusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-clock text-warning me-2"></i>Recent Calculations</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Employee</th>
                                        <th>Period</th>
                                        <th>Net Salary</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody id="recentTableBody">
                                    <tr><td colspan="6" class="text-center py-3"><i class="fas fa-spinner fa-spin me-2"></i>Loading...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- TAB 2: PERIODS -->
    <!-- ============================================ -->
    <div class="tab-pane fade" id="periods" role="tabpanel">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-calendar-alt text-primary me-2"></i>Payroll Periods</h6>
                <div class="d-flex gap-2">
                    <button class="btn btn-success btn-sm" id="exportPeriods"><i class="fas fa-file-excel me-1"></i> Export</button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Period Code</th>
                                <th>Period Name</th>
                                <th>Company</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Pay Date</th>
                                <th>Status</th>
                                <th>Locked</th>
                            </tr>
                        </thead>
                        <tbody id="periodsTableBody">
                            <tr><td colspan="9" class="text-center py-3"><i class="fas fa-spinner fa-spin me-2"></i>Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- TAB 3: CALCULATIONS -->
    <!-- ============================================ -->
    <div class="tab-pane fade" id="calculations" role="tabpanel">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-calculator text-primary me-2"></i>Payroll Calculations</h6>
                <div class="d-flex gap-2">
                    <button class="btn btn-success btn-sm" id="exportCalculations"><i class="fas fa-file-excel me-1"></i> Export</button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Employee</th>
                                <th>Period</th>
                                <th>Basic Salary</th>
                                <th>Earnings</th>
                                <th>Deductions</th>
                                <th>Net Salary</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="calculationsTableBody">
                            <tr><td colspan="8" class="text-center py-3"><i class="fas fa-spinner fa-spin me-2"></i>Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- TAB 4: PAYSLIPS -->
    <!-- ============================================ -->
    <div class="tab-pane fade" id="payslips" role="tabpanel">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-file-invoice text-primary me-2"></i>Payslips</h6>
                <div class="d-flex gap-2">
                    <button class="btn btn-success btn-sm" id="exportPayslips"><i class="fas fa-file-excel me-1"></i> Export</button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Payslip Number</th>
                                <th>Employee</th>
                                <th>Period</th>
                                <th>Issue Date</th>
                                <th>Downloaded</th>
                                <th>Emailed</th>
                            </tr>
                        </thead>
                        <tbody id="payslipsTableBody">
                            <tr><td colspan="7" class="text-center py-3"><i class="fas fa-spinner fa-spin me-2"></i>Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- TAB 5: LOANS -->
    <!-- ============================================ -->
    <div class="tab-pane fade" id="loans" role="tabpanel">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-hand-holding-usd text-primary me-2"></i>Employee Loans</h6>
                <div class="d-flex gap-2">
                    <button class="btn btn-success btn-sm" id="exportLoans"><i class="fas fa-file-excel me-1"></i> Export</button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Loan Number</th>
                                <th>Employee</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Monthly Installment</th>
                                <th>Remaining</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="loansTableBody">
                            <tr><td colspan="8" class="text-center py-3"><i class="fas fa-spinner fa-spin me-2"></i>Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- TAB 6: OVERTIME -->
    <!-- ============================================ -->
    <div class="tab-pane fade" id="overtime" role="tabpanel">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-clock text-primary me-2"></i>Employee Overtime</h6>
                <div class="d-flex gap-2">
                    <button class="btn btn-success btn-sm" id="exportOvertime"><i class="fas fa-file-excel me-1"></i> Export</button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Employee</th>
                                <th>Period</th>
                                <th>Date</th>
                                <th>Hours</th>
                                <th>Rate</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="overtimeTableBody">
                            <tr><td colspan="8" class="text-center py-3"><i class="fas fa-spinner fa-spin me-2"></i>Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- TAB 7: ADJUSTMENTS -->
    <!-- ============================================ -->
    <div class="tab-pane fade" id="adjustments" role="tabpanel">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-adjust text-primary me-2"></i>Payroll Adjustments</h6>
                <div class="d-flex gap-2">
                    <button class="btn btn-success btn-sm" id="exportAdjustments"><i class="fas fa-file-excel me-1"></i> Export</button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Employee</th>
                                <th>Period</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Amount</th>
                                <th>Taxable</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="adjustmentsTableBody">
                            <tr><td colspan="8" class="text-center py-3"><i class="fas fa-spinner fa-spin me-2"></i>Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- TAB 8: RATES -->
    <!-- ============================================ -->
    <div class="tab-pane fade" id="rates" role="tabpanel">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-percent text-primary me-2"></i>Payroll Rates</h6>
                <div class="d-flex gap-2">
                    <button class="btn btn-success btn-sm" id="exportRates"><i class="fas fa-file-excel me-1"></i> Export</button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Rate Type</th>
                                <th>Rate Value</th>
                                <th>Income From</th>
                                <th>Income To</th>
                                <th>Effective From</th>
                                <th>Effective To</th>
                                <th>Active</th>
                            </tr>
                        </thead>
                        <tbody id="ratesTableBody">
                            <tr><td colspan="8" class="text-center py-3"><i class="fas fa-spinner fa-spin me-2"></i>Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- TOAST CONTAINER -->
<!-- ============================================ -->
<div id="toastContainer" class="position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>

@push('scripts')
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<!-- SheetJS (XLSX) for Excel Export -->
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

<script>
$(document).ready(function() {
    let trendChartInstance = null;
    let statusChartInstance = null;
    let currentData = {};

    // ============================================
    // LOAD ALL REPORT DATA
    // ============================================
    function loadAllData() {
        const params = new URLSearchParams({
            date_from: $('#dateFrom').val(),
            date_to: $('#dateTo').val(),
            company_id: $('#companyFilter').val(),
            period_id: $('#periodFilter').val(),
            employee_id: $('#employeeFilter').val()
        });

        // Show loading states
        $('#periodsTableBody').html('<tr><td colspan="9" class="text-center py-3"><i class="fas fa-spinner fa-spin me-2"></i>Loading...</td></tr>');
        $('#calculationsTableBody').html('<tr><td colspan="8" class="text-center py-3"><i class="fas fa-spinner fa-spin me-2"></i>Loading...</td></tr>');
        $('#payslipsTableBody').html('<tr><td colspan="7" class="text-center py-3"><i class="fas fa-spinner fa-spin me-2"></i>Loading...</td></tr>');
        $('#loansTableBody').html('<tr><td colspan="8" class="text-center py-3"><i class="fas fa-spinner fa-spin me-2"></i>Loading...</td></tr>');
        $('#overtimeTableBody').html('<tr><td colspan="8" class="text-center py-3"><i class="fas fa-spinner fa-spin me-2"></i>Loading...</td></tr>');
        $('#adjustmentsTableBody').html('<tr><td colspan="8" class="text-center py-3"><i class="fas fa-spinner fa-spin me-2"></i>Loading...</td></tr>');
        $('#ratesTableBody').html('<tr><td colspan="8" class="text-center py-3"><i class="fas fa-spinner fa-spin me-2"></i>Loading...</td></tr>');
        $('#recentTableBody').html('<tr><td colspan="6" class="text-center py-3"><i class="fas fa-spinner fa-spin me-2"></i>Loading...</td></tr>');

        // Load all data in parallel
        Promise.all([
            loadPeriods(params),
            loadCalculations(params),
            loadPayslips(params),
            loadLoans(params),
            loadOvertime(params),
            loadAdjustments(params),
            loadRates(params),
            loadSummary(params)
        ]).then(() => {
            updateStats();
        }).catch((error) => {
            console.error('Error loading data:', error);
            showToast('error', 'Failed to load payroll data. Please try again.');
        });
    }

    // ============================================
    // LOAD PERIODS
    // ============================================
    function loadPeriods(params) {
        return $.ajax({
            url: '{{ route("admin.reports.api.payroll.periods") }}?' + params.toString(),
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                renderPeriods(response);
            },
            error: function() {
                $('#periodsTableBody').html('<tr><td colspan="9" class="text-center py-3 text-danger">Failed to load periods.</td></tr>');
            }
        });
    }

    function renderPeriods(response) {
        const data = response.data || [];
        const body = $('#periodsTableBody');

        if (data.length === 0) {
            body.html('<tr><td colspan="9" class="text-center py-3 text-muted">No periods found.</td></tr>');
            return;
        }

        const statusColors = {
            'Open': 'success',
            'Processing': 'warning text-dark',
            'Approved': 'primary',
            'Closed': 'secondary',
            'Locked': 'danger'
        };

        let html = '';
        data.forEach(function(item, index) {
            const color = statusColors[item.status] || 'secondary';
            html += `<tr>
                <td>${index + 1}</td>
                <td><span class="badge bg-secondary">${item.period_code}</span></td>
                <td><strong>${item.period_name}</strong></td>
                <td>${item.company}</td>
                <td>${item.start_date}</td>
                <td>${item.end_date}</td>
                <td>${item.pay_date}</td>
                <td><span class="badge bg-${color}">${item.status}</span></td>
                <td>${item.is_locked ? '<span class="badge bg-danger">Locked</span>' : '<span class="badge bg-success">Unlocked</span>'}</td>
            </tr>`;
        });
        body.html(html);
    }

    // ============================================
    // LOAD CALCULATIONS
    // ============================================
    function loadCalculations(params) {
        return $.ajax({
            url: '{{ route("admin.reports.api.payroll.calculations") }}?' + params.toString(),
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                renderCalculations(response);
                currentData.calculations = response;
            },
            error: function() {
                $('#calculationsTableBody').html('<tr><td colspan="8" class="text-center py-3 text-danger">Failed to load calculations.</td></tr>');
            }
        });
    }

    function renderCalculations(response) {
        const data = response.data || [];
        const body = $('#calculationsTableBody');

        if (data.length === 0) {
            body.html('<tr><td colspan="8" class="text-center py-3 text-muted">No calculations found.</td></tr>');
            return;
        }

        const statusColors = {
            'Pending': 'warning text-dark',
            'Verified': 'primary',
            'Paid': 'success'
        };

        let html = '';
        data.forEach(function(item, index) {
            const color = statusColors[item.payment_status] || 'secondary';
            html += `<tr>
                <td>${index + 1}</td>
                <td><strong>${item.employee}</strong><br><small class="text-muted">${item.employee_code}</small></td>
                <td>${item.period}</td>
                <td>${parseFloat(item.basic_salary).toFixed(2)}</td>
                <td>${parseFloat(item.total_earnings).toFixed(2)}</td>
                <td>${parseFloat(item.total_deductions).toFixed(2)}</td>
                <td><strong>${parseFloat(item.net_salary).toFixed(2)}</strong></td>
                <td><span class="badge bg-${color}">${item.payment_status}</span></td>
            </tr>`;
        });
        body.html(html);
    }

    // ============================================
    // LOAD PAYSLIPS
    // ============================================
    function loadPayslips(params) {
        return $.ajax({
            url: '{{ route("admin.reports.api.payroll.payslips") }}?' + params.toString(),
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                renderPayslips(response);
            },
            error: function() {
                $('#payslipsTableBody').html('<tr><td colspan="7" class="text-center py-3 text-danger">Failed to load payslips.</td></tr>');
            }
        });
    }

    function renderPayslips(response) {
        const data = response.data || [];
        const body = $('#payslipsTableBody');

        if (data.length === 0) {
            body.html('<tr><td colspan="7" class="text-center py-3 text-muted">No payslips found.</td></tr>');
            return;
        }

        let html = '';
        data.forEach(function(item, index) {
            html += `<tr>
                <td>${index + 1}</td>
                <td><span class="badge bg-primary">${item.payslip_number}</span></td>
                <td><strong>${item.employee}</strong></td>
                <td>${item.period}</td>
                <td>${item.issue_date}</td>
                <td>${item.is_downloaded ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>'}</td>
                <td>${item.emailed ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>'}</td>
            </tr>`;
        });
        body.html(html);
    }

    // ============================================
    // LOAD LOANS
    // ============================================
    function loadLoans(params) {
        return $.ajax({
            url: '{{ route("admin.reports.api.payroll.loans") }}?' + params.toString(),
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                renderLoans(response);
            },
            error: function() {
                $('#loansTableBody').html('<tr><td colspan="8" class="text-center py-3 text-danger">Failed to load loans.</td></tr>');
            }
        });
    }

    function renderLoans(response) {
        const data = response.data || [];
        const body = $('#loansTableBody');

        if (data.length === 0) {
            body.html('<tr><td colspan="8" class="text-center py-3 text-muted">No loans found.</td></tr>');
            return;
        }

        const statusColors = {
            'Active': 'success',
            'Completed': 'primary',
            'Suspended': 'warning text-dark',
            'Rejected': 'danger'
        };

        let html = '';
        data.forEach(function(item, index) {
            const color = statusColors[item.status] || 'secondary';
            html += `<tr>
                <td>${index + 1}</td>
                <td><span class="badge bg-secondary">${item.loan_number}</span></td>
                <td><strong>${item.employee}</strong></td>
                <td>${item.loan_type}</td>
                <td>${parseFloat(item.amount).toFixed(2)}</td>
                <td>${parseFloat(item.monthly_installment).toFixed(2)}</td>
                <td>${parseFloat(item.remaining_balance).toFixed(2)}</td>
                <td><span class="badge bg-${color}">${item.status}</span></td>
            </tr>`;
        });
        body.html(html);
    }

    // ============================================
    // LOAD OVERTIME
    // ============================================
    function loadOvertime(params) {
        return $.ajax({
            url: '{{ route("admin.reports.api.payroll.overtime") }}?' + params.toString(),
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                renderOvertime(response);
            },
            error: function() {
                $('#overtimeTableBody').html('<tr><td colspan="8" class="text-center py-3 text-danger">Failed to load overtime.</td></tr>');
            }
        });
    }

    function renderOvertime(response) {
        const data = response.data || [];
        const body = $('#overtimeTableBody');

        if (data.length === 0) {
            body.html('<tr><td colspan="8" class="text-center py-3 text-muted">No overtime records found.</td></tr>');
            return;
        }

        const statusColors = {
            'Pending': 'warning text-dark',
            'Approved': 'success',
            'Rejected': 'danger'
        };

        let html = '';
        data.forEach(function(item, index) {
            const color = statusColors[item.status] || 'secondary';
            html += `<tr>
                <td>${index + 1}</td>
                <td><strong>${item.employee}</strong></td>
                <td>${item.period}</td>
                <td>${item.overtime_date}</td>
                <td>${item.hours_worked}</td>
                <td>${item.rate_multiplier}x</td>
                <td>${parseFloat(item.amount).toFixed(2)}</td>
                <td><span class="badge bg-${color}">${item.status}</span></td>
            </tr>`;
        });
        body.html(html);
    }

    // ============================================
    // LOAD ADJUSTMENTS
    // ============================================
    function loadAdjustments(params) {
        return $.ajax({
            url: '{{ route("admin.reports.api.payroll.adjustments") }}?' + params.toString(),
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                renderAdjustments(response);
            },
            error: function() {
                $('#adjustmentsTableBody').html('<tr><td colspan="8" class="text-center py-3 text-danger">Failed to load adjustments.</td></tr>');
            }
        });
    }

    function renderAdjustments(response) {
        const data = response.data || [];
        const body = $('#adjustmentsTableBody');

        if (data.length === 0) {
            body.html('<tr><td colspan="8" class="text-center py-3 text-muted">No adjustments found.</td></tr>');
            return;
        }

        const typeColors = {
            'Allowance': 'success',
            'Deduction': 'danger'
        };

        const statusColors = {
            'Pending': 'warning text-dark',
            'Approved': 'success',
            'Rejected': 'danger'
        };

        let html = '';
        data.forEach(function(item, index) {
            const tColor = typeColors[item.adjustment_type] || 'secondary';
            const sColor = statusColors[item.status] || 'secondary';
            html += `<tr>
                <td>${index + 1}</td>
                <td><strong>${item.employee}</strong></td>
                <td>${item.period}</td>
                <td><span class="badge bg-${tColor}">${item.adjustment_type}</span></td>
                <td>${item.description}</td>
                <td>${parseFloat(item.amount).toFixed(2)}</td>
                <td>${item.is_taxable ? '<span class="badge bg-primary">Yes</span>' : '<span class="badge bg-secondary">No</span>'}</td>
                <td><span class="badge bg-${sColor}">${item.status}</span></td>
            </tr>`;
        });
        body.html(html);
    }

    // ============================================
    // LOAD RATES
    // ============================================
    function loadRates(params) {
        return $.ajax({
            url: '{{ route("admin.reports.api.payroll.rates") }}?' + params.toString(),
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                renderRates(response);
            },
            error: function() {
                $('#ratesTableBody').html('<tr><td colspan="8" class="text-center py-3 text-danger">Failed to load rates.</td></tr>');
            }
        });
    }

    function renderRates(response) {
        const data = response.data || [];
        const body = $('#ratesTableBody');

        if (data.length === 0) {
            body.html('<tr><td colspan="8" class="text-center py-3 text-muted">No rates found.</td></tr>');
            return;
        }

        let html = '';
        data.forEach(function(item, index) {
            html += `<tr>
                <td>${index + 1}</td>
                <td><span class="badge bg-primary">${item.rate_type}</span></td>
                <td><strong>${item.rate_value}%</strong></td>
                <td>${item.income_from || 'N/A'}</td>
                <td>${item.income_to || 'N/A'}</td>
                <td>${item.effective_from}</td>
                <td>${item.effective_to || 'Ongoing'}</td>
                <td>${item.is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'}</td>
            </tr>`;
        });
        body.html(html);
    }

    // ============================================
    // LOAD SUMMARY
    // ============================================
    function loadSummary(params) {
        return $.ajax({
            url: '{{ route("admin.reports.api.payroll.summary") }}?' + params.toString(),
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                renderSummary(response);
                updateCharts(response);
            },
            error: function() {
                showToast('error', 'Failed to load summary data.');
            }
        });
    }

    function renderSummary(response) {
        const stats = response.stats || {};
        const recent = response.recent_calculations || [];

        // Update stats
        $('#totalPeriods').text(stats.total_periods || 0);
        $('#totalCalculations').text(stats.total_calculations || 0);
        $('#totalNetSalary').text('$' + (stats.total_paid || 0).toFixed(2));
        $('#totalLoans').text(stats.active_loans || 0);

        // Render recent calculations
        const body = $('#recentTableBody');
        if (recent.length === 0) {
            body.html('<tr><td colspan="6" class="text-center py-3 text-muted">No recent calculations.</td></tr>');
            return;
        }

        const statusColors = {
            'Pending': 'warning text-dark',
            'Verified': 'primary',
            'Paid': 'success'
        };

        let html = '';
        recent.forEach(function(item, index) {
            const color = statusColors[item.status] || 'secondary';
            html += `<tr>
                <td>${index + 1}</td>
                <td><strong>${item.employee}</strong></td>
                <td>${item.period}</td>
                <td><strong>${parseFloat(item.net_salary).toFixed(2)}</strong></td>
                <td><span class="badge bg-${color}">${item.status}</span></td>
                <td>${item.calculated_at}</td>
            </tr>`;
        });
        body.html(html);
    }

    // ============================================
    // UPDATE STATS (from all data)
    // ============================================
    function updateStats() {
        // Stats are already updated from the summary endpoint
    }

    // ============================================
    // UPDATE CHARTS
    // ============================================
    function updateCharts(response) {
        const monthlyTrend = response.monthly_trend || [];

        // Trend Chart
        updateTrendChart(monthlyTrend);

        // Status Chart
        const stats = response.stats || {};
        updateStatusChart(stats);
    }

    function updateTrendChart(data) {
        const ctx = document.getElementById('trendChart').getContext('2d');

        if (trendChartInstance) {
            trendChartInstance.destroy();
        }

        const labels = data.map(item => item.month);
        const earnings = data.map(item => parseFloat(item.total_earnings || 0));
        const net = data.map(item => parseFloat(item.total_net || 0));

        trendChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Total Earnings',
                        data: earnings,
                        borderColor: '#4f46e5',
                        backgroundColor: 'rgba(79,70,229,0.1)',
                        fill: true,
                        tension: 0.4,
                    },
                    {
                        label: 'Net Salary',
                        data: net,
                        borderColor: '#22c55e',
                        backgroundColor: 'rgba(34,197,94,0.1)',
                        fill: true,
                        tension: 0.4,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toFixed(0);
                            }
                        }
                    }
                }
            }
        });
    }

    function updateStatusChart(stats) {
        const ctx = document.getElementById('statusChart').getContext('2d');

        if (statusChartInstance) {
            statusChartInstance.destroy();
        }

        const labels = ['Pending', 'Verified', 'Paid'];
        const values = [
            stats.pending_calculations || 0,
            stats.verified_calculations || 0,
            stats.paid_calculations || 0
        ];
        const colors = ['#f59e0b', '#4f46e5', '#22c55e'];

        statusChartInstance = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: colors,
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 15,
                            font: { size: 12 }
                        }
                    }
                },
                cutout: '65%'
            }
        });
    }

    // ============================================
    // EXPORT FUNCTIONS
    // ============================================
    function exportToExcel(tableId, fileName, data) {
        if (!data || data.length === 0) {
            showToast('warning', 'No data to export.');
            return;
        }

        try {
            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.json_to_sheet(data);
            XLSX.utils.book_append_sheet(wb, ws, 'Payroll Report');
            XLSX.writeFile(wb, `${fileName}_${new Date().toISOString().slice(0,10)}.xlsx`);
            showToast('success', 'Export successful!');
        } catch (error) {
            showToast('error', 'Failed to export.');
        }
    }

    $('#exportPeriods').on('click', function() {
        const data = currentData.periods?.data || [];
        exportToExcel('periodsTableBody', 'payroll_periods', data);
    });

    $('#exportCalculations').on('click', function() {
        const data = currentData.calculations?.data || [];
        exportToExcel('calculationsTableBody', 'payroll_calculations', data);
    });

    $('#exportPayslips').on('click', function() {
        const data = currentData.payslips?.data || [];
        exportToExcel('payslipsTableBody', 'payroll_payslips', data);
    });

    $('#exportLoans').on('click', function() {
        const data = currentData.loans?.data || [];
        exportToExcel('loansTableBody', 'payroll_loans', data);
    });

    $('#exportOvertime').on('click', function() {
        const data = currentData.overtime?.data || [];
        exportToExcel('overtimeTableBody', 'payroll_overtime', data);
    });

    $('#exportAdjustments').on('click', function() {
        const data = currentData.adjustments?.data || [];
        exportToExcel('adjustmentsTableBody', 'payroll_adjustments', data);
    });

    $('#exportRates').on('click', function() {
        const data = currentData.rates?.data || [];
        exportToExcel('ratesTableBody', 'payroll_rates', data);
    });

    // ============================================
    // TOAST NOTIFICATIONS
    // ============================================
    function showToast(type, message) {
        const icons = {
            success: 'check-circle',
            error: 'exclamation-circle',
            warning: 'exclamation-triangle',
            info: 'info-circle'
        };
        const colors = {
            success: 'success',
            error: 'danger',
            warning: 'warning',
            info: 'info'
        };

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

    // ============================================
    // EVENT LISTENERS
    // ============================================
    $('#applyFilters').on('click', loadAllData);
    $('#dateFrom, #dateTo').on('keypress', function(e) {
        if (e.which === 13) loadAllData();
    });

    // ============================================
    // INITIAL LOAD
    // ============================================
    loadAllData();
});
</script>
@endpush
@endsection