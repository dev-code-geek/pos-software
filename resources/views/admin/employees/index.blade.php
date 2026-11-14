@extends('layouts.admin')

@section('title', __('messages.employees'))
@section('page-title', __('messages.employee_management'))

@section('admin-content')
<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-primary">
            <div class="inner"><h3>{{ number_format($stats['total'] ?? 0) }}</h3><p>{{ __('messages.total') }}</p></div>
            <div class="icon"><i class="fas fa-users"></i></div>
            <a href="{{ route('admin.employees.index') }}" class="small-box-footer">{{ __('messages.view_all') }} <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-success">
            <div class="inner"><h3>{{ number_format($stats['active'] ?? 0) }}</h3><p>{{ __('messages.active') }}</p></div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
            <a href="#" class="small-box-footer">{{ __('messages.active') }} <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-secondary">
            <div class="inner"><h3>{{ number_format($stats['inactive'] ?? 0) }}</h3><p>{{ __('messages.inactive') }}</p></div>
            <div class="icon"><i class="fas fa-pause-circle"></i></div>
            <a href="#" class="small-box-footer">{{ __('messages.inactive') }} <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-danger">
            <div class="inner"><h3>{{ number_format($stats['terminated'] ?? 0) }}</h3><p>{{ __('messages.terminated') }}</p></div>
            <div class="icon"><i class="fas fa-user-slash"></i></div>
            <a href="#" class="small-box-footer">{{ __('messages.terminated') }} <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-warning text-dark">
            <div class="inner"><h3>{{ number_format($stats['on_leave'] ?? 0) }}</h3><p>{{ __('messages.on_leave') }}</p></div>
            <div class="icon"><i class="fas fa-clock"></i></div>
            <a href="#" class="small-box-footer">{{ __('messages.on_leave') }} <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-info">
            <div class="inner"><h3>{{ number_format($stats['with_loans'] ?? 0) }}</h3><p>{{ __('messages.with_loans') }}</p></div>
            <div class="icon"><i class="fas fa-hand-holding-usd"></i></div>
            <a href="#" class="small-box-footer">{{ __('messages.loans') }} <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<!-- Main Card -->
