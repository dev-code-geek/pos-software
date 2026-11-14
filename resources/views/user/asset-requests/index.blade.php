@extends('layouts.user')

@section('title', 'My Requests')
@section('page-title', 'My Requests')

@section('user-content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <span class="card-title">
                    <i class="fas fa-clipboard-list text-primary me-2"></i> My Asset Requests
                    <span class="badge bg-primary ms-1">{{ $requests->total() }}</span>
                </span>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createRequestModal">
                    <i class="fas fa-plus-circle me-1"></i> New Request
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 35px;">#</th>
                                <th style="min-width: 150px;">Title</th>
                                <th>Category</th>
                                <th style="width: 40px; text-align: center;">Qty</th>
                                <th style="width: 80px;">Priority</th>
                                <th style="width: 110px;">Status</th>
                                <th style="width: 100px;">Date</th>
                                <th style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            @forelse($requests as $request)
                            <tr data-id="{{ $request->id }}" data-status="{{ $request->status }}">
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div class="fw-bold">{{ $request->title }}</div>
                                    <small class="text-muted">
                                        <i class="fas fa-tag me-1"></i>
                                        {{ ucfirst($request->request_type ?? 'N/A') }}
                                    </small>
                                </td>
                                <td>{{ $request->category->name ?? 'N/A' }}</td>
                                <td class="text-center fw-bold">{{ $request->quantity_requested }}</td>
                                <td>
                                    <span class="badge bg-{{ 
                                        $request->priority == 'urgent' ? 'danger' : 
                                        ($request->priority == 'high' ? 'warning text-dark' : 
                                        ($request->priority == 'medium' ? 'info' : 'secondary')) 
                                    }}">
                                        {{ ucfirst($request->priority) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-status {{ 
                                        $request->status == 'pending_ict_review' ? 'pending' : 
                                        ($request->status == 'ict_reviewing' ? 'reviewing' : 
                                        ($request->status == 'approved_by_ict' ? 'approved' : 
                                        ($request->status == 'rejected_by_ict' ? 'rejected' : 
                                        ($request->status == 'assigned_to_user' ? 'assigned' : 'completed')))) 
                                    }}">
                                        {{ str_replace('_', ' ', $request->status) }}
                                    </span>
                                </td>
                                <td>
                                    <small>{{ $request->created_at->format('M d, Y') }}</small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button class="btn btn-outline-primary view-btn" data-id="{{ $request->id }}" title="View">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        @if(in_array($request->status, ['pending_ict_review', 'ict_reviewing']))
                                        <button class="btn btn-outline-warning edit-btn" data-id="{{ $request->id }}" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-danger cancel-btn" data-id="{{ $request->id }}" title="Cancel">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8">
                                    <div class="text-center py-5">
                                        <i class="fas fa-clipboard-list fa-4x text-muted mb-3 d-block"></i>
                                        <h5>No Requests Found</h5>
                                        <p class="text-muted">Click "New Request" to create your first request.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{ $requests->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Create Modal -->
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
    // Create Request
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

    // View
    $('.view-btn').on('click', function() {
        var id = $(this).data('id');
        showToast('info', 'View functionality coming soon.');
    });

    // Edit
    $('.edit-btn').on('click', function() {
        var id = $(this).data('id');
        showToast('info', 'Edit functionality coming soon.');
    });

    // Cancel
    $('.cancel-btn').on('click', function() {
        var id = $(this).data('id');
        if (confirm('Are you sure you want to cancel this request?')) {
            $.ajax({
                url: '/user/asset-requests/' + id + '/cancel',
                type: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(r) {
                    if (r.success) {
                        showToast('success', r.message);
                        setTimeout(function() { window.location.reload(); }, 1500);
                    }
                },
                error: function() {
                    showToast('error', 'Failed to cancel request.');
                }
            });
        }
    });

    function showToast(type, message) {
        var icons = { success: 'check-circle', error: 'exclamation-circle' };
        var colors = { success: 'success', error: 'danger' };
        var toast = $(`
            <div class="toast show" role="alert" style="min-width:300px;position:fixed;top:20px;right:20px;z-index:9999;">
                <div class="toast-header bg-${colors[type]} text-white">
                    <strong class="me-auto"><i class="fas fa-${icons[type]} me-2"></i> ${type.charAt(0).toUpperCase() + type.slice(1)}</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">${message}</div>
            </div>
        `);
        $('body').append(toast);
        setTimeout(function() { toast.remove(); }, 5000);
    }

    // Fix modal backdrop
    $('.modal').on('hidden.bs.modal', function() {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
    });
});
</script>
@endpush
@endsection