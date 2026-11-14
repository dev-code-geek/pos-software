@extends('layouts.admin')

@section('title', __('messages.payroll'))
@section('page-title', __('messages.payroll_management'))

@section('admin-content')
<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-primary">
            <div class="inner"><h3>{{ number_format($summary['total_employees'] ?? 0) }}</h3><p>{{ __('messages.active_employees') }}</p></div>
            <div class="icon"><i class="fas fa-users"></i></div>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-success">
            <div class="inner"><h3>{{ number_format($summary['total_payroll_amount'] ?? 0, 2) }}</h3><p>{{ __('messages.total_payroll_month') }}</p></div>
            <div class="icon"><i class="fas fa-wallet"></i></div>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-danger">
            <div class="inner"><h3>{{ number_format($summary['total_tax'] ?? 0, 2) }}</h3><p>{{ __('messages.total_tax') }}</p></div>
            <div class="icon"><i class="fas fa-file-invoice"></i></div>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-warning text-dark">
            <div class="inner"><h3>{{ number_format($summary['pending_payments'] ?? 0) }}</h3><p>{{ __('messages.pending_payments') }}</p></div>
            <div class="icon"><i class="fas fa-clock"></i></div>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-info">
            <div class="inner"><h3>{{ number_format($summary['active_periods'] ?? 0) }}</h3><p>{{ __('messages.active_periods') }}</p></div>
            <div class="icon"><i class="fas fa-calendar"></i></div>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-secondary">
            <div class="inner"><h3>{{ number_format($summary['active_loans'] ?? 0) }}</h3><p>{{ __('messages.active_loans') }}</p></div>
            <div class="icon"><i class="fas fa-hand-holding-usd"></i></div>
        </div>
    </div>
</div>

<!-- Tabs Navigation -->
<ul class="nav nav-tabs" id="payrollTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link {{ $tab == 'periods' ? 'active' : '' }}" id="periods-tab" data-bs-toggle="tab" data-bs-target="#periods" type="button" role="tab">
            <i class="fas fa-calendar me-1"></i> {{ __('messages.periods') }} <span class="badge bg-secondary">{{ $periodsCount ?? 0 }}</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link {{ $tab == 'calculations' ? 'active' : '' }}" id="calculations-tab" data-bs-toggle="tab" data-bs-target="#calculations" type="button" role="tab">
            <i class="fas fa-calculator me-1"></i> {{ __('messages.calculations') }} <span class="badge bg-secondary">{{ $calculationsCount ?? 0 }}</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link {{ $tab == 'payslips' ? 'active' : '' }}" id="payslips-tab" data-bs-toggle="tab" data-bs-target="#payslips" type="button" role="tab">
            <i class="fas fa-file-invoice me-1"></i> {{ __('messages.payslips') }} <span class="badge bg-secondary">{{ $payslipsCount ?? 0 }}</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link {{ $tab == 'loans' ? 'active' : '' }}" id="loans-tab" data-bs-toggle="tab" data-bs-target="#loans" type="button" role="tab">
            <i class="fas fa-hand-holding-usd me-1"></i> {{ __('messages.loans') }} <span class="badge bg-secondary">{{ $loansCount ?? 0 }}</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link {{ $tab == 'overtime' ? 'active' : '' }}" id="overtime-tab" data-bs-toggle="tab" data-bs-target="#overtime" type="button" role="tab">
            <i class="fas fa-clock me-1"></i> {{ __('messages.overtime') }} <span class="badge bg-secondary">{{ $overtimeCount ?? 0 }}</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link {{ $tab == 'adjustments' ? 'active' : '' }}" id="adjustments-tab" data-bs-toggle="tab" data-bs-target="#adjustments" type="button" role="tab">
            <i class="fas fa-sliders-h me-1"></i> {{ __('messages.adjustments') }} <span class="badge bg-secondary">{{ $adjustmentsCount ?? 0 }}</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link {{ $tab == 'rates' ? 'active' : '' }}" id="rates-tab" data-bs-toggle="tab" data-bs-target="#rates" type="button" role="tab">
            <i class="fas fa-percent me-1"></i> {{ __('messages.rates') }} <span class="badge bg-secondary">{{ $ratesCount ?? 0 }}</span>
        </button>
    </li>
</ul>

