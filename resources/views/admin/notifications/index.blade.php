@extends('layouts.admin')

@section('title', 'Notifications')
@section('page-title', 'Notification Management')

@section('admin-content')
<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ $stats['total'] ?? 0 }}</h3>
                <p>Total Notifications</p>
            </div>
            <div class="icon">
                <i class="fas fa-bell"></i>
            </div>
            <a href="{{ route('admin.notifications.index') }}" class="small-box-footer">
                View All <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $stats['unread'] ?? 0 }}</h3>
                <p>Unread</p>
            </div>
            <div class="icon">
                <i class="fas fa-envelope"></i>
            </div>
            <a href="{{ route('admin.notifications.index', ['status' => 'unread']) }}" class="small-box-footer">
                View Unread <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $stats['read'] ?? 0 }}</h3>
                <p>Read</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <a href="{{ route('admin.notifications.index', ['status' => 'read']) }}" class="small-box-footer">
                View Read <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $stats['today'] ?? 0 }}</h3>
                <p>Today</p>
            </div>
            <div class="icon">
                <i class="fas fa-calendar-day"></i>
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
                <i class="fas fa-bell text-primary me-2"></i>
                All Notifications
                <span class="badge bg-primary ms-1">{{ $notifications->total() }}</span>
            </h5>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <div class="input-group input-group-sm" style="width: 200px;">
                <input type="text" id="searchInput" class="form-control" placeholder="Search...">
                <button class="btn btn-secondary" type="button" id="searchBtn">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            <select id="statusFilter" class="form-select form-select-sm" style="width: 120px;">
                <option value="">All Status</option>
                <option value="unread">Unread</option>
                <option value="read">Read</option>
            </select>
            <select id="typeFilter" class="form-select form-select-sm" style="width: 140px;">
                <option value="">All Types</option>
                <option value="warranty_expiry">Warranty Expiry</option>
                <option value="low_stock">Low Stock</option>
                <option value="assignment">Assignment</option>
                <option value="maintenance">Maintenance</option>
            </select>
            @can('notification.create')
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fas fa-plus-circle me-1"></i> Send Notification
            </button>
            @endcan
            @can('notification.view')
            <a href="{{ route('admin.notifications.export') }}" class="btn btn-success btn-sm">
                <i class="fas fa-file-excel me-1"></i> Export
            </a>
            @endcan
            @can('notification.mark_all_read')
            <button class="btn btn-info btn-sm" id="markAllReadBtn">
                <i class="fas fa-check-double me-1"></i> Mark All Read
            </button>
            @endcan
            @can('notification.delete')
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
                        @can('notification.delete')
                        <th style="width: 35px;">
                            <input type="checkbox" id="selectAll">
                        </th>
                        @endcan
                        <th style="width: 35px;">#</th>
                        <th style="min-width: 140px;">User</th>
                        <th style="min-width: 180px;">Message</th>
                        <th>Type</th>
                        <th style="width: 80px;">Status</th>
                        <th style="width: 100px;">Date</th>
                        <th style="width: 180px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($notifications as $notification)
                    <tr data-id="{{ $notification->id }}" data-status="{{ $notification->is_read ? 'read' : 'unread' }}" data-type="{{ $notification->type }}">
                        @can('notification.delete')
                        <td>
                            <input type="checkbox" class="row-checkbox" value="{{ $notification->id }}">
                        </td>
                        @endcan
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle bg-primary text-white me-2">
                                    {{ strtoupper(substr($notification->user->name ?? 'U', 0, 2)) }}
                                </div>
                                <div>
                                    <div class="fw-bold small">{{ Str::limit($notification->user->name ?? 'Unknown', 18) }}</div>
                                    <small class="text-muted">{{ Str::limit($notification->user->email ?? 'N/A', 20) }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="fw-bold small {{ !$notification->is_read ? 'text-primary' : '' }}">
                                {{ Str::limit($notification->message, 40) }}
                            </div>
                            @if($notification->link)
                            <small class="text-muted">
                                <a href="{{ $notification->link }}" target="_blank" class="text-decoration-none">
                                    <i class="fas fa-link me-1"></i> View
                                </a>
                            </small>
                            @endif
                        </td>
                        <td>
                            @php
                                $typeColors = [
                                    'warranty_expiry' => 'warning text-dark',
                                    'low_stock' => 'danger',
                                    'assignment' => 'info',
                                    'maintenance' => 'secondary'
                                ];
                                $typeIcons = [
                                    'warranty_expiry' => 'fa-file-contract',
                                    'low_stock' => 'fa-exclamation-triangle',
                                    'assignment' => 'fa-handshake',
                                    'maintenance' => 'fa-tools'
                                ];
                            @endphp
                            <span class="badge bg-{{ $typeColors[$notification->type] ?? 'secondary' }}">
                                <i class="fas {{ $typeIcons[$notification->type] ?? 'fa-bell' }} me-1"></i>
                                {{ str_replace('_', ' ', ucfirst($notification->type)) }}
                            </span>
                        </td>
                        <td>
                            @if($notification->is_read)
                                <span class="badge bg-success">Read</span>
                            @else
                                <span class="badge bg-warning text-dark">Unread</span>
                            @endif
                        </td>
                        <td>
                            <small>{{ $notification->created_at->format('M d, Y') }}</small>
                            <br>
                            <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                @can('notification.view')
                                <button class="btn btn-outline-primary btn-sm view-btn" data-id="{{ $notification->id }}" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @endcan
                                @can('notification.edit')
                                <button class="btn btn-outline-warning btn-sm edit-btn" data-id="{{ $notification->id }}" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @endcan
                                @if(!$notification->is_read)
                                    @can('notification.mark_read')
                                    <button class="btn btn-outline-success btn-sm mark-read-btn" data-id="{{ $notification->id }}" title="Mark as Read">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    @endcan
                                @else
                                    @can('notification.mark_unread')
                                    <button class="btn btn-outline-secondary btn-sm mark-unread-btn" data-id="{{ $notification->id }}" title="Mark as Unread">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                    @endcan
                                @endif
                                @can('notification.delete')
                                <button class="btn btn-outline-danger btn-sm delete-btn" data-id="{{ $notification->id }}" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ auth()->user()->can('notification.delete') ? 8 : 7 }}">
                            <div class="text-center py-5">
                                <i class="fas fa-bell fa-4x text-muted mb-3 d-block"></i>
                                <h5 class="fw-bold">No Notifications Found</h5>
                                <p class="text-muted">Notifications will appear here.</p>
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
                Showing {{ $notifications->firstItem() ?? 0 }} to {{ $notifications->lastItem() ?? 0 }} of {{ $notifications->total() }} entries
            </span>
        </div>
        <div>
            {{ $notifications->links() }}
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
                    <i class="fas fa-plus-circle text-primary me-2"></i> Send Notification
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createForm" action="{{ route('admin.notifications.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div id="createFormErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">User <span class="text-danger">*</span></label>
                            <select name="user_id" class="form-select" required>
                                <option value="">Select User</option>
                                @foreach($users ?? [] as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required>
                                <option value="">Select Type</option>
                                <option value="warranty_expiry">Warranty Expiry</option>
                                <option value="low_stock">Low Stock</option>
                                <option value="assignment">Assignment</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Message <span class="text-danger">*</span></label>
                            <textarea name="message" class="form-control" rows="3" required placeholder="Enter notification message..."></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Link <span class="text-muted">(optional)</span></label>
                            <input type="url" name="link" class="form-control" placeholder="https://example.com/page">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="createSubmitBtn">
                        <i class="fas fa-paper-plane me-1"></i> Send Notification
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
                    <i class="fas fa-eye text-info me-2"></i> Notification Details
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
                    <i class="fas fa-edit text-warning me-2"></i> Edit Notification
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
                        <i class="fas fa-save me-1"></i> Update Notification
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
                <p class="fw-bold">Are you sure you want to delete this notification?</p>
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
    // MARK ALL AS READ
    // ==========================================
    @can('notification.mark_all_read')
    $('#markAllReadBtn').on('click', function() {
        Swal.fire({
            title: 'Mark All as Read?',
            text: 'This will mark all notifications as read.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0dcaf0',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, mark all read!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Processing...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: '{{ route("admin.notifications.mark-all-read") }}',
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
                            }).then(() => { window.location.reload(); });
                        }
                    },
                    error: function() {
                        Swal.close();
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Failed to mark all as read.'
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
    @can('notification.delete')
    $('#selectAll').on('change', function() {
        $('.row-checkbox').prop('checked', $(this).prop('checked'));
    });

    $('#bulkDeleteBtn').on('click', function() {
        var selected = $('.row-checkbox:checked');
        if (selected.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Items Selected',
                text: 'Please select at least one notification to delete.'
            });
            return;
        }

        Swal.fire({
            title: 'Delete Selected Notifications?',
            text: 'You are about to delete ' + selected.length + ' notification(s). This action cannot be undone.',
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
                    url: '{{ route("admin.notifications.bulk-delete") }}',
                    type: 'POST',
                    data: {
                        notification_ids: ids
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
                    error: function() {
                        Swal.close();
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Failed to delete selected notifications.'
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
            url: '/admin/notifications/' + id,
            type: 'GET',
            dataType: 'json',
            success: function(r) {
                var html = `
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="text-muted small fw-bold">User</label>
                            <p class="fw-bold mb-0">${r.user ? r.user.name : 'N/A'}</p>
                            <small class="text-muted">${r.user ? r.user.email : 'N/A'}</small>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Type</label>
                            <p class="mb-0"><span class="badge bg-${r.type == 'warranty_expiry' ? 'warning text-dark' : (r.type == 'low_stock' ? 'danger' : (r.type == 'assignment' ? 'info' : 'secondary'))}">${r.type.replace(/_/g, ' ').toUpperCase()}</span></p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Status</label>
                            <p class="mb-0"><span class="badge bg-${r.is_read ? 'success' : 'warning text-dark'}">${r.is_read ? 'Read' : 'Unread'}</span></p>
                        </div>
                        <div class="col-12">
                            <label class="text-muted small fw-bold">Message</label>
                            <p class="mb-0">${r.message || 'N/A'}</p>
                        </div>
                        ${r.link ? `
                        <div class="col-12">
                            <label class="text-muted small fw-bold">Link</label>
                            <p class="mb-0"><a href="${r.link}" target="_blank">${r.link}</a></p>
                        </div>` : ''}
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Created</label>
                            <p class="mb-0">${new Date(r.created_at).toLocaleString()}</p>
                        </div>
                        ${r.read_at ? `
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Read At</label>
                            <p class="mb-0">${new Date(r.read_at).toLocaleString()}</p>
                        </div>` : ''}
                    </div>
                `;
                body.html(html);
            },
            error: function(xhr) {
                body.html(`
                    <div class="text-center py-5 text-danger">
                        <i class="fas fa-exclamation-circle fa-4x mb-3 d-block"></i>
                        <h5>Failed to load notification details</h5>
                        <p>${xhr.status} - ${xhr.statusText}</p>
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

        form.attr('action', '/admin/notifications/' + id);
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
            url: '/admin/notifications/' + id + '/edit',
            type: 'GET',
            dataType: 'json',
            success: function(r) {
                body.html(`
                    <div id="editFormErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">User <span class="text-danger">*</span></label>
                            <select name="user_id" class="form-select" required>
                                <option value="">Select User</option>
                                @foreach($users ?? [] as $user)
                                    <option value="{{ $user->id }}" ${r.user_id == {{ $user->id }} ? 'selected' : ''}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required>
                                <option value="warranty_expiry" ${r.type == 'warranty_expiry' ? 'selected' : ''}>Warranty Expiry</option>
                                <option value="low_stock" ${r.type == 'low_stock' ? 'selected' : ''}>Low Stock</option>
                                <option value="assignment" ${r.type == 'assignment' ? 'selected' : ''}>Assignment</option>
                                <option value="maintenance" ${r.type == 'maintenance' ? 'selected' : ''}>Maintenance</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Message <span class="text-danger">*</span></label>
                            <textarea name="message" class="form-control" rows="3" required>${r.message || ''}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Link <span class="text-muted">(optional)</span></label>
                            <input type="url" name="link" class="form-control" value="${r.link || ''}" placeholder="https://example.com/page">
                        </div>
                    </div>
                `);
            },
            error: function(xhr) {
                body.html(`
                    <div id="editFormErrors" class="alert alert-danger d-none"></div>
                    <div class="text-center py-5 text-danger">
                        <i class="fas fa-exclamation-circle fa-4x mb-3 d-block"></i>
                        <h5>Failed to load notification data</h5>
                        <p>${xhr.status} - ${xhr.statusText}</p>
                    </div>
                `);
            }
        });
    });

    // ==========================================
    // CREATE
    // ==========================================
    $('#createForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        var data = form.serialize();
        var btn = $('#createSubmitBtn');
        var errorContainer = $('#createFormErrors');

        clearErrors(errorContainer);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Sending...');

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
                        title: 'Sent!',
                        text: r.message,
                        timer: 2000,
                        showConfirmButton: true
                    }).then(() => { window.location.reload(); });
                } else {
                    btn.prop('disabled', false).html('<i class="fas fa-paper-plane me-1"></i> Send Notification');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: r.message || 'Something went wrong.',
                    });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-paper-plane me-1"></i> Send Notification');
                if (xhr.status === 422) {
                    showErrors(errorContainer, xhr.responseJSON.errors);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to send notification. Please try again.'
                    });
                }
            }
        });
    });

    // ==========================================
    // UPDATE
    // ==========================================
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
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Update Notification');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: r.message || 'Something went wrong.',
                    });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Update Notification');
                if (xhr.status === 422) {
                    showErrors(errorContainer, xhr.responseJSON.errors);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to update notification. Please try again.'
                    });
                }
            }
        });
    });

    // ==========================================
    // MARK AS READ
    // ==========================================
    $(document).on('click', '.mark-read-btn', function() {
        var id = $(this).data('id');

        Swal.fire({
            title: 'Mark as Read?',
            text: 'This notification will be marked as read.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, mark read!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/admin/notifications/' + id + '/mark-read',
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    dataType: 'json',
                    success: function(r) {
                        if (r.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Done!',
                                text: r.message,
                                timer: 1500,
                                showConfirmButton: true
                            }).then(() => { window.location.reload(); });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Failed to mark as read.'
                        });
                    }
                });
            }
        });
    });

    // ==========================================
    // MARK AS UNREAD
    // ==========================================
    $(document).on('click', '.mark-unread-btn', function() {
        var id = $(this).data('id');

        Swal.fire({
            title: 'Mark as Unread?',
            text: 'This notification will be marked as unread.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#6c757d',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, mark unread!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/admin/notifications/' + id + '/mark-unread',
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    dataType: 'json',
                    success: function(r) {
                        if (r.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Done!',
                                text: r.message,
                                timer: 1500,
                                showConfirmButton: true
                            }).then(() => { window.location.reload(); });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Failed to mark as unread.'
                        });
                    }
                });
            }
        });
    });

    // ==========================================
    // DELETE
    // ==========================================
    $(document).on('click', '.delete-btn', function() {
        var id = $(this).data('id');
        var form = $('#deleteForm');
        form.attr('action', '/admin/notifications/' + id);

        Swal.fire({
            title: 'Are you sure?',
            text: "This notification will be permanently deleted.",
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
                        text: r.message || 'Failed to delete notification.',
                    });
                }
            },
            error: function(xhr) {
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to delete notification. Please try again.'
                });
            }
        });
    });

    // ==========================================
    // SEARCH & FILTER
    // ==========================================
    $('#searchBtn').on('click', function() { filterTable(); });
    $('#searchInput').on('keypress', function(e) {
        if (e.which === 13) filterTable();
    });
    $('#statusFilter').on('change', function() { filterTable(); });
    $('#typeFilter').on('change', function() { filterTable(); });

    function filterTable() {
        var search = $('#searchInput').val().toLowerCase();
        var status = $('#statusFilter').val();
        var type = $('#typeFilter').val();

        $('#tableBody tr').each(function() {
            var row = $(this);
            var text = row.text().toLowerCase();
            var rowStatus = row.data('status');
            var rowType = row.data('type');

            var show = true;
            if (search && !text.includes(search)) show = false;
            if (status && rowStatus !== status) show = false;
            if (type && rowType !== type) show = false;

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