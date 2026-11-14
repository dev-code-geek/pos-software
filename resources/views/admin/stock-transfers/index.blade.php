@extends('layouts.admin')

@section('title', __('messages.stock_transfers'))
@section('page-title', __('messages.stock_transfer_management'))

@section('admin-content')
<div class="row g-3 mb-4">
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-primary">
            <div class="inner"><h3>{{ number_format($stats['total'] ?? 0) }}</h3><p>{{ __('messages.total') }}</p></div>
            <div class="icon"><i class="fas fa-exchange-alt"></i></div>
            <a href="{{ route('admin.stock-transfers.index') }}" class="small-box-footer">{{ __('messages.view_all') }} <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-secondary">
            <div class="inner"><h3>{{ number_format($stats['draft'] ?? 0) }}</h3><p>{{ __('messages.draft') }}</p></div>
            <div class="icon"><i class="fas fa-pen"></i></div>
            <a href="{{ route('admin.stock-transfers.index', ['status' => 'draft']) }}" class="small-box-footer">{{ __('messages.view_draft') }} <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-warning text-dark">
            <div class="inner"><h3>{{ number_format($stats['pending'] ?? 0) }}</h3><p>{{ __('messages.pending') }}</p></div>
            <div class="icon"><i class="fas fa-clock"></i></div>
            <a href="{{ route('admin.stock-transfers.index', ['status' => 'pending']) }}" class="small-box-footer">{{ __('messages.view_pending') }} <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-info">
            <div class="inner"><h3>{{ number_format($stats['in_transit'] ?? 0) }}</h3><p>{{ __('messages.in_transit') }}</p></div>
            <div class="icon"><i class="fas fa-truck"></i></div>
            <a href="{{ route('admin.stock-transfers.index', ['status' => 'in_transit']) }}" class="small-box-footer">{{ __('messages.view_in_transit') }} <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-success">
            <div class="inner"><h3>{{ number_format($stats['completed'] ?? 0) }}</h3><p>{{ __('messages.completed') }}</p></div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
            <a href="{{ route('admin.stock-transfers.index', ['status' => 'completed']) }}" class="small-box-footer">{{ __('messages.view_completed') }} <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-danger">
            <div class="inner"><h3>{{ number_format($stats['cancelled'] ?? 0) }}</h3><p>{{ __('messages.cancelled') }}</p></div>
            <div class="icon"><i class="fas fa-times-circle"></i></div>
            <a href="{{ route('admin.stock-transfers.index', ['status' => 'cancelled']) }}" class="small-box-footer">{{ __('messages.view_cancelled') }} <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <h5 class="card-title mb-0"><i class="fas fa-exchange-alt text-primary me-2"></i>{{ __('messages.all_transfers') }} <span class="badge bg-primary ms-1">{{ $transfers->total() }}</span></h5>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <input type="text" id="searchInput" class="form-control form-control-sm" style="width:200px;" placeholder="{{ __('messages.search') }}...">
            <select id="statusFilter" class="form-select form-select-sm" style="width:120px;">
                <option value="">{{ __('messages.all_status') }}</option>
                <option value="draft">{{ __('messages.draft') }}</option>
                <option value="pending">{{ __('messages.pending') }}</option>
                <option value="in_transit">{{ __('messages.in_transit') }}</option>
                <option value="completed">{{ __('messages.completed') }}</option>
                <option value="cancelled">{{ __('messages.cancelled') }}</option>
            </select>
            @can('stock_transfer.create')
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fas fa-plus-circle me-1"></i> {{ __('messages.new_transfer') }}
            </button>
            @endcan
            @can('stock_transfer.export')
            <button class="btn btn-success btn-sm" id="exportBtn"><i class="fas fa-file-excel me-1"></i> {{ __('messages.export') }}</button>
            @endcan
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:35px;">#</th>
                        <th>{{ __('messages.transfer_number') }}</th>
                        <th>{{ __('messages.from') }}</th>
                        <th>{{ __('messages.to') }}</th>
                        <th>{{ __('messages.status') }}</th>
                        <th>{{ __('messages.date') }}</th>
                        <th>{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($transfers as $transfer)
                    <tr data-id="{{ $transfer->id }}" data-status="{{ $transfer->status }}">
                        <td>{{ $loop->iteration }}</td>
                        <td><span class="fw-bold">{{ $transfer->transfer_number }}</span></td>
                        <td>{{ $transfer->fromWarehouse->name ?? 'N/A' }}</td>
                        <td>{{ $transfer->toWarehouse->name ?? 'N/A' }}</td>
                        <td>
                            @php $statusColors = ['draft'=>'secondary','pending'=>'warning','in_transit'=>'info','completed'=>'success','cancelled'=>'danger']; @endphp
                            <span class="badge bg-{{ $statusColors[$transfer->status] ?? 'secondary' }}">{{ __('messages.' . $transfer->status) }}</span>
                        </td>
                        <td>{{ $transfer->transfer_date ? $transfer->transfer_date->format('M d, Y') : 'N/A' }}</td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                @can('stock_transfer.view')
                                <button class="btn btn-outline-primary btn-sm view-btn" data-id="{{ $transfer->id }}" title="{{ __('messages.view') }}"><i class="fas fa-eye"></i></button>
                                @endcan
                                @can('stock_transfer.edit')
                                @if($transfer->status == 'draft')
                                <button class="btn btn-outline-warning btn-sm edit-btn" data-id="{{ $transfer->id }}" title="{{ __('messages.edit') }}"><i class="fas fa-edit"></i></button>
                                @endif
                                @endcan
                                @can('stock_transfer.approve')
                                @if($transfer->status == 'draft')
                                <button class="btn btn-outline-success btn-sm approve-btn" data-id="{{ $transfer->id }}" title="{{ __('messages.approve') }}"><i class="fas fa-check"></i></button>
                                @endif
                                @endcan
                                @can('stock_transfer.complete')
                                @if(in_array($transfer->status, ['pending', 'in_transit']))
                                <button class="btn btn-outline-primary btn-sm complete-btn" data-id="{{ $transfer->id }}" title="{{ __('messages.complete') }}"><i class="fas fa-check-double"></i></button>
                                @endif
                                @endcan
                                @can('stock_transfer.cancel')
                                @if(in_array($transfer->status, ['draft', 'pending', 'in_transit']))
                                <button class="btn btn-outline-danger btn-sm cancel-btn" data-id="{{ $transfer->id }}" title="{{ __('messages.cancel') }}"><i class="fas fa-times"></i></button>
                                @endif
                                @endcan
                                @can('stock_transfer.delete')
                                @if(in_array($transfer->status, ['draft', 'cancelled']))
                                <button class="btn btn-outline-danger btn-sm delete-btn" data-id="{{ $transfer->id }}" title="{{ __('messages.delete') }}"><i class="fas fa-trash"></i></button>
                                @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7"><div class="text-center py-5"><i class="fas fa-exchange-alt fa-4x text-muted mb-3 d-block"></i><h5 class="fw-bold">{{ __('messages.no_stock_transfers_found') }}</h5></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div><span class="text-muted small">{{ __('messages.showing') }} {{ $transfers->firstItem() ?? 0 }} {{ __('messages.to') }} {{ $transfers->lastItem() ?? 0 }} {{ __('messages.of') }} {{ $transfers->total() }} {{ __('messages.entries') }}</span></div>
        <div>{{ $transfers->links() }}</div>
    </div>
</div>

<!-- CREATE MODAL -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width:700px;">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="fas fa-plus-circle me-2"></i> {{ __('messages.new_stock_transfer') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="createForm" action="{{ route('admin.stock-transfers.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div id="createFormErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.from_warehouse') }} <span class="text-danger">*</span></label>
                            <select name="from_warehouse_id" class="form-select" required>
                                <option value="">{{ __('messages.select_warehouse') }}</option>
                                @foreach($warehouses ?? [] as $wh)
                                    <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.to_warehouse') }} <span class="text-danger">*</span></label>
                            <select name="to_warehouse_id" class="form-select" required>
                                <option value="">{{ __('messages.select_warehouse') }}</option>
                                @foreach($warehouses ?? [] as $wh)
                                    <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.transfer_date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="transfer_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.notes') }}</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="{{ __('messages.additional_notes') }}"></textarea>
                        </div>
                        <div class="col-12">
                            <hr>
                            <h6 class="fw-bold"><i class="fas fa-boxes me-2"></i>{{ __('messages.items') }}</h6>
                            <div id="createItems">
                                <div class="row g-2 item-row align-items-end">
                                    <div class="col-6">
                                        <select name="items[0][product_id]" class="form-select product-select" required>
                                            <option value="">{{ __('messages.product') }}</option>
                                            @foreach($products ?? [] as $prod)
                                                <option value="{{ $prod->id }}">{{ $prod->name }} ({{ $prod->sku }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-4">
                                        <input type="number" name="items[0][quantity]" class="form-control" placeholder="{{ __('messages.qty') }}" step="0.01" min="0.01" required>
                                    </div>
                                    <div class="col-2">
                                        <button type="button" class="btn btn-danger btn-sm remove-item-btn" style="display:none;"><i class="fas fa-trash"></i></button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-secondary mt-2" id="addItemBtn"><i class="fas fa-plus me-1"></i> {{ __('messages.add_item') }}</button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> {{ __('messages.cancel') }}</button>
                    <button type="submit" class="btn btn-primary" id="createSubmitBtn"><i class="fas fa-save me-1"></i> {{ __('messages.create_transfer') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- VIEW MODAL -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width:700px;">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title text-white"><i class="fas fa-eye me-2"></i> {{ __('messages.transfer_details') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewModalBody"><div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">{{ __('messages.loading') }}...</span></div></div></div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.close') }}</button></div>
        </div>
    </div>
</div>

<!-- EDIT MODAL -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width:700px;">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title text-dark"><i class="fas fa-edit me-2"></i> {{ __('messages.edit_transfer') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body" id="editModalBody">
                    <div id="editFormErrors" class="alert alert-danger d-none"></div>
                    <div class="text-center py-5"><div class="spinner-border text-warning" role="status"><span class="visually-hidden">{{ __('messages.loading') }}...</span></div></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> {{ __('messages.cancel') }}</button>
                    <button type="submit" class="btn btn-warning" id="editSubmitBtn"><i class="fas fa-save me-1"></i> {{ __('messages.update_transfer') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- DELETE MODAL -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:560px;">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white"><i class="fas fa-exclamation-triangle me-2"></i> {{ __('messages.confirm_delete') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-trash fa-4x text-danger mb-3 d-block"></i>
                <p class="fw-bold">{{ __('messages.are_you_sure_delete_transfer') }}</p>
                <p class="text-muted small">{{ __('messages.this_action_cannot_be_undone') }}</p>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> {{ __('messages.cancel') }}</button>
                <button type="submit" class="btn btn-danger" id="deleteSubmitBtn" form="deleteForm"><i class="fas fa-trash me-1"></i> {{ __('messages.delete') }}</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
window.trans = {
    loading: @json(__('messages.loading')),
    close: @json(__('messages.close')),
    cancel: @json(__('messages.cancel')),
    delete: @json(__('messages.delete')),
    product: @json(__('messages.product')),
    qty: @json(__('messages.qty')),
    items: @json(__('messages.items')),
    transfer_number: @json(__('messages.transfer_number')),
    status: @json(__('messages.status')),
    from: @json(__('messages.from')),
    to: @json(__('messages.to')),
    transfer_date: @json(__('messages.transfer_date')),
    notes: @json(__('messages.notes')),
    draft: @json(__('messages.draft')),
    pending: @json(__('messages.pending')),
    in_transit: @json(__('messages.in_transit')),
    completed: @json(__('messages.completed')),
    cancelled: @json(__('messages.cancelled')),
    from_warehouse: @json(__('messages.from_warehouse')),
    to_warehouse: @json(__('messages.to_warehouse')),
    select_warehouse: @json(__('messages.select_warehouse')),
    add_item: @json(__('messages.add_item')),
    create_transfer: @json(__('messages.create_transfer')),
    update_transfer: @json(__('messages.update_transfer')),
    error_title: @json(__('messages.error')),
    failed_to_load: @json(__('messages.failed_to_load')),
    failed_to_load_details: @json(__('messages.failed_to_load_details')),
    failed_to_create: @json(__('messages.failed_to_create')),
    failed_to_update: @json(__('messages.failed_to_update')),
    failed_to_delete: @json(__('messages.failed_to_delete')),
    failed_to_approve: @json(__('messages.failed_to_approve')),
    failed_to_complete: @json(__('messages.failed_to_complete')),
    failed_to_cancel: @json(__('messages.failed_to_cancel')),
    created: @json(__('messages.created')),
    updated: @json(__('messages.updated')),
    deleted: @json(__('messages.deleted')),
    approving: @json(__('messages.approving')),
    completing: @json(__('messages.completing')),
    cancelling: @json(__('messages.cancelling')),
    creating: @json(__('messages.creating')),
    updating: @json(__('messages.updating')),
    deleting: @json(__('messages.deleting')),
    are_you_sure: @json(__('messages.are_you_sure')),
    wont_be_able_to_revert: @json(__('messages.wont_be_able_to_revert')),
    yes_delete: @json(__('messages.yes_delete')),
    approve_transfer_q: @json(__('messages.approve_transfer_q')),
    approve_transfer_warning: @json(__('messages.approve_transfer_warning')),
    yes_approve: @json(__('messages.yes_approve')),
    complete_transfer_q: @json(__('messages.complete_transfer_q')),
    complete_transfer_warning: @json(__('messages.complete_transfer_warning')),
    yes_complete: @json(__('messages.yes_complete')),
    cancel_transfer_q: @json(__('messages.cancel_transfer_q')),
    cancel_transfer_warning: @json(__('messages.cancel_transfer_warning')),
    yes_cancel: @json(__('messages.yes_cancel')),
};
$(document).ready(function() {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    function showErrors(container, errors) {
        container.removeClass('d-none');
        var html = '<ul class="mb-0">';
        $.each(errors, function(key, messages) {
            $.each(messages, function(i, msg) { html += '<li>' + msg + '</li>'; });
        });
        html += '</ul>';
        container.html(html);
        container[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    function clearErrors(container) { container.addClass('d-none').empty(); }

    var productOptionsHtml = `@foreach($products ?? [] as $prod)<option value="{{ $prod->id }}">{{ $prod->name }} ({{ $prod->sku }})</option>@endforeach`;

    // ADD/REMOVE ITEMS (Create)
    var itemIndex = 1;
    $('#addItemBtn').on('click', function() {
        var html = `
            <div class="row g-2 item-row align-items-end mt-2">
                <div class="col-6">
                    <select name="items[${itemIndex}][product_id]" class="form-select product-select" required>
                        <option value="">${trans.product}</option>
                        ${productOptionsHtml}
                    </select>
                </div>
                <div class="col-4">
                    <input type="number" name="items[${itemIndex}][quantity]" class="form-control" placeholder="${trans.qty}" step="0.01" min="0.01" required>
                </div>
                <div class="col-2">
                    <button type="button" class="btn btn-danger btn-sm remove-item-btn"><i class="fas fa-trash"></i></button>
                </div>
            </div>
        `;
        $('#createItems').append(html);
        if ($('#createItems .item-row').length > 1) { $('#createItems .remove-item-btn').show(); }
        itemIndex++;
    });

    $(document).on('click', '.remove-item-btn', function() {
        $(this).closest('.item-row').remove();
        if ($('#createItems .item-row').length <= 1) { $('#createItems .remove-item-btn').hide(); }
    });
    setTimeout(() => $('#createItems .remove-item-btn').hide(), 100);

    // VIEW
    @can('stock_transfer.view')
    $(document).on('click', '.view-btn', function() {
        var id = $(this).data('id');
        var body = $('#viewModalBody');
        body.html(`<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">${trans.loading}...</span></div></div>`);
        $('#viewModal').modal('show');
        $.ajax({
            url: '/admin/stock-transfers/' + id, type: 'GET', dataType: 'json',
            success: function(r) {
                var transfer = r;
                var items = transfer.items || [];
                var html = `
                    <div class="row g-3">
                        <div class="col-md-6"><label class="text-muted small fw-bold">${trans.transfer_number}</label><p class="fw-bold mb-0">${transfer.transfer_number}</p></div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">${trans.status}</label><p class="mb-0"><span class="badge bg-${transfer.status == 'completed' ? 'success' : (transfer.status == 'draft' ? 'secondary' : (transfer.status == 'pending' ? 'warning' : (transfer.status == 'in_transit' ? 'info' : 'danger')))}">${trans[transfer.status] || transfer.status}</span></p></div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">${trans.from}</label><p class="mb-0">${transfer.from_warehouse ? transfer.from_warehouse.name : 'N/A'}</p></div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">${trans.to}</label><p class="mb-0">${transfer.to_warehouse ? transfer.to_warehouse.name : 'N/A'}</p></div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">${trans.transfer_date}</label><p class="mb-0">${new Date(transfer.transfer_date).toLocaleDateString()}</p></div>
                        ${transfer.notes ? `<div class="col-12"><label class="text-muted small fw-bold">${trans.notes}</label><p class="mb-0">${transfer.notes}</p></div>` : ''}
                        <div class="col-12"><hr><h6 class="fw-bold text-primary"><i class="fas fa-boxes me-2"></i>${trans.items}</h6>
                        <table class="table table-sm table-bordered">
                            <thead><tr><th>#</th><th>${trans.product}</th><th class="text-end">${trans.qty}</th></tr></thead>
                            <tbody>${items.map(function(item, idx) {
                                return `<tr><td>${idx+1}</td><td>${item.product ? item.product.name : 'N/A'}</td><td class="text-end">${item.quantity}</td></tr>`;
                            }).join('')}</tbody>
                        </table></div>
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

    // EDIT
    @can('stock_transfer.edit')
    $(document).on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        var body = $('#editModalBody');
        var form = $('#editForm');
        form.attr('action', '/admin/stock-transfers/' + id);
        body.html(`<div id="editFormErrors" class="alert alert-danger d-none"></div><div class="text-center py-5"><div class="spinner-border text-warning" role="status"><span class="visually-hidden">${trans.loading}...</span></div></div>`);
        $('#editModal').modal('show');
        $.ajax({
            url: '/admin/stock-transfers/' + id + '/edit', type: 'GET', dataType: 'json',
            success: function(response) {
                if (!response.success) {
                    body.html(`<div id="editFormErrors" class="alert alert-danger">${response.error || trans.failed_to_load}</div>`);
                    return;
                }
                var transfer = response.transfer;
                var warehouses = response.warehouses || [];
                var products = response.products || [];
                var items = transfer.items || [];

                var warehouseOptions = warehouses.map(w => `<option value="${w.id}" ${transfer.from_warehouse_id == w.id ? 'selected' : ''}>${w.name}</option>`).join('');
                var warehouseOptionsTo = warehouses.map(w => `<option value="${w.id}" ${transfer.to_warehouse_id == w.id ? 'selected' : ''}>${w.name}</option>`).join('');
                var productOptions = products.map(p => `<option value="${p.id}">${p.name} (${p.sku})</option>`).join('');

                var itemsHtml = items.map(function(item, idx) {
                    return `
                        <div class="row g-2 item-row align-items-end">
                            <div class="col-6">
                                <select name="items[${idx}][product_id]" class="form-select" required>
                                    <option value="">${trans.product}</option>
                                    ${products.map(p => `<option value="${p.id}" ${item.product_id == p.id ? 'selected' : ''}>${p.name} (${p.sku})</option>`).join('')}
                                </select>
                            </div>
                            <div class="col-4">
                                <input type="number" name="items[${idx}][quantity]" class="form-control" placeholder="${trans.qty}" step="0.01" min="0.01" value="${item.quantity}" required>
                            </div>
                            <div class="col-2">
                                <button type="button" class="btn btn-danger btn-sm remove-item-btn ${items.length <= 1 ? 'd-none' : ''}"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                    `;
                }).join('');

                body.html(`
                    <div id="editFormErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">${trans.from_warehouse} <span class="text-danger">*</span></label>
                            <select name="from_warehouse_id" class="form-select" required>${warehouseOptions}</select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">${trans.to_warehouse} <span class="text-danger">*</span></label>
                            <select name="to_warehouse_id" class="form-select" required>${warehouseOptionsTo}</select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">${trans.transfer_date} <span class="text-danger">*</span></label>
                            <input type="date" name="transfer_date" class="form-control" value="${transfer.transfer_date ? new Date(transfer.transfer_date).toISOString().split('T')[0] : ''}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">${trans.notes}</label>
                            <textarea name="notes" class="form-control" rows="2">${transfer.notes || ''}</textarea>
                        </div>
                        <div class="col-12">
                            <hr>
                            <h6 class="fw-bold"><i class="fas fa-boxes me-2"></i>${trans.items}</h6>
                            <div id="editItems">${itemsHtml}</div>
                            <button type="button" class="btn btn-sm btn-secondary mt-2" id="editAddItemBtn"><i class="fas fa-plus me-1"></i> ${trans.add_item}</button>
                        </div>
                    </div>
                `);

                var editItemIndex = items.length;
                $('#editAddItemBtn').off('click').on('click', function() {
                    var html = `
                        <div class="row g-2 item-row align-items-end mt-2">
                            <div class="col-6">
                                <select name="items[${editItemIndex}][product_id]" class="form-select" required>
                                    <option value="">${trans.product}</option>
                                    ${productOptions}
                                </select>
                            </div>
                            <div class="col-4">
                                <input type="number" name="items[${editItemIndex}][quantity]" class="form-control" placeholder="${trans.qty}" step="0.01" min="0.01" required>
                            </div>
                            <div class="col-2">
                                <button type="button" class="btn btn-danger btn-sm remove-item-btn"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                    `;
                    $('#editItems').append(html);
                    editItemIndex++;
                });
                $(document).on('click', '.remove-item-btn', function() {
                    $(this).closest('.item-row').remove();
                    if ($('#editItems .item-row').length <= 1) { $('#editItems .remove-item-btn').addClass('d-none'); }
                });
                if ($('#editItems .item-row').length <= 1) { $('#editItems .remove-item-btn').addClass('d-none'); }
            },
            error: function() {
                body.html(`<div id="editFormErrors" class="alert alert-danger">${trans.failed_to_load}</div>`);
            }
        });
    });
    @endcan

    // CREATE
    @can('stock_transfer.create')
    $('#createForm').on('submit', function(e) {
        e.preventDefault();
        var btn = $('#createSubmitBtn');
        var errorContainer = $('#createFormErrors');
        clearErrors(errorContainer);
        btn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm me-1"></span> ${trans.creating}...`);
        $.ajax({
            url: $(this).attr('action'), type: 'POST', data: $(this).serialize(), dataType: 'json',
            success: function(r) {
                if (r.success) {
                    $('#createModal').modal('hide');
                    Swal.fire({ icon: 'success', title: trans.created, text: r.message, timer: 2000 }).then(() => location.reload());
                } else {
                    btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.create_transfer}`);
                    if (r.errors) showErrors(errorContainer, r.errors);
                    else Swal.fire({ icon: 'error', title: trans.error_title, text: r.message || trans.failed_to_create });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.create_transfer}`);
                if (xhr.status === 422) showErrors(errorContainer, xhr.responseJSON.errors);
                else Swal.fire({ icon: 'error', title: trans.error_title, text: trans.failed_to_create });
            }
        });
    });
    @endcan

    // UPDATE
    @can('stock_transfer.edit')
    $('#editForm').on('submit', function(e) {
        e.preventDefault();
        var btn = $('#editSubmitBtn');
        var errorContainer = $('#editFormErrors');
        clearErrors(errorContainer);
        btn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm me-1"></span> ${trans.updating}...`);
        $.ajax({
            url: $(this).attr('action'), type: 'POST', data: $(this).serialize(), dataType: 'json',
            success: function(r) {
                if (r.success) {
                    $('#editModal').modal('hide');
                    Swal.fire({ icon: 'success', title: trans.updated, text: r.message, timer: 2000 }).then(() => location.reload());
                } else {
                    btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.update_transfer}`);
                    if (r.errors) showErrors(errorContainer, r.errors);
                    else Swal.fire({ icon: 'error', title: trans.error_title, text: r.message || trans.failed_to_update });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.update_transfer}`);
                if (xhr.status === 422) showErrors(errorContainer, xhr.responseJSON.errors);
                else Swal.fire({ icon: 'error', title: trans.error_title, text: trans.failed_to_update });
            }
        });
    });
    @endcan

    // DELETE
    @can('stock_transfer.delete')
    $(document).on('click', '.delete-btn', function() {
        var id = $(this).data('id');
        $('#deleteForm').attr('action', '/admin/stock-transfers/' + id);
        Swal.fire({
            title: trans.are_you_sure,
            text: trans.wont_be_able_to_revert,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: trans.yes_delete,
            cancelButtonText: trans.cancel
        }).then((result) => { if (result.isConfirmed) { $('#deleteModal').modal('show'); } });
    });
    $('#deleteForm').on('submit', function(e) {
        e.preventDefault();
        var btn = $('#deleteSubmitBtn');
        btn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm me-1"></span> ${trans.deleting}...`);
        $.ajax({
            url: $(this).attr('action'), type: 'POST',
            data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
            success: function(r) {
                if (r.success) {
                    $('#deleteModal').modal('hide');
                    Swal.fire({ icon: 'success', title: trans.deleted, text: r.message, timer: 1500 }).then(() => location.reload());
                } else {
                    btn.prop('disabled', false).html(`<i class="fas fa-trash me-1"></i> ${trans.delete}`);
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

    // APPROVE
    @can('stock_transfer.approve')
    $(document).on('click', '.approve-btn', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: trans.approve_transfer_q,
            text: trans.approve_transfer_warning,
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
                    url: '/admin/stock-transfers/' + id + '/approve', type: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(r) {
                        if (r.success) {
                            Swal.fire({ icon: 'success', title: trans.approved_alert || trans.updated, text: r.message, timer: 1500 }).then(() => location.reload());
                        } else {
                            Swal.fire(trans.error_title, r.message || trans.failed_to_approve, 'error');
                        }
                    },
                    error: function() { Swal.fire(trans.error_title, trans.failed_to_approve, 'error'); }
                });
            }
        });
    });
    @endcan

    // COMPLETE
    @can('stock_transfer.complete')
    $(document).on('click', '.complete-btn', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: trans.complete_transfer_q,
            text: trans.complete_transfer_warning,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0d6efd',
            cancelButtonColor: '#6c757d',
            confirmButtonText: trans.yes_complete,
            cancelButtonText: trans.cancel
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: trans.completing, allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                $.ajax({
                    url: '/admin/stock-transfers/' + id + '/complete', type: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(r) {
                        if (r.success) {
                            Swal.fire({ icon: 'success', title: trans.completed, text: r.message, timer: 1500 }).then(() => location.reload());
                        } else {
                            Swal.fire(trans.error_title, r.message || trans.failed_to_complete, 'error');
                        }
                    },
                    error: function() { Swal.fire(trans.error_title, trans.failed_to_complete, 'error'); }
                });
            }
        });
    });
    @endcan

    // CANCEL
    @can('stock_transfer.cancel')
    $(document).on('click', '.cancel-btn', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: trans.cancel_transfer_q,
            text: trans.cancel_transfer_warning,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: trans.yes_cancel,
            cancelButtonText: trans.cancel
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: trans.cancelling, allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                $.ajax({
                    url: '/admin/stock-transfers/' + id + '/cancel', type: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(r) {
                        if (r.success) {
                            Swal.fire({ icon: 'success', title: trans.cancelled, text: r.message, timer: 1500 }).then(() => location.reload());
                        } else {
                            Swal.fire(trans.error_title, r.message || trans.failed_to_cancel, 'error');
                        }
                    },
                    error: function() { Swal.fire(trans.error_title, trans.failed_to_cancel, 'error'); }
                });
            }
        });
    });
    @endcan

    // SEARCH & FILTER
    $('#searchInput').on('keyup', filterTable);
    $('#statusFilter').on('change', filterTable);

    function filterTable() {
        var search = $('#searchInput').val().toLowerCase();
        var status = $('#statusFilter').val();
        $('#tableBody tr').each(function() {
            var row = $(this);
            var text = row.text().toLowerCase();
            var show = true;
            if (search && !text.includes(search)) show = false;
            if (status && row.data('status') !== status) show = false;
            row.toggle(show);
        });
    }

    $('#exportBtn').on('click', function() { window.location.href = '{{ route("admin.stock-transfers.export") }}'; });
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