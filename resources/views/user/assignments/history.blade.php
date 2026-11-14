@extends('layouts.user')

@section('title', 'Assignment History')
@section('page-title', 'Assignment History')

@section('user-content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <span class="card-title">
                    <i class="fas fa-history text-primary me-2"></i> Assignment History
                    <span class="badge bg-primary ms-1">{{ $assignments->total() }}</span>
                </span>
                <div>
                    <a href="{{ route('user.my-assignments') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Back to Active
                    </a>
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
                                <th>Returned Date</th>
                                <th style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assignments as $assignment)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle bg-secondary text-white me-2">
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
                                </td>
                                <td>
                                    <small>{{ $assignment->actual_return_date ? $assignment->actual_return_date->format('M d, Y') : 'N/A' }}</small>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary view-btn" data-id="{{ $assignment->id }}" title="View">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5">
                                    <div class="text-center py-5">
                                        <i class="fas fa-history fa-4x text-muted mb-3 d-block"></i>
                                        <h5>No Assignment History</h5>
                                        <p class="text-muted">You haven't returned any assets yet.</p>
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

<!-- View Modal -->
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

@push('scripts')
<script>
$(document).ready(function() {
    // View Assignment
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
                            <label class="text-muted small fw-bold">Returned Date</label>
                            <p class="mb-0">${r.actual_return_date ? new Date(r.actual_return_date).toLocaleDateString() : 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Condition</label>
                            <p class="mb-0">${r.condition_in || 'N/A'}</p>
                        </div>
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

    // Fix modal backdrop
    $('.modal').on('hidden.bs.modal', function() {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
    });
});
</script>
@endpush
@endsection