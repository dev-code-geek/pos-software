@extends('layouts.admin')

@section('title', __('messages.stock_movements'))
@section('page-title', __('messages.stock_movement_log'))

@section('admin-content')
<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ $stats['total'] ?? $movements->total() ?? 0 }}</h3>
                <p>{{ __('messages.total_movements') }}</p>
            </div>
            <div class="icon"><i class="fas fa-exchange-alt"></i></div>
            <a href="{{ route('admin.stock-movements.index') }}" class="small-box-footer">
                {{ __('messages.view_all') }} <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $stats['incoming'] ?? 0 }}</h3>
                <p>{{ __('messages.incoming') }}</p>
            </div>
            <div class="icon"><i class="fas fa-arrow-down"></i></div>
            <a href="{{ route('admin.stock-movements.index', ['direction' => 'incoming']) }}" class="small-box-footer">
                {{ __('messages.view_incoming') }} <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $stats['outgoing'] ?? 0 }}</h3>
                <p>{{ __('messages.outgoing') }}</p>
            </div>
            <div class="icon"><i class="fas fa-arrow-up"></i></div>
            <a href="{{ route('admin.stock-movements.index', ['direction' => 'outgoing']) }}" class="small-box-footer">
                {{ __('messages.view_outgoing') }} <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $stats['net'] ?? 0 }}</h3>
                <p>{{ __('messages.net_change') }}</p>
            </div>
            <div class="icon"><i class="fas fa-calculator"></i></div>
            <a href="#" class="small-box-footer"><i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<!-- Main Card -->
