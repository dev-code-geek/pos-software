@extends('layouts.admin')

@section('title', 'Specification Amendments')
@section('page-title', 'Specification Amendment Management')

@section('admin-content')
<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ $stats['total'] ?? 0 }}</h3>
                <p>Total Amendments</p>
            </div>
            <div class="icon">
                <i class="fas fa-edit"></i>
            </div>
            <a href="{{ route('admin.specification-amendments.index') }}" class="small-box-footer">
                View All <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $stats['pending'] ?? 0 }}</h3>
                <p>Pending Review</p>
            </div>
            <div class="icon">
                <i class="fas fa-clock"></i>
            </div>
            <a href="{{ route('admin.specification-amendments.index', ['status' => 'pending']) }}" class="small-box-footer">
                View Pending <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $stats['approved'] ?? 0 }}</h3>
                <p>Approved</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <a href="{{ route('admin.specification-amendments.index', ['status' => 'approved']) }}" class="small-box-footer">
                View Approved <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $stats['rejected'] ?? 0 }}</h3>
                <p>Rejected</p>
            </div>
            <div class="icon">
                <i class="fas fa-times-circle"></i>
            </div>
            <a href="{{ route('admin.specification-amendments.index', ['status' => 'rejected']) }}" class="small-box-footer">
                View Rejected <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Main Card -->
