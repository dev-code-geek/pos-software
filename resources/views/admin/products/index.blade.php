@extends('layouts.admin')

@section('title', __('messages.products'))
@section('page-title', __('messages.product_management'))

@section('admin-content')

<!-- ==================== STATS CARDS ==================== -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ $stats['total'] ?? 0 }}</h3>
                <p>{{ __('messages.total_products') }}</p>
            </div>
            <div class="icon"><i class="fas fa-boxes"></i></div>
            <a href="{{ route('admin.products.index') }}" class="small-box-footer">
                {{ __('messages.view_all') }} <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $stats['available'] ?? 0 }}</h3>
                <p>{{ __('messages.available') }}</p>
            </div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
            <a href="{{ route('admin.products.index', ['status' => 'available']) }}" class="small-box-footer">
                {{ __('messages.view_available') }} <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $stats['low_stock'] ?? 0 }}</h3>
                <p>{{ __('messages.low_stock') }}</p>
            </div>
            <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
            <a href="{{ route('admin.products.low-stock') }}" class="small-box-footer">
                {{ __('messages.view_low_stock') }} <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $stats['out_of_stock'] ?? 0 }}</h3>
                <p>{{ __('messages.out_of_stock') }}</p>
            </div>
            <div class="icon"><i class="fas fa-times-circle"></i></div>
            <a href="{{ route('admin.products.index', ['status' => 'out_of_stock']) }}" class="small-box-footer">
                {{ __('messages.view_out_of_stock') }} <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- ==================== MAIN CARD ==================== -->
