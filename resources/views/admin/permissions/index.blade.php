@extends('layouts.admin')

@section('title', 'Permissions')
@section('page-title', 'Permission Management')

@section('admin-content')
<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ $stats['total'] ?? 0 }}</h3>
                <p>Total Permissions</p>
            </div>
            <div class="icon">
                <i class="fas fa-key"></i>
            </div>
            <a href="{{ route('admin.permissions.index') }}" class="small-box-footer">
                View All <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ count($groups ?? []) }}</h3>
                <p>Permission Groups</p>
            </div>
            <div class="icon">
                <i class="fas fa-layer-group"></i>
            </div>
            <a href="#" class="small-box-footer">
                <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ isset($stats['total']) && $stats['total'] > 0 ? round((count($groups ?? []) / $stats['total']) * 100, 0) : 0 }}%</h3>
                <p>Grouped</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <a href="#" class="small-box-footer">
                <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ isset($stats['by_group']['General']) ? $stats['by_group']['General'] : 0 }}</h3>
                <p>Ungrouped</p>
            </div>
            <div class="icon">
                <i class="fas fa-exclamation-triangle"></i>
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
                <i class="fas fa-key text-primary me-2"></i>
                All Permissions
                <span class="badge bg-primary ms-1">{{ $permissions->total() ?? 0 }}</span>
            </h5>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <div class="input-group input-group-sm" style="width: 200px;">
                <input type="text" id="searchInput" class="form-control" placeholder="Search...">
                <button class="btn btn-secondary" type="button" id="searchBtn">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            <select id="groupFilter" class="form-select form-select-sm" style="width: 150px;">
                <option value="">All Groups</option>
                @foreach($groups ?? [] as $group)
                    <option value="{{ $group }}">{{ ucfirst($group) }}</option>
                @endforeach
            </select>
            @can('role.create')
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fas fa-plus-circle me-1"></i> Add Permission
            </button>
            @endcan
            @can('role.view')
            <a href="{{ route('admin.permissions.export') }}" class="btn btn-success btn-sm">
                <i class="fas fa-file-excel me-1"></i> Export
            </a>
            @endcan
            @can('role.edit')
            <button class="btn btn-info btn-sm" id="refreshCacheBtn">
                <i class="fas fa-sync me-1"></i> Refresh Cache
            </button>
            @endcan
            @can('role.delete')
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
                        @can('role.delete')
                        <th style="width: 35px;">
                            <input type="checkbox" id="selectAll">
                        </th>
                        @endcan
                        <th style="width: 35px;">#</th>
                        <th style="min-width: 200px;">Permission Name</th>
                        <th style="min-width: 150px;">Group</th>
                        <th style="width: 100px;">Guard</th>
                        <th style="width: 160px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($permissions as $permission)
                    <tr data-id="{{ $permission->id }}" data-group="{{ $permission->group_name }}">
                        @can('role.delete')
                        <td>
                            <input type="checkbox" class="row-checkbox" value="{{ $permission->id }}">
                        </td>
                        @endcan
                        <td>{{ $permissions->firstItem() + $loop->index }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle bg-primary text-white me-2">
                                    <i class="fas fa-key"></i>
                                </div>
                                <div>
                                    <div class="fw-bold small">{{ $permission->name }}</div>
                                    <small class="text-muted">ID: #{{ $permission->id }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-{{ $permission->group_name ? 'info' : 'secondary' }}">
                                {{ $permission->group_name ?? 'Uncategorized' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $permission->guard_name ?? 'web' }}</span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                @can('role.view')
                                <button class="btn btn-outline-primary btn-sm view-btn" data-id="{{ $permission->id }}" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @endcan
                                @can('role.edit')
                                <button class="btn btn-outline-warning btn-sm edit-btn" data-id="{{ $permission->id }}" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @endcan
                                @can('role.delete')
                                <button class="btn btn-outline-danger btn-sm delete-btn" data-id="{{ $permission->id }}" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ auth()->user()->can('role.delete') ? 6 : 5 }}">
                            <div class="text-center py-5">
                                <i class="fas fa-key fa-4x text-muted mb-3 d-block"></i>
                                <h5 class="fw-bold">No Permissions Found</h5>
                                <p class="text-muted">Click "Add Permission" to create your first permission.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- IMPROVED PAGINATION -->
    <div class="card-footer">
        <div class="row align-items-center g-3">
            <div class="col-md-6">
                <div class="text-muted small">
                    Showing 
                    <span class="fw-bold">{{ $permissions->firstItem() ?? 0 }}</span>
                    to 
                    <span class="fw-bold">{{ $permissions->lastItem() ?? 0 }}</span>
                    of 
                    <span class="fw-bold">{{ $permissions->total() }}</span>
                    entries
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex justify-content-md-end align-items-center gap-2 flex-wrap">
                    <!-- Per Page Selector -->
                    <div class="d-flex align-items-center gap-2">
                        <label class="text-muted small mb-0">Show:</label>
                        <select id="perPage" class="form-select form-select-sm" style="width: 70px;">
                            <option value="15" {{ $permissions->perPage() == 15 ? 'selected' : '' }}>15</option>
                            <option value="25" {{ $permissions->perPage() == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ $permissions->perPage() == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ $permissions->perPage() == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>

                    <!-- Pagination Links -->
                    <div class="pagination-wrapper">
                        {{ $permissions->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- MODALS (Create, View, Edit, Delete) -->
<!-- ============================================ -->

<!-- CREATE MODAL -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle text-primary me-2"></i> Add Permission
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createForm" action="{{ route('admin.permissions.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div id="createFormErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Permission Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g., user.view">
                            <small class="text-muted">Format: resource.action (e.g., user.view, product.create)</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Group <span class="text-danger">*</span></label>
                            <select name="group_name" class="form-select" required>
                                <option value="">Select Group</option>
                                <option value="dashboard">Dashboard</option>
                                <option value="user">User Management</option>
                                <option value="role">Role & Permission</option>
                                <option value="department">Department</option>
                                <option value="category">Category</option>
                                <option value="supplier">Supplier</option>
                                <option value="product">Product</option>
                                <option value="assignment">Assignment</option>
                                <option value="stock">Stock</option>
                                <option value="purchase_order">Purchase Order</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="asset_request">Asset Request</option>
                                <option value="procurement">Procurement</option>
                                <option value="inspection">Inspection</option>
                                <option value="direct_request">Direct Request</option>
                                <option value="notification">Notification</option>
                                <option value="report">Report</option>
                                <option value="settings">Settings</option>
                                <option value="license">License</option>
                                <option value="activity_log">Activity Log</option>
                                <option value="api">API</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Guard</label>
                            <select name="guard_name" class="form-select">
                                <option value="web">Web</option>
                                <option value="api">API</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="createSubmitBtn">
                        <i class="fas fa-save me-1"></i> Create Permission
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
                <h5 class="modal-title">
                    <i class="fas fa-eye text-info me-2"></i> Permission Details
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

<!-- EDIT MODAL -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit text-warning me-2"></i> Edit Permission
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
                        <i class="fas fa-save me-1"></i> Update Permission
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- DELETE CONFIRM MODAL -->
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
                <p class="fw-bold">Are you sure you want to delete this permission?</p>
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

/* Pagination Styles */
.pagination-wrapper .pagination {
    margin-bottom: 0;
    flex-wrap: wrap;
    gap: 2px;
}
.pagination-wrapper .page-link {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
    color: #4f46e5;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    transition: all 0.15s ease;
}
.pagination-wrapper .page-link:hover {
    background-color: #f8fafc;
    border-color: #cbd5e1;
    color: #4338ca;
}
.pagination-wrapper .page-item.active .page-link {
    background-color: #4f46e5;
    border-color: #4f46e5;
    color: #fff;
}
.pagination-wrapper .page-item.disabled .page-link {
    color: #94a3b8;
    pointer-events: none;
    background-color: #f1f5f9;
    border-color: #e2e8f0;
}
.pagination-wrapper .page-item:first-child .page-link,
.pagination-wrapper .page-item:last-child .page-link {
    border-radius: 6px;
}
.pagination-wrapper .page-item:not(:first-child) .page-link {
    margin-left: 2px;
}

/* Card Footer */
.card-footer {
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    padding: 0.75rem 1.25rem;
}
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
    // PER PAGE SELECTOR
    // ==========================================
    $('#perPage').on('change', function() {
        const perPage = $(this).val();
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('per_page', perPage);
        window.location.href = currentUrl.toString();
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
    // REFRESH CACHE
    // ==========================================
    @can('role.edit')
    $('#refreshCacheBtn').on('click', function() {
        Swal.fire({
            title: 'Refresh Cache?',
            text: 'This will refresh the permission cache.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0dcaf0',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, refresh!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Refreshing...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: '{{ route("admin.permissions.refresh-cache") }}',
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    dataType: 'json',
                    success: function(r) {
                        if (r.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Done!',
                                text: r.message,
                                timer: 2000,
                                showConfirmButton: true
                            });
                        }
                    },
                    error: function() {
                        Swal.close();
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Failed to refresh cache.'
                        });
                    }
                });
            }
        });
    });
    @endcan

    // ==========================================
    // BULK SELECT
    // ==========================================
    @can('role.delete')
    $('#selectAll').on('change', function() {
        $('.row-checkbox').prop('checked', $(this).prop('checked'));
    });

    $('#bulkDeleteBtn').on('click', function() {
        var selected = $('.row-checkbox:checked');
        if (selected.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Items Selected',
                text: 'Please select at least one permission to delete.'
            });
            return;
        }

        Swal.fire({
            title: 'Delete Selected Permissions?',
            text: 'You are about to delete ' + selected.length + ' permission(s). This action cannot be undone.',
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
                    url: '{{ route("admin.permissions.bulk-delete") }}',
                    type: 'POST',
                    data: {
                        permission_ids: ids
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
                        var msg = xhr.responseJSON?.error || 'Failed to delete selected permissions.';
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: msg
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
    @can('role.view')
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
            url: '/admin/permissions/' + id,
            type: 'GET',
            dataType: 'json',
            success: function(r) {
                var permission = r.permission || r;
                var roles = r.assigned_to_roles || [];

                var html = `
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="text-muted small fw-bold">Permission Name</label>
                            <p class="fw-bold mb-0">${permission.name}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Group</label>
                            <p class="mb-0"><span class="badge bg-info">${permission.group_name || 'Uncategorized'}</span></p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Guard</label>
                            <p class="mb-0"><span class="badge bg-secondary">${permission.guard_name || 'web'}</span></p>
                        </div>
                        <div class="col-12">
                            <label class="text-muted small fw-bold">Created</label>
                            <p class="mb-0">${new Date(permission.created_at).toLocaleString()}</p>
                        </div>
                        <div class="col-12">
                            <label class="text-muted small fw-bold">Updated</label>
                            <p class="mb-0">${new Date(permission.updated_at).toLocaleString()}</p>
                        </div>
                        ${roles.length > 0 ? `
                        <div class="col-12">
                            <hr>
                            <h6 class="fw-bold">Assigned to Roles</h6>
                            ${roles.map(function(role) {
                                return `<span class="badge bg-primary me-1">${role.name}</span>`;
                            }).join('')}
                        </div>` : `
                        <div class="col-12">
                            <hr>
                            <p class="text-muted small">Not assigned to any role</p>
                        </div>`}
                    </div>
                `;
                body.html(html);
            },
            error: function(xhr) {
                body.html(`
                    <div class="text-center py-5 text-danger">
                        <i class="fas fa-exclamation-circle fa-4x mb-3 d-block"></i>
                        <h5>Failed to load permission details</h5>
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
    @can('role.edit')
    $(document).on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        var body = $('#editModalBody');
        var form = $('#editForm');

        form.attr('action', '/admin/permissions/' + id);
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
            url: '/admin/permissions/' + id + '/edit',
            type: 'GET',
            dataType: 'json',
            success: function(r) {
                body.html(`
                    <div id="editFormErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Permission Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="${r.name}" required>
                            <small class="text-muted">Format: resource.action (e.g., user.view, product.create)</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Group <span class="text-danger">*</span></label>
                            <select name="group_name" class="form-select" required>
                                <option value="">Select Group</option>
                                <option value="dashboard" ${r.group_name == 'dashboard' ? 'selected' : ''}>Dashboard</option>
                                <option value="user" ${r.group_name == 'user' ? 'selected' : ''}>User Management</option>
                                <option value="role" ${r.group_name == 'role' ? 'selected' : ''}>Role & Permission</option>
                                <option value="department" ${r.group_name == 'department' ? 'selected' : ''}>Department</option>
                                <option value="category" ${r.group_name == 'category' ? 'selected' : ''}>Category</option>
                                <option value="supplier" ${r.group_name == 'supplier' ? 'selected' : ''}>Supplier</option>
                                <option value="product" ${r.group_name == 'product' ? 'selected' : ''}>Product</option>
                                <option value="assignment" ${r.group_name == 'assignment' ? 'selected' : ''}>Assignment</option>
                                <option value="stock" ${r.group_name == 'stock' ? 'selected' : ''}>Stock</option>
                                <option value="purchase_order" ${r.group_name == 'purchase_order' ? 'selected' : ''}>Purchase Order</option>
                                <option value="maintenance" ${r.group_name == 'maintenance' ? 'selected' : ''}>Maintenance</option>
                                <option value="asset_request" ${r.group_name == 'asset_request' ? 'selected' : ''}>Asset Request</option>
                                <option value="procurement" ${r.group_name == 'procurement' ? 'selected' : ''}>Procurement</option>
                                <option value="inspection" ${r.group_name == 'inspection' ? 'selected' : ''}>Inspection</option>
                                <option value="direct_request" ${r.group_name == 'direct_request' ? 'selected' : ''}>Direct Request</option>
                                <option value="notification" ${r.group_name == 'notification' ? 'selected' : ''}>Notification</option>
                                <option value="report" ${r.group_name == 'report' ? 'selected' : ''}>Report</option>
                                <option value="settings" ${r.group_name == 'settings' ? 'selected' : ''}>Settings</option>
                                <option value="license" ${r.group_name == 'license' ? 'selected' : ''}>License</option>
                                <option value="activity_log" ${r.group_name == 'activity_log' ? 'selected' : ''}>Activity Log</option>
                                <option value="api" ${r.group_name == 'api' ? 'selected' : ''}>API</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Guard</label>
                            <select name="guard_name" class="form-select">
                                <option value="web" ${r.guard_name == 'web' || !r.guard_name ? 'selected' : ''}>Web</option>
                                <option value="api" ${r.guard_name == 'api' ? 'selected' : ''}>API</option>
                            </select>
                        </div>
                    </div>
                `);
            },
            error: function(xhr) {
                body.html(`
                    <div id="editFormErrors" class="alert alert-danger d-none"></div>
                    <div class="text-center py-5 text-danger">
                        <i class="fas fa-exclamation-circle fa-4x mb-3 d-block"></i>
                        <h5>Failed to load permission data</h5>
                        <p>${xhr.status} - ${xhr.statusText}</p>
                    </div>
                `);
            }
        });
    });
    @endcan

    // ==========================================
    // CREATE
    // ==========================================
    @can('role.create')
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
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Create Permission');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: r.message || 'Something went wrong.',
                    });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Create Permission');
                if (xhr.status === 422) {
                    showErrors(errorContainer, xhr.responseJSON.errors);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to create permission. Please try again.'
                    });
                }
            }
        });
    });
    @endcan

    // ==========================================
    // UPDATE
    // ==========================================
    @can('role.edit')
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
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Update Permission');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: r.message || 'Something went wrong.',
                    });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Update Permission');
                if (xhr.status === 422) {
                    showErrors(errorContainer, xhr.responseJSON.errors);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to update permission. Please try again.'
                    });
                }
            }
        });
    });
    @endcan

    // ==========================================
    // DELETE
    // ==========================================
    @can('role.delete')
    $(document).on('click', '.delete-btn', function() {
        var id = $(this).data('id');
        var form = $('#deleteForm');
        form.attr('action', '/admin/permissions/' + id);

        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
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
                        text: r.message || 'Failed to delete permission.',
                    });
                }
            },
            error: function(xhr) {
                Swal.close();
                var msg = xhr.responseJSON?.error || 'Failed to delete permission.';
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: msg
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
    $('#groupFilter').on('change', function() { filterTable(); });

    function filterTable() {
        var search = $('#searchInput').val().toLowerCase();
        var group = $('#groupFilter').val();

        $('#tableBody tr').each(function() {
            var row = $(this);
            var text = row.text().toLowerCase();
            var rowGroup = row.data('group');

            var show = true;
            if (search && !text.includes(search)) show = false;
            if (group && rowGroup !== group) show = false;

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