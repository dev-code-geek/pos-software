@extends('layouts.admin')

@section('title', 'Product Variants')
@section('page-title', 'Product Variant Management')

@section('admin-content')
<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="small-box bg-primary">
            <div class="inner"><h3>{{ number_format($stats['total'] ?? 0) }}</h3><p>Total Variants</p></div>
            <div class="icon"><i class="fas fa-cubes"></i></div>
            <a href="{{ route('admin.variants.index') }}" class="small-box-footer">View All <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="small-box bg-success">
            <div class="inner"><h3>{{ number_format($stats['active'] ?? 0) }}</h3><p>Active</p></div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
            <a href="{{ route('admin.variants.index', ['status' => 'active']) }}" class="small-box-footer">View Active <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="small-box bg-secondary">
            <div class="inner"><h3>{{ number_format($stats['inactive'] ?? 0) }}</h3><p>Inactive</p></div>
            <div class="icon"><i class="fas fa-pause-circle"></i></div>
            <a href="{{ route('admin.variants.index', ['status' => 'inactive']) }}" class="small-box-footer">View Inactive <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="small-box bg-warning text-dark">
            <div class="inner"><h3>{{ number_format($stats['low_stock'] ?? 0) }}</h3><p>Low Stock</p></div>
            <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
            <a href="#" class="small-box-footer">View Low Stock <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<!-- Main Card -->
