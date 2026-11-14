@extends('layouts.admin')

@section('title', 'Inspections')
@section('page-title', 'Inspection Management')

@section('admin-content')
<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ $stats['total'] ?? $inspections->total() ?? 0 }}</h3>
                <p>Total Inspections</p>
            </div>
            <div class="icon">
                <i class="fas fa-clipboard-check"></i>
            </div>
            <a href="{{ route('admin.inspections.index') }}" class="small-box-footer">
                View All <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $stats['passed'] ?? 0 }}</h3>
                <p>Passed</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <a href="{{ route('admin.inspections.index', ['result' => 'passed']) }}" class="small-box-footer">
                View Passed <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $stats['failed'] ?? 0 }}</h3>
                <p>Failed</p>
            </div>
            <div class="icon">
                <i class="fas fa-times-circle"></i>
            </div>
            <a href="{{ route('admin.inspections.index', ['result' => 'failed']) }}" class="small-box-footer">
                View Failed <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $stats['partial'] ?? 0 }}</h3>
                <p>Partial</p>
            </div>
            <div class="icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <a href="{{ route('admin.inspections.index', ['result' => 'partial']) }}" class="small-box-footer">
                View Partial <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Main Card -->
<div class="card">
    <div class="card-header">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <h5 class="card-title mb-0">
                <i class="fas fa-clipboard-check text-primary me-2"></i>
                All Inspections
                <span class="badge bg-primary ms-1">{{ $inspections->total() }}</span>
            </h5>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <div class="input-group input-group-sm" style="width: 200px;">
                <input type="text" id="searchInput" class="form-control" placeholder="Search...">
                <button class="btn btn-secondary" type="button" id="searchBtn">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            <select id="resultFilter" class="form-select form-select-sm" style="width: 120px;">
                <option value="">All Results</option>
                <option value="passed">Passed</option>
                <option value="failed">Failed</option>
                <option value="partial">Partial</option>
            </select>
            <select id="returnedFilter" class="form-select form-select-sm" style="width: 120px;">
                <option value="">All Status</option>
                <option value="no">Not Returned</option>
                <option value="yes">Returned</option>
            </select>
            @can('inspection.create')
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fas fa-plus-circle me-1"></i> New Inspection
            </button>
            @endcan
            @can('inspection.view')
            <a href="{{ route('admin.inspections.export') }}" class="btn btn-success btn-sm">
                <i class="fas fa-file-excel me-1"></i> Export
            </a>
            @endcan
            @can('inspection.delete')
            <button class="btn btn-danger btn-sm" id="bulkDeleteBtn">
                <i class="fas fa-trash me-1"></i> Bulk Delete
            </button>
            @endcan
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        @can('inspection.delete')
                        <th style="width: 35px;">
                            <input type="checkbox" id="selectAll">
                        </th>
                        @endcan
                        <th style="width: 35px;">#</th>
                        <th style="min-width: 140px;">Product</th>
                        <th style="min-width: 140px;">Request</th>
                        <th>Inspected By</th>
                        <th style="width: 100px;">Date</th>
                        <th style="width: 80px;">Condition</th>
                        <th style="width: 80px;">Result</th>
                        <th style="width: 80px;">Returned</th>
                        <th style="width: 180px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($inspections as $inspection)
                    <tr data-id="{{ $inspection->id }}" data-result="{{ $inspection->result }}" data-returned="{{ $inspection->is_returned ? 'yes' : 'no' }}">
                        @can('inspection.delete')
                        <td>
                            <input type="checkbox" class="row-checkbox" value="{{ $inspection->id }}">
                        </td>
                        @endcan
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle bg-primary text-white me-2">
                                    <i class="fas fa-box"></i>
                                </div>
                                <div>
                                    <div class="fw-bold small">{{ Str::limit($inspection->product->name ?? 'N/A', 20) }}</div>
                                    <small class="text-muted">{{ $inspection->product->sku ?? 'N/A' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <a href="{{ route('admin.asset-requests.show', $inspection->asset_request_id) }}" class="text-decoration-none">
                                #{{ $inspection->asset_request_id }}
                            </a>
                        </td>
                        <td>
                            <small>{{ $inspection->inspectedBy->name ?? 'N/A' }}</small>
                        </td>
                        <td>
                            <small>{{ $inspection->inspection_date ? $inspection->inspection_date->format('M d, Y') : 'N/A' }}</small>
                        </td>
                        <td>
                            @php
                                $conditionColors = [
                                    'new' => 'success',
                                    'excellent' => 'success',
                                    'good' => 'info',
                                    'fair' => 'warning text-dark',
                                    'poor' => 'danger',
                                    'damaged' => 'danger'
                                ];
                            @endphp
                            <span class="badge bg-{{ $conditionColors[$inspection->physical_condition] ?? 'secondary' }}">
                                {{ ucfirst($inspection->physical_condition) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $inspection->result == 'passed' ? 'success' : ($inspection->result == 'failed' ? 'danger' : 'warning text-dark') }}">
                                {{ ucfirst($inspection->result) }}
                            </span>
                        </td>
                        <td>
                            @if($inspection->is_returned)
                                <span class="badge bg-danger">Returned</span>
                            @else
                                <span class="badge bg-secondary">Active</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                @can('inspection.view')
                                <button class="btn btn-outline-primary btn-sm view-btn" data-id="{{ $inspection->id }}" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @endcan
                                @can('inspection.edit')
                                <button class="btn btn-outline-warning btn-sm edit-btn" data-id="{{ $inspection->id }}" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @endcan
                                @if(!$inspection->is_returned)
                                    @can('inspection.pass')
                                    <button class="btn btn-outline-success btn-sm pass-btn" data-id="{{ $inspection->id }}" title="Pass">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    @endcan
                                    @can('inspection.fail')
                                    <button class="btn btn-outline-danger btn-sm fail-btn" data-id="{{ $inspection->id }}" title="Fail">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    @endcan
                                    @can('inspection.return_to_supplier')
                                    <button class="btn btn-outline-info btn-sm return-btn" data-id="{{ $inspection->id }}" title="Return to Supplier">
                                        <i class="fas fa-undo-alt"></i>
                                    </button>
                                    @endcan
                                @endif
                                @can('inspection.delete')
                                <button class="btn btn-outline-danger btn-sm delete-btn" data-id="{{ $inspection->id }}" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ auth()->user()->can('inspection.delete') ? 10 : 9 }}">
                            <div class="text-center py-5">
                                <i class="fas fa-clipboard-check fa-4x text-muted mb-3 d-block"></i>
                                <h5 class="fw-bold">No Inspections Found</h5>
                                <p class="text-muted">Click "New Inspection" to create your first inspection.</p>
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
                Showing {{ $inspections->firstItem() ?? 0 }} to {{ $inspections->lastItem() ?? 0 }} of {{ $inspections->total() }} entries
            </span>
        </div>
        <div>
            {{ $inspections->links() }}
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- CREATE MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle text-primary me-2"></i> New Inspection
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createForm" action="{{ route('admin.inspections.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div id="createFormErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Asset Request <span class="text-danger">*</span></label>
                            <select name="asset_request_id" class="form-select" required>
                                <option value="">Select Request</option>
                                @foreach($assetRequests ?? [] as $assetRequest)
                                    <option value="{{ $assetRequest->id }}">#{{ $assetRequest->id }} - {{ Str::limit($assetRequest->title, 40) }}</option>
                                @endforeach
                            </select>
                        </div>
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
                            <label class="form-label fw-bold">Inspection Date <span class="text-danger">*</span></label>
                            <input type="date" name="inspection_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Result <span class="text-danger">*</span></label>
                            <select name="result" class="form-select" required>
                                <option value="passed">Passed</option>
                                <option value="failed">Failed</option>
                                <option value="partial">Partial</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Physical Condition <span class="text-danger">*</span></label>
                            <select name="physical_condition" class="form-select" required>
                                <option value="new">New</option>
                                <option value="excellent">Excellent</option>
                                <option value="good">Good</option>
                                <option value="fair">Fair</option>
                                <option value="poor">Poor</option>
                                <option value="damaged">Damaged</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Functional Condition <span class="text-danger">*</span></label>
                            <select name="functional_condition" class="form-select" required>
                                <option value="working">Working</option>
                                <option value="minor_issues">Minor Issues</option>
                                <option value="major_issues">Major Issues</option>
                                <option value="non_functional">Non Functional</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Issues Found</label>
                            <textarea name="issues_found" class="form-control" rows="2" placeholder="Describe any issues found..."></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Recommendations</label>
                            <textarea name="recommendations" class="form-control" rows="2" placeholder="Recommendations..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="createSubmitBtn">
                        <i class="fas fa-save me-1"></i> Create Inspection
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- VIEW MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-eye text-info me-2"></i> Inspection Details
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
<!-- EDIT MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit text-warning me-2"></i> Edit Inspection
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body" id="editModalBody">
                    <div id="editFormErrors" class="alert alert-danger d-none"></div>
                    <div class="text-center py-5">
                        <div class="spinner-border text-warning" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-warning" id="editSubmitBtn">
                        <i class="fas fa-save me-1"></i> Update Inspection
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- PASS MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="passModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-success">
                    <i class="fas fa-check-circle me-2"></i> Pass Inspection
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-check-circle fa-4x text-success mb-3 d-block"></i>
                <p class="fw-bold">Are you sure you want to mark this inspection as passed?</p>
                <p class="text-muted small">This will assign the asset to the user.</p>
                <form id="passForm" method="POST">
                    @csrf
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <button type="submit" class="btn btn-success" id="passSubmitBtn" form="passForm">
                    <i class="fas fa-check me-1"></i> Pass
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- FAIL MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="failModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">
                    <i class="fas fa-times-circle me-2"></i> Fail Inspection
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="failForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div id="failFormErrors" class="alert alert-danger d-none"></div>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        This will mark the inspection as failed.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-danger">Reason <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="3" required placeholder="Please provide a reason for failure..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-danger" id="failSubmitBtn">
                        <i class="fas fa-times me-1"></i> Fail
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- RETURN TO SUPPLIER MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="returnModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-info">
                    <i class="fas fa-undo-alt me-2"></i> Return to Supplier
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="returnForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div id="returnFormErrors" class="alert alert-danger d-none"></div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        This will mark the product as returned to supplier.
                    </div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Supplier <span class="text-danger">*</span></label>
                            <select name="supplier_id" class="form-select" required>
                                <option value="">Select Supplier</option>
                                @foreach($suppliers ?? [] as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Return Reason <span class="text-danger">*</span></label>
                            <textarea name="return_reason" class="form-control" rows="3" required placeholder="Reason for returning to supplier..."></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Return Date <span class="text-danger">*</span></label>
                            <input type="date" name="return_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-info" id="returnSubmitBtn">
                        <i class="fas fa-undo-alt me-1"></i> Return to Supplier
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- DELETE CONFIRM MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i> Confirm Delete
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-trash fa-4x text-danger mb-3 d-block"></i>
                <p class="fw-bold">Are you sure you want to delete this inspection?</p>
                <p class="text-muted small">This action cannot be undone.</p>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <button type="submit" class="btn btn-danger" id="deleteSubmitBtn" form="deleteForm">
                    <i class="fas fa-trash me-1"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-circle {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 600;
    flex-shrink: 0;
}
.small-box {
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 0;
    position: relative;
    overflow: hidden;
    transition: transform 0.15s ease;
}
.small-box:hover {
    transform: translateY(-2px);
}
.small-box .inner { color: #fff; }
.small-box .inner h3 { font-size: 2rem; font-weight: 700; margin: 0 0 5px; }
.small-box .inner p { font-size: 0.9rem; opacity: 0.9; margin: 0; }
.small-box .icon {
    position: absolute;
    right: 15px;
    top: 15px;
    font-size: 2.5rem;
    opacity: 0.25;
    color: #fff;
}
.small-box .small-box-footer {
    display: block;
    padding: 8px 0 0;
    color: rgba(255,255,255,0.8);
    text-decoration: none;
    font-size: 0.85rem;
    transition: all 0.15s ease;
}
.small-box .small-box-footer:hover { color: #fff; }
.small-box .small-box-footer i { transition: transform 0.15s ease; }
.small-box:hover .small-box-footer i { transform: translateX(4px); }
</style>

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
    // HELPER: Show Errors
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

    // ==========================================
    // BULK SELECT
    // ==========================================
    @can('inspection.delete')
    $('#selectAll').on('change', function() {
        $('.row-checkbox').prop('checked', $(this).prop('checked'));
    });

    $('#bulkDeleteBtn').on('click', function() {
        var selected = $('.row-checkbox:checked');
        if (selected.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Items Selected',
                text: 'Please select at least one inspection to delete.'
            });
            return;
        }

        Swal.fire({
            title: 'Delete Selected Inspections?',
            text: 'You are about to delete ' + selected.length + ' inspection(s). This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete them!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                var ids = [];
                selected.each(function() {
                    ids.push($(this).val());
                });

                Swal.fire({
                    title: 'Deleting...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: '{{ route("admin.inspections.bulk-delete") }}',
                    type: 'POST',
                    data: {
                        inspection_ids: ids
                    },
                    dataType: 'json',
                    success: function(r) {
                        if (r.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: r.message,
                                timer: 2000,
                                showConfirmButton: true
                            }).then(() => { window.location.reload(); });
                        }
                    },
                    error: function(xhr) {
                        Swal.close();
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Failed to delete selected inspections.'
                        });
                    }
                });
            }
        });
    });
    @endcan

    // ==========================================
    // VIEW
    // ==========================================
    $(document).on('click', '.view-btn', function() {
        var id = $(this).data('id');
        var body = $('#viewModalBody');

        body.html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `);
        $('#viewModal').modal('show');

        $.ajax({
            url: '/admin/inspections/' + id,
            type: 'GET',
            dataType: 'json',
            success: function(r) {
                var inspection = r.inspection || r;
                var workflows = r.workflows || [];

                var html = `
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Product</label>
                            <p class="fw-bold mb-0">${inspection.product ? inspection.product.name : 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">SKU</label>
                            <p class="mb-0">${inspection.product ? inspection.product.sku : 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Request ID</label>
                            <p class="mb-0">#${inspection.asset_request_id}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Inspected By</label>
                            <p class="mb-0">${inspection.inspected_by ? inspection.inspected_by.name : 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Inspection Date</label>
                            <p class="mb-0">${new Date(inspection.inspection_date).toLocaleDateString()}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Result</label>
                            <p class="mb-0"><span class="badge bg-${inspection.result == 'passed' ? 'success' : (inspection.result == 'failed' ? 'danger' : 'warning text-dark')}">${inspection.result}</span></p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Physical Condition</label>
                            <p class="mb-0"><span class="badge bg-${inspection.physical_condition == 'new' ? 'success' : (inspection.physical_condition == 'damaged' ? 'danger' : 'warning text-dark')}">${inspection.physical_condition}</span></p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Functional Condition</label>
                            <p class="mb-0">${inspection.functional_condition ? inspection.functional_condition.replace(/_/g, ' ') : 'N/A'}</p>
                        </div>
                        ${inspection.issues_found ? `
                        <div class="col-12">
                            <label class="text-muted small fw-bold text-danger">Issues Found</label>
                            <p class="mb-0">${inspection.issues_found}</p>
                        </div>` : ''}
                        ${inspection.recommendations ? `
                        <div class="col-12">
                            <label class="text-muted small fw-bold text-primary">Recommendations</label>
                            <p class="mb-0">${inspection.recommendations}</p>
                        </div>` : ''}
                        ${inspection.is_returned ? `
                        <div class="col-12">
                            <label class="text-muted small fw-bold text-danger">Returned to Supplier</label>
                            <p class="mb-0">${inspection.returned_to_supplier ? inspection.returned_to_supplier.name : 'N/A'} - ${inspection.return_reason || 'No reason provided'}</p>
                            <p class="mb-0 small text-muted">Return Date: ${new Date(inspection.return_date).toLocaleDateString()}</p>
                        </div>` : ''}
                        <div class="col-12">
                            <label class="text-muted small fw-bold">Created</label>
                            <p class="mb-0">${new Date(inspection.created_at).toLocaleString()}</p>
                        </div>
                        ${workflows.length > 0 ? `
                        <div class="col-12">
                            <hr>
                            <h6 class="fw-bold">Workflow History</h6>
                            ${workflows.map(function(w) {
                                return `<div class="d-flex gap-2 mb-2">
                                    <small class="text-muted">${new Date(w.created_at).toLocaleString()}</small>
                                    <span class="badge bg-secondary">${w.action}</span>
                                    <span class="small">${w.comments || ''}</span>
                                </div>`;
                            }).join('')}
                        </div>` : ''}
                    </div>
                `;
                body.html(html);
            },
            error: function(xhr) {
                body.html(`
                    <div class="text-center py-5 text-danger">
                        <i class="fas fa-exclamation-circle fa-4x mb-3 d-block"></i>
                        <h5>Failed to load inspection details</h5>
                        <p>${xhr.status} - ${xhr.statusText}</p>
                    </div>
                `);
            }
        });
    });

    // ==========================================
    // EDIT
    // ==========================================
    $(document).on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        var body = $('#editModalBody');
        var form = $('#editForm');

        form.attr('action', '/admin/inspections/' + id);
        body.html(`
            <div id="editFormErrors" class="alert alert-danger d-none"></div>
            <div class="text-center py-5">
                <div class="spinner-border text-warning" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `);
        $('#editModal').modal('show');

        $.ajax({
            url: '/admin/inspections/' + id + '/edit',
            type: 'GET',
            dataType: 'json',
            success: function(r) {
                body.html(`
                    <div id="editFormErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Product <span class="text-danger">*</span></label>
                            <select name="product_id" class="form-select" required>
                                <option value="">Select Product</option>
                                @foreach($products ?? [] as $product)
                                    <option value="{{ $product->id }}" ${r.product_id == {{ $product->id }} ? 'selected' : ''}>{{ $product->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Inspection Date <span class="text-danger">*</span></label>
                            <input type="date" name="inspection_date" class="form-control" value="${r.inspection_date ? new Date(r.inspection_date).toISOString().split('T')[0] : ''}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Result <span class="text-danger">*</span></label>
                            <select name="result" class="form-select" required>
                                <option value="passed" ${r.result == 'passed' ? 'selected' : ''}>Passed</option>
                                <option value="failed" ${r.result == 'failed' ? 'selected' : ''}>Failed</option>
                                <option value="partial" ${r.result == 'partial' ? 'selected' : ''}>Partial</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Physical Condition <span class="text-danger">*</span></label>
                            <select name="physical_condition" class="form-select" required>
                                <option value="new" ${r.physical_condition == 'new' ? 'selected' : ''}>New</option>
                                <option value="excellent" ${r.physical_condition == 'excellent' ? 'selected' : ''}>Excellent</option>
                                <option value="good" ${r.physical_condition == 'good' ? 'selected' : ''}>Good</option>
                                <option value="fair" ${r.physical_condition == 'fair' ? 'selected' : ''}>Fair</option>
                                <option value="poor" ${r.physical_condition == 'poor' ? 'selected' : ''}>Poor</option>
                                <option value="damaged" ${r.physical_condition == 'damaged' ? 'selected' : ''}>Damaged</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Functional Condition <span class="text-danger">*</span></label>
                            <select name="functional_condition" class="form-select" required>
                                <option value="working" ${r.functional_condition == 'working' ? 'selected' : ''}>Working</option>
                                <option value="minor_issues" ${r.functional_condition == 'minor_issues' ? 'selected' : ''}>Minor Issues</option>
                                <option value="major_issues" ${r.functional_condition == 'major_issues' ? 'selected' : ''}>Major Issues</option>
                                <option value="non_functional" ${r.functional_condition == 'non_functional' ? 'selected' : ''}>Non Functional</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Issues Found</label>
                            <textarea name="issues_found" class="form-control" rows="2">${r.issues_found || ''}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Recommendations</label>
                            <textarea name="recommendations" class="form-control" rows="2">${r.recommendations || ''}</textarea>
                        </div>
                    </div>
                `);
            },
            error: function(xhr) {
                body.html(`
                    <div id="editFormErrors" class="alert alert-danger d-none"></div>
                    <div class="text-center py-5 text-danger">
                        <i class="fas fa-exclamation-circle fa-4x mb-3 d-block"></i>
                        <h5>Failed to load inspection data</h5>
                        <p>${xhr.status} - ${xhr.statusText}</p>
                    </div>
                `);
            }
        });
    });

    // ==========================================
    // CREATE
    // ==========================================
    $('#createForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        var data = form.serialize();
        var btn = $('#createSubmitBtn');
        var errorContainer = $('#createFormErrors');

        clearErrors(errorContainer);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Creating...');

        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(r) {
                if (r.success) {
                    $('#createModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Created!',
                        text: r.message,
                        timer: 2000,
                        showConfirmButton: true
                    }).then(() => { window.location.reload(); });
                } else {
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Create Inspection');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: r.message || 'Something went wrong.',
                    });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Create Inspection');
                if (xhr.status === 422) {
                    showErrors(errorContainer, xhr.responseJSON.errors);
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
    // UPDATE
    // ==========================================
    $('#editForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        var data = form.serialize();
        var btn = $('#editSubmitBtn');
        var errorContainer = $('#editFormErrors');

        clearErrors(errorContainer);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Updating...');

        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(r) {
                if (r.success) {
                    $('#editModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated!',
                        text: r.message,
                        timer: 2000,
                        showConfirmButton: true
                    }).then(() => { window.location.reload(); });
                } else {
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Update Inspection');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: r.message || 'Something went wrong.',
                    });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Update Inspection');
                if (xhr.status === 422) {
                    showErrors(errorContainer, xhr.responseJSON.errors);
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
    // PASS
    // ==========================================
    $(document).on('click', '.pass-btn', function() {
        var id = $(this).data('id');
        var form = $('#passForm');
        form.attr('action', '/admin/inspections/' + id + '/pass');
        $('#passModal').modal('show');
    });

    $('#passForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        var btn = $('#passSubmitBtn');

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Processing...');

        $.ajax({
            url: url,
            type: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            dataType: 'json',
            success: function(r) {
                if (r.success) {
                    $('#passModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Passed!',
                        text: r.message,
                        timer: 2000,
                        showConfirmButton: true
                    }).then(() => { window.location.reload(); });
                } else {
                    btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i> Pass');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: r.message || 'Failed to pass inspection.',
                    });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i> Pass');
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to pass inspection.'
                });
            }
        });
    });

    // ==========================================
    // FAIL
    // ==========================================
    $(document).on('click', '.fail-btn', function() {
        var id = $(this).data('id');
        var form = $('#failForm');
        form.attr('action', '/admin/inspections/' + id + '/fail');
        clearErrors($('#failFormErrors'));
        $('#failModal').modal('show');
    });

    $('#failForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        var data = form.serialize();
        var btn = $('#failSubmitBtn');
        var errorContainer = $('#failFormErrors');

        clearErrors(errorContainer);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Processing...');

        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(r) {
                if (r.success) {
                    $('#failModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Failed!',
                        text: r.message,
                        timer: 2000,
                        showConfirmButton: true
                    }).then(() => { window.location.reload(); });
                } else {
                    btn.prop('disabled', false).html('<i class="fas fa-times me-1"></i> Fail');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: r.message || 'Failed to fail inspection.',
                    });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-times me-1"></i> Fail');
                if (xhr.status === 422) {
                    showErrors(errorContainer, xhr.responseJSON.errors);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to fail inspection.'
                    });
                }
            }
        });
    });

    // ==========================================
    // RETURN TO SUPPLIER
    // ==========================================
    $(document).on('click', '.return-btn', function() {
        var id = $(this).data('id');
        var form = $('#returnForm');
        form.attr('action', '/admin/inspections/' + id + '/return-supplier');
        clearErrors($('#returnFormErrors'));
        $('#returnModal').modal('show');
    });

    $('#returnForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        var data = form.serialize();
        var btn = $('#returnSubmitBtn');
        var errorContainer = $('#returnFormErrors');

        clearErrors(errorContainer);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Processing...');

        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(r) {
                if (r.success) {
                    $('#returnModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Returned!',
                        text: r.message,
                        timer: 2000,
                        showConfirmButton: true
                    }).then(() => { window.location.reload(); });
                } else {
                    btn.prop('disabled', false).html('<i class="fas fa-undo-alt me-1"></i> Return to Supplier');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: r.message || 'Failed to return to supplier.',
                    });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-undo-alt me-1"></i> Return to Supplier');
                if (xhr.status === 422) {
                    showErrors(errorContainer, xhr.responseJSON.errors);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to return to supplier.'
                    });
                }
            }
        });
    });

    // ==========================================
    // DELETE
    // ==========================================
    $(document).on('click', '.delete-btn', function() {
        var id = $(this).data('id');
        var form = $('#deleteForm');
        form.attr('action', '/admin/inspections/' + id);

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
            if (result.isConfirmed) {
                $('#deleteModal').modal('show');
            }
        });
    });

    $('#deleteForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');

        Swal.fire({
            title: 'Deleting...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        $.ajax({
            url: url,
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
            dataType: 'json',
            success: function(r) {
                if (r.success) {
                    $('#deleteModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: r.message,
                        timer: 2000,
                        showConfirmButton: true
                    }).then(() => { window.location.reload(); });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: r.message || 'Failed to delete.',
                    });
                }
            },
            error: function(xhr) {
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to delete inspection.'
                });
            }
        });
    });

    // ==========================================
    // SEARCH & FILTER
    // ==========================================
    $('#searchBtn').on('click', function() { filterTable(); });
    $('#searchInput').on('keypress', function(e) {
        if (e.which === 13) filterTable();
    });
    $('#resultFilter').on('change', function() { filterTable(); });
    $('#returnedFilter').on('change', function() { filterTable(); });

    function filterTable() {
        var search = $('#searchInput').val().toLowerCase();
        var result = $('#resultFilter').val();
        var returned = $('#returnedFilter').val();

        $('#tableBody tr').each(function() {
            var row = $(this);
            var text = row.text().toLowerCase();
            var rowResult = row.data('result');
            var rowReturned = row.data('returned');

            var show = true;
            if (search && !text.includes(search)) show = false;
            if (result && rowResult !== result) show = false;
            if (returned && rowReturned !== returned) show = false;

            row.toggle(show);
        });
    }

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