@extends('layouts.admin')

@section('title', 'Asset Requests')
@section('page-title', 'Asset Request Management')

@section('admin-content')
<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ $stats['total'] ?? $requests->total() ?? 0 }}</h3>
                <p>Total Requests</p>
            </div>
            <div class="icon">
                <i class="fas fa-clipboard-list"></i>
            </div>
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
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $stats['assigned'] ?? 0 }}</h3>
                <p>Assigned</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-check"></i>
            </div>
        </div>
    </div>
</div>

<!-- Main Card -->
<div class="card">
    <div class="card-header">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <h5 class="card-title mb-0">
                <i class="fas fa-clipboard-list text-primary me-2"></i>
                All Asset Requests
                <span class="badge bg-primary ms-1">{{ $requests->total() }}</span>
            </h5>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <div class="input-group input-group-sm" style="width: 180px;">
                <input type="text" id="searchInput" class="form-control" placeholder="Search...">
                <button class="btn btn-secondary" type="button" id="searchBtn">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            <select id="statusFilter" class="form-select form-select-sm" style="width: 120px;">
                <option value="">All Status</option>
                <option value="pending_ict_review">Pending</option>
                <option value="ict_reviewing">Reviewing</option>
                <option value="approved_by_ict">Approved</option>
                <option value="rejected_by_ict">Rejected</option>
                <option value="sent_to_director">With Director</option>
                <option value="sent_to_procurement">With Procurement</option>
                <option value="assigned_to_user">Assigned</option>
                <option value="completed">Completed</option>
            </select>
            @can('asset_request.create')
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fas fa-plus-circle me-1"></i> New Request
            </button>
            @endcan
            @can('asset_request.export')
            <a href="{{ route('admin.asset-requests.export') }}" class="btn btn-success btn-sm">
                <i class="fas fa-file-excel me-1"></i> Export
            </a>
            @endcan
            @can('asset_request.bulk_delete')
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
                        @can('asset_request.bulk_delete')
                        <th style="width: 35px;">
                            <input type="checkbox" id="selectAll">
                        </th>
                        @endcan
                        <th style="width: 35px;">#</th>
                        <th style="min-width: 150px;">Requestor</th>
                        <th style="min-width: 150px;">Title</th>
                        <th>Category</th>
                        <th style="width: 40px; text-align: center;">Qty</th>
                        <th style="width: 80px;">Priority</th>
                        <th style="width: 110px;">Status</th>
                        <th style="width: 90px;">Date</th>
                        <th style="width: 220px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($requests as $request)
                    <tr data-id="{{ $request->id }}" data-status="{{ $request->status }}">
                        @can('asset_request.bulk_delete')
                        <td>
                            <input type="checkbox" class="row-checkbox" value="{{ $request->id }}">
                        </td>
                        @endcan
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle bg-primary text-white me-2">
                                    {{ strtoupper(substr($request->user->name ?? 'U', 0, 2)) }}
                                </div>
                                <div>
                                    <div class="fw-bold small">{{ Str::limit($request->user->name ?? 'Unknown', 18) }}</div>
                                    <small class="text-muted">{{ Str::limit($request->user->email ?? 'N/A', 20) }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="fw-bold">{{ Str::limit($request->title, 28) }}</div>
                            <small class="text-muted">
                                <i class="fas fa-tag me-1"></i>
                                {{ ucfirst($request->request_type ?? 'N/A') }}
                            </small>
                        </td>
                        <td>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                {{ $request->category->name ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="text-center fw-bold">{{ $request->quantity_requested }}</td>
                        <td>
                            @php
                                $priorityColors = [
                                    'urgent' => 'danger',
                                    'high' => 'warning text-dark',
                                    'medium' => 'info',
                                    'low' => 'secondary'
                                ];
                            @endphp
                            <span class="badge bg-{{ $priorityColors[$request->priority] ?? 'secondary' }}">
                                <i class="fas fa-{{ $request->priority == 'urgent' ? 'exclamation-circle' : ($request->priority == 'high' ? 'arrow-up' : ($request->priority == 'medium' ? 'minus' : 'arrow-down')) }} me-1"></i>
                                {{ ucfirst($request->priority) }}
                            </span>
                        </td>
                        <td>
                            @php
                                $statusClasses = [
                                    'pending_ict_review' => 'bg-warning text-dark',
                                    'ict_reviewing' => 'bg-info text-white',
                                    'approved_by_ict' => 'bg-success text-white',
                                    'rejected_by_ict' => 'bg-danger text-white',
                                    'sent_to_director' => 'bg-primary text-white',
                                    'approved_by_director' => 'bg-primary text-white',
                                    'rejected_by_director' => 'bg-danger text-white',
                                    'sent_to_procurement' => 'bg-primary text-white',
                                    'procurement_reviewing' => 'bg-info text-white',
                                    'specs_revision_needed' => 'bg-warning text-dark',
                                    'ready_for_purchase' => 'bg-success text-white',
                                    'ordered' => 'bg-primary text-white',
                                    'received' => 'bg-success text-white',
                                    'under_inspection' => 'bg-info text-white',
                                    'inspection_passed' => 'bg-success text-white',
                                    'inspection_failed' => 'bg-danger text-white',
                                    'assigned_to_user' => 'bg-primary text-white',
                                    'completed' => 'bg-secondary text-white'
                                ];
                            @endphp
                            <span class="badge {{ $statusClasses[$request->status] ?? 'bg-secondary' }}">
                                {{ str_replace('_', ' ', $request->status) }}
                            </span>
                        </td>
                        <td>
                            <small>{{ $request->created_at->format('M d, Y') }}</small>
                            <br>
                            <small class="text-muted">{{ $request->created_at->diffForHumans() }}</small>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <!-- View -->
                                @can('asset_request.view')
                                <button type="button" class="btn btn-outline-primary btn-sm view-btn" data-id="{{ $request->id }}" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @endcan

                                @if(in_array($request->status, ['pending_ict_review', 'ict_reviewing']))
                                    <!-- Edit -->
                                    @can('asset_request.edit')
                                    <button type="button" class="btn btn-outline-warning btn-sm edit-btn" data-id="{{ $request->id }}" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @endcan

                                    <!-- Review/Approve -->
                                    @can('asset_request.review')
                                    <button type="button" class="btn btn-outline-success btn-sm approve-btn" data-id="{{ $request->id }}" title="Review & Approve">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    @endcan

                                    <!-- Reject -->
                                    @can('asset_request.reject')
                                    <button type="button" class="btn btn-outline-danger btn-sm reject-btn" data-id="{{ $request->id }}" title="Reject">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    @endcan

                                    <!-- Forward to Director -->
                                    @can('asset_request.forward_to_director')
                                    <button type="button" class="btn btn-outline-primary btn-sm forward-btn" data-id="{{ $request->id }}" title="Forward to Director">
                                        <i class="fas fa-arrow-right"></i>
                                    </button>
                                    @endcan

                                    <!-- Assign Asset -->
                                    @can('asset_request.specify')
                                    <button type="button" class="btn btn-outline-info btn-sm assign-btn" data-id="{{ $request->id }}" title="Assign Asset">
                                        <i class="fas fa-handshake"></i>
                                    </button>
                                    @endcan
                                @endif

                                @if($request->status == 'approved_by_director')
                                    <!-- Forward to Procurement -->
                                    @can('asset_request.forward_to_procurement')
                                    <button type="button" class="btn btn-outline-primary btn-sm forward-procurement-btn" data-id="{{ $request->id }}" title="Forward to Procurement">
                                        <i class="fas fa-shopping-cart"></i>
                                    </button>
                                    @endcan
                                @endif

                                @if(in_array($request->status, ['pending_ict_review', 'ict_reviewing', 'rejected_by_ict']))
                                    <!-- Delete -->
                                    @can('asset_request.delete')
                                    <button type="button" class="btn btn-outline-danger btn-sm delete-btn" data-id="{{ $request->id }}" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endcan
                                @endif

                                <!-- Cancel (for pending requests) -->
                                @if(in_array($request->status, ['pending_ict_review', 'ict_reviewing']) && auth()->id() == $request->user_id)
                                    @can('asset_request.cancel')
                                    <button type="button" class="btn btn-outline-secondary btn-sm cancel-btn" data-id="{{ $request->id }}" title="Cancel Request">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                    @endcan
                                @endif

                                <!-- Timeline -->
                                <button type="button" class="btn btn-outline-secondary btn-sm timeline-btn" data-id="{{ $request->id }}" title="Timeline">
                                    <i class="fas fa-history"></i>
                                </button>
                            </div>
                        </td>
                    </tr> 
                    @empty
                    <tr>
                        <td colspan="{{ auth()->user()->can('asset_request.bulk_delete') ? 10 : 9 }}">
                            <div class="text-center py-5">
                                <i class="fas fa-clipboard-list fa-4x text-muted mb-3 d-block"></i>
                                <h5 class="fw-bold">No Asset Requests Found</h5>
                                <p class="text-muted">Click "New Request" to create your first request.</p>
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
                Showing {{ $requests->firstItem() ?? 0 }} to {{ $requests->lastItem() ?? 0 }} of {{ $requests->total() }} entries
            </span>
        </div>
        <div>
            {{ $requests->links() }}
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- CREATE MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle text-primary me-2"></i> New Asset Request
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createForm" action="{{ route('admin.asset-requests.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div id="createFormErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="request_title" class="form-label fw-bold">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="request_title" class="form-control" required placeholder="Enter request title">
                        </div>
                        <div class="col-md-6">
                            <label for="request_type" class="form-label fw-bold">Request Type <span class="text-danger">*</span></label>
                            <select name="request_type" id="request_type" class="form-select" required>
                                <option value="new">New Asset</option>
                                <option value="replacement">Replacement</option>
                                <option value="upgrade">Upgrade</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="request_category" class="form-label fw-bold">Category</label>
                            <select name="category_id" id="request_category" class="form-select">
                                <option value="">Select Category</option>
                                @foreach($categories ?? [] as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="request_quantity" class="form-label fw-bold">Quantity <span class="text-danger">*</span></label>
                            <input type="number" name="quantity_requested" id="request_quantity" class="form-control" value="1" min="1" required>
                        </div>
                        <div class="col-md-6">
                            <label for="request_priority" class="form-label fw-bold">Priority <span class="text-danger">*</span></label>
                            <select name="priority" id="request_priority" class="form-select" required>
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="request_description" class="form-label fw-bold">Description <span class="text-danger">*</span></label>
                            <textarea name="description" id="request_description" class="form-control" rows="3" required placeholder="Describe what you need and why..."></textarea>
                        </div>
                        <div class="col-12">
                            <label for="request_specs" class="form-label fw-bold">Specifications</label>
                            <textarea name="specifications" id="request_specs" class="form-control" rows="2" placeholder="Technical specifications, model numbers, etc."></textarea>
                        </div>
                        <div class="col-12">
                            <label for="request_justification" class="form-label fw-bold">Justification</label>
                            <textarea name="justification" id="request_justification" class="form-control" rows="2" placeholder="Why do you need this asset?"></textarea>
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
                    <i class="fas fa-eye text-info me-2"></i> Request Details
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
    <div class="modal-dialog modal-dialog-centered" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit text-warning me-2"></i> Edit Request
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
                        <i class="fas fa-save me-1"></i> Update Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- REVIEW/APPROVE MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle text-success me-2"></i> Review & Approve Request
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="approveForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div id="approveFormErrors" class="alert alert-danger d-none"></div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        This will review and process the request. The user will be notified.
                    </div>
                    <div class="mb-3">
                        <label for="approve_action" class="form-label fw-bold">Action <span class="text-danger">*</span></label>
                        <select name="action" id="approve_action" class="form-select" required>
                            <option value="approve">Approve</option>
                            <option value="forward_director">Forward to Director</option>
                            <option value="assign_existing">Assign Existing Asset</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="approve_notes" class="form-label fw-bold">Notes <span class="text-muted">(optional)</span></label>
                        <textarea name="notes" id="approve_notes" class="form-control" rows="3" placeholder="Add any notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-success" id="approveSubmitBtn">
                        <i class="fas fa-check me-1"></i> Submit Review
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
                <h5 class="modal-title">
                    <i class="fas fa-times-circle text-danger me-2"></i> Reject Request
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div id="rejectFormErrors" class="alert alert-danger d-none"></div>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        This action cannot be undone. The user will be notified.
                    </div>
                    <div class="mb-3">
                        <label for="reject_reason" class="form-label fw-bold text-danger">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="reason" id="reject_reason" class="form-control" rows="3" required placeholder="Please provide a reason for rejection..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-danger" id="rejectSubmitBtn">
                        <i class="fas fa-times me-1"></i> Reject Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- FORWARD TO DIRECTOR MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="forwardModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-arrow-right text-primary me-2"></i> Forward to Director
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="forwardForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div id="forwardFormErrors" class="alert alert-danger d-none"></div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        This will forward the request to the Director for review.
                    </div>
                    <div class="mb-3">
                        <label for="forward_notes" class="form-label fw-bold">Notes <span class="text-muted">(optional)</span></label>
                        <textarea name="notes" id="forward_notes" class="form-control" rows="3" placeholder="Add any notes for the Director..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="forwardSubmitBtn">
                        <i class="fas fa-arrow-right me-1"></i> Forward
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- FORWARD TO PROCUREMENT MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="forwardProcurementModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-shopping-cart text-primary me-2"></i> Forward to Procurement
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="forwardProcurementForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div id="forwardProcurementFormErrors" class="alert alert-danger d-none"></div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        This will forward the request to Procurement for purchasing.
                    </div>
                    <div class="mb-3">
                        <label for="procurement_notes" class="form-label fw-bold">Notes <span class="text-muted">(optional)</span></label>
                        <textarea name="notes" id="procurement_notes" class="form-control" rows="3" placeholder="Add any notes for Procurement..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="forwardProcurementSubmitBtn">
                        <i class="fas fa-shopping-cart me-1"></i> Forward
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- ASSIGN ASSET MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="assignModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-handshake text-info me-2"></i> Assign Existing Asset
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="assignForm" method="POST">
                @csrf
                <div class="modal-body" id="assignModalBody">
                    <div id="assignFormErrors" class="alert alert-danger d-none"></div>
                    <div class="text-center py-5">
                        <div class="spinner-border text-info" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-info" id="assignSubmitBtn">
                        <i class="fas fa-handshake me-1"></i> Assign Asset
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- TIMELINE MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="timelineModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-history text-primary me-2"></i> Request Timeline
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="timelineModalBody">
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
                <p class="fw-bold">Are you sure you want to delete this request?</p>
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

<!-- ============================================ -->
<!-- CANCEL REQUEST MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-warning">
                    <i class="fas fa-ban me-2"></i> Cancel Request
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-ban fa-4x text-warning mb-3 d-block"></i>
                <p class="fw-bold">Are you sure you want to cancel this request?</p>
                <p class="text-muted small">This action cannot be undone.</p>
                <form id="cancelForm" method="POST">
                    @csrf
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Close
                </button>
                <button type="submit" class="btn btn-warning" id="cancelSubmitBtn" form="cancelForm">
                    <i class="fas fa-ban me-1"></i> Cancel Request
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
    // ROUTE MAP
    // ==========================================
    var routes = {
        show: "{{ route('admin.asset-requests.show', ['id' => '__ID__']) }}",
        edit: "{{ route('admin.asset-requests.edit', ['id' => '__ID__']) }}",
        update: "{{ route('admin.asset-requests.update', ['id' => '__ID__']) }}",
        destroy: "{{ route('admin.asset-requests.destroy', ['id' => '__ID__']) }}",
        review: "{{ route('admin.asset-requests.review', ['id' => '__ID__']) }}",
        reject: "{{ route('admin.asset-requests.reject', ['id' => '__ID__']) }}",
        forwardDirector: "{{ route('admin.asset-requests.forward-director', ['id' => '__ID__']) }}",
        forwardProcurement: "{{ route('admin.asset-requests.forward-procurement', ['id' => '__ID__']) }}",
        assignExisting: "{{ route('admin.asset-requests.assign-existing', ['id' => '__ID__']) }}",
        availableProducts: "{{ route('admin.asset-requests.available-products', ['id' => '__ID__']) }}",
        timeline: "{{ route('admin.asset-requests.timeline', ['id' => '__ID__']) }}",
        cancel: "{{ route('admin.asset-requests.cancel', ['id' => '__ID__']) }}",
    };

    function routeUrl(name, id) {
        return routes[name] ? routes[name].replace('__ID__', id) : '#';
    }

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
    @can('asset_request.bulk_delete')
    $('#selectAll').on('change', function() {
        $('.row-checkbox').prop('checked', $(this).prop('checked'));
    });

    $('#bulkDeleteBtn').on('click', function() {
        var selected = $('.row-checkbox:checked');
        if (selected.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Items Selected',
                text: 'Please select at least one request to delete.'
            });
            return;
        }

        Swal.fire({
            title: 'Delete Selected Requests?',
            text: 'You are about to delete ' + selected.length + ' request(s). This action cannot be undone.',
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
                    url: '{{ route("admin.asset-requests.bulk-action") }}',
                    type: 'POST',
                    data: {
                        request_ids: ids,
                        action: 'delete'
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
                            text: 'Failed to delete selected requests.'
                        });
                    }
                });
            }
        });
    });
    @endcan

    // ==========================================
    // VIEW REQUEST
    // ==========================================
    $(document).on('click', '.view-btn', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var url = routeUrl('show', id);
        if (url === '#') return;

        var modal = $('#viewModal');
        var body = $('#viewModalBody');
        body.html('<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');
        modal.modal('show');

        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(r) {
                var statusBadge = {
                    'pending_ict_review': 'warning',
                    'ict_reviewing': 'info',
                    'approved_by_ict': 'success',
                    'rejected_by_ict': 'danger',
                    'assigned_to_user': 'primary',
                    'completed': 'secondary'
                }[r.status] || 'secondary';

                var html = `
                    <div class="row g-3">
                        <div class="col-md-6"><label class="text-muted small fw-bold">Request ID</label><p class="fw-bold mb-0">#${r.id}</p></div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">Requested By</label><p class="mb-0">${r.user ? r.user.name : 'N/A'}</p></div>
                        <div class="col-12"><label class="text-muted small fw-bold">Title</label><p class="fw-bold mb-0">${r.title || 'N/A'}</p></div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">Request Type</label><p class="mb-0">${r.request_type ? r.request_type.charAt(0).toUpperCase() + r.request_type.slice(1) : 'N/A'}</p></div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">Category</label><p class="mb-0">${r.category ? r.category.name : 'N/A'}</p></div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">Quantity</label><p class="mb-0">${r.quantity_requested || 1}</p></div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">Priority</label><p class="mb-0"><span class="badge bg-${r.priority == 'urgent' ? 'danger' : (r.priority == 'high' ? 'warning text-dark' : (r.priority == 'medium' ? 'info' : 'secondary'))}">${r.priority ? r.priority.charAt(0).toUpperCase() + r.priority.slice(1) : 'N/A'}</span></p></div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">Status</label><p class="mb-0"><span class="badge bg-${statusBadge}">${r.status ? r.status.replace(/_/g, ' ').toUpperCase() : 'N/A'}</span></p></div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">Created</label><p class="mb-0">${new Date(r.created_at).toLocaleString()}</p></div>
                        <div class="col-12"><label class="text-muted small fw-bold">Description</label><p class="mb-0">${r.description || 'No description'}</p></div>
                        ${r.specifications ? `<div class="col-12"><label class="text-muted small fw-bold">Specifications</label><p class="mb-0">${r.specifications}</p></div>` : ''}
                        ${r.justification ? `<div class="col-12"><label class="text-muted small fw-bold">Justification</label><p class="mb-0">${r.justification}</p></div>` : ''}
                        ${r.ict_notes ? `<div class="col-12"><label class="text-muted small fw-bold text-warning">ICT Notes</label><p class="mb-0">${r.ict_notes}</p></div>` : ''}
                        ${r.director_notes ? `<div class="col-12"><label class="text-muted small fw-bold text-primary">Director Notes</label><p class="mb-0">${r.director_notes}</p></div>` : ''}
                        ${r.procurement_notes ? `<div class="col-12"><label class="text-muted small fw-bold text-info">Procurement Notes</label><p class="mb-0">${r.procurement_notes}</p></div>` : ''}
                        ${r.rejection_reason ? `<div class="col-12"><label class="text-muted small fw-bold text-danger">Rejection Reason</label><p class="mb-0">${r.rejection_reason}</p></div>` : ''}
                        ${r.assigned_product ? `<div class="col-12"><label class="text-muted small fw-bold text-success">Assigned Product</label><p class="mb-0"><i class="fas fa-box text-success me-1"></i> ${r.assigned_product.name}</p></div>` : ''}
                    </div>
                `;
                body.html(html);
            },
            error: function(xhr) {
                body.html(`<div class="text-center py-5 text-danger"><i class="fas fa-exclamation-circle fa-4x mb-3 d-block"></i><h5>Failed to load request details</h5><p>${xhr.status} - ${xhr.statusText}</p></div>`);
            }
        });
    });

    // ==========================================
    // EDIT REQUEST
    // ==========================================
    $(document).on('click', '.edit-btn', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var url = routeUrl('edit', id);
        if (url === '#') return;

        var modal = $('#editModal');
        var body = $('#editModalBody');
        var form = $('#editForm');

        form.attr('action', routeUrl('update', id));
        body.html('<div id="editFormErrors" class="alert alert-danger d-none"></div><div class="text-center py-5"><div class="spinner-border text-warning"></div></div>');
        modal.modal('show');

        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(r) {
                var html = `
                    <div class="row g-3">
                        <div class="col-12"><label for="edit_title" class="form-label fw-bold">Title <span class="text-danger">*</span></label><input type="text" name="title" id="edit_title" class="form-control" value="${r.title || ''}" required></div>
                        <div class="col-md-6"><label for="edit_type" class="form-label fw-bold">Request Type <span class="text-danger">*</span></label><select name="request_type" id="edit_type" class="form-select" required>
                            <option value="new" ${r.request_type == 'new' ? 'selected' : ''}>New Asset</option>
                            <option value="replacement" ${r.request_type == 'replacement' ? 'selected' : ''}>Replacement</option>
                            <option value="upgrade" ${r.request_type == 'upgrade' ? 'selected' : ''}>Upgrade</option>
                        </select></div>
                        <div class="col-md-6"><label for="edit_category" class="form-label fw-bold">Category</label><select name="category_id" id="edit_category" class="form-select">
                            <option value="">Select Category</option>
                            @foreach($categories ?? [] as $category)
                                <option value="{{ $category->id }}" ${r.category_id == {{ $category->id }} ? 'selected' : ''}>{{ $category->name }}</option>
                            @endforeach
                        </select></div>
                        <div class="col-md-6"><label for="edit_quantity" class="form-label fw-bold">Quantity <span class="text-danger">*</span></label><input type="number" name="quantity_requested" id="edit_quantity" class="form-control" value="${r.quantity_requested || 1}" min="1" required></div>
                        <div class="col-md-6"><label for="edit_priority" class="form-label fw-bold">Priority <span class="text-danger">*</span></label><select name="priority" id="edit_priority" class="form-select" required>
                            <option value="low" ${r.priority == 'low' ? 'selected' : ''}>Low</option>
                            <option value="medium" ${r.priority == 'medium' ? 'selected' : ''}>Medium</option>
                            <option value="high" ${r.priority == 'high' ? 'selected' : ''}>High</option>
                            <option value="urgent" ${r.priority == 'urgent' ? 'selected' : ''}>Urgent</option>
                        </select></div>
                        <div class="col-12"><label for="edit_description" class="form-label fw-bold">Description <span class="text-danger">*</span></label><textarea name="description" id="edit_description" class="form-control" rows="3" required>${r.description || ''}</textarea></div>
                        <div class="col-12"><label for="edit_specs" class="form-label fw-bold">Specifications</label><textarea name="specifications" id="edit_specs" class="form-control" rows="2">${r.specifications || ''}</textarea></div>
                        <div class="col-12"><label for="edit_justification" class="form-label fw-bold">Justification</label><textarea name="justification" id="edit_justification" class="form-control" rows="2">${r.justification || ''}</textarea></div>
                        <div class="col-12"><label for="edit_ict_notes" class="form-label fw-bold">ICT Notes</label><textarea name="ict_notes" id="edit_ict_notes" class="form-control" rows="2">${r.ict_notes || ''}</textarea></div>
                    </div>
                `;
                body.html(html);
            },
            error: function(xhr) {
                body.html(`<div class="text-center py-5 text-danger"><i class="fas fa-exclamation-circle fa-4x mb-3 d-block"></i><h5>Failed to load request data</h5><p>${xhr.status} - ${xhr.statusText}</p></div>`);
            }
        });
    });

    // ==========================================
    // EDIT FORM SUBMIT
    // ==========================================
    $('#editForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        var data = form.serialize();
        var errorContainer = $('#editFormErrors');

        clearErrors(errorContainer);

        Swal.fire({
            title: 'Updating Request...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

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
                    Swal.fire({ icon: 'error', title: 'Error!', text: r.message || 'Something went wrong.' });
                }
            },
            error: function(xhr) {
                Swal.close();
                if (xhr.status === 422) {
                    showErrors(errorContainer, xhr.responseJSON.errors);
                } else {
                    Swal.fire({ icon: 'error', title: 'Error!', text: 'Something went wrong. Please try again.' });
                }
            }
        });
    });

    // ==========================================
    // CREATE REQUEST
    // ==========================================
    $('#createForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        var data = form.serialize();
        var errorContainer = $('#createFormErrors');

        clearErrors(errorContainer);

        Swal.fire({
            title: 'Submitting Request...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

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
                    }).then(() => { 
                        if (r.redirect) {
                            window.location.href = r.redirect;
                        } else {
                            window.location.reload();
                        }
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error!', text: r.message || 'Something went wrong.' });
                }
            },
            error: function(xhr) {
                Swal.close();
                if (xhr.status === 422) {
                    showErrors(errorContainer, xhr.responseJSON.errors);
                } else {
                    Swal.fire({ icon: 'error', title: 'Error!', text: 'Something went wrong. Please try again.' });
                }
            }
        });
    });

    // ==========================================
    // REVIEW/APPROVE REQUEST
    // ==========================================
    $(document).on('click', '.approve-btn', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var url = routeUrl('review', id);
        if (url === '#') return;
        $('#approveForm').attr('action', url);
        clearErrors($('#approveFormErrors'));
        $('#approveModal').modal('show');
    });

    $('#approveForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        var data = form.serialize();
        var errorContainer = $('#approveFormErrors');

        clearErrors(errorContainer);

        Swal.fire({
            title: 'Processing Review...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

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
                        title: 'Success!',
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
                if (xhr.status === 422) {
                    showErrors(errorContainer, xhr.responseJSON.errors);
                } else {
                    Swal.fire({ icon: 'error', title: 'Error!', text: 'Failed to process review.' });
                }
            }
        });
    });

    // ==========================================
    // REJECT REQUEST
    // ==========================================
    $(document).on('click', '.reject-btn', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var url = routeUrl('reject', id);
        if (url === '#') return;
        $('#rejectForm').attr('action', url);
        clearErrors($('#rejectFormErrors'));
        $('#rejectModal').modal('show');
    });

    $('#rejectForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        var data = form.serialize();
        var errorContainer = $('#rejectFormErrors');

        clearErrors(errorContainer);

        Swal.fire({
            title: 'Rejecting Request...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

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
                }
            },
            error: function(xhr) {
                Swal.close();
                if (xhr.status === 422) {
                    showErrors(errorContainer, xhr.responseJSON.errors);
                } else {
                    Swal.fire({ icon: 'error', title: 'Error!', text: 'Failed to reject request.' });
                }
            }
        });
    });

    // ==========================================
    // FORWARD TO DIRECTOR
    // ==========================================
    $(document).on('click', '.forward-btn', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var url = routeUrl('forwardDirector', id);
        if (url === '#') return;
        $('#forwardForm').attr('action', url);
        clearErrors($('#forwardFormErrors'));
        $('#forwardModal').modal('show');
    });

    $('#forwardForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        var data = form.serialize();
        var errorContainer = $('#forwardFormErrors');

        clearErrors(errorContainer);

        Swal.fire({
            title: 'Forwarding Request...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(r) {
                if (r.success) {
                    $('#forwardModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Forwarded!',
                        text: r.message,
                        timer: 2000,
                        showConfirmButton: true
                    }).then(() => { window.location.reload(); });
                }
            },
            error: function(xhr) {
                Swal.close();
                if (xhr.status === 422) {
                    showErrors(errorContainer, xhr.responseJSON.errors);
                } else {
                    Swal.fire({ icon: 'error', title: 'Error!', text: 'Failed to forward request.' });
                }
            }
        });
    });

    // ==========================================
    // FORWARD TO PROCUREMENT
    // ==========================================
    $(document).on('click', '.forward-procurement-btn', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var url = routeUrl('forwardProcurement', id);
        if (url === '#') return;
        $('#forwardProcurementForm').attr('action', url);
        clearErrors($('#forwardProcurementFormErrors'));
        $('#forwardProcurementModal').modal('show');
    });

    $('#forwardProcurementForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        var data = form.serialize();
        var errorContainer = $('#forwardProcurementFormErrors');

        clearErrors(errorContainer);

        Swal.fire({
            title: 'Forwarding to Procurement...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(r) {
                if (r.success) {
                    $('#forwardProcurementModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Forwarded!',
                        text: r.message,
                        timer: 2000,
                        showConfirmButton: true
                    }).then(() => { window.location.reload(); });
                }
            },
            error: function(xhr) {
                Swal.close();
                if (xhr.status === 422) {
                    showErrors(errorContainer, xhr.responseJSON.errors);
                } else {
                    Swal.fire({ icon: 'error', title: 'Error!', text: 'Failed to forward to Procurement.' });
                }
            }
        });
    });

    // ==========================================
    // ASSIGN EXISTING ASSET
    // ==========================================
    $(document).on('click', '.assign-btn', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var url = routeUrl('assignExisting', id);
        if (url === '#') return;
        var modal = $('#assignModal');
        var body = $('#assignModalBody');
        var form = $('#assignForm');

        form.attr('action', url);
        body.html('<div id="assignFormErrors" class="alert alert-danger d-none"></div><div class="text-center py-5"><div class="spinner-border text-info"></div></div>');
        modal.modal('show');

        $.ajax({
            url: routeUrl('availableProducts', id),
            type: 'GET',
            dataType: 'json',
            success: function(r) {
                var html = `
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Assign to <strong>${r.user_name || 'the requester'}</strong>
                    </div>
                    <div class="mb-3">
                        <label for="assign_product" class="form-label fw-bold">Select Product <span class="text-danger">*</span></label>
                        <select name="product_id" id="assign_product" class="form-select" required>
                            <option value="">-- Select a product --</option>
                `;

                if (r.products && r.products.length > 0) {
                    r.products.forEach(function(p) {
                        html += `<option value="${p.id}">${p.name} (${p.sku}) - ${p.quantity} available</option>`;
                    });
                } else {
                    html += `<option value="">No available products found</option>`;
                }

                html += `
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="assign_notes" class="form-label fw-bold">Notes <span class="text-muted">(optional)</span></label>
                        <textarea name="notes" id="assign_notes" class="form-control" rows="3" placeholder="Add any notes..."></textarea>
                    </div>
                `;

                body.html(html);
            },
            error: function(xhr) {
                body.html(`<div class="text-center py-5 text-danger"><i class="fas fa-exclamation-circle fa-4x mb-3 d-block"></i><h5>Failed to load available products</h5><p>${xhr.status} - ${xhr.statusText}</p></div>`);
            }
        });
    });

    $('#assignForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        var data = form.serialize();
        var errorContainer = $('#assignFormErrors');

        clearErrors(errorContainer);

        Swal.fire({
            title: 'Assigning Asset...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(r) {
                if (r.success) {
                    $('#assignModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Assigned!',
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
                if (xhr.status === 422) {
                    showErrors(errorContainer, xhr.responseJSON.errors);
                } else {
                    Swal.fire({ icon: 'error', title: 'Error!', text: 'Failed to assign asset.' });
                }
            }
        });
    });

    // ==========================================
    // CANCEL REQUEST
    // ==========================================
    $(document).on('click', '.cancel-btn', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var url = routeUrl('cancel', id);
        if (url === '#') return;
        $('#cancelForm').attr('action', url);
        $('#cancelModal').modal('show');
    });

    $('#cancelForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');

        Swal.fire({
            title: 'Cancelling Request...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            dataType: 'json',
            success: function(r) {
                if (r.success) {
                    $('#cancelModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Cancelled!',
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
                    text: 'Failed to cancel request. ' + (xhr.responseJSON ? xhr.responseJSON.message : ''),
                });
            }
        });
    });

    // ==========================================
    // TIMELINE
    // ==========================================
    $(document).on('click', '.timeline-btn', function(e) {
        e.preventDefault();
        var id = $(this).closest('tr').data('id');
        var url = routeUrl('timeline', id);
        if (url === '#') return;

        var modal = $('#timelineModal');
        var body = $('#timelineModalBody');
        body.html('<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');
        modal.modal('show');

        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(r) {
                if (r && r.length > 0) {
                    var html = '<div class="timeline">';
                    r.forEach(function(wf, index) {
                        var statusClass = {
                            'pending_ict_review': 'warning',
                            'ict_reviewing': 'info',
                            'approved_by_ict': 'success',
                            'rejected_by_ict': 'danger',
                            'assigned_to_user': 'primary',
                            'completed': 'secondary'
                        }[wf.status_after] || 'secondary';

                        var icon = wf.status_after == 'pending_ict_review' ? 'clock' : 
                                   wf.status_after == 'approved_by_ict' ? 'check-circle' : 
                                   wf.status_after == 'rejected_by_ict' ? 'times-circle' : 'circle';

                        var actorName = wf.actor ? wf.actor.name : 'System';
                        var actionLabel = wf.action ? wf.action.replace(/_/g, ' ').toUpperCase() : '';

                        html += `
                            <div class="d-flex gap-3 mb-3 pb-2 ${index < r.length - 1 ? 'border-bottom' : ''}">
                                <div class="bg-${statusClass} bg-opacity-10 rounded-circle p-2" style="width:36px;height:36px;text-align:center;flex-shrink:0;">
                                    <i class="fas fa-${icon} text-${statusClass}"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <span class="badge bg-${statusClass}">${wf.status_after ? wf.status_after.replace(/_/g, ' ') : 'N/A'}</span>
                                        <small class="text-muted">${new Date(wf.created_at).toLocaleString()}</small>
                                    </div>
                                    ${actionLabel ? `<p class="mb-0 small fw-bold">${actionLabel}</p>` : ''}
                                    ${wf.comments ? `<p class="mb-0 small text-muted">${wf.comments}</p>` : ''}
                                    <small class="text-muted">By: ${actorName}</small>
                                </div>
                            </div>
                        `;
                    });
                    html += '</div>';
                    body.html(html);
                } else {
                    body.html(`<div class="text-center py-5"><i class="fas fa-inbox fa-4x text-muted mb-3 d-block"></i><p class="text-muted">No timeline entries available.</p></div>`);
                }
            },
            error: function(xhr) {
                body.html(`<div class="text-center py-5 text-danger"><i class="fas fa-exclamation-circle fa-4x mb-3 d-block"></i><h5>Failed to load timeline</h5><p>${xhr.status} - ${xhr.statusText}</p></div>`);
            }
        });
    });

    // ==========================================
    // DELETE
    // ==========================================
    $(document).on('click', '.delete-btn', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var url = routeUrl('destroy', id);
        if (url === '#') return;
        var form = $('#deleteForm');
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
                    text: 'Failed to delete request. ' + (xhr.responseJSON ? xhr.responseJSON.message : ''),
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