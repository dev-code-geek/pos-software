@extends('layouts.admin')

@section('title', 'Stock Levels')
@section('page-title', 'Stock Management')

@section('admin-content')
<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ $stats['total_products'] ?? 0 }}</h3>
                <p>Products in Stock</p>
            </div>
            <div class="icon"><i class="fas fa-boxes"></i></div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $stats['total_quantity'] ?? 0 }}</h3>
                <p>Total Quantity</p>
            </div>
            <div class="icon"><i class="fas fa-box"></i></div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $stats['low_stock'] ?? 0 }}</h3>
                <p>Low Stock Items</p>
            </div>
            <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $stock->total() }}</h3>
                <p>Warehouse Records</p>
            </div>
            <div class="icon"><i class="fas fa-warehouse"></i></div>
        </div>
    </div>
</div>

<!-- Main Card -->
<div class="card">
    <div class="card-header">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <h5 class="card-title mb-0">
                <i class="fas fa-list text-primary me-2"></i>
                All Stock Records
                <span class="badge bg-primary ms-1">{{ $stock->total() }}</span>
            </h5>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <div class="input-group input-group-sm" style="width: 200px;">
                <input type="text" id="searchInput" class="form-control" placeholder="Search product...">
                <button class="btn btn-secondary" type="button" id="searchBtn"><i class="fas fa-search"></i></button>
            </div>
            <select id="productFilter" class="form-select form-select-sm" style="width: 150px;">
                <option value="">All Products</option>
                @foreach($products as $p)
                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                @endforeach
            </select>
            <select id="warehouseFilter" class="form-select form-select-sm" style="width: 140px;">
                <option value="">All Warehouses</option>
                @foreach($warehouses as $w)
                    <option value="{{ $w->id }}">{{ $w->name }}</option>
                @endforeach
            </select>
            <input type="number" id="minQty" class="form-control form-control-sm" style="width: 90px;" placeholder="Min Qty">
            <input type="number" id="maxQty" class="form-control form-control-sm" style="width: 90px;" placeholder="Max Qty">
            <button class="btn btn-secondary btn-sm" id="filterBtn"><i class="fas fa-filter"></i> Filter</button>
            @can('stock.export')
                <a href="{{ route('admin.stock.export') }}" class="btn btn-success btn-sm">
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
                        <th style="width:35px;">#</th>
                        <th style="min-width:180px;">Product</th>
                        <th style="min-width:120px;">SKU</th>
                        <th style="min-width:140px;">Warehouse</th>
                        <th style="width:100px; text-align:center;">Quantity</th>
                        <th style="width:100px;">Min Stock</th>
                        <th style="width:100px;">Status</th>
                        <th style="width:120px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($stock as $item)
                    <tr data-id="{{ $item->id }}" 
                        data-product="{{ $item->product_id }}"
                        data-warehouse="{{ $item->warehouse_id }}"
                        data-quantity="{{ $item->quantity }}">
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle bg-primary text-white me-2">
                                    <i class="fas fa-box"></i>
                                </div>
                                <div>
                                    <div class="fw-bold small">{{ Str::limit($item->product->name ?? 'N/A', 30) }}</div>
                                    <small class="text-muted">{{ $item->product->type ?? 'N/A' }}</small>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge bg-secondary">{{ $item->product->sku ?? 'N/A' }}</span></td>
                        <td>{{ $item->warehouse->name ?? 'N/A' }}</td>
                        <td class="text-center">
                            <span class="fw-bold {{ $item->quantity == 0 ? 'text-danger' : ($item->quantity <= ($item->product->min_stock_level ?? 0) ? 'text-warning' : 'text-success') }}">
                                {{ $item->quantity }}
                            </span>
                            @if($item->quantity <= ($item->product->min_stock_level ?? 0) && $item->quantity > 0)
                                <i class="fas fa-exclamation-triangle text-warning ms-1" title="Low stock"></i>
                            @endif
                            @if($item->quantity == 0)
                                <i class="fas fa-times-circle text-danger ms-1" title="Out of stock"></i>
                            @endif
                        </td>
                        <td>{{ $item->product->min_stock_level ?? 0 }}</td>
                        <td>
                            @php
                                $status = $item->quantity == 0 ? 'Out of Stock' : ($item->quantity <= ($item->product->min_stock_level ?? 0) ? 'Low Stock' : 'In Stock');
                                $class = $item->quantity == 0 ? 'danger' : ($item->quantity <= ($item->product->min_stock_level ?? 0) ? 'warning text-dark' : 'success');
                            @endphp
                            <span class="badge bg-{{ $class }}">{{ $status }}</span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <button class="btn btn-outline-primary btn-sm view-btn" data-id="{{ $item->id }}" title="View"><i class="fas fa-eye"></i></button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">
                            <div class="text-center py-5">
                                <i class="fas fa-boxes fa-4x text-muted mb-3 d-block"></i>
                                <h5 class="fw-bold">No Stock Records Found</h5>
                                <p class="text-muted">Start by adding products and performing stock adjustments.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div><span class="text-muted small">Showing {{ $stock->firstItem() ?? 0 }} to {{ $stock->lastItem() ?? 0 }} of {{ $stock->total() }} entries</span></div>
        <div>{{ $stock->links() }}</div>
    </div>
</div>

<!-- ============================================ -->
<!-- VIEW MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 700px;">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title text-white"><i class="fas fa-eye me-2"></i> Stock Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewModalBody" style="padding:20px 25px;">
                <div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
        </div>
    </div>
</div>