<div class="card">
    <div class="card-header">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <h5 class="card-title mb-0">
                <i class="fas fa-box text-primary me-2"></i>
                {{ __('messages.all_products') }}
                <span class="badge bg-primary ms-1">{{ $products->total() }}</span>
            </h5>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <div class="input-group input-group-sm" style="width: 200px;">
                <input type="text" id="searchInput" class="form-control" placeholder="{{ __('messages.search') }}...">
                <button class="btn btn-secondary" type="button" id="searchBtn"><i class="fas fa-search"></i></button>
            </div>

            <input type="text" id="typeFilter" class="form-control form-control-sm" style="width:110px;" placeholder="{{ __('messages.type') }}...">

            <select id="statusFilter" class="form-select form-select-sm" style="width:130px;">
                <option value="">{{ __('messages.all_status') }}</option>
                <option value="available">{{ __('messages.available') }}</option>
                <option value="assigned">{{ __('messages.assigned') }}</option>
                <option value="under_repair">{{ __('messages.under_repair') }}</option>
                <option value="retired">{{ __('messages.retired') }}</option>
                <option value="expired">{{ __('messages.expired') }}</option>
            </select>

            <select id="categoryFilter" class="form-select form-select-sm" style="width:140px;">
                <option value="">{{ __('messages.all_categories') }}</option>
                @foreach($categories ?? [] as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>

            <select id="supplierFilter" class="form-select form-select-sm" style="width:150px;">
                <option value="">{{ __('messages.all_suppliers') }}</option>
                @foreach($suppliers ?? [] as $supplier)
                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                @endforeach
            </select>

            @can('product.create')
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fas fa-plus-circle me-1"></i> {{ __('messages.add_product') }}
            </button>
            @endcan

            @can('product.view')
            <a href="{{ route('admin.products.export') }}" class="btn btn-success btn-sm">
                <i class="fas fa-file-excel me-1"></i> {{ __('messages.export') }}
            </a>
            @endcan

            @can('product.create')
            <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#bulkUploadModal">
                <i class="fas fa-upload me-1"></i> {{ __('messages.bulk_upload') }}
            </button>
            @endcan

            @can('product.delete')
            <button class="btn btn-danger btn-sm" id="bulkDeleteBtn">
                <i class="fas fa-trash me-1"></i> {{ __('messages.bulk_delete') }}
            </button>
            @endcan
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        @can('product.delete')
                        <th style="width:35px;"><input type="checkbox" id="selectAll"></th>
                        @endcan
                        <th style="width:35px;">#</th>
                        <th style="min-width:160px;">{{ __('messages.product') }}</th>
                        <th>{{ __('messages.sku') }}</th>
                        <th>{{ __('messages.barcode') }}</th>
                        <th style="width:80px;">{{ __('messages.type') }}</th>
                        <th style="width:50px; text-align:center;">{{ __('messages.qty') }}</th>
                        <th style="text-align:right;">{{ __('messages.sell_price') }}</th>
                        <th style="text-align:right;">{{ __('messages.cost') }}</th>
                        <th style="width:100px;">{{ __('messages.status') }}</th>
                        <th style="width:200px;">{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($products as $product)
                    <tr data-id="{{ $product->id }}"
                        data-type="{{ $product->type ?? '' }}"
                        data-status="{{ $product->status }}"
                        data-category="{{ $product->category_id }}"
                        data-supplier="{{ $product->supplier_id }}"
                        data-quantity="{{ $product->quantity }}">

                        @can('product.delete')
                        <td><input type="checkbox" class="row-checkbox" value="{{ $product->id }}"></td>
                        @endcan

                        <td>{{ $loop->iteration }}</td>

                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle bg-primary text-white me-2">
                                    <i class="fas fa-box"></i>
                                </div>
                                <div>
                                    <div class="fw-bold small">{{ Str::limit($product->name, 25) }}</div>
                                    <small class="text-muted">{{ $product->type ?? 'N/A' }}</small>
                                </div>
                            </div>
                        </td>

                        <td><span class="badge bg-secondary">{{ $product->sku }}</span></td>
                        <td>{{ $product->barcode ?? 'N/A' }}</td>
                        <td><span class="badge bg-secondary">{{ $product->type ?? 'N/A' }}</span></td>

                        <td class="text-center">
                            <span class="fw-bold {{ $product->quantity == 0 ? 'text-danger' : ($product->quantity <= $product->min_stock_level ? 'text-warning' : 'text-success') }}">
                                {{ $product->quantity }}
                            </span>
                        </td>

                        <td class="text-end fw-bold text-primary">{{ number_format($product->selling_price ?? 0, 2) }}</td>
                        <td class="text-end text-muted">{{ number_format($product->cost_price ?? 0, 2) }}</td>

                        <td>
                            @php
                                $statusClasses = [
                                    'available' => 'bg-success',
                                    'assigned' => 'bg-primary',
                                    'under_repair' => 'bg-warning text-dark',
                                    'retired' => 'bg-secondary',
                                    'expired' => 'bg-danger'
                                ];
                            @endphp
                            <span class="badge {{ $statusClasses[$product->status] ?? 'bg-secondary' }}">
                                {{ str_replace('_', ' ', ucfirst($product->status)) }}
                            </span>
                        </td>

                        <td>
                            <div class="btn-group btn-group-sm">
                                @can('product.view')
                                <button class="btn btn-outline-primary btn-sm view-btn" data-id="{{ $product->id }}" title="{{ __('messages.view') }}">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @endcan
                                @can('product.edit')
                                <button class="btn btn-outline-warning btn-sm edit-btn" data-id="{{ $product->id }}" title="{{ __('messages.edit') }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @endcan
                                @can('stock.create')
                                <button class="btn btn-outline-info btn-sm adjust-btn" data-id="{{ $product->id }}" title="{{ __('messages.adjust_stock') }}">
                                    <i class="fas fa-plus-minus"></i>
                                </button>
                                @endcan
                                @can('product.delete')
                                <button class="btn btn-outline-danger btn-sm delete-btn" data-id="{{ $product->id }}" title="{{ __('messages.delete') }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ auth()->user()->can('product.delete') ? 11 : 10 }}">
                            <div class="text-center py-5">
                                <i class="fas fa-box fa-4x text-muted mb-3 d-block"></i>
                                <h5 class="fw-bold">{{ __('messages.no_products_found') }}</h5>
                                <p class="text-muted">{{ __('messages.click_add_product') }}</p>
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
                {{ __('messages.showing') }} {{ $products->firstItem() ?? 0 }}
                {{ __('messages.to') }} {{ $products->lastItem() ?? 0 }}
                {{ __('messages.of') }} {{ $products->total() }}
                {{ __('messages.entries') }}
            </span>
        </div>
        <div>{{ $products->links() }}</div>
    </div>
</div>

{{-- ==================== CREATE MODAL ==================== --}}
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl" style="max-width:1100px;">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle me-2"></i> {{ __('messages.add_product') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="createForm" action="{{ route('admin.products.store') }}" method="POST">
                @csrf
                <div class="modal-body" style="max-height:75vh; overflow-y:auto;">
                    <div id="createFormErrors" class="alert alert-danger d-none"></div>

                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-info-circle text-primary me-2"></i>{{ __('messages.basic_information') }}</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">{{ __('messages.name') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">{{ __('messages.sku') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="sku" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">{{ __('messages.barcode') }}</label>
                                    <input type="text" name="barcode" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">{{ __('messages.type') }}</label>
                                    <input type="text" name="type" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">{{ __('messages.category') }} <span class="text-danger">*</span></label>
                                    <select name="category_id" class="form-select" required>
                                        <option value="">{{ __('messages.select_category') }}</option>
                                        @foreach($categories ?? [] as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">{{ __('messages.supplier') }}</label>
                                    <select name="supplier_id" class="form-select">
                                        <option value="">{{ __('messages.select_supplier') }}</option>
                                        @foreach($suppliers ?? [] as $supplier)
                                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">{{ __('messages.quantity') }} <span class="text-danger">*</span></label>
                                    <input type="number" name="quantity" class="form-control" value="1" min="0" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">{{ __('messages.unit') }}</label>
                                    <input type="text" name="unit" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">{{ __('messages.status') }} <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select" required>
                                        <option value="available">{{ __('messages.available') }}</option>
                                        <option value="assigned">{{ __('messages.assigned') }}</option>
                                        <option value="under_repair">{{ __('messages.under_repair') }}</option>
                                        <option value="retired">{{ __('messages.retired') }}</option>
                                        <option value="expired">{{ __('messages.expired') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-money-bill-wave text-success me-2"></i>{{ __('messages.pricing') }}</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">{{ __('messages.selling_price') }}</label>
                                    <input type="number" name="selling_price" class="form-control" step="0.01" min="0">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">{{ __('messages.cost_price') }}</label>
                                    <input type="number" name="cost_price" class="form-control" step="0.01" min="0">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">{{ __('messages.purchase_price') }}</label>
                                    <input type="number" name="purchase_price" class="form-control" step="0.01" min="0">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">{{ __('messages.tax_rate') }} (%)</label>
                                    <input type="number" name="tax_rate" class="form-control" step="0.01" min="0" max="100">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-align-left me-2"></i>{{ __('messages.description') }}</h6>
                        </div>
                        <div class="card-body">
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> {{ __('messages.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary" id="createSubmitBtn">
                        <i class="fas fa-save me-1"></i> {{ __('messages.save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ==================== VIEW MODAL ==================== --}}
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl" style="max-width:1100px;">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title text-white">
                    <i class="fas fa-eye me-2"></i> {{ __('messages.product_details') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewModalBody" style="max-height:75vh; overflow-y:auto; padding:25px 30px;">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.close') }}</button>
            </div>
        </div>
    </div>
</div>

{{-- ==================== EDIT MODAL ==================== --}}
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl" style="max-width:1100px;">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title text-dark">
                    <i class="fas fa-edit me-2"></i> {{ __('messages.edit_product') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body" id="editModalBody" style="max-height:75vh; overflow-y:auto; padding:25px 30px;">
                    <div id="editFormErrors" class="alert alert-danger d-none"></div>
                    <div class="text-center py-5">
                        <div class="spinner-border text-warning"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> {{ __('messages.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-warning" id="editSubmitBtn">
                        <i class="fas fa-save me-1"></i> {{ __('messages.update') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ==================== ADJUST STOCK MODAL ==================== --}}
<div class="modal fade" id="adjustModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:560px;">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title text-white">
                    <i class="fas fa-plus-minus me-2"></i> {{ __('messages.adjust_stock') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="adjustForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div id="adjustFormErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.current_quantity') }}</label>
                            <p class="fw-bold" id="currentQuantityDisplay">0</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.new_quantity') }}</label>
                            <p class="fw-bold text-success" id="newQuantityDisplay">0</p>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">{{ __('messages.adjustment_type') }} <span class="text-danger">*</span></label>
                            <select name="type" id="adjustType" class="form-select" required>
                                <option value="add">{{ __('messages.add_stock') }}</option>
                                <option value="subtract">{{ __('messages.remove_stock') }}</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">{{ __('messages.quantity_to_adjust') }} <span class="text-danger">*</span></label>
                            <input type="number" name="adjustment" id="adjustQuantity" class="form-control" min="1" value="1" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">{{ __('messages.reason') }}</label>
                            <textarea name="reason" id="adjustReason" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> {{ __('messages.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-info" id="adjustSubmitBtn">
                        <i class="fas fa-save me-1"></i> {{ __('messages.adjust_stock') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ==================== DELETE MODAL ==================== --}}
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:560px;">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white">
                    <i class="fas fa-exclamation-triangle me-2"></i> {{ __('messages.confirm_delete') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-trash fa-4x text-danger mb-3 d-block"></i>
                <p class="fw-bold">{{ __('messages.are_you_sure') }}</p>
                <p class="text-muted small">{{ __('messages.this_action_cannot_be_undone') }}</p>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> {{ __('messages.cancel') }}
                </button>
                <button type="submit" class="btn btn-danger" id="deleteSubmitBtn" form="deleteForm">
                    <i class="fas fa-trash me-1"></i> {{ __('messages.delete') }}
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- BULK UPLOAD MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="bulkUploadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width:700px;">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title text-white"><i class="fas fa-upload me-2"></i> Bulk Upload Products</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding:20px 25px;">
                <div id="bulkUploadErrors" class="alert alert-danger d-none"></div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Instructions:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Upload a CSV or Excel file with product data</li>
                        <li>Required columns: <strong>name</strong>, <strong>sku</strong></li>
                        <li>Download the template for the correct format</li>
                        <li>Maximum file size: 5MB</li>
                        <li>Supported formats: CSV, XLSX, XLS</li>
                    </ul>
                </div>

                <div class="mb-3">
                    <a href="{{ route('admin.products.bulk-template') }}" class="btn btn-sm btn-outline-success">
                        <i class="fas fa-download me-1"></i> Download Template
                    </a>
                </div>

                <form id="bulkUploadForm" action="{{ route('admin.products.bulk-upload.process') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Upload File <span class="text-danger">*</span></label>
                            <input type="file" name="file" class="form-control" accept=".csv,.xlsx,.xls" required>
                            <small class="text-muted">CSV, XLSX, or XLS files only</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Default Category</label>
                            <select name="category_id" class="form-select">
                                <option value="">No Default Category</option>
                                @foreach($categories ?? [] as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Used if category column is empty</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Default Supplier</label>
                            <select name="supplier_id" class="form-select">
                                <option value="">No Default Supplier</option>
                                @foreach($suppliers ?? [] as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Used if supplier column is empty</small>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Cancel</button>
                <button type="button" class="btn btn-info" id="bulkUploadSubmitBtn"><i class="fas fa-upload me-1"></i> Upload Products</button>
            </div>
        </div>
    </div>
</div>


<style>
.avatar-circle { width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:600; }
.small-box { border-radius:12px; padding:20px; position:relative; overflow:hidden; }
.small-box .inner { color:#fff; }
.small-box .inner h3 { font-size:2rem; font-weight:700; margin:0 0 5px; }
.small-box .inner p { font-size:0.9rem; opacity:0.9; margin:0; }
.small-box .icon { position:absolute; right:15px; top:15px; font-size:2.5rem; opacity:0.25; color:#fff; }
.small-box .small-box-footer { display:block; padding:8px 0 0; color:rgba(255,255,255,0.8); text-decoration:none; font-size:0.85rem; }
</style>

@push('scripts')
<script>
// ====================== TRANSLATIONS ======================
window.trans = {
    loading: @json(__('messages.loading')),
    close: @json(__('messages.close')),
    cancel: @json(__('messages.cancel')),
    save: @json(__('messages.save')),
    update: @json(__('messages.update')),
    delete: @json(__('messages.delete')),
    edit: @json(__('messages.edit')),
    view: @json(__('messages.view')),
    active: @json(__('messages.active')),
    inactive: @json(__('messages.inactive')),
    name: @json(__('messages.name')),
    description: @json(__('messages.description')),
    status: @json(__('messages.status')),
    confirm_delete: @json(__('messages.confirm_delete')),
    are_you_sure: @json(__('messages.are_you_sure')),
    this_action_cannot_be_undone: @json(__('messages.this_action_cannot_be_undone')),
    product_details: @json(__('messages.product_details')),
    edit_product: @json(__('messages.edit_product')),
    adjust_stock: @json(__('messages.adjust_stock')),
    basic_information: @json(__('messages.basic_information')),
    pricing: @json(__('messages.pricing')),
    sku: @json(__('messages.sku')),
    barcode: @json(__('messages.barcode')),
    type: @json(__('messages.type')),
    category: @json(__('messages.category')),
    supplier: @json(__('messages.supplier')),
    quantity: @json(__('messages.quantity')),
    unit: @json(__('messages.unit')),
    selling_price: @json(__('messages.selling_price')),
    cost_price: @json(__('messages.cost_price')),
    purchase_price: @json(__('messages.purchase_price')),
    tax_rate: @json(__('messages.tax_rate')),
    current_quantity: @json(__('messages.current_quantity')),
    new_quantity: @json(__('messages.new_quantity')),
    adjustment_type: @json(__('messages.adjustment_type')),
    add_stock: @json(__('messages.add_stock')),
    remove_stock: @json(__('messages.remove_stock')),
    quantity_to_adjust: @json(__('messages.quantity_to_adjust')),
    reason: @json(__('messages.reason')),
    no_items_selected: @json(__('messages.no_items_selected')),
    delete_selected: @json(__('messages.delete_selected')),
};

$(document).ready(function() {
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    function showErrors(container, errors) {
        container.removeClass('d-none');
        let html = '<ul class="mb-0">';
        $.each(errors, function(key, messages) {
            $.each(messages, function(i, msg) { html += `<li>${msg}</li>`; });
        });
        html += '</ul>';
        container.html(html);
    }

    function clearErrors(container) {
        container.addClass('d-none').empty();
    }

    // ====================== BULK DELETE ======================
    @can('product.delete')
    $('#selectAll').on('change', function() {
        $('.row-checkbox').prop('checked', $(this).prop('checked'));
    });

    $('#bulkDeleteBtn').on('click', function() {
        const selected = $('.row-checkbox:checked');
        if (selected.length === 0) {
            Swal.fire({ icon: 'warning', title: trans.no_items_selected });
            return;
        }

        Swal.fire({
            title: trans.delete_selected,
            text: `You are about to delete ${selected.length} product(s).`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonText: trans.cancel,
            confirmButtonText: 'Yes, delete!'
        }).then((result) => {
            if (result.isConfirmed) {
                const ids = [];
                selected.each(function() { ids.push($(this).val()); });

                $.ajax({
                    url: '{{ route("admin.products.bulk-delete") }}',
                    type: 'POST',
                    data: { product_ids: ids, _token: '{{ csrf_token() }}' },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({ icon: 'success', title: 'Deleted!', timer: 1500 })
                                .then(() => location.reload());
                        } else {
                            Swal.fire({ icon: 'error', title: 'Error', text: response.message });
                        }
                    }
                });
            }
        });
    });
    @endcan

    // ====================== VIEW ======================
    @can('product.view')
    $(document).on('click', '.view-btn', function() {
        const id = $(this).data('id');
        const body = $('#viewModalBody');
        body.html(`<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>`);
        $('#viewModal').modal('show');

        $.get('/admin/products/' + id, function(response) {
            if (!response.success) {
                body.html(`<div class="text-center py-5 text-danger"><h5>Error</h5></div>`);
                return;
            }
            const p = response.product;
            body.html(`
                <div class="row g-3">
                    <div class="col-12">
                        <h5 class="fw-bold">${p.name}</h5>
                        <hr>
                    </div>
                    <div class="col-md-4"><label class="text-muted small fw-bold">${trans.name}</label><p class="fw-bold">${p.name}</p></div>
                    <div class="col-md-4"><label class="text-muted small fw-bold">${trans.sku}</label><p><span class="badge bg-secondary">${p.sku}</span></p></div>
                    <div class="col-md-4"><label class="text-muted small fw-bold">${trans.barcode}</label><p>${p.barcode || 'N/A'}</p></div>
                    <div class="col-md-4"><label class="text-muted small fw-bold">${trans.type}</label><p>${p.type || 'N/A'}</p></div>
                    <div class="col-md-4"><label class="text-muted small fw-bold">${trans.category}</label><p>${p.category?.name || 'N/A'}</p></div>
                    <div class="col-md-4"><label class="text-muted small fw-bold">${trans.supplier}</label><p>${p.supplier?.name || 'N/A'}</p></div>
                    <div class="col-md-4"><label class="text-muted small fw-bold">${trans.quantity}</label><p class="fw-bold">${p.quantity}</p></div>
                    <div class="col-md-4"><label class="text-muted small fw-bold">${trans.selling_price}</label><p class="text-primary fw-bold">${p.selling_price ? parseFloat(p.selling_price).toFixed(2) : 'N/A'}</p></div>
                    <div class="col-md-4"><label class="text-muted small fw-bold">${trans.cost_price}</label><p>${p.cost_price ? parseFloat(p.cost_price).toFixed(2) : 'N/A'}</p></div>
                    ${p.description ? `<div class="col-12"><hr><label class="text-muted small fw-bold">${trans.description}</label><p>${p.description}</p></div>` : ''}
                </div>
            `);
        });
    });
    @endcan

    // ====================== ADJUST STOCK ======================
    @can('stock.create')
    $(document).on('click', '.adjust-btn', function() {
        const id = $(this).data('id');
        $('#adjustForm').attr('action', '/admin/products/' + id + '/adjust');
        const currentQty = parseInt($(this).closest('tr').data('quantity')) || 0;
        $('#currentQuantityDisplay').text(currentQty);
        $('#newQuantityDisplay').text(currentQty);
        $('#adjustType').val('add');
        $('#adjustQuantity').val(1);
        $('#adjustReason').val('');
        clearErrors($('#adjustFormErrors'));
        $('#adjustModal').modal('show');
    });

    $('#adjustType, #adjustQuantity').on('change keyup', function() {
        const current = parseInt($('#currentQuantityDisplay').text()) || 0;
        const adj = parseInt($('#adjustQuantity').val()) || 0;
        const type = $('#adjustType').val();
        const newQty = type === 'add' ? current + adj : current - adj;
        $('#newQuantityDisplay').text(newQty).toggleClass('text-danger', newQty < 0).toggleClass('text-success', newQty >= 0);
    });

    $('#adjustForm').on('submit', function(e) {
        e.preventDefault();
        const btn = $('#adjustSubmitBtn');
        btn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm me-1"></span> ${trans.loading}...`);

        $.post($(this).attr('action'), $(this).serialize())
            .done(function(response) {
                if (response.success) {
                    $('#adjustModal').modal('hide');
                    Swal.fire({ icon: 'success', title: 'Adjusted!', timer: 1500 }).then(() => location.reload());
                } else {
                    btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.adjust_stock}`);
                    Swal.fire({ icon: 'error', title: 'Error', text: response.message });
                }
            })
            .fail(function() {
                btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.adjust_stock}`);
            });
    });
    @endcan

    // ====================== CREATE ======================
    @can('product.create')
    $('#createForm').on('submit', function(e) {
        e.preventDefault();
        const btn = $('#createSubmitBtn');
        const errorContainer = $('#createFormErrors');
        clearErrors(errorContainer);
        btn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm me-1"></span> ${trans.loading}...`);

        $.post($(this).attr('action'), $(this).serialize())
            .done(function(response) {
                if (response.success) {
                    $('#createModal').modal('hide');
                    Swal.fire({ icon: 'success', title: 'Added!', timer: 1500 }).then(() => location.reload());
                } else {
                    btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.save}`);
                    if (response.errors) showErrors(errorContainer, response.errors);
                }
            })
            .fail(function(xhr) {
                btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.save}`);
                if (xhr.status === 422) showErrors(errorContainer, xhr.responseJSON.errors);
            });
    });
    @endcan

    // ====================== EDIT PRODUCT ======================
@can('product.edit')
$(document).on('click', '.edit-btn', function() {
    const id = $(this).data('id');
    const body = $('#editModalBody');
    const form = $('#editForm');

    form.attr('action', '/admin/products/' + id);

    body.html(`
        <div id="editFormErrors" class="alert alert-danger d-none"></div>
        <div class="text-center py-5">
            <div class="spinner-border text-warning"></div>
            <p class="mt-2">${trans.loading}...</p>
        </div>
    `);

    $('#editModal').modal('show');

    $.ajax({
        url: '/admin/products/' + id + '/edit',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (!response.success) {
                body.html(`
                    <div class="text-center py-5 text-danger">
                        <i class="fas fa-exclamation-circle fa-4x mb-3"></i>
                        <h5>Error</h5>
                        <p>${response.message || 'Failed to load product data'}</p>
                    </div>
                `);
                return;
            }

            const r = response.product;

            // Build category options
            let categoryOptions = `<option value="">${trans.category}</option>`;
            @foreach($categories ?? [] as $category)
                categoryOptions += `<option value="{{ $category->id }}" ${r.category_id == {{ $category->id }} ? 'selected' : ''}>{{ $category->name }}</option>`;
            @endforeach

            // Build supplier options
            let supplierOptions = `<option value="">${trans.supplier}</option>`;
            @foreach($suppliers ?? [] as $supplier)
                supplierOptions += `<option value="{{ $supplier->id }}" ${r.supplier_id == {{ $supplier->id }} ? 'selected' : ''}>{{ $supplier->name }}</option>`;
            @endforeach

            body.html(`
                <div id="editFormErrors" class="alert alert-danger d-none"></div>

                <!-- Basic Information -->
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-info-circle text-primary me-2"></i>${trans.basic_information || 'Basic Information'}</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">${trans.name} <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="${r.name || ''}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">${trans.sku} <span class="text-danger">*</span></label>
                                <input type="text" name="sku" class="form-control" value="${r.sku || ''}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">${trans.barcode}</label>
                                <input type="text" name="barcode" class="form-control" value="${r.barcode || ''}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">${trans.type}</label>
                                <input type="text" name="type" class="form-control" value="${r.type || ''}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">${trans.category} <span class="text-danger">*</span></label>
                                <select name="category_id" class="form-select" required>
                                    ${categoryOptions}
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">${trans.supplier}</label>
                                <select name="supplier_id" class="form-select">
                                    ${supplierOptions}
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">${trans.quantity} <span class="text-danger">*</span></label>
                                <input type="number" name="quantity" class="form-control" value="${r.quantity || 0}" min="0" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">${trans.unit || 'Unit'}</label>
                                <input type="text" name="unit" class="form-control" value="${r.unit || ''}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">${trans.status} <span class="text-danger">*</span></label>
                                <select name="status" class="form-select" required>
                                    <option value="available" ${r.status === 'available' ? 'selected' : ''}>${trans.active || 'Available'}</option>
                                    <option value="assigned" ${r.status === 'assigned' ? 'selected' : ''}>Assigned</option>
                                    <option value="under_repair" ${r.status === 'under_repair' ? 'selected' : ''}>Under Repair</option>
                                    <option value="retired" ${r.status === 'retired' ? 'selected' : ''}>Retired</option>
                                    <option value="expired" ${r.status === 'expired' ? 'selected' : ''}>Expired</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pricing -->
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-money-bill-wave text-success me-2"></i>${trans.pricing || 'Pricing'}</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-bold">${trans.selling_price}</label>
                                <input type="number" name="selling_price" class="form-control" step="0.01" min="0" value="${r.selling_price || ''}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">${trans.cost_price}</label>
                                <input type="number" name="cost_price" class="form-control" step="0.01" min="0" value="${r.cost_price || ''}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">${trans.purchase_price || 'Purchase Price'}</label>
                                <input type="number" name="purchase_price" class="form-control" step="0.01" min="0" value="${r.purchase_price || ''}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">${trans.tax_rate || 'Tax Rate'} (%)</label>
                                <input type="number" name="tax_rate" class="form-control" step="0.01" min="0" max="100" value="${r.tax_rate || ''}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-align-left me-2"></i>${trans.description}</h6>
                    </div>
                    <div class="card-body">
                        <textarea name="description" class="form-control" rows="3">${r.description || ''}</textarea>
                    </div>
                </div>
            `);
        },
        error: function(xhr) {
            body.html(`
                <div class="text-center py-5 text-danger">
                    <i class="fas fa-exclamation-circle fa-4x mb-3"></i>
                    <h5>Failed to load product data</h5>
                    <p>${xhr.status} - ${xhr.statusText}</p>
                </div>
            `);
        }
    });
});

// Update form submit
$('#editForm').on('submit', function(e) {
    e.preventDefault();
    const form = $(this);
    const btn = $('#editSubmitBtn');
    const errorContainer = $('#editFormErrors');

    clearErrors(errorContainer);
    btn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm me-1"></span> ${trans.loading}...`);

    $.ajax({
        url: form.attr('action'),
        type: 'POST',
        data: form.serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#editModal').modal('hide');
                Swal.fire({ icon: 'success', title: 'Updated!', text: response.message, timer: 1500 })
                    .then(() => location.reload());
            } else {
                btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.update}`);
                if (response.errors) showErrors(errorContainer, response.errors);
                else Swal.fire({ icon: 'error', title: 'Error', text: response.message });
            }
        },
        error: function(xhr) {
            btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.update}`);
            if (xhr.status === 422) {
                showErrors(errorContainer, xhr.responseJSON.errors);
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to update product' });
            }
        }
    });
});
@endcan

    // ====================== DELETE ======================
    @can('product.delete')
    $(document).on('click', '.delete-btn', function() {
        const id = $(this).data('id');
        $('#deleteForm').attr('action', '/admin/products/' + id);

        Swal.fire({
            title: trans.confirm_delete,
            text: trans.are_you_sure,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonText: trans.cancel,
            confirmButtonText: 'Yes, delete!'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#deleteModal').modal('show');
            }
        });
    });

    $('#deleteForm').on('submit', function(e) {
        e.preventDefault();
        const btn = $('#deleteSubmitBtn');
        btn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm me-1"></span> ${trans.loading}...`);

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
            success: function(response) {
                if (response.success) {
                    $('#deleteModal').modal('hide');
                    Swal.fire({ icon: 'success', title: 'Deleted!', timer: 1500 }).then(() => location.reload());
                } else {
                    btn.prop('disabled', false).html(`<i class="fas fa-trash me-1"></i> ${trans.delete}`);
                    Swal.fire({ icon: 'error', title: 'Error', text: response.message });
                }
            },
            error: function() {
                btn.prop('disabled', false).html(`<i class="fas fa-trash me-1"></i> ${trans.delete}`);
            }
        });
    });
    @endcan

    // ====================== SEARCH & FILTER ======================
    $('#searchBtn').on('click', filterTable);
    $('#searchInput').on('keypress', function(e) { if (e.which === 13) filterTable(); });
    $('#typeFilter, #statusFilter, #categoryFilter, #supplierFilter').on('change', filterTable);

    function filterTable() {
        const search = $('#searchInput').val().toLowerCase();
        const type = $('#typeFilter').val().toLowerCase();
        const status = $('#statusFilter').val();
        const category = $('#categoryFilter').val();
        const supplier = $('#supplierFilter').val();

        $('#tableBody tr').each(function() {
            const row = $(this);
            let show = true;
            if (search && !row.text().toLowerCase().includes(search)) show = false;
            if (type && !(row.data('type') || '').toLowerCase().includes(type)) show = false;
            if (status && row.data('status') !== status) show = false;
            if (category && row.data('category') != category) show = false;
            if (supplier && row.data('supplier') != supplier) show = false;
            row.toggle(show);
        });
    }

    // Modal cleanup
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