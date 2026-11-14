@extends('layouts.user')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('user-content')
<style>
/* ============================================
   DASHBOARD CUSTOM STYLES
============================================ */
.dashboard-stat-card {
    border-radius: 16px;
    padding: 20px;
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
    border: none;
    height: 100%;
}

.dashboard-stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 40px rgba(0,0,0,0.12);
}

.dashboard-stat-card .stat-icon {
    position: absolute;
    right: 15px;
    top: 15px;
    font-size: 2.5rem;
    opacity: 0.15;
    color: #fff;
}

.dashboard-stat-card .stat-number {
    font-size: 2.2rem;
    font-weight: 700;
    margin: 0;
    color: #fff;
}

.dashboard-stat-card .stat-label {
    font-size: 0.85rem;
    opacity: 0.85;
    color: #fff;
    margin: 0;
}

.dashboard-stat-card .stat-link {
    display: block;
    padding-top: 10px;
    color: rgba(255,255,255,0.8);
    text-decoration: none;
    font-size: 0.8rem;
    transition: all 0.2s ease;
}

.dashboard-stat-card .stat-link:hover {
    color: #fff;
}

.dashboard-stat-card .stat-link i {
    transition: transform 0.2s ease;
}

.dashboard-stat-card:hover .stat-link i {
    transform: translateX(4px);
}

/* Welcome Card */
.welcome-card {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    border: none;
    border-radius: 16px;
    color: #fff;
    position: relative;
    overflow: hidden;
}

.welcome-card::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 300px;
    height: 300px;
    background: rgba(255,255,255,0.05);
    border-radius: 50%;
}

.welcome-card .welcome-icon {
    font-size: 3rem;
    opacity: 0.2;
    position: absolute;
    right: 20px;
    top: 20px;
}

/* Quick Action Cards */
.quick-action-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 15px;
}

.quick-action-item {
    text-decoration: none;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 20px 15px;
    text-align: center;
    transition: all 0.3s ease;
    cursor: pointer;
}

.quick-action-item:hover {
    transform: translateY(-4px);
    border-color: #4f46e5;
    box-shadow: 0 8px 25px rgba(79,70,229,0.10);
}

.quick-action-item .qa-icon {
    font-size: 2rem;
    margin-bottom: 10px;
    display: block;
}

.quick-action-item .qa-title {
    font-size: 0.8rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
}

.quick-action-item .qa-desc {
    font-size: 0.65rem;
    color: #94a3b8;
    margin: 4px 0 0;
}

