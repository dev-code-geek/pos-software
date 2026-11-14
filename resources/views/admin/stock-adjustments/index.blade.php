@extends('layouts.admin')

@section('title', __('messages.stock_adjustments'))
@section('page-title', __('messages.stock_adjustment_management'))

@section('admin-content')
<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ number_format($stats['total'] ?? 0) }}</h3>
                <p>{{ __('messages.total_adjustments') }}</p>
            </div>
            <div class="icon"><i class="fas fa-balance-scale"></i></div>
            <a href="{{ route('admin.stock-adjustments.index') }}" class="small-box-footer">
                {{ __('messages.view_all') }} <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ number_format($stats['increase'] ?? 0) }}</h3>
                <p>{{ __('messages.increase') }}</p>
            </div>
            <div class="icon"><i class="fas fa-plus-circle"></i></div>
            <a href="{{ route('admin.stock-adjustments.index', ['type' => 'increase']) }}" class="small-box-footer">
                {{ __('messages.view_increases') }} <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ number_format($stats['decrease'] ?? 0) }}</h3>
                <p>{{ __('messages.decrease') }}</p>
            </div>
            <div class="icon"><i class="fas fa-minus-circle"></i></div>
            <a href="{{ route('admin.stock-adjustments.index', ['type' => 'decrease']) }}" class="small-box-footer">
                {{ __('messages.view_decreases') }} <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ number_format($stats['transfer'] ?? 0) }}</h3>
                <p>{{ __('messages.transfer') }}</p>
            </div>
            <div class="icon"><i class="fas fa-exchange-alt"></i></div>
            <a href="{{ route('admin.stock-adjustments.index', ['type' => 'transfer']) }}" class="small-box-footer">
                {{ __('messages.view_transfers') }} <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-secondary">
            <div class="inner">
                <h3>{{ number_format($stats['draft'] ?? 0) }}</h3>
                <p>{{ __('messages.draft') }}</p>
            </div>
            <div class="icon"><i class="fas fa-pen"></i></div>
            <a href="{{ route('admin.stock-adjustments.index', ['status' => 'draft']) }}" class="small-box-footer">
                {{ __('messages.view_draft') }} <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ number_format($stats['approved'] ?? 0) }}</h3>
                <p>{{ __('messages.approved') }}</p>
            </div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
            <a href="{{ route('admin.stock-adjustments.index', ['status' => 'approved']) }}" class="small-box-footer">
                {{ __('messages.view_approved') }} <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Main Card -->
