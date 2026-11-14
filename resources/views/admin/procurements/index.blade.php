@extends('layouts.admin')

@section('title', 'Procurements')
@section('page-title', 'Procurement Management')

@section('admin-content')
<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ $stats['total'] ?? $procurements->total() ?? 0 }}</h3>
                <p>Total Procurements</p>
            </div>
            <div class="icon">
                <i class="fas fa-file-invoice"></i>
            </div>
            <a href="{{ route('admin.procurements.index') }}" class="small-box-footer">
                View All <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $stats['pending'] ?? 0 }}</h3>
                <p>Pending</p>
            </div>
            <div class="icon">
                <i class="fas fa-clock"></i>
            </div>
            <a href="{{ route('admin.procurements.index', ['status' => 'pending']) }}" class="small-box-footer">
                View Pending <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $stats['delivered'] ?? 0 }}</h3>
                <p>Delivered</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <a href="{{ route('admin.procurements.index', ['status' => 'delivered']) }}" class="small-box-footer">
                View Delivered <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $stats['returned'] ?? 0 }}</h3>
                <p>Returned</p>
            </div>
            <div class="icon">
                <i class="fas fa-undo-alt"></i>
            </div>
            <a href="{{ route('admin.procurements.index', ['status' => 'returned_to_supplier']) }}" class="small-box-footer">
                View Returned <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Main Card -->