<div class="card">
    <div class="card-header">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <h5 class="card-title mb-0"><i class="fas fa-users text-primary me-2"></i>{{ __('messages.all_employees') }} <span class="badge bg-primary ms-1">{{ $employees->total() }}</span></h5>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <div class="input-group input-group-sm" style="width:200px;">
                <input type="text" id="searchInput" class="form-control" placeholder="{{ __('messages.search') }}...">
                <button class="btn btn-secondary" type="button" id="searchBtn"><i class="fas fa-search"></i></button>
            </div>
            <select id="companyFilter" class="form-select form-select-sm" style="width:150px;">
                <option value="">{{ __('messages.all_companies') }}</option>
                @foreach($companies as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </select>
            <select id="branchFilter" class="form-select form-select-sm" style="width:140px;">
                <option value="">{{ __('messages.all_branches') }}</option>
                @foreach($branches as $b)
                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                @endforeach
            </select>
            <select id="departmentFilter" class="form-select form-select-sm" style="width:140px;">
                <option value="">{{ __('messages.all_departments') }}</option>
                @foreach($departments as $d)
                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                @endforeach
            </select>
            <select id="statusFilter" class="form-select form-select-sm" style="width:120px;">
                <option value="">{{ __('messages.all_status') }}</option>
                <option value="Active">{{ __('messages.emp_status_active') }}</option>
                <option value="Inactive">{{ __('messages.emp_status_inactive') }}</option>
                <option value="Terminated">{{ __('messages.emp_status_terminated') }}</option>
                <option value="On Leave">{{ __('messages.emp_status_on_leave') }}</option>
            </select>
            @can('employee.create')
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fas fa-plus-circle me-1"></i> {{ __('messages.add_employee') }}
            </button>
            @endcan
            @can('employee.export')
            <a href="{{ route('admin.employees.export') }}" class="btn btn-success btn-sm"><i class="fas fa-file-excel me-1"></i> {{ __('messages.export') }}</a>
            @endcan
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:35px;">#</th>
                        <th style="min-width:160px;">{{ __('messages.employee') }}</th>
                        <th>{{ __('messages.code') }}</th>
                        <th>{{ __('messages.company') }}</th>
                        <th>{{ __('messages.department') }}</th>
                        <th>{{ __('messages.status') }}</th>
                        <th style="text-align:right;">{{ __('messages.basic_salary') }}</th>
                        <th style="width:200px;">{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($employees as $employee)
                    <tr data-id="{{ $employee->id }}" data-status="{{ $employee->employment_status }}" data-company="{{ $employee->company_id }}" data-branch="{{ $employee->branch_id }}" data-department="{{ $employee->department_id }}">
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle bg-primary text-white me-2"><i class="fas fa-user"></i></div>
                                <div>
                                    <div class="fw-bold small">{{ Str::limit($employee->first_name . ' ' . $employee->last_name, 25) }}</div>
                                    <small class="text-muted">{{ $employee->employee_code }}</small>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge bg-secondary">{{ $employee->employee_code }}</span></td>
                        <td>{{ $employee->company->name ?? 'N/A' }}</td>
                        <td>{{ $employee->department->name ?? 'N/A' }}</td>
                        <td>
                            @php
                                $statusColors = ['Active' => 'success', 'Inactive' => 'secondary', 'Terminated' => 'danger', 'On Leave' => 'warning text-dark'];
                                $statusKeys = ['Active' => 'emp_status_active', 'Inactive' => 'emp_status_inactive', 'Terminated' => 'emp_status_terminated', 'On Leave' => 'emp_status_on_leave'];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$employee->employment_status] ?? 'secondary' }}">
                                {{ __('messages.' . ($statusKeys[$employee->employment_status] ?? 'unknown')) }}
                            </span>
                        </td>
                        <td class="text-end"><span class="fw-bold text-primary">{{ number_format($employee->basic_salary ?? 0, 2) }}</span></td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                @can('employee.view')
                                <button class="btn btn-outline-primary btn-sm view-btn" data-id="{{ $employee->id }}" title="{{ __('messages.view') }}"><i class="fas fa-eye"></i></button>
                                @endcan
                                @can('employee.edit')
                                <button class="btn btn-outline-warning btn-sm edit-btn" data-id="{{ $employee->id }}" title="{{ __('messages.edit') }}"><i class="fas fa-edit"></i></button>
                                @endcan
                                @can('employee.edit')
                                <button class="btn btn-outline-{{ $employee->is_active ? 'secondary' : 'success' }} btn-sm toggle-status" data-id="{{ $employee->id }}" title="{{ $employee->is_active ? __('messages.deactivate') : __('messages.activate') }}">
                                    <i class="fas {{ $employee->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                </button>
                                @endcan
                                @can('employee.delete')
                                <button class="btn btn-outline-danger btn-sm delete-btn" data-id="{{ $employee->id }}" title="{{ __('messages.delete') }}"><i class="fas fa-trash"></i></button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8"><div class="text-center py-5"><i class="fas fa-users fa-4x text-muted mb-3 d-block"></i><h5 class="fw-bold">{{ __('messages.no_employees_found') }}</h5></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div><span class="text-muted small">{{ __('messages.showing') }} {{ $employees->firstItem() ?? 0 }} {{ __('messages.to') }} {{ $employees->lastItem() ?? 0 }} {{ __('messages.of') }} {{ $employees->total() }} {{ __('messages.entries') }}</span></div>
        <div>{{ $employees->links() }}</div>
    </div>
</div>

