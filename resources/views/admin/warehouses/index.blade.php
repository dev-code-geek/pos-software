@extends('layouts.admin')

@section('title', __('messages.warehouses'))
@section('page-title', __('messages.warehouse_management'))

@section('admin-content')
<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ number_format($stats['total'] ?? 0) }}</h3>
                <p>{{ __('messages.total_warehouses') }}</p>
            </div>
            <div class="icon"><i class="fas fa-warehouse"></i></div>
            <a href="{{ route('admin.warehouses.index') }}" class="small-box-footer">
                {{ __('messages.view_all') }} <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ number_format($stats['active'] ?? 0) }}</h3>
                <p>{{ __('messages.active') }}</p>
            </div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
            <a href="{{ route('admin.warehouses.index', ['status' => 'active']) }}" class="small-box-footer">
                {{ __('messages.view_active') }} <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="small-box bg-secondary">
            <div class="inner">
                <h3>{{ number_format($stats['inactive'] ?? 0) }}</h3>
                <p>{{ __('messages.inactive') }}</p>
            </div>
            <div class="icon"><i class="fas fa-pause-circle"></i></div>
            <a href="{{ route('admin.warehouses.index', ['status' => 'inactive']) }}" class="small-box-footer">
                {{ __('messages.view_inactive') }} <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ number_format($stats['with_products'] ?? 0) }}</h3>
                <p>{{ __('messages.with_products') }}</p>
            </div>
            <div class="icon"><i class="fas fa-boxes"></i></div>
            <a href="#" class="small-box-footer">
                {{ __('messages.view_details') }} <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Main Card -->
