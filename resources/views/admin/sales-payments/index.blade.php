@extends('layouts.admin')

@section('title', __('messages.sales_payments'))
@section('page-title', __('messages.sales_payment_management'))

@section('admin-content')
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-lg-6">
        <div class="small-box bg-primary">
            <div class="inner"><h3>{{ number_format($stats['total'] ?? 0) }}</h3><p>{{ __('messages.total_payments') }}</p></div>
            <div class="icon"><i class="fas fa-arrow-left"></i></div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6">
        <div class="small-box bg-success">
            <div class="inner"><h3>{{ number_format($stats['total_amount'] ?? 0, 2) }}</h3><p>{{ __('messages.total_amount') }}</p></div>
            <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6">
        <div class="small-box bg-info">
            <div class="inner"><h3>{{ $stats['today'] ?? 0 }}</h3><p>{{ __('messages.today') }}</p></div>
            <div class="icon"><i class="fas fa-calendar-day"></i></div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6">
        <div class="small-box bg-warning">
            <div class="inner"><h3>{{ $stats['this_month'] ?? 0 }}</h3><p>{{ __('messages.this_month') }}</p></div>
            <div class="icon"><i class="fas fa-calendar-alt"></i></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <h5 class="card-title mb-0"><i class="fas fa-list text-primary me-2"></i>{{ __('messages.all_sales_payments') }} <span class="badge bg-primary ms-1">{{ $payments->total() }}</span></h5>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <div class="input-group input-group-sm" style="width:200px;">
                <input type="text" id="searchInput" class="form-control" placeholder="{{ __('messages.search') }}...">
                <button class="btn btn-secondary" type="button" id="searchBtn"><i class="fas fa-search"></i></button>
            </div>
            <select id="methodFilter" class="form-select form-select-sm" style="width:140px;">
                <option value="">{{ __('messages.all_methods') }}</option>
                @foreach($methods as $m)
                    <option value="{{ $m }}">{{ __('messages.' . $m) }}</option>
                @endforeach
            </select>
            <select id="customerFilter" class="form-select form-select-sm" style="width:150px;">
                <option value="">{{ __('messages.all_customers') }}</option>
                @foreach($customers as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </select>
            @can('sales_payment.create')
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fas fa-plus-circle me-1"></i> {{ __('messages.new_payment') }}
            </button>
            @endcan
            @can('sales_payment.export')
            <a href="{{ route('admin.sales-payments.export') }}" class="btn btn-success btn-sm"><i class="fas fa-file-excel me-1"></i> {{ __('messages.export') }}</a>
            @endcan
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:35px;">#</th>
                        <th>{{ __('messages.date') }}</th>
                        <th>{{ __('messages.invoice_number') }}</th>
                        <th>{{ __('messages.customer') }}</th>
                        <th class="text-end">{{ __('messages.amount') }}</th>
                        <th>{{ __('messages.method') }}</th>
                        <th>{{ __('messages.reference') }}</th>
                        <th style="width:200px;">{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($payments as $p)
                    <tr data-id="{{ $p->id }}" data-method="{{ $p->method }}" data-customer="{{ $p->customer_id }}">
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $p->payment_date->format('M d, Y') }}</td>
                        <td>{{ $p->invoice->invoice_number ?? 'N/A' }}</td>
                        <td>{{ $p->customer->name ?? 'N/A' }}</td>
                        <td class="text-end"><strong>{{ $p->currency->symbol ?? '$' }}{{ number_format($p->amount, 2) }}</strong></td>
                        <td>
                            <span class="badge bg-{{ ['cash'=>'success','bank_transfer'=>'info','cheque'=>'warning','mobile'=>'primary','credit'=>'secondary'][$p->method] ?? 'secondary' }}">
                                {{ __('messages.' . $p->method) }}
                            </span>
                        </td>
                        <td>{{ $p->reference ?? 'N/A' }}</td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                @can('sales_payment.view')
                                <button class="btn btn-outline-primary btn-sm view-btn" data-id="{{ $p->id }}" title="{{ __('messages.view') }}"><i class="fas fa-eye"></i></button>
                                @endcan
                                @can('sales_payment.edit')
                                <button class="btn btn-outline-warning btn-sm edit-btn" data-id="{{ $p->id }}" title="{{ __('messages.edit') }}"><i class="fas fa-edit"></i></button>
                                @endcan
                                @can('sales_payment.delete')
                                <button class="btn btn-outline-danger btn-sm delete-btn" data-id="{{ $p->id }}" title="{{ __('messages.delete') }}"><i class="fas fa-trash"></i></button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8"><div class="text-center py-5"><i class="fas fa-arrow-left fa-4x text-muted mb-3 d-block"></i><h5 class="fw-bold">{{ __('messages.no_sales_payments_found') }}</h5></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div><span class="text-muted small">{{ __('messages.showing') }} {{ $payments->firstItem() ?? 0 }} {{ __('messages.to') }} {{ $payments->lastItem() ?? 0 }} {{ __('messages.of') }} {{ $payments->total() }} {{ __('messages.entries') }}</span></div>
        <div>{{ $payments->links() }}</div>
    </div>
