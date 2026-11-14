@extends('layouts.admin')

@section('title', 'Roles')
@section('page-title', 'Role Management')

@section('admin-content')
{{--
    Permissions are grouped right here in the view, by the prefix of each
    permission's name (e.g. "users.view", "users.create" -> section "Users").
    This does NOT depend on a group_name database column — it works purely
    off however $permissions arrives from the controller, whether that's
    already grouped, flat, or grouped by a null/empty group_name.

    Convention expected: permission names like "section.action"
    (users.view, products.create, departments.edit, etc.). If a permission
    name has no separator, it falls into a "General" section.
--}}
@php
    // Normalize whatever shape $permissions arrives in (grouped collection
    // or flat collection) down to one flat list of permission models.
    $flatPermissions = $permissions instanceof \Illuminate\Support\Collection && $permissions->first() instanceof \Illuminate\Support\Collection
        ? $permissions->flatten()
        : (is_iterable($permissions) ? collect($permissions)->flatten() : collect());

    $groupedPermissions = $flatPermissions->groupBy(function ($perm) {
        $parts = preg_split('/[._\-\s]+/', $perm->name);
        $section = $parts[0] ?? 'general';
        return ucfirst($section);
    })->sortKeys();
@endphp
<!-- Stats Cards -->
{{-- FIX: stats now come from the controller ($stats), computed against
     the full roles/permissions tables — not by filtering/summing the
     paginated $roles collection (which only ever holds 15 records). --}}
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ $stats['total'] }}</h3>
                <p>Total Roles</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-shield"></i>
            </div>
            <a href="#" class="small-box-footer">
                <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $stats['total_users_with_roles'] }}</h3>
                <p>Total Users with Roles</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
            <a href="#" class="small-box-footer">
                <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $stats['custom_roles'] }}</h3>
                <p>Custom Roles</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-tag"></i>
            </div>
            <a href="#" class="small-box-footer">
                <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $stats['total_permissions'] }}</h3>
                <p>Total Permissions</p>
            </div>
            <div class="icon">
                <i class="fas fa-key"></i>
            </div>
            <a href="#" class="small-box-footer">
                <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Main Card -->
