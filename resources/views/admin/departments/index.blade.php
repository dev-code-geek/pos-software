@extends('layouts.admin')

@section('title', __('messages.departments'))
@section('page-title', __('messages.department_management'))

@section('admin-content')
<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ $stats['total'] ?? $departments->total() ?? 0 }}</h3>
                <p>{{ __('messages.total_departments') }}</p>
            </div>
            <div class="icon"><i class="fas fa-building"></i></div>
            <a href="{{ route('admin.departments.index') }}" class="small-box-footer">
                {{ __('messages.view_all') }} <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $stats['with_users'] ?? 0 }}</h3>
                <p>{{ __('messages.active_departments') }}</p>
            </div>
            <div class="icon"><i class="fas fa-users"></i></div>
            <a href="#" class="small-box-footer"><i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $stats['without_users'] ?? 0 }}</h3>
                <p>{{ __('messages.empty_departments') }}</p>
            </div>
            <div class="icon"><i class="fas fa-user-slash"></i></div>
            <a href="#" class="small-box-footer"><i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $departments->sum('users_count') }}</h3>
                <p>{{ __('messages.total_users') }}</p>
            </div>
            <div class="icon"><i class="fas fa-user-friends"></i></div>
            <a href="{{ route('admin.users.index') }}" class="small-box-footer">
                {{ __('messages.view_users') }} <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Main Card -->