<style>
    .avatar-circle { width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:600; flex-shrink:0; }
    .small-box { border-radius:12px; padding:20px; margin-bottom:0; position:relative; overflow:hidden; transition:transform 0.15s ease; }
    .small-box:hover { transform:translateY(-2px); }
    .small-box .inner { color:#fff; }
    .small-box .inner h3 { font-size:2rem; font-weight:700; margin:0 0 5px; }
    .small-box .inner p { font-size:0.9rem; opacity:0.9; margin:0; }
    .small-box .icon { position:absolute; right:15px; top:15px; font-size:2.5rem; opacity:0.25; color:#fff; }
    .small-box .small-box-footer { display:block; padding:8px 0 0; color:rgba(255,255,255,0.8); text-decoration:none; font-size:0.85rem; transition:all 0.15s ease; }
    .small-box .small-box-footer:hover { color:#fff; }
    .small-box .small-box-footer i { transition:transform 0.15s ease; }
    .small-box:hover .small-box-footer i { transform:translateX(4px); }
</style>

@push('scripts')
<script>
$(document).ready(function() {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    // === VIEW STOCK DETAILS ===
    @can('stock.view')
    $(document).on('click', '.view-btn', function() {
        var id = $(this).data('id');
        var body = $('#viewModalBody');
        body.html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
        $('#viewModal').modal('show');
        $.ajax({
            url: '/admin/stock/' + id,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                var html = `
                    <div class="row g-3">
                        <div class="col-12"><h5 class="fw-bold">${data.product ? data.product.name : 'N/A'}</h5><hr></div>
                        <div class="col-6"><label class="text-muted small fw-bold">SKU</label><p class="mb-0"><span class="badge bg-secondary">${data.product ? data.product.sku : 'N/A'}</span></p></div>
                        <div class="col-6"><label class="text-muted small fw-bold">Type</label><p class="mb-0">${data.product ? data.product.type : 'N/A'}</p></div>
                        <div class="col-6"><label class="text-muted small fw-bold">Warehouse</label><p class="mb-0">${data.warehouse ? data.warehouse.name : 'N/A'}</p></div>
                        <div class="col-6"><label class="text-muted small fw-bold">Quantity</label><p class="mb-0 fw-bold ${data.quantity == 0 ? 'text-danger' : (data.quantity <= (data.product ? data.product.min_stock_level : 0) ? 'text-warning' : 'text-success')}">${data.quantity}</p></div>
                        <div class="col-6"><label class="text-muted small fw-bold">Min Stock Level</label><p class="mb-0">${data.product ? data.product.min_stock_level : 0}</p></div>
                        <div class="col-6"><label class="text-muted small fw-bold">Status</label>
                            <p class="mb-0"><span class="badge bg-${data.quantity == 0 ? 'danger' : (data.quantity <= (data.product ? data.product.min_stock_level : 0) ? 'warning text-dark' : 'success')}">
                                ${data.quantity == 0 ? 'Out of Stock' : (data.quantity <= (data.product ? data.product.min_stock_level : 0) ? 'Low Stock' : 'In Stock')}
                            </span></p>
                        </div>
                        <div class="col-6"><label class="text-muted small fw-bold">Last Updated</label><p class="mb-0">${new Date(data.updated_at).toLocaleString()}</p></div>
                        ${data.product ? `
                        <div class="col-12"><hr><h6 class="fw-bold text-secondary">Product Details</h6></div>
                        <div class="col-6"><label class="text-muted small fw-bold">Category</label><p class="mb-0">${data.product.category ? data.product.category.name : 'N/A'}</p></div>
                        <div class="col-6"><label class="text-muted small fw-bold">Supplier</label><p class="mb-0">${data.product.supplier ? data.product.supplier.name : 'N/A'}</p></div>
                        <div class="col-6"><label class="text-muted small fw-bold">Selling Price</label><p class="mb-0">${data.product.selling_price ? '$' + parseFloat(data.product.selling_price).toFixed(2) : 'N/A'}</p></div>
                        <div class="col-6"><label class="text-muted small fw-bold">Cost Price</label><p class="mb-0">${data.product.cost_price ? '$' + parseFloat(data.product.cost_price).toFixed(2) : 'N/A'}</p></div>
                        ` : ''}
                    </div>
                `;
                body.html(html);
            },
            error: function(xhr) {
                body.html('<div class="text-center py-5 text-danger"><i class="fas fa-exclamation-circle fa-4x mb-3 d-block"></i><h5>Failed to load stock details</h5><p>' + xhr.status + ' - ' + xhr.statusText + '</p></div>');
            }
        });
    });
    @endcan

    // === FILTER ===
    function filterTable() {
        var search = $('#searchInput').val().toLowerCase();
        var product = $('#productFilter').val();
        var warehouse = $('#warehouseFilter').val();
        var minQty = parseInt($('#minQty').val()) || 0;
        var maxQty = parseInt($('#maxQty').val()) || 999999;

        $('#tableBody tr').each(function() {
            var row = $(this);
            var text = row.text().toLowerCase();
            var rowProduct = row.data('product');
            var rowWarehouse = row.data('warehouse');
            var rowQty = parseInt(row.data('quantity')) || 0;

            var show = true;
            if (search && !text.includes(search)) show = false;
            if (product && rowProduct != product) show = false;
            if (warehouse && rowWarehouse != warehouse) show = false;
            if (rowQty < minQty || rowQty > maxQty) show = false;

            row.toggle(show);
        });
    }

    $('#searchBtn').on('click', filterTable);
    $('#searchInput').on('keypress', function(e) { if (e.which === 13) filterTable(); });
    $('#productFilter, #warehouseFilter').on('change', filterTable);
    $('#filterBtn').on('click', filterTable);

    // Reset filters
    $('#filterBtn').on('dblclick', function() {
        $('#searchInput').val('');
        $('#productFilter').val('');
        $('#warehouseFilter').val('');
        $('#minQty').val('');
        $('#maxQty').val('');
        filterTable();
    });
});
</script>
@endpush
@endsection