<div class="card">
    <div class="card-header">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <h5 class="card-title mb-0">
                <i class="fas fa-user-shield text-primary me-2"></i>
                All Roles
                <span class="badge bg-primary ms-1">{{ $roles->total() }}</span>
            </h5>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <div class="input-group input-group-sm" style="width: 200px;">
                <input type="text" id="searchInput" class="form-control" placeholder="Search...">
                <button class="btn btn-secondary" type="button" id="searchBtn">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fas fa-plus-circle me-1"></i> Add Role
            </button>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 35px;">#</th>
                        <th style="min-width: 160px;">Role Name</th>
                        <th style="min-width: 120px;">Permissions</th>
                        <th style="width: 100px;">Users</th>
                        <th style="width: 100px;">Guard</th>
                        <th style="width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($roles as $role)
                    <tr data-id="{{ $role->id }}">
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle bg-primary text-white me-2">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <div>
                                    <div class="fw-bold small">{{ $role->name }}</div>
                                    <small class="text-muted">ID: #{{ $role->id }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-info">{{ $role->permissions->count() }}</span>
                            <button class="btn btn-sm btn-outline-info view-permissions-btn" data-id="{{ $role->id }}" title="View Permissions">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                        <td>
                            {{-- FIX: uses $role->users_count from withCount() in the
                                 controller instead of $role->users->count(), which
                                 fired one extra query per row (N+1). --}}
                            <span class="badge bg-primary">{{ $role->users_count }}</span>
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $role->guard_name }}</span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <button class="btn btn-outline-warning edit-btn" data-id="{{ $role->id }}" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @if($role->name !== 'super_admin')
                                <button class="btn btn-outline-danger delete-btn" data-id="{{ $role->id }}" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">
                            <div class="text-center py-5">
                                <i class="fas fa-user-shield fa-4x text-muted mb-3 d-block"></i>
                                <h5 class="fw-bold">No Roles Found</h5>
                                <p class="text-muted">Click "Add Role" to create your first role.</p>
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
                Showing {{ $roles->firstItem() ?? 0 }} to {{ $roles->lastItem() ?? 0 }} of {{ $roles->total() }} entries
            </span>
        </div>
        <div>
            {{ $roles->links() }}
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- CREATE MODAL (Centered, Scrollable, Medium) -->
<!-- ============================================ -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 640px;">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle text-primary me-2"></i> Create Role
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createForm" action="{{ route('admin.roles.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="role_name" class="form-label fw-bold">Role Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="role_name" class="form-control" required placeholder="Enter role name">
                    </div>

                    <div class="mb-2 d-flex justify-content-between align-items-center">
                        <label class="form-label fw-bold mb-0">Permissions</label>
                        <div class="form-check form-switch mb-0">
                            <input type="checkbox" id="create_master_select_all" class="form-check-input master-select-all">
                            <label class="form-check-label small fw-bold" for="create_master_select_all">Select All Sections</label>
                        </div>
                    </div>

                    <div class="permission-groups" style="max-height: 360px; overflow-y: auto;">
                        @forelse($groupedPermissions as $groupName => $perms)
                            @php $groupSlug = \Illuminate\Support\Str::slug($groupName ?: 'general'); @endphp
                            <div class="card mb-2 group-container" data-group="{{ $groupName }}">
                                <div class="card-header py-2 px-3 d-flex justify-content-between align-items-center bg-light">
                                    <div class="form-check mb-0">
                                        <input type="checkbox" class="form-check-input group-select-all" data-group="{{ $groupName }}" id="create_group_{{ $groupSlug }}">
                                        <label class="form-check-label fw-bold small text-uppercase" for="create_group_{{ $groupSlug }}">
                                            {{ $groupName ?: 'General' }}
                                        </label>
                                    </div>
                                    <span class="badge bg-secondary">{{ $perms->count() }}</span>
                                </div>
                                <div class="card-body py-2 px-3">
                                    <div class="row g-1">
                                        @foreach($perms as $perm)
                                            <div class="col-md-6">
                                                <div class="form-check">
                                                    <input type="checkbox" name="permissions[]" value="{{ $perm->id }}" class="form-check-input permission-checkbox" data-group="{{ $groupName }}" id="create_perm_{{ $perm->id }}">
                                                    <label class="form-check-label small" for="create_perm_{{ $perm->id }}">{{ $perm->name }}</label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted small mb-0">No permissions available yet. Create some under Permission Management first.</p>
                        @endforelse
                    </div>
                    <small class="text-muted">Tick a section's "Select All" to grant every permission in that section, or the top switch for everything.</small>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="createSubmitBtn">
                        <i class="fas fa-save me-1"></i> Create Role
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- EDIT MODAL (Centered, Scrollable, Medium) -->
<!-- ============================================ -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 640px;">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">
                    <i class="fas fa-edit text-warning me-2"></i> Edit Role
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body" id="editModalBody">
                    <div class="text-center py-5">
                        <div class="spinner-border text-warning" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-warning" id="editSubmitBtn">
                        <i class="fas fa-save me-1"></i> Update Role
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- VIEW PERMISSIONS MODAL (Centered, Scrollable, Medium) -->
<!-- ============================================ -->
<div class="modal fade" id="viewPermissionsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">
                    <i class="fas fa-key text-info me-2"></i> Role Permissions
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewPermissionsBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-info" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // ==========================================
    // CSRF Setup
    // ==========================================
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // ==========================================
    // PERMISSION GROUP LOGIC
    // Handlers are bound ONCE here via delegation on `document`, rather
    // than being re-attached every time a modal fires `shown.bs.modal`.
    // Delegation off `document` also means it "just works" for the Edit
    // modal's dynamically-injected checkboxes with no extra wiring.
    // ==========================================
    function syncGroupState($container, group) {
        var $boxes = $container.find('.permission-checkbox[data-group="' + group + '"]');
        var checkedCount = $boxes.filter(':checked').length;
        var $groupAll = $container.find('.group-select-all[data-group="' + group + '"]');
        $groupAll.prop('checked', checkedCount > 0 && checkedCount === $boxes.length);
        $groupAll.prop('indeterminate', checkedCount > 0 && checkedCount < $boxes.length);
    }

    function syncMasterState($container) {
        var $boxes = $container.find('.permission-checkbox');
        if ($boxes.length === 0) return;
        var checkedCount = $boxes.filter(':checked').length;
        var $master = $container.find('.master-select-all');
        $master.prop('checked', checkedCount === $boxes.length);
        $master.prop('indeterminate', checkedCount > 0 && checkedCount < $boxes.length);
    }

    function syncAllStates($container) {
        $container.find('.group-select-all').each(function() {
            syncGroupState($container, $(this).data('group'));
        });
        syncMasterState($container);
    }

    // Master "Select All Sections" toggle
    $(document).on('change', '.master-select-all', function() {
        var $container = $(this).closest('.modal-body');
        var checked = $(this).prop('checked');
        $(this).prop('indeterminate', false);
        $container.find('.permission-checkbox, .group-select-all')
            .prop('checked', checked)
            .prop('indeterminate', false);
    });

    // Per-section "Select All" toggle
    $(document).on('change', '.group-select-all', function() {
        var $container = $(this).closest('.modal-body');
        var group = $(this).data('group');
        var checked = $(this).prop('checked');
        $(this).prop('indeterminate', false);
        $container.find('.permission-checkbox[data-group="' + group + '"]').prop('checked', checked);
        syncMasterState($container);
    });

    // Individual permission checkbox
    $(document).on('change', '.permission-checkbox', function() {
        var $container = $(this).closest('.modal-body');
        var group = $(this).data('group');
        syncGroupState($container, group);
        syncMasterState($container);
    });

    // ==========================================
    // VIEW PERMISSIONS (grouped by section)
    // ==========================================
    $(document).on('click', '.view-permissions-btn', function() {
        var id = $(this).data('id');
        var body = $('#viewPermissionsBody');

        body.html(`
            <div class="text-center py-5">
                <div class="spinner-border text-info" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `);
        $('#viewPermissionsModal').modal('show');

        $.ajax({
            url: '/admin/roles/' + id,
            type: 'GET',
            dataType: 'json',
            success: function(r) {
                var groupsHtml = '';

                if (r.permissions && r.permissions.length) {
                    // Derive section from the permission name prefix
                    // (e.g. "users.view" -> "Users"), matching the same
                    // logic used to build the Create/Edit modal sections.
                    var grouped = {};
                    r.permissions.forEach(function(perm) {
                        var parts = perm.name.split(/[._\-\s]+/);
                        var key = parts[0] ? (parts[0].charAt(0).toUpperCase() + parts[0].slice(1)) : 'General';
                        if (!grouped[key]) grouped[key] = [];
                        grouped[key].push(perm.name);
                    });

                    Object.keys(grouped).sort().forEach(function(group) {
                        var badges = grouped[group].map(function(name) {
                            return `<span class="badge bg-primary me-1 mb-1">${name}</span>`;
                        }).join('');
                        groupsHtml += `
                            <div class="mb-2">
                                <div class="text-muted small fw-bold text-uppercase mb-1">${group}</div>
                                <div>${badges}</div>
                            </div>
                        `;
                    });
                } else {
                    groupsHtml = '<span class="text-muted">No permissions assigned.</span>';
                }

                body.html(`
                    <div class="mb-3">
                        <label class="text-muted small fw-bold">Role</label>
                        <p class="fw-bold mb-0">${r.name}</p>
                    </div>
                    <div>
                        <label class="text-muted small fw-bold">Permissions (${r.permissions ? r.permissions.length : 0})</label>
                        <div class="border p-2 rounded mt-1">
                            ${groupsHtml}
                        </div>
                    </div>
                `);
            },
            error: function() {
                body.html(`
                    <div class="text-center py-5 text-danger">
                        <i class="fas fa-exclamation-circle fa-4x mb-3 d-block"></i>
                        <h5>Failed to load permissions</h5>
                        <p>Please try again later.</p>
                    </div>
                `);
            }
        });
    });

    // ==========================================
    // EDIT
    // ==========================================
    $(document).on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        var body = $('#editModalBody');
        var form = $('#editForm');

        form.attr('action', '/admin/roles/' + id);
        body.html(`
            <div class="text-center py-5">
                <div class="spinner-border text-warning" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `);
        $('#editModal').modal('show');

        $.ajax({
            url: '/admin/roles/' + id + '/edit',
            type: 'GET',
            dataType: 'json',
            success: function(r) {
                // FIX: the controller already returns a flat array of
                // permission IDs (via ->pluck('id')->toArray()), NOT an
                // array of {id, name, ...} objects. The old code did
                // r.permissions.map(p => p.id), which ran ".id" on plain
                // numbers and produced an array of `undefined`s — so no
                // checkbox was ever pre-checked on edit. Just use the
                // array as-is.
                var permIds = r.permissions || [];

                var groupsHtml = '';
                @foreach($groupedPermissions as $groupName => $perms)
                    groupsHtml += `
                        <div class="card mb-2 group-container" data-group="{{ $groupName }}">
                            <div class="card-header py-2 px-3 d-flex justify-content-between align-items-center bg-light">
                                <div class="form-check mb-0">
                                    <input type="checkbox" class="form-check-input group-select-all" data-group="{{ $groupName }}" id="edit_group_{{ \Illuminate\Support\Str::slug($groupName ?: 'general') }}">
                                    <label class="form-check-label fw-bold small text-uppercase" for="edit_group_{{ \Illuminate\Support\Str::slug($groupName ?: 'general') }}">
                                        {{ $groupName ?: 'General' }}
                                    </label>
                                </div>
                                <span class="badge bg-secondary">{{ $perms->count() }}</span>
                            </div>
                            <div class="card-body py-2 px-3">
                                <div class="row g-1">`;
                    @foreach($perms as $perm)
                        groupsHtml += (permIds.includes({{ $perm->id }})
                            ? `<div class="col-md-6"><div class="form-check"><input type="checkbox" name="permissions[]" value="{{ $perm->id }}" class="form-check-input permission-checkbox" data-group="{{ $groupName }}" id="edit_perm_{{ $perm->id }}" checked><label class="form-check-label small" for="edit_perm_{{ $perm->id }}">{{ $perm->name }}</label></div></div>`
                            : `<div class="col-md-6"><div class="form-check"><input type="checkbox" name="permissions[]" value="{{ $perm->id }}" class="form-check-input permission-checkbox" data-group="{{ $groupName }}" id="edit_perm_{{ $perm->id }}"><label class="form-check-label small" for="edit_perm_{{ $perm->id }}">{{ $perm->name }}</label></div></div>`);
                    @endforeach
                    groupsHtml += `
                                </div>
                            </div>
                        </div>
                    `;
                @endforeach

                body.html(`
                    <div class="mb-3">
                        <label for="edit_name" class="form-label fw-bold">Role Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_name" class="form-control" value="${r.name}" required>
                    </div>

                    <div class="mb-2 d-flex justify-content-between align-items-center">
                        <label class="form-label fw-bold mb-0">Permissions</label>
                        <div class="form-check form-switch mb-0">
                            <input type="checkbox" id="edit_master_select_all" class="form-check-input master-select-all">
                            <label class="form-check-label small fw-bold" for="edit_master_select_all">Select All Sections</label>
                        </div>
                    </div>

                    <div class="permission-groups" style="max-height: 360px; overflow-y: auto;">
                        ${groupsHtml}
                    </div>
                    <small class="text-muted">Tick a section's "Select All" to grant every permission in that section, or the top switch for everything.</small>
                `);

                // Initialize the group / master "Select All" states to
                // reflect the permissions this role already has, instead
                // of leaving them unchecked until the admin manually
                // clicks something.
                syncAllStates($('#editModalBody'));
            },
            error: function() {
                body.html(`
                    <div class="text-center py-5 text-danger">
                        <i class="fas fa-exclamation-circle fa-4x mb-3 d-block"></i>
                        <h5>Failed to load role data</h5>
                        <p>Please try again later.</p>
                    </div>
                `);
            }
        });
    });

    // ==========================================
    // CREATE (SweetAlert)
    // ==========================================
    $('#createForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);

        Swal.fire({
            title: 'Creating Role...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(r) {
                if (r.success) {
                    $('#createModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Created!',
                        text: r.message,
                        timer: 2000,
                        showConfirmButton: true
                    }).then(() => { window.location.reload(); });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: r.message || 'Something went wrong.',
                    });
                }
            },
            error: function(xhr) {
                Swal.close();
                if (xhr.status === 422) {
                    var msg = '';
                    $.each(xhr.responseJSON.errors, function(k, v) {
                        msg += v[0] + '\n';
                    });
                    Swal.fire({ icon: 'error', title: 'Validation Error', text: msg });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error!', text: 'Failed to create role. Please try again.' });
                }
            }
        });
    });

    // ==========================================
    // UPDATE (SweetAlert)
    // ==========================================
    $('#editForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);

        Swal.fire({
            title: 'Updating Role...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(r) {
                if (r.success) {
                    $('#editModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated!',
                        text: r.message,
                        timer: 2000,
                        showConfirmButton: true
                    }).then(() => { window.location.reload(); });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: r.message || 'Something went wrong.',
                    });
                }
            },
            error: function(xhr) {
                Swal.close();
                if (xhr.status === 422) {
                    var msg = '';
                    $.each(xhr.responseJSON.errors, function(k, v) {
                        msg += v[0] + '\n';
                    });
                    Swal.fire({ icon: 'error', title: 'Validation Error', text: msg });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error!', text: 'Failed to update role. Please try again.' });
                }
            }
        });
    });

    // ==========================================
    // DELETE (SweetAlert)
    // ==========================================
    $(document).on('click', '.delete-btn', function() {
        var id = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "This role will be permanently deleted.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (!result.isConfirmed) return;

            Swal.fire({
                title: 'Deleting...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            $.ajax({
                url: '/admin/roles/' + id,
                type: 'POST',
                data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
                dataType: 'json',
                success: function(r) {
                    if (r.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: r.message,
                            timer: 2000,
                            showConfirmButton: true
                        }).then(() => { window.location.reload(); });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: r.message || 'Failed to delete role.',
                        });
                    }
                },
                error: function(xhr) {
                    Swal.close();
                    if (xhr.status === 422) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Cannot Delete',
                            text: xhr.responseJSON.error || 'Unable to delete this role.',
                        });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error!', text: 'Failed to delete role. Please try again.' });
                    }
                }
            });
        });
    });

    // ==========================================
    // Reset Create form (checkboxes included) when closed without submit
    // ==========================================
    $('#createModal').on('hidden.bs.modal', function() {
        $('#createForm')[0].reset();
        $(this).find('.group-select-all, .master-select-all').prop('indeterminate', false);
    });

    // ==========================================
    // SEARCH
    // ==========================================
    $('#searchBtn').on('click', function() { filterTable(); });
    $('#searchInput').on('keypress', function(e) {
        if (e.which === 13) filterTable();
    });

    function filterTable() {
        var search = $('#searchInput').val().toLowerCase();
        $('#tableBody tr').each(function() {
            var row = $(this);
            var text = row.text().toLowerCase();
            row.toggle(text.includes(search));
        });
    }

    // ==========================================
    // FIX MODAL BACKDROP
    // ==========================================
    $('.modal').on('hidden.bs.modal', function() {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
    });
});
</script>
@endpush
@endsection