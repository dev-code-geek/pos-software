{{-- resources/views/admin/pos/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Point of Sale')
@section('page-title', 'Point of Sale')

@section('admin-content')
<div class="row g-3">
    {{-- Product Grid --}}
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-boxes me-2"></i>Products
                        <span class="badge bg-secondary ms-1" id="productCount">{{ $products->count() }}</span>
                    </h5>
                    <div class="d-flex gap-2">
                        <div class="input-group input-group-sm" style="width: 250px;">
                            <input type="text" id="searchProduct" class="form-control" placeholder="Search products...">
                            <button class="btn btn-secondary" type="button" id="searchBtn">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <select id="categoryFilter" class="form-select form-select-sm" style="width: 150px;">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="row g-2 p-3" id="productGrid">
                    @foreach($products as $product)
                    <div class="col-lg-2 col-md-3 col-6">
                        <div class="product-card card h-100 shadow-sm" data-product-id="{{ $product->id }}">
                            <div class="card-body text-center p-2">
                                <div class="product-icon">
                                    <i class="fas fa-box fa-2x text-primary"></i>
                                </div>
                                <h6 class="product-name mb-0 text-truncate">{{ $product->name }}</h6>
                                <small class="text-muted d-block">{{ $product->sku }}</small>
                                <div class="mt-2">
                                    <span class="badge bg-success">In Stock: {{ $product->quantity }}</span>
                                </div>
                                <div class="mt-1">
                                    <strong class="text-primary">${{ number_format($product->selling_price, 2) }}</strong>
                                </div>
                                <button class="btn btn-primary btn-sm w-100 add-to-cart mt-2" 
                                        data-product-id="{{ $product->id }}"
                                        data-product-name="{{ $product->name }}"
                                        data-product-price="{{ $product->selling_price }}">
                                    <i class="fas fa-cart-plus me-1"></i> Add
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Cart Sidebar --}}
    <div class="col-lg-4">
        <div class="card sticky-top" style="top: 20px;">
            <div class="card-header bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-shopping-cart me-2"></i> Cart</h5>
                    <span class="badge bg-light text-dark" id="cartCount">{{ $itemCount }}</span>
                </div>
            </div>
            <div class="card-body p-0">
                <div id="cartItems" style="max-height: 400px; overflow-y: auto;">
                    @if(empty($cart))
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                            <p>Your cart is empty</p>
                        </div>
                    @else
                        @foreach($cart as $item)
                        <div class="cart-item d-flex justify-content-between align-items-center p-2 border-bottom">
                            <div>
                                <small class="fw-bold d-block">{{ $item['name'] }}</small>
                                <small class="text-muted">{{ $item['sku'] }}</small>
                                <div class="d-flex align-items-center mt-1">
                                    <button class="btn btn-sm btn-outline-secondary qty-btn" data-product-id="{{ $item['product_id'] }}" data-action="decrease">-</button>
                                    <span class="mx-2 quantity-display">{{ $item['quantity'] }}</span>
                                    <button class="btn btn-sm btn-outline-secondary qty-btn" data-product-id="{{ $item['product_id'] }}" data-action="increase">+</button>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold text-primary">${{ number_format($item['total'], 2) }}</div>
                                <button class="btn btn-sm btn-danger remove-item" data-product-id="{{ $item['product_id'] }}">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    @endif
                </div>
            </div>
            <div class="card-footer bg-light">
                <div class="row g-2">
                    <div class="col-12">
                        <div class="d-flex justify-content-between">
                            <span>Items:</span>
                            <span id="cartTotalItems">{{ $itemCount }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Subtotal:</span>
                            <span id="cartSubtotal" class="fw-bold">${{ number_format($subtotal, 2) }}</span>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">Discount</span>
                            <input type="number" id="discountInput" class="form-control" step="0.01" min="0" placeholder="0.00">
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">Amount Paid</span>
                            <input type="number" id="amountPaid" class="form-control" step="0.01" min="0" placeholder="0.00">
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex justify-content-between">
                            <span>Change:</span>
                            <span id="changeAmount" class="fw-bold text-success">$0.00</span>
                        </div>
                    </div>
                    <div class="col-12">
                        <select id="paymentMethod" class="form-select form-select-sm">
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="mobile">Mobile Money</option>
                            <option value="credit">Credit</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <select id="customerSelect" class="form-select form-select-sm">
                            <option value="">Walk-in Customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <button class="btn btn-danger btn-sm w-100" id="clearCartBtn">
                            <i class="fas fa-trash me-1"></i> Clear
                        </button>
                    </div>
                    <div class="col-6">
                        <button class="btn btn-success btn-sm w-100" id="checkoutBtn">
                            <i class="fas fa-check me-1"></i> Checkout
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.product-card {
    cursor: pointer;
    transition: transform 0.2s;
}
.product-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
.product-icon {
    font-size: 1.5rem;
}
.product-name {
    font-size: 0.8rem;
    font-weight: 600;
}
.cart-item {
    transition: background-color 0.2s;
}
.cart-item:hover {
    background-color: #f8f9fa;
}
.qty-btn {
    width: 24px;
    height: 24px;
    padding: 0;
    font-size: 12px;
    line-height: 1;
}
.quantity-display {
    min-width: 20px;
    text-align: center;
    font-weight: 600;
}
</style>

@push('scripts')
<script>
$(document).ready(function() {
    // ==========================================
    // ADD TO CART
    // ==========================================
    $(document).on('click', '.add-to-cart', function() {
        var productId = $(this).data('product-id');
        var productName = $(this).data('product-name');
        
        $.ajax({
            url: '{{ route("admin.pos.add-to-cart") }}',
            type: 'POST',
            data: {
                product_id: productId,
                _token: '{{ csrf_token() }}'
            },
            success: function(r) {
                if (r.success) {
                    updateCartUI(r);
                    showToast('Added to cart: ' + productName, 'success');
                }
            },
            error: function(xhr) {
                showToast(xhr.responseJSON?.message || 'Failed to add to cart', 'error');
            }
        });
    });

    // ==========================================
    // UPDATE QUANTITY
    // ==========================================
    $(document).on('click', '.qty-btn', function() {
        var productId = $(this).data('product-id');
        var action = $(this).data('action');
        var currentQty = parseInt($(this).closest('.cart-item').find('.quantity-display').text());
        var newQty = action === 'increase' ? currentQty + 1 : currentQty - 1;

        if (newQty <= 0) {
            // Remove item
            removeItem(productId);
            return;
        }

        $.ajax({
            url: '{{ route("admin.pos.update-cart") }}',
            type: 'POST',
            data: {
                product_id: productId,
                quantity: newQty,
                _token: '{{ csrf_token() }}'
            },
            success: function(r) {
                if (r.success) {
                    updateCartUI(r);
                }
            }
        });
    });

    // ==========================================
    // REMOVE ITEM
    // ==========================================
    $(document).on('click', '.remove-item', function() {
        var productId = $(this).data('product-id');
        removeItem(productId);
    });

    function removeItem(productId) {
        $.ajax({
            url: '{{ route("admin.pos.remove-from-cart") }}',
            type: 'POST',
            data: {
                product_id: productId,
                _token: '{{ csrf_token() }}'
            },
            success: function(r) {
                if (r.success) {
                    updateCartUI(r);
                }
            }
        });
    }

    // ==========================================
    // CLEAR CART
    // ==========================================
    $('#clearCartBtn').on('click', function() {
        if (!confirm('Are you sure you want to clear the cart?')) return;

        $.ajax({
            url: '{{ route("admin.pos.clear-cart") }}',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function(r) {
                if (r.success) {
                    updateCartUI({ cart: [], subtotal: 0, count: 0 });
                    showToast('Cart cleared', 'info');
                }
            }
        });
    });

    // ==========================================
    // SEARCH PRODUCTS
    // ==========================================
    $('#searchBtn').on('click', searchProducts);
    $('#searchProduct').on('keypress', function(e) {
        if (e.which === 13) searchProducts();
    });

    function searchProducts() {
        var search = $('#searchProduct').val();
        var category = $('#categoryFilter').val();

        $.ajax({
            url: '{{ route("admin.pos.search-products") }}',
            type: 'GET',
            data: { search: search, category: category },
            success: function(products) {
                var grid = $('#productGrid');
                grid.empty();

                if (products.length === 0) {
                    grid.html('<div class="col-12 text-center py-5"><i class="fas fa-search fa-3x text-muted mb-3"></i><p>No products found</p></div>');
                    return;
                }

                $.each(products, function(index, product) {
                    var html = `
                        <div class="col-lg-2 col-md-3 col-6">
                            <div class="product-card card h-100 shadow-sm" data-product-id="${product.id}">
                                <div class="card-body text-center p-2">
                                    <div class="product-icon">
                                        <i class="fas fa-box fa-2x text-primary"></i>
                                    </div>
                                    <h6 class="product-name mb-0 text-truncate">${product.name}</h6>
                                    <small class="text-muted d-block">${product.sku}</small>
                                    <div class="mt-2">
                                        <span class="badge bg-success">In Stock: ${product.quantity}</span>
                                    </div>
                                    <div class="mt-1">
                                        <strong class="text-primary">$${parseFloat(product.selling_price).toFixed(2)}</strong>
                                    </div>
                                    <button class="btn btn-primary btn-sm w-100 add-to-cart mt-2" 
                                            data-product-id="${product.id}"
                                            data-product-name="${product.name}"
                                            data-product-price="${product.selling_price}">
                                        <i class="fas fa-cart-plus me-1"></i> Add
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                    grid.append(html);
                });

                $('#productCount').text(products.length);
            }
        });
    }

    // ==========================================
    // CATEGORY FILTER
    // ==========================================
    $('#categoryFilter').on('change', function() {
        searchProducts();
    });

    // ==========================================
    // UPDATE CART UI
    // ==========================================
    function updateCartUI(data) {
        // Update cart items
        var cartItems = $('#cartItems');
        cartItems.empty();

        if (data.cart && Object.keys(data.cart).length > 0) {
            $.each(data.cart, function(productId, item) {
                var html = `
                    <div class="cart-item d-flex justify-content-between align-items-center p-2 border-bottom">
                        <div>
                            <small class="fw-bold d-block">${item.name}</small>
                            <small class="text-muted">${item.sku}</small>
                            <div class="d-flex align-items-center mt-1">
                                <button class="btn btn-sm btn-outline-secondary qty-btn" data-product-id="${item.product_id}" data-action="decrease">-</button>
                                <span class="mx-2 quantity-display">${item.quantity}</span>
                                <button class="btn btn-sm btn-outline-secondary qty-btn" data-product-id="${item.product_id}" data-action="increase">+</button>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold text-primary">$${parseFloat(item.total).toFixed(2)}</div>
                            <button class="btn btn-sm btn-danger remove-item" data-product-id="${item.product_id}">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                `;
                cartItems.append(html);
            });
        } else {
            cartItems.html(`
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                    <p>Your cart is empty</p>
                </div>
            `);
        }

        // Update totals
        $('#cartCount').text(data.count);
        $('#cartTotalItems').text(data.count);
        $('#cartSubtotal').text('$' + parseFloat(data.subtotal).toFixed(2));

        // Update product quantities in grid
        if (data.cart) {
            $('.product-card').each(function() {
                var productId = $(this).data('product-id');
                if (data.cart[productId]) {
                    $(this).find('.add-to-cart').prop('disabled', true).html('<i class="fas fa-check me-1"></i> Added');
                } else {
                    $(this).find('.add-to-cart').prop('disabled', false).html('<i class="fas fa-cart-plus me-1"></i> Add');
                }
            });
        }
    }

    // ==========================================
    // CHECKOUT
    // ==========================================
    $('#checkoutBtn').on('click', function() {
        var customerId = $('#customerSelect').val();
        var paymentMethod = $('#paymentMethod').val();
        var amountPaid = parseFloat($('#amountPaid').val()) || 0;
        var discount = parseFloat($('#discountInput').val()) || 0;

        if (amountPaid <= 0) {
            showToast('Please enter amount paid', 'warning');
            return;
        }

        $.ajax({
            url: '{{ route("admin.pos.checkout") }}',
            type: 'POST',
            data: {
                customer_id: customerId,
                payment_method: paymentMethod,
                amount_paid: amountPaid,
                discount: discount,
                _token: '{{ csrf_token() }}'
            },
            success: function(r) {
                if (r.success) {
                    showToast(r.message, 'success');
                    updateCartUI({ cart: {}, subtotal: 0, count: 0 });
                    $('#discountInput').val('');
                    $('#amountPaid').val('');
                    $('#changeAmount').text('$0.00');
                    
                    if (r.redirect) {
                        window.location.href = r.redirect;
                    } else {
                        location.reload();
                    }
                }
            },
            error: function(xhr) {
                showToast(xhr.responseJSON?.message || 'Checkout failed', 'error');
            }
        });
    });

    // ==========================================
    // CALCULATE CHANGE
    // ==========================================
    $('#amountPaid').on('input', function() {
        var amountPaid = parseFloat($(this).val()) || 0;
        var subtotal = parseFloat($('#cartSubtotal').text().replace('$', '')) || 0;
        var discount = parseFloat($('#discountInput').val()) || 0;
        var total = subtotal - discount;
        var change = amountPaid - total;
        $('#changeAmount').text('$' + Math.max(0, change).toFixed(2));
    });

    $('#discountInput').on('input', function() {
        var subtotal = parseFloat($('#cartSubtotal').text().replace('$', '')) || 0;
        var discount = parseFloat($(this).val()) || 0;
        var total = subtotal - discount;
        var amountPaid = parseFloat($('#amountPaid').val()) || 0;
        var change = amountPaid - total;
        $('#changeAmount').text('$' + Math.max(0, change).toFixed(2));
    });

    // ==========================================
    // TOAST NOTIFICATION
    // ==========================================
    function showToast(message, type) {
        Swal.fire({
            icon: type,
            title: message,
            timer: 2000,
            showConfirmButton: false,
        });
    }

    // ==========================================
    // BARCODE SCANNING
    // ==========================================
    // If you have a barcode scanner, it will automatically
    // trigger keypress events. You can use this:
    var barcodeBuffer = '';
    var barcodeTimer = null;

    $(document).on('keypress', function(e) {
        // If input fields are focused, ignore
        if ($('input:focus').length > 0) return;

        // Allow numbers and some special characters
        if (e.key >= '0' && e.key <= '9') {
            barcodeBuffer += e.key;
            clearTimeout(barcodeTimer);
            barcodeTimer = setTimeout(function() {
                if (barcodeBuffer.length > 0) {
                    scanBarcode(barcodeBuffer);
                    barcodeBuffer = '';
                }
            }, 100);
        }
    });

    function scanBarcode(barcode) {
        $.ajax({
            url: '{{ route("admin.pos.scan-barcode") }}',
            type: 'GET',
            data: { barcode: barcode },
            success: function(product) {
                if (product) {
                    // Add to cart
                    $.ajax({
                        url: '{{ route("admin.pos.add-to-cart") }}',
                        type: 'POST',
                        data: {
                            product_id: product.id,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(r) {
                            if (r.success) {
                                updateCartUI(r);
                                showToast('Added: ' + product.name, 'success');
                            }
                        }
                    });
                }
            },
            error: function() {
                showToast('Product not found', 'error');
            }
        });
    }
});
</script>
@endpush
@endsection