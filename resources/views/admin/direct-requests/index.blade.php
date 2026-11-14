@extends('layouts.admin')

@section('title', 'Direct Requests')
@section('page-title', 'Direct Request Management')

@section('admin-content')
<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ $stats['total'] ?? 0 }}</h3>
                <p>Total Requests</p>
            </div>
            <div class="icon">
                <i class="fas fa-file-alt"></i>
            </div>
            <a href="{{ route('admin.direct-requests.index') }}" class="small-box-footer">
                View All <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $stats['pending_director'] ?? 0 }}</h3>
                <p>Pending Approval</p>
            </div>
            <div class="icon">
                <i class="fas fa-clock"></i>
            </div>
            <a href="{{ route('admin.direct-requests.index', ['status' => 'pending_director']) }}" class="small-box-footer">
                View Pending <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $stats['director_approved'] ?? 0 }}</h3>
                <p>Approved</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <a href="{{ route('admin.direct-requests.index', ['status' => 'director_approved']) }}" class="small-box-footer">
                View Approved <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $stats['sent_to_ict'] ?? 0 }}</h3>
                <p>With ICT</p>
            </div>
            <div class="icon">
                <i class="fas fa-microchip"></i>
            </div>
            <a href="{{ route('admin.direct-requests.index', ['status' => 'sent_to_ict']) }}" class="small-box-footer">
                View ICT <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $stats['completed'] ?? 0 }}</h3>
                <p>Completed</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-double"></i>
            </div>
            <a href="{{ route('admin.direct-requests.index', ['status' => 'completed']) }}" class="small-box-footer">
                View Completed <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-secondary">
            <div class="inner">
                <h3>{{ $stats['merged'] ?? 0 }}</h3>
                <p>Merged</p>
            </div>
            <div class="icon">
                <i class="fas fa-code-branch"></i>
            </div>
            <a href="{{ route('admin.direct-requests.index', ['status' => 'merged_to_request']) }}" class="small-box-footer">
                View Merged <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    @if(isset($stats['without_approver']) && $stats['without_approver'] > 0)
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $stats['without_approver'] ?? 0 }}</h3>
                <p>Without Approver</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-slash"></i>
            </div>
            <a href="{{ route('admin.direct-requests.index', ['has_approver' => 'no']) }}" class="small-box-footer">
                View <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    @endif
</div>