<!-- Tab Content -->
<div class="tab-content" id="payrollTabContent">

    <!-- PERIODS TAB -->
    <div class="tab-pane fade {{ $tab == 'periods' ? 'show active' : '' }}" id="periods" role="tabpanel" aria-labelledby="periods-tab">
        <div class="card mt-3">
            <div class="card-header">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <h5 class="card-title mb-0"><i class="fas fa-calendar text-primary me-2"></i>{{ __('messages.payroll_periods') }}</h5>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <select id="periodCompanyFilter" class="form-select form-select-sm" style="width:150px;">
                        <option value="">{{ __('messages.all_companies') }}</option>
                        @foreach($companies ?? [] as $c)
                            <option value="{{ $c->id }}" {{ request('company_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                    <select id="periodStatusFilter" class="form-select form-select-sm" style="width:120px;">
                        <option value="">{{ __('messages.all_status') }}</option>
                        @foreach(['Open','Processing','Approved','Closed','Locked'] as $s)
                            <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ __('messages.period_status_' . strtolower($s)) }}</option>
                        @endforeach
                    </select>
                    @can('payroll_period.create')
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#periodModal">
                        <i class="fas fa-plus-circle me-1"></i> {{ __('messages.new_period') }}
                    </button>
                    @endcan
                    @can('payroll_period.calculate')
                    <button class="btn btn-success btn-sm" id="calculatePeriodBtn">
                        <i class="fas fa-calculator me-1"></i> {{ __('messages.calculate') }}
                    </button>
                    @endcan
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:35px;">#</th>
                                <th>{{ __('messages.code') }}</th>
                                <th>{{ __('messages.name') }}</th>
                                <th>{{ __('messages.company') }}</th>
                                <th>{{ __('messages.start') }}</th>
                                <th>{{ __('messages.end') }}</th>
                                <th>{{ __('messages.pay_date') }}</th>
                                <th>{{ __('messages.status') }}</th>
                                <th style="width:250px;">{{ __('messages.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody id="periodsTableBody">
                            @forelse($periods ?? [] as $period)
                            <tr data-id="{{ $period->id }}" data-status="{{ $period->status }}">
                                <td>{{ $loop->iteration }}</td>
                                <td><span class="badge bg-secondary">{{ $period->period_code }}</span></td>
                                <td>{{ $period->period_name }}</td>
                                <td>{{ $period->company->name ?? 'N/A' }}</td>
                                <td>{{ $period->start_date ? $period->start_date->format('M d, Y') : 'N/A' }}</td>
                                <td>{{ $period->end_date ? $period->end_date->format('M d, Y') : 'N/A' }}</td>
                                <td>{{ $period->pay_date ? $period->pay_date->format('M d, Y') : 'N/A' }}</td>
                                <td>
                                    @php
                                        $statusColors = ['Open' => 'success', 'Processing' => 'warning text-dark', 'Approved' => 'info', 'Closed' => 'secondary', 'Locked' => 'danger'];
                                    @endphp
                                    <span class="badge bg-{{ $statusColors[$period->status] ?? 'secondary' }}">
                                        {{ __('messages.period_status_' . strtolower($period->status)) }}
                                    </span>
                                    @if($period->is_locked)
                                        <span class="badge bg-danger"><i class="fas fa-lock me-1"></i>{{ __('messages.locked') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        @can('payroll_period.view')
                                        <button class="btn btn-outline-primary btn-sm view-period-btn" data-id="{{ $period->id }}" title="{{ __('messages.view') }}"><i class="fas fa-eye"></i></button>
                                        @endcan
                                        @can('payroll_period.edit')
                                        @if(!$period->is_locked && $period->status != 'Locked')
                                        <button class="btn btn-outline-warning btn-sm edit-period-btn" data-id="{{ $period->id }}" title="{{ __('messages.edit') }}"><i class="fas fa-edit"></i></button>
                                        @endif
                                        @endcan
                                        @can('payroll_period.approve')
                                        @if($period->status == 'Processing' && !$period->is_locked)
                                        <button class="btn btn-outline-info btn-sm approve-period-btn" data-id="{{ $period->id }}" title="{{ __('messages.approve') }}"><i class="fas fa-check-circle"></i></button>
                                        @endif
                                        @endcan
                                        @can('payroll_period.lock')
                                        @if($period->status == 'Approved' && !$period->is_locked)
                                        <button class="btn btn-outline-danger btn-sm lock-period-btn" data-id="{{ $period->id }}" title="{{ __('messages.lock') }}"><i class="fas fa-lock"></i></button>
                                        @endif
                                        @endcan
                                        @can('payroll_period.unlock')
                                        @if($period->is_locked)
                                        <button class="btn btn-outline-success btn-sm unlock-period-btn" data-id="{{ $period->id }}" title="{{ __('messages.unlock') }}"><i class="fas fa-unlock"></i></button>
                                        @endif
                                        @endcan
                                        @can('payroll_period.delete')
                                        @if($period->status != 'Locked' && !$period->is_locked && $period->calculations()->count() == 0)
                                        <button class="btn btn-outline-danger btn-sm delete-period-btn" data-id="{{ $period->id }}" title="{{ __('messages.delete') }}"><i class="fas fa-trash"></i></button>
                                        @endif
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="9"><div class="text-center py-5"><i class="fas fa-calendar fa-4x text-muted mb-3 d-block"></i><h5 class="fw-bold">{{ __('messages.no_payroll_periods_found') }}</h5></div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{ ($periods ?? collect())->appends(request()->query())->links() }}
            </div>
        </div>
    </div>

    <!-- CALCULATIONS TAB -->
    <div class="tab-pane fade {{ $tab == 'calculations' ? 'show active' : '' }}" id="calculations" role="tabpanel" aria-labelledby="calculations-tab">
        <div class="card mt-3">
            <div class="card-header">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <h5 class="card-title mb-0"><i class="fas fa-calculator text-primary me-2"></i>{{ __('messages.payroll_calculations') }}</h5>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <select id="calcPeriodFilter" class="form-select form-select-sm" style="width:150px;">
                        <option value="">{{ __('messages.all_periods') }}</option>
                        @foreach($periodsList ?? [] as $p)
                            <option value="{{ $p->id }}" {{ request('period_id') == $p->id ? 'selected' : '' }}>{{ $p->period_code }}</option>
                        @endforeach
                    </select>
                    <select id="calcStatusFilter" class="form-select form-select-sm" style="width:120px;">
                        <option value="">{{ __('messages.all_status') }}</option>
                        @foreach(['Pending','Verified','Paid'] as $s)
                            <option value="{{ $s }}" {{ request('payment_status') == $s ? 'selected' : '' }}>{{ __('messages.calc_status_' . strtolower($s)) }}</option>
                        @endforeach
                    </select>
                    @can('payroll_calculation.calculate')
                    <button class="btn btn-success btn-sm" id="runPayrollBtn">
                        <i class="fas fa-calculator me-1"></i> {{ __('messages.run_payroll') }}
                    </button>
                    @endcan
                    @can('payroll_calculation.create')
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#calculationModal">
                        <i class="fas fa-plus-circle me-1"></i> {{ __('messages.manual_calculation') }}
                    </button>
                    @endcan
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:35px;">#</th>
                                <th>{{ __('messages.employee') }}</th>
                                <th>{{ __('messages.period') }}</th>
                                <th class="text-end">{{ __('messages.basic') }}</th>
                                <th class="text-end">{{ __('messages.allowances') }}</th>
                                <th class="text-end">{{ __('messages.deductions') }}</th>
                                <th class="text-end">{{ __('messages.net_salary') }}</th>
                                <th>{{ __('messages.status') }}</th>
                                <th style="width:220px;">{{ __('messages.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody id="calculationsTableBody">
                            @forelse($calculations ?? [] as $calc)
                            <tr data-id="{{ $calc->id }}" data-status="{{ $calc->payment_status }}">
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle bg-primary text-white me-2" style="width:32px;height:32px;font-size:12px;">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold small">{{ $calc->employee->first_name ?? 'N/A' }} {{ $calc->employee->last_name ?? '' }}</div>
                                            <small class="text-muted">{{ $calc->employee->employee_code ?? '' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $calc->period->period_code ?? 'N/A' }}</td>
                                <td class="text-end">{{ number_format($calc->basic_salary ?? 0, 2) }}</td>
                                <td class="text-end">{{ number_format(($calc->transport_allowance ?? 0) + ($calc->housing_allowance ?? 0) + ($calc->food_allowance ?? 0) + ($calc->medical_allowance ?? 0) + ($calc->other_allowances ?? 0), 2) }}</td>
                                <td class="text-end">{{ number_format(($calc->paye_tax ?? 0) + ($calc->nssf_employee ?? 0) + ($calc->loan_deductions ?? 0) + ($calc->other_deductions ?? 0), 2) }}</td>
                                <td class="text-end"><span class="fw-bold text-primary">{{ number_format($calc->net_salary ?? 0, 2) }}</span></td>
                                <td>
                                    @php
                                        $calcColors = ['Pending' => 'warning text-dark', 'Verified' => 'info', 'Paid' => 'success'];
                                    @endphp
                                    <span class="badge bg-{{ $calcColors[$calc->payment_status] ?? 'secondary' }}">
                                        {{ __('messages.calc_status_' . strtolower($calc->payment_status)) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        @can('payroll_calculation.view')
                                        <button class="btn btn-outline-primary btn-sm view-calc-btn" data-id="{{ $calc->id }}" title="{{ __('messages.view') }}"><i class="fas fa-eye"></i></button>
                                        @endcan
                                        @can('payroll_calculation.edit')
                                        @if($calc->payment_status == 'Pending')
                                        <button class="btn btn-outline-warning btn-sm edit-calc-btn" data-id="{{ $calc->id }}" title="{{ __('messages.edit') }}"><i class="fas fa-edit"></i></button>
                                        @endif
                                        @endcan
                                        @can('payroll_calculation.verify')
                                        @if($calc->payment_status == 'Pending')
                                        <button class="btn btn-outline-info btn-sm verify-calc-btn" data-id="{{ $calc->id }}" title="{{ __('messages.verify') }}"><i class="fas fa-check-circle"></i></button>
                                        @endif
                                        @endcan
                                        @can('payroll_calculation.pay')
                                        @if($calc->payment_status == 'Verified')
                                        <button class="btn btn-outline-success btn-sm pay-calc-btn" data-id="{{ $calc->id }}" title="{{ __('messages.process_payment') }}"><i class="fas fa-money-bill-wave"></i></button>
                                        @endif
                                        @endcan
                                        @can('payroll_calculation.delete')
                                        @if($calc->payment_status == 'Pending')
                                        <button class="btn btn-outline-danger btn-sm delete-calc-btn" data-id="{{ $calc->id }}" title="{{ __('messages.delete') }}"><i class="fas fa-trash"></i></button>
                                        @endif
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="9"><div class="text-center py-5"><i class="fas fa-calculator fa-4x text-muted mb-3 d-block"></i><h5 class="fw-bold">{{ __('messages.no_calculations_found') }}</h5></div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{ ($calculations ?? collect())->appends(request()->query())->links() }}
            </div>
        </div>
    </div>

    <!-- PAYSLIPS TAB -->
    <div class="tab-pane fade {{ $tab == 'payslips' ? 'show active' : '' }}" id="payslips" role="tabpanel" aria-labelledby="payslips-tab">
        <div class="card mt-3">
            <div class="card-header">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <h5 class="card-title mb-0"><i class="fas fa-file-invoice text-primary me-2"></i>{{ __('messages.payslips') }}</h5>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <select id="payslipPeriodFilter" class="form-select form-select-sm" style="width:150px;">
                        <option value="">{{ __('messages.all_periods') }}</option>
                        @foreach($periodsList ?? [] as $p)
                            <option value="{{ $p->id }}" {{ request('period_id') == $p->id ? 'selected' : '' }}>{{ $p->period_code }}</option>
                        @endforeach
                    </select>
                    <select id="payslipEmployeeFilter" class="form-select form-select-sm" style="width:180px;">
                        <option value="">{{ __('messages.all_employees') }}</option>
                        @foreach($employees ?? [] as $e)
                            <option value="{{ $e->id }}" {{ request('employee_id') == $e->id ? 'selected' : '' }}>{{ $e->first_name }} {{ $e->last_name }}</option>
                        @endforeach
                    </select>
                    <input type="text" id="payslipSearchFilter" class="form-control form-control-sm" style="width:180px;" placeholder="{{ __('messages.search_payslip') }}" value="{{ request('search') }}">
                    @can('payslip.generate')
                    <button class="btn btn-primary btn-sm" id="generatePayslipsBtn">
                        <i class="fas fa-file-pdf me-1"></i> {{ __('messages.generate_payslips') }}
                    </button>
                    @endcan
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:35px;">#</th>
                                <th>{{ __('messages.payslip_number') }}</th>
                                <th>{{ __('messages.employee') }}</th>
                                <th>{{ __('messages.period') }}</th>
                                <th class="text-end">{{ __('messages.net_salary') }}</th>
                                <th>{{ __('messages.issue_date') }}</th>
                                <th>{{ __('messages.status') }}</th>
                                <th style="width:200px;">{{ __('messages.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody id="payslipsTableBody">
                            @forelse($payslips ?? [] as $payslip)
                            <tr data-id="{{ $payslip->id }}">
                                <td>{{ $loop->iteration }}</td>
                                <td><span class="fw-bold">{{ $payslip->payslip_number }}</span></td>
                                <td>{{ $payslip->employee->first_name ?? 'N/A' }} {{ $payslip->employee->last_name ?? '' }}</td>
                                <td>{{ $payslip->period->period_code ?? 'N/A' }}</td>
                                <td class="text-end"><span class="fw-bold text-primary">{{ number_format($payslip->payroll->net_salary ?? 0, 2) }}</span></td>
                                <td>{{ $payslip->issue_date ? $payslip->issue_date->format('M d, Y') : 'N/A' }}</td>
                                <td>
                                    @if($payslip->pdf_file_path)
                                        <span class="badge bg-success"><i class="fas fa-check me-1"></i> {{ __('messages.generated') }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ __('messages.pending') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        @can('payslip.view')
                                        @if($payslip->pdf_file_path)
                                        <a href="{{ route('admin.payroll.payslips.preview', $payslip->id) }}" class="btn btn-outline-primary btn-sm" title="{{ __('messages.preview') }}" target="_blank"><i class="fas fa-eye"></i></a>
                                        <a href="{{ route('admin.payroll.payslips.download', $payslip->id) }}" class="btn btn-outline-success btn-sm" title="{{ __('messages.download') }}"><i class="fas fa-download"></i></a>
                                        <a href="{{ route('admin.payroll.payslips.print', $payslip->id) }}" class="btn btn-outline-secondary btn-sm" title="{{ __('messages.print') }}" target="_blank"><i class="fas fa-print"></i></a>
                                        @endif
                                        @endcan
                                        @can('payslip.email')
                                        @if($payslip->pdf_file_path)
                                        <button class="btn btn-outline-info btn-sm email-payslip-btn" data-id="{{ $payslip->id }}" title="{{ __('messages.email') }}"><i class="fas fa-envelope"></i></button>
                                        @endif
                                        @endcan
                                        @can('payslip.regenerate')
                                        <button class="btn btn-outline-warning btn-sm regenerate-payslip-btn" data-id="{{ $payslip->id }}" title="{{ __('messages.regenerate') }}"><i class="fas fa-sync"></i></button>
                                        @endcan
                                        @can('payslip.delete')
                                        <button class="btn btn-outline-danger btn-sm delete-payslip-btn" data-id="{{ $payslip->id }}" title="{{ __('messages.delete') }}"><i class="fas fa-trash"></i></button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="8"><div class="text-center py-5"><i class="fas fa-file-invoice fa-4x text-muted mb-3 d-block"></i><h5 class="fw-bold">{{ __('messages.no_payslips_found') }}</h5></div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{ ($payslips ?? collect())->appends(request()->query())->links() }}
            </div>
        </div>
    </div>

    <!-- LOANS TAB -->
    <div class="tab-pane fade {{ $tab == 'loans' ? 'show active' : '' }}" id="loans" role="tabpanel" aria-labelledby="loans-tab">
        <div class="card mt-3">
            <div class="card-header">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <h5 class="card-title mb-0"><i class="fas fa-hand-holding-usd text-primary me-2"></i>{{ __('messages.employee_loans') }}</h5>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <select id="loanEmployeeFilter" class="form-select form-select-sm" style="width:180px;">
                        <option value="">{{ __('messages.all_employees') }}</option>
                        @foreach($employees ?? [] as $e)
                            <option value="{{ $e->id }}" {{ request('employee_id') == $e->id ? 'selected' : '' }}>{{ $e->first_name }} {{ $e->last_name }}</option>
                        @endforeach
                    </select>
                    <select id="loanStatusFilter" class="form-select form-select-sm" style="width:120px;">
                        <option value="">{{ __('messages.all_status') }}</option>
                        @foreach(['Active','Completed','Suspended','Rejected'] as $s)
                            <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ __('messages.loan_status_' . strtolower($s)) }}</option>
                        @endforeach
                    </select>
                    <select id="loanTypeFilter" class="form-select form-select-sm" style="width:150px;">
                        <option value="">{{ __('messages.all_types') }}</option>
                        @foreach(['Salary Advance','Personal Loan','Emergency'] as $t)
                            <option value="{{ $t }}" {{ request('loan_type') == $t ? 'selected' : '' }}>{{ __('messages.loan_type_' . strtolower(str_replace(' ', '_', $t))) }}</option>
                        @endforeach
                    </select>
                    @can('employee_loan.create')
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#loanModal">
                        <i class="fas fa-plus-circle me-1"></i> {{ __('messages.new_loan') }}
                    </button>
                    @endcan
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:35px;">#</th>
                                <th>{{ __('messages.loan_number') }}</th>
                                <th>{{ __('messages.employee') }}</th>
                                <th>{{ __('messages.type') }}</th>
                                <th class="text-end">{{ __('messages.amount') }}</th>
                                <th class="text-end">{{ __('messages.monthly') }}</th>
                                <th class="text-end">{{ __('messages.balance') }}</th>
                                <th>{{ __('messages.status') }}</th>
                                <th style="width:220px;">{{ __('messages.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody id="loansTableBody">
                            @forelse($loans ?? [] as $loan)
                            <tr data-id="{{ $loan->id }}" data-status="{{ $loan->status }}">
                                <td>{{ $loop->iteration }}</td>
                                <td><span class="fw-bold">{{ $loan->loan_number }}</span></td>
                                <td>{{ $loan->employee->first_name ?? 'N/A' }} {{ $loan->employee->last_name ?? '' }}</td>
                                <td>{{ __('messages.loan_type_' . strtolower(str_replace(' ', '_', $loan->loan_type))) }}</td>
                                <td class="text-end">{{ number_format($loan->amount, 2) }}</td>
                                <td class="text-end">{{ number_format($loan->monthly_installment, 2) }}</td>
                                <td class="text-end"><span class="fw-bold text-{{ $loan->remaining_balance > 0 ? 'danger' : 'success' }}">{{ number_format($loan->remaining_balance, 2) }}</span></td>
                                <td>
                                    @php
                                        $loanColors = ['Active' => 'success', 'Completed' => 'info', 'Suspended' => 'warning text-dark', 'Rejected' => 'danger'];
                                    @endphp
                                    <span class="badge bg-{{ $loanColors[$loan->status] ?? 'secondary' }}">
                                        {{ __('messages.loan_status_' . strtolower($loan->status)) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        @can('employee_loan.view')
                                        <button class="btn btn-outline-primary btn-sm view-loan-btn" data-id="{{ $loan->id }}" title="{{ __('messages.view') }}"><i class="fas fa-eye"></i></button>
                                        @endcan
                                        @can('employee_loan.edit')
                                        @if($loan->status == 'Active' || $loan->status == 'Suspended')
                                        <button class="btn btn-outline-warning btn-sm edit-loan-btn" data-id="{{ $loan->id }}" title="{{ __('messages.edit') }}"><i class="fas fa-edit"></i></button>
                                        @endif
                                        @endcan
                                        @can('employee_loan.approve')
                                        @if(!$loan->approved_at && $loan->status != 'Rejected')
                                        <button class="btn btn-outline-success btn-sm approve-loan-btn" data-id="{{ $loan->id }}" title="{{ __('messages.approve') }}"><i class="fas fa-check"></i></button>
                                        <button class="btn btn-outline-secondary btn-sm reject-loan-btn" data-id="{{ $loan->id }}" title="{{ __('messages.reject') }}"><i class="fas fa-times"></i></button>
                                        @endif
                                        @endcan
                                        @can('employee_loan.disburse')
                                        @if($loan->approved_at && !$loan->disbursed_at && $loan->status == 'Active')
                                        <button class="btn btn-outline-info btn-sm disburse-loan-btn" data-id="{{ $loan->id }}" title="{{ __('messages.disburse') }}"><i class="fas fa-hand-holding"></i></button>
                                        @endif
                                        @endcan
                                        @can('employee_loan.suspend')
                                        @if($loan->status == 'Active' && $loan->disbursed_at)
                                        <button class="btn btn-outline-warning btn-sm suspend-loan-btn" data-id="{{ $loan->id }}" title="{{ __('messages.suspend') }}"><i class="fas fa-pause"></i></button>
                                        @endif
                                        @endcan
                                        @can('employee_loan.resume')
                                        @if($loan->status == 'Suspended')
                                        <button class="btn btn-outline-success btn-sm resume-loan-btn" data-id="{{ $loan->id }}" title="{{ __('messages.resume') }}"><i class="fas fa-play"></i></button>
                                        @endif
                                        @endcan
                                        @can('employee_loan.complete')
                                        @if($loan->status == 'Active' && $loan->remaining_balance <= 0)
                                        <button class="btn btn-outline-info btn-sm complete-loan-btn" data-id="{{ $loan->id }}" title="{{ __('messages.complete') }}"><i class="fas fa-check-double"></i></button>
                                        @endif
                                        @endcan
                                        @can('employee_loan.delete')
                                        @if($loan->status == 'Active' && !$loan->disbursed_at)
                                        <button class="btn btn-outline-danger btn-sm delete-loan-btn" data-id="{{ $loan->id }}" title="{{ __('messages.delete') }}"><i class="fas fa-trash"></i></button>
                                        @endif
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="9"><div class="text-center py-5"><i class="fas fa-hand-holding-usd fa-4x text-muted mb-3 d-block"></i><h5 class="fw-bold">{{ __('messages.no_loans_found') }}</h5></div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{ ($loans ?? collect())->appends(request()->query())->links() }}
            </div>
        </div>
    </div>

    <!-- OVERTIME TAB -->
    <div class="tab-pane fade {{ $tab == 'overtime' ? 'show active' : '' }}" id="overtime" role="tabpanel" aria-labelledby="overtime-tab">
        <div class="card mt-3">
            <div class="card-header">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <h5 class="card-title mb-0"><i class="fas fa-clock text-primary me-2"></i>{{ __('messages.employee_overtime') }}</h5>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <select id="overtimePeriodFilter" class="form-select form-select-sm" style="width:150px;">
                        <option value="">{{ __('messages.all_periods') }}</option>
                        @foreach($periodsList ?? [] as $p)
                            <option value="{{ $p->id }}" {{ request('period_id') == $p->id ? 'selected' : '' }}>{{ $p->period_code }}</option>
                        @endforeach
                    </select>
                    <select id="overtimeEmployeeFilter" class="form-select form-select-sm" style="width:180px;">
                        <option value="">{{ __('messages.all_employees') }}</option>
                        @foreach($employees ?? [] as $e)
                            <option value="{{ $e->id }}" {{ request('employee_id') == $e->id ? 'selected' : '' }}>{{ $e->first_name }} {{ $e->last_name }}</option>
                        @endforeach
                    </select>
                    <select id="overtimeStatusFilter" class="form-select form-select-sm" style="width:120px;">
                        <option value="">{{ __('messages.all_status') }}</option>
                        @foreach(['Pending','Approved','Rejected'] as $s)
                            <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ __('messages.ot_status_' . strtolower($s)) }}</option>
                        @endforeach
                    </select>
                    @can('overtime.create')
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#overtimeModal">
                        <i class="fas fa-plus-circle me-1"></i> {{ __('messages.new_overtime') }}
                    </button>
                    @endcan
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:35px;">#</th>
                                <th>{{ __('messages.employee') }}</th>
                                <th>{{ __('messages.date') }}</th>
                                <th class="text-center">{{ __('messages.hours') }}</th>
                                <th>{{ __('messages.rate') }}</th>
                                <th class="text-end">{{ __('messages.amount') }}</th>
                                <th>{{ __('messages.status') }}</th>
                                <th style="width:220px;">{{ __('messages.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody id="overtimeTableBody">
                            @forelse($overtime ?? [] as $ot)
                            <tr data-id="{{ $ot->id }}" data-status="{{ $ot->status }}">
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $ot->employee->first_name ?? 'N/A' }} {{ $ot->employee->last_name ?? '' }}</td>
                                <td>{{ $ot->overtime_date ? $ot->overtime_date->format('M d, Y') : 'N/A' }}</td>
                                <td class="text-center"><span class="fw-bold">{{ $ot->hours_worked }}</span></td>
                                <td>{{ $ot->rate_multiplier }}x</td>
                                <td class="text-end">{{ number_format($ot->amount ?? 0, 2) }}</td>
                                <td>
                                    @php
                                        $otColors = ['Pending' => 'warning text-dark', 'Approved' => 'success', 'Rejected' => 'danger'];
                                    @endphp
                                    <span class="badge bg-{{ $otColors[$ot->status] ?? 'secondary' }}">
                                        {{ __('messages.ot_status_' . strtolower($ot->status)) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        @can('overtime.view')
                                        <button class="btn btn-outline-primary btn-sm view-overtime-btn" data-id="{{ $ot->id }}" title="{{ __('messages.view') }}"><i class="fas fa-eye"></i></button>
                                        @endcan
                                        @can('overtime.edit')
                                        @if($ot->status == 'Pending')
                                        <button class="btn btn-outline-warning btn-sm edit-overtime-btn" data-id="{{ $ot->id }}" title="{{ __('messages.edit') }}"><i class="fas fa-edit"></i></button>
                                        @endif
                                        @endcan
                                        @can('overtime.approve')
                                        @if($ot->status == 'Pending')
                                        <button class="btn btn-outline-success btn-sm approve-overtime-btn" data-id="{{ $ot->id }}" title="{{ __('messages.approve') }}"><i class="fas fa-check"></i></button>
                                        @endif
                                        @endcan
                                        @can('overtime.reject')
                                        @if($ot->status == 'Pending')
                                        <button class="btn btn-outline-danger btn-sm reject-overtime-btn" data-id="{{ $ot->id }}" title="{{ __('messages.reject') }}"><i class="fas fa-times"></i></button>
                                        @endif
                                        @endcan
                                        @can('overtime.delete')
                                        @if($ot->status == 'Pending')
                                        <button class="btn btn-outline-danger btn-sm delete-overtime-btn" data-id="{{ $ot->id }}" title="{{ __('messages.delete') }}"><i class="fas fa-trash"></i></button>
                                        @endif
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="8"><div class="text-center py-5"><i class="fas fa-clock fa-4x text-muted mb-3 d-block"></i><h5 class="fw-bold">{{ __('messages.no_overtime_records_found') }}</h5></div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{ ($overtime ?? collect())->appends(request()->query())->links() }}
            </div>
        </div>
    </div>

    <!-- ADJUSTMENTS TAB -->
    <div class="tab-pane fade {{ $tab == 'adjustments' ? 'show active' : '' }}" id="adjustments" role="tabpanel" aria-labelledby="adjustments-tab">
        <div class="card mt-3">
            <div class="card-header">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <h5 class="card-title mb-0"><i class="fas fa-sliders-h text-primary me-2"></i>{{ __('messages.payroll_adjustments') }}</h5>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <select id="adjustmentPeriodFilter" class="form-select form-select-sm" style="width:150px;">
                        <option value="">{{ __('messages.all_periods') }}</option>
                        @foreach($periodsList ?? [] as $p)
                            <option value="{{ $p->id }}" {{ request('period_id') == $p->id ? 'selected' : '' }}>{{ $p->period_code }}</option>
                        @endforeach
                    </select>
                    <select id="adjustmentEmployeeFilter" class="form-select form-select-sm" style="width:180px;">
                        <option value="">{{ __('messages.all_employees') }}</option>
                        @foreach($employees ?? [] as $e)
                            <option value="{{ $e->id }}" {{ request('employee_id') == $e->id ? 'selected' : '' }}>{{ $e->first_name }} {{ $e->last_name }}</option>
                        @endforeach
                    </select>
                    <select id="adjustmentTypeFilter" class="form-select form-select-sm" style="width:120px;">
                        <option value="">{{ __('messages.all_types') }}</option>
                        <option value="Allowance" {{ request('adjustment_type') == 'Allowance' ? 'selected' : '' }}>{{ __('messages.allowance') }}</option>
                        <option value="Deduction" {{ request('adjustment_type') == 'Deduction' ? 'selected' : '' }}>{{ __('messages.deduction') }}</option>
                    </select>
                    @can('payroll_adjustment.create')
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#adjustmentModal">
                        <i class="fas fa-plus-circle me-1"></i> {{ __('messages.new_adjustment') }}
                    </button>
                    @endcan
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:35px;">#</th>
                                <th>{{ __('messages.employee') }}</th>
                                <th>{{ __('messages.period') }}</th>
                                <th>{{ __('messages.type') }}</th>
                                <th>{{ __('messages.description') }}</th>
                                <th class="text-end">{{ __('messages.amount') }}</th>
                                <th>{{ __('messages.taxable') }}</th>
                                <th>{{ __('messages.status') }}</th>
                                <th style="width:220px;">{{ __('messages.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody id="adjustmentsTableBody">
                            @forelse($adjustments ?? [] as $adj)
                            <tr data-id="{{ $adj->id }}" data-status="{{ $adj->status }}">
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $adj->employee->first_name ?? 'N/A' }} {{ $adj->employee->last_name ?? '' }}</td>
                                <td>{{ $adj->period->period_code ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-{{ $adj->adjustment_type == 'Allowance' ? 'success' : 'danger' }}">
                                        {{ __('messages.' . strtolower($adj->adjustment_type)) }}
                                    </span>
                                </td>
                                <td>{{ Str::limit($adj->description, 30) }}</td>
                                <td class="text-end"><span class="fw-bold text-{{ $adj->adjustment_type == 'Allowance' ? 'success' : 'danger' }}">{{ number_format($adj->amount ?? 0, 2) }}</span></td>
                                <td>{{ $adj->is_taxable ? __('messages.yes') : __('messages.no') }}</td>
                                <td>
                                    @php
                                        $adjColors = ['Pending' => 'warning text-dark', 'Approved' => 'success', 'Rejected' => 'danger'];
                                    @endphp
                                    <span class="badge bg-{{ $adjColors[$adj->status] ?? 'secondary' }}">
                                        {{ __('messages.adj_status_' . strtolower($adj->status)) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        @can('payroll_adjustment.view')
                                        <button class="btn btn-outline-primary btn-sm view-adjustment-btn" data-id="{{ $adj->id }}" title="{{ __('messages.view') }}"><i class="fas fa-eye"></i></button>
                                        @endcan
                                        @can('payroll_adjustment.edit')
                                        @if($adj->status == 'Pending')
                                        <button class="btn btn-outline-warning btn-sm edit-adjustment-btn" data-id="{{ $adj->id }}" title="{{ __('messages.edit') }}"><i class="fas fa-edit"></i></button>
                                        @endif
                                        @endcan
                                        @can('payroll_adjustment.approve')
                                        @if($adj->status == 'Pending')
                                        <button class="btn btn-outline-success btn-sm approve-adjustment-btn" data-id="{{ $adj->id }}" title="{{ __('messages.approve') }}"><i class="fas fa-check"></i></button>
                                        @endif
                                        @endcan
                                        @can('payroll_adjustment.reject')
                                        @if($adj->status == 'Pending')
                                        <button class="btn btn-outline-danger btn-sm reject-adjustment-btn" data-id="{{ $adj->id }}" title="{{ __('messages.reject') }}"><i class="fas fa-times"></i></button>
                                        @endif
                                        @endcan
                                        @can('payroll_adjustment.delete')
                                        @if($adj->status == 'Pending')
                                        <button class="btn btn-outline-danger btn-sm delete-adjustment-btn" data-id="{{ $adj->id }}" title="{{ __('messages.delete') }}"><i class="fas fa-trash"></i></button>
                                        @endif
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="9"><div class="text-center py-5"><i class="fas fa-sliders-h fa-4x text-muted mb-3 d-block"></i><h5 class="fw-bold">{{ __('messages.no_adjustments_found') }}</h5></div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{ ($adjustments ?? collect())->appends(request()->query())->links() }}
            </div>
        </div>
    </div>

    <!-- RATES TAB -->
    <div class="tab-pane fade {{ $tab == 'rates' ? 'show active' : '' }}" id="rates" role="tabpanel" aria-labelledby="rates-tab">
        <div class="card mt-3">
            <div class="card-header">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <h5 class="card-title mb-0"><i class="fas fa-percent text-primary me-2"></i>{{ __('messages.payroll_rates') }}</h5>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <select id="rateTypeFilter" class="form-select form-select-sm" style="width:150px;">
                        <option value="">{{ __('messages.all_types') }}</option>
                        @foreach(['PAYE','NSSF','PPF','TRANSPORT_ALLOWANCE','HOUSING_ALLOWANCE','FOOD_ALLOWANCE','MEDICAL_ALLOWANCE'] as $rt)
                            <option value="{{ $rt }}" {{ request('rate_type') == $rt ? 'selected' : '' }}>{{ __('messages.rate_type_' . strtolower($rt)) }}</option>
                        @endforeach
                    </select>
                    <select id="rateActiveFilter" class="form-select form-select-sm" style="width:100px;">
                        <option value="">{{ __('messages.all') }}</option>
                        <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>{{ __('messages.active') }}</option>
                        <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>{{ __('messages.inactive') }}</option>
                    </select>
                    @can('payroll_rate.create')
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#rateModal">
                        <i class="fas fa-plus-circle me-1"></i> {{ __('messages.new_rate') }}
                    </button>
                    @endcan
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:35px;">#</th>
                                <th>{{ __('messages.type') }}</th>
                                <th class="text-center">{{ __('messages.rate_percent') }}</th>
                                <th>{{ __('messages.income_from') }}</th>
                                <th>{{ __('messages.income_to') }}</th>
                                <th>{{ __('messages.effective_from') }}</th>
                                <th>{{ __('messages.effective_to') }}</th>
                                <th>{{ __('messages.status') }}</th>
                                <th style="width:200px;">{{ __('messages.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody id="ratesTableBody">
                            @forelse($rates ?? [] as $rate)
                            <tr data-id="{{ $rate->id }}" data-active="{{ $rate->is_active ? '1' : '0' }}">
                                <td>{{ $loop->iteration }}</td>
                                <td><span class="badge bg-secondary">{{ __('messages.rate_type_' . strtolower($rate->rate_type)) }}</span></td>
                                <td class="text-center"><span class="fw-bold">{{ $rate->rate_value }}%</span></td>
                                <td>{{ $rate->income_from ? number_format($rate->income_from, 2) : '-' }}</td>
                                <td>{{ $rate->income_to ? number_format($rate->income_to, 2) : '-' }}</td>
                                <td>{{ $rate->effective_from ? $rate->effective_from->format('M d, Y') : 'N/A' }}</td>
                                <td>{{ $rate->effective_to ? $rate->effective_to->format('M d, Y') : __('messages.ongoing') }}</td>
                                <td>
                                    <span class="badge bg-{{ $rate->is_active ? 'success' : 'secondary' }}">
                                        {{ $rate->is_active ? __('messages.active') : __('messages.inactive') }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        @can('payroll_rate.view')
                                        <button class="btn btn-outline-primary btn-sm view-rate-btn" data-id="{{ $rate->id }}" title="{{ __('messages.view') }}"><i class="fas fa-eye"></i></button>
                                        @endcan
                                        @can('payroll_rate.edit')
                                        <button class="btn btn-outline-warning btn-sm edit-rate-btn" data-id="{{ $rate->id }}" title="{{ __('messages.edit') }}"><i class="fas fa-edit"></i></button>
                                        @endcan
                                        @can('payroll_rate.delete')
                                        <button class="btn btn-outline-danger btn-sm delete-rate-btn" data-id="{{ $rate->id }}" title="{{ __('messages.delete') }}"><i class="fas fa-trash"></i></button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="9"><div class="text-center py-5"><i class="fas fa-percent fa-4x text-muted mb-3 d-block"></i><h5 class="fw-bold">{{ __('messages.no_payroll_rates_found') }}</h5></div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{ ($rates ?? collect())->appends(request()->query())->links() }}
            </div>
        </div>
    </div>

</div>

<style>
.small-box { border-radius:12px; padding:20px; margin-bottom:0; position:relative; overflow:hidden; transition:transform 0.15s ease; }
.small-box:hover { transform:translateY(-2px); }
.small-box .inner { color:#fff; }
.small-box .inner h3 { font-size:2rem; font-weight:700; margin:0 0 5px; }
.small-box .inner p { font-size:0.9rem; opacity:0.9; margin:0; }
.small-box .icon { position:absolute; right:15px; top:15px; font-size:2.5rem; opacity:0.25; color:#fff; }
.small-box .small-box-footer { display:block; padding:8px 0 0; color:rgba(255,255,255,0.8); text-decoration:none; font-size:0.85rem; transition:all 0.15s ease; }
.small-box .small-box-footer:hover { color:#fff; }
.small-box .small-box-footer i { transition:transform 0.15s ease; }
.small-box:hover .small-box-footer i { transform:translateX(4px); }
.nav-tabs .nav-link { color: #495057; cursor: pointer; }
.nav-tabs .nav-link.active { color: #4f46e5; font-weight:600; border-bottom-color: #4f46e5; }
.nav-tabs .nav-link:hover { border-color: #e9ecef #e9ecef #dee2e6; }
.tab-content { padding-top:0; }
</style>

<!-- PERIOD MODAL -->
<div class="modal fade" id="periodModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:600px;">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white" id="periodModalTitle"><i class="fas fa-plus-circle me-2"></i> {{ __('messages.new_payroll_period') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="periodForm" action="{{ route('admin.payroll.periods.store') }}" method="POST">
                @csrf
                <input type="hidden" name="period_id" id="period_id_field">
                <div class="modal-body" style="padding:20px 25px;">
                    <div id="periodFormErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.company') }} <span class="text-danger">*</span></label>
                            <select name="company_id" class="form-select" required>
                                <option value="">{{ __('messages.select_company') }}</option>
                                @foreach($companies ?? [] as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.period_code') }} <span class="text-danger">*</span></label>
                            <input type="text" name="period_code" class="form-control" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">{{ __('messages.period_name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="period_name" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">{{ __('messages.start_date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">{{ __('messages.end_date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">{{ __('messages.pay_date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="pay_date" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.status') }} <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="Open">{{ __('messages.period_status_open') }}</option>
                                <option value="Processing">{{ __('messages.period_status_processing') }}</option>
                                <option value="Approved">{{ __('messages.period_status_approved') }}</option>
                                <option value="Closed">{{ __('messages.period_status_closed') }}</option>
                                <option value="Locked">{{ __('messages.period_status_locked') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-4">
                                <input type="checkbox" name="is_locked" class="form-check-input" id="periodIsLocked" value="1">
                                <label class="form-check-label" for="periodIsLocked">{{ __('messages.locked') }}</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">{{ __('messages.notes') }}</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> {{ __('messages.cancel') }}</button>
                    <button type="submit" class="btn btn-primary" id="periodSubmitBtn"><i class="fas fa-save me-1"></i> {{ __('messages.save_period') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- CALCULATION MODAL -->
<div class="modal fade" id="calculationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:600px;">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white" id="calculationModalTitle"><i class="fas fa-plus-circle me-2"></i> {{ __('messages.manual_calculation') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="calculationForm" action="{{ route('admin.payroll.calculations.store') }}" method="POST">
                @csrf
                <input type="hidden" name="calculation_id" id="calculation_id_field">
                <div class="modal-body" style="padding:20px 25px;">
                    <div id="calculationFormErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.employee') }} <span class="text-danger">*</span></label>
                            <select name="employee_id" id="calcEmployeeSelect" class="form-select" required>
                                <option value="">{{ __('messages.select_employee') }}</option>
                                @foreach($employees ?? [] as $e)
                                    <option value="{{ $e->id }}" data-salary="{{ $e->basic_salary }}">{{ $e->first_name }} {{ $e->last_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.period') }} <span class="text-danger">*</span></label>
                            <select name="period_id" class="form-select" required>
                                <option value="">{{ __('messages.select_period') }}</option>
                                @foreach($openPeriods ?? [] as $p)
                                    <option value="{{ $p->id }}">{{ $p->period_code }} ({{ $p->period_name }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6"><label class="form-label fw-bold">{{ __('messages.basic_salary') }} <span class="text-danger">*</span></label><input type="number" name="basic_salary" class="form-control" step="0.01" min="0" required></div>
                        <div class="col-md-6"><label class="form-label fw-bold">{{ __('messages.transport_allowance') }}</label><input type="number" name="transport_allowance" class="form-control" step="0.01" min="0" value="0"></div>
                        <div class="col-md-6"><label class="form-label fw-bold">{{ __('messages.housing_allowance') }}</label><input type="number" name="housing_allowance" class="form-control" step="0.01" min="0" value="0"></div>
                        <div class="col-md-6"><label class="form-label fw-bold">{{ __('messages.food_allowance') }}</label><input type="number" name="food_allowance" class="form-control" step="0.01" min="0" value="0"></div>
                        <div class="col-md-6"><label class="form-label fw-bold">{{ __('messages.medical_allowance') }}</label><input type="number" name="medical_allowance" class="form-control" step="0.01" min="0" value="0"></div>
                        <div class="col-md-6"><label class="form-label fw-bold">{{ __('messages.overtime_amount') }}</label><input type="number" name="overtime_amount" class="form-control" step="0.01" min="0" value="0"></div>
                        <div class="col-md-6"><label class="form-label fw-bold">{{ __('messages.paye_tax') }}</label><input type="number" name="paye_tax" class="form-control" step="0.01" min="0" value="0"></div>
                        <div class="col-md-6"><label class="form-label fw-bold">{{ __('messages.nssf_employee') }}</label><input type="number" name="nssf_employee" class="form-control" step="0.01" min="0" value="0"></div>
                        <div class="col-md-6"><label class="form-label fw-bold">{{ __('messages.loan_deductions') }}</label><input type="number" name="loan_deductions" class="form-control" step="0.01" min="0" value="0"></div>
                        <div class="col-md-6"><label class="form-label fw-bold">{{ __('messages.other_deductions') }}</label><input type="number" name="other_deductions" class="form-control" step="0.01" min="0" value="0"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> {{ __('messages.cancel') }}</button>
                    <button type="submit" class="btn btn-primary" id="calculationSubmitBtn"><i class="fas fa-save me-1"></i> {{ __('messages.save_calculation') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- LOAN MODAL -->
<div class="modal fade" id="loanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:600px;">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white" id="loanModalTitle"><i class="fas fa-plus-circle me-2"></i> {{ __('messages.new_loan') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="loanForm" action="{{ route('admin.payroll.loans.store') }}" method="POST">
                @csrf
                <input type="hidden" name="loan_id" id="loan_id_field">
                <div class="modal-body" style="padding:20px 25px;">
                    <div id="loanFormErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">{{ __('messages.employee') }} <span class="text-danger">*</span></label>
                            <select name="employee_id" class="form-select" required>
                                <option value="">{{ __('messages.select_employee') }}</option>
                                @foreach($employees ?? [] as $e)
                                    <option value="{{ $e->id }}">{{ $e->first_name }} {{ $e->last_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.loan_type') }} <span class="text-danger">*</span></label>
                            <select name="loan_type" class="form-select" required>
                                <option value="Salary Advance">{{ __('messages.loan_type_salary_advance') }}</option>
                                <option value="Personal Loan">{{ __('messages.loan_type_personal_loan') }}</option>
                                <option value="Emergency">{{ __('messages.loan_type_emergency') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6"><label class="form-label fw-bold">{{ __('messages.amount') }} <span class="text-danger">*</span></label><input type="number" name="amount" class="form-control" step="0.01" min="0" required></div>
                        <div class="col-md-6"><label class="form-label fw-bold">{{ __('messages.monthly_installment') }} <span class="text-danger">*</span></label><input type="number" name="monthly_installment" class="form-control" step="0.01" min="0" required></div>
                        <div class="col-md-6"><label class="form-label fw-bold">{{ __('messages.total_installments') }} <span class="text-danger">*</span></label><input type="number" name="total_installments" class="form-control" min="1" required></div>
                        <div class="col-md-6"><label class="form-label fw-bold">{{ __('messages.disbursement_date') }} <span class="text-danger">*</span></label><input type="date" name="disbursement_date" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label fw-bold">{{ __('messages.deduction_start_date') }} <span class="text-danger">*</span></label><input type="date" name="deduction_start_date" class="form-control" required></div>
                        <div class="col-12"><label class="form-label fw-bold">{{ __('messages.notes') }}</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> {{ __('messages.cancel') }}</button>
                    <button type="submit" class="btn btn-primary" id="loanSubmitBtn"><i class="fas fa-save me-1"></i> {{ __('messages.save_loan') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- OVERTIME MODAL -->
<div class="modal fade" id="overtimeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:600px;">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white" id="overtimeModalTitle"><i class="fas fa-plus-circle me-2"></i> {{ __('messages.new_overtime') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="overtimeForm" action="{{ route('admin.payroll.overtime.store') }}" method="POST">
                @csrf
                <input type="hidden" name="overtime_id" id="overtime_id_field">
                <div class="modal-body" style="padding:20px 25px;">
                    <div id="overtimeFormErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.employee') }} <span class="text-danger">*</span></label>
                            <select name="employee_id" class="form-select" required>
                                <option value="">{{ __('messages.select_employee') }}</option>
                                @foreach($employees ?? [] as $e)
                                    <option value="{{ $e->id }}">{{ $e->first_name }} {{ $e->last_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.period') }} <span class="text-danger">*</span></label>
                            <select name="period_id" class="form-select" required>
                                <option value="">{{ __('messages.select_period') }}</option>
                                @foreach($periodsList ?? [] as $p)
                                    <option value="{{ $p->id }}">{{ $p->period_code }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6"><label class="form-label fw-bold">{{ __('messages.overtime_date') }} <span class="text-danger">*</span></label><input type="date" name="overtime_date" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label fw-bold">{{ __('messages.hours_worked') }} <span class="text-danger">*</span></label><input type="number" name="hours_worked" class="form-control" step="0.5" min="0.5" required></div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.rate_multiplier') }} <span class="text-danger">*</span></label>
                            <select name="rate_multiplier" class="form-select" required>
                                <option value="1.5">1.5x ({{ __('messages.normal_overtime') }})</option>
                                <option value="2.0">2.0x ({{ __('messages.holiday_overtime') }})</option>
                                <option value="2.5">2.5x ({{ __('messages.special_overtime') }})</option>
                            </select>
                        </div>
                        <div class="col-12"><label class="form-label fw-bold">{{ __('messages.notes') }}</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> {{ __('messages.cancel') }}</button>
                    <button type="submit" class="btn btn-primary" id="overtimeSubmitBtn"><i class="fas fa-save me-1"></i> {{ __('messages.save_overtime') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ADJUSTMENT MODAL -->
<div class="modal fade" id="adjustmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:600px;">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white" id="adjustmentModalTitle"><i class="fas fa-plus-circle me-2"></i> {{ __('messages.new_adjustment') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="adjustmentForm" action="{{ route('admin.payroll.adjustments.store') }}" method="POST">
                @csrf
                <input type="hidden" name="adjustment_id" id="adjustment_id_field">
                <div class="modal-body" style="padding:20px 25px;">
                    <div id="adjustmentFormErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.employee') }} <span class="text-danger">*</span></label>
                            <select name="employee_id" class="form-select" required>
                                <option value="">{{ __('messages.select_employee') }}</option>
                                @foreach($employees ?? [] as $e)
                                    <option value="{{ $e->id }}">{{ $e->first_name }} {{ $e->last_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.period') }} <span class="text-danger">*</span></label>
                            <select name="period_id" class="form-select" required>
                                <option value="">{{ __('messages.select_period') }}</option>
                                @foreach($periodsList ?? [] as $p)
                                    <option value="{{ $p->id }}">{{ $p->period_code }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.adjustment_type') }} <span class="text-danger">*</span></label>
                            <select name="adjustment_type" class="form-select" required>
                                <option value="Allowance">{{ __('messages.allowance') }}</option>
                                <option value="Deduction">{{ __('messages.deduction') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6"><label class="form-label fw-bold">{{ __('messages.amount') }} <span class="text-danger">*</span></label><input type="number" name="amount" class="form-control" step="0.01" min="0" required></div>
                        <div class="col-md-6"><label class="form-label fw-bold">{{ __('messages.description') }} <span class="text-danger">*</span></label><input type="text" name="description" class="form-control" required></div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-4">
                                <input type="checkbox" name="is_taxable" class="form-check-input" id="adjustmentIsTaxable" value="1" checked>
                                <label class="form-check-label" for="adjustmentIsTaxable">{{ __('messages.taxable') }}</label>
                            </div>
                        </div>
                        <div class="col-12"><label class="form-label fw-bold">{{ __('messages.notes') }}</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> {{ __('messages.cancel') }}</button>
                    <button type="submit" class="btn btn-primary" id="adjustmentSubmitBtn"><i class="fas fa-save me-1"></i> {{ __('messages.save_adjustment') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- RATE MODAL -->
<div class="modal fade" id="rateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:600px;">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white" id="rateModalTitle"><i class="fas fa-plus-circle me-2"></i> {{ __('messages.new_rate') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="rateForm" action="{{ route('admin.payroll.rates.store') }}" method="POST" data-create-action="{{ route('admin.payroll.rates.store') }}">
                @csrf
                <div class="modal-body" style="padding:20px 25px;">
                    <div id="rateFormErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.rate_type') }} <span class="text-danger">*</span></label>
                            <select name="rate_type" class="form-select" required>
                                <option value="PAYE">{{ __('messages.rate_type_paye') }}</option>
                                <option value="NSSF">{{ __('messages.rate_type_nssf') }}</option>
                                <option value="PPF">{{ __('messages.rate_type_ppf') }}</option>
                                <option value="TRANSPORT_ALLOWANCE">{{ __('messages.rate_type_transport_allowance') }}</option>
                                <option value="HOUSING_ALLOWANCE">{{ __('messages.rate_type_housing_allowance') }}</option>
                                <option value="FOOD_ALLOWANCE">{{ __('messages.rate_type_food_allowance') }}</option>
                                <option value="MEDICAL_ALLOWANCE">{{ __('messages.rate_type_medical_allowance') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6"><label class="form-label fw-bold">{{ __('messages.rate_value') }} <span class="text-danger">*</span></label><input type="number" name="rate_value" class="form-control" step="0.01" min="0" max="100" required></div>
                        <div class="col-md-6"><label class="form-label fw-bold">{{ __('messages.income_from') }}</label><input type="number" name="income_from" class="form-control" step="0.01" min="0"></div>
                        <div class="col-md-6"><label class="form-label fw-bold">{{ __('messages.income_to') }}</label><input type="number" name="income_to" class="form-control" step="0.01" min="0"></div>
                        <div class="col-md-6"><label class="form-label fw-bold">{{ __('messages.effective_from') }} <span class="text-danger">*</span></label><input type="date" name="effective_from" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label fw-bold">{{ __('messages.effective_to') }}</label><input type="date" name="effective_to" class="form-control"></div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-4">
                                <input type="checkbox" name="is_active" class="form-check-input" id="rateIsActive" value="1" checked>
                                <label class="form-check-label" for="rateIsActive">{{ __('messages.active') }}</label>
                            </div>
                        </div>
                        <div class="col-12"><label class="form-label fw-bold">{{ __('messages.description') }}</label><textarea name="description" class="form-control" rows="2"></textarea></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> {{ __('messages.cancel') }}</button>
                    <button type="submit" class="btn btn-primary" id="rateSubmitBtn"><i class="fas fa-save me-1"></i> {{ __('messages.save_rate') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
window.trans = {
    loading: @json(__('messages.loading')),
    close: @json(__('messages.close')),
    cancel: @json(__('messages.cancel')),
    yes: @json(__('messages.yes')),
    no: @json(__('messages.no')),
    done: @json(__('messages.done')),
    error_title: @json(__('messages.error')),
    action_failed: @json(__('messages.action_failed')),
    could_not_load_record: @json(__('messages.could_not_load_record')),
    proceed: @json(__('messages.proceed')),
    delete_q: @json(__('messages.delete_q')),
    delete_warning: @json(__('messages.delete_warning')),
    saving: @json(__('messages.saving')),
    // Filter labels
    select_company: @json(__('messages.select_company')),
    select_period: @json(__('messages.select_period')),
    company: @json(__('messages.company')),
    period: @json(__('messages.period')),
    // Run calculations
    calculate_payroll_title: @json(__('messages.calculate_payroll_title')),
    run_payroll_title: @json(__('messages.run_payroll_title')),
    force_recalculate: @json(__('messages.force_recalculate')),
    generate_payslips_title: @json(__('messages.generate_payslips_title')),
    calculate: @json(__('messages.calculate')),
    run: @json(__('messages.run')),
    generate: @json(__('messages.generate')),
    please_select_company_period: @json(__('messages.please_select_company_period')),
    please_select_period: @json(__('messages.please_select_period')),
    confirm: @json(__('messages.confirm')),
    record_details: @json(__('messages.record_details')),
};
$(document).ready(function() {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
    var BASE = "{{ url('admin/payroll') }}";

    // ---- Helpers ----
    function showErrors(container, errors) {
        container.removeClass('d-none');
        var html = '<ul class="mb-0">';
        $.each(errors, function(key, messages) { $.each(messages, function(i, msg) { html += '<li>' + msg + '</li>'; }); });
        html += '</ul>';
        container.html(html);
        container[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    function clearErrors(container) { container.addClass('d-none').empty(); }

    function postAction(url, confirmTitle, confirmText, shouldReload) {
        shouldReload = (shouldReload === undefined) ? true : shouldReload;
        Swal.fire({
            title: confirmTitle, text: confirmText, icon: 'warning',
            showCancelButton: true, confirmButtonText: trans.proceed, cancelButtonText: trans.cancel
        }).then(function(result) {
            if (!result.isConfirmed) return;
            $.ajax({
                url: url, type: 'POST', dataType: 'json',
                success: function(r) {
                    if (r.success) {
                        Swal.fire({ icon: 'success', title: trans.done, text: r.message, timer: 1800 })
                            .then(function() { if (shouldReload) location.reload(); });
                    } else {
                        Swal.fire({ icon: 'error', title: trans.error_title, text: r.message || trans.action_failed });
                    }
                },
                error: function(xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : trans.action_failed;
                    Swal.fire({ icon: 'error', title: trans.error_title, text: msg });
                }
            });
        });
    }
    function deleteAction(url) { postAction(url, trans.delete_q, trans.delete_warning, true); }
    function viewRecord(url, title) {
        $.ajax({
            url: url, type: 'GET', dataType: 'json',
            success: function(r) {
                if (!r.success) { Swal.fire({ icon: 'error', title: trans.error_title, text: r.message }); return; }
                var entries = Object.entries(r.data).filter(function(pair) { return typeof pair[1] !== 'object' || pair[1] === null; });
                var html = '<table class="table table-sm text-start"><tbody>' +
                    entries.map(function(pair) { return '<tr><th>' + pair[0] + '</th><td>' + (pair[1] === null ? '-' : pair[1]) + '</td></tr>'; }).join('') +
                    '</tbody></table>';
                Swal.fire({ title: title, html: html, width: 500 });
            },
            error: function() { Swal.fire({ icon: 'error', title: trans.error_title, text: trans.could_not_load_record }); }
        });
    }
    function dateOnly(v) { return v ? String(v).split('T')[0] : ''; }

    // ---- Tab persistence ----
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
        var target = $(e.target).data('bs-target');
        var tabId = target.replace('#', '');
        var url = new URL(window.location.href);
        url.searchParams.set('tab', tabId);
        window.history.replaceState({}, '', url);
    });
    var urlParams = new URLSearchParams(window.location.search);
    var activeTab = urlParams.get('tab') || 'periods';
    var tabButton = $('button[data-bs-target="#' + activeTab + '"]');
    if (tabButton.length) tabButton.tab('show');

    // ---- Filters ----
    function applyFilters(tabId, paramMap) {
        var url = new URL(window.location.href);
        Object.keys(paramMap).forEach(function(param) { url.searchParams.delete(param); });
        url.searchParams.set('tab', tabId);
        url.searchParams.delete('page');
        Object.keys(paramMap).forEach(function(param) {
            var val = $(paramMap[param]).val();
            if (val !== null && val !== undefined && val !== '') url.searchParams.set(param, val);
        });
        window.location.href = url.toString();
    }
    $('#periodCompanyFilter, #periodStatusFilter').on('change', function() { applyFilters('periods', { company_id: '#periodCompanyFilter', status: '#periodStatusFilter' }); });
    $('#calcPeriodFilter, #calcStatusFilter').on('change', function() { applyFilters('calculations', { period_id: '#calcPeriodFilter', payment_status: '#calcStatusFilter' }); });
    $('#payslipPeriodFilter, #payslipEmployeeFilter').on('change', function() { applyFilters('payslips', { period_id: '#payslipPeriodFilter', employee_id: '#payslipEmployeeFilter', search: '#payslipSearchFilter' }); });
    $('#payslipSearchFilter').on('keypress', function(e) { if (e.which === 13) applyFilters('payslips', { period_id: '#payslipPeriodFilter', employee_id: '#payslipEmployeeFilter', search: '#payslipSearchFilter' }); });
    $('#loanEmployeeFilter, #loanStatusFilter, #loanTypeFilter').on('change', function() { applyFilters('loans', { employee_id: '#loanEmployeeFilter', status: '#loanStatusFilter', loan_type: '#loanTypeFilter' }); });
    $('#overtimePeriodFilter, #overtimeEmployeeFilter, #overtimeStatusFilter').on('change', function() { applyFilters('overtime', { period_id: '#overtimePeriodFilter', employee_id: '#overtimeEmployeeFilter', status: '#overtimeStatusFilter' }); });
    $('#adjustmentPeriodFilter, #adjustmentEmployeeFilter, #adjustmentTypeFilter').on('change', function() { applyFilters('adjustments', { period_id: '#adjustmentPeriodFilter', employee_id: '#adjustmentEmployeeFilter', adjustment_type: '#adjustmentTypeFilter' }); });
    $('#rateTypeFilter, #rateActiveFilter').on('change', function() { applyFilters('rates', { rate_type: '#rateTypeFilter', is_active: '#rateActiveFilter' }); });

    // ---- Period form ----
    @can('payroll_period.create')
    $('#periodForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this); var btn = $('#periodSubmitBtn'); var errorContainer = $('#periodFormErrors');
        clearErrors(errorContainer);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> ' + trans.saving + '...');
        $.ajax({
            url: form.attr('action'), type: 'POST', data: form.serialize(), dataType: 'json',
            success: function(r) {
                if (r.success) { $('#periodModal').modal('hide'); Swal.fire({ icon: 'success', title: trans.done, text: r.message, timer: 2000 }).then(() => location.reload()); }
                else { btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> {{ __('messages.save_period') }}'); if (r.errors) showErrors(errorContainer, r.errors); else Swal.fire({ icon: 'error', title: trans.error_title, text: r.message || trans.action_failed }); }
            },
            error: function(xhr) { btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> {{ __('messages.save_period') }}'); if (xhr.status === 422) showErrors(errorContainer, xhr.responseJSON.errors); else Swal.fire({ icon: 'error', title: trans.error_title, text: trans.action_failed }); }
        });
    });
    @endcan
    @can('payroll_period.view') $(document).on('click', '.view-period-btn', function() { viewRecord(BASE + '/periods/' + $(this).data('id') + '/edit', '{{ __('messages.payroll_period') }}'); }); @endcan
    @can('payroll_period.edit')
    $(document).on('click', '.edit-period-btn', function() {
        var id = $(this).data('id');
        $.getJSON(BASE + '/periods/' + id + '/edit', function(r) {
            if (!r.success) return;
            var p = r.data; var f = $('#periodForm');
            f.find('[name=company_id]').val(p.company_id);
            f.find('[name=period_code]').val(p.period_code);
            f.find('[name=period_name]').val(p.period_name);
            f.find('[name=start_date]').val(dateOnly(p.start_date));
            f.find('[name=end_date]').val(dateOnly(p.end_date));
            f.find('[name=pay_date]').val(dateOnly(p.pay_date));
            f.find('[name=status]').val(p.status);
            f.find('[name=is_locked]').prop('checked', !!p.is_locked);
            f.find('[name=notes]').val(p.notes);
            $('#period_id_field').val(id);
            $('#periodModalTitle').html('<i class="fas fa-edit me-2"></i> {{ __('messages.edit_payroll_period') }}');
            $('#periodModal').modal('show');
        });
    });
    @endcan
    @can('payroll_period.approve') $(document).on('click', '.approve-period-btn', function() { postAction(BASE + '/periods/' + $(this).data('id') + '/approve', '{{ __('messages.confirm') }}', '{{ __('messages.approve_period_warning') }}'); }); @endcan
    @can('payroll_period.lock')    $(document).on('click', '.lock-period-btn',    function() { postAction(BASE + '/periods/' + $(this).data('id') + '/lock',    '{{ __('messages.confirm') }}', '{{ __('messages.lock_period_warning') }}'); }); @endcan
    @can('payroll_period.unlock')  $(document).on('click', '.unlock-period-btn',  function() { postAction(BASE + '/periods/' + $(this).data('id') + '/unlock',  '{{ __('messages.confirm') }}', '{{ __('messages.unlock_period_warning') }}'); }); @endcan
    @can('payroll_period.delete')  $(document).on('click', '.delete-period-btn',  function() { deleteAction(BASE + '/periods/' + $(this).data('id')); }); @endcan

    @can('payroll_period.calculate')
    $('#calculatePeriodBtn').on('click', function() {
        Swal.fire({
            title: trans.calculate_payroll_title,
            html:
                '<label class="form-label text-start d-block">' + trans.company + '</label>' +
                '<select id="swalCompany" class="swal2-select w-100">' + $('#periodCompanyFilter').html() + '</select>' +
                '<label class="form-label text-start d-block mt-2">' + trans.period + '</label>' +
                '<select id="swalPeriod" class="swal2-select w-100">' + $('#calcPeriodFilter').html() + '</select>',
            confirmButtonText: trans.calculate, showCancelButton: true, cancelButtonText: trans.cancel,
            preConfirm: function() {
                var companyId = $('#swalCompany').val(); var periodId = $('#swalPeriod').val();
                if (!companyId || !periodId) { Swal.showValidationMessage(trans.please_select_company_period); return false; }
                return { company_id: companyId, period_id: periodId };
            }
        }).then(function(result) {
            if (!result.isConfirmed) return;
            $.post(BASE + '/periods/calculate', result.value, function(r) {
                Swal.fire({ icon: r.success ? 'success' : 'error', title: r.success ? trans.done : trans.error_title, text: r.message }).then(function() { if (r.success) location.reload(); });
            }, 'json').fail(function(xhr) { Swal.fire({ icon: 'error', title: trans.error_title, text: (xhr.responseJSON && xhr.responseJSON.message) || trans.action_failed }); });
        });
    });
    @endcan

    // ---- Calculation ----
    $('#calcEmployeeSelect').on('change', function() {
        var salary = $(this).find(':selected').data('salary');
        if (salary && !$('#calculationForm [name=basic_salary]').val()) $('#calculationForm [name=basic_salary]').val(salary);
    });
    @can('payroll_calculation.create')
    $('#calculationForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this); var btn = $('#calculationSubmitBtn'); var errorContainer = $('#calculationFormErrors');
        clearErrors(errorContainer);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> ' + trans.saving + '...');
        $.ajax({
            url: form.attr('action'), type: 'POST', data: form.serialize(), dataType: 'json',
            success: function(r) {
                if (r.success) { $('#calculationModal').modal('hide'); Swal.fire({ icon: 'success', title: trans.done, text: r.message, timer: 2000 }).then(() => location.reload()); }
                else { btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> {{ __('messages.save_calculation') }}'); if (r.errors) showErrors(errorContainer, r.errors); else Swal.fire({ icon: 'error', title: trans.error_title, text: r.message || trans.action_failed }); }
            },
            error: function(xhr) { btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> {{ __('messages.save_calculation') }}'); if (xhr.status === 422) showErrors(errorContainer, xhr.responseJSON.errors); else Swal.fire({ icon: 'error', title: trans.error_title, text: trans.action_failed }); }
        });
    });
    @endcan
    @can('payroll_calculation.view') $(document).on('click', '.view-calc-btn', function() { viewRecord(BASE + '/calculations/' + $(this).data('id'), '{{ __('messages.payroll_calculation') }}'); }); @endcan
    @can('payroll_calculation.edit')
    $(document).on('click', '.edit-calc-btn', function() {
        var id = $(this).data('id');
        $.getJSON(BASE + '/calculations/' + id + '/edit', function(r) {
            if (!r.success) return;
            var c = r.data; var f = $('#calculationForm');
            ['employee_id','period_id','basic_salary','transport_allowance','housing_allowance','food_allowance','medical_allowance','overtime_amount','paye_tax','nssf_employee','loan_deductions','other_deductions'].forEach(function(k) { f.find('[name=' + k + ']').val(c[k]); });
            $('#calculation_id_field').val(id);
            $('#calculationModalTitle').html('<i class="fas fa-edit me-2"></i> {{ __('messages.edit_calculation') }}');
            $('#calculationModal').modal('show');
        });
    });
    @endcan
    @can('payroll_calculation.verify') $(document).on('click', '.verify-calc-btn', function() { postAction(BASE + '/calculations/' + $(this).data('id') + '/verify', '{{ __('messages.confirm') }}', '{{ __('messages.verify_calc_warning') }}'); }); @endcan
    @can('payroll_calculation.pay')    $(document).on('click', '.pay-calc-btn',    function() { postAction(BASE + '/calculations/' + $(this).data('id') + '/pay',    '{{ __('messages.confirm') }}', '{{ __('messages.pay_calc_warning') }}'); }); @endcan
    @can('payroll_calculation.delete') $(document).on('click', '.delete-calc-btn', function() { deleteAction(BASE + '/calculations/' + $(this).data('id')); }); @endcan
    @can('payroll_calculation.calculate')
    $('#runPayrollBtn').on('click', function() {
        Swal.fire({
            title: trans.run_payroll_title,
            html:
                '<label class="form-label text-start d-block">' + trans.company + '</label>' +
                '<select id="swalCompany2" class="swal2-select w-100">' + $('#periodCompanyFilter').html() + '</select>' +
                '<label class="form-label text-start d-block mt-2">' + trans.period + '</label>' +
                '<select id="swalPeriod2" class="swal2-select w-100">' + $('#calcPeriodFilter').html() + '</select>' +
                '<label class="d-block mt-3 text-start"><input type="checkbox" id="swalForce"> ' + trans.force_recalculate + '</label>',
            confirmButtonText: trans.run, showCancelButton: true, cancelButtonText: trans.cancel,
            preConfirm: function() {
                var companyId = $('#swalCompany2').val(); var periodId = $('#swalPeriod2').val();
                if (!companyId || !periodId) { Swal.showValidationMessage(trans.please_select_company_period); return false; }
                return { company_id: companyId, period_id: periodId, force: $('#swalForce').is(':checked') ? 1 : 0 };
            }
        }).then(function(result) {
            if (!result.isConfirmed) return;
            $.post(BASE + '/calculations/calculate', result.value, function(r) {
                var extra = (r.errors && r.errors.length) ? ('<br><small class="text-danger">' + r.errors.join('<br>') + '</small>') : '';
                Swal.fire({ icon: r.success ? 'success' : 'error', title: r.success ? trans.done : trans.error_title, html: (r.message || '') + extra }).then(function() { if (r.success) location.reload(); });
            }, 'json').fail(function(xhr) { Swal.fire({ icon: 'error', title: trans.error_title, text: (xhr.responseJSON && xhr.responseJSON.message) || trans.action_failed }); });
        });
    });
    @endcan

    // ---- Payslips ----
    @can('payslip.generate')
    $('#generatePayslipsBtn').on('click', function() {
        Swal.fire({
            title: trans.generate_payslips_title,
            html: '<select id="swalPayslipPeriod" class="swal2-select w-100">' + $('#payslipPeriodFilter').html() + '</select>',
            confirmButtonText: trans.generate, showCancelButton: true, cancelButtonText: trans.cancel,
            preConfirm: function() {
                var periodId = $('#swalPayslipPeriod').val();
                if (!periodId) { Swal.showValidationMessage(trans.please_select_period); return false; }
                return { period_id: periodId };
            }
        }).then(function(result) {
            if (!result.isConfirmed) return;
            $.post(BASE + '/payslips/generate', result.value, function(r) {
                var extra = (r.errors && r.errors.length) ? ('<br><small class="text-danger">' + r.errors.join('<br>') + '</small>') : '';
                Swal.fire({ icon: r.success ? 'success' : 'error', title: r.success ? trans.done : trans.error_title, html: (r.message || '') + extra }).then(function() { if (r.success) location.reload(); });
            }, 'json').fail(function(xhr) { Swal.fire({ icon: 'error', title: trans.error_title, text: (xhr.responseJSON && xhr.responseJSON.message) || trans.action_failed }); });
        });
    });
    @endcan
    @can('payslip.email')      $(document).on('click', '.email-payslip-btn',      function() { postAction(BASE + '/payslips/' + $(this).data('id') + '/email', '{{ __('messages.confirm') }}', '{{ __('messages.email_payslip_warning') }}', false); }); @endcan
    @can('payslip.regenerate') $(document).on('click', '.regenerate-payslip-btn', function() { postAction(BASE + '/payslips/' + $(this).data('id') + '/regenerate', '{{ __('messages.confirm') }}', '{{ __('messages.regenerate_payslip_warning') }}'); }); @endcan
    @can('payslip.delete')     $(document).on('click', '.delete-payslip-btn',     function() { deleteAction(BASE + '/payslips/' + $(this).data('id')); }); @endcan

    // ---- Loans ----
    @can('employee_loan.create')
    $('#loanForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this); var btn = $('#loanSubmitBtn'); var errorContainer = $('#loanFormErrors');
        clearErrors(errorContainer);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> ' + trans.saving + '...');
        $.ajax({ url: form.attr('action'), type: 'POST', data: form.serialize(), dataType: 'json',
            success: function(r) {
                if (r.success) { $('#loanModal').modal('hide'); Swal.fire({ icon: 'success', title: trans.done, text: r.message, timer: 2000 }).then(() => location.reload()); }
                else { btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> {{ __('messages.save_loan') }}'); if (r.errors) showErrors(errorContainer, r.errors); else Swal.fire({ icon: 'error', title: trans.error_title, text: r.message || trans.action_failed }); }
            },
            error: function(xhr) { btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> {{ __('messages.save_loan') }}'); if (xhr.status === 422) showErrors(errorContainer, xhr.responseJSON.errors); else Swal.fire({ icon: 'error', title: trans.error_title, text: trans.action_failed }); }
        });
    });
    @endcan
    @can('employee_loan.view') $(document).on('click', '.view-loan-btn', function() { viewRecord(BASE + '/loans/' + $(this).data('id') + '/view', '{{ __('messages.employee_loan') }}'); }); @endcan
    @can('employee_loan.edit')
    $(document).on('click', '.edit-loan-btn', function() {
        var id = $(this).data('id');
        $.getJSON(BASE + '/loans/' + id + '/edit', function(r) {
            if (!r.success) return;
            var l = r.data; var f = $('#loanForm');
            f.find('[name=employee_id]').val(l.employee_id);
            f.find('[name=loan_type]').val(l.loan_type);
            f.find('[name=amount]').val(l.amount);
            f.find('[name=monthly_installment]').val(l.monthly_installment);
            f.find('[name=total_installments]').val(l.total_installments);
            f.find('[name=disbursement_date]').val(dateOnly(l.disbursement_date));
            f.find('[name=deduction_start_date]').val(dateOnly(l.deduction_start_date));
            f.find('[name=notes]').val(l.notes);
            $('#loan_id_field').val(id);
            $('#loanModalTitle').html('<i class="fas fa-edit me-2"></i> {{ __('messages.edit_loan') }}');
            $('#loanModal').modal('show');
        });
    });
    @endcan
    @can('employee_loan.approve')
        $(document).on('click', '.approve-loan-btn', function() { postAction(BASE + '/loans/' + $(this).data('id') + '/approve', '{{ __('messages.confirm') }}', ''); });
        $(document).on('click', '.reject-loan-btn',  function() { postAction(BASE + '/loans/' + $(this).data('id') + '/reject',  '{{ __('messages.confirm') }}', '{{ __('messages.reject_loan_warning') }}'); });
    @endcan
    @can('employee_loan.disburse') $(document).on('click', '.disburse-loan-btn', function() { postAction(BASE + '/loans/' + $(this).data('id') + '/disburse', '{{ __('messages.confirm') }}', '{{ __('messages.disburse_loan_warning') }}'); }); @endcan
    @can('employee_loan.suspend')  $(document).on('click', '.suspend-loan-btn',  function() { postAction(BASE + '/loans/' + $(this).data('id') + '/suspend',  '{{ __('messages.confirm') }}', '{{ __('messages.suspend_loan_warning') }}'); }); @endcan
    @can('employee_loan.resume')   $(document).on('click', '.resume-loan-btn',   function() { postAction(BASE + '/loans/' + $(this).data('id') + '/resume',   '{{ __('messages.confirm') }}', ''); }); @endcan
    @can('employee_loan.complete') $(document).on('click', '.complete-loan-btn', function() { postAction(BASE + '/loans/' + $(this).data('id') + '/complete', '{{ __('messages.confirm') }}', ''); }); @endcan
    @can('employee_loan.delete')   $(document).on('click', '.delete-loan-btn',   function() { deleteAction(BASE + '/loans/' + $(this).data('id')); }); @endcan

    // ---- Overtime ----
    @can('overtime.create')
    $('#overtimeForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this); var btn = $('#overtimeSubmitBtn'); var errorContainer = $('#overtimeFormErrors');
        clearErrors(errorContainer);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> ' + trans.saving + '...');
        $.ajax({ url: form.attr('action'), type: 'POST', data: form.serialize(), dataType: 'json',
            success: function(r) {
                if (r.success) { $('#overtimeModal').modal('hide'); Swal.fire({ icon: 'success', title: trans.done, text: r.message, timer: 2000 }).then(() => location.reload()); }
                else { btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> {{ __('messages.save_overtime') }}'); if (r.errors) showErrors(errorContainer, r.errors); else Swal.fire({ icon: 'error', title: trans.error_title, text: r.message || trans.action_failed }); }
            },
            error: function(xhr) { btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> {{ __('messages.save_overtime') }}'); if (xhr.status === 422) showErrors(errorContainer, xhr.responseJSON.errors); else Swal.fire({ icon: 'error', title: trans.error_title, text: trans.action_failed }); }
        });
    });
    @endcan
    @can('overtime.view') $(document).on('click', '.view-overtime-btn', function() { viewRecord(BASE + '/overtime/' + $(this).data('id') + '/edit', '{{ __('messages.overtime_record') }}'); }); @endcan
    @can('overtime.edit')
    $(document).on('click', '.edit-overtime-btn', function() {
        var id = $(this).data('id');
        $.getJSON(BASE + '/overtime/' + id + '/edit', function(r) {
            if (!r.success) return;
            var o = r.data; var f = $('#overtimeForm');
            f.find('[name=employee_id]').val(o.employee_id);
            f.find('[name=period_id]').val(o.period_id);
            f.find('[name=overtime_date]').val(dateOnly(o.overtime_date));
            f.find('[name=hours_worked]').val(o.hours_worked);
            f.find('[name=rate_multiplier]').val(o.rate_multiplier);
            f.find('[name=notes]').val(o.notes);
            $('#overtime_id_field').val(id);
            $('#overtimeModalTitle').html('<i class="fas fa-edit me-2"></i> {{ __('messages.edit_overtime') }}');
            $('#overtimeModal').modal('show');
        });
    });
    @endcan
    @can('overtime.approve') $(document).on('click', '.approve-overtime-btn', function() { postAction(BASE + '/overtime/' + $(this).data('id') + '/approve', '{{ __('messages.confirm') }}', ''); }); @endcan
    @can('overtime.reject')  $(document).on('click', '.reject-overtime-btn',  function() { postAction(BASE + '/overtime/' + $(this).data('id') + '/reject',  '{{ __('messages.confirm') }}', ''); }); @endcan
    @can('overtime.delete')  $(document).on('click', '.delete-overtime-btn',  function() { deleteAction(BASE + '/overtime/' + $(this).data('id')); }); @endcan

    // ---- Adjustments ----
    @can('payroll_adjustment.create')
    $('#adjustmentForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this); var btn = $('#adjustmentSubmitBtn'); var errorContainer = $('#adjustmentFormErrors');
        clearErrors(errorContainer);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> ' + trans.saving + '...');
        $.ajax({ url: form.attr('action'), type: 'POST', data: form.serialize(), dataType: 'json',
            success: function(r) {
                if (r.success) { $('#adjustmentModal').modal('hide'); Swal.fire({ icon: 'success', title: trans.done, text: r.message, timer: 2000 }).then(() => location.reload()); }
                else { btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> {{ __('messages.save_adjustment') }}'); if (r.errors) showErrors(errorContainer, r.errors); else Swal.fire({ icon: 'error', title: trans.error_title, text: r.message || trans.action_failed }); }
            },
            error: function(xhr) { btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> {{ __('messages.save_adjustment') }}'); if (xhr.status === 422) showErrors(errorContainer, xhr.responseJSON.errors); else Swal.fire({ icon: 'error', title: trans.error_title, text: trans.action_failed }); }
        });
    });
    @endcan
    @can('payroll_adjustment.view') $(document).on('click', '.view-adjustment-btn', function() { viewRecord(BASE + '/adjustments/' + $(this).data('id') + '/edit', '{{ __('messages.payroll_adjustment') }}'); }); @endcan
    @can('payroll_adjustment.edit')
    $(document).on('click', '.edit-adjustment-btn', function() {
        var id = $(this).data('id');
        $.getJSON(BASE + '/adjustments/' + id + '/edit', function(r) {
            if (!r.success) return;
            var a = r.data; var f = $('#adjustmentForm');
            f.find('[name=employee_id]').val(a.employee_id);
            f.find('[name=period_id]').val(a.period_id);
            f.find('[name=adjustment_type]').val(a.adjustment_type);
            f.find('[name=amount]').val(a.amount);
            f.find('[name=description]').val(a.description);
            f.find('[name=notes]').val(a.notes);
            f.find('[name=is_taxable]').prop('checked', !!a.is_taxable);
            $('#adjustment_id_field').val(id);
            $('#adjustmentModalTitle').html('<i class="fas fa-edit me-2"></i> {{ __('messages.edit_adjustment') }}');
            $('#adjustmentModal').modal('show');
        });
    });
    @endcan
    @can('payroll_adjustment.approve') $(document).on('click', '.approve-adjustment-btn', function() { postAction(BASE + '/adjustments/' + $(this).data('id') + '/approve', '{{ __('messages.confirm') }}', ''); }); @endcan
    @can('payroll_adjustment.reject')  $(document).on('click', '.reject-adjustment-btn',  function() { postAction(BASE + '/adjustments/' + $(this).data('id') + '/reject',  '{{ __('messages.confirm') }}', ''); }); @endcan
    @can('payroll_adjustment.delete')  $(document).on('click', '.delete-adjustment-btn',  function() { deleteAction(BASE + '/adjustments/' + $(this).data('id')); }); @endcan

    // ---- Rates ----
    @can('payroll_rate.create')
    $('#rateForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this); var btn = $('#rateSubmitBtn'); var errorContainer = $('#rateFormErrors');
        clearErrors(errorContainer);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> ' + trans.saving + '...');
        $.ajax({ url: form.attr('action'), type: 'POST', data: form.serialize(), dataType: 'json',
            success: function(r) {
                if (r.success) { $('#rateModal').modal('hide'); Swal.fire({ icon: 'success', title: trans.done, text: r.message, timer: 2000 }).then(() => location.reload()); }
                else { btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> {{ __('messages.save_rate') }}'); if (r.errors) showErrors(errorContainer, r.errors); else Swal.fire({ icon: 'error', title: trans.error_title, text: r.message || trans.action_failed }); }
            },
            error: function(xhr) { btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> {{ __('messages.save_rate') }}'); if (xhr.status === 422) showErrors(errorContainer, xhr.responseJSON.errors); else Swal.fire({ icon: 'error', title: trans.error_title, text: trans.action_failed }); }
        });
    });
    @endcan
    @can('payroll_rate.view') $(document).on('click', '.view-rate-btn', function() { viewRecord(BASE + '/rates/' + $(this).data('id') + '/edit', '{{ __('messages.payroll_rate') }}'); }); @endcan
    @can('payroll_rate.edit')
    $(document).on('click', '.edit-rate-btn', function() {
        var id = $(this).data('id');
        $.getJSON(BASE + '/rates/' + id + '/edit', function(r) {
            if (!r.success) return;
            var rt = r.data; var f = $('#rateForm');
            f.find('[name=rate_type]').val(rt.rate_type);
            f.find('[name=rate_value]').val(rt.rate_value);
            f.find('[name=income_from]').val(rt.income_from);
            f.find('[name=income_to]').val(rt.income_to);
            f.find('[name=effective_from]').val(dateOnly(rt.effective_from));
            f.find('[name=effective_to]').val(dateOnly(rt.effective_to));
            f.find('[name=is_active]').prop('checked', !!rt.is_active);
            f.find('[name=description]').val(rt.description);
            f.attr('action', BASE + '/rates/' + id);
            f.find('input[name=_method]').remove();
            f.prepend('<input type="hidden" name="_method" value="PUT">');
            $('#rateModalTitle').html('<i class="fas fa-edit me-2"></i> {{ __('messages.edit_rate') }}');
            $('#rateModal').modal('show');
        });
    });
    @endcan
    @can('payroll_rate.delete') $(document).on('click', '.delete-rate-btn', function() { deleteAction(BASE + '/rates/' + $(this).data('id')); }); @endcan

    // ---- Modal reset ----
    $('.modal').on('hidden.bs.modal', function() {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
        var modal = $(this);
        modal.find('.alert-danger').addClass('d-none').empty();
        var form = modal.find('form')[0];
        if (form) form.reset();
        modal.find('input[type=hidden][id$="_id_field"]').val('');
        var titleMap = {
            periodModal: '<i class="fas fa-plus-circle me-2"></i> {{ __('messages.new_payroll_period') }}',
            calculationModal: '<i class="fas fa-plus-circle me-2"></i> {{ __('messages.manual_calculation') }}',
            loanModal: '<i class="fas fa-plus-circle me-2"></i> {{ __('messages.new_loan') }}',
            overtimeModal: '<i class="fas fa-plus-circle me-2"></i> {{ __('messages.new_overtime') }}',
            adjustmentModal: '<i class="fas fa-plus-circle me-2"></i> {{ __('messages.new_adjustment') }}',
            rateModal: '<i class="fas fa-plus-circle me-2"></i> {{ __('messages.new_rate') }}'
        };
        var titleId = modal.attr('id') + 'Title';
        if (titleMap[modal.attr('id')]) $('#' + titleId).html(titleMap[modal.attr('id')]);
        if (modal.attr('id') === 'rateModal') {
            var rateForm = $('#rateForm');
            rateForm.attr('action', rateForm.data('create-action'));
            rateForm.find('input[name=_method]').remove();
        }
    });
});
</script>
@endpush
@endsection