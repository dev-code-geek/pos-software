@extends('layouts.admin')

@section('title', __('messages.sales'))
@section('page-title', __('messages.sales_management'))

@section('admin-content')
<div class="row g-3 mb-4">
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-primary">
            <div class="inner"><h3>{{ number_format($stats['total'] ?? 0) }}</h3><p>{{ __('messages.total') }}</p></div>
            <div class="icon"><i class="fas fa-file-invoice-dollar"></i></div>
            <a href="{{ route('admin.sales.index') }}" class="small-box-footer">{{ __('messages.view_all') }} <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-warning text-dark">
            <div class="inner"><h3>{{ number_format($stats['pending'] ?? 0) }}</h3><p>{{ __('messages.pending') }}</p></div>
            <div class="icon"><i class="fas fa-clock"></i></div>
            <a href="{{ route('admin.sales.index', ['status' => 'pending']) }}" class="small-box-footer">{{ __('messages.view_pending') }} <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-info">
            <div class="inner"><h3>{{ number_format($stats['partial'] ?? 0) }}</h3><p>{{ __('messages.partial') }}</p></div>
            <div class="icon"><i class="fas fa-fill-drip"></i></div>
            <a href="{{ route('admin.sales.index', ['status' => 'partial']) }}" class="small-box-footer">{{ __('messages.view_partial') }} <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-success">
            <div class="inner"><h3>{{ number_format($stats['paid'] ?? 0) }}</h3><p>{{ __('messages.paid') }}</p></div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
            <a href="{{ route('admin.sales.index', ['status' => 'paid']) }}" class="small-box-footer">{{ __('messages.view_paid') }} <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-danger">
            <div class="inner"><h3>{{ number_format($stats['cancelled'] ?? 0) }}</h3><p>{{ __('messages.cancelled') }}</p></div>
            <div class="icon"><i class="fas fa-times-circle"></i></div>
            <a href="{{ route('admin.sales.index', ['status' => 'cancelled']) }}" class="small-box-footer">{{ __('messages.view_cancelled') }} <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-danger">
            <div class="inner"><h3>{{ number_format($stats['overdue'] ?? 0) }}</h3><p>{{ __('messages.overdue') }}</p></div>
            <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
            <a href="{{ route('admin.sales.index', ['status' => 'overdue']) }}" class="small-box-footer">{{ __('messages.view_overdue') }} <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <h5 class="card-title mb-0"><i class="fas fa-file-invoice-dollar text-primary me-2"></i>{{ __('messages.all_sales') }} <span class="badge bg-primary ms-1">{{ $sales->total() }}</span></h5>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <div class="input-group input-group-sm" style="width:200px;">
                <input type="text" id="searchInput" class="form-control" placeholder="{{ __('messages.search') }}...">
                <button class="btn btn-secondary" type="button" id="searchBtn"><i class="fas fa-search"></i></button>
            </div>
            <select id="statusFilter" class="form-select form-select-sm" style="width:120px;">
                <option value="">{{ __('messages.all_status') }}</option>
                <option value="pending">{{ __('messages.pending') }}</option>
                <option value="partial">{{ __('messages.partial') }}</option>
                <option value="paid">{{ __('messages.paid') }}</option>
                <option value="cancelled">{{ __('messages.cancelled') }}</option>
                <option value="overdue">{{ __('messages.overdue') }}</option>
            </select>
            @can('sale.create')
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createModal"><i class="fas fa-plus-circle me-1"></i> {{ __('messages.new_sale') }}</button>
            @endcan
            @can('sale.export')
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
                        <th>{{ __('messages.invoice_number') }}</th>
                        <th>{{ __('messages.customer') }}</th>
                        <th class="text-end">{{ __('messages.total') }}</th>
                        <th>{{ __('messages.payment_status') }}</th>
                        <th>{{ __('messages.date') }}</th>
                        <th style="width:200px;">{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($sales as $sale)
                    <tr data-id="{{ $sale->id }}" data-status="{{ $sale->payment_status }}">
                        <td>{{ $loop->iteration }}</td>
                        <td><span class="fw-bold">{{ $sale->invoice_number }}</span></td>
                        <td>{{ $sale->customer->name ?? 'N/A' }}</td>
                        <td class="text-end"><span class="fw-bold text-primary">{{ $sale->currency->code ?? '' }} {{ number_format($sale->total, 2) }}</span></td>
                        <td>
                            @php
                                $statusColors = ['pending' => 'warning', 'partial' => 'info', 'paid' => 'success', 'cancelled' => 'secondary', 'overdue' => 'danger'];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$sale->payment_status] ?? 'secondary' }}">{{ __('messages.' . $sale->payment_status) }}</span>
                        </td>
                        <td>{{ $sale->sale_date ? $sale->sale_date->format('M d, Y') : 'N/A' }}</td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                @can('sale.view')
                                <button class="btn btn-outline-primary btn-sm view-btn" data-id="{{ $sale->id }}" title="{{ __('messages.view') }}"><i class="fas fa-eye"></i></button>
                                @endcan
                                @can('sale.edit')
                                @if($sale->payment_status != 'paid' && $sale->payment_status != 'cancelled')
                                <button class="btn btn-outline-warning btn-sm edit-btn" data-id="{{ $sale->id }}" title="{{ __('messages.edit') }}"><i class="fas fa-edit"></i></button>
                                @endif
                                @endcan
                                @can('sale.pay')
                                @if($sale->payment_status != 'paid' && $sale->payment_status != 'cancelled')
                                <button class="btn btn-outline-success btn-sm pay-btn" data-id="{{ $sale->id }}" title="{{ __('messages.register_payment') }}"><i class="fas fa-money-bill-wave"></i></button>
                                @endif
                                @endcan
                                @can('sale.cancel')
                                @if($sale->payment_status != 'paid' && $sale->payment_status != 'cancelled')
                                <button class="btn btn-outline-danger btn-sm cancel-btn" data-id="{{ $sale->id }}" title="{{ __('messages.cancel') }}"><i class="fas fa-ban"></i></button>
                                @endif
                                @endcan
                                @can('sale.print')
                                <a href="{{ route('admin.sales.print', $sale->id) }}" class="btn btn-outline-secondary btn-sm" title="{{ __('messages.print_invoice') }}" target="_blank"><i class="fas fa-print"></i></a>
                                @endcan
                                @can('sale.delete')
                                @if($sale->payment_status == 'pending' || $sale->payment_status == 'cancelled')
                                <button class="btn btn-outline-danger btn-sm delete-btn" data-id="{{ $sale->id }}" title="{{ __('messages.delete') }}"><i class="fas fa-trash"></i></button>
                                @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7"><div class="text-center py-5"><i class="fas fa-file-invoice-dollar fa-4x text-muted mb-3 d-block"></i><h5 class="fw-bold">{{ __('messages.no_sales_found') }}</h5><p class="text-muted">{{ __('messages.click_new_sale') }}</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div><span class="text-muted small">{{ __('messages.showing') }} {{ $sales->firstItem() ?? 0 }} {{ __('messages.to') }} {{ $sales->lastItem() ?? 0 }} {{ __('messages.of') }} {{ $sales->total() }} {{ __('messages.entries') }}</span></div>
        <div>{{ $sales->links() }}</div>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width:750px;">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="fas fa-plus-circle me-2"></i> {{ __('messages.new_sale') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="createForm" action="{{ route('admin.sales.store') }}" method="POST">
                @csrf
                <div class="modal-body" style="max-height:70vh; overflow-y:auto;">
                    <div id="createFormErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.customer') }} <span class="text-danger">*</span></label>
                            <select name="customer_id" class="form-select" required>
                                <option value="">{{ __('messages.select_customer') }}</option>
                                @foreach($customers ?? [] as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.currency') }} <span class="text-danger">*</span></label>
                            <select name="currency_id" class="form-select" required>
                                <option value="">{{ __('messages.select_currency') }}</option>
                                @foreach($currencies ?? [] as $currency)
                                    <option value="{{ $currency->id }}">{{ $currency->code }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.sale_date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="sale_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.due_date') }}</label>
                            <input type="date" name="due_date" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.payment_method') }} <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select" required>
                                <option value="cash">{{ __('messages.cash') }}</option>
                                <option value="card">{{ __('messages.card') }}</option>
                                <option value="mobile">{{ __('messages.mobile_money') }}</option>
                                <option value="credit">{{ __('messages.credit') }}</option>
                                <option value="bank_transfer">{{ __('messages.bank_transfer') }}</option>
                                <option value="cheque">{{ __('messages.cheque') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.discount_type') }}</label>
                            <select name="discount_type" class="form-select">
                                <option value="percentage">{{ __('messages.percentage') }}</option>
                                <option value="fixed">{{ __('messages.fixed') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.discount') }}</label>
                            <input type="number" name="discount" class="form-control" step="0.01" min="0" placeholder="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.tax_percent') }}</label>
                            <input type="number" name="tax" class="form-control" step="0.01" min="0" placeholder="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.shipping_cost') }}</label>
                            <input type="number" name="shipping_cost" class="form-control" step="0.01" min="0" placeholder="0">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">{{ __('messages.notes') }}</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <hr>
                            <h6 class="fw-bold"><i class="fas fa-boxes me-2"></i>{{ __('messages.items') }}</h6>
                            <div id="createItems">
                                <div class="row g-2 item-row align-items-end">
                                    <div class="col-4">
                                        <select name="items[0][product_id]" class="form-select product-select" required>
                                            <option value="">{{ __('messages.select_product') }}</option>
                                            @foreach($products ?? [] as $prod)
                                                <option value="{{ $prod->id }}" data-price="{{ $prod->selling_price ?? $prod->price ?? 0 }}">{{ $prod->name }} ({{ $prod->sku }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-2"><input type="number" name="items[0][quantity]" class="form-control qty-input" placeholder="{{ __('messages.qty') }}" step="1" min="1" value="1" required></div>
                                    <div class="col-2"><input type="number" name="items[0][unit_price]" class="form-control price-input" placeholder="{{ __('messages.price') }}" step="0.01" min="0" required></div>
                                    <div class="col-2"><input type="number" name="items[0][discount]" class="form-control discount-input" placeholder="{{ __('messages.disc') }}" step="0.01" min="0" value="0"></div>
                                    <div class="col-2"><button type="button" class="btn btn-danger btn-sm remove-item-btn" style="display:none;"><i class="fas fa-trash"></i></button></div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-secondary mt-2" id="addItemBtn"><i class="fas fa-plus me-1"></i> {{ __('messages.add_item') }}</button>
                            <div class="mt-2 text-end"><span class="fw-bold">{{ __('messages.subtotal') }}: </span><span id="subtotalDisplay" class="fw-bold text-primary">$0.00</span></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> {{ __('messages.cancel') }}</button>
                    <button type="submit" class="btn btn-primary" id="createSubmitBtn"><i class="fas fa-save me-1"></i> {{ __('messages.create_sale') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Modal -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width:750px;">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title text-white"><i class="fas fa-eye me-2"></i> {{ __('messages.sale_details') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewModalBody" style="max-height:70vh; overflow-y:auto;"><div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">{{ __('messages.loading') }}...</span></div></div></div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.close') }}</button></div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width:750px;">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title text-dark"><i class="fas fa-edit me-2"></i> {{ __('messages.edit_sale') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body" id="editModalBody" style="max-height:70vh; overflow-y:auto;"><div id="editFormErrors" class="alert alert-danger d-none"></div><div class="text-center py-5"><div class="spinner-border text-warning" role="status"><span class="visually-hidden">{{ __('messages.loading') }}...</span></div></div></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> {{ __('messages.cancel') }}</button>
                    <button type="submit" class="btn btn-warning" id="editSubmitBtn"><i class="fas fa-save me-1"></i> {{ __('messages.update_sale') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:560px;">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title text-white"><i class="fas fa-money-bill-wave me-2"></i> {{ __('messages.register_payment') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="paymentForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div id="paymentFormErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">{{ __('messages.amount') }} <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control" step="0.01" min="0.01" placeholder="0.00" required>
                            <small class="text-muted">{{ __('messages.outstanding_balance') }}: <span id="outstandingBalance">0.00</span></small>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">{{ __('messages.payment_method') }}</label>
                            <select name="payment_method" class="form-select">
                                <option value="cash">{{ __('messages.cash') }}</option>
                                <option value="card">{{ __('messages.card') }}</option>
                                <option value="mobile">{{ __('messages.mobile_money') }}</option>
                                <option value="bank_transfer">{{ __('messages.bank_transfer') }}</option>
                                <option value="cheque">{{ __('messages.cheque') }}</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">{{ __('messages.reference_optional') }}</label>
                            <input type="text" name="reference" class="form-control" placeholder="{{ __('messages.reference') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">{{ __('messages.notes_optional') }}</label>
                            <input type="text" name="notes" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> {{ __('messages.cancel') }}</button>
                    <button type="submit" class="btn btn-success" id="paymentSubmitBtn"><i class="fas fa-save me-1"></i> {{ __('messages.process_payment') }}</button>
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
                <p class="fw-bold">{{ __('messages.are_you_sure_delete_sale') }}</p>
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
.item-row { padding: 6px; border-radius: 6px; background: #f8fafc; margin-bottom: 6px; }
.item-row:nth-child(even) { background: #f1f5f9; }
.price-input:read-only { background-color: #f0fdf4; }
</style>

@push('scripts')
<script>
window.trans = {
    loading: @json(__('messages.loading')),
    close: @json(__('messages.close')),
    cancel: @json(__('messages.cancel')),
    delete: @json(__('messages.delete')),
    edit: @json(__('messages.edit')),
    view: @json(__('messages.view')),
    invoice_number: @json(__('messages.invoice_number')),
    customer: @json(__('messages.customer')),
    total: @json(__('messages.total')),
    payment_status: @json(__('messages.payment_status')),
    date: @json(__('messages.date')),
    due_date: @json(__('messages.due_date')),
    payment_method: @json(__('messages.payment_method')),
    amount_paid: @json(__('messages.amount_paid')),
    notes: @json(__('messages.notes')),
    product: @json(__('messages.product')),
    qty: @json(__('messages.qty')),
    price: @json(__('messages.price')),
    disc: @json(__('messages.disc')),
    items: @json(__('messages.items')),
    subtotal: @json(__('messages.subtotal')),
    pending: @json(__('messages.pending')),
    partial: @json(__('messages.partial')),
    paid: @json(__('messages.paid')),
    cancelled: @json(__('messages.cancelled')),
    overdue: @json(__('messages.overdue')),
    created: @json(__('messages.created')),
    updated: @json(__('messages.updated')),
    deleted: @json(__('messages.deleted')),
    payment_processed: @json(__('messages.payment_processed')),
    cancelled_alert: @json(__('messages.cancelled_alert')),
    error_title: @json(__('messages.error')),
    something_went_wrong: @json(__('messages.something_went_wrong')),
    failed_to_load_details: @json(__('messages.failed_to_load_details')),
    failed_to_create: @json(__('messages.failed_to_create')),
    failed_to_update: @json(__('messages.failed_to_update')),
    failed_to_delete: @json(__('messages.failed_to_delete')),
    failed_to_process_payment: @json(__('messages.failed_to_process_payment')),
    failed_to_cancel: @json(__('messages.failed_to_cancel')),
    are_you_sure: @json(__('messages.are_you_sure')),
    wont_be_able_to_revert: @json(__('messages.wont_be_able_to_revert')),
    yes_delete: @json(__('messages.yes_delete')),
    cancel_sale_q: @json(__('messages.cancel_sale_q')),
    cancel_sale_warning: @json(__('messages.cancel_sale_warning')),
    yes_cancel: @json(__('messages.yes_cancel')),
    no: @json(__('messages.no')),
    cancelling: @json(__('messages.cancelling')),
    creating: @json(__('messages.creating')),
    updating: @json(__('messages.updating')),
    deleting: @json(__('messages.deleting')),
    processing: @json(__('messages.processing')),
    create_sale: @json(__('messages.create_sale')),
    update_sale: @json(__('messages.update_sale')),
    process_payment: @json(__('messages.process_payment')),
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

    function loadProductPrice(selectElement) {
        var row = selectElement.closest('.item-row');
        var priceInput = row.find('.price-input');
        var selectedOption = selectElement.find('option:selected');
        var price = selectedOption.data('price');
        if (price !== undefined && price !== null && price > 0) {
            priceInput.val(parseFloat(price).toFixed(2)).prop('readonly', true).css('background-color', '#f0fdf4');
        } else {
            var productId = selectElement.val();
            if (productId) {
                $.ajax({
                    url: '/admin/sales/product/' + productId, type: 'GET', dataType: 'json',
                    success: function(response) {
                        if (response.success && response.selling_price) {
                            priceInput.val(parseFloat(response.selling_price).toFixed(2)).prop('readonly', true).css('background-color', '#f0fdf4');
                        } else {
                            priceInput.val('').prop('readonly', false).css('background-color', '');
                        }
                        updateSubtotal();
                    },
                    error: function() { priceInput.val('').prop('readonly', false).css('background-color', ''); updateSubtotal(); }
                });
            } else {
                priceInput.val('').prop('readonly', false).css('background-color', '');
            }
        }
        updateSubtotal();
    }
    function updateSubtotal() {
        var total = 0;
        $('.item-row').each(function() {
            var qty = parseFloat($(this).find('.qty-input').val()) || 0;
            var price = parseFloat($(this).find('.price-input').val()) || 0;
            var discount = parseFloat($(this).find('.discount-input').val()) || 0;
            var itemTotal = (qty * price) - discount;
            total += itemTotal > 0 ? itemTotal : 0;
        });
        $('#subtotalDisplay').text('$' + total.toFixed(2));
    }
    $(document).on('change', '.product-select', function() { loadProductPrice($(this)); });
    $(document).on('change keyup', '.qty-input, .price-input, .discount-input', function() { updateSubtotal(); });

    var itemIndex = 1;
    var productOptionsHtml = `@foreach($products ?? [] as $prod)<option value="{{ $prod->id }}" data-price="{{ $prod->selling_price ?? $prod->price ?? 0 }}">{{ $prod->name }} ({{ $prod->sku }})</option>@endforeach`;
    $('#addItemBtn').on('click', function() {
        var html = `
            <div class="row g-2 item-row align-items-end">
                <div class="col-4">
                    <select name="items[${itemIndex}][product_id]" class="form-select product-select" required>
                        <option value="">${trans.product}</option>
                        ${productOptionsHtml}
                    </select>
                </div>
                <div class="col-2"><input type="number" name="items[${itemIndex}][quantity]" class="form-control qty-input" placeholder="${trans.qty}" step="1" min="1" value="1" required></div>
                <div class="col-2"><input type="number" name="items[${itemIndex}][unit_price]" class="form-control price-input" placeholder="${trans.price}" step="0.01" min="0" required></div>
                <div class="col-2"><input type="number" name="items[${itemIndex}][discount]" class="form-control discount-input" placeholder="${trans.disc}" step="0.01" min="0" value="0"></div>
                <div class="col-2"><button type="button" class="btn btn-danger btn-sm remove-item-btn"><i class="fas fa-trash"></i></button></div>
            </div>
        `;
        $('#createItems').append(html);
        if ($('#createItems .item-row').length > 1) $('#createItems .remove-item-btn').show();
        itemIndex++;
        updateSubtotal();
    });
    $(document).on('click', '.remove-item-btn', function() {
        $(this).closest('.item-row').remove();
        if ($('#createItems .item-row').length <= 1) $('#createItems .remove-item-btn').hide();
        updateSubtotal();
    });
    setTimeout(() => $('#createItems .remove-item-btn').hide(), 100);

    @can('sale.create')
    $('#createForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = $('#createSubmitBtn');
        var errorContainer = $('#createFormErrors');
        clearErrors(errorContainer);
        btn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm me-1"></span> ${trans.creating}...`);
        $('.price-input').each(function() { if (!$(this).val() || $(this).val() === '') { $(this).val(0); } });
        $.ajax({
            url: form.attr('action'), type: 'POST', data: form.serialize(), dataType: 'json',
            success: function(r) {
                if (r.success) {
                    $('#createModal').modal('hide');
                    Swal.fire({ icon: 'success', title: trans.created, text: r.message, timer: 2000 }).then(() => location.reload());
                } else {
                    btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.create_sale}`);
                    if (r.errors) showErrors(errorContainer, r.errors);
                    else Swal.fire({ icon: 'error', title: trans.error_title, text: r.message || trans.something_went_wrong });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.create_sale}`);
                if (xhr.status === 422) showErrors(errorContainer, xhr.responseJSON.errors);
                else Swal.fire({ icon: 'error', title: trans.error_title, text: xhr.responseJSON?.error || trans.failed_to_create });
            }
        });
    });
    @endcan

    @can('sale.view')
    $(document).on('click', '.view-btn', function() {
        var id = $(this).data('id');
        var body = $('#viewModalBody');
        body.html(`<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">${trans.loading}...</span></div></div>`);
        $('#viewModal').modal('show');
        $.ajax({
            url: '/admin/sales/' + id, type: 'GET', dataType: 'json',
            success: function(r) {
                var sale = r;
                var items = sale.items || [];
                var currencyCode = sale.currency ? sale.currency.code : '';
                var html = `
                    <div class="row g-3">
                        <div class="col-md-6"><label class="text-muted small fw-bold">${trans.invoice_number}</label><p class="fw-bold mb-0">${sale.invoice_number}</p></div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">${trans.customer}</label><p class="mb-0">${sale.customer ? sale.customer.name : 'N/A'}</p></div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">${trans.total}</label><p class="fw-bold text-primary mb-0">${currencyCode} ${parseFloat(sale.total).toFixed(2)}</p></div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">${trans.payment_status}</label><p class="mb-0"><span class="badge bg-${sale.payment_status == 'paid' ? 'success' : (sale.payment_status == 'pending' ? 'warning' : (sale.payment_status == 'partial' ? 'info' : 'danger'))}">${trans[sale.payment_status] || sale.payment_status}</span></p></div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">${trans.date}</label><p class="mb-0">${new Date(sale.sale_date).toLocaleDateString()}</p></div>
                        ${sale.due_date ? `<div class="col-md-6"><label class="text-muted small fw-bold">${trans.due_date}</label><p class="mb-0">${new Date(sale.due_date).toLocaleDateString()}</p></div>` : ''}
                        <div class="col-md-6"><label class="text-muted small fw-bold">${trans.payment_method}</label><p class="mb-0">${trans[sale.payment_method] || sale.payment_method}</p></div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">${trans.amount_paid}</label><p class="mb-0">${currencyCode} ${parseFloat(sale.amount_paid || 0).toFixed(2)}</p></div>
                        ${sale.notes ? `<div class="col-12"><label class="text-muted small fw-bold">${trans.notes}</label><p class="mb-0">${sale.notes}</p></div>` : ''}
                        <div class="col-12"><hr><h6 class="fw-bold text-primary"><i class="fas fa-boxes me-2"></i>${trans.items}</h6>
                        <table class="table table-sm table-bordered">
                            <thead><tr><th>#</th><th>${trans.product}</th><th class="text-end">${trans.qty}</th><th class="text-end">${trans.price}</th><th class="text-end">${trans.total}</th></tr></thead>
                            <tbody>${items.map(function(item, idx) {
                                return `<tr><td>${idx+1}</td><td>${item.product_name || (item.product ? item.product.name : 'N/A')}</td><td class="text-end">${item.quantity}</td><td class="text-end">${currencyCode} ${parseFloat(item.unit_price).toFixed(2)}</td><td class="text-end">${currencyCode} ${parseFloat(item.total).toFixed(2)}</td></tr>`;
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

    @can('sale.pay')
    $(document).on('click', '.pay-btn', function() {
        var id = $(this).data('id');
        var form = $('#paymentForm');
        form.attr('action', '/admin/sales/' + id + '/pay');
        clearErrors($('#paymentFormErrors'));
        $.ajax({
            url: '/admin/sales/' + id, type: 'GET', dataType: 'json',
            success: function(r) {
                var balance = parseFloat(r.total) - parseFloat(r.amount_paid || 0);
                $('#outstandingBalance').text(balance.toFixed(2));
                $('#paymentForm input[name="amount"]').attr('max', balance).attr('placeholder', 'Max: ' + balance.toFixed(2));
            }
        });
        $('#paymentModal').modal('show');
    });
    $('#paymentForm').on('submit', function(e) {
        e.preventDefault();
        var btn = $('#paymentSubmitBtn');
        var errorContainer = $('#paymentFormErrors');
        clearErrors(errorContainer);
        btn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm me-1"></span> ${trans.processing}...`);
        $.ajax({
            url: $(this).attr('action'), type: 'POST', data: $(this).serialize(), dataType: 'json',
            success: function(r) {
                if (r.success) {
                    $('#paymentModal').modal('hide');
                    Swal.fire({ icon: 'success', title: trans.payment_processed, text: r.message, timer: 2000 }).then(() => location.reload());
                } else {
                    btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.process_payment}`);
                    if (r.errors) showErrors(errorContainer, r.errors);
                    else Swal.fire({ icon: 'error', title: trans.error_title, text: r.message || r.error || trans.something_went_wrong });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.process_payment}`);
                if (xhr.status === 422) {
                    if (xhr.responseJSON?.errors) showErrors(errorContainer, xhr.responseJSON.errors);
                    else errorContainer.removeClass('d-none').html('<ul class="mb-0"><li>' + (xhr.responseJSON?.error || trans.something_went_wrong) + '</li></ul>');
                } else {
                    Swal.fire({ icon: 'error', title: trans.error_title, text: xhr.responseJSON?.error || trans.failed_to_process_payment });
                }
            }
        });
    });
    @endcan

    @can('sale.cancel')
    $(document).on('click', '.cancel-btn', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: trans.cancel_sale_q, text: trans.cancel_sale_warning, icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#6c757d',
            confirmButtonText: trans.yes_cancel, cancelButtonText: trans.no
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: trans.cancelling, allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                $.ajax({
                    url: '/admin/sales/' + id + '/cancel', type: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(r) {
                        if (r.success) Swal.fire({ icon: 'success', title: trans.cancelled_alert, text: r.message, timer: 1500 }).then(() => location.reload());
                        else Swal.fire(trans.error_title, r.message || trans.failed_to_cancel, 'error');
                    },
                    error: function() { Swal.fire(trans.error_title, trans.failed_to_cancel, 'error'); }
                });
            }
        });
    });
    @endcan

    @can('sale.delete')
    $(document).on('click', '.delete-btn', function() {
        var id = $(this).data('id');
        $('#deleteForm').attr('action', '/admin/sales/' + id);
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

    $('#searchBtn').on('click', filterTable);
    $('#searchInput').on('keypress', function(e) { if (e.which === 13) filterTable(); });
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

    $('#exportBtn').on('click', function() { window.location.href = '{{ route("admin.sales.export") }}'; });
    $('.modal').on('hidden.bs.modal', function() {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
        $(this).find('.alert-danger').addClass('d-none').empty();
        $(this).find('form')[0]?.reset();
        if ($(this).find('#createItems').length) {
            var productOptions = $('#createItems .item-row:first .product-select').html();
            $('#createItems').html(`
                <div class="row g-2 item-row align-items-end">
                    <div class="col-4"><select name="items[0][product_id]" class="form-select product-select" required>${productOptions}</select></div>
                    <div class="col-2"><input type="number" name="items[0][quantity]" class="form-control qty-input" placeholder="${trans.qty}" step="1" min="1" value="1" required></div>
                    <div class="col-2"><input type="number" name="items[0][unit_price]" class="form-control price-input" placeholder="${trans.price}" step="0.01" min="0" required></div>
                    <div class="col-2"><input type="number" name="items[0][discount]" class="form-control discount-input" placeholder="${trans.disc}" step="0.01" min="0" value="0"></div>
                    <div class="col-2"><button type="button" class="btn btn-danger btn-sm remove-item-btn" style="display:none;"><i class="fas fa-trash"></i></button></div>
                </div>
            `);
            itemIndex = 1;
            updateSubtotal();
        }
        if ($(this).find('#editItems').length) $('#editItems').empty();
    });
});
</script>
@endpush
@endsection