<div class="card">
    <div class="card-header">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <h5 class="card-title mb-0">
                <i class="fas fa-exchange-alt text-primary me-2"></i>
                {{ __('messages.stock_movement_log') }}
                <span class="badge bg-primary ms-1">{{ $movements->total() }}</span>
            </h5>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <div class="input-group input-group-sm" style="width: 200px;">
                <input type="text" id="searchInput" class="form-control" placeholder="{{ __('messages.search') }}...">
                <button class="btn btn-secondary" type="button" id="searchBtn"><i class="fas fa-search"></i></button>
            </div>
            <select id="typeFilter" class="form-select form-select-sm" style="width: 120px;">
                <option value="">{{ __('messages.all_types') }}</option>
                <option value="purchase">{{ __('messages.purchase') }}</option>
                <option value="adjustment">{{ __('messages.adjustment') }}</option>
                <option value="return">{{ __('messages.return') }}</option>
                <option value="damage">{{ __('messages.damage') }}</option>
            </select>
            <select id="directionFilter" class="form-select form-select-sm" style="width: 120px;">
                <option value="">{{ __('messages.all_direction') }}</option>
                <option value="incoming">{{ __('messages.incoming') }}</option>
                <option value="outgoing">{{ __('messages.outgoing') }}</option>
            </select>
            <select id="productFilter" class="form-select form-select-sm" style="width: 150px;">
                <option value="">{{ __('messages.all_products') }}</option>
                @foreach($products ?? [] as $product)
                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                @endforeach
            </select>
            @can('stock.create')
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fas fa-plus-circle me-1"></i> {{ __('messages.add_movement') }}
            </button>
            @endcan
            @can('stock.view')
            <a href="{{ route('admin.stock-movements.export') }}" class="btn btn-success btn-sm">
                <i class="fas fa-file-excel me-1"></i> {{ __('messages.export') }}
            </a>
            @endcan
            @can('stock.create')
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
                        @can('stock.create')
                        <th style="width: 35px;"><input type="checkbox" id="selectAll"></th>
                        @endcan
                        <th style="width: 35px;">#</th>
                        <th style="min-width: 160px;">{{ __('messages.product') }}</th>
                        <th style="width: 100px;">{{ __('messages.type') }}</th>
                        <th style="width: 80px; text-align: center;">{{ __('messages.qty_change') }}</th>
                        <th style="width: 80px; text-align: center;">{{ __('messages.previous') }}</th>
                        <th style="width: 80px; text-align: center;">{{ __('messages.new') }}</th>
                        <th style="width: 100px;">{{ __('messages.user') }}</th>
                        <th style="width: 100px;">{{ __('messages.date') }}</th>
                        <th style="width: 120px;">{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($movements as $movement)
                    <tr data-id="{{ $movement->id }}" data-type="{{ $movement->type }}" data-product="{{ $movement->product_id }}">
                        @can('stock.create')
                        <td><input type="checkbox" class="row-checkbox" value="{{ $movement->id }}"></td>
                        @endcan
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle bg-primary text-white me-2"><i class="fas fa-box"></i></div>
                                <div>
                                    <div class="fw-bold small">{{ Str::limit($movement->product->name ?? 'N/A', 20) }}</div>
                                    <small class="text-muted">{{ $movement->product->sku ?? 'N/A' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            @php
                                $typeColors = ['purchase' => 'success','return' => 'info','adjustment' => 'warning text-dark','damage' => 'danger'];
                                $typeIcons  = ['purchase' => 'fa-shopping-cart','return' => 'fa-undo-alt','adjustment' => 'fa-sliders-h','damage' => 'fa-exclamation-triangle'];
                            @endphp
                            <span class="badge bg-{{ $typeColors[$movement->type] ?? 'secondary' }}">
                                <i class="fas {{ $typeIcons[$movement->type] ?? 'fa-exchange-alt' }} me-1"></i>
                                {{ __('messages.' . $movement->type) }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="fw-bold {{ $movement->quantity_change > 0 ? 'text-success' : 'text-danger' }}">
                                {{ $movement->quantity_change > 0 ? '+' : '' }}{{ $movement->quantity_change }}
                            </span>
                        </td>
                        <td class="text-center">{{ $movement->previous_quantity }}</td>
                        <td class="text-center">{{ $movement->new_quantity }}</td>
                        <td><small>{{ $movement->user->name ?? 'N/A' }}</small></td>
                        <td>
                            <small>{{ $movement->created_at->format('M d, Y H:i') }}</small><br>
                            <small class="text-muted">{{ $movement->created_at->diffForHumans() }}</small>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                @can('stock.view')
                                <button class="btn btn-outline-primary btn-sm view-btn" data-id="{{ $movement->id }}" title="{{ __('messages.view') }}"><i class="fas fa-eye"></i></button>
                                @endcan
                                @can('stock.create')
                                <button class="btn btn-outline-danger btn-sm delete-btn" data-id="{{ $movement->id }}" title="{{ __('messages.delete') }}"><i class="fas fa-trash"></i></button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ auth()->user()->can('stock.create') ? 10 : 9 }}">
                            <div class="text-center py-5">
                                <i class="fas fa-exchange-alt fa-4x text-muted mb-3 d-block"></i>
                                <h5 class="fw-bold">{{ __('messages.no_stock_movements') }}</h5>
                                <p class="text-muted">{{ __('messages.stock_movements_logged_automatically') }}</p>
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
                {{ __('messages.showing') }} {{ $movements->firstItem() ?? 0 }}
                {{ __('messages.to') }} {{ $movements->lastItem() ?? 0 }}
                {{ __('messages.of') }} {{ $movements->total() }}
                {{ __('messages.entries') }}
            </span>
        </div>
        <div>{{ $movements->links() }}</div>
    </div>
</div>

<!-- CREATE MODAL -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle text-primary me-2"></i> {{ __('messages.add_stock_movement') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createForm" action="{{ route('admin.stock-movements.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div id="createFormErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">{{ __('messages.product') }} <span class="text-danger">*</span></label>
                            <select name="product_id" class="form-select" required>
                                <option value="">{{ __('messages.select_product') }}</option>
                                @foreach($products ?? [] as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }}) - {{ __('messages.current') }}: {{ $product->quantity }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.type') }} <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required>
                                <option value="purchase">{{ __('messages.purchase') }}</option>
                                <option value="adjustment">{{ __('messages.adjustment') }}</option>
                                <option value="return">{{ __('messages.return') }}</option>
                                <option value="damage">{{ __('messages.damage') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.quantity_change') }} <span class="text-danger">*</span></label>
                            <input type="number" name="quantity_change" class="form-control" placeholder="{{ __('messages.enter_quantity_sign') }}" required>
                            <small class="text-muted">{{ __('messages.positive_incoming_negative_outgoing') }}</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">{{ __('messages.reference') }}</label>
                            <input type="text" name="reference" class="form-control" placeholder="{{ __('messages.reference_placeholder') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">{{ __('messages.notes') }}</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="{{ __('messages.additional_notes') }}"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> {{ __('messages.cancel') }}</button>
                    <button type="submit" class="btn btn-primary" id="createSubmitBtn"><i class="fas fa-save me-1"></i> {{ __('messages.record_movement') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- VIEW MODAL -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-eye text-info me-2"></i> {{ __('messages.stock_movement_details') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewModalBody">
                <div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">{{ __('messages.loading') }}...</span></div></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.close') }}</button>
            </div>
        </div>
    </div>
</div>

<!-- DELETE MODAL -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger"><i class="fas fa-exclamation-triangle me-2"></i> {{ __('messages.confirm_delete') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-trash fa-4x text-danger mb-3 d-block"></i>
                <p class="fw-bold">{{ __('messages.are_you_sure_delete_movement') }}</p>
                <p class="text-muted small">{{ __('messages.reverse_stock_change_warning') }}</p>
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
</style>

@push('scripts')
<script>
window.trans = {
    loading: @json(__('messages.loading')),
    close: @json(__('messages.close')),
    cancel: @json(__('messages.cancel')),
    delete: @json(__('messages.delete')),
    product: @json(__('messages.product')),
    type: @json(__('messages.type')),
    quantity_change: @json(__('messages.quantity_change')),
    previous_quantity: @json(__('messages.previous_quantity')),
    new_quantity: @json(__('messages.new_quantity')),
    user: @json(__('messages.user')),
    reference: @json(__('messages.reference')),
    created: @json(__('messages.created')),
    notes: @json(__('messages.notes')),
    purchase: @json(__('messages.purchase')),
    adjustment: @json(__('messages.adjustment')),
    return: @json(__('messages.return')),
    damage: @json(__('messages.damage')),
    // Alerts
    no_items_selected: @json(__('messages.no_items_selected')),
    select_at_least_one: @json(__('messages.select_at_least_one')),
    delete_selected_movements_q: @json(__('messages.delete_selected_movements_q')),
    delete_movements_warning: @json(__('messages.delete_movements_warning')),
    yes_delete_them: @json(__('messages.yes_delete_them')),
    deleting: @json(__('messages.deleting')),
    deleted: @json(__('messages.deleted')),
    error_title: @json(__('messages.error')),
    failed_to_delete_selected: @json(__('messages.failed_to_delete_selected')),
    failed_to_load_details: @json(__('messages.failed_to_load_details')),
    recorded: @json(__('messages.recorded')),
    recording: @json(__('messages.recording')),
    record_movement: @json(__('messages.record_movement')),
    something_went_wrong: @json(__('messages.something_went_wrong')),
    failed_to_record: @json(__('messages.failed_to_record')),
    are_you_sure: @json(__('messages.are_you_sure')),
    reverse_stock_change_warning: @json(__('messages.reverse_stock_change_warning')),
    yes_delete: @json(__('messages.yes_delete')),
    failed_to_delete: @json(__('messages.failed_to_delete')),
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

    // BULK SELECT
    @can('stock.create')
    $('#selectAll').on('change', function() { $('.row-checkbox').prop('checked', $(this).prop('checked')); });

    $('#bulkDeleteBtn').on('click', function() {
        var selected = $('.row-checkbox:checked');
        if (selected.length === 0) {
            Swal.fire({ icon: 'warning', title: trans.no_items_selected, text: trans.select_at_least_one });
            return;
        }
        Swal.fire({
            title: trans.delete_selected_movements_q,
            text: trans.delete_movements_warning.replace(':count', selected.length),
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: trans.yes_delete_them,
            cancelButtonText: trans.cancel
        }).then((result) => {
            if (result.isConfirmed) {
                var ids = [];
                selected.each(function() { ids.push($(this).val()); });
                Swal.fire({ title: trans.deleting, allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                $.ajax({
                    url: '{{ route("admin.stock-movements.bulk-delete") }}',
                    type: 'POST', data: { movement_ids: ids }, dataType: 'json',
                    success: function(r) {
                        if (r.success) {
                            Swal.fire({ icon: 'success', title: trans.deleted, text: r.message, timer: 2000, showConfirmButton: true }).then(() => window.location.reload());
                        }
                    },
                    error: function(xhr) {
                        Swal.close();
                        Swal.fire({ icon: 'error', title: trans.error_title, text: xhr.responseJSON?.error || trans.failed_to_delete_selected });
                    }
                });
            }
        });
    });
    @endcan

    // VIEW
    @can('stock.view')
    $(document).on('click', '.view-btn', function() {
        var id = $(this).data('id');
        var body = $('#viewModalBody');
        body.html(`<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">${trans.loading}...</span></div></div>`);
        $('#viewModal').modal('show');
        $.ajax({
            url: '/admin/stock-movements/' + id, type: 'GET', dataType: 'json',
            success: function(r) {
                body.html(`
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">${trans.product}</label>
                            <p class="fw-bold mb-0">${r.product ? r.product.name : 'N/A'}</p>
                            <small class="text-muted">SKU: ${r.product ? r.product.sku : 'N/A'}</small>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">${trans.type}</label>
                            <p class="mb-0"><span class="badge bg-${r.type == 'purchase' ? 'success' : (r.type == 'return' ? 'info' : (r.type == 'adjustment' ? 'warning text-dark' : 'danger'))}">${trans[r.type] || r.type}</span></p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">${trans.quantity_change}</label>
                            <p class="fw-bold mb-0 ${r.quantity_change > 0 ? 'text-success' : 'text-danger'}">${r.quantity_change > 0 ? '+' : ''}${r.quantity_change}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">${trans.user}</label>
                            <p class="mb-0">${r.user ? r.user.name : 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">${trans.previous_quantity}</label>
                            <p class="mb-0">${r.previous_quantity}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">${trans.new_quantity}</label>
                            <p class="mb-0">${r.new_quantity}</p>
                        </div>
                        ${r.reference ? `<div class="col-md-6"><label class="text-muted small fw-bold">${trans.reference}</label><p class="mb-0"><span class="badge bg-secondary">${r.reference}</span></p></div>` : ''}
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">${trans.created}</label>
                            <p class="mb-0">${new Date(r.created_at).toLocaleString()}</p>
                        </div>
                        ${r.notes ? `<div class="col-12"><label class="text-muted small fw-bold">${trans.notes}</label><p class="mb-0">${r.notes}</p></div>` : ''}
                    </div>
                `);
            },
            error: function(xhr) {
                body.html(`<div class="text-center py-5 text-danger"><i class="fas fa-exclamation-circle fa-4x mb-3 d-block"></i><h5>${trans.failed_to_load_details}</h5><p>${xhr.status} - ${xhr.statusText}</p></div>`);
            }
        });
    });
    @endcan

    // CREATE
    @can('stock.create')
    $('#createForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = $('#createSubmitBtn');
        var errorContainer = $('#createFormErrors');
        clearErrors(errorContainer);
        btn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm me-1"></span> ${trans.recording}...`);
        $.ajax({
            url: form.attr('action'), type: 'POST', data: form.serialize(), dataType: 'json',
            success: function(r) {
                if (r.success) {
                    $('#createModal').modal('hide');
                    Swal.fire({ icon: 'success', title: trans.recorded, text: r.message, timer: 2000, showConfirmButton: true }).then(() => window.location.reload());
                } else {
                    btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.record_movement}`);
                    Swal.fire({ icon: 'error', title: trans.error_title, text: r.message || trans.something_went_wrong });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.record_movement}`);
                if (xhr.status === 422) { showErrors(errorContainer, xhr.responseJSON.errors); }
                else { Swal.fire({ icon: 'error', title: trans.error_title, text: trans.failed_to_record }); }
            }
        });
    });
    @endcan

    // DELETE
    @can('stock.create')
    $(document).on('click', '.delete-btn', function() {
        var id = $(this).data('id');
        $('#deleteForm').attr('action', '/admin/stock-movements/' + id);
        Swal.fire({
            title: trans.are_you_sure,
            text: trans.reverse_stock_change_warning,
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
        Swal.fire({ title: trans.deleting, allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        $.ajax({
            url: $(this).attr('action'), type: 'POST',
            data: { _token: '{{ csrf_token() }}', _method: 'DELETE' }, dataType: 'json',
            success: function(r) {
                if (r.success) {
                    $('#deleteModal').modal('hide');
                    Swal.fire({ icon: 'success', title: trans.deleted, text: r.message, timer: 2000, showConfirmButton: true }).then(() => window.location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: trans.error_title, text: r.message || trans.failed_to_delete });
                }
            },
            error: function(xhr) {
                Swal.close();
                Swal.fire({ icon: 'error', title: trans.error_title, text: xhr.responseJSON?.error || trans.failed_to_delete });
            }
        });
    });
    @endcan

    // SEARCH & FILTER
    $('#searchBtn').on('click', filterTable);
    $('#searchInput').on('keypress', function(e) { if (e.which === 13) filterTable(); });
    $('#typeFilter, #directionFilter, #productFilter').on('change', filterTable);

    function filterTable() {
        var search = $('#searchInput').val().toLowerCase();
        var type = $('#typeFilter').val();
        var direction = $('#directionFilter').val();
        var product = $('#productFilter').val();
        $('#tableBody tr').each(function() {
            var row = $(this);
            var text = row.text().toLowerCase();
            var show = true;
            if (search && !text.includes(search)) show = false;
            if (type && row.data('type') !== type) show = false;
            if (product && row.data('product') != product) show = false;
            if (direction) {
                var qtyText = row.find('td:eq(4)').text().trim();
                var isIncoming = qtyText.startsWith('+') || (!qtyText.startsWith('-') && qtyText !== '0');
                if (direction === 'incoming' && !isIncoming) show = false;
                if (direction === 'outgoing' && isIncoming) show = false;
            }
            row.toggle(show);
        });
    }

    // MODAL CLEANUP
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