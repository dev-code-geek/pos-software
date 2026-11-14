@extends('layouts.admin')

@section('title', __('messages.leave_types'))
@section('page-title', __('messages.leave_type_management'))

@section('admin-content')
<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-4 col-lg-6">
        <div class="small-box bg-primary">
            <div class="inner"><h3>{{ number_format($leaveTypes->total() ?? 0) }}</h3><p>{{ __('messages.total_leave_types') }}</p></div>
            <div class="icon"><i class="fas fa-calendar-alt"></i></div>
            <a href="{{ route('admin.leave-types.index') }}" class="small-box-footer">{{ __('messages.view_all') }} <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-xl-4 col-lg-6">
        <div class="small-box bg-success">
            <div class="inner"><h3>{{ number_format($leaveTypes->where('is_active', 1)->count() ?? 0) }}</h3><p>{{ __('messages.active') }}</p></div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
            <a href="#" class="small-box-footer">{{ __('messages.active') }} <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-xl-4 col-lg-6">
        <div class="small-box bg-secondary">
            <div class="inner"><h3>{{ number_format($leaveTypes->where('is_active', 0)->count() ?? 0) }}</h3><p>{{ __('messages.inactive') }}</p></div>
            <div class="icon"><i class="fas fa-pause-circle"></i></div>
            <a href="#" class="small-box-footer">{{ __('messages.inactive') }} <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<!-- Main Card -->