<div class="card">
    <div class="card-header">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <h5 class="card-title mb-0"><i class="fas fa-cubes text-primary me-2"></i>All Variants <span class="badge bg-primary ms-1">{{ $variants->total() }}</span></h5>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <input type="text" id="searchInput" class="form-control form-control-sm" style="width:200px;" placeholder="Search...">
            <select id="statusFilter" class="form-select form-select-sm" style="width:120px;">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
            @can('variant.create')
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fas fa-plus-circle me-1"></i> Add Variant
            </button>
            @endcan
            @can('variant.export')
            <button class="btn btn-success btn-sm" id="exportBtn"><i class="fas fa-file-excel me-1"></i> Export</button>
            @endcan
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:35px;">#</th>
                        <th style="min-width:150px;">Product</th>
                        <th>SKU</th>
                        <th style="min-width:130px;">Name</th>
                        <th class="text-end">Price</th>
                        <th class="text-end">Qty</th>
                        <th style="width:80px;">Status</th>
                        <th style="width:180px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($variants as $variant)
                    <tr data-id="{{ $variant->id }}" data-status="{{ $variant->is_active ? 'active' : 'inactive' }}">
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <div class="fw-bold small">{{ Str::limit($variant->product->name ?? 'N/A', 25) }}</div>
                            <small class="text-muted">{{ $variant->product->sku ?? '' }}</small>
                        </td>
                        <td><span class="badge bg-secondary">{{ $variant->sku }}</span></td>
                        <td>{{ Str::limit($variant->name, 30) }}</td>
                        <td class="text-end"><span class="fw-bold text-primary">${{ number_format($variant->selling_price, 2) }}</span></td>
                        <td class="text-end">{{ number_format($variant->quantity) }}</td>
                        <td>
                            @if($variant->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                @can('variant.view')
                                <button class="btn btn-outline-primary btn-sm view-btn" data-id="{{ $variant->id }}" title="View"><i class="fas fa-eye"></i></button>
                                @endcan
                                @can('variant.edit')
                                <button class="btn btn-outline-warning btn-sm edit-btn" data-id="{{ $variant->id }}" title="Edit"><i class="fas fa-edit"></i></button>
                                @endcan
                                @can('variant.toggle_status')
                                <button class="btn btn-outline-{{ $variant->is_active ? 'secondary' : 'success' }} toggle-status" 
                                        data-id="{{ $variant->id }}" 
                                        data-active="{{ $variant->is_active ? '1' : '0' }}"
                                        title="{{ $variant->is_active ? 'Deactivate' : 'Activate' }}">
                                    <i class="fas {{ $variant->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                                </button>
                                @endcan
                                @can('variant.delete')
                                <button class="btn btn-outline-danger btn-sm delete-btn" data-id="{{ $variant->id }}" title="Delete"><i class="fas fa-trash"></i></button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8"><div class="text-center py-5"><i class="fas fa-cubes fa-4x text-muted mb-3 d-block"></i><h5 class="fw-bold">No Variants Found</h5></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div><span class="text-muted small">Showing {{ $variants->firstItem() ?? 0 }} to {{ $variants->lastItem() ?? 0 }} of {{ $variants->total() }} entries</span></div>
        <div>{{ $variants->links() }}</div>
    </div>
</div>

<!-- ============================================ -->
<!-- CREATE MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width:600px;">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="fas fa-plus-circle me-2"></i> Add Product Variant</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="createForm" action="{{ route('admin.variants.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div id="createFormErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Product <span class="text-danger">*</span></label>
                            <select name="product_id" class="form-select" required>
                                <option value="">Select Product</option>
                                @foreach($products ?? [] as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">SKU <span class="text-danger">*</span></label>
                            <input type="text" name="sku" class="form-control" placeholder="Unique SKU" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g., Red, Large" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Selling Price <span class="text-danger">*</span></label>
                            <input type="number" name="selling_price" class="form-control" step="0.01" min="0" placeholder="0.00" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Cost Price</label>
                            <input type="number" name="cost_price" class="form-control" step="0.01" min="0" placeholder="0.00">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Quantity <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" class="form-control" min="0" placeholder="0" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Barcode</label>
                            <input type="text" name="barcode" class="form-control" placeholder="Barcode">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="create_is_active" value="1" checked>
                                <label class="form-check-label" for="create_is_active">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Cancel</button>
                    <button type="submit" class="btn btn-primary" id="createSubmitBtn"><i class="fas fa-save me-1"></i> Save Variant</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- VIEW MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width:600px;">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title text-white"><i class="fas fa-eye me-2"></i> Variant Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewModalBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- EDIT MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width:600px;">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title text-dark"><i class="fas fa-edit me-2"></i> Edit Variant</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body" id="editModalBody">
                    <div id="editFormErrors" class="alert alert-danger d-none"></div>
                    <div class="text-center py-5">
                        <div class="spinner-border text-warning" role="status"><span class="visually-hidden">Loading...</span></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Cancel</button>
                    <button type="submit" class="btn btn-warning" id="editSubmitBtn"><i class="fas fa-save me-1"></i> Update Variant</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- DELETE CONFIRM MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:560px;">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white"><i class="fas fa-exclamation-triangle me-2"></i> Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-trash fa-4x text-danger mb-3 d-block"></i>
                <p class="fw-bold">Are you sure you want to delete this variant?</p>
                <p class="text-muted small">This action cannot be undone.</p>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Cancel</button>
                <button type="submit" class="btn btn-danger" id="deleteSubmitBtn" form="deleteForm"><i class="fas fa-trash me-1"></i> Delete</button>
            </div>
        </div>
    </div>
</div>

<style>
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
.small-box.bg-warning .inner { color:#212529; }
.small-box.bg-warning .small-box-footer { color:rgba(33,37,41,0.8); }
.small-box.bg-warning .small-box-footer:hover { color:#212529; }
</style>

@push('scripts')
<script>
$(document).ready(function() {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    function showErrors(container, errors) {
        container.removeClass('d-none');
        var html = '<ul class="mb-0">';
        $.each(errors, function(key, messages) {
            $.each(messages, function(index, msg) { html += '<li>' + msg + '</li>'; });
        });
        html += '</ul>';
        container.html(html);
        container[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    function clearErrors(container) { container.addClass('d-none').empty(); }

    // ==========================================
    // CREATE
    // ==========================================
    @can('variant.create')
    $('#createForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var data = form.serialize();
        var btn = $('#createSubmitBtn');
        var errorContainer = $('#createFormErrors');

        clearErrors(errorContainer);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Creating...');

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(r) {
                if (r.success) {
                    $('#createModal').modal('hide');
                    Swal.fire({ icon: 'success', title: 'Added!', text: r.message, timer: 2000, showConfirmButton: true }).then(() => { window.location.reload(); });
                } else {
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save Variant');
                    if (r.errors) { showErrors(errorContainer, r.errors); } else { Swal.fire({ icon: 'error', title: 'Error!', text: r.message || 'Something went wrong.' }); }
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save Variant');
                if (xhr.status === 422) { showErrors(errorContainer, xhr.responseJSON.errors); } else { Swal.fire({ icon: 'error', title: 'Error!', text: 'Failed to create variant.' }); }
            }
        });
    });
    @endcan

    // ==========================================
    // VIEW
    // ==========================================
    @can('variant.view')
    $(document).on('click', '.view-btn', function() {
        var id = $(this).data('id');
        var body = $('#viewModalBody');
        body.html(`<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>`);
        $('#viewModal').modal('show');
        $.ajax({
            url: '/admin/variants/' + id,
            type: 'GET',
            dataType: 'json',
            success: function(r) {
                body.html(`
                    <div class="row g-3">
                        <div class="col-md-6"><label class="text-muted small fw-bold">Product</label><p class="fw-bold mb-0">${r.product ? r.product.name : 'N/A'}</p></div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">SKU</label><p class="mb-0"><span class="badge bg-secondary">${r.sku}</span></p></div>
                        <div class="col-12"><label class="text-muted small fw-bold">Name</label><p class="fw-bold mb-0">${r.name}</p></div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">Selling Price</label><p class="fw-bold text-primary mb-0">$${parseFloat(r.selling_price).toFixed(2)}</p></div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">Cost Price</label><p class="mb-0">$${parseFloat(r.cost_price || 0).toFixed(2)}</p></div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">Quantity</label><p class="mb-0">${r.quantity}</p></div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">Status</label><p class="mb-0">${r.is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'}</p></div>
                        ${r.attributes ? `<div class="col-12"><label class="text-muted small fw-bold">Attributes</label><p class="mb-0">${JSON.stringify(r.attributes)}</p></div>` : ''}
                    </div>
                `);
            },
            error: function(xhr) {
                body.html(`<div class="text-center py-5 text-danger"><i class="fas fa-exclamation-circle fa-4x mb-3 d-block"></i><h5>Failed to load</h5><p>${xhr.status} - ${xhr.statusText}</p></div>`);
            }
        });
    });
    @endcan

    // ==========================================
    // EDIT
    // ==========================================
    @can('variant.edit')
    $(document).on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        var body = $('#editModalBody');
        var form = $('#editForm');

        form.attr('action', '/admin/variants/' + id);
        body.html(`<div id="editFormErrors" class="alert alert-danger d-none"></div><div class="text-center py-5"><div class="spinner-border text-warning" role="status"><span class="visually-hidden">Loading...</span></div></div>`);
        $('#editModal').modal('show');

        $.ajax({
            url: '/admin/variants/' + id + '/edit',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                // response contains { variant: {...}, products: [...] }
                var variant = response.variant;
                var products = response.products || [];

                var html = `
                    <div id="editFormErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Product <span class="text-danger">*</span></label>
                            <select name="product_id" class="form-select" required>
                                <option value="">Select Product</option>
                                ${products.map(function(p) {
                                    return `<option value="${p.id}" ${variant.product_id == p.id ? 'selected' : ''}>${p.name} (${p.sku})</option>`;
                                }).join('')}
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">SKU <span class="text-danger">*</span></label>
                            <input type="text" name="sku" class="form-control" value="${variant.sku}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="${variant.name}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Selling Price <span class="text-danger">*</span></label>
                            <input type="number" name="selling_price" class="form-control" step="0.01" min="0" value="${variant.selling_price}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Cost Price</label>
                            <input type="number" name="cost_price" class="form-control" step="0.01" min="0" value="${variant.cost_price || ''}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Quantity <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" class="form-control" min="0" value="${variant.quantity}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Barcode</label>
                            <input type="text" name="barcode" class="form-control" value="${variant.barcode || ''}">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="edit_is_active" value="1" ${variant.is_active ? 'checked' : ''}>
                                <label class="form-check-label" for="edit_is_active">Active</label>
                            </div>
                        </div>
                    </div>
                `;
                body.html(html);
            },
            error: function(xhr) {
                body.html(`<div id="editFormErrors" class="alert alert-danger"></div><div class="text-center py-5 text-danger"><i class="fas fa-exclamation-circle fa-4x mb-3 d-block"></i><h5>Failed to load</h5><p>${xhr.status} - ${xhr.statusText}</p></div>`);
            }
        });
    });
    @endcan

    // ==========================================
    // UPDATE
    // ==========================================
    @can('variant.edit')
    $('#editForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var data = form.serialize();
        var btn = $('#editSubmitBtn');
        var errorContainer = $('#editFormErrors');

        clearErrors(errorContainer);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Updating...');

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(r) {
                if (r.success) {
                    $('#editModal').modal('hide');
                    Swal.fire({ icon: 'success', title: 'Updated!', text: r.message, timer: 2000, showConfirmButton: true }).then(() => { window.location.reload(); });
                } else {
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Update Variant');
                    if (r.errors) { showErrors(errorContainer, r.errors); } else { Swal.fire({ icon: 'error', title: 'Error!', text: r.message || 'Something went wrong.' }); }
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Update Variant');
                if (xhr.status === 422) { showErrors(errorContainer, xhr.responseJSON.errors); } else { Swal.fire({ icon: 'error', title: 'Error!', text: 'Failed to update variant.' }); }
            }
        });
    });
    @endcan

    // ==========================================
    // DELETE
    // ==========================================
    @can('variant.delete')
    $(document).on('click', '.delete-btn', function() {
        var id = $(this).data('id');
        var form = $('#deleteForm');
        form.attr('action', '/admin/variants/' + id);
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
            if (result.isConfirmed) { $('#deleteModal').modal('show'); }
        });
    });
    $('#deleteForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = $('#deleteSubmitBtn');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Deleting...');
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
            success: function(r) {
                if (r.success) {
                    $('#deleteModal').modal('hide');
                    Swal.fire({ icon: 'success', title: 'Deleted!', text: r.message, timer: 1500 }).then(() => { location.reload(); });
                } else {
                    btn.prop('disabled', false).html('<i class="fas fa-trash me-1"></i> Delete');
                    Swal.fire({ icon: 'error', title: 'Error!', text: r.message || 'Failed to delete.' });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-trash me-1"></i> Delete');
                Swal.fire({ icon: 'error', title: 'Error!', text: xhr.responseJSON?.message || 'Failed to delete.' });
            }
        });
    });
    @endcan

    // ==========================================
    // TOGGLE STATUS
    // ==========================================
    @can('variant.toggle_status')
    $(document).on('click', '.toggle-status', function() {
        var id = $(this).data('id');
        var active = $(this).data('active');
        Swal.fire({
            title: 'Confirm Status Change',
            text: active == 1 ? 'Deactivate this variant?' : 'Activate this variant?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: active == 1 ? '#d33' : '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: active == 1 ? 'Yes, deactivate' : 'Yes, activate'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/admin/variants/' + id + '/toggle-status',
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(r) {
                        if (r.success) {
                            Swal.fire({ icon: 'success', title: 'Success', text: r.message, timer: 1500 }).then(() => { location.reload(); });
                        }
                    },
                    error: function() { Swal.fire('Error', 'Failed to toggle status.', 'error'); }
                });
            }
        });
    });
    @endcan

    // ==========================================
    // SEARCH & FILTER
    // ==========================================
    $('#searchInput').on('keyup', filterTable);
    $('#statusFilter').on('change', filterTable);

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
    // EXPORT
    // ==========================================
    $('#exportBtn').on('click', function() {
        window.location.href = '{{ route("admin.variants.export") }}';
    });

    // ==========================================
    // MODAL RESET
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