<div class="card">
    <div class="card-header">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <h5 class="card-title mb-0">
                <i class="fas fa-warehouse text-primary me-2"></i>
                {{ __('messages.all_warehouses') }}
                <span class="badge bg-primary ms-1">{{ $warehouses->total() }}</span>
            </h5>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <div class="input-group input-group-sm" style="width:200px;">
                <input type="text" id="searchInput" class="form-control" placeholder="{{ __('messages.search') }}...">
                <button class="btn btn-secondary" type="button" id="searchBtn"><i class="fas fa-search"></i></button>
            </div>
            <select id="statusFilter" class="form-select form-select-sm" style="width:130px;">
                <option value="">{{ __('messages.all_status') }}</option>
                <option value="active">{{ __('messages.active') }}</option>
                <option value="inactive">{{ __('messages.inactive') }}</option>
            </select>
            @can('warehouse.create')
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fas fa-plus-circle me-1"></i> {{ __('messages.add_warehouse') }}
            </button>
            @endcan
            @can('warehouse.export')
            <button class="btn btn-success btn-sm" id="exportBtn">
                <i class="fas fa-file-excel me-1"></i> {{ __('messages.export') }}
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
                        <th style="min-width:150px;">{{ __('messages.name') }}</th>
                        <th>{{ __('messages.code') }}</th>
                        <th>{{ __('messages.city') }}</th>
                        <th>{{ __('messages.manager') }}</th>
                        <th style="width:80px;">{{ __('messages.status') }}</th>
                        <th style="width:180px;">{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($warehouses as $warehouse)
                    <tr data-id="{{ $warehouse->id }}" data-status="{{ $warehouse->is_active ? 'active' : 'inactive' }}">
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle bg-primary text-white me-2">
                                    {{ strtoupper(substr($warehouse->name, 0, 2)) }}
                                </div>
                                <div class="fw-bold small">{{ Str::limit($warehouse->name, 25) }}</div>
                            </div>
                        </td>
                        <td><span class="badge bg-secondary">{{ $warehouse->code }}</span></td>
                        <td>{{ $warehouse->city ?? 'N/A' }}</td>
                        <td>{{ $warehouse->manager->name ?? 'N/A' }}</td>
                        <td>
                            @if($warehouse->is_active)
                                <span class="badge bg-success">{{ __('messages.active') }}</span>
                            @else
                                <span class="badge bg-secondary">{{ __('messages.inactive') }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                @can('warehouse.view')
                                <button class="btn btn-outline-primary btn-sm view-btn" data-id="{{ $warehouse->id }}" title="{{ __('messages.view') }}">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @endcan
                                @can('warehouse.edit')
                                <button class="btn btn-outline-warning btn-sm edit-btn" data-id="{{ $warehouse->id }}" title="{{ __('messages.edit') }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @endcan
                                @can('warehouse.toggle_status')
                                <button class="btn btn-outline-{{ $warehouse->is_active ? 'secondary' : 'success' }} btn-sm toggle-status-btn"
                                        data-id="{{ $warehouse->id }}"
                                        title="{{ $warehouse->is_active ? __('messages.deactivate') : __('messages.activate') }}">
                                    <i class="fas {{ $warehouse->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                                </button>
                                @endcan
                                @can('warehouse.delete')
                                <button class="btn btn-outline-danger btn-sm delete-btn" data-id="{{ $warehouse->id }}" title="{{ __('messages.delete') }}">
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
                                <i class="fas fa-warehouse fa-4x text-muted mb-3 d-block"></i>
                                <h5 class="fw-bold">{{ __('messages.no_warehouses_found') }}</h5>
                                <p class="text-muted">{{ __('messages.click_add_warehouse') }}</p>
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
                {{ __('messages.showing') }} {{ $warehouses->firstItem() ?? 0 }}
                {{ __('messages.to') }} {{ $warehouses->lastItem() ?? 0 }}
                {{ __('messages.of') }} {{ $warehouses->total() }}
                {{ __('messages.entries') }}
            </span>
        </div>
        <div>{{ $warehouses->links() }}</div>
    </div>
</div>

{{-- CREATE MODAL --}}
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:600px;">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i> {{ __('messages.add_warehouse') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="createForm" action="{{ route('admin.warehouses.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div id="createFormErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold">{{ __('messages.name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">{{ __('messages.code') }} <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">{{ __('messages.address') }}</label>
                            <textarea name="address" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">{{ __('messages.city') }}</label>
                            <input type="text" name="city" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">{{ __('messages.state') }}</label>
                            <input type="text" name="state" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">{{ __('messages.country') }}</label>
                            <input type="text" name="country" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.manager') }}</label>
                            <select name="manager_id" class="form-select">
                                <option value="">{{ __('messages.select_manager') }}</option>
                                @foreach($managers ?? [] as $manager)
                                    <option value="{{ $manager->id }}">{{ $manager->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
                                <label class="form-check-label">{{ __('messages.active') }}</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> {{ __('messages.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary" id="createSubmitBtn">
                        <i class="fas fa-save me-1"></i> {{ __('messages.save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- VIEW MODAL --}}
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:600px;">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-eye me-2"></i> {{ __('messages.warehouse_details') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewModalBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.close') }}</button>
            </div>
        </div>
    </div>
</div>

{{-- EDIT MODAL --}}
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:600px;">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i> {{ __('messages.edit_warehouse') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body" id="editModalBody">
                    <div id="editFormErrors" class="alert alert-danger d-none"></div>
                    <div class="text-center py-5">
                        <div class="spinner-border text-warning"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> {{ __('messages.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-warning" id="editSubmitBtn">
                        <i class="fas fa-save me-1"></i> {{ __('messages.update') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- DELETE MODAL --}}
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:560px;">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i> {{ __('messages.confirm_delete') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-trash fa-4x text-danger mb-3 d-block"></i>
                <p class="fw-bold">{{ __('messages.are_you_sure_delete_warehouse') }}</p>
                <p class="text-muted small">{{ __('messages.this_action_cannot_be_undone') }}</p>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                </form>
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
.avatar-circle { width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:600; }
.small-box { border-radius:12px; padding:20px; position:relative; overflow:hidden; }
.small-box .inner { color:#fff; }
.small-box .inner h3 { font-size:2rem; font-weight:700; margin:0 0 5px; }
.small-box .inner p { font-size:0.9rem; opacity:0.9; margin:0; }
.small-box .icon { position:absolute; right:15px; top:15px; font-size:2.5rem; opacity:0.25; color:#fff; }
.small-box .small-box-footer { display:block; padding:8px 0 0; color:rgba(255,255,255,0.8); text-decoration:none; font-size:0.85rem; }
</style>

@push('scripts')
<script>
window.trans = {
    loading: @json(__('messages.loading')),
    close: @json(__('messages.close')),
    cancel: @json(__('messages.cancel')),
    save: @json(__('messages.save')),
    update: @json(__('messages.update')),
    delete: @json(__('messages.delete')),
    edit: @json(__('messages.edit')),
    view: @json(__('messages.view')),
    active: @json(__('messages.active')),
    inactive: @json(__('messages.inactive')),
    name: @json(__('messages.name')),
    code: @json(__('messages.code')),
    city: @json(__('messages.city')),
    manager: @json(__('messages.manager')),
    address: @json(__('messages.address')),
    state: @json(__('messages.state')),
    country: @json(__('messages.country')),
    status: @json(__('messages.status')),
    confirm_delete: @json(__('messages.confirm_delete')),
    are_you_sure: @json(__('messages.are_you_sure')),
    this_action_cannot_be_undone: @json(__('messages.this_action_cannot_be_undone')),
    warehouse_details: @json(__('messages.warehouse_details')),
    edit_warehouse: @json(__('messages.edit_warehouse')),
    select_manager: @json(__('messages.select_manager')),
    deactivate: @json(__('messages.deactivate')),
    activate: @json(__('messages.activate')),
    no_items_selected: @json(__('messages.no_items_selected') ?? 'No items selected'),
};

$(document).ready(function() {
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    function showErrors(container, errors) {
        container.removeClass('d-none');
        let html = '<ul class="mb-0">';
        $.each(errors, function(key, messages) {
            $.each(messages, function(i, msg) { html += `<li>${msg}</li>`; });
        });
        html += '</ul>';
        container.html(html);
    }

    function clearErrors(container) {
        container.addClass('d-none').empty();
    }

    // ====================== VIEW ======================
    @can('warehouse.view')
    $(document).on('click', '.view-btn', function() {
        const id = $(this).data('id');
        const body = $('#viewModalBody');
        body.html(`<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>`);
        $('#viewModal').modal('show');

        $.get('/admin/warehouses/' + id, function(r) {
            body.html(`
                <div class="row g-3">
                    <div class="col-md-6"><label class="text-muted small fw-bold">${trans.name}</label><p class="fw-bold mb-0">${r.name}</p></div>
                    <div class="col-md-6"><label class="text-muted small fw-bold">${trans.code}</label><p class="mb-0"><span class="badge bg-secondary">${r.code}</span></p></div>
                    <div class="col-12"><label class="text-muted small fw-bold">${trans.address}</label><p class="mb-0">${r.address || 'N/A'}</p></div>
                    <div class="col-md-4"><label class="text-muted small fw-bold">${trans.city}</label><p class="mb-0">${r.city || 'N/A'}</p></div>
                    <div class="col-md-4"><label class="text-muted small fw-bold">${trans.state}</label><p class="mb-0">${r.state || 'N/A'}</p></div>
                    <div class="col-md-4"><label class="text-muted small fw-bold">${trans.country}</label><p class="mb-0">${r.country || 'N/A'}</p></div>
                    <div class="col-md-6"><label class="text-muted small fw-bold">${trans.manager}</label><p class="mb-0">${r.manager ? r.manager.name : 'N/A'}</p></div>
                    <div class="col-md-6"><label class="text-muted small fw-bold">${trans.status}</label>
                        <p class="mb-0">${r.is_active ? '<span class="badge bg-success">' + trans.active + '</span>' : '<span class="badge bg-secondary">' + trans.inactive + '</span>'}</p>
                    </div>
                </div>
            `);
        }).fail(function() {
            body.html(`<div class="text-center py-5 text-danger"><h5>Failed to load</h5></div>`);
        });
    });
    @endcan

    // ====================== EDIT ======================
    @can('warehouse.edit')
    $(document).on('click', '.edit-btn', function() {
        const id = $(this).data('id');
        const body = $('#editModalBody');
        const form = $('#editForm');

        form.attr('action', '/admin/warehouses/' + id);
        body.html(`<div id="editFormErrors" class="alert alert-danger d-none"></div><div class="text-center py-5"><div class="spinner-border text-warning"></div></div>`);
        $('#editModal').modal('show');

        $.get('/admin/warehouses/' + id + '/edit', function(response) {
            const warehouse = response.warehouse || response;
            const managers = response.managers || [];

            let managerOptions = `<option value="">${trans.select_manager}</option>`;
            managers.forEach(function(m) {
                managerOptions += `<option value="${m.id}" ${warehouse.manager_id == m.id ? 'selected' : ''}>${m.name}</option>`;
            });

            body.html(`
                <div id="editFormErrors" class="alert alert-danger d-none"></div>
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-bold">${trans.name} <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="${warehouse.name || ''}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">${trans.code} <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control" value="${warehouse.code || ''}" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">${trans.address}</label>
                        <textarea name="address" class="form-control" rows="2">${warehouse.address || ''}</textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">${trans.city}</label>
                        <input type="text" name="city" class="form-control" value="${warehouse.city || ''}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">${trans.state}</label>
                        <input type="text" name="state" class="form-control" value="${warehouse.state || ''}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">${trans.country}</label>
                        <input type="text" name="country" class="form-control" value="${warehouse.country || ''}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">${trans.manager}</label>
                        <select name="manager_id" class="form-select">${managerOptions}</select>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check form-switch mt-4">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" ${warehouse.is_active ? 'checked' : ''}>
                            <label class="form-check-label">${trans.active}</label>
                        </div>
                    </div>
                </div>
            `);
        });
    });

    $('#editForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const btn = $('#editSubmitBtn');
        const errorContainer = $('#editFormErrors');

        clearErrors(errorContainer);
        btn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm me-1"></span> ${trans.loading}...`);

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    $('#editModal').modal('hide');
                    Swal.fire({ icon: 'success', title: 'Updated!', timer: 1500 }).then(() => location.reload());
                } else {
                    btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.update}`);
                    if (response.errors) showErrors(errorContainer, response.errors);
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.update}`);
                if (xhr.status === 422) showErrors(errorContainer, xhr.responseJSON.errors);
            }
        });
    });
    @endcan

    // ====================== CREATE ======================
    @can('warehouse.create')
    $('#createForm').on('submit', function(e) {
        e.preventDefault();
        const btn = $('#createSubmitBtn');
        const errorContainer = $('#createFormErrors');
        clearErrors(errorContainer);
        btn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm me-1"></span> ${trans.loading}...`);

        $.post($(this).attr('action'), $(this).serialize())
            .done(function(response) {
                if (response.success) {
                    $('#createModal').modal('hide');
                    Swal.fire({ icon: 'success', title: 'Added!', timer: 1500 }).then(() => location.reload());
                } else {
                    btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.save}`);
                    if (response.errors) showErrors(errorContainer, response.errors);
                }
            })
            .fail(function(xhr) {
                btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.save}`);
                if (xhr.status === 422) showErrors(errorContainer, xhr.responseJSON.errors);
            });
    });
    @endcan

    // ====================== TOGGLE STATUS ======================
    @can('warehouse.toggle_status')
    $(document).on('click', '.toggle-status-btn', function() {
        const id = $(this).data('id');

        Swal.fire({
            title: 'Toggle Status?',
            text: 'Are you sure?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: trans.cancel
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('/admin/warehouses/' + id + '/toggle-status', { _token: '{{ csrf_token() }}' })
                    .done(function(response) {
                        if (response.success) {
                            Swal.fire({ icon: 'success', title: 'Updated!', timer: 1200 }).then(() => location.reload());
                        }
                    });
            }
        });
    });
    @endcan

    // ====================== DELETE ======================
    @can('warehouse.delete')
    $(document).on('click', '.delete-btn', function() {
        const id = $(this).data('id');
        $('#deleteForm').attr('action', '/admin/warehouses/' + id);

        Swal.fire({
            title: trans.confirm_delete,
            text: trans.are_you_sure,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonText: trans.cancel,
            confirmButtonText: 'Yes, delete!'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#deleteModal').modal('show');
            }
        });
    });

    $('#deleteForm').on('submit', function(e) {
        e.preventDefault();
        const btn = $('#deleteSubmitBtn');
        btn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm me-1"></span> ${trans.loading}...`);

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
            success: function(response) {
                if (response.success) {
                    $('#deleteModal').modal('hide');
                    Swal.fire({ icon: 'success', title: 'Deleted!', timer: 1500 }).then(() => location.reload());
                } else {
                    btn.prop('disabled', false).html(`<i class="fas fa-trash me-1"></i> ${trans.delete}`);
                    Swal.fire({ icon: 'error', title: 'Error', text: response.message });
                }
            },
            error: function() {
                btn.prop('disabled', false).html(`<i class="fas fa-trash me-1"></i> ${trans.delete}`);
            }
        });
    });
    @endcan

    // ====================== SEARCH & FILTER ======================
    $('#searchBtn').on('click', filterTable);
    $('#searchInput').on('keypress', function(e) { if (e.which === 13) filterTable(); });
    $('#statusFilter').on('change', filterTable);

    function filterTable() {
        const search = $('#searchInput').val().toLowerCase();
        const status = $('#statusFilter').val();

        $('#tableBody tr').each(function() {
            const row = $(this);
            let show = true;
            if (search && !row.text().toLowerCase().includes(search)) show = false;
            if (status && row.data('status') !== status) show = false;
            row.toggle(show);
        });
    }

    // Modal cleanup
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