<div class="card">
    <div class="card-header">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <h5 class="card-title mb-0">
                <i class="fas fa-building text-primary me-2"></i>
                {{ __('messages.all_departments') }}
                <span class="badge bg-primary ms-1">{{ $departments->total() }}</span>
            </h5>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <div class="input-group input-group-sm" style="width: 200px;">
                <input type="text" id="searchInput" class="form-control" placeholder="{{ __('messages.search') }}...">
                <button class="btn btn-secondary" type="button" id="searchBtn"><i class="fas fa-search"></i></button>
            </div>
            @can('department.create')
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fas fa-plus-circle me-1"></i> {{ __('messages.add_department') }}
            </button>
            @endcan
            @can('department.view')
            <a href="{{ route('admin.departments.export') }}" class="btn btn-success btn-sm">
                <i class="fas fa-file-excel me-1"></i> {{ __('messages.export') }}
            </a>
            @endcan
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 35px;">#</th>
                        <th style="min-width: 160px;">{{ __('messages.department') }}</th>
                        <th>{{ __('messages.head_of_department') }}</th>
                        <th>{{ __('messages.email') }}</th>
                        <th>{{ __('messages.phone') }}</th>
                        <th style="width: 80px; text-align: center;">{{ __('messages.users') }}</th>
                        <th style="width: 130px;">{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($departments as $department)
                    <tr data-id="{{ $department->id }}">
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle bg-primary text-white me-2">
                                    {{ strtoupper(substr($department->name, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="fw-bold small">{{ Str::limit($department->name, 25) }}</div>
                                    <small class="text-muted">ID: #{{ $department->id }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            @php
                                $headName = $department->head_of_department
                                    ? ($departmentHeads[$department->head_of_department] ?? __('messages.unknown'))
                                    : 'N/A';
                            @endphp
                            {{ $headName }}
                        </td>
                        <td>{{ $department->email ?? 'N/A' }}</td>
                        <td>{{ $department->phone ?? 'N/A' }}</td>
                        <td class="text-center">
                            <span class="badge bg-{{ $department->users_count > 0 ? 'success' : 'secondary' }}">
                                {{ $department->users_count ?? 0 }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                @can('department.view')
                                <button class="btn btn-outline-primary btn-sm view-btn" data-id="{{ $department->id }}" title="{{ __('messages.view') }}">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @endcan
                                @can('department.edit')
                                <button class="btn btn-outline-warning btn-sm edit-btn" data-id="{{ $department->id }}" title="{{ __('messages.edit') }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @endcan
                                @can('department.delete')
                                <button class="btn btn-outline-danger btn-sm delete-btn" data-id="{{ $department->id }}" title="{{ __('messages.delete') }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7">
                            <div class="text-center py-5">
                                <i class="fas fa-building fa-4x text-muted mb-3 d-block"></i>
                                <h5 class="fw-bold">{{ __('messages.no_departments_found') }}</h5>
                                <p class="text-muted">{{ __('messages.click_add_department') }}</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <span class="text-muted small">
                {{ __('messages.showing') }} {{ $departments->firstItem() ?? 0 }}
                {{ __('messages.to') }} {{ $departments->lastItem() ?? 0 }}
                {{ __('messages.of') }} {{ $departments->total() }}
                {{ __('messages.entries') }}
            </span>
        </div>
        <div>{{ $departments->links() }}</div>
    </div>
</div>

<!-- CREATE MODAL -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle text-primary me-2"></i> {{ __('messages.add_department') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createForm" action="{{ route('admin.departments.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div id="createFormErrors" class="alert alert-danger d-none"></div>
                    <div class="mb-3">
                        <label for="dept_name" class="form-label fw-bold">{{ __('messages.department_name') }} <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="dept_name" class="form-control" required placeholder="{{ __('messages.enter_department_name') }}">
                    </div>
                    <div class="mb-3">
                        <label for="dept_head" class="form-label fw-bold">{{ __('messages.head_of_department') }}</label>
                        <input type="text" name="head_of_department" id="dept_head" class="form-control" placeholder="{{ __('messages.enter_head_of_department') }}">
                    </div>
                    <div class="mb-3">
                        <label for="dept_email" class="form-label fw-bold">{{ __('messages.email') }}</label>
                        <input type="email" name="email" id="dept_email" class="form-control" placeholder="{{ __('messages.enter_department_email') }}">
                    </div>
                    <div class="mb-3">
                        <label for="dept_phone" class="form-label fw-bold">{{ __('messages.phone') }}</label>
                        <input type="text" name="phone" id="dept_phone" class="form-control" placeholder="{{ __('messages.enter_phone') }}">
                    </div>
                    <div class="mb-3">
                        <label for="dept_description" class="form-label fw-bold">{{ __('messages.description') }}</label>
                        <textarea name="description" id="dept_description" class="form-control" rows="2" placeholder="{{ __('messages.enter_department_description') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> {{ __('messages.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary" id="createSubmitBtn">
                        <i class="fas fa-save me-1"></i> {{ __('messages.save_department') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- VIEW MODAL -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-eye text-info me-2"></i> {{ __('messages.department_details') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewModalBody">
                <div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">{{ __('messages.loading') }}...</span></div></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.close') }}</button>
            </div>
        </div>
    </div>
</div>

<!-- EDIT MODAL -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit text-warning me-2"></i> {{ __('messages.edit_department') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body" id="editModalBody">
                    <div id="editFormErrors" class="alert alert-danger d-none"></div>
                    <div class="text-center py-5"><div class="spinner-border text-warning" role="status"><span class="visually-hidden">{{ __('messages.loading') }}...</span></div></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> {{ __('messages.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-warning" id="editSubmitBtn">
                        <i class="fas fa-save me-1"></i> {{ __('messages.update_department') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- DELETE MODAL -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger"><i class="fas fa-exclamation-triangle me-2"></i> {{ __('messages.confirm_delete') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-trash fa-4x text-danger mb-3 d-block"></i>
                <p class="fw-bold">{{ __('messages.are_you_sure_delete_department') }}</p>
                <p class="text-muted small">{{ __('messages.this_action_cannot_be_undone') }}</p>
                <form id="deleteForm" method="POST">@csrf @method('DELETE')</form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> {{ __('messages.cancel') }}
                </button>
                <button type="submit" class="btn btn-danger" id="deleteSubmitBtn" form="deleteForm">
                    <i class="fas fa-trash me-1"></i> {{ __('messages.delete') }}
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-circle { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 600; flex-shrink: 0; }
.small-box { border-radius: 12px; padding: 20px; margin-bottom: 0; position: relative; overflow: hidden; transition: transform 0.15s ease; }
.small-box:hover { transform: translateY(-2px); }
.small-box .inner { color: #fff; }
.small-box .inner h3 { font-size: 2rem; font-weight: 700; margin: 0 0 5px; }
.small-box .inner p { font-size: 0.9rem; opacity: 0.9; margin: 0; }
.small-box .icon { position: absolute; right: 15px; top: 15px; font-size: 2.5rem; opacity: 0.25; color: #fff; }
.small-box .small-box-footer { display: block; padding: 8px 0 0; color: rgba(255,255,255,0.8); text-decoration: none; font-size: 0.85rem; transition: all 0.15s ease; }
.small-box .small-box-footer:hover { color: #fff; }
.small-box .small-box-footer i { transition: transform 0.15s ease; }
.small-box:hover .small-box-footer i { transform: translateX(4px); }
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
    department_name: @json(__('messages.department_name')),
    head_of_department: @json(__('messages.head_of_department')),
    email: @json(__('messages.email')),
    phone: @json(__('messages.phone')),
    description: @json(__('messages.description')),
    users: @json(__('messages.users')),
    total_users: @json(__('messages.total_users')),
    request_statistics: @json(__('messages.request_statistics')),
    total: @json(__('messages.total')),
    pending: @json(__('messages.pending')),
    approved: @json(__('messages.approved')),
    recent_users: @json(__('messages.recent_users')),
    created: @json(__('messages.created')),
    created_alert: @json(__('messages.created_alert')),
    updated: @json(__('messages.updated')),
    deleted: @json(__('messages.deleted')),
    error_title: @json(__('messages.error')),
    something_went_wrong: @json(__('messages.something_went_wrong')),
    failed_to_load: @json(__('messages.failed_to_load')),
    failed_to_load_details: @json(__('messages.failed_to_load_details')),
    failed_to_create: @json(__('messages.failed_to_create')),
    failed_to_update: @json(__('messages.failed_to_update')),
    failed_to_delete: @json(__('messages.failed_to_delete')),
    cannot_delete: @json(__('messages.cannot_delete')),
    department_has_users: @json(__('messages.department_has_users')),
    are_you_sure: @json(__('messages.are_you_sure')),
    wont_be_able_to_revert: @json(__('messages.wont_be_able_to_revert')),
    yes_delete: @json(__('messages.yes_delete')),
    saving: @json(__('messages.saving')),
    updating: @json(__('messages.updating')),
    deleting: @json(__('messages.deleting')),
    save_department: @json(__('messages.save_department')),
    update_department: @json(__('messages.update_department')),
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
    @can('department.view')
    $(document).on('click', '.view-btn', function() {
        var id = $(this).data('id');
        var body = $('#viewModalBody');
        body.html(`<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">${trans.loading}...</span></div></div>`);
        $('#viewModal').modal('show');
        $.ajax({
            url: '/admin/departments/' + id, type: 'GET', dataType: 'json',
            success: function(r) {
                var department = r.department || r;
                var headName = r.head_name || department.head_of_department || 'N/A';
                var requestStats = r.request_stats || { total: 0, pending: 0, approved: 0 };
                var recentUsers = r.recent_users || [];
                var html = `
                    <div class="row g-3">
                        <div class="col-12"><label class="text-muted small fw-bold">${trans.department_name}</label><p class="fw-bold mb-0">${department.name}</p></div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">${trans.head_of_department}</label><p class="mb-0">${headName}</p></div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">${trans.email}</label><p class="mb-0">${department.email || 'N/A'}</p></div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">${trans.phone}</label><p class="mb-0">${department.phone || 'N/A'}</p></div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">${trans.total_users}</label><p class="mb-0"><span class="badge bg-primary">${department.users_count || 0}</span> ${trans.users}</p></div>
                        ${department.description ? `<div class="col-12"><label class="text-muted small fw-bold">${trans.description}</label><p class="mb-0">${department.description}</p></div>` : ''}
                        <div class="col-12">
                            <hr>
                            <h6 class="fw-bold">${trans.request_statistics}</h6>
                            <div class="row">
                                <div class="col-md-4"><div class="text-center p-2 bg-light rounded"><span class="d-block fw-bold">${requestStats.total || 0}</span><small class="text-muted">${trans.total}</small></div></div>
                                <div class="col-md-4"><div class="text-center p-2 bg-warning bg-opacity-10 rounded"><span class="d-block fw-bold text-warning">${requestStats.pending || 0}</span><small class="text-muted">${trans.pending}</small></div></div>
                                <div class="col-md-4"><div class="text-center p-2 bg-success bg-opacity-10 rounded"><span class="d-block fw-bold text-success">${requestStats.approved || 0}</span><small class="text-muted">${trans.approved}</small></div></div>
                            </div>
                        </div>
                        ${recentUsers.length > 0 ? `
                        <div class="col-12">
                            <hr>
                            <h6 class="fw-bold">${trans.recent_users}</h6>
                            <ul class="list-unstyled small">
                                ${recentUsers.map(function(u) {
                                    return `<li><i class="fas fa-user text-primary me-1"></i> ${u.name} (${u.email})</li>`;
                                }).join('')}
                            </ul>
                        </div>` : ''}
                        <div class="col-12">
                            <hr>
                            <label class="text-muted small fw-bold">${trans.created}</label>
                            <p class="mb-0">${new Date(department.created_at).toLocaleString()}</p>
                        </div>
                    </div>
                `;
                body.html(html);
            },
            error: function(xhr) {
                body.html(`<div class="text-center py-5 text-danger"><i class="fas fa-exclamation-circle fa-4x mb-3 d-block"></i><h5>${trans.failed_to_load_details}</h5><p>${xhr.status} - ${xhr.statusText}</p></div>`);
            }
        });
    });
    @endcan

    // EDIT
    @can('department.edit')
    $(document).on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        var body = $('#editModalBody');
        var form = $('#editForm');
        form.attr('action', '/admin/departments/' + id);
        body.html(`<div id="editFormErrors" class="alert alert-danger d-none"></div><div class="text-center py-5"><div class="spinner-border text-warning" role="status"><span class="visually-hidden">${trans.loading}...</span></div></div>`);
        $('#editModal').modal('show');
        $.ajax({
            url: '/admin/departments/' + id + '/edit', type: 'GET', dataType: 'json',
            success: function(r) {
                body.html(`
                    <div id="editFormErrors" class="alert alert-danger d-none"></div>
                    <div class="mb-3"><label for="edit_name" class="form-label fw-bold">${trans.department_name} <span class="text-danger">*</span></label><input type="text" name="name" id="edit_name" class="form-control" value="${r.name || ''}" required></div>
                    <div class="mb-3"><label for="edit_head" class="form-label fw-bold">${trans.head_of_department}</label><input type="text" name="head_of_department" id="edit_head" class="form-control" value="${r.head_of_department || ''}"></div>
                    <div class="mb-3"><label for="edit_email" class="form-label fw-bold">${trans.email}</label><input type="email" name="email" id="edit_email" class="form-control" value="${r.email || ''}"></div>
                    <div class="mb-3"><label for="edit_phone" class="form-label fw-bold">${trans.phone}</label><input type="text" name="phone" id="edit_phone" class="form-control" value="${r.phone || ''}"></div>
                    <div class="mb-3"><label for="edit_description" class="form-label fw-bold">${trans.description}</label><textarea name="description" id="edit_description" class="form-control" rows="2">${r.description || ''}</textarea></div>
                `);
            },
            error: function(xhr) {
                body.html(`<div id="editFormErrors" class="alert alert-danger d-none"></div><div class="text-center py-5 text-danger"><i class="fas fa-exclamation-circle fa-4x mb-3 d-block"></i><h5>${trans.failed_to_load}</h5><p>${xhr.status} - ${xhr.statusText}</p></div>`);
            }
        });
    });
    @endcan

    // CREATE
    @can('department.create')
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
                    Swal.fire({ icon: 'success', title: trans.created_alert, text: r.message, timer: 2000, showConfirmButton: true }).then(() => window.location.reload());
                } else {
                    btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.save_department}`);
                    Swal.fire({ icon: 'error', title: trans.error_title, text: r.message || trans.something_went_wrong });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.save_department}`);
                if (xhr.status === 422) showErrors(errorContainer, xhr.responseJSON.errors);
                else Swal.fire({ icon: 'error', title: trans.error_title, text: trans.failed_to_create });
            }
        });
    });
    @endcan

    // UPDATE
    @can('department.edit')
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
                    Swal.fire({ icon: 'success', title: trans.updated, text: r.message, timer: 2000, showConfirmButton: true }).then(() => window.location.reload());
                } else {
                    btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.update_department}`);
                    Swal.fire({ icon: 'error', title: trans.error_title, text: r.message || trans.something_went_wrong });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.update_department}`);
                if (xhr.status === 422) showErrors(errorContainer, xhr.responseJSON.errors);
                else Swal.fire({ icon: 'error', title: trans.error_title, text: trans.failed_to_update });
            }
        });
    });
    @endcan

    // DELETE
    @can('department.delete')
    $(document).on('click', '.delete-btn', function() {
        var id = $(this).data('id');
        $('#deleteForm').attr('action', '/admin/departments/' + id);
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
            data: { _token: '{{ csrf_token() }}', _method: 'DELETE' }, dataType: 'json',
            success: function(r) {
                if (r.success) {
                    $('#deleteModal').modal('hide');
                    Swal.fire({ icon: 'success', title: trans.deleted, text: r.message, timer: 2000, showConfirmButton: true }).then(() => window.location.reload());
                } else {
                    btn.prop('disabled', false).html(`<i class="fas fa-trash me-1"></i> ${trans.delete}`);
                    Swal.fire({ icon: 'error', title: trans.error_title, text: r.message || trans.failed_to_delete });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(`<i class="fas fa-trash me-1"></i> ${trans.delete}`);
                if (xhr.status === 422) {
                    Swal.fire({ icon: 'error', title: trans.cannot_delete, text: xhr.responseJSON.error || trans.department_has_users });
                } else {
                    Swal.fire({ icon: 'error', title: trans.error_title, text: trans.failed_to_delete });
                }
            }
        });
    });
    @endcan

    // SEARCH
    $('#searchBtn').on('click', filterTable);
    $('#searchInput').on('keypress', function(e) { if (e.which === 13) filterTable(); });
    function filterTable() {
        var search = $('#searchInput').val().toLowerCase();
        $('#tableBody tr').each(function() {
            var row = $(this);
            row.toggle(row.text().toLowerCase().includes(search));
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