</div>

<!-- CREATE MODAL -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:600px;">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="fas fa-plus-circle me-2"></i> {{ __('messages.new_sales_payment') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="createForm" action="{{ route('admin.sales-payments.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div id="createFormErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">{{ __('messages.invoice') }} <span class="text-danger">*</span></label>
                            <select name="invoice_id" class="form-select" required>
                                <option value="">{{ __('messages.select_invoice') }}</option>
                                @foreach($invoices ?? [] as $inv)
                                    <option value="{{ $inv->id }}">{{ $inv->invoice_number }} - {{ $inv->customer->name ?? 'N/A' }} (${{ number_format($inv->balance, 2) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.amount') }} <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control" step="0.01" min="0.01" placeholder="0.00" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.payment_date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.method') }} <span class="text-danger">*</span></label>
                            <select name="method" class="form-select" required>
                                @foreach($methods as $m)
                                    <option value="{{ $m }}">{{ __('messages.' . $m) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.currency') }} <span class="text-danger">*</span></label>
                            <select name="currency_id" class="form-select" required>
                                @foreach($currencies as $c)
                                    <option value="{{ $c->id }}" {{ $c->is_base ? 'selected' : '' }}>{{ $c->code }} ({{ $c->symbol }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.exchange_rate') }}</label>
                            <input type="number" name="exchange_rate" class="form-control" step="0.000001" min="0" value="1">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.reference') }}</label>
                            <input type="text" name="reference" class="form-control" placeholder="{{ __('messages.transaction_reference') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">{{ __('messages.notes') }}</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                    <button type="submit" class="btn btn-primary" id="createSubmitBtn"><i class="fas fa-save me-1"></i> {{ __('messages.save_payment') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- VIEW MODAL -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:600px;">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title text-white"><i class="fas fa-eye me-2"></i> {{ __('messages.payment_details') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewModalBody"><div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div></div>
            <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.close') }}</button></div>
        </div>
    </div>
</div>

<!-- EDIT MODAL -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:600px;">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title text-dark"><i class="fas fa-edit me-2"></i> {{ __('messages.edit_payment') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body" id="editModalBody"><div class="text-center py-5"><div class="spinner-border text-warning" role="status"></div></div></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                    <button type="submit" class="btn btn-warning" id="editSubmitBtn"><i class="fas fa-save me-1"></i> {{ __('messages.update') }}</button>
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
                <p class="fw-bold">{{ __('messages.are_you_sure_delete_payment') }}</p>
                <form id="deleteForm" method="POST">@csrf @method('DELETE')</form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                <button type="submit" class="btn btn-danger" id="deleteSubmitBtn" form="deleteForm">{{ __('messages.delete') }}</button>
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
.small-box.bg-warning .inner { color:#212529; }
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
    amount: @json(__('messages.amount')),
    date: @json(__('messages.date')),
    method: @json(__('messages.method')),
    reference: @json(__('messages.reference')),
    invoice: @json(__('messages.invoice')),
    customer: @json(__('messages.customer')),
    notes: @json(__('messages.notes')),
    none: @json(__('messages.none')),
    created_by: @json(__('messages.created_by')),
    created_at: @json(__('messages.created_at')),
    payment_details: @json(__('messages.payment_details')),
    payment_date: @json(__('messages.payment_date')),
    currency: @json(__('messages.currency')),
    exchange_rate: @json(__('messages.exchange_rate')),
    save_payment: @json(__('messages.save_payment')),
    update: @json(__('messages.update')),
    recording: @json(__('messages.recording')),
    updating: @json(__('messages.updating')),
    deleting: @json(__('messages.deleting')),
    recorded: @json(__('messages.recorded')),
    updated: @json(__('messages.updated')),
    deleted: @json(__('messages.deleted')),
    error_title: @json(__('messages.error')),
    something_went_wrong: @json(__('messages.something_went_wrong')),
    failed_to_save_payment: @json(__('messages.failed_to_save_payment')),
    failed_to_update_payment: @json(__('messages.failed_to_update_payment')),
    failed_to_delete_payment: @json(__('messages.failed_to_delete_payment')),
    are_you_sure: @json(__('messages.are_you_sure')),
    wont_be_able_to_revert: @json(__('messages.wont_be_able_to_revert')),
    yes_delete: @json(__('messages.yes_delete')),
    cash: @json(__('messages.cash')),
    bank_transfer: @json(__('messages.bank_transfer')),
    cheque: @json(__('messages.cheque')),
    mobile: @json(__('messages.mobile')),
    credit: @json(__('messages.credit')),
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

    @can('sales_payment.view')
    $(document).on('click', '.view-btn', function() {
        var id = $(this).data('id');
        var body = $('#viewModalBody');
        body.html(`<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>`);
        $('#viewModal').modal('show');
        $.get('/admin/sales-payments/' + id, function(p) {
            var html = `
                <div class="row g-3">
                    <div class="col-6"><label class="text-muted">${trans.amount}</label><p class="fw-bold">${p.currency ? p.currency.symbol : '$'}${parseFloat(p.amount).toFixed(2)}</p></div>
                    <div class="col-6"><label class="text-muted">${trans.date}</label><p>${new Date(p.payment_date).toLocaleDateString()}</p></div>
                    <div class="col-6"><label class="text-muted">${trans.method}</label><p>${trans[p.method] || p.method}</p></div>
                    <div class="col-6"><label class="text-muted">${trans.reference}</label><p>${p.reference || 'N/A'}</p></div>
                    <div class="col-12"><label class="text-muted">${trans.invoice}</label><p>${p.invoice ? p.invoice.invoice_number : 'N/A'}</p></div>
                    <div class="col-12"><label class="text-muted">${trans.customer}</label><p>${p.customer ? p.customer.name : 'N/A'}</p></div>
                    <div class="col-12"><label class="text-muted">${trans.notes}</label><p>${p.notes || trans.none}</p></div>
                    <div class="col-6"><label class="text-muted">${trans.created_by}</label><p>${p.created_by ? p.created_by.name : 'N/A'}</p></div>
                    <div class="col-6"><label class="text-muted">${trans.created_at}</label><p>${new Date(p.created_at).toLocaleString()}</p></div>
                </div>
            `;
            body.html(html);
        });
    });
    @endcan

    @can('sales_payment.edit')
    $(document).on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        var body = $('#editModalBody');
        var form = $('#editForm');
        form.attr('action', '/admin/sales-payments/' + id);
        body.html('<div id="editFormErrors" class="alert alert-danger d-none"></div><div class="text-center py-5"><div class="spinner-border text-warning" role="status"></div></div>');
        $('#editModal').modal('show');

        $.get('/admin/sales-payments/' + id + '/edit', function(p) {
            var currencyOptions = '';
            @foreach($currencies as $c)
                currencyOptions += `<option value="{{ $c->id }}" ${p.currency_id == {{ $c->id }} ? 'selected' : ''}>{{ $c->code }} ({{ $c->symbol }})</option>`;
            @endforeach
            var methods = ['cash', 'bank_transfer', 'cheque', 'mobile', 'credit'];
            var methodOptions = '';
            methods.forEach(function(m) {
                var label = trans[m] || m;
                var selected = (p.method == m) ? 'selected' : '';
                methodOptions += `<option value="${m}" ${selected}>${label}</option>`;
            });
            body.html(`
                <div id="editFormErrors" class="alert alert-danger d-none"></div>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label fw-bold">${trans.amount} <span class="text-danger">*</span></label><input type="number" name="amount" class="form-control" step="0.01" min="0.01" value="${p.amount}" required></div>
                    <div class="col-md-6"><label class="form-label fw-bold">${trans.payment_date} <span class="text-danger">*</span></label><input type="date" name="payment_date" class="form-control" value="${p.payment_date}" required></div>
                    <div class="col-md-6"><label class="form-label fw-bold">${trans.method} <span class="text-danger">*</span></label><select name="method" class="form-select" required>${methodOptions}</select></div>
                    <div class="col-md-6"><label class="form-label fw-bold">${trans.currency} <span class="text-danger">*</span></label><select name="currency_id" class="form-select" required>${currencyOptions}</select></div>
                    <div class="col-md-6"><label class="form-label fw-bold">${trans.exchange_rate}</label><input type="number" name="exchange_rate" class="form-control" step="0.000001" min="0" value="${p.exchange_rate || 1}"></div>
                    <div class="col-md-6"><label class="form-label fw-bold">${trans.reference}</label><input type="text" name="reference" class="form-control" value="${p.reference || ''}"></div>
                    <div class="col-12"><label class="form-label fw-bold">${trans.notes}</label><textarea name="notes" class="form-control" rows="2">${p.notes || ''}</textarea></div>
                    <div class="col-12"><small class="text-muted">${trans.invoice}: ${p.invoice ? p.invoice.invoice_number : 'N/A'}</small></div>
                </div>
            `);
        });
    });
    @endcan

    @can('sales_payment.create')
    $('#createForm').on('submit', function(e) {
        e.preventDefault();
        var btn = $('#createSubmitBtn');
        var errorContainer = $('#createFormErrors');
        clearErrors(errorContainer);
        btn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm me-1"></span> ${trans.recording}...`);
        $.ajax({
            url: $(this).attr('action'), type: 'POST', data: $(this).serialize(), dataType: 'json',
            success: function(r) {
                if (r.success) {
                    $('#createModal').modal('hide');
                    Swal.fire({ icon: 'success', title: trans.recorded, text: r.message, timer: 2000 }).then(() => location.reload());
                } else {
                    btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.save_payment}`);
                    if (r.errors) showErrors(errorContainer, r.errors);
                    else Swal.fire({ icon: 'error', title: trans.error_title, text: r.message || trans.something_went_wrong });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.save_payment}`);
                if (xhr.status === 422) showErrors(errorContainer, xhr.responseJSON.errors);
                else Swal.fire({ icon: 'error', title: trans.error_title, text: trans.failed_to_save_payment });
            }
        });
    });
    @endcan

    @can('sales_payment.edit')
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
                    btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.update}`);
                    if (r.errors) showErrors(errorContainer, r.errors);
                    else Swal.fire({ icon: 'error', title: trans.error_title, text: r.message || trans.something_went_wrong });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.update}`);
                if (xhr.status === 422) showErrors(errorContainer, xhr.responseJSON.errors);
                else Swal.fire({ icon: 'error', title: trans.error_title, text: trans.failed_to_update_payment });
            }
        });
    });
    @endcan

    @can('sales_payment.delete')
    $(document).on('click', '.delete-btn', function() {
        var id = $(this).data('id');
        $('#deleteForm').attr('action', '/admin/sales-payments/' + id);
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
                    Swal.fire({ icon: 'success', title: trans.deleted, text: r.message, timer: 2000 }).then(() => location.reload());
                } else {
                    btn.prop('disabled', false).html(`<i class="fas fa-trash me-1"></i> ${trans.delete}`);
                    Swal.fire({ icon: 'error', title: trans.error_title, text: r.message || trans.failed_to_delete_payment });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(`<i class="fas fa-trash me-1"></i> ${trans.delete}`);
                Swal.fire({ icon: 'error', title: trans.error_title, text: xhr.responseJSON?.message || trans.failed_to_delete_payment });
            }
        });
    });
    @endcan

    $('#searchBtn').on('click', filterTable);
    $('#searchInput').on('keypress', function(e) { if (e.which === 13) filterTable(); });
    $('#methodFilter, #customerFilter').on('change', filterTable);
    function filterTable() {
        var search = $('#searchInput').val().toLowerCase();
        var method = $('#methodFilter').val();
        var customer = $('#customerFilter').val();
        $('#tableBody tr').each(function() {
            var row = $(this);
            var show = true;
            if (search && !row.text().toLowerCase().includes(search)) show = false;
            if (method && row.data('method') !== method) show = false;
            if (customer && row.data('customer') != customer) show = false;
            row.toggle(show);
        });
    }

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