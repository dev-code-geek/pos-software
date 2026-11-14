@extends('layouts.admin')

@section('title', __('messages.quotations'))
@section('page-title', __('messages.quotation_management'))

@section('admin-content')
<div class="row g-3 mb-4">
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-primary">
            <div class="inner"><h3>{{ number_format($stats['total'] ?? 0) }}</h3><p>{{ __('messages.total') }}</p></div>
            <div class="icon"><i class="fas fa-file-invoice"></i></div>
            <a href="{{ route('admin.quotations.index') }}" class="small-box-footer">{{ __('messages.view_all') }} <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-secondary">
            <div class="inner"><h3>{{ number_format($stats['draft'] ?? 0) }}</h3><p>{{ __('messages.draft') }}</p></div>
            <div class="icon"><i class="fas fa-pen"></i></div>
            <a href="{{ route('admin.quotations.index', ['status' => 'draft']) }}" class="small-box-footer">{{ __('messages.view_draft') }} <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-info">
            <div class="inner"><h3>{{ number_format($stats['sent'] ?? 0) }}</h3><p>{{ __('messages.sent') }}</p></div>
            <div class="icon"><i class="fas fa-paper-plane"></i></div>
            <a href="{{ route('admin.quotations.index', ['status' => 'sent']) }}" class="small-box-footer">{{ __('messages.view_sent') }} <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-success">
            <div class="inner"><h3>{{ number_format($stats['accepted'] ?? 0) }}</h3><p>{{ __('messages.accepted') }}</p></div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
            <a href="{{ route('admin.quotations.index', ['status' => 'accepted']) }}" class="small-box-footer">{{ __('messages.view_accepted') }} <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-danger">
            <div class="inner"><h3>{{ number_format($stats['rejected'] ?? 0) }}</h3><p>{{ __('messages.rejected') }}</p></div>
            <div class="icon"><i class="fas fa-times-circle"></i></div>
            <a href="{{ route('admin.quotations.index', ['status' => 'rejected']) }}" class="small-box-footer">{{ __('messages.view_rejected') }} <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-primary">
            <div class="inner"><h3>{{ number_format($stats['converted'] ?? 0) }}</h3><p>{{ __('messages.converted') }}</p></div>
            <div class="icon"><i class="fas fa-exchange-alt"></i></div>
            <a href="{{ route('admin.quotations.index', ['status' => 'converted']) }}" class="small-box-footer">{{ __('messages.view_converted') }} <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <h5 class="card-title mb-0"><i class="fas fa-file-invoice text-primary me-2"></i>{{ __('messages.all_quotations') }} <span class="badge bg-primary ms-1">{{ $quotations->total() }}</span></h5>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <input type="text" id="searchInput" class="form-control form-control-sm" style="width:200px;" placeholder="{{ __('messages.search') }}...">
            <select id="statusFilter" class="form-select form-select-sm" style="width:120px;">
                <option value="">{{ __('messages.all_status') }}</option>
                <option value="draft">{{ __('messages.draft') }}</option>
                <option value="sent">{{ __('messages.sent') }}</option>
                <option value="accepted">{{ __('messages.accepted') }}</option>
                <option value="rejected">{{ __('messages.rejected') }}</option>
                <option value="expired">{{ __('messages.expired') }}</option>
                <option value="converted">{{ __('messages.converted') }}</option>
            </select>
            @can('quotation.create')
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createModal"><i class="fas fa-plus-circle me-1"></i> {{ __('messages.new_quotation') }}</button>
            @endcan
            @can('quotation.export')
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
                        <th>{{ __('messages.quotation_number') }}</th>
                        <th>{{ __('messages.customer') }}</th>
                        <th class="text-end">{{ __('messages.total') }}</th>
                        <th>{{ __('messages.status') }}</th>
                        <th>{{ __('messages.date') }}</th>
                        <th>{{ __('messages.valid_until') }}</th>
                        <th>{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($quotations as $quotation)
                    <tr data-id="{{ $quotation->id }}" data-status="{{ $quotation->status }}">
                        <td>{{ $loop->iteration }}</td>
                        <td><span class="fw-bold">{{ $quotation->quotation_number }}</span></td>
                        <td>{{ $quotation->customer->name ?? 'N/A' }}</td>
                        <td class="text-end"><span class="fw-bold text-primary">{{ $quotation->currency->code ?? '' }} {{ number_format($quotation->total, 2) }}</span></td>
                        <td>
                            @php
                                $colors = ['draft'=>'secondary','sent'=>'info','accepted'=>'success','rejected'=>'danger','expired'=>'warning','converted'=>'primary'];
                            @endphp
                            <span class="badge bg-{{ $colors[$quotation->status] ?? 'secondary' }}">{{ __('messages.' . $quotation->status) }}</span>
                        </td>
                        <td>{{ $quotation->quotation_date ? $quotation->quotation_date->format('M d, Y') : 'N/A' }}</td>
                        <td>{{ $quotation->valid_until ? $quotation->valid_until->format('M d, Y') : 'N/A' }}</td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                @can('quotation.view')
                                <button class="btn btn-outline-primary btn-sm view-btn" data-id="{{ $quotation->id }}" title="{{ __('messages.view') }}"><i class="fas fa-eye"></i></button>
                                @endcan
                                @can('quotation.edit')
                                @if($quotation->status == 'draft')
                                <button class="btn btn-outline-warning btn-sm edit-btn" data-id="{{ $quotation->id }}" title="{{ __('messages.edit') }}"><i class="fas fa-edit"></i></button>
                                @endif
                                @endcan
                                @can('quotation.send')
                                @if($quotation->status == 'draft')
                                <button class="btn btn-outline-info btn-sm send-btn" data-id="{{ $quotation->id }}" title="{{ __('messages.send') }}"><i class="fas fa-paper-plane"></i></button>
                                @endif
                                @endcan
                                @can('quotation.accept')
                                @if($quotation->status == 'sent')
                                <button class="btn btn-outline-success btn-sm accept-btn" data-id="{{ $quotation->id }}" title="{{ __('messages.accept') }}"><i class="fas fa-check"></i></button>
                                @endif
                                @endcan
                                @can('quotation.reject')
                                @if($quotation->status == 'sent')
                                <button class="btn btn-outline-danger btn-sm reject-btn" data-id="{{ $quotation->id }}" title="{{ __('messages.reject') }}"><i class="fas fa-times"></i></button>
                                @endif
                                @endcan
                                @can('quotation.convert_to_sale')
                                @if($quotation->status == 'accepted')
                                <button class="btn btn-outline-primary btn-sm convert-btn" data-id="{{ $quotation->id }}" title="{{ __('messages.convert_to_sale') }}"><i class="fas fa-exchange-alt"></i></button>
                                @endif
                                @endcan
                                @can('quotation.print')
                                <a href="{{ route('admin.quotations.print', $quotation->id) }}" class="btn btn-outline-secondary btn-sm" target="_blank" title="{{ __('messages.print') }}"><i class="fas fa-print"></i></a>
                                @endcan
                                @can('quotation.pdf')
                                <a href="{{ route('admin.quotations.pdf', $quotation->id) }}" class="btn btn-outline-secondary btn-sm" target="_blank" title="{{ __('messages.download_pdf') }}"><i class="fas fa-file-pdf"></i></a>
                                @endcan
                                @can('quotation.delete')
                                @if(in_array($quotation->status, ['draft', 'rejected', 'expired']))
                                <button class="btn btn-outline-danger btn-sm delete-btn" data-id="{{ $quotation->id }}" title="{{ __('messages.delete') }}"><i class="fas fa-trash"></i></button>
                                @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8"><div class="text-center py-5"><i class="fas fa-file-invoice fa-4x text-muted mb-3 d-block"></i><h5 class="fw-bold">{{ __('messages.no_quotations_found') }}</h5></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div><span class="text-muted small">{{ __('messages.showing') }} {{ $quotations->firstItem() ?? 0 }} {{ __('messages.to') }} {{ $quotations->lastItem() ?? 0 }} {{ __('messages.of') }} {{ $quotations->total() }} {{ __('messages.entries') }}</span></div>
        <div>{{ $quotations->links() }}</div>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl" style="max-width: 1050px;">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="fas fa-plus-circle me-2"></i> {{ __('messages.new_quotation') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="createForm" action="{{ route('admin.quotations.store') }}" method="POST">
                @csrf
                <div class="modal-body" style="max-height:70vh; overflow-y:auto;">
                    <div id="createFormErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">{{ __('messages.customer') }} <span class="text-danger">*</span></label>
                            <select name="customer_id" class="form-select" required>
                                <option value="">{{ __('messages.select_customer') }}</option>
                                @foreach($customers ?? [] as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">{{ __('messages.warehouse') }}</label>
                            <select name="warehouse_id" class="form-select">
                                <option value="">{{ __('messages.select_warehouse') }}</option>
                                @foreach($warehouses ?? [] as $wh)
                                    <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">{{ __('messages.currency') }} <span class="text-danger">*</span></label>
                            <select name="currency_id" class="form-select" required>
                                <option value="">{{ __('messages.select_currency') }}</option>
                                @foreach($currencies ?? [] as $cur)
                                    <option value="{{ $cur->id }}">{{ $cur->code }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">{{ __('messages.quotation_date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="quotation_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">{{ __('messages.valid_until') }}</label>
                            <input type="date" name="valid_until" class="form-control" value="{{ date('Y-m-d', strtotime('+30 days')) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">{{ __('messages.discount_type') }}</label>
                            <select name="discount_type" class="form-select">
                                <option value="percentage">{{ __('messages.percentage') }}</option>
                                <option value="fixed">{{ __('messages.fixed_amount') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">{{ __('messages.discount') }}</label>
                            <input type="number" name="discount" class="form-control" step="0.01" min="0" placeholder="0.00">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">{{ __('messages.tax_percent') }}</label>
                            <input type="number" name="tax" class="form-control" step="0.01" min="0" placeholder="0">
                        </div>
                        <div class="col-md-9">
                            <label class="form-label fw-bold">{{ __('messages.notes') }}</label>
                            <input type="text" name="notes" class="form-control" placeholder="{{ __('messages.customer_notes') }}">
                        </div>
                        <div class="col-12">
                            <hr>
                            <h6 class="fw-bold"><i class="fas fa-boxes me-2"></i>{{ __('messages.items') }}</h6>
                            <div class="row g-2 fw-bold small text-muted d-none d-md-flex mb-1">
                                <div class="col-3">{{ __('messages.product') }}</div>
                                <div class="col-2">{{ __('messages.qty') }}</div>
                                <div class="col-2">{{ __('messages.price') }}</div>
                                <div class="col-2">{{ __('messages.discount') }}</div>
                                <div class="col-2">{{ __('messages.total') }}</div>
                            </div>
                            <div id="createItems">
                                <div class="row g-2 item-row align-items-end">
                                    <div class="col-3">
                                        <select name="items[0][product_id]" class="form-select product-select" required>
                                            <option value="">{{ __('messages.product') }}</option>
                                            @foreach($products ?? [] as $prod)
                                                <option value="{{ $prod->id }}" data-price="{{ $prod->selling_price ?? 0 }}" data-stock="{{ $prod->quantity ?? 0 }}">{{ $prod->name }} ({{ $prod->sku }})</option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted stock-hint d-block mt-1"></small>
                                    </div>
                                    <div class="col-2"><input type="number" name="items[0][quantity]" class="form-control qty-input" placeholder="{{ __('messages.qty') }}" step="0.01" min="0.01" value="1" required></div>
                                    <div class="col-2"><input type="number" name="items[0][unit_price]" class="form-control price-input" placeholder="{{ __('messages.price') }}" step="0.01" min="0" readonly required></div>
                                    <div class="col-2"><input type="number" name="items[0][discount]" class="form-control discount-input" placeholder="{{ __('messages.disc') }}" step="0.01" min="0" value="0"></div>
                                    <div class="col-2"><input type="text" class="form-control total-display" placeholder="{{ __('messages.total') }}" readonly tabindex="-1"></div>
                                    <div class="col-1"><button type="button" class="btn btn-danger btn-sm remove-item-btn" style="display:none;"><i class="fas fa-trash"></i></button></div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-secondary mt-2" id="addItemBtn"><i class="fas fa-plus me-1"></i> {{ __('messages.add_item') }}</button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> {{ __('messages.cancel') }}</button>
                    <button type="submit" class="btn btn-primary" id="createSubmitBtn"><i class="fas fa-save me-1"></i> {{ __('messages.create_quotation') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Modal -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width:800px;">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title text-white"><i class="fas fa-eye me-2"></i> {{ __('messages.quotation_details') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewModalBody"><div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">{{ __('messages.loading') }}...</span></div></div></div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.close') }}</button></div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl" style="max-width:1050px;">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title text-dark"><i class="fas fa-edit me-2"></i> {{ __('messages.edit_quotation') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body" id="editModalBody"><div id="editFormErrors" class="alert alert-danger d-none"></div><div class="text-center py-5"><div class="spinner-border text-warning" role="status"><span class="visually-hidden">{{ __('messages.loading') }}...</span></div></div></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> {{ __('messages.cancel') }}</button>
                    <button type="submit" class="btn btn-warning" id="editSubmitBtn"><i class="fas fa-save me-1"></i> {{ __('messages.update_quotation') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:560px;">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white"><i class="fas fa-exclamation-triangle me-2"></i> {{ __('messages.confirm_delete') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-trash fa-4x text-danger mb-3 d-block"></i>
                <p class="fw-bold">{{ __('messages.are_you_sure_delete_quotation') }}</p>
                <p class="text-muted small">{{ __('messages.this_action_cannot_be_undone') }}</p>
                <form id="deleteForm" method="POST">@csrf @method('DELETE')</form>
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
    edit: @json(__('messages.edit')),
    view: @json(__('messages.view')),
    product: @json(__('messages.product')),
    qty: @json(__('messages.qty')),
    price: @json(__('messages.price')),
    discount: @json(__('messages.discount')),
    total: @json(__('messages.total')),
    subtotal: @json(__('messages.subtotal')),
    quotation_number: @json(__('messages.quotation_number')),
    customer: @json(__('messages.customer')),
    date: @json(__('messages.date')),
    valid_until: @json(__('messages.valid_until')),
    status: @json(__('messages.status')),
    notes: @json(__('messages.notes')),
    items: @json(__('messages.items')),
    draft: @json(__('messages.draft')),
    sent: @json(__('messages.sent')),
    accepted: @json(__('messages.accepted')),
    rejected: @json(__('messages.rejected')),
    expired: @json(__('messages.expired')),
    converted: @json(__('messages.converted')),
    in_stock: @json(__('messages.in_stock')),
    only_x_in_stock_exceeds: @json(__('messages.only_x_in_stock_exceeds')),
    created: @json(__('messages.created')),
    updated: @json(__('messages.updated')),
    deleted: @json(__('messages.deleted')),
    error_title: @json(__('messages.error')),
    something_went_wrong: @json(__('messages.something_went_wrong')),
    failed_to_load: @json(__('messages.failed_to_load')),
    failed_to_load_details: @json(__('messages.failed_to_load_details')),
    failed_to_create: @json(__('messages.failed_to_create')),
    failed_to_update: @json(__('messages.failed_to_update')),
    failed_to_delete: @json(__('messages.failed_to_delete')),
    are_you_sure: @json(__('messages.are_you_sure')),
    wont_be_able_to_revert: @json(__('messages.wont_be_able_to_revert')),
    yes_delete: @json(__('messages.yes_delete')),
    creating: @json(__('messages.creating')),
    updating: @json(__('messages.updating')),
    deleting: @json(__('messages.deleting')),
    create_quotation: @json(__('messages.create_quotation')),
    update_quotation: @json(__('messages.update_quotation')),
    send_quotation_q: @json(__('messages.send_quotation_q')),
    send_quotation_warning: @json(__('messages.send_quotation_warning')),
    yes_send: @json(__('messages.yes_send')),
    sending: @json(__('messages.sending')),
    sent_alert: @json(__('messages.sent_alert')),
    failed_to_send: @json(__('messages.failed_to_send')),
    accept_quotation_q: @json(__('messages.accept_quotation_q')),
    accept_quotation_warning: @json(__('messages.accept_quotation_warning')),
    yes_accept: @json(__('messages.yes_accept')),
    accepting: @json(__('messages.accepting')),
    accepted_alert: @json(__('messages.accepted_alert')),
    failed_to_accept: @json(__('messages.failed_to_accept')),
    reject_quotation_q: @json(__('messages.reject_quotation_q')),
    reason_optional: @json(__('messages.reason_optional')),
    enter_reason: @json(__('messages.enter_reason')),
    yes_reject: @json(__('messages.yes_reject')),
    rejecting: @json(__('messages.rejecting')),
    rejected_alert: @json(__('messages.rejected_alert')),
    failed_to_reject: @json(__('messages.failed_to_reject')),
    convert_to_sale: @json(__('messages.convert_to_sale')),
    convert_to_sale_warning: @json(__('messages.convert_to_sale_warning')),
    payment_method: @json(__('messages.payment_method')),
    credit: @json(__('messages.credit')),
    cash: @json(__('messages.cash')),
    card: @json(__('messages.card')),
    mobile_money: @json(__('messages.mobile_money')),
    bank_transfer: @json(__('messages.bank_transfer')),
    cheque: @json(__('messages.cheque')),
    yes_convert: @json(__('messages.yes_convert')),
    converting: @json(__('messages.converting')),
    converted_alert: @json(__('messages.converted_alert')),
    failed_to_convert: @json(__('messages.failed_to_convert')),
};
$(document).ready(function() {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    function showErrors(container, errors) {
        container.removeClass('d-none');
        var html = '<ul class="mb-0">';
        $.each(errors, function(key, messages) { $.each(messages, function(i, msg) { html += '<li>' + msg + '</li>'; }); });
        html += '</ul>';
        container.html(html);
        container[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    function clearErrors(container) { container.addClass('d-none').empty(); }

    function calcRowTotal(row) {
        var qty = parseFloat(row.find('.qty-input').val()) || 0;
        var price = parseFloat(row.find('.price-input').val()) || 0;
        var discount = parseFloat(row.find('.discount-input').val()) || 0;
        var total = (qty * price) - discount;
        if (total < 0) total = 0;
        row.find('.total-display').val(total.toFixed(2));
    }
    function checkStockHint(row) {
        var stock = parseFloat(row.find('.product-select').find('option:selected').data('stock'));
        var hint = row.find('.stock-hint');
        if (isNaN(stock)) { hint.text(''); return; }
        var qty = parseFloat(row.find('.qty-input').val()) || 0;
        if (qty > stock) {
            hint.text(trans.only_x_in_stock_exceeds.replace(':stock', stock)).addClass('text-danger').removeClass('text-muted');
            row.find('.qty-input').addClass('border-danger');
        } else {
            hint.text(trans.in_stock + ': ' + stock).addClass('text-muted').removeClass('text-danger');
            row.find('.qty-input').removeClass('border-danger');
        }
    }
    $(document).on('change', '.product-select', function() {
        var row = $(this).closest('.item-row');
        var price = $(this).find('option:selected').data('price') || 0;
        row.find('.price-input').val(parseFloat(price).toFixed(2));
        if (!row.find('.qty-input').val()) row.find('.qty-input').val(1);
        calcRowTotal(row); checkStockHint(row);
    });
    $(document).on('input', '.qty-input, .discount-input', function() {
        var row = $(this).closest('.item-row');
        calcRowTotal(row); checkStockHint(row);
    });

    var itemIndex = 1;
    var productOptionsHtml = `@foreach($products ?? [] as $prod)<option value="{{ $prod->id }}" data-price="{{ $prod->selling_price ?? 0 }}" data-stock="{{ $prod->quantity ?? 0 }}">{{ $prod->name }} ({{ $prod->sku }})</option>@endforeach`;
    $('#addItemBtn').on('click', function() {
        var html = `
            <div class="row g-2 item-row align-items-end mt-2">
                <div class="col-3">
                    <select name="items[${itemIndex}][product_id]" class="form-select product-select" required>
                        <option value="">${trans.product}</option>
                        ${productOptionsHtml}
                    </select>
                    <small class="text-muted stock-hint d-block mt-1"></small>
                </div>
                <div class="col-2"><input type="number" name="items[${itemIndex}][quantity]" class="form-control qty-input" placeholder="${trans.qty}" step="0.01" min="0.01" value="1" required></div>
                <div class="col-2"><input type="number" name="items[${itemIndex}][unit_price]" class="form-control price-input" placeholder="${trans.price}" step="0.01" min="0" readonly required></div>
                <div class="col-2"><input type="number" name="items[${itemIndex}][discount]" class="form-control discount-input" placeholder="${trans.discount}" step="0.01" min="0" value="0"></div>
                <div class="col-2"><input type="text" class="form-control total-display" placeholder="${trans.total}" readonly tabindex="-1"></div>
                <div class="col-1"><button type="button" class="btn btn-danger btn-sm remove-item-btn"><i class="fas fa-trash"></i></button></div>
            </div>
        `;
        $('#createItems').append(html);
        $('#createItems .remove-item-btn').show();
        itemIndex++;
    });
    $(document).on('click', '.remove-item-btn', function() {
        $(this).closest('.item-row').remove();
        var container = $(this).closest('#createItems, #editItems');
        if (container.find('.item-row').length <= 1) container.find('.remove-item-btn').hide();
    });
    setTimeout(() => $('#createItems .remove-item-btn').hide(), 100);

    @can('quotation.view')
    $(document).on('click', '.view-btn', function() {
        var id = $(this).data('id');
        var body = $('#viewModalBody');
        body.html(`<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">${trans.loading}...</span></div></div>`);
        $('#viewModal').modal('show');
        $.ajax({
            url: '/admin/quotations/' + id, type: 'GET', dataType: 'json',
            success: function(r) {
                var q = r;
                var items = q.items || [];
                var html = `
                    <div class="row g-3">
                        <div class="col-md-4"><label class="text-muted small fw-bold">${trans.quotation_number}</label><p class="fw-bold mb-0">${q.quotation_number}</p></div>
                        <div class="col-md-4"><label class="text-muted small fw-bold">${trans.customer}</label><p class="mb-0">${q.customer ? q.customer.name : 'N/A'}</p></div>
                        <div class="col-md-4"><label class="text-muted small fw-bold">${trans.status}</label><p class="mb-0"><span class="badge bg-${['secondary','info','success','danger','warning','primary'][['draft','sent','accepted','rejected','expired','converted'].indexOf(q.status)]}">${trans[q.status] || q.status}</span></p></div>
                        <div class="col-md-4"><label class="text-muted small fw-bold">${trans.date}</label><p class="mb-0">${new Date(q.quotation_date).toLocaleDateString()}</p></div>
                        <div class="col-md-4"><label class="text-muted small fw-bold">${trans.valid_until}</label><p class="mb-0">${q.valid_until ? new Date(q.valid_until).toLocaleDateString() : 'N/A'}</p></div>
                        <div class="col-md-4"><label class="text-muted small fw-bold">${trans.total}</label><p class="fw-bold text-primary mb-0">${q.currency ? q.currency.code : ''} ${parseFloat(q.total).toFixed(2)}</p></div>
                        ${q.notes ? `<div class="col-12"><label class="text-muted small fw-bold">${trans.notes}</label><p class="mb-0">${q.notes}</p></div>` : ''}
                        <div class="col-12"><hr><h6 class="fw-bold text-primary"><i class="fas fa-boxes me-2"></i>${trans.items}</h6>
                        <table class="table table-sm table-bordered">
                            <thead><tr><th>#</th><th>${trans.product}</th><th class="text-end">${trans.qty}</th><th class="text-end">${trans.price}</th><th class="text-end">${trans.discount}</th><th class="text-end">${trans.total}</th></tr></thead>
                            <tbody>
                                ${items.map(function(item, idx) {
                                    return `<tr><td>${idx+1}</td><td>${item.product_name}</td><td class="text-end">${item.quantity}</td><td class="text-end">${parseFloat(item.unit_price).toFixed(2)}</td><td class="text-end">${parseFloat(item.discount || 0).toFixed(2)}</td><td class="text-end">${parseFloat(item.total).toFixed(2)}</td></tr>`;
                                }).join('')}
                            </tbody>
                        </table></div>
                    </div>
                `;
                body.html(html);
            },
            error: function(xhr) {
                body.html(`<div class="text-center py-5 text-danger"><i class="fas fa-exclamation-circle fa-4x mb-3 d-block"></i><h5>${trans.failed_to_load}</h5><p>${xhr.status} - ${xhr.statusText}</p></div>`);
            }
        });
    });
    @endcan

    @can('quotation.create')
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
                    btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.create_quotation}`);
                    if (r.errors) showErrors(errorContainer, r.errors);
                    else Swal.fire({ icon: 'error', title: trans.error_title, text: r.message || trans.something_went_wrong });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.create_quotation}`);
                if (xhr.status === 422) showErrors(errorContainer, xhr.responseJSON.errors);
                else Swal.fire({ icon: 'error', title: trans.error_title, text: trans.failed_to_create });
            }
        });
    });
    @endcan

    @can('quotation.edit')
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
                    btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.update_quotation}`);
                    if (r.errors) showErrors(errorContainer, r.errors);
                    else Swal.fire({ icon: 'error', title: trans.error_title, text: r.message || trans.something_went_wrong });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.update_quotation}`);
                if (xhr.status === 422) showErrors(errorContainer, xhr.responseJSON.errors);
                else Swal.fire({ icon: 'error', title: trans.error_title, text: trans.failed_to_update });
            }
        });
    });
    @endcan

    @can('quotation.delete')
    $(document).on('click', '.delete-btn', function() {
        var id = $(this).data('id');
        $('#deleteForm').attr('action', '/admin/quotations/' + id);
        Swal.fire({
            title: trans.are_you_sure, text: trans.wont_be_able_to_revert, icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#6c757d',
            confirmButtonText: trans.yes_delete, cancelButtonText: trans.cancel
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

    @can('quotation.send')
    $(document).on('click', '.send-btn', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: trans.send_quotation_q, text: trans.send_quotation_warning, icon: 'question',
            showCancelButton: true, confirmButtonColor: '#0dcaf0', cancelButtonColor: '#6c757d',
            confirmButtonText: trans.yes_send, cancelButtonText: trans.cancel
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: trans.sending, allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                $.ajax({
                    url: '/admin/quotations/' + id + '/send', type: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(r) {
                        if (r.success) Swal.fire({ icon: 'success', title: trans.sent_alert, text: r.message, timer: 1500 }).then(() => location.reload());
                        else Swal.fire(trans.error_title, r.message || trans.failed_to_send, 'error');
                    },
                    error: function() { Swal.fire(trans.error_title, trans.failed_to_send, 'error'); }
                });
            }
        });
    });
    @endcan

    @can('quotation.accept')
    $(document).on('click', '.accept-btn', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: trans.accept_quotation_q, text: trans.accept_quotation_warning, icon: 'question',
            showCancelButton: true, confirmButtonColor: '#198754', cancelButtonColor: '#6c757d',
            confirmButtonText: trans.yes_accept, cancelButtonText: trans.cancel
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: trans.accepting, allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                $.ajax({
                    url: '/admin/quotations/' + id + '/accept', type: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(r) {
                        if (r.success) Swal.fire({ icon: 'success', title: trans.accepted_alert, text: r.message, timer: 1500 }).then(() => location.reload());
                        else Swal.fire(trans.error_title, r.message || trans.failed_to_accept, 'error');
                    },
                    error: function() { Swal.fire(trans.error_title, trans.failed_to_accept, 'error'); }
                });
            }
        });
    });
    @endcan

    @can('quotation.reject')
    $(document).on('click', '.reject-btn', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: trans.reject_quotation_q,
            input: 'textarea',
            inputLabel: trans.reason_optional,
            inputPlaceholder: trans.enter_reason,
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: trans.yes_reject,
            cancelButtonText: trans.cancel
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: trans.rejecting, allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                $.ajax({
                    url: '/admin/quotations/' + id + '/reject', type: 'POST',
                    data: { _token: '{{ csrf_token() }}', reason: result.value || '' },
                    success: function(r) {
                        if (r.success) Swal.fire({ icon: 'success', title: trans.rejected_alert, text: r.message, timer: 1500 }).then(() => location.reload());
                        else Swal.fire(trans.error_title, r.message || trans.failed_to_reject, 'error');
                    },
                    error: function() { Swal.fire(trans.error_title, trans.failed_to_reject, 'error'); }
                });
            }
        });
    });
    @endcan

    @can('quotation.convert_to_sale')
    $(document).on('click', '.convert-btn', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: trans.convert_to_sale,
            html: trans.convert_to_sale_warning + '<br><br>' +
                  '<label class="form-label fw-bold text-start d-block">' + trans.payment_method + '</label>' +
                  '<select id="convertPaymentMethod" class="form-select">' +
                  '<option value="credit" selected>' + trans.credit + '</option>' +
                  '<option value="cash">' + trans.cash + '</option>' +
                  '<option value="card">' + trans.card + '</option>' +
                  '<option value="mobile">' + trans.mobile_money + '</option>' +
                  '<option value="bank_transfer">' + trans.bank_transfer + '</option>' +
                  '<option value="cheque">' + trans.cheque + '</option>' +
                  '</select>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0d6efd',
            cancelButtonColor: '#6c757d',
            confirmButtonText: trans.yes_convert,
            cancelButtonText: trans.cancel,
            preConfirm: () => document.getElementById('convertPaymentMethod').value
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: trans.converting, allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                $.ajax({
                    url: '/admin/quotations/' + id + '/convert-to-sale', type: 'POST',
                    data: { _token: '{{ csrf_token() }}', payment_method: result.value },
                    success: function(r) {
                        if (r.success) {
                            Swal.fire({ icon: 'success', title: trans.converted_alert, text: r.message, timer: 1500 }).then(() => {
                                if (r.invoice_url) window.location.href = r.invoice_url;
                                else if (r.sale_id) window.location.href = '/admin/sales/' + r.sale_id;
                                else location.reload();
                            });
                        } else Swal.fire(trans.error_title, r.message || r.error || trans.failed_to_convert, 'error');
                    },
                    error: function(xhr) { Swal.fire(trans.error_title, xhr.responseJSON?.error || trans.failed_to_convert, 'error'); }
                });
            }
        });
    });
    @endcan

    $('#searchInput').on('keyup', filterTable);
    $('#statusFilter').on('change', filterTable);
    function filterTable() {
        var search = $('#searchInput').val().toLowerCase();
        var status = $('#statusFilter').val();
        $('#tableBody tr').each(function() {
            var row = $(this);
            var show = true;
            if (search && !row.text().toLowerCase().includes(search)) show = false;
            if (status && row.data('status') !== status) show = false;
            row.toggle(show);
        });
    }

    $('#exportBtn').on('click', function() { window.location.href = '{{ route("admin.quotations.export") }}'; });
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