<div class="card">
    <div class="card-header">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <h5 class="card-title mb-0">
                <i class="fas fa-balance-scale text-primary me-2"></i>
                {{ __('messages.all_adjustments') }}
                <span class="badge bg-primary ms-1">{{ $adjustments->total() }}</span>
            </h5>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <div class="input-group input-group-sm" style="width: 200px;">
                <input type="text" id="searchInput" class="form-control" placeholder="{{ __('messages.search') }}...">
                <button class="btn btn-secondary" type="button" id="searchBtn">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            <select id="typeFilter" class="form-select form-select-sm" style="width: 120px;">
                <option value="">{{ __('messages.all_types') }}</option>
                <option value="increase">{{ __('messages.increase') }}</option>
                <option value="decrease">{{ __('messages.decrease') }}</option>
                <option value="transfer">{{ __('messages.transfer') }}</option>
            </select>
            <select id="statusFilter" class="form-select form-select-sm" style="width: 120px;">
                <option value="">{{ __('messages.all_status') }}</option>
                <option value="draft">{{ __('messages.draft') }}</option>
                <option value="approved">{{ __('messages.approved') }}</option>
                <option value="cancelled">{{ __('messages.cancelled') }}</option>
            </select>
            @can('stock_adjustment.create')
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fas fa-plus-circle me-1"></i> {{ __('messages.new_adjustment') }}
            </button>
            @endcan
            @can('stock_adjustment.export')
            <button class="btn btn-success btn-sm" id="exportBtn">
                <i class="fas fa-file-excel me-1"></i> {{ __('messages.export') }}
            </button>
            @endcan
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 35px;">#</th>
                        <th style="min-width: 140px;">{{ __('messages.adjustment_number') }}</th>
                        <th style="min-width: 140px;">{{ __('messages.warehouse') }}</th>
                        <th style="width: 100px;">{{ __('messages.type') }}</th>
                        <th style="min-width: 150px;">{{ __('messages.reason') }}</th>
                        <th style="width: 100px;">{{ __('messages.status') }}</th>
                        <th style="width: 100px;">{{ __('messages.date') }}</th>
                        <th style="width: 200px;">{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($adjustments as $adj)
                    <tr data-id="{{ $adj->id }}" data-type="{{ $adj->type }}" data-status="{{ $adj->status }}">
                        <td>{{ $loop->iteration }}</td>
                        <td><span class="fw-bold">{{ $adj->adjustment_number }}</span></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle bg-info text-white me-2">
                                    <i class="fas fa-warehouse"></i>
                                </div>
                                <div>
                                    <div class="fw-bold small">{{ Str::limit($adj->warehouse->name ?? 'N/A', 20) }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @php
                                $typeColors = ['increase' => 'success', 'decrease' => 'danger', 'transfer' => 'info'];
                            @endphp
                            <span class="badge bg-{{ $typeColors[$adj->type] ?? 'secondary' }}">
                                {{ __('messages.' . $adj->type) }}
                            </span>
                        </td>
                        <td><span title="{{ $adj->reason }}">{{ Str::limit($adj->reason, 30) }}</span></td>
                        <td>
                            @php
                                $statusColors = ['draft' => 'secondary', 'approved' => 'success', 'cancelled' => 'danger'];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$adj->status] ?? 'secondary' }}">
                                {{ __('messages.' . $adj->status) }}
                            </span>
                        </td>
                        <td>
                            <small>{{ $adj->adjustment_date ? $adj->adjustment_date->format('M d, Y') : 'N/A' }}</small>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                @can('stock_adjustment.view')
                                <button class="btn btn-outline-primary btn-sm view-btn" data-id="{{ $adj->id }}" title="{{ __('messages.view') }}">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @endcan
                                @can('stock_adjustment.edit')
                                @if($adj->status == 'draft')
                                <button class="btn btn-outline-warning btn-sm edit-btn" data-id="{{ $adj->id }}" title="{{ __('messages.edit') }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @endif
                                @endcan
                                @can('stock_adjustment.approve')
                                @if($adj->status == 'draft')
                                <button class="btn btn-outline-success btn-sm approve-btn" data-id="{{ $adj->id }}" title="{{ __('messages.approve') }}">
                                    <i class="fas fa-check"></i>
                                </button>
                                @endif
                                @endcan
                                @can('stock_adjustment.cancel')
                                @if($adj->status == 'draft')
                                <button class="btn btn-outline-danger btn-sm cancel-btn" data-id="{{ $adj->id }}" title="{{ __('messages.cancel') }}">
                                    <i class="fas fa-times"></i>
                                </button>
                                @endif
                                @endcan
                                @can('stock_adjustment.delete')
                                @if(in_array($adj->status, ['draft', 'cancelled']))
                                <button class="btn btn-outline-danger btn-sm delete-btn" data-id="{{ $adj->id }}" title="{{ __('messages.delete') }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">
                            <div class="text-center py-5">
                                <i class="fas fa-balance-scale fa-4x text-muted mb-3 d-block"></i>
                                <h5 class="fw-bold">{{ __('messages.no_adjustments_found') }}</h5>
                                <p class="text-muted">{{ __('messages.click_new_adjustment') }}</p>
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
                {{ __('messages.showing') }} {{ $adjustments->firstItem() ?? 0 }}
                {{ __('messages.to') }} {{ $adjustments->lastItem() ?? 0 }}
                {{ __('messages.of') }} {{ $adjustments->total() }}
                {{ __('messages.entries') }}
            </span>
        </div>
        <div>{{ $adjustments->links() }}</div>
    </div>
</div>