<div class="card">
    <div class="card-header">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <h5 class="card-title mb-0"><i class="fas fa-calendar-alt text-primary me-2"></i>{{ __('messages.all_leave_types') }} <span class="badge bg-primary ms-1">{{ $leaveTypes->total() }}</span></h5>
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
            <select id="statusFilter" class="form-select form-select-sm" style="width:120px;">
                <option value="">{{ __('messages.all_status') }}</option>
                <option value="1">{{ __('messages.active') }}</option>
                <option value="0">{{ __('messages.inactive') }}</option>
            </select>
            @can('leave_type.create')
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fas fa-plus-circle me-1"></i> {{ __('messages.add_leave_type') }}
            </button>
            @endcan
            @can('leave_type.export')
            <a href="{{ route('admin.leave-types.export') }}" class="btn btn-success btn-sm"><i class="fas fa-file-excel me-1"></i> {{ __('messages.export') }}</a>
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
                        <th class="text-center">{{ __('messages.days_per_year') }}</th>
                        <th>{{ __('messages.paid') }}</th>
                        <th>{{ __('messages.medical_cert') }}</th>
                        <th>{{ __('messages.status') }}</th>
                        <th style="width:180px;">{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($leaveTypes as $type)
                    <tr data-id="{{ $type->id }}" data-status="{{ $type->is_active ? '1' : '0' }}" data-company="{{ $type->company_id }}">
                        <td>{{ $loop->iteration }}</td>
                        <td><span class="badge bg-secondary">{{ $type->leave_code }}</span></td>
                        <td>{{ $type->leave_name }}</td>
                        <td>{{ $type->company->name ?? 'N/A' }}</td>
                        <td class="text-center"><span class="fw-bold">{{ $type->days_per_year }}</span></td>
                        <td><span class="badge bg-{{ $type->is_paid ? 'success' : 'secondary' }}">{{ $type->is_paid ? __('messages.yes') : __('messages.no') }}</span></td>
                        <td><span class="badge bg-{{ $type->requires_medical_certificate ? 'info' : 'secondary' }}">{{ $type->requires_medical_certificate ? __('messages.yes') : __('messages.no') }}</span></td>
                        <td>
                            <span class="badge bg-{{ $type->is_active ? 'success' : 'secondary' }}">
                                {{ $type->is_active ? __('messages.active') : __('messages.inactive') }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                @can('leave_type.view')
                                <button class="btn btn-outline-primary btn-sm view-btn" data-id="{{ $type->id }}" title="{{ __('messages.view') }}"><i class="fas fa-eye"></i></button>
                                @endcan
                                @can('leave_type.edit')
                                <button class="btn btn-outline-warning btn-sm edit-btn" data-id="{{ $type->id }}" title="{{ __('messages.edit') }}"><i class="fas fa-edit"></i></button>
                                @endcan
                                @can('leave_type.edit')
                                <button class="btn btn-outline-{{ $type->is_active ? 'secondary' : 'success' }} btn-sm toggle-status" data-id="{{ $type->id }}" title="{{ $type->is_active ? __('messages.deactivate') : __('messages.activate') }}">
                                    <i class="fas {{ $type->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                </button>
                                @endcan
                                @can('leave_type.delete')
                                <button class="btn btn-outline-danger btn-sm delete-btn" data-id="{{ $type->id }}" title="{{ __('messages.delete') }}"><i class="fas fa-trash"></i></button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9"><div class="text-center py-5"><i class="fas fa-calendar-alt fa-4x text-muted mb-3 d-block"></i><h5 class="fw-bold">{{ __('messages.no_leave_types_found') }}</h5></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div><span class="text-muted small">{{ __('messages.showing') }} {{ $leaveTypes->firstItem() ?? 0 }} {{ __('messages.to') }} {{ $leaveTypes->lastItem() ?? 0 }} {{ __('messages.of') }} {{ $leaveTypes->total() }} {{ __('messages.entries') }}</span></div>
        <div>{{ $leaveTypes->links() }}</div>
    </div>
</div>

<!-- CREATE MODAL -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:600px;">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="fas fa-plus-circle me-2"></i> {{ __('messages.add_leave_type') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="createForm" action="{{ route('admin.leave-types.store') }}" method="POST">
                @csrf
                <div class="modal-body" style="padding:20px 25px;">
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
                            <label class="form-label fw-bold">{{ __('messages.code') }} <span class="text-danger">*</span></label>
                            <input type="text" name="leave_code" class="form-control" required placeholder="{{ __('messages.code_placeholder') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="leave_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.days_per_year') }} <span class="text-danger">*</span></label>
                            <input type="number" name="days_per_year" class="form-control" step="0.5" min="0" max="365" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.max_consecutive_days') }}</label>
                            <input type="number" name="max_consecutive_days" class="form-control" min="1" placeholder="{{ __('messages.optional') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">{{ __('messages.description') }}</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch mt-2">
                                <input type="checkbox" name="is_paid" class="form-check-input" id="createIsPaid" value="1" checked>
                                <label class="form-check-label" for="createIsPaid">{{ __('messages.paid') }}</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch mt-2">
                                <input type="checkbox" name="requires_approval" class="form-check-input" id="createRequiresApproval" value="1" checked>
                                <label class="form-check-label" for="createRequiresApproval">{{ __('messages.requires_approval') }}</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch mt-2">
                                <input type="checkbox" name="requires_medical_certificate" class="form-check-input" id="createRequiresMedical" value="1">
                                <label class="form-check-label" for="createRequiresMedical">{{ __('messages.medical_certificate') }}</label>
                            </div>
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
                    <button type="submit" class="btn btn-primary" id="createSubmitBtn"><i class="fas fa-save me-1"></i> {{ __('messages.save_leave_type') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- VIEW MODAL -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:600px;">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title text-white"><i class="fas fa-eye me-2"></i> {{ __('messages.leave_type_details') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewModalBody" style="padding:20px 25px;">
                <div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.close') }}</button></div>
        </div>
    </div>
</div>

<!-- EDIT MODAL -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:600px;">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title text-dark"><i class="fas fa-edit me-2"></i> {{ __('messages.edit_leave_type') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-body" id="editModalBody" style="padding:20px 25px;">
                    <div id="editFormErrors" class="alert alert-danger d-none"></div>
                    <div class="text-center py-5"><div class="spinner-border text-warning" role="status"></div></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> {{ __('messages.cancel') }}</button>
                    <button type="submit" class="btn btn-warning" id="editSubmitBtn"><i class="fas fa-save me-1"></i> {{ __('messages.update_leave_type') }}</button>
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
                <p class="fw-bold">{{ __('messages.are_you_sure_delete_leave_type') }}</p>
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
    code: @json(__('messages.code')),
    name: @json(__('messages.name')),
    company: @json(__('messages.company')),
    days_per_year: @json(__('messages.days_per_year')),
    max_consecutive_days: @json(__('messages.max_consecutive_days')),
    description: @json(__('messages.description')),
    paid: @json(__('messages.paid')),
    requires_approval: @json(__('messages.requires_approval')),
    medical_certificate: @json(__('messages.medical_certificate')),
    active: @json(__('messages.active')),
    inactive: @json(__('messages.inactive')),
    yes: @json(__('messages.yes')),
    no: @json(__('messages.no')),
    unlimited: @json(__('messages.unlimited')),
    status: @json(__('messages.status')),
    created: @json(__('messages.created')),
    created_alert: @json(__('messages.created_alert')),
    updated: @json(__('messages.updated')),
    deleted: @json(__('messages.deleted')),
    saving: @json(__('messages.saving')),
    updating: @json(__('messages.updating')),
    deleting: @json(__('messages.deleting')),
    save_leave_type: @json(__('messages.save_leave_type')),
    update_leave_type: @json(__('messages.update_leave_type')),
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
    @can('leave_type.view')
    $(document).on('click', '.view-btn', function() {
        var id = $(this).data('id');
        var body = $('#viewModalBody');
        body.html(`<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>`);
        $('#viewModal').modal('show');
        $.get('/admin/leave-types/' + id, function(t) {
            var html = `
                <div class="row g-3">
                    <div class="col-md-6"><label class="text-muted small fw-bold">${trans.code}</label><p class="fw-bold mb-0">${t.leave_code}</p></div>
                    <div class="col-md-6"><label class="text-muted small fw-bold">${trans.name}</label><p class="mb-0">${t.leave_name}</p></div>
                    <div class="col-md-6"><label class="text-muted small fw-bold">${trans.company}</label><p class="mb-0">${t.company ? t.company.name : 'N/A'}</p></div>
                    <div class="col-md-6"><label class="text-muted small fw-bold">${trans.days_per_year}</label><p class="fw-bold mb-0">${t.days_per_year}</p></div>
                    <div class="col-md-6"><label class="text-muted small fw-bold">${trans.max_consecutive_days}</label><p class="mb-0">${t.max_consecutive_days || trans.unlimited}</p></div>
                    <div class="col-md-6"><label class="text-muted small fw-bold">${trans.description}</label><p class="mb-0">${t.description || 'N/A'}</p></div>
                    <div class="col-md-4"><label class="text-muted small fw-bold">${trans.paid}</label><p class="mb-0"><span class="badge bg-${t.is_paid ? 'success' : 'secondary'}">${t.is_paid ? trans.yes : trans.no}</span></p></div>
                    <div class="col-md-4"><label class="text-muted small fw-bold">${trans.requires_approval}</label><p class="mb-0"><span class="badge bg-${t.requires_approval ? 'success' : 'secondary'}">${t.requires_approval ? trans.yes : trans.no}</span></p></div>
                    <div class="col-md-4"><label class="text-muted small fw-bold">${trans.medical_certificate}</label><p class="mb-0"><span class="badge bg-${t.requires_medical_certificate ? 'info' : 'secondary'}">${t.requires_medical_certificate ? trans.yes : trans.no}</span></p></div>
                    <div class="col-md-6"><label class="text-muted small fw-bold">${trans.status}</label><p class="mb-0"><span class="badge bg-${t.is_active ? 'success' : 'secondary'}">${t.is_active ? trans.active : trans.inactive}</span></p></div>
                    <div class="col-md-6"><label class="text-muted small fw-bold">${trans.created}</label><p class="mb-0">${new Date(t.created_at).toLocaleString()}</p></div>
                </div>`;
            body.html(html);
        });
    });
    @endcan

    // EDIT
    @can('leave_type.edit')
    $(document).on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        var body = $('#editModalBody');
        var form = $('#editForm');
        form.attr('action', '/admin/leave-types/' + id);
        body.html(`<div id="editFormErrors" class="alert alert-danger d-none"></div><div class="text-center py-5"><div class="spinner-border text-warning" role="status"></div></div>`);
        $('#editModal').modal('show');
        $.get('/admin/leave-types/' + id + '/edit', function(r) {
            var t = r.leave_type;
            var companyOptions = '';
            r.companies.forEach(function(c) { companyOptions += `<option value="${c.id}" ${t.company_id == c.id ? 'selected' : ''}>${c.name}</option>`; });
            body.html(`
                <div id="editFormErrors" class="alert alert-danger d-none"></div>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label fw-bold">${trans.company} <span class="text-danger">*</span></label><select name="company_id" class="form-select" required>${companyOptions}</select></div>
                    <div class="col-md-6"><label class="form-label fw-bold">${trans.code} <span class="text-danger">*</span></label><input type="text" name="leave_code" class="form-control" value="${t.leave_code}" required></div>
                    <div class="col-md-6"><label class="form-label fw-bold">${trans.name} <span class="text-danger">*</span></label><input type="text" name="leave_name" class="form-control" value="${t.leave_name}" required></div>
                    <div class="col-md-6"><label class="form-label fw-bold">${trans.days_per_year} <span class="text-danger">*</span></label><input type="number" name="days_per_year" class="form-control" step="0.5" min="0" max="365" value="${t.days_per_year}" required></div>
                    <div class="col-md-6"><label class="form-label fw-bold">${trans.max_consecutive_days}</label><input type="number" name="max_consecutive_days" class="form-control" min="1" value="${t.max_consecutive_days || ''}"></div>
                    <div class="col-12"><label class="form-label fw-bold">${trans.description}</label><textarea name="description" class="form-control" rows="2">${t.description || ''}</textarea></div>
                    <div class="col-md-4"><div class="form-check form-switch mt-2"><input type="checkbox" name="is_paid" class="form-check-input" id="editIsPaid" value="1" ${t.is_paid ? 'checked' : ''}><label class="form-check-label" for="editIsPaid">${trans.paid}</label></div></div>
                    <div class="col-md-4"><div class="form-check form-switch mt-2"><input type="checkbox" name="requires_approval" class="form-check-input" id="editRequiresApproval" value="1" ${t.requires_approval ? 'checked' : ''}><label class="form-check-label" for="editRequiresApproval">${trans.requires_approval}</label></div></div>
                    <div class="col-md-4"><div class="form-check form-switch mt-2"><input type="checkbox" name="requires_medical_certificate" class="form-check-input" id="editRequiresMedical" value="1" ${t.requires_medical_certificate ? 'checked' : ''}><label class="form-check-label" for="editRequiresMedical">${trans.medical_certificate}</label></div></div>
                    <div class="col-md-6"><div class="form-check form-switch mt-2"><input type="checkbox" name="is_active" class="form-check-input" id="editIsActive" value="1" ${t.is_active ? 'checked' : ''}><label class="form-check-label" for="editIsActive">${trans.active}</label></div></div>
                </div>`);
        });
    });
    @endcan

    // CREATE
    @can('leave_type.create')
    $('#createForm').on('submit', function(e) {
        e.preventDefault();
        var btn = $('#createSubmitBtn'); var errorContainer = $('#createFormErrors');
        clearErrors(errorContainer);
        btn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm me-1"></span> ${trans.saving}...`);
        $.ajax({ url: $(this).attr('action'), type: 'POST', data: $(this).serialize(), dataType: 'json',
            success: function(r) {
                if (r.success) { $('#createModal').modal('hide'); Swal.fire({ icon: 'success', title: trans.created_alert, text: r.message, timer: 2000 }).then(() => location.reload()); }
                else { btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.save_leave_type}`); if (r.errors) showErrors(errorContainer, r.errors); else Swal.fire({ icon: 'error', title: trans.error_title, text: r.message || trans.something_went_wrong }); }
            },
            error: function(xhr) { btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.save_leave_type}`); if (xhr.status === 422) showErrors(errorContainer, xhr.responseJSON.errors); else Swal.fire({ icon: 'error', title: trans.error_title, text: trans.failed_to_create }); }
        });
    });
    @endcan

    // UPDATE
    @can('leave_type.edit')
    $('#editForm').on('submit', function(e) {
        e.preventDefault();
        var btn = $('#editSubmitBtn'); var errorContainer = $('#editFormErrors');
        clearErrors(errorContainer);
        btn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm me-1"></span> ${trans.updating}...`);
        $.ajax({ url: $(this).attr('action'), type: 'POST', data: $(this).serialize(), dataType: 'json',
            success: function(r) {
                if (r.success) { $('#editModal').modal('hide'); Swal.fire({ icon: 'success', title: trans.updated, text: r.message, timer: 2000 }).then(() => location.reload()); }
                else { btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.update_leave_type}`); if (r.errors) showErrors(errorContainer, r.errors); else Swal.fire({ icon: 'error', title: trans.error_title, text: r.message || trans.something_went_wrong }); }
            },
            error: function(xhr) { btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.update_leave_type}`); if (xhr.status === 422) showErrors(errorContainer, xhr.responseJSON.errors); else Swal.fire({ icon: 'error', title: trans.error_title, text: trans.failed_to_update }); }
        });
    });
    @endcan

    // TOGGLE STATUS
    @can('leave_type.edit')
    $(document).on('click', '.toggle-status', function() {
        var id = $(this).data('id');
        $.post('/admin/leave-types/' + id + '/toggle-status', { _token: '{{ csrf_token() }}' }, function(r) {
            if (r.success) Swal.fire({ icon: 'success', title: trans.updated, text: r.message, timer: 1500 }).then(() => location.reload());
        });
    });
    @endcan

    // DELETE
    @can('leave_type.delete')
    $(document).on('click', '.delete-btn', function() {
        var id = $(this).data('id');
        $('#deleteForm').attr('action', '/admin/leave-types/' + id);
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
        $.ajax({ url: $(this).attr('action'), type: 'POST', data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
            success: function(r) {
                if (r.success) { $('#deleteModal').modal('hide'); Swal.fire({ icon: 'success', title: trans.deleted, text: r.message, timer: 2000 }).then(() => location.reload()); }
                else { btn.prop('disabled', false).html(`<i class="fas fa-trash me-1"></i> ${trans.delete}`); Swal.fire({ icon: 'error', title: trans.error_title, text: r.message || trans.failed_to_delete }); }
            },
            error: function(xhr) { btn.prop('disabled', false).html(`<i class="fas fa-trash me-1"></i> ${trans.delete}`); Swal.fire({ icon: 'error', title: trans.error_title, text: xhr.responseJSON?.message || trans.failed_to_delete }); }
        });
    });
    @endcan

    // FILTERS
    $('#searchBtn').on('click', filterTable);
    $('#searchInput').on('keypress', function(e) { if (e.which === 13) filterTable(); });
    $('#companyFilter, #statusFilter').on('change', filterTable);
    function filterTable() {
        var search = $('#searchInput').val().toLowerCase();
        var company = $('#companyFilter').val();
        var status = $('#statusFilter').val();
        $('#tableBody tr').each(function() {
            var row = $(this);
            var show = true;
            if (search && !row.text().toLowerCase().includes(search)) show = false;
            if (company && row.data('company') != company) show = false;
            if (status !== '' && row.data('status') != status) show = false;
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