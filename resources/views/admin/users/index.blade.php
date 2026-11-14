@extends('layouts.admin')

@section('title', 'Users')
@section('page-title', 'User Management')

@section('admin-content')
<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ $stats['total'] ?? 0 }}</h3>
                <p>Total Users</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
            <a href="{{ route('admin.users.index') }}" class="small-box-footer">
                View All <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $stats['admins'] ?? 0 }}</h3>
                <p>Administrators</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-shield"></i>
            </div>
            <a href="{{ route('admin.users.index', ['role' => 'admin']) }}" class="small-box-footer">
                View Admins <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $stats['unverified'] ?? 0 }}</h3>
                <p>Unverified Users</p>
            </div>
            <div class="icon">
                <i class="fas fa-envelope"></i>
            </div>
            <a href="{{ route('admin.users.index', ['verified' => 'unverified']) }}" class="small-box-footer">
                View Unverified <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $stats['new_users'] ?? 0 }}</h3>
                <p>New Users (7 days)</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-plus"></i>
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
                <i class="fas fa-users text-primary me-2"></i>
                All Users
                <span class="badge bg-primary ms-1">{{ $users->total() }}</span>
            </h5>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <div class="input-group input-group-sm" style="width: 200px;">
                <input type="text" id="searchInput" class="form-control" placeholder="Search...">
                <button class="btn btn-secondary" type="button" id="searchBtn">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            <select id="roleFilter" class="form-select form-select-sm" style="width: 140px;">
                <option value="">All Roles</option>
                @foreach($roles ?? [] as $role)
                    <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                @endforeach
            </select>
            <select id="departmentFilter" class="form-select form-select-sm" style="width: 150px;">
                <option value="">All Departments</option>
                @foreach($departments ?? [] as $department)
                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                @endforeach
            </select>
            <select id="verifiedFilter" class="form-select form-select-sm" style="width: 130px;">
                <option value="">All Status</option>
                <option value="verified">Verified</option>
                <option value="unverified">Unverified</option>
            </select>
            @can('user.create')
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fas fa-plus-circle me-1"></i> Add User
            </button>
            @endcan
            @can('user.view')
            <a href="{{ route('admin.users.export') }}" class="btn btn-success btn-sm">
                <i class="fas fa-file-excel me-1"></i> Export
            </a>
            @endcan
            @can('user.delete')
            <button class="btn btn-danger btn-sm" id="bulkDeleteBtn">
                <i class="fas fa-trash me-1"></i> Bulk Delete
            </button>
            @endcan
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        @can('user.delete')
                        <th style="width: 35px;">
                            <input type="checkbox" id="selectAll">
                        </th>
                        @endcan
                        <th style="width: 35px;">#</th>
                        <th style="min-width: 160px;">User</th>
                        <th>Email</th>
                        <th style="min-width: 120px;">Roles</th>
                        <th>Department</th>
                        <th style="width: 80px;">Status</th>
                        <th style="width: 100px;">Created</th>
                        <th style="width: 200px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($users as $user)
                    <tr data-id="{{ $user->id }}" data-role="{{ $user->roles->pluck('name')->implode(',') }}" data-department="{{ $user->department_id }}" data-verified="{{ $user->email_verified_at ? 'verified' : 'unverified' }}">
                        @can('user.delete')
                        <td>
                            @if($user->id !== auth()->id())
                            <input type="checkbox" class="row-checkbox" value="{{ $user->id }}">
                            @endif
                        </td>
                        @endcan
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle bg-primary text-white me-2">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="fw-bold small">{{ Str::limit($user->name, 25) }}</div>
                                    <small class="text-muted">ID: #{{ $user->id }}</small>
                                </div>
                            </div>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @if($user->roles->isNotEmpty())
                                @foreach($user->roles as $role)
                                    <span class="badge bg-{{ $role->name == 'super_admin' ? 'danger' : ($role->name == 'admin' ? 'primary' : 'secondary') }} me-1">
                                        {{ ucfirst($role->name) }}
                                    </span>
                                @endforeach
                            @else
                                <span class="badge bg-secondary">No Role</span>
                            @endif
                        </td>
                        <td>{{ $user->department->name ?? 'N/A' }}</td>
                        <td>
                            @if($user->email_verified_at)
                                <span class="badge bg-success">Verified</span>
                            @else
                                <span class="badge bg-warning text-dark">Unverified</span>
                            @endif
                        </td>
                        <td>
                            <small>{{ $user->created_at->format('M d, Y') }}</small>
                            <br>
                            <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                @can('user.view')
                                <button class="btn btn-outline-primary btn-sm view-btn" data-id="{{ $user->id }}" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @endcan
                                @can('user.edit')
                                <button class="btn btn-outline-warning btn-sm edit-btn" data-id="{{ $user->id }}" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @endcan
                                @can('user.edit')
                                @if(!$user->email_verified_at)
                                <button class="btn btn-outline-success btn-sm verify-btn" data-id="{{ $user->id }}" title="Verify Email">
                                    <i class="fas fa-check-circle"></i>
                                </button>
                                @else
                                <button class="btn btn-outline-secondary btn-sm unverify-btn" data-id="{{ $user->id }}" title="Unverify Email">
                                    <i class="fas fa-times-circle"></i>
                                </button>
                                @endif
                                @endcan
                                @can('user.delete')
                                @if($user->id !== auth()->id())
                                <button class="btn btn-outline-danger btn-sm delete-btn" data-id="{{ $user->id }}" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ auth()->user()->can('user.delete') ? 9 : 8 }}">
                            <div class="text-center py-5">
                                <i class="fas fa-users fa-4x text-muted mb-3 d-block"></i>
                                <h5 class="fw-bold">No Users Found</h5>
                                <p class="text-muted">Click "Add User" to create your first user.</p>
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
                Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} entries
            </span>
        </div>
        <div>
            {{ $users->links() }}
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- CREATE MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle text-primary me-2"></i> Add User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createForm" action="{{ route('admin.users.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div id="createFormErrors" class="alert alert-danger d-none"></div>
                    <div class="mb-3">
                        <label for="user_name" class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="user_name" class="form-control" required placeholder="Enter full name">
                    </div>
                    <div class="mb-3">
                        <label for="user_email" class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="user_email" class="form-control" required placeholder="Enter email">
                    </div>
                    <div class="mb-3">
                        <label for="user_password" class="form-label fw-bold">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" id="user_password" class="form-control" required placeholder="Enter password">
                    </div>
                    <div class="mb-3">
                        <label for="user_password_confirmation" class="form-label fw-bold">Confirm Password <span class="text-danger">*</span></label>
                        <input type="password" name="password_confirmation" id="user_password_confirmation" class="form-control" required placeholder="Confirm password">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Roles</label>
                        <div class="d-flex flex-wrap gap-2">
                            @forelse($roles ?? [] as $role)
                                @php
                                    $roleColor = $role->name == 'super_admin' ? 'danger' : ($role->name == 'admin' ? 'primary' : 'secondary');
                                @endphp
                                <input type="checkbox" class="btn-check" name="roles[]" value="{{ $role->id }}" id="role_create_{{ $role->id }}" autocomplete="off">
                                <label class="btn btn-outline-{{ $roleColor }} btn-sm rounded-pill px-3" for="role_create_{{ $role->id }}">
                                    {{ ucfirst($role->name) }}
                                </label>
                            @empty
                                <span class="text-muted small">No roles available. Create one under Role Management first.</span>
                            @endforelse
                        </div>
                        <small class="text-muted">Click a role to select it. Multiple roles allowed.</small>
                    </div>
                    <div class="mb-3">
                        <label for="user_department" class="form-label fw-bold">Department</label>
                        <select name="department_id" id="user_department" class="form-select">
                            <option value="">Select Department</option>
                            @foreach($departments ?? [] as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="user_employee_id" class="form-label fw-bold">Employee ID</label>
                        <input type="text" name="employee_id" id="user_employee_id" class="form-control" placeholder="Enter employee ID">
                    </div>
                    <div class="mb-3">
                        <label for="user_phone" class="form-label fw-bold">Phone</label>
                        <input type="text" name="phone" id="user_phone" class="form-control" placeholder="Enter phone number">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="createSubmitBtn">
                        <i class="fas fa-save me-1"></i> Save User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- VIEW MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-eye text-info me-2"></i> User Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewModalBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- EDIT MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit text-warning me-2"></i> Edit User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body" id="editModalBody">
                    <div id="editFormErrors" class="alert alert-danger d-none"></div>
                    <div class="text-center py-5">
                        <div class="spinner-border text-warning" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-warning" id="editSubmitBtn">
                        <i class="fas fa-save me-1"></i> Update User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- DELETE CONFIRM MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i> Confirm Delete
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-trash fa-4x text-danger mb-3 d-block"></i>
                <p class="fw-bold">Are you sure you want to delete this user?</p>
                <p class="text-muted small">This action cannot be undone.</p>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <button type="submit" class="btn btn-danger" id="deleteSubmitBtn" form="deleteForm">
                    <i class="fas fa-trash me-1"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-circle {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 600;
    flex-shrink: 0;
}
.small-box {
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 0;
    position: relative;
    overflow: hidden;
    transition: transform 0.15s ease;
}
.small-box:hover {
    transform: translateY(-2px);
}
.small-box .inner { color: #fff; }
.small-box .inner h3 { font-size: 2rem; font-weight: 700; margin: 0 0 5px; }
.small-box .inner p { font-size: 0.9rem; opacity: 0.9; margin: 0; }
.small-box .icon {
    position: absolute;
    right: 15px;
    top: 15px;
    font-size: 2.5rem;
    opacity: 0.25;
    color: #fff;
}
.small-box .small-box-footer {
    display: block;
    padding: 8px 0 0;
    color: rgba(255,255,255,0.8);
    text-decoration: none;
    font-size: 0.85rem;
    transition: all 0.15s ease;
}
.small-box .small-box-footer:hover { color: #fff; }
.small-box .small-box-footer i { transition: transform 0.15s ease; }
.small-box:hover .small-box-footer i { transform: translateX(4px); }
</style>

@push('scripts')
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
    // HELPER: Show Errors
    // ==========================================
    function showErrors(container, errors) {
        container.removeClass('d-none');
        var html = '<ul class="mb-0">';
        $.each(errors, function(key, messages) {
            $.each(messages, function(index, msg) {
                html += '<li>' + msg + '</li>';
            });
        });
        html += '</ul>';
        container.html(html);
        container[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function clearErrors(container) {
        container.addClass('d-none').empty();
    }

    // ==========================================
    // BULK SELECT
    // ==========================================
    @can('user.delete')
    $('#selectAll').on('change', function() {
        $('.row-checkbox').prop('checked', $(this).prop('checked'));
    });

    $('#bulkDeleteBtn').on('click', function() {
        var selected = $('.row-checkbox:checked');
        if (selected.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Items Selected',
                text: 'Please select at least one user to delete.'
            });
            return;
        }

        Swal.fire({
            title: 'Delete Selected Users?',
            text: 'You are about to delete ' + selected.length + ' user(s). This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete them!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                var ids = [];
                selected.each(function() {
                    ids.push($(this).val());
                });

                Swal.fire({
                    title: 'Deleting...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: '{{ route("admin.users.bulk-delete") }}',
                    type: 'POST',
                    data: {
                        user_ids: ids
                    },
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
                        }
                    },
                    error: function(xhr) {
                        Swal.close();
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: xhr.responseJSON?.error || 'Failed to delete selected users.'
                        });
                    }
                });
            }
        });
    });
    @endcan

    // ==========================================
    // VIEW
    // ==========================================
    @can('user.view')
    $(document).on('click', '.view-btn', function() {
        var id = $(this).data('id');
        var body = $('#viewModalBody');

        body.html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `);
        $('#viewModal').modal('show');

        $.ajax({
            url: '/admin/users/' + id,
            type: 'GET',
            dataType: 'json',
            success: function(r) {
                var user = r.user || r;
                var stats = r.stats || {};
                var roles = r.roles || [];
                var permissions = r.permissions || [];

                var rolesHtml = roles.length ? 
                    roles.map(function(role) {
                        return `<span class="badge bg-primary me-1">${role}</span>`;
                    }).join('') : 
                    '<span class="badge bg-secondary">No Role</span>';

                var permissionsHtml = permissions.length ?
                    permissions.map(function(perm) {
                        return `<span class="badge bg-info me-1">${perm}</span>`;
                    }).join('') :
                    '<span class="text-muted">No direct permissions</span>';

                body.html(`
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="text-center">
                                <div class="avatar-circle bg-primary text-white mx-auto" style="width:64px;height:64px;font-size:24px;display:flex;align-items:center;justify-content:center;">
                                    ${user.name ? user.name.charAt(0).toUpperCase() : 'U'}
                                </div>
                                <h5 class="fw-bold mt-2">${user.name}</h5>
                                <span class="badge ${user.email_verified_at ? 'bg-success' : 'bg-warning text-dark'}">
                                    ${user.email_verified_at ? 'Verified' : 'Unverified'}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Email</label>
                            <p class="mb-0">${user.email}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Employee ID</label>
                            <p class="mb-0">${user.employee_id || 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Department</label>
                            <p class="mb-0">${user.department ? user.department.name : 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Phone</label>
                            <p class="mb-0">${user.phone || 'N/A'}</p>
                        </div>
                        <div class="col-12">
                            <label class="text-muted small fw-bold">Roles</label>
                            <p class="mb-0">${rolesHtml}</p>
                        </div>
                        <div class="col-12">
                            <label class="text-muted small fw-bold">Direct Permissions</label>
                            <p class="mb-0">${permissionsHtml}</p>
                        </div>
                        ${user.bio ? `
                        <div class="col-12">
                            <label class="text-muted small fw-bold">Bio</label>
                            <p class="mb-0">${user.bio}</p>
                        </div>` : ''}
                        <div class="col-12">
                            <hr>
                            <h6 class="fw-bold">Activity Summary</h6>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="text-center p-2 bg-light rounded">
                                        <span class="d-block fw-bold">${stats.assignments || 0}</span>
                                        <small class="text-muted">Assignments</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center p-2 bg-light rounded">
                                        <span class="d-block fw-bold">${stats.active_assignments || 0}</span>
                                        <small class="text-muted">Active</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center p-2 bg-light rounded">
                                        <span class="d-block fw-bold">${stats.asset_requests || 0}</span>
                                        <small class="text-muted">Requests</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center p-2 bg-light rounded">
                                        <span class="d-block fw-bold">${stats.pending_requests || 0}</span>
                                        <small class="text-muted">Pending</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="text-muted small fw-bold">Created</label>
                            <p class="mb-0">${new Date(user.created_at).toLocaleString()}</p>
                        </div>
                        ${user.updated_at ? `
                        <div class="col-12">
                            <label class="text-muted small fw-bold">Last Updated</label>
                            <p class="mb-0">${new Date(user.updated_at).toLocaleString()}</p>
                        </div>` : ''}
                    </div>
                `);
            },
            error: function(xhr) {
                body.html(`
                    <div class="text-center py-5 text-danger">
                        <i class="fas fa-exclamation-circle fa-4x mb-3 d-block"></i>
                        <h5>Failed to load user details</h5>
                        <p>${xhr.status} - ${xhr.statusText}</p>
                    </div>
                `);
            }
        });
    });
    @endcan

    // ==========================================
    // EDIT
    // ==========================================
    @can('user.edit')
    $(document).on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        var body = $('#editModalBody');
        var form = $('#editForm');

        form.attr('action', '/admin/users/' + id);
        body.html(`
            <div id="editFormErrors" class="alert alert-danger d-none"></div>
            <div class="text-center py-5">
                <div class="spinner-border text-warning" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `);
        $('#editModal').modal('show');

        $.ajax({
            url: '/admin/users/' + id + '/edit',
            type: 'GET',
            dataType: 'json',
            success: function(r) {
                var roleIds = r.roles ? r.roles.map(function(role) { return role.id; }) : [];

                var rolesHtml = '';
                @forelse($roles ?? [] as $role)
                    @php
                        $roleColor = $role->name == 'super_admin' ? 'danger' : ($role->name == 'admin' ? 'primary' : 'secondary');
                    @endphp
                    rolesHtml += `
                        <input type="checkbox" class="btn-check" name="roles[]" value="{{ $role->id }}" id="role_edit_{{ $role->id }}" autocomplete="off" ${roleIds.includes({{ $role->id }}) ? 'checked' : ''}>
                        <label class="btn btn-outline-{{ $roleColor }} btn-sm rounded-pill px-3" for="role_edit_{{ $role->id }}">
                            {{ ucfirst($role->name) }}
                        </label>
                    `;
                @empty
                    rolesHtml = '<span class="text-muted small">No roles available.</span>';
                @endforelse

                body.html(`
                    <div id="editFormErrors" class="alert alert-danger d-none"></div>
                    <div class="mb-3">
                        <label for="edit_name" class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_name" class="form-control" value="${r.name || ''}" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_email" class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="edit_email" class="form-control" value="${r.email || ''}" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_password" class="form-label fw-bold">Password</label>
                        <input type="password" name="password" id="edit_password" class="form-control" placeholder="Leave blank to keep current">
                        <small class="text-muted">Leave blank to keep current password.</small>
                    </div>
                    <div class="mb-3">
                        <label for="edit_password_confirmation" class="form-label fw-bold">Confirm Password</label>
                        <input type="password" name="password_confirmation" id="edit_password_confirmation" class="form-control" placeholder="Confirm new password">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Roles</label>
                        <div class="d-flex flex-wrap gap-2">
                            ${rolesHtml}
                        </div>
                        <small class="text-muted">Click a role to toggle it. Deselecting all roles will remove them from this user.</small>
                    </div>
                    <div class="mb-3">
                        <label for="edit_department" class="form-label fw-bold">Department</label>
                        <select name="department_id" id="edit_department" class="form-select">
                            <option value="">Select Department</option>
                            @foreach($departments ?? [] as $department)
                                <option value="{{ $department->id }}" ${r.department_id == {{ $department->id }} ? 'selected' : ''}>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_employee_id" class="form-label fw-bold">Employee ID</label>
                        <input type="text" name="employee_id" id="edit_employee_id" class="form-control" value="${r.employee_id || ''}" placeholder="Enter employee ID">
                    </div>
                    <div class="mb-3">
                        <label for="edit_phone" class="form-label fw-bold">Phone</label>
                        <input type="text" name="phone" id="edit_phone" class="form-control" value="${r.phone || ''}" placeholder="Enter phone number">
                    </div>
                `);
            },
            error: function(xhr) {
                body.html(`
                    <div id="editFormErrors" class="alert alert-danger d-none"></div>
                    <div class="text-center py-5 text-danger">
                        <i class="fas fa-exclamation-circle fa-4x mb-3 d-block"></i>
                        <h5>Failed to load user data</h5>
                        <p>${xhr.status} - ${xhr.statusText}</p>
                    </div>
                `);
            }
        });
    });
    @endcan

    // ==========================================
    // VERIFY
    // ==========================================
    @can('user.edit')
    $(document).on('click', '.verify-btn', function() {
        var id = $(this).data('id');

        Swal.fire({
            title: 'Verify Email?',
            text: "This will mark the user's email as verified.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, verify!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Verifying...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: '/admin/users/' + id + '/verify-email',
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    dataType: 'json',
                    success: function(r) {
                        if (r.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Verified!',
                                text: r.message,
                                timer: 2000,
                                showConfirmButton: true
                            }).then(() => { window.location.reload(); });
                        }
                    },
                    error: function() {
                        Swal.close();
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Failed to verify user.'
                        });
                    }
                });
            }
        });
    });

    $(document).on('click', '.unverify-btn', function() {
        var id = $(this).data('id');

        Swal.fire({
            title: 'Unverify Email?',
            text: "This will mark the user's email as unverified.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#6c757d',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, unverify!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Unverifying...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: '/admin/users/' + id + '/unverify-email',
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    dataType: 'json',
                    success: function(r) {
                        if (r.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Unverified!',
                                text: r.message,
                                timer: 2000,
                                showConfirmButton: true
                            }).then(() => { window.location.reload(); });
                        }
                    },
                    error: function() {
                        Swal.close();
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Failed to unverify user.'
                        });
                    }
                });
            }
        });
    });
    @endcan

    // ==========================================
    // CREATE
    // ==========================================
    @can('user.create')
    $('#createForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        var data = form.serialize();
        var btn = $('#createSubmitBtn');
        var errorContainer = $('#createFormErrors');

        clearErrors(errorContainer);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Creating...');

        $.ajax({
            url: url,
            type: 'POST',
            data: data,
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
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save User');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: r.message || 'Something went wrong.',
                    });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save User');
                if (xhr.status === 422) {
                    showErrors(errorContainer, xhr.responseJSON.errors);
                } else if (xhr.responseJSON && xhr.responseJSON.debug) {
                    var d = xhr.responseJSON.debug;
                    Swal.fire({
                        icon: 'error',
                        title: d.exception || 'Error',
                        html: `<div style="text-align:left; font-size: 0.85rem;">
                                 <strong>Message:</strong> ${d.message}<br>
                                 <strong>File:</strong> ${d.file}:${d.line}
                               </div>`,
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to create user. Please try again.'
                    });
                }
            }
        });
    });
    @endcan

    // ==========================================
    // UPDATE
    // ==========================================
    @can('user.edit')
    $('#editForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        var data = form.serialize();
        var btn = $('#editSubmitBtn');
        var errorContainer = $('#editFormErrors');

        clearErrors(errorContainer);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Updating...');

        $.ajax({
            url: url,
            type: 'POST',
            data: data,
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
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Update User');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: r.message || 'Something went wrong.',
                    });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Update User');
                if (xhr.status === 422) {
                    showErrors(errorContainer, xhr.responseJSON.errors);
                } else if (xhr.responseJSON && xhr.responseJSON.debug) {
                    var d = xhr.responseJSON.debug;
                    Swal.fire({
                        icon: 'error',
                        title: d.exception || 'Error',
                        html: `<div style="text-align:left; font-size: 0.85rem;">
                                 <strong>Message:</strong> ${d.message}<br>
                                 <strong>File:</strong> ${d.file}:${d.line}
                               </div>`,
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to update user. Please try again.'
                    });
                }
            }
        });
    });
    @endcan

    // ==========================================
    // DELETE
    // ==========================================
    @can('user.delete')
    $(document).on('click', '.delete-btn', function() {
        var id = $(this).data('id');
        var form = $('#deleteForm');
        form.attr('action', '/admin/users/' + id);

        Swal.fire({
            title: 'Are you sure?',
            text: "This user will be permanently deleted.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#deleteModal').modal('show');
            }
        });
    });

    $('#deleteForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');

        Swal.fire({
            title: 'Deleting...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        $.ajax({
            url: url,
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
            dataType: 'json',
            success: function(r) {
                if (r.success) {
                    $('#deleteModal').modal('hide');
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
                        text: r.message || 'Failed to delete user.',
                    });
                }
            },
            error: function(xhr) {
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: xhr.responseJSON?.error || 'Failed to delete user.'
                });
            }
        });
    });
    @endcan

    // ==========================================
    // SEARCH & FILTER
    // ==========================================
    $('#searchBtn').on('click', function() { filterTable(); });
    $('#searchInput').on('keypress', function(e) {
        if (e.which === 13) filterTable();
    });
    $('#roleFilter').on('change', function() { filterTable(); });
    $('#departmentFilter').on('change', function() { filterTable(); });
    $('#verifiedFilter').on('change', function() { filterTable(); });

    function filterTable() {
        var search = $('#searchInput').val().toLowerCase();
        var role = $('#roleFilter').val();
        var department = $('#departmentFilter').val();
        var verified = $('#verifiedFilter').val();

        $('#tableBody tr').each(function() {
            var row = $(this);
            var text = row.text().toLowerCase();
            var rowRole = row.data('role') || '';
            var rowDepartment = row.data('department');
            var rowVerified = row.data('verified');

            var show = true;
            if (search && !text.includes(search)) show = false;
            if (role && !rowRole.includes(role)) show = false;
            if (department && rowDepartment != department) show = false;
            if (verified && rowVerified !== verified) show = false;

            row.toggle(show);
        });
    }

    // ==========================================
    // FIX MODAL BACKDROP
    // ==========================================
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