<!-- CREATE MODAL -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 750px;">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white">
                    <i class="fas fa-plus-circle me-2"></i> {{ __('messages.new_stock_adjustment') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="createForm" action="{{ route('admin.stock-adjustments.store') }}" method="POST">
                @csrf
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <div id="createFormErrors" class="alert alert-danger d-none"></div>

                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="fw-bold mb-0"><i class="fas fa-info-circle text-primary me-2"></i>{{ __('messages.basic_information') }}</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">{{ __('messages.warehouse') }} <span class="text-danger">*</span></label>
                                    <select name="warehouse_id" class="form-select" required>
                                        <option value="">{{ __('messages.select_warehouse') }}</option>
                                        @foreach($warehouses ?? [] as $wh)
                                            <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->code }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">{{ __('messages.type') }} <span class="text-danger">*</span></label>
                                    <select name="type" class="form-select" required>
                                        <option value="increase">{{ __('messages.increase') }}</option>
                                        <option value="decrease">{{ __('messages.decrease') }}</option>
                                        <option value="transfer">{{ __('messages.transfer') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">{{ __('messages.adjustment_date') }} <span class="text-danger">*</span></label>
                                    <input type="date" name="adjustment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">{{ __('messages.reason') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="reason" class="form-control" placeholder="{{ __('messages.reason_for_adjustment') }}" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold">{{ __('messages.notes') }}</label>
                                    <textarea name="notes" class="form-control" rows="2" placeholder="{{ __('messages.additional_notes') }}"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="fw-bold mb-0"><i class="fas fa-boxes text-success me-2"></i>{{ __('messages.items') }}</h6>
                        </div>
                        <div class="card-body">
                            <div id="createItems">
                                <div class="row g-2 item-row align-items-end">
                                    <div class="col-md-5">
                                        <label class="form-label fw-bold small">{{ __('messages.product') }} <span class="text-danger">*</span></label>
                                        <select name="items[0][product_id]" class="form-select product-select" required>
                                            <option value="">{{ __('messages.select_product') }}</option>
                                            @foreach($products ?? [] as $prod)
                                                <option value="{{ $prod->id }}">{{ $prod->name }} ({{ $prod->sku }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold small">{{ __('messages.quantity') }} <span class="text-danger">*</span></label>
                                        <input type="number" name="items[0][quantity]" class="form-control" placeholder="{{ __('messages.qty') }}" step="0.01" min="0.01" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold small">{{ __('messages.reason') }}</label>
                                        <input type="text" name="items[0][reason]" class="form-control" placeholder="{{ __('messages.item_reason') }}">
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-danger btn-sm remove-item-btn mt-2" style="display: none;">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-secondary mt-2" id="addItemBtn">
                                <i class="fas fa-plus me-1"></i> {{ __('messages.add_item') }}
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> {{ __('messages.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary" id="createSubmitBtn">
                        <i class="fas fa-save me-1"></i> {{ __('messages.save_adjustment') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- VIEW MODAL -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 750px;">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title text-white">
                    <i class="fas fa-eye me-2"></i> {{ __('messages.adjustment_details') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewModalBody" style="max-height: 70vh; overflow-y: auto;">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">{{ __('messages.loading') }}...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.close') }}</button>
            </div>
        </div>
    </div>
</div>

<!-- EDIT MODAL -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 750px;">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title text-dark">
                    <i class="fas fa-edit me-2"></i> {{ __('messages.edit_adjustment') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body" id="editModalBody" style="max-height: 70vh; overflow-y: auto;">
                    <div id="editFormErrors" class="alert alert-danger d-none"></div>
                    <div class="text-center py-5">
                        <div class="spinner-border text-warning" role="status">
                            <span class="visually-hidden">{{ __('messages.loading') }}...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> {{ __('messages.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-warning" id="editSubmitBtn">
                        <i class="fas fa-save me-1"></i> {{ __('messages.update_adjustment') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- DELETE CONFIRM MODAL -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white">
                    <i class="fas fa-exclamation-triangle me-2"></i> {{ __('messages.confirm_delete') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-trash fa-4x text-danger mb-3 d-block"></i>
                <p class="fw-bold">{{ __('messages.are_you_sure_delete_adjustment') }}</p>
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

<style>
.avatar-circle { width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:600; flex-shrink:0; }
.small-box { border-radius:12px; padding:20px; margin-bottom:0; position:relative; overflow:hidden; transition:transform .15s ease; }
.small-box:hover { transform:translateY(-2px); }
.small-box .inner { color:#fff; }
.small-box .inner h3 { font-size:2rem; font-weight:700; margin:0 0 5px; }
.small-box .inner p { font-size:.9rem; opacity:.9; margin:0; }
.small-box .icon { position:absolute; right:15px; top:15px; font-size:2.5rem; opacity:.25; color:#fff; }
.small-box .small-box-footer { display:block; padding:8px 0 0; color:rgba(255,255,255,.8); text-decoration:none; font-size:.85rem; transition:all .15s ease; }
.small-box .small-box-footer:hover { color:#fff; }
.small-box .small-box-footer i { transition:transform .15s ease; }
.small-box:hover .small-box-footer i { transform:translateX(4px); }
.modal-body .card { border:1px solid #e5e7eb; border-radius:8px; margin-bottom:15px; }
.modal-body .card-header { background:#f9fafb; border-bottom:1px solid #e5e7eb; padding:10px 15px; }
.modal-body .card-body { padding:15px; }
.item-row { padding:8px; background:#f8fafc; border-radius:6px; margin-bottom:8px; }
</style>

@push('scripts')
<script>
window.trans = {
    loading: @json(__('messages.loading')),
    close: @json(__('messages.close')),
    cancel: @json(__('messages.cancel')),
    save: @json(__('messages.save')),
    update: @json(__('messages.update')),
    delete: @json(__('messages.delete')),
    edit: @json(__('messages.edit')),
    view: @json(__('messages.view')),
    approve: @json(__('messages.approve')),
    active: @json(__('messages.active')),
    inactive: @json(__('messages.inactive')),
    // Adjustment specifics
    adjustment_number: @json(__('messages.adjustment_number')),
    warehouse: @json(__('messages.warehouse')),
    type: @json(__('messages.type')),
    reason: @json(__('messages.reason')),
    status: @json(__('messages.status')),
    date: @json(__('messages.date')),
    notes: @json(__('messages.notes')),
    quantity_adjustment: @json(__('messages.quantity_adjustment')),
    before: @json(__('messages.before')),
    after: @json(__('messages.after')),
    product: @json(__('messages.product')),
    items: @json(__('messages.items')),
    no_items_found: @json(__('messages.no_items_found')),
    draft: @json(__('messages.draft')),
    approved: @json(__('messages.approved')),
    cancelled: @json(__('messages.cancelled')),
    increase: @json(__('messages.increase')),
    decrease: @json(__('messages.decrease')),
    transfer: @json(__('messages.transfer')),
    select_warehouse: @json(__('messages.select_warehouse')),
    select_product: @json(__('messages.select_product')),
    qty: @json(__('messages.qty')),
    item_reason: @json(__('messages.item_reason')),
    add_item: @json(__('messages.add_item')),
    adjustment_date: @json(__('messages.adjustment_date')),
    additional_notes: @json(__('messages.additional_notes')),
    reason_for_adjustment: @json(__('messages.reason_for_adjustment')),
    // Alerts
    created: @json(__('messages.created')),
    updated: @json(__('messages.updated')),
    deleted: @json(__('messages.deleted')),
    approved_alert: @json(__('messages.approved_alert')),
    cancelled_alert: @json(__('messages.cancelled_alert')),
    error_title: @json(__('messages.error')),
    success_title: @json(__('messages.success')),
    something_went_wrong: @json(__('messages.something_went_wrong')),
    failed_to_load: @json(__('messages.failed_to_load')),
    failed_to_load_details: @json(__('messages.failed_to_load_details')),
    failed_to_create: @json(__('messages.failed_to_create')),
    failed_to_update: @json(__('messages.failed_to_update')),
    failed_to_delete: @json(__('messages.failed_to_delete')),
    failed_to_approve: @json(__('messages.failed_to_approve')),
    failed_to_cancel: @json(__('messages.failed_to_cancel')),
    confirm_delete: @json(__('messages.confirm_delete')),
    are_you_sure: @json(__('messages.are_you_sure')),
    wont_be_able_to_revert: @json(__('messages.wont_be_able_to_revert')),
    yes_delete: @json(__('messages.yes_delete')),
    approve_adjustment_q: @json(__('messages.approve_adjustment_q')),
    approve_warning: @json(__('messages.approve_warning')),
    yes_approve: @json(__('messages.yes_approve')),
    cancel_adjustment_q: @json(__('messages.cancel_adjustment_q')),
    cancel_warning: @json(__('messages.cancel_warning')),
    yes_cancel: @json(__('messages.yes_cancel')),
    no: @json(__('messages.no')),
    approving: @json(__('messages.approving')),
    cancelling: @json(__('messages.cancelling')),
    creating: @json(__('messages.creating')),
    updating: @json(__('messages.updating')),
    deleting: @json(__('messages.deleting')),
    // Buttons (used when re-enabling)
    save_adjustment: @json(__('messages.save_adjustment')),
    update_adjustment: @json(__('messages.update_adjustment')),
};

$(document).ready(function() {
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

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

    function clearErrors(container) {
        container.addClass('d-none').empty();
    }

    // Product options template (reused)
    var productOptionsHtml = `@foreach($products ?? [] as $prod)<option value="{{ $prod->id }}">{{ $prod->name }} ({{ $prod->sku }})</option>@endforeach`;

    // ============ ADD/REMOVE ITEMS (Create) ============
    var itemIndex = 1;

    $('#addItemBtn').on('click', function() {
        var html = `
            <div class="row g-2 item-row align-items-end">
                <div class="col-md-5">
                    <select name="items[${itemIndex}][product_id]" class="form-select product-select" required>
                        <option value="">${trans.select_product}</option>
                        ${productOptionsHtml}
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="number" name="items[${itemIndex}][quantity]" class="form-control" placeholder="${trans.qty}" step="0.01" min="0.01" required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="items[${itemIndex}][reason]" class="form-control" placeholder="${trans.item_reason}">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger btn-sm remove-item-btn mt-2">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
        $('#createItems').append(html);
        $('#createItems .remove-item-btn').show();
        if ($('#createItems .item-row').length === 1) {
            $('#createItems .remove-item-btn').hide();
        }
        itemIndex++;
    });

    $(document).on('click', '.remove-item-btn', function() {
        $(this).closest('.item-row').remove();
        if ($('#createItems .item-row').length === 1) {
            $('#createItems .remove-item-btn').hide();
        }
    });

    setTimeout(function() { $('#createItems .remove-item-btn').hide(); }, 100);

    // ============ VIEW ============
    @can('stock_adjustment.view')
    $(document).on('click', '.view-btn', function() {
        var id = $(this).data('id');
        var body = $('#viewModalBody');

        body.html(`<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">${trans.loading}...</span></div></div>`);
        $('#viewModal').modal('show');

        $.ajax({
            url: '/admin/stock-adjustments/' + id,
            type: 'GET', dataType: 'json',
            success: function(r) {
                var adjustment = r;
                var items = adjustment.items || [];

                var html = `
                    <div class="row g-3">
                        <div class="col-md-6"><label class="text-muted small fw-bold">${trans.adjustment_number}</label><p class="fw-bold mb-0">${adjustment.adjustment_number}</p></div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">${trans.warehouse}</label><p class="mb-0">${adjustment.warehouse ? adjustment.warehouse.name : 'N/A'}</p></div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">${trans.type}</label>
                            <p class="mb-0"><span class="badge bg-${adjustment.type == 'increase' ? 'success' : (adjustment.type == 'decrease' ? 'danger' : 'info')}">${trans[adjustment.type] || adjustment.type}</span></p>
                        </div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">${trans.status}</label>
                            <p class="mb-0"><span class="badge bg-${adjustment.status == 'approved' ? 'success' : (adjustment.status == 'draft' ? 'secondary' : 'danger')}">${trans[adjustment.status] || adjustment.status}</span></p>
                        </div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">${trans.date}</label><p class="mb-0">${new Date(adjustment.adjustment_date).toLocaleDateString()}</p></div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">${trans.reason}</label><p class="mb-0">${adjustment.reason}</p></div>
                        ${adjustment.notes ? `<div class="col-12"><label class="text-muted small fw-bold">${trans.notes}</label><p class="mb-0">${adjustment.notes}</p></div>` : ''}
                        <div class="col-12">
                            <hr>
                            <h6 class="fw-bold text-primary"><i class="fas fa-boxes me-2"></i>${trans.items}</h6>
                            ${items.length > 0 ? `
                            <table class="table table-sm table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>${trans.product}</th>
                                        <th class="text-end">${trans.quantity_adjustment}</th>
                                        <th class="text-end">${trans.before}</th>
                                        <th class="text-end">${trans.after}</th>
                                        <th>${trans.reason}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${items.map(function(item, index) {
                                        return `<tr>
                                            <td>${index + 1}</td>
                                            <td>${item.product ? item.product.name : 'N/A'}</td>
                                            <td class="text-end fw-bold ${item.quantity_adjustment >= 0 ? 'text-success' : 'text-danger'}">${item.quantity_adjustment >= 0 ? '+' : ''}${item.quantity_adjustment}</td>
                                            <td class="text-end">${item.quantity_before}</td>
                                            <td class="text-end">${item.quantity_after}</td>
                                            <td>${item.reason || '-'}</td>
                                        </tr>`;
                                    }).join('')}
                                </tbody>
                            </table>` : `<p class="text-muted">${trans.no_items_found}</p>`}
                        </div>
                    </div>
                `;
                body.html(html);
            },
            error: function(xhr) {
                body.html(`<div class="text-center py-5 text-danger"><i class="fas fa-exclamation-circle fa-4x mb-3 d-block"></i><h5>${trans.failed_to_load_details}</h5><p>${xhr.status} - ${xhr.statusText}</p></div>`);
            }
        });
    });
    @endcan

    // ============ EDIT ============
    @can('stock_adjustment.edit')
    $(document).on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        var body = $('#editModalBody');
        var form = $('#editForm');

        form.attr('action', '/admin/stock-adjustments/' + id);
        body.html(`<div id="editFormErrors" class="alert alert-danger d-none"></div><div class="text-center py-5"><div class="spinner-border text-warning" role="status"><span class="visually-hidden">${trans.loading}...</span></div></div>`);
        $('#editModal').modal('show');

        $.ajax({
            url: '/admin/stock-adjustments/' + id + '/edit',
            type: 'GET', dataType: 'json',
            success: function(response) {
                if (!response.success) {
                    body.html(`<div id="editFormErrors" class="alert alert-danger"><strong>${trans.error_title}:</strong> ${response.error || trans.failed_to_load}</div>`);
                    return;
                }

                var adj = response.adjustment;
                var warehouses = response.warehouses || [];
                var products = response.products || [];
                var items = adj.items || [];

                var warehouseOptions = warehouses.map(function(w) {
                    var selected = adj.warehouse_id == w.id ? 'selected' : '';
                    return `<option value="${w.id}" ${selected}>${w.name} (${w.code})</option>`;
                }).join('');

                var productOptions = products.map(function(p) {
                    return `<option value="${p.id}">${p.name} (${p.sku})</option>`;
                }).join('');

                var itemsHtml = '';
                items.forEach(function(item, index) {
                    itemsHtml += `
                        <div class="row g-2 item-row align-items-end">
                            <div class="col-md-5">
                                <select name="items[${index}][product_id]" class="form-select product-select" required>
                                    <option value="">${trans.select_product}</option>
                                    ${products.map(function(p) {
                                        return `<option value="${p.id}" ${item.product_id == p.id ? 'selected' : ''}>${p.name} (${p.sku})</option>`;
                                    }).join('')}
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="number" name="items[${index}][quantity]" class="form-control" placeholder="${trans.qty}" step="0.01" min="0.01" value="${Math.abs(item.quantity_adjustment)}" required>
                            </div>
                            <div class="col-md-3">
                                <input type="text" name="items[${index}][reason]" class="form-control" placeholder="${trans.item_reason}" value="${item.reason || ''}">
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-danger btn-sm remove-item-btn mt-2 ${items.length <= 1 ? 'd-none' : ''}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    `;
                });

                body.html(`
                    <div id="editFormErrors" class="alert alert-danger d-none"></div>
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="fw-bold mb-0"><i class="fas fa-info-circle text-primary me-2"></i>${trans.basic_information || ''}</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">${trans.warehouse} <span class="text-danger">*</span></label>
                                    <select name="warehouse_id" class="form-select" required>${warehouseOptions}</select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">${trans.type} <span class="text-danger">*</span></label>
                                    <select name="type" class="form-select" required>
                                        <option value="increase" ${adj.type == 'increase' ? 'selected' : ''}>${trans.increase}</option>
                                        <option value="decrease" ${adj.type == 'decrease' ? 'selected' : ''}>${trans.decrease}</option>
                                        <option value="transfer" ${adj.type == 'transfer' ? 'selected' : ''}>${trans.transfer}</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">${trans.adjustment_date} <span class="text-danger">*</span></label>
                                    <input type="date" name="adjustment_date" class="form-control" value="${adj.adjustment_date ? new Date(adj.adjustment_date).toISOString().split('T')[0] : ''}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">${trans.reason} <span class="text-danger">*</span></label>
                                    <input type="text" name="reason" class="form-control" value="${adj.reason}" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold">${trans.notes}</label>
                                    <textarea name="notes" class="form-control" rows="2">${adj.notes || ''}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="fw-bold mb-0"><i class="fas fa-boxes text-success me-2"></i>${trans.items}</h6>
                        </div>
                        <div class="card-body">
                            <div id="editItems">${itemsHtml}</div>
                            <button type="button" class="btn btn-sm btn-secondary mt-2" id="editAddItemBtn">
                                <i class="fas fa-plus me-1"></i> ${trans.add_item}
                            </button>
                        </div>
                    </div>
                `);

                var editItemIndex = items.length;
                $('#editAddItemBtn').off('click').on('click', function() {
                    var html = `
                        <div class="row g-2 item-row align-items-end">
                            <div class="col-md-5">
                                <select name="items[${editItemIndex}][product_id]" class="form-select product-select" required>
                                    <option value="">${trans.select_product}</option>
                                    ${productOptions}
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="number" name="items[${editItemIndex}][quantity]" class="form-control" placeholder="${trans.qty}" step="0.01" min="0.01" required>
                            </div>
                            <div class="col-md-3">
                                <input type="text" name="items[${editItemIndex}][reason]" class="form-control" placeholder="${trans.item_reason}">
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-danger btn-sm remove-item-btn mt-2"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                    `;
                    $('#editItems').append(html);
                    editItemIndex++;
                    $('#editItems .remove-item-btn').show();
                });

                $(document).on('click', '.remove-item-btn', function() {
                    $(this).closest('.item-row').remove();
                    if ($('#editItems .item-row').length <= 1) {
                        $('#editItems .remove-item-btn').addClass('d-none');
                    }
                });

                if ($('#editItems .item-row').length <= 1) {
                    $('#editItems .remove-item-btn').addClass('d-none');
                }
            },
            error: function(xhr) {
                body.html(`<div id="editFormErrors" class="alert alert-danger"><strong>${trans.error_title}:</strong> ${xhr.responseJSON?.error || trans.failed_to_load}</div>`);
            }
        });
    });
    @endcan

    // ============ CREATE ============
    @can('stock_adjustment.create')
    $('#createForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = $('#createSubmitBtn');
        var errorContainer = $('#createFormErrors');

        clearErrors(errorContainer);
        btn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm me-1"></span> ${trans.creating}...`);

        $.ajax({
            url: form.attr('action'), type: 'POST', data: form.serialize(), dataType: 'json',
            success: function(r) {
                btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.save_adjustment}`);
                if (r.success) {
                    $('#createModal').modal('hide');
                    Swal.fire({ icon: 'success', title: trans.created, text: r.message, timer: 2000, showConfirmButton: true }).then(() => window.location.reload());
                } else if (r.errors) {
                    showErrors(errorContainer, r.errors);
                } else {
                    Swal.fire({ icon: 'error', title: trans.error_title, text: r.message || trans.something_went_wrong });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.save_adjustment}`);
                if (xhr.status === 422) { showErrors(errorContainer, xhr.responseJSON.errors); }
                else { Swal.fire({ icon: 'error', title: trans.error_title, text: xhr.responseJSON?.error || trans.failed_to_create }); }
            }
        });
    });
    @endcan

    // ============ UPDATE ============
    @can('stock_adjustment.edit')
    $('#editForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = $('#editSubmitBtn');
        var errorContainer = $('#editFormErrors');

        clearErrors(errorContainer);
        btn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm me-1"></span> ${trans.updating}...`);

        $.ajax({
            url: form.attr('action'), type: 'POST', data: form.serialize(), dataType: 'json',
            success: function(r) {
                btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.update_adjustment}`);
                if (r.success) {
                    $('#editModal').modal('hide');
                    Swal.fire({ icon: 'success', title: trans.updated, text: r.message, timer: 2000, showConfirmButton: true }).then(() => window.location.reload());
                } else if (r.errors) {
                    showErrors(errorContainer, r.errors);
                } else {
                    Swal.fire({ icon: 'error', title: trans.error_title, text: r.message || trans.something_went_wrong });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.update_adjustment}`);
                if (xhr.status === 422) { showErrors(errorContainer, xhr.responseJSON.errors); }
                else { Swal.fire({ icon: 'error', title: trans.error_title, text: xhr.responseJSON?.error || trans.failed_to_update }); }
            }
        });
    });
    @endcan

    // ============ DELETE ============
    @can('stock_adjustment.delete')
    $(document).on('click', '.delete-btn', function() {
        var id = $(this).data('id');
        $('#deleteForm').attr('action', '/admin/stock-adjustments/' + id);

        Swal.fire({
            title: trans.are_you_sure,
            text: trans.wont_be_able_to_revert,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: trans.yes_delete,
            cancelButtonText: trans.cancel
        }).then((result) => {
            if (result.isConfirmed) { $('#deleteModal').modal('show'); }
        });
    });

    $('#deleteForm').on('submit', function(e) {
        e.preventDefault();
        var btn = $('#deleteSubmitBtn');
        btn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm me-1"></span> ${trans.deleting}...`);

        $.ajax({
            url: $(this).attr('action'), type: 'POST',
            data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
            dataType: 'json',
            success: function(r) {
                btn.prop('disabled', false).html(`<i class="fas fa-trash me-1"></i> ${trans.delete}`);
                if (r.success) {
                    $('#deleteModal').modal('hide');
                    Swal.fire({ icon: 'success', title: trans.deleted, text: r.message, timer: 2000, showConfirmButton: true }).then(() => window.location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: trans.error_title, text: r.message || trans.failed_to_delete });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(`<i class="fas fa-trash me-1"></i> ${trans.delete}`);
                Swal.fire({ icon: 'error', title: trans.error_title, text: xhr.responseJSON?.message || trans.failed_to_delete });
            }
        });
    });
    @endcan

    // ============ APPROVE ============
    @can('stock_adjustment.approve')
    $(document).on('click', '.approve-btn', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: trans.approve_adjustment_q,
            text: trans.approve_warning,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: trans.yes_approve,
            cancelButtonText: trans.cancel
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: trans.approving, allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                $.ajax({
                    url: '/admin/stock-adjustments/' + id + '/approve',
                    type: 'POST', data: { _token: '{{ csrf_token() }}' }, dataType: 'json',
                    success: function(r) {
                        if (r.success) {
                            Swal.fire({ icon: 'success', title: trans.approved_alert, text: r.message, timer: 2000, showConfirmButton: true }).then(() => window.location.reload());
                        } else {
                            Swal.fire({ icon: 'error', title: trans.error_title, text: r.message || trans.failed_to_approve });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({ icon: 'error', title: trans.error_title, text: xhr.responseJSON?.message || trans.failed_to_approve });
                    }
                });
            }
        });
    });
    @endcan

    // ============ CANCEL ============
    @can('stock_adjustment.cancel')
    $(document).on('click', '.cancel-btn', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: trans.cancel_adjustment_q,
            text: trans.cancel_warning,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: trans.yes_cancel,
            cancelButtonText: trans.no
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: trans.cancelling, allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                $.ajax({
                    url: '/admin/stock-adjustments/' + id + '/cancel',
                    type: 'POST', data: { _token: '{{ csrf_token() }}' }, dataType: 'json',
                    success: function(r) {
                        if (r.success) {
                            Swal.fire({ icon: 'success', title: trans.cancelled_alert, text: r.message, timer: 2000, showConfirmButton: true }).then(() => window.location.reload());
                        } else {
                            Swal.fire({ icon: 'error', title: trans.error_title, text: r.message || trans.failed_to_cancel });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({ icon: 'error', title: trans.error_title, text: xhr.responseJSON?.message || trans.failed_to_cancel });
                    }
                });
            }
        });
    });
    @endcan

    // ============ SEARCH & FILTER ============
    $('#searchBtn').on('click', filterTable);
    $('#searchInput').on('keypress', function(e) { if (e.which === 13) filterTable(); });
    $('#typeFilter, #statusFilter').on('change', filterTable);

    function filterTable() {
        var search = $('#searchInput').val().toLowerCase();
        var type = $('#typeFilter').val();
        var status = $('#statusFilter').val();
        $('#tableBody tr').each(function() {
            var row = $(this);
            var text = row.text().toLowerCase();
            var show = true;
            if (search && !text.includes(search)) show = false;
            if (type && row.data('type') !== type) show = false;
            if (status && row.data('status') !== status) show = false;
            row.toggle(show);
        });
    }

    // ============ EXPORT ============
    $('#exportBtn').on('click', function() {
        window.location.href = '{{ route("admin.stock-adjustments.export") }}';
    });

    // ============ MODAL CLEANUP ============
    $('.modal').on('hidden.bs.modal', function() {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
        $(this).find('.alert-danger').addClass('d-none').empty();
        $(this).find('form')[0]?.reset();

        if ($(this).find('#createItems').length) {
            $('#createItems').html(`
                <div class="row g-2 item-row align-items-end">
                    <div class="col-md-5">
                        <select name="items[0][product_id]" class="form-select product-select" required>
                            <option value="">${trans.select_product}</option>
                            ${productOptionsHtml}
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="number" name="items[0][quantity]" class="form-control" placeholder="${trans.qty}" step="0.01" min="0.01" required>
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="items[0][reason]" class="form-control" placeholder="${trans.item_reason}">
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-danger btn-sm remove-item-btn mt-2" style="display:none;"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            `);
            itemIndex = 1;
        }
    });
});
</script>
@endpush
@endsection