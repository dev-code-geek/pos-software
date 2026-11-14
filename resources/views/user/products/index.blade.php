@extends('layouts.user')

@section('title', 'Available Products')
@section('page-title', 'Available Products')

@section('user-content')
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="fas fa-box text-primary me-2"></i>Available Products</h5>
        <div class="d-flex gap-2">
            <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Search..." style="width:200px;">
            <select id="categoryFilter" class="form-select form-select-sm" style="width:150px;">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3">
            @forelse($products as $product)
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="product-card">
                    <div class="product-card-body">
                        <div class="product-icon">
                            <i class="fas fa-{{ $product->type == 'hardware' ? 'microchip' : 'code' }}"></i>
                        </div>
                        <h6 class="product-name">{{ $product->name }}</h6>
                        <div class="product-details">
                            <span class="badge bg-{{ $product->type == 'hardware' ? 'primary' : 'success' }}">{{ ucfirst($product->type) }}</span>
                            <span class="badge bg-secondary">{{ $product->sku }}</span>
                        </div>
                        <div class="product-stock">
                            <span class="text-muted small">Available: <strong>{{ $product->quantity }}</strong></span>
                        </div>
                        <a href="{{ route('user.products.show', $product->id) }}" class="btn btn-primary btn-sm w-100 mt-2">
                            <i class="fas fa-eye me-1"></i> View Details
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12 text-center py-5">
                <i class="fas fa-box fa-4x text-muted mb-3 d-block"></i>
                <h5>No Products Available</h5>
                <p class="text-muted">Check back later for new inventory.</p>
            </div>
            @endforelse
        </div>
    </div>
    <div class="card-footer">
        {{ $products->links() }}
    </div>
</div>

<style>
.product-card {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    transition: all 0.2s ease;
    height: 100%;
}
.product-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
}
.product-card-body {
    padding: 20px;
    text-align: center;
}
.product-icon {
    width: 60px;
    height: 60px;
    background: #f1f5f9;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 12px;
    font-size: 1.5rem;
    color: #4f46e5;
}
.product-name {
    font-weight: 600;
    margin-bottom: 8px;
}
.product-details {
    display: flex;
    justify-content: center;
    gap: 6px;
    flex-wrap: wrap;
    margin-bottom: 10px;
}
.product-stock {
    margin-bottom: 10px;
}
</style>

@push('scripts')
<script>
$(document).ready(function() {
    function filterProducts() {
        var search = $('#searchInput').val().toLowerCase();
        var category = $('#categoryFilter').val();

        $('.product-card').each(function() {
            var card = $(this);
            var text = card.text().toLowerCase();
            var categoryId = card.data('category');
            
            var show = true;
            if (search && !text.includes(search)) show = false;
            if (category && categoryId != category) show = false;
            
            card.closest('.col-xl-3').toggle(show);
        });
    }

    $('#searchInput').on('keyup', filterProducts);
    $('#categoryFilter').on('change', filterProducts);
});
</script>
@endpush
@endsection