/* Status Badges */
.badge-status {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 600;
}
.badge-status.pending { background: #fef3c7; color: #92400e; }
.badge-status.reviewing { background: #dbeafe; color: #1e40af; }
.badge-status.approved { background: #dcfce7; color: #166534; }
.badge-status.rejected { background: #fee2e2; color: #991b1b; }
.badge-status.assigned { background: #e0e7ff; color: #3730a3; }
.badge-status.completed { background: #e2e8f0; color: #475569; }

/* Animations */
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.fade-in-up {
    animation: fadeInUp 0.6s ease forwards;
}
.fade-in-up-delay-1 { animation-delay: 0.1s; }
.fade-in-up-delay-2 { animation-delay: 0.2s; }
.fade-in-up-delay-3 { animation-delay: 0.3s; }
</style>

<!-- ============================================ -->
<!-- WELCOME SECTION -->
<!-- ============================================ -->
<div class="row mb-4">
    <div class="col-12">
        <div class="welcome-card card fade-in-up">
            <div class="card-body py-4">
                <div class="welcome-icon"><i class="fas fa-user-circle"></i></div>
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <h4 class="mb-1 fw-bold">
                            <i class="fas fa-hand-peace me-2"></i>
                            Welcome back, <span class="text-warning">{{ auth()->user()->name }}</span>!
                        </h4>
                        <p class="mb-0 opacity-75">
                            <i class="fas fa-calendar-alt me-1"></i> {{ now()->format('l, F d, Y') }}
                            <span class="mx-2">|</span>
                            <i class="fas fa-clock me-1"></i> {{ now()->format('h:i A') }}
                        </p>
                        <div class="mt-2">
                            <span class="badge bg-light text-dark me-1">
                                <i class="fas fa-user-tag me-1"></i> {{ auth()->user()->roles->first()->name ?? 'User' }}
                            </span>
                            <span class="badge bg-success">
                                <i class="fas fa-check-circle me-1"></i> Online
                            </span>
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="d-flex gap-2">
                            <a href="{{ route('profile.edit') }}" class="btn btn-light btn-sm">
                                <i class="fas fa-user-edit me-1"></i> Profile
                            </a>
                            <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#createRequestModal">
                                <i class="fas fa-plus-circle me-1"></i> Request
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- STATS CARDS -->
<!-- ============================================ -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="dashboard-stat-card bg-primary fade-in-up">
            <div class="stat-icon"><i class="fas fa-laptop"></i></div>
            <div class="stat-number">{{ $stats['my_assignments'] ?? 0 }}</div>
            <div class="stat-label">My Assignments</div>
            <a href="{{ route('user.assignments.index') }}" class="stat-link">
                View Assignments <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="dashboard-stat-card bg-success fade-in-up fade-in-up-delay-1">
            <div class="stat-icon"><i class="fas fa-clipboard-list"></i></div>
            <div class="stat-number">{{ $stats['my_requests'] ?? 0 }}</div>
            <div class="stat-label">Total Requests</div>
            <a href="{{ route('user.asset-requests.index') }}" class="stat-link">
                View Requests <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="dashboard-stat-card bg-warning fade-in-up fade-in-up-delay-2">
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
            <div class="stat-number">{{ $stats['pending_requests'] ?? 0 }}</div>
            <div class="stat-label">Pending Requests</div>
            <a href="{{ route('user.asset-requests.index', ['status' => 'pending']) }}" class="stat-link">
                View Pending <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="dashboard-stat-card bg-danger fade-in-up fade-in-up-delay-3">
            <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="stat-number">{{ $stats['overdue_assignments'] ?? 0 }}</div>
            <div class="stat-label">Overdue Returns</div>
            <a href="{{ route('user.assignments.index', ['status' => 'overdue']) }}" class="stat-link">
                View Overdue <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- MY ASSIGNMENTS & RECENT REQUESTS -->
<!-- ============================================ -->
<div class="row g-3 mb-4">
    <div class="col-xl-6">
        <div class="card fade-in-up">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-laptop text-primary me-2"></i>My Assignments</h6>
                <a href="{{ route('user.assignments.index') }}" class="btn btn-sm btn-outline-primary">
                    View All <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @if(isset($myAssignments) && $myAssignments->count() > 0)
                        @foreach($myAssignments->take(5) as $assignment)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold">{{ $assignment->product->name ?? 'N/A' }}</div>
                                <small class="text-muted">
                                    <i class="fas fa-calendar-alt me-1"></i> 
                                    Assigned: {{ $assignment->assigned_date ? $assignment->assigned_date->format('M d, Y') : 'N/A' }}
                                    @if($assignment->expected_return_date)
                                    <span class="mx-1">|</span>
                                    <i class="fas fa-clock me-1"></i>
                                    Due: {{ $assignment->expected_return_date->format('M d, Y') }}
                                    @endif
                                </small>
                            </div>
                            <div>
                                @if($assignment->expected_return_date && $assignment->expected_return_date < now())
                                    <span class="badge bg-danger me-1">Overdue</span>
                                @endif
                                <span class="badge bg-success">Active</span>
                            </div>
                        </div>
                        @endforeach
                        @if($myAssignments->count() > 5)
                        <div class="list-group-item text-center">
                            <small class="text-muted">+{{ $myAssignments->count() - 5 }} more assignments</small>
                        </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-laptop fa-3x text-muted mb-3 d-block"></i>
                            <p class="text-muted mb-0">No active assignments</p>
                            <button class="btn btn-sm btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#createRequestModal">
                                <i class="fas fa-plus-circle me-1"></i> Request Asset
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card fade-in-up fade-in-up-delay-1">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-clock text-warning me-2"></i>Recent Requests</h6>
                <a href="{{ route('user.asset-requests.index') }}" class="btn btn-sm btn-outline-primary">
                    View All <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @if(isset($recentRequests) && $recentRequests->count() > 0)
                        @foreach($recentRequests as $request)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold">{{ $request->title }}</div>
                                <small class="text-muted">
                                    <i class="fas fa-calendar-alt me-1"></i> 
                                    {{ $request->created_at->diffForHumans() }}
                                    @if($request->priority)
                                    <span class="mx-1">|</span>
                                    <span class="badge bg-{{ $request->priority == 'urgent' ? 'danger' : ($request->priority == 'high' ? 'warning text-dark' : ($request->priority == 'medium' ? 'info' : 'secondary')) }}">
                                        {{ ucfirst($request->priority) }}
                                    </span>
                                    @endif
                                </small>
                            </div>
                            <span class="badge-status {{ 
                                $request->status == 'pending_ict_review' ? 'pending' : 
                                ($request->status == 'ict_reviewing' ? 'reviewing' : 
                                ($request->status == 'approved_by_ict' ? 'approved' : 
                                ($request->status == 'rejected_by_ict' ? 'rejected' : 
                                ($request->status == 'assigned_to_user' ? 'assigned' : 'completed')))) 
                            }}">
                                {{ str_replace('_', ' ', $request->status) }}
                            </span>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-clipboard-list fa-3x text-muted mb-3 d-block"></i>
                            <p class="text-muted mb-0">No requests yet</p>
                            <button class="btn btn-sm btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#createRequestModal">
                                <i class="fas fa-plus-circle me-1"></i> Create Request
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- QUICK ACTIONS - FIXED ROUTES -->
<!-- ============================================ -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card fade-in-up fade-in-up-delay-2">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-bolt text-primary me-2"></i>Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="quick-action-grid">
                    <div class="quick-action-item" data-bs-toggle="modal" data-bs-target="#createRequestModal">
                        <span class="qa-icon text-primary"><i class="fas fa-plus-circle"></i></span>
                        <h6 class="qa-title">New Request</h6>
                        <p class="qa-desc">Request an asset</p>
                    </div>
                    <a href="{{ route('user.assignments.index') }}" class="quick-action-item">
                        <span class="qa-icon text-success"><i class="fas fa-laptop"></i></span>
                        <h6 class="qa-title">My Assignments</h6>
                        <p class="qa-desc">View your assets</p>
                    </a>
                    <a href="{{ route('user.asset-requests.index') }}" class="quick-action-item">
                        <span class="qa-icon text-info"><i class="fas fa-list"></i></span>
                        <h6 class="qa-title">My Requests</h6>
                        <p class="qa-desc">Track requests</p>
                    </a>
                    <!-- FIXED: Changed from user.products.index to user.products -->
                    <a href="{{ route('user.products') }}" class="quick-action-item">
                        <span class="qa-icon text-warning"><i class="fas fa-box"></i></span>
                        <h6 class="qa-title">Available Products</h6>
                        <p class="qa-desc">Browse inventory</p>
                    </a>
                    <a href="{{ route('user.assignments.history') }}" class="quick-action-item">
                        <span class="qa-icon text-secondary"><i class="fas fa-history"></i></span>
                        <h6 class="qa-title">History</h6>
                        <p class="qa-desc">View past assignments</p>
                    </a>
                    <a href="{{ route('profile.edit') }}" class="quick-action-item">
                        <span class="qa-icon text-purple"><i class="fas fa-user-cog" style="color:#7c3aed;"></i></span>
                        <h6 class="qa-title">Profile</h6>
                        <p class="qa-desc">Update your details</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- NOTIFICATIONS & UPCOMING RETURNS -->
<!-- ============================================ -->
<div class="row g-3">
    <div class="col-xl-6">
        <div class="card fade-in-up fade-in-up-delay-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-bell text-warning me-2"></i>Notifications</h6>
                @if($unreadCount > 0)
                <button class="btn btn-sm btn-link text-decoration-none" id="markAllReadBtn">
                    <i class="fas fa-check-double me-1"></i> Mark all read
                </button>
                @endif
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @if(isset($notifications) && $notifications->count() > 0)
                        @foreach($notifications as $notification)
                        <div class="list-group-item list-group-item-action {{ !$notification->is_read ? 'bg-primary bg-opacity-5' : '' }}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="fw-bold small">{{ $notification->message }}</div>
                                    <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                </div>
                                @if(!$notification->is_read)
                                <button class="btn btn-sm btn-outline-primary mark-read-btn" data-id="{{ $notification->id }}" title="Mark as read">
                                    <i class="fas fa-check"></i>
                                </button>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-bell-slash fa-3x text-muted mb-3 d-block"></i>
                            <p class="text-muted mb-0">No notifications</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card fade-in-up fade-in-up-delay-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-calendar-check text-info me-2"></i>Upcoming Returns</h6>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @if(isset($upcomingReturns) && $upcomingReturns->count() > 0)
                        @foreach($upcomingReturns->take(5) as $assignment)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold">{{ $assignment->product->name ?? 'N/A' }}</div>
                                <small class="text-muted">
                                    <i class="fas fa-calendar-alt me-1"></i> 
                                    Due: {{ $assignment->expected_return_date->format('M d, Y') }}
                                    <span class="mx-1">|</span>
                                    <span class="text-{{ $assignment->expected_return_date->diffInDays(now()) <= 3 ? 'danger' : 'warning' }}">
                                        {{ $assignment->expected_return_date->diffForHumans() }}
                                    </span>
                                </small>
                            </div>
                            <a href="{{ route('user.assignments.show', $assignment->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye me-1"></i> View
                            </a>
                        </div>
                        @endforeach
                        @if($upcomingReturns->count() > 5)
                        <div class="list-group-item text-center">
                            <small class="text-muted">+{{ $upcomingReturns->count() - 5 }} more</small>
                        </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-check fa-3x text-muted mb-3 d-block"></i>
                            <p class="text-muted mb-0">No upcoming returns</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- CREATE REQUEST MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="createRequestModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle text-primary me-2"></i> New Asset Request
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createRequestForm" action="{{ route('user.asset-requests.store') }}" method="POST">
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
    // CREATE REQUEST
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

    $('#createRequestForm').on('submit', function(e) {
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
                    $('#createRequestModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Submitted!',
                        text: r.message,
                        timer: 2000,
                        showConfirmButton: true
                    }).then(() => {
                        window.location.reload();
                    });
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
                        text: 'Something went wrong. Please try again.'
                    });
                }
            }
        });
    });

    // ==========================================
    // MARK NOTIFICATION AS READ
    // ==========================================
    $(document).on('click', '.mark-read-btn', function() {
        var id = $(this).data('id');
        var btn = $(this);
        var row = btn.closest('.list-group-item');

        // Show loading state
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

        $.ajax({
            url: '/user/notifications/' + id + '/read',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            dataType: 'json',
            success: function(r) {
                if (r.success) {
                    // Remove the unread styling
                    row.removeClass('bg-primary bg-opacity-5');
                    // Remove the button
                    btn.remove();
                    
                    // Update badge count
                    var badge = $('#notification-count');
                    if (badge.length) {
                        var count = parseInt(badge.text()) || 0;
                        var newCount = count - 1;
                        badge.text(newCount);
                        if (newCount <= 0) {
                            badge.hide();
                        }
                    }
                    
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Marked as Read!',
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: r.message || 'Failed to mark notification as read.'
                    });
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to mark notification as read. Please try again.'
                });
                btn.prop('disabled', false).html('<i class="fas fa-check"></i>');
            }
        });
    });

    // ==========================================
    // MARK ALL AS READ
    // ==========================================
    $('#markAllReadBtn').on('click', function() {
        var btn = $(this);

        Swal.fire({
            title: 'Mark All as Read?',
            text: 'This will mark all notifications as read.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0d6efd',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, mark all read!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading state
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

                $.ajax({
                    url: '{{ route("user.notifications.mark-all-read") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    dataType: 'json',
                    success: function(r) {
                        if (r.success) {
                            // Remove all unread styling
                            $('.list-group-item.bg-primary').removeClass('bg-primary bg-opacity-5');
                            // Remove all mark read buttons
                            $('.mark-read-btn').remove();
                            // Update badge count
                            var badge = $('#notification-count');
                            if (badge.length) {
                                badge.text(0).hide();
                            }
                            // Hide the mark all button
                            btn.hide();
                            
                            Swal.fire({
                                icon: 'success',
                                title: 'All Marked as Read!',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: r.message || 'Failed to mark all as read.'
                            });
                        }
                    },
                    error: function(xhr) {
                        console.error('Error:', xhr);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Failed to mark all as read. Please try again.'
                        });
                        btn.prop('disabled', false).html('<i class="fas fa-check-double me-1"></i> Mark all read');
                    }
                });
            }
        });
    });

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