<div class="card">
    <div class="card-header">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <h5 class="card-title mb-0">
                <i class="fas fa-file-invoice text-primary me-2"></i>
                All Procurements
                <span class="badge bg-primary ms-1">{{ $procurements->total() }}</span>
            </h5>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <div class="input-group input-group-sm" style="width: 200px;">
                <input type="text" id="searchInput" class="form-control" placeholder="Search...">
                <button class="btn btn-secondary" type="button" id="searchBtn">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            <select id="statusFilter" class="form-select form-select-sm" style="width: 130px;">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="ordered">Ordered</option>
                <option value="shipped">Shipped</option>
                <option value="delivered">Delivered</option>
                <option value="partial_delivery">Partial Delivery</option>
                <option value="returned_to_supplier">Returned</option>
            </select>
            <select id="paymentFilter" class="form-select form-select-sm" style="width: 130px;">
                <option value="">All Payment</option>
                <option value="pending">Pending</option>
                <option value="paid">Paid</option>
                <option value="partial">Partial</option>
                <option value="cancelled">Cancelled</option>
            </select>
            @can('procurement.create')
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fas fa-plus-circle me-1"></i> New Procurement
            </button>
            @endcan
            @can('procurement.view')
            <a href="{{ route('admin.procurements.export') }}" class="btn btn-success btn-sm">
                <i class="fas fa-file-excel me-1"></i> Export
            </a>
            @endcan
            @can('procurement.delete')
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
                        @can('procurement.delete')
                        <th style="width: 35px;">
                            <input type="checkbox" id="selectAll">
                        </th>
                        @endcan
                        <th style="width: 35px;">#</th>
                        <th style="min-width: 140px;">Request</th>
                        <th style="min-width: 140px;">Supplier</th>
                        <th style="width: 100px;">Order Date</th>
                        <th style="width: 100px;">Expected Delivery</th>
                        <th style="width: 100px;">Total Cost</th>
                        <th style="width: 100px;">Payment Status</th>
                        <th style="width: 110px;">Delivery Status</th>
                        <th style="width: 200px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($procurements as $procurement)
                    <tr data-id="{{ $procurement->id }}" data-status="{{ $procurement->delivery_status }}" data-payment="{{ $procurement->payment_status }}">
                        @can('procurement.delete')
                        <td>
                            <input type="checkbox" class="row-checkbox" value="{{ $procurement->id }}">
                        </td>
                        @endcan
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle bg-primary text-white me-2">
                                    <i class="fas fa-clipboard"></i>
                                </div>
                                <div>
                                    <div class="fw-bold small">#{{ $procurement->asset_request_id }}</div>
                                    <small class="text-muted">{{ Str::limit($procurement->assetRequest->title ?? 'N/A', 20) }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle bg-secondary text-white me-2" style="width:28px;height:28px;font-size:0.6rem;">
                                    {{ strtoupper(substr($procurement->supplier->name ?? 'S', 0, 2)) }}
                                </div>
                                <div>
                                    <div class="fw-bold small">{{ Str::limit($procurement->supplier->name ?? 'N/A', 18) }}</div>
                                    <small class="text-muted">{{ $procurement->supplier->email ?? '' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <small>{{ $procurement->order_date ? $procurement->order_date->format('M d, Y') : 'N/A' }}</small>
                        </td>
                        <td>
                            <small>{{ $procurement->expected_delivery ? $procurement->expected_delivery->format('M d, Y') : 'N/A' }}</small>
                            @if($procurement->expected_delivery && $procurement->expected_delivery < now() && $procurement->delivery_status != 'delivered')
                                <span class="badge bg-danger ms-1">Overdue</span>
                            @endif
                        </td>
                        <td>
                            <span class="fw-bold">${{ number_format($procurement->total_cost ?? 0, 2) }}</span>
                        </td>
                        <td>
                            @php
                                $paymentColors = [
                                    'paid' => 'success',
                                    'pending' => 'warning text-dark',
                                    'partial' => 'info',
                                    'cancelled' => 'secondary'
                                ];
                            @endphp
                            <span class="badge bg-{{ $paymentColors[$procurement->payment_status] ?? 'secondary' }}">
                                {{ ucfirst($procurement->payment_status) }}
                            </span>
                        </td>
                        <td>
                            @php
                                $statusColors = [
                                    'pending' => 'secondary',
                                    'ordered' => 'warning text-dark',
                                    'shipped' => 'info',
                                    'delivered' => 'success',
                                    'partial_delivery' => 'primary',
                                    'returned_to_supplier' => 'danger'
                                ];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$procurement->delivery_status] ?? 'secondary' }}">
                                {{ str_replace('_', ' ', ucfirst($procurement->delivery_status)) }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                @can('procurement.view')
                                <button class="btn btn-outline-primary btn-sm view-btn" data-id="{{ $procurement->id }}" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @endcan
                                @can('procurement.edit')
                                <button class="btn btn-outline-warning btn-sm edit-btn" data-id="{{ $procurement->id }}" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @endcan
                                @if($procurement->delivery_status == 'ordered' || $procurement->delivery_status == 'shipped')
                                    @can('procurement.receive')
                                    <button class="btn btn-outline-success btn-sm receive-btn" data-id="{{ $procurement->id }}" title="Mark Received">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    @endcan
                                @endif
                                @if($procurement->delivery_status != 'delivered' && $procurement->delivery_status != 'returned_to_supplier')
                                    @can('procurement.edit')
                                    <button class="btn btn-outline-info btn-sm update-status-btn" data-id="{{ $procurement->id }}" title="Update Status">
                                        <i class="fas fa-sync"></i>
                                    </button>
                                    @endcan
                                @endif
                                @can('procurement.delete')
                                <button class="btn btn-outline-danger btn-sm delete-btn" data-id="{{ $procurement->id }}" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ auth()->user()->can('procurement.delete') ? 10 : 9 }}">
                            <div class="text-center py-5">
                                <i class="fas fa-file-invoice fa-4x text-muted mb-3 d-block"></i>
                                <h5 class="fw-bold">No Procurements Found</h5>
                                <p class="text-muted">Click "New Procurement" to create your first procurement.</p>
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
                Showing {{ $procurements->firstItem() ?? 0 }} to {{ $procurements->lastItem() ?? 0 }} of {{ $procurements->total() }} entries
            </span>
        </div>
        <div>
            {{ $procurements->links() }}
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
                    <i class="fas fa-plus-circle text-primary me-2"></i> New Procurement
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createForm" action="{{ route('admin.procurements.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div id="createFormErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Asset Request <span class="text-danger">*</span></label>
                            <select name="asset_request_id" class="form-select" required>
                                <option value="">Select Request</option>
                                @foreach($assetRequests ?? [] as $request)
                                    <option value="{{ $request->id }}">#{{ $request->id }} - {{ Str::limit($request->title, 40) }}</option>
                                @endforeach
                            </select>
                        </div>
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
                            <label class="form-label fw-bold">Purchase Order</label>
                            <select name="purchase_order_id" class="form-select">
                                <option value="">Select PO</option>
                                @foreach($purchaseOrders ?? [] as $po)
                                    <option value="{{ $po->id }}">{{ $po->po_number }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Order Date <span class="text-danger">*</span></label>
                            <input type="date" name="order_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Expected Delivery</label>
                            <input type="date" name="expected_delivery" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Total Cost</label>
                            <input type="number" name="total_cost" class="form-control" step="0.01" min="0" placeholder="0.00">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Payment Status <span class="text-danger">*</span></label>
                            <select name="payment_status" class="form-select" required>
                                <option value="pending">Pending</option>
                                <option value="paid">Paid</option>
                                <option value="partial">Partial</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Delivery Notes</label>
                            <textarea name="delivery_notes" class="form-control" rows="2" placeholder="Additional notes..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="createSubmitBtn">
                        <i class="fas fa-save me-1"></i> Create Procurement
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
                    <i class="fas fa-eye text-info me-2"></i> Procurement Details
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
                    <i class="fas fa-edit text-warning me-2"></i> Edit Procurement
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
                        <i class="fas fa-save me-1"></i> Update Procurement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- RECEIVE MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="receiveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-success">
                    <i class="fas fa-check-circle me-2"></i> Mark as Received
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="receiveForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div id="receiveFormErrors" class="alert alert-danger d-none"></div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        This will mark the procurement as delivered and update stock.
                    </div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Received Date <span class="text-danger">*</span></label>
                            <input type="date" name="received_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Delivery Notes</label>
                            <textarea name="delivery_notes" class="form-control" rows="3" placeholder="Additional notes about delivery..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-success" id="receiveSubmitBtn">
                        <i class="fas fa-check me-1"></i> Confirm Received
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- UPDATE STATUS MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="updateStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-info">
                    <i class="fas fa-sync me-2"></i> Update Delivery Status
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="updateStatusForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div id="updateStatusFormErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Delivery Status <span class="text-danger">*</span></label>
                            <select name="delivery_status" class="form-select" required>
                                <option value="pending">Pending</option>
                                <option value="ordered">Ordered</option>
                                <option value="shipped">Shipped</option>
                                <option value="delivered">Delivered</option>
                                <option value="partial_delivery">Partial Delivery</option>
                                <option value="returned_to_supplier">Returned to Supplier</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Actual Delivery Date</label>
                            <input type="date" name="actual_delivery" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Notes</label>
                            <textarea name="delivery_notes" class="form-control" rows="3" placeholder="Additional notes..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-info" id="updateStatusSubmitBtn">
                        <i class="fas fa-sync me-1"></i> Update Status
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
                <p class="fw-bold">Are you sure you want to delete this procurement?</p>
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
    @can('procurement.delete')
    $('#selectAll').on('change', function() {
        $('.row-checkbox').prop('checked', $(this).prop('checked'));
    });

    $('#bulkDeleteBtn').on('click', function() {
        var selected = $('.row-checkbox:checked');
        if (selected.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Items Selected',
                text: 'Please select at least one procurement to delete.'
            });
            return;
        }

        Swal.fire({
            title: 'Delete Selected Procurements?',
            text: 'You are about to delete ' + selected.length + ' procurement(s). This action cannot be undone.',
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
                    url: '{{ route("admin.procurements.bulk-delete") }}',
                    type: 'POST',
                    data: {
                        procurement_ids: ids
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
                            text: xhr.responseJSON?.error || 'Failed to delete selected procurements.'
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
    @can('procurement.view')
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
            url: '/admin/procurements/' + id,
            type: 'GET',
            dataType: 'json',
            success: function(r) {
                var procurement = r.procurement || r;
                var workflows = r.workflows || [];

                var html = `
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Request ID</label>
                            <p class="fw-bold mb-0">#${procurement.asset_request_id}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Supplier</label>
                            <p class="mb-0">${procurement.supplier ? procurement.supplier.name : 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Procurement Officer</label>
                            <p class="mb-0">${procurement.procurement_officer ? procurement.procurement_officer.name : 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Total Cost</label>
                            <p class="fw-bold mb-0">$${parseFloat(procurement.total_cost || 0).toFixed(2)}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Order Date</label>
                            <p class="mb-0">${new Date(procurement.order_date).toLocaleDateString()}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Expected Delivery</label>
                            <p class="mb-0">${procurement.expected_delivery ? new Date(procurement.expected_delivery).toLocaleDateString() : 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Actual Delivery</label>
                            <p class="mb-0">${procurement.actual_delivery ? new Date(procurement.actual_delivery).toLocaleDateString() : 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Delivery Status</label>
                            <p class="mb-0"><span class="badge bg-${procurement.delivery_status == 'delivered' ? 'success' : (procurement.delivery_status == 'pending' ? 'secondary' : (procurement.delivery_status == 'ordered' ? 'warning text-dark' : (procurement.delivery_status == 'shipped' ? 'info' : 'danger')))}">${procurement.delivery_status.replace(/_/g, ' ').toUpperCase()}</span></p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Payment Status</label>
                            <p class="mb-0"><span class="badge bg-${procurement.payment_status == 'paid' ? 'success' : (procurement.payment_status == 'pending' ? 'warning text-dark' : (procurement.payment_status == 'partial' ? 'info' : 'secondary'))}">${procurement.payment_status.toUpperCase()}</span></p>
                        </div>
                        ${procurement.delivery_notes ? `
                        <div class="col-12">
                            <label class="text-muted small fw-bold">Delivery Notes</label>
                            <p class="mb-0">${procurement.delivery_notes}</p>
                        </div>` : ''}
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
                        <h5>Failed to load procurement details</h5>
                        <p>${xhr.status} - ${xhr.statusText}</p>
                    </div>
                `);
            }
        });
    });
    @endcan

    // ==========================================
    // EDIT
    // ==========================================
    @can('procurement.edit')
    $(document).on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        var body = $('#editModalBody');
        var form = $('#editForm');

        form.attr('action', '/admin/procurements/' + id);
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
            url: '/admin/procurements/' + id + '/edit',
            type: 'GET',
            dataType: 'json',
            success: function(r) {
                body.html(`
                    <div id="editFormErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Supplier <span class="text-danger">*</span></label>
                            <select name="supplier_id" class="form-select" required>
                                <option value="">Select Supplier</option>
                                @foreach($suppliers ?? [] as $supplier)
                                    <option value="{{ $supplier->id }}" ${r.supplier_id == {{ $supplier->id }} ? 'selected' : ''}>{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Order Date <span class="text-danger">*</span></label>
                            <input type="date" name="order_date" class="form-control" value="${r.order_date ? new Date(r.order_date).toISOString().split('T')[0] : ''}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Expected Delivery</label>
                            <input type="date" name="expected_delivery" class="form-control" value="${r.expected_delivery ? new Date(r.expected_delivery).toISOString().split('T')[0] : ''}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Total Cost</label>
                            <input type="number" name="total_cost" class="form-control" step="0.01" min="0" value="${r.total_cost || ''}" placeholder="0.00">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Payment Status <span class="text-danger">*</span></label>
                            <select name="payment_status" class="form-select" required>
                                <option value="pending" ${r.payment_status == 'pending' ? 'selected' : ''}>Pending</option>
                                <option value="paid" ${r.payment_status == 'paid' ? 'selected' : ''}>Paid</option>
                                <option value="partial" ${r.payment_status == 'partial' ? 'selected' : ''}>Partial</option>
                                <option value="cancelled" ${r.payment_status == 'cancelled' ? 'selected' : ''}>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Delivery Notes</label>
                            <textarea name="delivery_notes" class="form-control" rows="2">${r.delivery_notes || ''}</textarea>
                        </div>
                    </div>
                `);
            },
            error: function(xhr) {
                body.html(`
                    <div id="editFormErrors" class="alert alert-danger d-none"></div>
                    <div class="text-center py-5 text-danger">
                        <i class="fas fa-exclamation-circle fa-4x mb-3 d-block"></i>
                        <h5>Failed to load procurement data</h5>
                        <p>${xhr.status} - ${xhr.statusText}</p>
                    </div>
                `);
            }
        });
    });
    @endcan

    // ==========================================
    // CREATE
    // ==========================================
    @can('procurement.create')
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
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Create Procurement');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: r.message || 'Something went wrong.',
                    });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Create Procurement');
                if (xhr.status === 422) {
                    showErrors(errorContainer, xhr.responseJSON.errors);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to create procurement. Please try again.'
                    });
                }
            }
        });
    });
    @endcan

    // ==========================================
    // UPDATE
    // ==========================================
    @can('procurement.edit')
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
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Update Procurement');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: r.message || 'Something went wrong.',
                    });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Update Procurement');
                if (xhr.status === 422) {
                    showErrors(errorContainer, xhr.responseJSON.errors);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to update procurement. Please try again.'
                    });
                }
            }
        });
    });
    @endcan

    // ==========================================
    // RECEIVE
    // ==========================================
    @can('procurement.receive')
    $(document).on('click', '.receive-btn', function() {
        var id = $(this).data('id');
        var form = $('#receiveForm');
        form.attr('action', '/admin/procurements/' + id + '/receive');
        clearErrors($('#receiveFormErrors'));
        $('#receiveModal').modal('show');
    });

    $('#receiveForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        var data = form.serialize();
        var btn = $('#receiveSubmitBtn');
        var errorContainer = $('#receiveFormErrors');

        clearErrors(errorContainer);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Processing...');

        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(r) {
                if (r.success) {
                    $('#receiveModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Received!',
                        text: r.message,
                        timer: 2000,
                        showConfirmButton: true
                    }).then(() => { window.location.reload(); });
                } else {
                    btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i> Confirm Received');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: r.message || 'Failed to receive procurement.',
                    });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i> Confirm Received');
                if (xhr.status === 422) {
                    showErrors(errorContainer, xhr.responseJSON.errors);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to receive procurement. Please try again.'
                    });
                }
            }
        });
    });
    @endcan

    // ==========================================
    // UPDATE STATUS
    // ==========================================
    @can('procurement.edit')
    $(document).on('click', '.update-status-btn', function() {
        var id = $(this).data('id');
        var form = $('#updateStatusForm');
        form.attr('action', '/admin/procurements/' + id + '/update-status');
        clearErrors($('#updateStatusFormErrors'));
        $('#updateStatusModal').modal('show');
    });

    $('#updateStatusForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        var data = form.serialize();
        var btn = $('#updateStatusSubmitBtn');
        var errorContainer = $('#updateStatusFormErrors');

        clearErrors(errorContainer);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Updating...');

        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(r) {
                if (r.success) {
                    $('#updateStatusModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated!',
                        text: r.message,
                        timer: 2000,
                        showConfirmButton: true
                    }).then(() => { window.location.reload(); });
                } else {
                    btn.prop('disabled', false).html('<i class="fas fa-sync me-1"></i> Update Status');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: r.message || 'Failed to update status.',
                    });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-sync me-1"></i> Update Status');
                if (xhr.status === 422) {
                    showErrors(errorContainer, xhr.responseJSON.errors);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to update status. Please try again.'
                    });
                }
            }
        });
    });
    @endcan

    // ==========================================
    // DELETE
    // ==========================================
    @can('procurement.delete')
    $(document).on('click', '.delete-btn', function() {
        var id = $(this).data('id');
        var form = $('#deleteForm');
        form.attr('action', '/admin/procurements/' + id);

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
                        text: r.message || 'Failed to delete procurement.',
                    });
                }
            },
            error: function(xhr) {
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: xhr.responseJSON?.error || 'Failed to delete procurement.'
                });
            }
        });
    });
    @endcan

    // ==========================================
    // SEARCH & FILTER
    // ==========================================
    $('#searchBtn').on('click', function() { filterTable(); });
    $('#searchInput').on('keypress', function(e) {
        if (e.which === 13) filterTable();
    });
    $('#statusFilter').on('change', function() { filterTable(); });
    $('#paymentFilter').on('change', function() { filterTable(); });

    function filterTable() {
        var search = $('#searchInput').val().toLowerCase();
        var status = $('#statusFilter').val();
        var payment = $('#paymentFilter').val();

        $('#tableBody tr').each(function() {
            var row = $(this);
            var text = row.text().toLowerCase();
            var rowStatus = row.data('status');
            var rowPayment = row.data('payment');

            var show = true;
            if (search && !text.includes(search)) show = false;
            if (status && rowStatus !== status) show = false;
            if (payment && rowPayment !== payment) show = false;

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