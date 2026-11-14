@extends('layouts.user')

@section('title', 'Product Details')
@section('page-title', 'Product Details')

@section('user-content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-4 d-inline-block">
                        <i class="fas fa-{{ $product->type == 'hardware' ? 'microchip' : 'code' }} fa-4x text-primary"></i>
                    </div>
                    <h3 class="fw-bold mt-3">{{ $product->name }}</h3>
                    <p class="text-muted">
                        <span class="badge bg-{{ $product->type == 'hardware' ? 'primary' : 'success' }}">
                            {{ ucfirst($product->type) }}
                        </span>
                        <span class="badge bg-secondary ms-1">{{ $product->sku }}</span>
                    </p>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="text-muted small fw-bold">Category</label>
                        <p>{{ $product->category->name ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small fw-bold">Quantity Available</label>
                        <p><span class="fw-bold text-success">{{ $product->quantity }}</span> units</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small fw-bold">Status</label>
                        <p><span class="badge bg-success">{{ ucfirst($product->status) }}</span></p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small fw-bold">Supplier</label>
                        <p>{{ $product->supplier->name ?? 'N/A' }}</p>
                    </div>
                    @if($product->purchase_price)
                    <div class="col-md-6">
                        <label class="text-muted small fw-bold">Purchase Price</label>
                        <p>${{ number_format($product->purchase_price, 2) }}</p>
                    </div>
                    @endif
                    @if($product->warranty_end_date)
                    <div class="col-md-6">
                        <label class="text-muted small fw-bold">Warranty End</label>
                        <p>{{ $product->warranty_end_date->format('M d, Y') }}</p>
                    </div>
                    @endif
                    @if($product->description)
                    <div class="col-12">
                        <label class="text-muted small fw-bold">Description</label>
                        <p>{{ $product->description }}</p>
                    </div>
                    @endif
                    @if($product->specs)
                    <div class="col-12">
                        <label class="text-muted small fw-bold">Specifications</label>
                        <div class="bg-light p-3 rounded">
                            @if(is_array($product->specs))
                                @foreach($product->specs as $key => $value)
                                    <div class="d-flex justify-content-between py-1 border-bottom">
                                        <span class="fw-bold">{{ ucfirst(str_replace('_', ' ', $key)) }}</span>
                                        <span>{{ $value }}</span>
                                    </div>
                                @endforeach
                            @else
                                <p>{{ $product->specs }}</p>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>

                <div class="mt-4 text-center">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createRequestModal">
                        <i class="fas fa-handshake me-1"></i> Request This Asset
                    </button>
                    <a href="{{ route('user.products') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Products
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Request Modal -->
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
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        You are requesting: <strong>{{ $product->name }}</strong>
                    </div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="request_title" class="form-label fw-bold">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="request_title" class="form-control" value="Request for {{ $product->name }}" required>
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
                                <option value="{{ $product->category_id }}" selected>{{ $product->category->name ?? 'N/A' }}</option>
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

    // Fix modal backdrop
    $('.modal').on('hidden.bs.modal', function() {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
    });
});
</script>
@endpush
@endsection