<!-- Main Card -->
<div class="card">
    <div class="card-header">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <h5 class="card-title mb-0">
                <i class="fas fa-file-alt text-primary me-2"></i>
                All Direct Requests
                <span class="badge bg-primary ms-1">{{ $requests->total() }}</span>
            </h5>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <div class="input-group input-group-sm" style="width: 200px;">
                <input type="text" id="searchInput" class="form-control" placeholder="Search...">
                <button class="btn btn-secondary" type="button" id="searchBtn">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            <select id="statusFilter" class="form-select form-select-sm" style="width: 130px;">
                <option value="">All Status</option>
                <option value="pending_director">Pending Approval</option>
                <option value="director_approved">Approved</option>
                <option value="director_rejected">Rejected</option>
                <option value="sent_to_ict">With ICT</option>
                <option value="ict_specified">ICT Specified</option>
                <option value="director_sent_to_procurement">Sent to Procurement</option>
                <option value="merged_to_request">Merged</option>
                <option value="completed">Completed</option>
            </select>
            <select id="urgencyFilter" class="form-select form-select-sm" style="width: 110px;">
                <option value="">All Urgency</option>
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
                <option value="critical">Critical</option>
            </select>
            <select id="categoryFilter" class="form-select form-select-sm" style="width: 140px;">
                <option value="">All Categories</option>
                @foreach($categories ?? [] as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
            <select id="approverFilter" class="form-select form-select-sm" style="width: 130px;">
                <option value="">All Approvers</option>
                <option value="with">With Approver</option>
                <option value="without">Without Approver</option>
            </select>
            @can('direct_request.create')
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fas fa-plus-circle me-1"></i> New Direct Request
            </button>
            @endcan
            @can('direct_request.view')
            <a href="{{ route('admin.direct-requests.export') }}" class="btn btn-success btn-sm">
                <i class="fas fa-file-excel me-1"></i> Export
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
                        <th style="min-width: 150px;">Requestor</th>
                        <th style="min-width: 150px;">Subject</th>
                        <th style="width: 120px;">Category</th>
                        <th style="width: 80px;">Urgency</th>
                        <th style="width: 130px;">Status</th>
                        <th style="width: 100px;">Date</th>
                        <th style="width: 200px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($requests as $request)
                    <tr data-id="{{ $request->id }}" 
                        data-status="{{ $request->status }}" 
                        data-urgency="{{ $request->urgency }}" 
                        data-category="{{ $request->category_id }}"
                        data-has-approver="{{ $request->director_id ? 'with' : 'without' }}">
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
                            <div class="fw-bold">{{ Str::limit($request->subject, 30) }}</div>
                            <small class="text-muted">{{ Str::limit($request->description, 40) }}</small>
                        </td>
                        <td>
                            @if($request->category)
                                <span class="badge bg-info">{{ $request->category->name }}</span>
                            @else
                                <span class="badge bg-secondary">Uncategorized</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $urgencyColors = [
                                    'critical' => 'danger',
                                    'high' => 'warning text-dark',
                                    'medium' => 'info',
                                    'low' => 'secondary'
                                ];
                            @endphp
                            <span class="badge bg-{{ $urgencyColors[$request->urgency] ?? 'secondary' }}">
                                <i class="fas fa-{{ $request->urgency == 'critical' ? 'exclamation-circle' : ($request->urgency == 'high' ? 'arrow-up' : ($request->urgency == 'medium' ? 'minus' : 'arrow-down')) }} me-1"></i>
                                {{ ucfirst($request->urgency) }}
                            </span>
                        </td>
                        <td>
                            @php
                                $statusClasses = [
                                    'pending_director' => 'bg-warning text-dark',
                                    'director_approved' => 'bg-success text-white',
                                    'director_rejected' => 'bg-danger text-white',
                                    'sent_to_ict' => 'bg-primary text-white',
                                    'ict_specified' => 'bg-info text-white',
                                    'director_sent_to_procurement' => 'bg-primary text-white',
                                    'merged_to_request' => 'bg-secondary text-white',
                                    'completed' => 'bg-success text-white'
                                ];
                            @endphp
                            <span class="badge {{ $statusClasses[$request->status] ?? 'bg-secondary' }}">
                                {{ str_replace('_', ' ', $request->status) }}
                            </span>
                            @if(!$request->director_id && $request->status == 'pending_director')
                                <span class="badge bg-danger ms-1" title="No approver assigned">No Approver</span>
                            @endif
                            @if($request->asset_request_id)
                                <span class="badge bg-secondary ms-1" title="Merged to asset request">Merged</span>
                            @endif
                        </td>
                        <td>
                            <small>{{ $request->created_at->format('M d, Y') }}</small>
                            <br>
                            <small class="text-muted">{{ $request->created_at->diffForHumans() }}</small>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                @can('direct_request.view')
                                <button class="btn btn-outline-primary btn-sm view-btn" data-id="{{ $request->id }}" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @endcan

                                @if($request->isEditable())
                                    @can('direct_request.edit')
                                    <button class="btn btn-outline-warning btn-sm edit-btn" data-id="{{ $request->id }}" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @endcan
                                @endif

                                @if($request->isApprovable())
                                    @can('direct_request.approve')
                                    <button class="btn btn-outline-success btn-sm approve-btn" data-id="{{ $request->id }}" title="Approve">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    @endcan
                                    @can('direct_request.reject')
                                    <button class="btn btn-outline-danger btn-sm reject-btn" data-id="{{ $request->id }}" title="Reject">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    @endcan
                                @endif

                                @if($request->canForwardToICT())
                                    @can('direct_request.forward_to_ict')
                                    <button class="btn btn-outline-primary btn-sm forward-ict-btn" data-id="{{ $request->id }}" title="Forward to ICT">
                                        <i class="fas fa-microchip"></i>
                                    </button>
                                    @endcan
                                @endif

                                @if($request->canAddSpecifications())
                                    @can('direct_request.specify')
                                    <button class="btn btn-outline-info btn-sm specify-btn" data-id="{{ $request->id }}" title="Add Specifications">
                                        <i class="fas fa-clipboard-list"></i>
                                    </button>
                                    @endcan
                                @endif

                                @if($request->canForwardToProcurement())
                                    @can('direct_request.forward_to_procurement')
                                    <button class="btn btn-outline-primary btn-sm forward-procurement-btn" data-id="{{ $request->id }}" title="Forward to Procurement">
                                        <i class="fas fa-shopping-cart"></i>
                                    </button>
                                    @endcan
                                @endif

                                @if($request->canMerge())
                                    @can('direct_request.merge_to_request')
                                    <button class="btn btn-outline-success btn-sm merge-btn" data-id="{{ $request->id }}" title="Merge to Asset Request">
                                        <i class="fas fa-code-branch"></i>
                                    </button>
                                    @endcan
                                @endif

                                @if($request->isDeletable())
                                    @can('direct_request.delete')
                                    <button class="btn btn-outline-danger btn-sm delete-btn" data-id="{{ $request->id }}" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endcan
                                @endif

                                <button class="btn btn-outline-secondary btn-sm timeline-btn" data-id="{{ $request->id }}" title="Timeline">
                                    <i class="fas fa-history"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">
                            <div class="text-center py-5">
                                <i class="fas fa-file-alt fa-4x text-muted mb-3 d-block"></i>
                                <h5 class="fw-bold">No Direct Requests Found</h5>
                                <p class="text-muted">Click "New Direct Request" to create your first request.</p>
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
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle text-primary me-2"></i> New Direct Request
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createForm" action="{{ route('admin.direct-requests.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div id="createFormErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Request will be created under your account: <strong>{{ auth()->user()->name }}</strong>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Subject <span class="text-danger">*</span></label>
                            <input type="text" name="subject" class="form-control" required placeholder="Enter subject">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Description <span class="text-danger">*</span></label>
                            <textarea name="description" class="form-control" rows="3" required placeholder="Describe the request in detail"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Urgency <span class="text-danger">*</span></label>
                            <select name="urgency" class="form-select" required>
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Category <span class="text-muted">(optional)</span></label>
                            <select name="category_id" class="form-select">
                                <option value="">Select Category</option>
                                @foreach($categories ?? [] as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Additional Notes <span class="text-muted">(optional)</span></label>
                            <textarea name="director_notes" class="form-control" rows="2" placeholder="Any additional notes..."></textarea>
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
                    <i class="fas fa-eye text-info me-2"></i> Direct Request Details
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
                    <i class="fas fa-edit text-warning me-2"></i> Edit Direct Request
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
<!-- APPROVE MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-success">
                    <i class="fas fa-check-circle me-2"></i> Approve Direct Request
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="approveForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div id="approveFormErrors" class="alert alert-danger d-none"></div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        This will approve the direct request. The user will be notified.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Notes <span class="text-muted">(optional)</span></label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Add any notes..."></textarea>
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
                    <i class="fas fa-times-circle me-2"></i> Reject Direct Request
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
                        <label class="form-label fw-bold text-danger">Reason <span class="text-danger">*</span></label>
                        <textarea name="reason" id="rejectReason" class="form-control" rows="3" required placeholder="Please provide a reason for rejection..."></textarea>
                        <small class="text-muted">Minimum 5 characters required</small>
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
<!-- FORWARD TO ICT MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="forwardIctModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-primary">
                    <i class="fas fa-microchip me-2"></i> Forward to ICT
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="forwardIctForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div id="forwardIctFormErrors" class="alert alert-danger d-none"></div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        This will forward the request to ICT for specifications.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Notes <span class="text-muted">(optional)</span></label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Add any notes for ICT..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="forwardIctSubmitBtn">
                        <i class="fas fa-microchip me-1"></i> Forward to ICT
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- SPECIFY MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="specifyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-info">
                    <i class="fas fa-clipboard-list me-2"></i> Add Specifications
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="specifyForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div id="specifyFormErrors" class="alert alert-danger d-none"></div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Add technical specifications for this request.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Specifications <span class="text-danger">*</span></label>
                        <textarea name="specifications" class="form-control" rows="4" required placeholder="Enter technical specifications..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Notes <span class="text-muted">(optional)</span></label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Any additional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-info" id="specifySubmitBtn">
                        <i class="fas fa-save me-1"></i> Save Specifications
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
                <h5 class="modal-title text-primary">
                    <i class="fas fa-shopping-cart me-2"></i> Forward to Procurement
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
                        <label class="form-label fw-bold">Notes <span class="text-muted">(optional)</span></label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Add any notes for Procurement..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="forwardProcurementSubmitBtn">
                        <i class="fas fa-shopping-cart me-1"></i> Forward to Procurement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- MERGE MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="mergeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-success">
                    <i class="fas fa-code-branch me-2"></i> Merge to Asset Request
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-code-branch fa-4x text-success mb-3 d-block"></i>
                <p class="fw-bold">Are you sure you want to merge this direct request to an asset request?</p>
                <p class="text-muted small">This will create a new asset request and link it to this direct request.</p>
                <form id="mergeForm" method="POST">
                    @csrf
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <button type="submit" class="btn btn-success" id="mergeSubmitBtn" form="mergeForm">
                    <i class="fas fa-code-branch me-1"></i> Merge
                </button>
            </div>
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
                <p class="fw-bold">Are you sure you want to delete this direct request?</p>
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
            url: '/admin/direct-requests/' + id,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (!response.success) {
                    body.html(`
                        <div class="text-center py-5 text-danger">
                            <i class="fas fa-exclamation-circle fa-4x mb-3 d-block"></i>
                            <h5>Error</h5>
                            <p>${response.message || 'Failed to load request details.'}</p>
                        </div>
                    `);
                    return;
                }

                var request = response.request;
                var workflows = response.workflows || [];

                var urgencyColors = {
                    critical: 'danger',
                    high: 'warning text-dark',
                    medium: 'info',
                    low: 'secondary'
                };

                var statusClasses = {
                    pending_director: 'warning text-dark',
                    director_approved: 'success text-white',
                    director_rejected: 'danger text-white',
                    sent_to_ict: 'primary text-white',
                    ict_specified: 'info text-white',
                    director_sent_to_procurement: 'primary text-white',
                    merged_to_request: 'secondary text-white',
                    completed: 'success text-white'
                };

                var html = `
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="text-muted small fw-bold">Subject</label>
                            <p class="fw-bold mb-0">${request.subject}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Requestor</label>
                            <p class="mb-0">${request.user ? request.user.name : 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Approver</label>
                            <p class="mb-0">${request.director ? request.director.name : '<span class="badge bg-secondary">Not Assigned</span>'}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Category</label>
                            <p class="mb-0">${request.category ? request.category.name : '<span class="badge bg-secondary">Uncategorized</span>'}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Urgency</label>
                            <p class="mb-0"><span class="badge bg-${urgencyColors[request.urgency] || 'secondary'}">${request.urgency}</span></p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Status</label>
                            <p class="mb-0"><span class="badge ${statusClasses[request.status] || 'bg-secondary'}">${request.status.replace(/_/g, ' ')}</span></p>
                        </div>
                        <div class="col-12">
                            <label class="text-muted small fw-bold">Description</label>
                            <p class="mb-0">${request.description || 'No description'}</p>
                        </div>
                        ${request.specifications_added ? `
                        <div class="col-12">
                            <label class="text-muted small fw-bold">Specifications</label>
                            <p class="mb-0">${request.specifications_added}</p>
                        </div>` : ''}
                        ${request.director_notes ? `
                        <div class="col-12">
                            <label class="text-muted small fw-bold">Additional Notes</label>
                            <p class="mb-0">${request.director_notes}</p>
                        </div>` : ''}
                        ${request.asset_request_id ? `
                        <div class="col-12">
                            <label class="text-muted small fw-bold">Asset Request</label>
                            <p class="mb-0"><i class="fas fa-link me-1"></i> #${request.asset_request_id}</p>
                        </div>` : ''}
                        <div class="col-12">
                            <label class="text-muted small fw-bold">Created</label>
                            <p class="mb-0">${new Date(request.created_at).toLocaleString()}</p>
                        </div>
                        ${workflows.length > 0 ? `
                        <div class="col-12">
                            <hr>
                            <h6 class="fw-bold">Workflow History</h6>
                            ${workflows.map(function(w) {
                                return `<div class="d-flex gap-2 mb-2">
                                    <small class="text-muted">${new Date(w.created_at).toLocaleString()}</small>
                                    <span class="badge bg-secondary">${w.action.replace(/_/g, ' ')}</span>
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
                        <h5>Failed to load request details</h5>
                        <p>${xhr.status} - ${xhr.statusText}</p>
                        <p class="small">${xhr.responseJSON?.message || ''}</p>
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

        form.attr('action', '/admin/direct-requests/' + id);
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
            url: '/admin/direct-requests/' + id + '/edit',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (!response.success) {
                    body.html(`
                        <div id="editFormErrors" class="alert alert-danger"></div>
                        <div class="text-center py-5 text-danger">
                            <i class="fas fa-exclamation-circle fa-4x mb-3 d-block"></i>
                            <h5>Error</h5>
                            <p>${response.message || 'Failed to load request data.'}</p>
                        </div>
                    `);
                    return;
                }

                var r = response.directRequest;
                var categories = response.categories || [];

                var categoryOptions = '<option value="">Select Category</option>';
                $.each(categories, function(index, category) {
                    categoryOptions += `<option value="${category.id}" ${r.category_id == category.id ? 'selected' : ''}>${category.name}</option>`;
                });

                body.html(`
                    <div id="editFormErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Request created by: <strong>${r.user ? r.user.name : 'Unknown'}</strong>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Subject <span class="text-danger">*</span></label>
                            <input type="text" name="subject" class="form-control" value="${r.subject}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Description <span class="text-danger">*</span></label>
                            <textarea name="description" class="form-control" rows="3" required>${r.description}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Urgency <span class="text-danger">*</span></label>
                            <select name="urgency" class="form-select" required>
                                <option value="low" ${r.urgency == 'low' ? 'selected' : ''}>Low</option>
                                <option value="medium" ${r.urgency == 'medium' ? 'selected' : ''}>Medium</option>
                                <option value="high" ${r.urgency == 'high' ? 'selected' : ''}>High</option>
                                <option value="critical" ${r.urgency == 'critical' ? 'selected' : ''}>Critical</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Category <span class="text-muted">(optional)</span></label>
                            <select name="category_id" class="form-select">
                                ${categoryOptions}
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Additional Notes</label>
                            <textarea name="director_notes" class="form-control" rows="2">${r.director_notes || ''}</textarea>
                        </div>
                    </div>
                `);
            },
            error: function(xhr) {
                body.html(`
                    <div id="editFormErrors" class="alert alert-danger"></div>
                    <div class="text-center py-5 text-danger">
                        <i class="fas fa-exclamation-circle fa-4x mb-3 d-block"></i>
                        <h5>Failed to load request data</h5>
                        <p>${xhr.status} - ${xhr.statusText}</p>
                        <p class="small">${xhr.responseJSON?.message || ''}</p>
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
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Submitting...');

        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#createModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Submitted!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: true
                    }).then(() => { window.location.reload(); });
                } else {
                    btn.prop('disabled', false).html('<i class="fas fa-paper-plane me-1"></i> Submit Request');
                    if (response.errors) {
                        showErrors(errorContainer, response.errors);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message || 'Something went wrong.',
                        });
                    }
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
                        text: xhr.responseJSON?.message || 'Something went wrong. Please try again.',
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
            success: function(response) {
                if (response.success) {
                    $('#editModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: true
                    }).then(() => { window.location.reload(); });
                } else {
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Update Request');
                    if (response.errors) {
                        showErrors(errorContainer, response.errors);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message || 'Something went wrong.',
                        });
                    }
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Update Request');
                if (xhr.status === 422) {
                    showErrors(errorContainer, xhr.responseJSON.errors);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'Something went wrong. Please try again.',
                    });
                }
            }
        });
    });

    // ==========================================
    // APPROVE
    // ==========================================
    $(document).on('click', '.approve-btn', function() {
        var id = $(this).data('id');
        var form = $('#approveForm');
        form.attr('action', '/admin/direct-requests/' + id + '/approve');
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
            success: function(response) {
                if (response.success) {
                    $('#approveModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Approved!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: true
                    }).then(() => { window.location.reload(); });
                } else {
                    btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i> Approve');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message || 'Failed to approve.',
                    });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i> Approve');
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: xhr.responseJSON?.message || 'Failed to approve request.',
                });
            }
        });
    });
 
    // ==========================================
    // REJECT
    // ==========================================
    $(document).on('click', '.reject-btn', function() {
        var id = $(this).data('id');
        var form = $('#rejectForm');
        form.attr('action', '/admin/direct-requests/' + id + '/reject');
        clearErrors($('#rejectFormErrors'));
        // Clear the reason field when modal opens
        $('#rejectReason').val('');
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
            success: function(response) {
                if (response.success) {
                    $('#rejectModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Rejected!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: true
                    }).then(() => { window.location.reload(); });
                } else {
                    btn.prop('disabled', false).html('<i class="fas fa-times me-1"></i> Reject');
                    if (response.errors) {
                        showErrors(errorContainer, response.errors);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message || 'Failed to reject.',
                        });
                    }
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-times me-1"></i> Reject');
                if (xhr.status === 422) {
                    // Show validation errors
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        showErrors(errorContainer, xhr.responseJSON.errors);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            text: 'Please provide a reason for rejection (minimum 5 characters).',
                        });
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'Failed to reject request.',
                    });
                }
            }
        });
    });

    // ==========================================
    // FORWARD TO ICT
    // ==========================================
    $(document).on('click', '.forward-ict-btn', function() {
        var id = $(this).data('id');
        var form = $('#forwardIctForm');
        form.attr('action', '/admin/direct-requests/' + id + '/forward-ict');
        clearErrors($('#forwardIctFormErrors'));
        $('#forwardIctModal').modal('show');
    });

    $('#forwardIctForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        var data = form.serialize();
        var btn = $('#forwardIctSubmitBtn');
        var errorContainer = $('#forwardIctFormErrors');

        clearErrors(errorContainer);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Forwarding...');

        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#forwardIctModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Forwarded!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: true
                    }).then(() => { window.location.reload(); });
                } else {
                    btn.prop('disabled', false).html('<i class="fas fa-microchip me-1"></i> Forward to ICT');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message || 'Failed to forward.',
                    });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-microchip me-1"></i> Forward to ICT');
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: xhr.responseJSON?.message || 'Failed to forward request.',
                });
            }
        });
    });

    // ==========================================
    // SPECIFY
    // ==========================================
    $(document).on('click', '.specify-btn', function() {
        var id = $(this).data('id');
        var form = $('#specifyForm');
        form.attr('action', '/admin/direct-requests/' + id + '/specify');
        clearErrors($('#specifyFormErrors'));
        $('#specifyModal').modal('show');
    });

    $('#specifyForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        var data = form.serialize();
        var btn = $('#specifySubmitBtn');
        var errorContainer = $('#specifyFormErrors');

        clearErrors(errorContainer);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#specifyModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Saved!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: true
                    }).then(() => { window.location.reload(); });
                } else {
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save Specifications');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message || 'Failed to save.',
                    });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save Specifications');
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: xhr.responseJSON?.message || 'Failed to save specifications.',
                });
            }
        });
    });

    // ==========================================
    // FORWARD TO PROCUREMENT
    // ==========================================
    $(document).on('click', '.forward-procurement-btn', function() {
        var id = $(this).data('id');
        var form = $('#forwardProcurementForm');
        form.attr('action', '/admin/direct-requests/' + id + '/forward-procurement');
        clearErrors($('#forwardProcurementFormErrors'));
        $('#forwardProcurementModal').modal('show');
    });

    $('#forwardProcurementForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        var data = form.serialize();
        var btn = $('#forwardProcurementSubmitBtn');
        var errorContainer = $('#forwardProcurementFormErrors');

        clearErrors(errorContainer);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Forwarding...');

        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#forwardProcurementModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Forwarded!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: true
                    }).then(() => { window.location.reload(); });
                } else {
                    btn.prop('disabled', false).html('<i class="fas fa-shopping-cart me-1"></i> Forward to Procurement');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message || 'Failed to forward.',
                    });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-shopping-cart me-1"></i> Forward to Procurement');
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: xhr.responseJSON?.message || 'Failed to forward request.',
                });
            }
        });
    });

    // ==========================================
    // MERGE
    // ==========================================
    $(document).on('click', '.merge-btn', function() {
        var id = $(this).data('id');
        var form = $('#mergeForm');
        form.attr('action', '/admin/direct-requests/' + id + '/merge');
        $('#mergeModal').modal('show');
    });

    $('#mergeForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        var btn = $('#mergeSubmitBtn');

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Merging...');

        $.ajax({
            url: url,
            type: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#mergeModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Merged!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: true
                    }).then(() => { 
                        if (response.asset_request_id) {
                            window.location.href = '/admin/asset-requests/' + response.asset_request_id;
                        } else {
                            window.location.reload();
                        }
                    });
                } else {
                    btn.prop('disabled', false).html('<i class="fas fa-code-branch me-1"></i> Merge');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message || 'Failed to merge.',
                    });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-code-branch me-1"></i> Merge');
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: xhr.responseJSON?.message || 'Failed to merge request.',
                });
            }
        });
    });

    // ==========================================
    // TIMELINE
    // ==========================================
    $(document).on('click', '.timeline-btn', function() {
        var id = $(this).data('id');
        var body = $('#timelineModalBody');

        body.html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `);
        $('#timelineModal').modal('show');

        $.ajax({
            url: '/admin/direct-requests/' + id + '/timeline',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (!response.success) {
                    body.html(`
                        <div class="text-center py-5 text-danger">
                            <i class="fas fa-exclamation-circle fa-4x mb-3 d-block"></i>
                            <h5>Error</h5>
                            <p>${response.message || 'Failed to load timeline.'}</p>
                        </div>
                    `);
                    return;
                }

                var timeline = response.timeline || [];
                
                if (timeline.length > 0) {
                    var html = '';
                    $.each(timeline, function(index, item) {
                        html += `
                            <div class="d-flex gap-3 mb-3 pb-2 ${index < timeline.length - 1 ? 'border-bottom' : ''}">
                                <div class="bg-primary bg-opacity-10 rounded-circle p-2" style="width:36px;height:36px;text-align:center;flex-shrink:0;">
                                    <i class="fas fa-${index === 0 ? 'plus-circle' : 'circle'} text-primary"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <span class="fw-bold small">${item.event}</span>
                                        <small class="text-muted">${new Date(item.date).toLocaleString()}</small>
                                    </div>
                                    <p class="mb-0 small">${item.description}</p>
                                    <small class="text-muted">By: ${item.actor}</small>
                                    ${item.status ? `<br><small class="text-muted">Status: ${item.status.replace(/_/g, ' ')}</small>` : ''}
                                </div>
                            </div>
                        `;
                    });
                    body.html(html);
                } else {
                    body.html(`
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-4x text-muted mb-3 d-block"></i>
                            <p class="text-muted">No timeline entries available.</p>
                        </div>
                    `);
                }
            },
            error: function(xhr) {
                body.html(`
                    <div class="text-center py-5 text-danger">
                        <i class="fas fa-exclamation-circle fa-4x mb-3 d-block"></i>
                        <h5>Failed to load timeline</h5>
                        <p>${xhr.status} - ${xhr.statusText}</p>
                        <p class="small">${xhr.responseJSON?.message || ''}</p>
                    </div>
                `);
            }
        });
    });

    // ==========================================
    // DELETE
    // ==========================================
    $(document).on('click', '.delete-btn', function() {
        var id = $(this).data('id');
        var form = $('#deleteForm');
        form.attr('action', '/admin/direct-requests/' + id);

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
            success: function(response) {
                if (response.success) {
                    $('#deleteModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: true
                    }).then(() => { window.location.reload(); });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message || 'Failed to delete.',
                    });
                }
            },
            error: function(xhr) {
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: xhr.responseJSON?.message || 'Failed to delete request.',
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
    $('#urgencyFilter').on('change', function() { filterTable(); });
    $('#categoryFilter').on('change', function() { filterTable(); });
    $('#approverFilter').on('change', function() { filterTable(); });

    function filterTable() {
        var search = $('#searchInput').val().toLowerCase();
        var status = $('#statusFilter').val();
        var urgency = $('#urgencyFilter').val();
        var category = $('#categoryFilter').val();
        var approver = $('#approverFilter').val();

        $('#tableBody tr').each(function() {
            var row = $(this);
            var text = row.text().toLowerCase();
            var rowStatus = row.data('status');
            var rowUrgency = row.data('urgency');
            var rowCategory = row.data('category');
            var rowHasApprover = row.data('has-approver');

            var show = true;
            if (search && !text.includes(search)) show = false;
            if (status && rowStatus !== status) show = false;
            if (urgency && rowUrgency !== urgency) show = false;
            if (category && rowCategory != category) show = false;
            if (approver === 'with' && rowHasApprover !== 'with') show = false;
            if (approver === 'without' && rowHasApprover !== 'without') show = false;

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

    // ==========================================
    // RESET FORM ON MODAL CLOSE
    // ==========================================
    $('.modal').on('hidden.bs.modal', function() {
        $(this).find('form').trigger('reset');
        $(this).find('.alert-danger').addClass('d-none').empty();
    });
});
</script>
@endpush
@endsection