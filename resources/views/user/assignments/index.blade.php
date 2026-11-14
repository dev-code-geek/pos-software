@extends('layouts.user')

@section('title', 'My Assignments')
@section('page-title', 'My Assignments')

@section('user-content')
<div class="row">
    <div class="col-12">
        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-xl-4 col-md-6">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="stat-label">Active Assignments</p>
                            <p class="stat-number text-primary">{{ $assignments->whereNull('actual_return_date')->count() }}</p>
                            <small class="text-success"><i class="fas fa-check-circle"></i> Currently assigned</small>
                        </div>
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                            <i class="fas fa-laptop"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="stat-label">Total Assignments</p>
                            <p class="stat-number text-info">{{ $assignments->total() }}</p>
                            <small class="text-muted">All time</small>
                        </div>
                        <div class="stat-icon bg-info bg-opacity-10 text-info">
                            <i class="fas fa-history"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="stat-label">Returned</p>
                            <p class="stat-number text-success">{{ $assignments->whereNotNull('actual_return_date')->count() }}</p>
                            <small class="text-success"><i class="fas fa-undo-alt"></i> Returned items</small>
                        </div>
                        <div class="stat-icon bg-success bg-opacity-10 text-success">
                            <i class="fas fa-undo-alt"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assignments Table -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">
                    <i class="fas fa-laptop text-primary me-2"></i> My Assignments
                    <span class="badge bg-primary ms-1">{{ $assignments->total() }}</span>
                </span>
                <div>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createRequestModal">
                        <i class="fas fa-plus-circle me-1"></i> Request Asset
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 35px;">#</th>
                                <th style="min-width: 150px;">Asset</th>
                                <th>Assigned Date</th>
                                <th>Expected Return</th>
                                <th style="width: 100px;">Status</th>
                                <th style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assignments as $assignment)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle bg-primary text-white me-2">
                                            <i class="fas fa-box"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold small">{{ Str::limit($assignment->product->name ?? 'N/A', 25) }}</div>
                                            <small class="text-muted">{{ $assignment->product->sku ?? 'N/A' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <small>{{ $assignment->assigned_date ? $assignment->assigned_date->format('M d, Y') : 'N/A' }}</small>
                                    <br>
                                    <small class="text-muted">{{ $assignment->assigned_date ? $assignment->assigned_date->diffForHumans() : '' }}</small>
                                </td>
                                <td>
                                    @if($assignment->expected_return_date)
                                        <small>{{ $assignment->expected_return_date->format('M d, Y') }}</small>
                                        @if($assignment->expected_return_date < now() && !$assignment->actual_return_date)
                                            <span class="badge bg-danger ms-1">Overdue</span>
                                        @endif
                                    @else
                                        <small class="text-muted">N/A</small>
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
                                        <button class="btn btn-outline-primary view-btn" data-id="{{ $assignment->id }}" title="View">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        @if(!$assignment->actual_return_date)
                                        <button class="btn btn-outline-success return-btn" data-id="{{ $assignment->id }}" title="Request Return">
                                            <i class="fas fa-undo-alt"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6">
                                    <div class="text-center py-5">
                                        <i class="fas fa-laptop fa-4x text-muted mb-3 d-block"></i>
                                        <h5>No Assignments Found</h5>
                                        <p class="text-muted">You don't have any assets assigned to you.</p>
                                        <button class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#createRequestModal">
                                            <i class="fas fa-plus-circle me-1"></i> Request Asset
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{ $assignments->links() }}
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- VIEW ASSIGNMENT MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-scrollable">
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
<!-- CREATE REQUEST MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="createRequestModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 560px;">
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
    // VIEW ASSIGNMENT
    // ==========================================
    $('.view-btn').on('click', function() {
        var id = $(this).data('id');
        var modal = $('#viewModal');
        var body = $('#viewModalBody');

        body.html('<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');
        modal.modal('show');

        $.ajax({
            url: '/api/user/assignments/' + id,
            type: 'GET',
            success: function(r) {
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
                            <label class="text-muted small fw-bold">Assigned Date</label>
                            <p class="mb-0">${r.assigned_date ? new Date(r.assigned_date).toLocaleDateString() : 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Expected Return</label>
                            <p class="mb-0">${r.expected_return_date ? new Date(r.expected_return_date).toLocaleDateString() : 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Status</label>
                            <p class="mb-0">
                                <span class="badge ${r.actual_return_date ? 'bg-secondary' : (r.expected_return_date && new Date(r.expected_return_date) < new Date() ? 'bg-danger' : 'bg-success')}">
                                    ${r.actual_return_date ? 'Returned' : (r.expected_return_date && new Date(r.expected_return_date) < new Date() ? 'Overdue' : 'Active')}
                                </span>
                            </p>
                        </div>
                        ${r.condition_out ? `
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Condition (Out)</label>
                            <p class="mb-0">${r.condition_out}</p>
                        </div>` : ''}
                        ${r.condition_in ? `
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Condition (In)</label>
                            <p class="mb-0">${r.condition_in}</p>
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
    // REQUEST RETURN
    // ==========================================
    $('.return-btn').on('click', function() {
        var id = $(this).data('id');
        
        Swal.fire({
            title: 'Request Return',
            text: "Are you sure you want to request return for this asset?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#22c55e',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, request return',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/api/user/assignments/' + id + '/return',
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(r) {
                        if (r.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Return Requested!',
                                text: r.message,
                                timer: 2000,
                                showConfirmButton: true
                            }).then(() => {
                                window.location.reload();
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Failed to request return. Please try again.',
                        });
                    }
                });
            }
        });
    });

    // ==========================================
    // CREATE REQUEST
    // ==========================================
    $('#createRequestForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        var data = form.serialize();

        Swal.fire({
            title: 'Submitting Request...',
            text: 'Please wait while we submit your request.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: url,
            type: 'POST',
            data: data,
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
                }
            },
            error: function(x) {
                Swal.close();
                if (x.status === 422) {
                    var msg = '';
                    $.each(x.responseJSON.errors, function(k, v) {
                        msg += v[0] + '\n';
                    });
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: msg,
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Something went wrong. Please try again.',
                    });
                }
            }
        });
    });

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