<div class="card">
    <div class="card-header">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <h5 class="card-title mb-0">
                <i class="fas fa-edit text-primary me-2"></i>
                All Specification Amendments
                <span class="badge bg-primary ms-1">{{ $amendments->total() }}</span>
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
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
            <select id="departmentFilter" class="form-select form-select-sm" style="width: 140px;">
                <option value="">All Departments</option>
                <option value="ict">ICT</option>
                <option value="director">Director</option>
                <option value="procurement">Procurement</option>
            </select>
            @can('asset_request.create')
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fas fa-plus-circle me-1"></i> Request Amendment
            </button>
            @endcan
            @can('asset_request.view')
            <a href="{{ route('admin.specification-amendments.export') }}" class="btn btn-success btn-sm">
                <i class="fas fa-file-excel me-1"></i> Export
            </a>
            @endcan
            @can('asset_request.approve')
            <button class="btn btn-success btn-sm" id="bulkApproveBtn">
                <i class="fas fa-check-double me-1"></i> Bulk Approve
            </button>
            @endcan
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        @can('asset_request.approve')
                        <th style="width: 35px;">
                            <input type="checkbox" id="selectAll">
                        </th>
                        @endcan
                        <th style="width: 35px;">#</th>
                        <th style="min-width: 140px;">Request</th>
                        <th style="min-width: 140px;">Requested By</th>
                        <th>From Department</th>
                        <th style="min-width: 120px;">Reason</th>
                        <th style="width: 100px;">Status</th>
                        <th style="width: 100px;">Date</th>
                        <th style="width: 220px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($amendments as $amendment)
                    <tr data-id="{{ $amendment->id }}" data-status="{{ $amendment->status }}" data-department="{{ $amendment->requested_from }}">
                        @can('asset_request.approve')
                        <td>
                            @if($amendment->status == 'pending')
                            <input type="checkbox" class="row-checkbox" value="{{ $amendment->id }}">
                            @endif
                        </td>
                        @endcan
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle bg-primary text-white me-2">
                                    <i class="fas fa-clipboard"></i>
                                </div>
                                <div>
                                    <div class="fw-bold small">#{{ $amendment->asset_request_id }}</div>
                                    <small class="text-muted">{{ Str::limit($amendment->assetRequest->title ?? 'N/A', 25) }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle bg-secondary text-white me-2" style="width:28px;height:28px;font-size:0.6rem;">
                                    {{ strtoupper(substr($amendment->requestedBy->name ?? 'U', 0, 2)) }}
                                </div>
                                <div>
                                    <div class="fw-bold small">{{ Str::limit($amendment->requestedBy->name ?? 'Unknown', 18) }}</div>
                                    <small class="text-muted">{{ Str::limit($amendment->requestedBy->email ?? 'N/A', 20) }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-{{ $amendment->requested_from == 'ict' ? 'info' : ($amendment->requested_from == 'director' ? 'primary' : 'warning text-dark') }}">
                                {{ ucfirst($amendment->requested_from) }}
                            </span>
                        </td>
                        <td>
                            <div class="fw-bold small">{{ Str::limit($amendment->reason_for_revision, 30) }}</div>
                        </td>
                        <td>
                            @php
                                $statusColors = [
                                    'pending' => 'bg-warning text-dark',
                                    'approved' => 'bg-success text-white',
                                    'rejected' => 'bg-danger text-white'
                                ];
                            @endphp
                            <span class="badge {{ $statusColors[$amendment->status] ?? 'bg-secondary' }}">
                                {{ ucfirst($amendment->status) }}
                            </span>
                        </td>
                        <td>
                            <small>{{ $amendment->created_at->format('M d, Y') }}</small>
                            <br>
                            <small class="text-muted">{{ $amendment->created_at->diffForHumans() }}</small>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                @can('asset_request.view')
                                <button class="btn btn-outline-primary btn-sm view-btn" data-id="{{ $amendment->id }}" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @endcan
                                @if($amendment->status == 'pending')
                                    @can('asset_request.edit')
                                    <button class="btn btn-outline-warning btn-sm edit-btn" data-id="{{ $amendment->id }}" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @endcan
                                    @can('asset_request.approve')
                                    <button class="btn btn-outline-success btn-sm approve-btn" data-id="{{ $amendment->id }}" title="Approve">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    @endcan
                                    @can('asset_request.approve')
                                    <button class="btn btn-outline-danger btn-sm reject-btn" data-id="{{ $amendment->id }}" title="Reject">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    @endcan
                                    @can('asset_request.delete')
                                    <button class="btn btn-outline-danger btn-sm delete-btn" data-id="{{ $amendment->id }}" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endcan
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ auth()->user()->can('asset_request.approve') ? 9 : 8 }}">
                            <div class="text-center py-5">
                                <i class="fas fa-edit fa-4x text-muted mb-3 d-block"></i>
                                <h5 class="fw-bold">No Specification Amendments Found</h5>
                                <p class="text-muted">Click "Request Amendment" to create your first amendment request.</p>
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
                Showing {{ $amendments->firstItem() ?? 0 }} to {{ $amendments->lastItem() ?? 0 }} of {{ $amendments->total() }} entries
            </span>
        </div>
        <div>
            {{ $amendments->links() }}
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
                    <i class="fas fa-plus-circle text-primary me-2"></i> Request Specification Amendment
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createForm" action="{{ route('admin.specification-amendments.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div id="createFormErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Asset Request <span class="text-danger">*</span></label>
                            <select name="asset_request_id" class="form-select" required>
                                <option value="">Select Request</option>
                                @foreach($assetRequests ?? [] as $request)
                                    <option value="{{ $request->id }}">#{{ $request->id }} - {{ Str::limit($request->title, 40) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Request From <span class="text-danger">*</span></label>
                            <select name="requested_from" class="form-select" required>
                                <option value="">Select Department</option>
                                <option value="ict">ICT</option>
                                <option value="director">Director</option>
                                <option value="procurement">Procurement</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Reason for Revision <span class="text-danger">*</span></label>
                            <textarea name="reason_for_revision" class="form-control" rows="3" required placeholder="Why do you need to revise the specifications?"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">New Specifications <span class="text-danger">*</span></label>
                            <textarea name="new_specifications" class="form-control" rows="4" required placeholder="Enter the new specifications..."></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Old Specifications <span class="text-muted">(optional)</span></label>
                            <textarea name="old_specifications" class="form-control" rows="3" placeholder="Enter the old specifications for reference..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="createSubmitBtn">
                        <i class="fas fa-paper-plane me-1"></i> Submit Request
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
                    <i class="fas fa-eye text-info me-2"></i> Amendment Details
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
                    <i class="fas fa-edit text-warning me-2"></i> Edit Amendment
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
                        <i class="fas fa-save me-1"></i> Update Amendment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- APPROVE MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-success">
                    <i class="fas fa-check-circle me-2"></i> Approve Amendment
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="approveForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div id="approveFormErrors" class="alert alert-danger d-none"></div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        This will approve the specification amendment and update the asset request.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Comments <span class="text-muted">(optional)</span></label>
                        <textarea name="comments" class="form-control" rows="3" placeholder="Add any comments..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-success" id="approveSubmitBtn">
                        <i class="fas fa-check me-1"></i> Approve
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- REJECT MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">
                    <i class="fas fa-times-circle me-2"></i> Reject Amendment
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div id="rejectFormErrors" class="alert alert-danger d-none"></div>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        This will reject the specification amendment. The requester will be notified.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-danger">Reason <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="3" required placeholder="Please provide a reason for rejection..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-danger" id="rejectSubmitBtn">
                        <i class="fas fa-times me-1"></i> Reject
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
                <p class="fw-bold">Are you sure you want to delete this amendment?</p>
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
    @can('asset_request.approve')
    $('#selectAll').on('change', function() {
        $('.row-checkbox').prop('checked', $(this).prop('checked'));
    });

    $('#bulkApproveBtn').on('click', function() {
        var selected = $('.row-checkbox:checked');
        if (selected.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Items Selected',
                text: 'Please select at least one pending amendment to approve.'
            });
            return;
        }

        Swal.fire({
            title: 'Approve Selected Amendments?',
            text: 'You are about to approve ' + selected.length + ' amendment(s).',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, approve them!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                var ids = [];
                selected.each(function() {
                    ids.push($(this).val());
                });

                Swal.fire({
                    title: 'Approving...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: '{{ route("admin.specification-amendments.bulk-approve") }}',
                    type: 'POST',
                    data: {
                        amendment_ids: ids,
                        comments: 'Bulk approved'
                    },
                    dataType: 'json',
                    success: function(r) {
                        if (r.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Approved!',
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
                            text: xhr.responseJSON?.error || 'Failed to approve amendments.'
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
    @can('asset_request.view')
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
            url: '/admin/specification-amendments/' + id,
            type: 'GET',
            dataType: 'json',
            success: function(r) {
                var amendment = r.amendment || r;
                var workflows = r.workflows || [];

                var html = `
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Asset Request</label>
                            <p class="fw-bold mb-0">#${amendment.asset_request_id}</p>
                            <small class="text-muted">${amendment.asset_request ? amendment.asset_request.title : 'N/A'}</small>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Requested By</label>
                            <p class="mb-0">${amendment.requested_by ? amendment.requested_by.name : 'N/A'}</p>
                            <small class="text-muted">${amendment.requested_from ? amendment.requested_from.toUpperCase() : 'N/A'}</small>
                        </div>
                        <div class="col-12">
                            <label class="text-muted small fw-bold">Reason for Revision</label>
                            <p class="mb-0">${amendment.reason_for_revision}</p>
                        </div>
                        ${amendment.old_specifications ? `
                        <div class="col-12">
                            <label class="text-muted small fw-bold text-danger">Old Specifications</label>
                            <p class="mb-0 bg-light p-2 rounded">${amendment.old_specifications}</p>
                        </div>` : ''}
                        <div class="col-12">
                            <label class="text-muted small fw-bold text-success">New Specifications</label>
                            <p class="mb-0 bg-light p-2 rounded">${amendment.new_specifications}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Status</label>
                            <p class="mb-0">
                                <span class="badge ${amendment.status == 'pending' ? 'bg-warning text-dark' : (amendment.status == 'approved' ? 'bg-success' : 'bg-danger')}">
                                    ${amendment.status.charAt(0).toUpperCase() + amendment.status.slice(1)}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Created</label>
                            <p class="mb-0">${new Date(amendment.created_at).toLocaleString()}</p>
                        </div>
                        ${amendment.reviewed_by ? `
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Reviewed By</label>
                            <p class="mb-0">${amendment.reviewed_by ? amendment.reviewed_by.name : 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Reviewed At</label>
                            <p class="mb-0">${new Date(amendment.reviewed_at).toLocaleString()}</p>
                        </div>` : ''}
                        ${workflows.length > 0 ? `
                        <div class="col-12">
                            <hr>
                            <h6 class="fw-bold">Workflow History</h6>
                            ${workflows.map(function(w) {
                                return `<div class="d-flex gap-2 mb-2">
                                    <small class="text-muted">${new Date(w.created_at).toLocaleString()}</small>
                                    <span class="badge bg-secondary">${w.action}</span>
                                    <span class="small">${w.comments || ''}</span>
                                </div>`;
                            }).join('')}
                        </div>` : ''}
                    </div>
                `;
                body.html(html);
            },
            error: function(xhr) {
                body.html(`
                    <div class="text-center py-5 text-danger">
                        <i class="fas fa-exclamation-circle fa-4x mb-3 d-block"></i>
                        <h5>Failed to load amendment details</h5>
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
    @can('asset_request.edit')
    $(document).on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        var body = $('#editModalBody');
        var form = $('#editForm');

        form.attr('action', '/admin/specification-amendments/' + id);
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
            url: '/admin/specification-amendments/' + id + '/edit',
            type: 'GET',
            dataType: 'json',
            success: function(r) {
                body.html(`
                    <div id="editFormErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Asset Request <span class="text-danger">*</span></label>
                            <select name="asset_request_id" class="form-select" required>
                                <option value="">Select Request</option>
                                @foreach($assetRequests ?? [] as $request)
                                    <option value="{{ $request->id }}" ${r.asset_request_id == {{ $request->id }} ? 'selected' : ''}>
                                        #{{ $request->id }} - {{ Str::limit($request->title, 40) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Request From <span class="text-danger">*</span></label>
                            <select name="requested_from" class="form-select" required>
                                <option value="ict" ${r.requested_from == 'ict' ? 'selected' : ''}>ICT</option>
                                <option value="director" ${r.requested_from == 'director' ? 'selected' : ''}>Director</option>
                                <option value="procurement" ${r.requested_from == 'procurement' ? 'selected' : ''}>Procurement</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Reason for Revision <span class="text-danger">*</span></label>
                            <textarea name="reason_for_revision" class="form-control" rows="3" required>${r.reason_for_revision || ''}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">New Specifications <span class="text-danger">*</span></label>
                            <textarea name="new_specifications" class="form-control" rows="4" required>${r.new_specifications || ''}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Old Specifications <span class="text-muted">(optional)</span></label>
                            <textarea name="old_specifications" class="form-control" rows="3">${r.old_specifications || ''}</textarea>
                        </div>
                    </div>
                `);
            },
            error: function(xhr) {
                body.html(`
                    <div id="editFormErrors" class="alert alert-danger d-none"></div>
                    <div class="text-center py-5 text-danger">
                        <i class="fas fa-exclamation-circle fa-4x mb-3 d-block"></i>
                        <h5>Failed to load amendment data</h5>
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
    @can('asset_request.create')
    $('#createForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        var data = form.serialize();
        var btn = $('#createSubmitBtn');
        var errorContainer = $('#createFormErrors');

        clearErrors(errorContainer);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Submitting...');

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
                        title: 'Submitted!',
                        text: r.message,
                        timer: 2000,
                        showConfirmButton: true
                    }).then(() => { window.location.reload(); });
                } else {
                    btn.prop('disabled', false).html('<i class="fas fa-paper-plane me-1"></i> Submit Request');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: r.message || 'Something went wrong.',
                    });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-paper-plane me-1"></i> Submit Request');
                if (xhr.status === 422) {
                    showErrors(errorContainer, xhr.responseJSON.errors);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to submit amendment request. Please try again.'
                    });
                }
            }
        });
    });
    @endcan

    // ==========================================
    // UPDATE
    // ==========================================
    @can('asset_request.edit')
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
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Update Amendment');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: r.message || 'Something went wrong.',
                    });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Update Amendment');
                if (xhr.status === 422) {
                    showErrors(errorContainer, xhr.responseJSON.errors);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to update amendment. Please try again.'
                    });
                }
            }
        });
    });
    @endcan

    // ==========================================
    // APPROVE
    // ==========================================
    @can('asset_request.approve')
    $(document).on('click', '.approve-btn', function() {
        var id = $(this).data('id');
        var form = $('#approveForm');
        form.attr('action', '/admin/specification-amendments/' + id + '/approve');
        clearErrors($('#approveFormErrors'));
        $('#approveModal').modal('show');
    });

    $('#approveForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        var data = form.serialize();
        var btn = $('#approveSubmitBtn');
        var errorContainer = $('#approveFormErrors');

        clearErrors(errorContainer);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Approving...');

        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(r) {
                if (r.success) {
                    $('#approveModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Approved!',
                        text: r.message,
                        timer: 2000,
                        showConfirmButton: true
                    }).then(() => { window.location.reload(); });
                } else {
                    btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i> Approve');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: r.message || 'Failed to approve.',
                    });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i> Approve');
                if (xhr.status === 422) {
                    showErrors(errorContainer, xhr.responseJSON.errors);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to approve amendment.'
                    });
                }
            }
        });
    });
    @endcan

    // ==========================================
    // REJECT
    // ==========================================
    @can('asset_request.approve')
    $(document).on('click', '.reject-btn', function() {
        var id = $(this).data('id');
        var form = $('#rejectForm');
        form.attr('action', '/admin/specification-amendments/' + id + '/reject');
        clearErrors($('#rejectFormErrors'));
        $('#rejectModal').modal('show');
    });

    $('#rejectForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        var data = form.serialize();
        var btn = $('#rejectSubmitBtn');
        var errorContainer = $('#rejectFormErrors');

        clearErrors(errorContainer);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Rejecting...');

        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(r) {
                if (r.success) {
                    $('#rejectModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Rejected!',
                        text: r.message,
                        timer: 2000,
                        showConfirmButton: true
                    }).then(() => { window.location.reload(); });
                } else {
                    btn.prop('disabled', false).html('<i class="fas fa-times me-1"></i> Reject');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: r.message || 'Failed to reject.',
                    });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-times me-1"></i> Reject');
                if (xhr.status === 422) {
                    showErrors(errorContainer, xhr.responseJSON.errors);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to reject amendment.'
                    });
                }
            }
        });
    });
    @endcan

    // ==========================================
    // DELETE
    // ==========================================
    @can('asset_request.delete')
    $(document).on('click', '.delete-btn', function() {
        var id = $(this).data('id');
        var form = $('#deleteForm');
        form.attr('action', '/admin/specification-amendments/' + id);

        Swal.fire({
            title: 'Are you sure?',
            text: "This amendment will be permanently deleted.",
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
                        text: r.message || 'Failed to delete amendment.',
                    });
                }
            },
            error: function(xhr) {
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: xhr.responseJSON?.error || 'Failed to delete amendment.'
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
    $('#statusFilter').on('change', function() { filterTable(); });
    $('#departmentFilter').on('change', function() { filterTable(); });

    function filterTable() {
        var search = $('#searchInput').val().toLowerCase();
        var status = $('#statusFilter').val();
        var department = $('#departmentFilter').val();

        $('#tableBody tr').each(function() {
            var row = $(this);
            var text = row.text().toLowerCase();
            var rowStatus = row.data('status');
            var rowDepartment = row.data('department');

            var show = true;
            if (search && !text.includes(search)) show = false;
            if (status && rowStatus !== status) show = false;
            if (department && rowDepartment !== department) show = false;

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