@extends('layouts.admin')

@section('title', 'Assignments')
@section('page-title', 'Assignment Management')

@section('admin-content')
<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ $stats['total'] ?? $assignments->total() ?? 0 }}</h3>
                <p>Total Assignments</p>
            </div>
            <div class="icon">
                <i class="fas fa-handshake"></i>
            </div>
            <a href="{{ route('admin.assignments.index') }}" class="small-box-footer">
                View All <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $stats['active'] ?? 0 }}</h3>
                <p>Active Assignments</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <a href="{{ route('admin.assignments.index', ['status' => 'active']) }}" class="small-box-footer">
                View Active <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $stats['overdue'] ?? 0 }}</h3>
                <p>Overdue Returns</p>
            </div>
            <div class="icon">
                <i class="fas fa-clock"></i>
            </div>
            <a href="{{ route('admin.assignments.index', ['status' => 'overdue']) }}" class="small-box-footer">
                View Overdue <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $stats['returned'] ?? 0 }}</h3>
                <p>Returned</p>
            </div>
            <div class="icon">
                <i class="fas fa-undo-alt"></i>
            </div>
            <a href="{{ route('admin.assignments.index', ['status' => 'returned']) }}" class="small-box-footer">
                View Returned <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Main Card -->
<div class="card">
    <div class="card-header">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <h5 class="card-title mb-0">
                <i class="fas fa-handshake text-primary me-2"></i>
                All Assignments
                <span class="badge bg-primary ms-1">{{ $assignments->total() }}</span>
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
                <option value="active">Active</option>
                <option value="returned">Returned</option>
                <option value="overdue">Overdue</option>
            </select>
            @can('assignment.create')
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fas fa-plus-circle me-1"></i> Assign Asset
            </button>
            @endcan
            @can('assignment.export')
            <a href="{{ route('admin.assignments.export') }}" class="btn btn-success btn-sm">
                <i class="fas fa-file-excel me-1"></i> Export
            </a>
            @endcan
            @can('assignment.delete')
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
                        @can('assignment.delete')
                        <th style="width: 35px;">
                            <input type="checkbox" id="selectAll">
                        </th>
                        @endcan
                        <th style="width: 35px;">#</th>
                        <th style="min-width: 140px;">Asset</th>
                        <th style="min-width: 140px;">Assigned To</th>
                        <th>Assigned By</th>
                        <th style="width: 100px;">Date</th>
                        <th style="width: 100px;">Return Date</th>
                        <th style="width: 80px;">Status</th>
                        <th style="width: 230px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($assignments as $assignment)
                    <tr data-id="{{ $assignment->id }}" data-status="{{ $assignment->actual_return_date ? 'returned' : ($assignment->expected_return_date && $assignment->expected_return_date < now() ? 'overdue' : 'active') }}">
                        @can('assignment.delete')
                        <td>
                            <input type="checkbox" class="row-checkbox" value="{{ $assignment->id }}">
                        </td>
                        @endcan
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle bg-primary text-white me-2">
                                    <i class="fas fa-box"></i>
                                </div>
                                <div>
                                    <div class="fw-bold small">{{ Str::limit($assignment->product->name ?? 'N/A', 20) }}</div>
                                    <small class="text-muted">{{ $assignment->product->sku ?? 'N/A' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle bg-success text-white me-2" style="width:28px;height:28px;font-size:0.6rem;">
                                    {{ strtoupper(substr($assignment->user->name ?? 'U', 0, 2)) }}
                                </div>
                                <div>
                                    <div class="fw-bold small">{{ Str::limit($assignment->user->name ?? 'Unknown', 18) }}</div>
                                    <small class="text-muted">{{ Str::limit($assignment->user->email ?? 'N/A', 20) }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <small>{{ $assignment->assignedBy->name ?? 'N/A' }}</small>
                        </td>
                        <td>
                            <small>{{ $assignment->assigned_date ? $assignment->assigned_date->format('M d, Y') : 'N/A' }}</small>
                        </td>
                        <td>
                            <small>{{ $assignment->expected_return_date ? $assignment->expected_return_date->format('M d, Y') : 'N/A' }}</small>
                            @if($assignment->expected_return_date && $assignment->expected_return_date < now() && !$assignment->actual_return_date)
                                <span class="badge bg-danger ms-1">Overdue</span>
                            @endif
                        </td>
                        <td>
                            @if($assignment->actual_return_date)
                                <span class="badge bg-secondary">Returned</span>
                            @elseif($assignment->expected_return_date && $assignment->expected_return_date < now())
                                <span class="badge bg-danger">Overdue</span>
                            @else
                                <span class="badge bg-success">Active</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                @can('assignment.view')
                                <button class="btn btn-outline-primary btn-sm view-btn" data-id="{{ $assignment->id }}" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @endcan

                                @if(!$assignment->actual_return_date)
                                    @can('assignment.edit')
                                    <button class="btn btn-outline-warning btn-sm edit-btn" data-id="{{ $assignment->id }}" title="Edit Assignment">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @endcan
                                    @can('assignment.edit')
                                    <button class="btn btn-outline-success btn-sm return-btn" data-id="{{ $assignment->id }}" title="Mark Returned">
                                        <i class="fas fa-undo-alt"></i>
                                    </button>
                                    @endcan
                                    @can('assignment.edit')
                                    <button class="btn btn-outline-info btn-sm extend-btn" data-id="{{ $assignment->id }}" title="Extend Return Date">
                                        <i class="fas fa-clock"></i>
                                    </button>
                                    @endcan
                                @endif

                                @if($assignment->actual_return_date)
                                    @can('assignment.delete')
                                    <button class="btn btn-outline-danger btn-sm delete-btn" data-id="{{ $assignment->id }}" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endcan
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ auth()->user()->can('assignment.delete') ? 9 : 8 }}">
                            <div class="text-center py-5">
                                <i class="fas fa-handshake fa-4x text-muted mb-3 d-block"></i>
                                <h5 class="fw-bold">No Assignments Found</h5>
                                <p class="text-muted">Click "Assign Asset" to create your first assignment.</p>
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
                Showing {{ $assignments->firstItem() ?? 0 }} to {{ $assignments->lastItem() ?? 0 }} of {{ $assignments->total() }} entries
            </span>
        </div>
        <div>
            {{ $assignments->links() }}
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- CREATE ASSIGNMENT MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-handshake text-primary me-2"></i> Assign Asset
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createAssignmentForm" action="{{ route('admin.assignments.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div id="createFormErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Product <span class="text-danger">*</span></label>
                            <select name="product_id" class="form-select" required>
                                <option value="">Select Product</option>
                                @foreach($products ?? [] as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }}) - Qty: {{ $product->quantity }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">User <span class="text-danger">*</span></label>
                            <select name="user_id" class="form-select" required>
                                <option value="">Select User</option>
                                @foreach($users ?? [] as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Assigned Date <span class="text-danger">*</span></label>
                            <input type="date" name="assigned_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Expected Return Date</label>
                            <input type="date" name="expected_return_date" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Condition (Out) <span class="text-danger">*</span></label>
                            <select name="condition_out" class="form-select" required>
                                <option value="">Select Condition</option>
                                <option value="new">New</option>
                                <option value="good">Good</option>
                                <option value="fair">Fair</option>
                                <option value="poor">Poor</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Notes</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Optional notes..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="submitAssignmentBtn">
                        <i class="fas fa-check me-1"></i> Assign Asset
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- EDIT MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit text-warning me-2"></i> Edit Assignment
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editAssignmentForm" method="POST">
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
                        <i class="fas fa-save me-1"></i> Update Assignment
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
                    <i class="fas fa-eye text-info me-2"></i> Assignment Details
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
<!-- RETURN MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="returnModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-success">
                    <i class="fas fa-undo-alt me-2"></i> Return Asset
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="returnForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div id="returnFormErrors" class="alert alert-danger d-none"></div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Mark this asset as returned. The product stock will be updated.
                    </div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Condition (In) <span class="text-danger">*</span></label>
                            <select name="condition_in" class="form-select" required>
                                <option value="">Select Condition</option>
                                <option value="good">Good</option>
                                <option value="fair">Fair</option>
                                <option value="poor">Poor</option>
                                <option value="damaged">Damaged</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Notes</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Return notes..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-success" id="returnSubmitBtn">
                        <i class="fas fa-undo-alt me-1"></i> Return Asset
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- EXTEND MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="extendModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-info">
                    <i class="fas fa-clock me-2"></i> Extend Return Date
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="extendForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div id="extendFormErrors" class="alert alert-danger d-none"></div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Extend the return date for this assignment.
                    </div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">New Return Date <span class="text-danger">*</span></label>
                            <input type="date" name="new_return_date" class="form-control" required>
                            <small class="text-muted">Must be a date after today.</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Reason <span class="text-muted">(optional)</span></label>
                            <textarea name="reason" class="form-control" rows="3" placeholder="Reason for extension..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-info" id="extendSubmitBtn">
                        <i class="fas fa-clock me-1"></i> Extend Return Date
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
                <p class="fw-bold">Are you sure you want to delete this assignment?</p>
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
    @can('assignment.delete')
    $('#selectAll').on('change', function() {
        $('.row-checkbox').prop('checked', $(this).prop('checked'));
    });

    $('#bulkDeleteBtn').on('click', function() {
        var selected = $('.row-checkbox:checked');
        if (selected.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Items Selected',
                text: 'Please select at least one assignment to delete.'
            });
            return;
        }

        Swal.fire({
            title: 'Delete Selected Assignments?',
            text: 'You are about to delete ' + selected.length + ' assignment(s). This action cannot be undone.',
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
                    url: '{{ route("admin.assignments.bulk-delete") }}',
                    type: 'POST',
                    data: {
                        assignment_ids: ids
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
                            text: xhr.responseJSON?.error || 'Failed to delete selected assignments.'
                        });
                    }
                });
            }
        });
    });
    @endcan

    // ==========================================
    // CREATE ASSIGNMENT
    // ==========================================
    $('#createAssignmentForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        var data = form.serialize();
        var btn = $('#submitAssignmentBtn');
        var errorContainer = $('#createFormErrors');

        clearErrors(errorContainer);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

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
                        title: 'Success!',
                        text: r.message,
                        timer: 2000,
                        showConfirmButton: true
                    }).then(() => { window.location.reload(); });
                } else {
                    btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i> Assign Asset');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: r.message || 'Failed to create assignment.'
                    });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i> Assign Asset');
                if (xhr.status === 422) {
                    showErrors(errorContainer, xhr.responseJSON.errors);
                } else if (xhr.responseJSON?.error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: xhr.responseJSON.error
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to create assignment. Please try again.'
                    });
                }
            }
        });
    });

    // ==========================================
    // EDIT ASSIGNMENT
    // ==========================================
    $(document).on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        var body = $('#editModalBody');
        var form = $('#editAssignmentForm');

        form.attr('action', '/admin/assignments/' + id);
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
            url: '/admin/assignments/' + id + '/edit',
            type: 'GET',
            dataType: 'json',
            success: function(r) {
                body.html(`
                    <div id="editFormErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Product <span class="text-danger">*</span></label>
                            <select name="product_id" class="form-select" required>
                                <option value="">Select Product</option>
                                @foreach($products ?? [] as $product)
                                    <option value="{{ $product->id }}" ${r.product_id == {{ $product->id }} ? 'selected' : ''}>
                                        {{ $product->name }} ({{ $product->sku }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">User <span class="text-danger">*</span></label>
                            <select name="user_id" class="form-select" required>
                                <option value="">Select User</option>
                                @foreach($users ?? [] as $user)
                                    <option value="{{ $user->id }}" ${r.user_id == {{ $user->id }} ? 'selected' : ''}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Assigned Date <span class="text-danger">*</span></label>
                            <input type="date" name="assigned_date" class="form-control" value="${r.assigned_date ? new Date(r.assigned_date).toISOString().split('T')[0] : ''}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Expected Return Date</label>
                            <input type="date" name="expected_return_date" class="form-control" value="${r.expected_return_date ? new Date(r.expected_return_date).toISOString().split('T')[0] : ''}">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Condition (Out) <span class="text-danger">*</span></label>
                            <select name="condition_out" class="form-select" required>
                                <option value="new" ${r.condition_out == 'new' ? 'selected' : ''}>New</option>
                                <option value="good" ${r.condition_out == 'good' ? 'selected' : ''}>Good</option>
                                <option value="fair" ${r.condition_out == 'fair' ? 'selected' : ''}>Fair</option>
                                <option value="poor" ${r.condition_out == 'poor' ? 'selected' : ''}>Poor</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Notes</label>
                            <textarea name="notes" class="form-control" rows="3">${r.notes || ''}</textarea>
                        </div>
                    </div>
                `);
            },
            error: function() {
                body.html(`
                    <div id="editFormErrors" class="alert alert-danger d-none"></div>
                    <div class="text-center py-5 text-danger">
                        <i class="fas fa-exclamation-circle fa-4x mb-3 d-block"></i>
                        <h5>Failed to load assignment data</h5>
                    </div>
                `);
            }
        });
    });

    $('#editAssignmentForm').on('submit', function(e) {
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
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Update Assignment');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: r.message || 'Failed to update assignment.'
                    });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Update Assignment');
                if (xhr.status === 422) {
                    showErrors(errorContainer, xhr.responseJSON.errors);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to update assignment. Please try again.'
                    });
                }
            }
        });
    });

    // ==========================================
    // VIEW ASSIGNMENT
    // ==========================================
    $('.view-btn').on('click', function() {
        var id = $(this).data('id');
        var modal = $('#viewModal');
        var body = $('#viewModalBody');

        body.html('<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');
        modal.modal('show');

        $.ajax({
            url: '/admin/assignments/' + id,
            type: 'GET',
            success: function(r) {
                var statusBadge = r.actual_return_date ? 'bg-secondary' : 
                                 (r.expected_return_date && new Date(r.expected_return_date) < new Date() ? 'bg-danger' : 'bg-success');
                var statusText = r.actual_return_date ? 'Returned' : 
                                 (r.expected_return_date && new Date(r.expected_return_date) < new Date() ? 'Overdue' : 'Active');

                body.html(`
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Asset</label>
                            <p class="fw-bold mb-0">${r.product ? r.product.name : 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">SKU</label>
                            <p class="mb-0">${r.product ? r.product.sku : 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Assigned To</label>
                            <p class="mb-0">${r.user ? r.user.name : 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Assigned By</label>
                            <p class="mb-0">${r.assigned_by ? r.assigned_by.name : 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Assigned Date</label>
                            <p class="mb-0">${r.assigned_date ? new Date(r.assigned_date).toLocaleDateString() : 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Expected Return</label>
                            <p class="mb-0">${r.expected_return_date ? new Date(r.expected_return_date).toLocaleDateString() : 'N/A'}</p>
                        </div>
                        ${r.actual_return_date ? `
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Actual Return</label>
                            <p class="mb-0">${new Date(r.actual_return_date).toLocaleDateString()}</p>
                        </div>` : ''}
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Status</label>
                            <p class="mb-0">
                                <span class="badge ${statusBadge}">${statusText}</span>
                            </p>
                        </div>
                        ${r.condition_out ? `
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Condition (Out)</label>
                            <p class="mb-0">${r.condition_out.charAt(0).toUpperCase() + r.condition_out.slice(1)}</p>
                        </div>` : ''}
                        ${r.condition_in ? `
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Condition (In)</label>
                            <p class="mb-0">${r.condition_in.charAt(0).toUpperCase() + r.condition_in.slice(1)}</p>
                        </div>` : ''}
                        ${r.notes ? `
                        <div class="col-12">
                            <label class="text-muted small fw-bold">Notes</label>
                            <p class="mb-0">${r.notes}</p>
                        </div>` : ''}
                    </div>
                `);
            },
            error: function() {
                body.html(`<div class="text-center py-5 text-danger"><i class="fas fa-exclamation-circle fa-4x mb-3 d-block"></i><h5>Failed to load assignment details</h5></div>`);
            }
        });
    });

    // ==========================================
    // RETURN ASSIGNMENT
    // ==========================================
    $('.return-btn').on('click', function() {
        var id = $(this).data('id');
        var form = $('#returnForm');
        var url = '/admin/assignments/' + id + '/return';
        form.attr('action', url);
        clearErrors($('#returnFormErrors'));
        $('#returnModal').modal('show');
    });

    $('#returnForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        var data = form.serialize();
        var btn = $('#returnSubmitBtn');
        var errorContainer = $('#returnFormErrors');

        clearErrors(errorContainer);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Processing...');

        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(r) {
                if (r.success) {
                    $('#returnModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Returned!',
                        text: r.message,
                        timer: 2000,
                        showConfirmButton: true
                    }).then(() => { window.location.reload(); });
                } else {
                    btn.prop('disabled', false).html('<i class="fas fa-undo-alt me-1"></i> Return Asset');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: r.message || 'Failed to return asset.'
                    });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-undo-alt me-1"></i> Return Asset');
                if (xhr.status === 422) {
                    showErrors(errorContainer, xhr.responseJSON.errors);
                } else if (xhr.responseJSON?.error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: xhr.responseJSON.error
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to return asset. Please try again.'
                    });
                }
            }
        });
    });

    // ==========================================
    // EXTEND RETURN DATE
    // ==========================================
    $('.extend-btn').on('click', function() {
        var id = $(this).data('id');
        var form = $('#extendForm');
        var url = '/admin/assignments/' + id + '/extend';
        form.attr('action', url);
        clearErrors($('#extendFormErrors'));
        
        // Set min date to tomorrow
        var tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        $('input[name="new_return_date"]').attr('min', tomorrow.toISOString().split('T')[0]);
        
        $('#extendModal').modal('show');
    });

    $('#extendForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        var data = form.serialize();
        var btn = $('#extendSubmitBtn');
        var errorContainer = $('#extendFormErrors');

        clearErrors(errorContainer);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Processing...');

        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(r) {
                if (r.success) {
                    $('#extendModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Extended!',
                        text: r.message,
                        timer: 2000,
                        showConfirmButton: true
                    }).then(() => { window.location.reload(); });
                } else {
                    btn.prop('disabled', false).html('<i class="fas fa-clock me-1"></i> Extend Return Date');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: r.message || 'Failed to extend return date.'
                    });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-clock me-1"></i> Extend Return Date');
                if (xhr.status === 422) {
                    showErrors(errorContainer, xhr.responseJSON.errors);
                } else if (xhr.responseJSON?.error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: xhr.responseJSON.error
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to extend return date. Please try again.'
                    });
                }
            }
        });
    });

    // ==========================================
    // DELETE ASSIGNMENT
    // ==========================================
    $('.delete-btn').on('click', function() {
        var id = $(this).data('id');
        var form = $('#deleteForm');
        var url = '/admin/assignments/' + id;
        form.attr('action', url);

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
            data: {
                _token: '{{ csrf_token() }}',
                _method: 'DELETE'
            },
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
                    Swal.fire({ icon: 'error', title: 'Error!', text: r.message || 'Something went wrong.' });
                }
            },
            error: function(xhr) {
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: xhr.responseJSON?.error || 'Failed to delete assignment.'
                });
            }
        });
    });

    // ==========================================
    // SEARCH & FILTER
    // ==========================================
    $('#searchBtn').on('click', function() { filterTable(); });
    $('#searchInput').on('keypress', function(e) { if (e.which === 13) filterTable(); });
    $('#statusFilter').on('change', function() { filterTable(); });

    function filterTable() {
        var search = $('#searchInput').val().toLowerCase();
        var status = $('#statusFilter').val();
        $('#tableBody tr').each(function() {
            var row = $(this);
            var text = row.text().toLowerCase();
            var rowStatus = row.data('status');
            var show = true;
            if (search && !text.includes(search)) show = false;
            if (status && rowStatus !== status) show = false;
            row.toggle(show);
        });
    }

    // ==========================================
    // FIX MODAL BACKDROP
    // ==========================================
    $('.modal').on('hidden.bs.modal', function() {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
        // Clear errors when modal closes
        $(this).find('.alert-danger').addClass('d-none').empty();
        // Reset forms
        $(this).find('form')[0]?.reset();
    });
});
</script>
@endpush
@endsection