<!-- CREATE MODAL -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width:700px;">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="fas fa-plus-circle me-2"></i> {{ __('messages.add_employee') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="createForm" action="{{ route('admin.employees.store') }}" method="POST">
                @csrf
                <div class="modal-body" style="max-height:70vh; overflow-y:auto; padding:20px 25px;">
                    <div id="createFormErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.company') }} <span class="text-danger">*</span></label>
                            <select name="company_id" class="form-select" required>
                                <option value="">{{ __('messages.select_company') }}</option>
                                @foreach($companies as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.branch') }}</label>
                            <select name="branch_id" class="form-select">
                                <option value="">{{ __('messages.select_branch') }}</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.department') }}</label>
                            <select name="department_id" class="form-select">
                                <option value="">{{ __('messages.select_department') }}</option>
                                @foreach($departments as $d)
                                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.first_name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.last_name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.email') }}</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.phone') }}</label>
                            <input type="text" name="phone" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.gender') }}</label>
                            <select name="gender" class="form-select">
                                <option value="">{{ __('messages.select_gender') }}</option>
                                <option value="Male">{{ __('messages.male') }}</option>
                                <option value="Female">{{ __('messages.female') }}</option>
                                <option value="Other">{{ __('messages.other') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.date_of_birth') }}</label>
                            <input type="date" name="date_of_birth" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.hire_date') }}</label>
                            <input type="date" name="hire_date" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.employment_status') }} <span class="text-danger">*</span></label>
                            <select name="employment_status" class="form-select" required>
                                <option value="Active">{{ __('messages.emp_status_active') }}</option>
                                <option value="Inactive">{{ __('messages.emp_status_inactive') }}</option>
                                <option value="Terminated">{{ __('messages.emp_status_terminated') }}</option>
                                <option value="On Leave">{{ __('messages.emp_status_on_leave') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.basic_salary') }}</label>
                            <input type="number" name="basic_salary" class="form-control" step="0.01" min="0" placeholder="0.00">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">{{ __('messages.address') }}</label>
                            <textarea name="address" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-2">
                                <input type="checkbox" name="is_active" class="form-check-input" id="createIsActive" value="1" checked>
                                <label class="form-check-label" for="createIsActive">{{ __('messages.active') }}</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> {{ __('messages.cancel') }}</button>
                    <button type="submit" class="btn btn-primary" id="createSubmitBtn"><i class="fas fa-save me-1"></i> {{ __('messages.save_employee') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- VIEW MODAL -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:700px;">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title text-white"><i class="fas fa-eye me-2"></i> {{ __('messages.employee_details') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewModalBody" style="max-height:70vh; overflow-y:auto; padding:20px 25px;">
                <div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.close') }}</button></div>
        </div>
    </div>
</div>

<!-- EDIT MODAL -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:700px;">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title text-dark"><i class="fas fa-edit me-2"></i> {{ __('messages.edit_employee') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-body" id="editModalBody" style="max-height:70vh; overflow-y:auto; padding:20px 25px;">
                    <div id="editFormErrors" class="alert alert-danger d-none"></div>
                    <div class="text-center py-5"><div class="spinner-border text-warning" role="status"></div></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> {{ __('messages.cancel') }}</button>
                    <button type="submit" class="btn btn-warning" id="editSubmitBtn"><i class="fas fa-save me-1"></i> {{ __('messages.update_employee') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- DELETE MODAL -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:560px;">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white"><i class="fas fa-exclamation-triangle me-2"></i> {{ __('messages.confirm_delete') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-trash fa-4x text-danger mb-3 d-block"></i>
                <p class="fw-bold">{{ __('messages.are_you_sure_delete_employee') }}</p>
                <p class="text-muted small">{{ __('messages.this_action_cannot_be_undone') }}</p>
                <form id="deleteForm" method="POST">@csrf @method('DELETE')</form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> {{ __('messages.cancel') }}</button>
                <button type="submit" class="btn btn-danger" id="deleteSubmitBtn" form="deleteForm"><i class="fas fa-trash me-1"></i> {{ __('messages.delete') }}</button>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-circle { width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:16px; font-weight:600; flex-shrink:0; }
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
</style>

@push('scripts')
<script>
window.trans = {
    loading: @json(__('messages.loading')),
    close: @json(__('messages.close')),
    cancel: @json(__('messages.cancel')),
    delete: @json(__('messages.delete')),
    edit: @json(__('messages.edit')),
    view: @json(__('messages.view')),
    // Fields
    employee: @json(__('messages.employee')),
    code: @json(__('messages.code')),
    company: @json(__('messages.company')),
    branch: @json(__('messages.branch')),
    department: @json(__('messages.department')),
    first_name: @json(__('messages.first_name')),
    last_name: @json(__('messages.last_name')),
    email: @json(__('messages.email')),
    phone: @json(__('messages.phone')),
    gender: @json(__('messages.gender')),
    select_gender: @json(__('messages.select_gender')),
    male: @json(__('messages.male')),
    female: @json(__('messages.female')),
    other: @json(__('messages.other')),
    date_of_birth: @json(__('messages.date_of_birth')),
    hire_date: @json(__('messages.hire_date')),
    employment_status: @json(__('messages.employment_status')),
    basic_salary: @json(__('messages.basic_salary')),
    address: @json(__('messages.address')),
    active: @json(__('messages.active')),
    inactive: @json(__('messages.inactive')),
    yes: @json(__('messages.yes')),
    no: @json(__('messages.no')),
    created: @json(__('messages.created')),
    // Employment statuses
    emp_status_active: @json(__('messages.emp_status_active')),
    emp_status_inactive: @json(__('messages.emp_status_inactive')),
    emp_status_terminated: @json(__('messages.emp_status_terminated')),
    emp_status_on_leave: @json(__('messages.emp_status_on_leave')),
    select_company: @json(__('messages.select_company')),
    select_branch: @json(__('messages.select_branch')),
    select_department: @json(__('messages.select_department')),
    // Alerts
    created_alert: @json(__('messages.created_alert')),
    updated: @json(__('messages.updated')),
    deleted: @json(__('messages.deleted')),
    saving: @json(__('messages.saving')),
    updating: @json(__('messages.updating')),
    deleting: @json(__('messages.deleting')),
    save_employee: @json(__('messages.save_employee')),
    update_employee: @json(__('messages.update_employee')),
    error_title: @json(__('messages.error')),
    something_went_wrong: @json(__('messages.something_went_wrong')),
    failed_to_create: @json(__('messages.failed_to_create')),
    failed_to_update: @json(__('messages.failed_to_update')),
    failed_to_delete: @json(__('messages.failed_to_delete')),
    are_you_sure: @json(__('messages.are_you_sure')),
    wont_be_able_to_revert: @json(__('messages.wont_be_able_to_revert')),
    yes_delete: @json(__('messages.yes_delete')),
};
$(document).ready(function() {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    function showErrors(container, errors) {
        container.removeClass('d-none');
        var html = '<ul class="mb-0">';
        $.each(errors, function(key, messages) { $.each(messages, function(i, msg) { html += '<li>' + msg + '</li>'; }); });
        html += '</ul>';
        container.html(html);
        container[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    function clearErrors(container) { container.addClass('d-none').empty(); }

    // VIEW
    @can('employee.view')
    $(document).on('click', '.view-btn', function() {
        var id = $(this).data('id');
        var body = $('#viewModalBody');
        body.html(`<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>`);
        $('#viewModal').modal('show');
        $.get('/admin/employees/' + id, function(e) {
            var statusKey = 'emp_status_' + e.employment_status.toLowerCase().replace(/\s+/g, '_');
            var statusLabel = trans[statusKey] || e.employment_status;
            var statusColor = e.employment_status == 'Active' ? 'success' : (e.employment_status == 'Inactive' ? 'secondary' : (e.employment_status == 'Terminated' ? 'danger' : 'warning text-dark'));
            var html = `
                <div class="row g-3">
                    <div class="col-md-6"><label class="text-muted small fw-bold">${trans.employee}</label><p class="fw-bold mb-0">${e.first_name} ${e.last_name}</p></div>
                    <div class="col-md-6"><label class="text-muted small fw-bold">${trans.code}</label><p class="mb-0"><span class="badge bg-secondary">${e.employee_code}</span></p></div>
                    <div class="col-md-6"><label class="text-muted small fw-bold">${trans.company}</label><p class="mb-0">${e.company ? e.company.name : 'N/A'}</p></div>
                    <div class="col-md-6"><label class="text-muted small fw-bold">${trans.branch}</label><p class="mb-0">${e.branch ? e.branch.name : 'N/A'}</p></div>
                    <div class="col-md-6"><label class="text-muted small fw-bold">${trans.department}</label><p class="mb-0">${e.department ? e.department.name : 'N/A'}</p></div>
                    <div class="col-md-6"><label class="text-muted small fw-bold">${trans.employment_status}</label><p class="mb-0"><span class="badge bg-${statusColor}">${statusLabel}</span></p></div>
                    <div class="col-md-6"><label class="text-muted small fw-bold">${trans.email}</label><p class="mb-0">${e.email || 'N/A'}</p></div>
                    <div class="col-md-6"><label class="text-muted small fw-bold">${trans.phone}</label><p class="mb-0">${e.phone || 'N/A'}</p></div>
                    <div class="col-md-6"><label class="text-muted small fw-bold">${trans.gender}</label><p class="mb-0">${e.gender ? (trans[e.gender.toLowerCase()] || e.gender) : 'N/A'}</p></div>
                    <div class="col-md-6"><label class="text-muted small fw-bold">${trans.date_of_birth}</label><p class="mb-0">${e.date_of_birth ? new Date(e.date_of_birth).toLocaleDateString() : 'N/A'}</p></div>
                    <div class="col-md-6"><label class="text-muted small fw-bold">${trans.hire_date}</label><p class="mb-0">${e.hire_date ? new Date(e.hire_date).toLocaleDateString() : 'N/A'}</p></div>
                    <div class="col-md-6"><label class="text-muted small fw-bold">${trans.basic_salary}</label><p class="fw-bold text-primary mb-0">${e.basic_salary ? '$' + parseFloat(e.basic_salary).toFixed(2) : 'N/A'}</p></div>
                    ${e.address ? `<div class="col-12"><label class="text-muted small fw-bold">${trans.address}</label><p class="mb-0">${e.address}</p></div>` : ''}
                    <div class="col-md-6"><label class="text-muted small fw-bold">${trans.active}</label><p class="mb-0">${e.is_active ? trans.yes : trans.no}</p></div>
                    <div class="col-md-6"><label class="text-muted small fw-bold">${trans.created}</label><p class="mb-0">${new Date(e.created_at).toLocaleString()}</p></div>
                </div>`;
            body.html(html);
        });
    });
    @endcan

    // EDIT
    @can('employee.edit')
    $(document).on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        var body = $('#editModalBody');
        var form = $('#editForm');
        form.attr('action', '/admin/employees/' + id);
        body.html(`<div id="editFormErrors" class="alert alert-danger d-none"></div><div class="text-center py-5"><div class="spinner-border text-warning" role="status"></div></div>`);
        $('#editModal').modal('show');
        $.get('/admin/employees/' + id + '/edit', function(r) {
            var e = r.employee;
            var companyOptions = ''; r.companies.forEach(function(c) { companyOptions += `<option value="${c.id}" ${e.company_id == c.id ? 'selected' : ''}>${c.name}</option>`; });
            var branchOptions = `<option value="">${trans.select_branch}</option>`; r.branches.forEach(function(b) { branchOptions += `<option value="${b.id}" ${e.branch_id == b.id ? 'selected' : ''}>${b.name}</option>`; });
            var deptOptions = `<option value="">${trans.select_department}</option>`; r.departments.forEach(function(d) { deptOptions += `<option value="${d.id}" ${e.department_id == d.id ? 'selected' : ''}>${d.name}</option>`; });
            body.html(`
                <div id="editFormErrors" class="alert alert-danger d-none"></div>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label fw-bold">${trans.company} <span class="text-danger">*</span></label><select name="company_id" class="form-select" required>${companyOptions}</select></div>
                    <div class="col-md-6"><label class="form-label fw-bold">${trans.branch}</label><select name="branch_id" class="form-select">${branchOptions}</select></div>
                    <div class="col-md-6"><label class="form-label fw-bold">${trans.department}</label><select name="department_id" class="form-select">${deptOptions}</select></div>
                    <div class="col-md-6"><label class="form-label fw-bold">${trans.first_name} <span class="text-danger">*</span></label><input type="text" name="first_name" class="form-control" value="${e.first_name}" required></div>
                    <div class="col-md-6"><label class="form-label fw-bold">${trans.last_name} <span class="text-danger">*</span></label><input type="text" name="last_name" class="form-control" value="${e.last_name}" required></div>
                    <div class="col-md-6"><label class="form-label fw-bold">${trans.email}</label><input type="email" name="email" class="form-control" value="${e.email || ''}"></div>
                    <div class="col-md-6"><label class="form-label fw-bold">${trans.phone}</label><input type="text" name="phone" class="form-control" value="${e.phone || ''}"></div>
                    <div class="col-md-6"><label class="form-label fw-bold">${trans.gender}</label>
                        <select name="gender" class="form-select">
                            <option value="">${trans.select_gender}</option>
                            <option value="Male" ${e.gender == 'Male' ? 'selected' : ''}>${trans.male}</option>
                            <option value="Female" ${e.gender == 'Female' ? 'selected' : ''}>${trans.female}</option>
                            <option value="Other" ${e.gender == 'Other' ? 'selected' : ''}>${trans.other}</option>
                        </select>
                    </div>
                    <div class="col-md-6"><label class="form-label fw-bold">${trans.date_of_birth}</label><input type="date" name="date_of_birth" class="form-control" value="${e.date_of_birth || ''}"></div>
                    <div class="col-md-6"><label class="form-label fw-bold">${trans.hire_date}</label><input type="date" name="hire_date" class="form-control" value="${e.hire_date || ''}"></div>
                    <div class="col-md-6"><label class="form-label fw-bold">${trans.employment_status} <span class="text-danger">*</span></label>
                        <select name="employment_status" class="form-select" required>
                            <option value="Active" ${e.employment_status == 'Active' ? 'selected' : ''}>${trans.emp_status_active}</option>
                            <option value="Inactive" ${e.employment_status == 'Inactive' ? 'selected' : ''}>${trans.emp_status_inactive}</option>
                            <option value="Terminated" ${e.employment_status == 'Terminated' ? 'selected' : ''}>${trans.emp_status_terminated}</option>
                            <option value="On Leave" ${e.employment_status == 'On Leave' ? 'selected' : ''}>${trans.emp_status_on_leave}</option>
                        </select>
                    </div>
                    <div class="col-md-6"><label class="form-label fw-bold">${trans.basic_salary}</label><input type="number" name="basic_salary" class="form-control" step="0.01" min="0" value="${e.basic_salary || ''}"></div>
                    <div class="col-12"><label class="form-label fw-bold">${trans.address}</label><textarea name="address" class="form-control" rows="2">${e.address || ''}</textarea></div>
                    <div class="col-md-6"><div class="form-check form-switch mt-2"><input type="checkbox" name="is_active" class="form-check-input" id="editIsActive" value="1" ${e.is_active ? 'checked' : ''}><label class="form-check-label" for="editIsActive">${trans.active}</label></div></div>
                </div>`);
        });
    });
    @endcan

    // CREATE
    @can('employee.create')
    $('#createForm').on('submit', function(e) {
        e.preventDefault();
        var btn = $('#createSubmitBtn');
        var errorContainer = $('#createFormErrors');
        clearErrors(errorContainer);
        btn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm me-1"></span> ${trans.saving}...`);
        $.ajax({
            url: $(this).attr('action'), type: 'POST', data: $(this).serialize(), dataType: 'json',
            success: function(r) {
                if (r.success) {
                    $('#createModal').modal('hide');
                    Swal.fire({ icon: 'success', title: trans.created_alert, text: r.message, timer: 2000 }).then(() => location.reload());
                } else {
                    btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.save_employee}`);
                    if (r.errors) showErrors(errorContainer, r.errors);
                    else Swal.fire({ icon: 'error', title: trans.error_title, text: r.message || trans.something_went_wrong });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.save_employee}`);
                if (xhr.status === 422) showErrors(errorContainer, xhr.responseJSON.errors);
                else Swal.fire({ icon: 'error', title: trans.error_title, text: trans.failed_to_create });
            }
        });
    });
    @endcan

    // UPDATE
    @can('employee.edit')
    $('#editForm').on('submit', function(e) {
        e.preventDefault();
        var btn = $('#editSubmitBtn');
        var errorContainer = $('#editFormErrors');
        clearErrors(errorContainer);
        btn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm me-1"></span> ${trans.updating}...`);
        $.ajax({
            url: $(this).attr('action'), type: 'POST', data: $(this).serialize(), dataType: 'json',
            success: function(r) {
                if (r.success) {
                    $('#editModal').modal('hide');
                    Swal.fire({ icon: 'success', title: trans.updated, text: r.message, timer: 2000 }).then(() => location.reload());
                } else {
                    btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.update_employee}`);
                    if (r.errors) showErrors(errorContainer, r.errors);
                    else Swal.fire({ icon: 'error', title: trans.error_title, text: r.message || trans.something_went_wrong });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.update_employee}`);
                if (xhr.status === 422) showErrors(errorContainer, xhr.responseJSON.errors);
                else Swal.fire({ icon: 'error', title: trans.error_title, text: trans.failed_to_update });
            }
        });
    });
    @endcan

    // TOGGLE STATUS
    @can('employee.edit')
    $(document).on('click', '.toggle-status', function() {
        var id = $(this).data('id');
        $.post('/admin/employees/' + id + '/toggle-status', { _token: '{{ csrf_token() }}' }, function(r) {
            if (r.success) Swal.fire({ icon: 'success', title: trans.updated, text: r.message, timer: 1500 }).then(() => location.reload());
        });
    });
    @endcan

    // DELETE
    @can('employee.delete')
    $(document).on('click', '.delete-btn', function() {
        var id = $(this).data('id');
        $('#deleteForm').attr('action', '/admin/employees/' + id);
        Swal.fire({
            title: trans.are_you_sure, text: trans.wont_be_able_to_revert, icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#6c757d',
            confirmButtonText: trans.yes_delete, cancelButtonText: trans.cancel
        }).then((result) => { if (result.isConfirmed) { $('#deleteModal').modal('show'); } });
    });
    $('#deleteForm').on('submit', function(e) {
        e.preventDefault();
        var btn = $('#deleteSubmitBtn');
        btn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm me-1"></span> ${trans.deleting}...`);
        $.ajax({
            url: $(this).attr('action'), type: 'POST',
            data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
            success: function(r) {
                if (r.success) {
                    $('#deleteModal').modal('hide');
                    Swal.fire({ icon: 'success', title: trans.deleted, text: r.message, timer: 2000 }).then(() => location.reload());
                } else {
                    btn.prop('disabled', false).html(`<i class="fas fa-trash me-1"></i> ${trans.delete}`);
                    Swal.fire({ icon: 'error', title: trans.error_title, text: r.message || trans.failed_to_delete });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(`<i class="fas fa-trash me-1"></i> ${trans.delete}`);
                Swal.fire({ icon: 'error', title: trans.error_title, text: xhr.responseJSON?.message || trans.failed_to_delete });
            }
        });
    });
    @endcan

    // SEARCH & FILTER
    $('#searchBtn').on('click', filterTable);
    $('#searchInput').on('keypress', function(e) { if (e.which === 13) filterTable(); });
    $('#companyFilter, #branchFilter, #departmentFilter, #statusFilter').on('change', filterTable);
    function filterTable() {
        var search = $('#searchInput').val().toLowerCase();
        var company = $('#companyFilter').val();
        var branch = $('#branchFilter').val();
        var department = $('#departmentFilter').val();
        var status = $('#statusFilter').val();
        $('#tableBody tr').each(function() {
            var row = $(this);
            var show = true;
            if (search && !row.text().toLowerCase().includes(search)) show = false;
            if (company && row.data('company') != company) show = false;
            if (branch && row.data('branch') != branch) show = false;
            if (department && row.data('department') != department) show = false;
            if (status && row.data('status') !== status) show = false;
            row.toggle(show);
        });
    }

    $('.modal').on('hidden.bs.modal', function() {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
        $(this).find('.alert-danger').addClass('d-none').empty();
        $(this).find('form')[0]?.reset();
    });
});
</script>
@endpush
@endsection