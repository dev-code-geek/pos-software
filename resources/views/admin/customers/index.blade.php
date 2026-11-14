@extends('layouts.admin')

@section('title', __('messages.customers'))
@section('page-title', __('messages.customer_management'))

@section('admin-content')
<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ $stats['total'] ?? 0 }}</h3>
                <p>{{ __('messages.total_customers') }}</p>
            </div>
            <div class="icon"><i class="fas fa-users"></i></div>
            <a href="{{ route('admin.customers.index') }}" class="small-box-footer">
                {{ __('messages.view_all') }} <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $stats['active'] ?? 0 }}</h3>
                <p>{{ __('messages.active_customers') }}</p>
            </div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
            <a href="{{ route('admin.customers.index', ['status' => 'active']) }}" class="small-box-footer">
                {{ __('messages.view_active') }} <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $stats['with_sales'] ?? 0 }}</h3>
                <p>{{ __('messages.with_sales') }}</p>
            </div>
            <div class="icon"><i class="fas fa-shopping-cart"></i></div>
            <a href="{{ route('admin.customers.index', ['has_sales' => 'yes']) }}" class="small-box-footer">
                {{ __('messages.view_with_sales') }} <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $stats['without_sales'] ?? 0 }}</h3>
                <p>{{ __('messages.without_sales') }}</p>
            </div>
            <div class="icon"><i class="fas fa-user-plus"></i></div>
            <a href="{{ route('admin.customers.index', ['has_sales' => 'no']) }}" class="small-box-footer">
                {{ __('messages.view_without_sales') }} <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Main Card -->
<div class="card">
    <div class="card-header">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <h5 class="card-title mb-0">
                <i class="fas fa-users text-primary me-2"></i>
                {{ __('messages.all_customers') }}
                <span class="badge bg-primary ms-1">{{ $customers->total() }}</span>
            </h5>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <div class="input-group input-group-sm" style="width: 200px;">
                <input type="text" id="searchInput" class="form-control" placeholder="{{ __('messages.search') }}...">
                <button class="btn btn-secondary" type="button" id="searchBtn"><i class="fas fa-search"></i></button>
            </div>
            <select id="hasSalesFilter" class="form-select form-select-sm" style="width: 140px;">
                <option value="">{{ __('messages.all_customers') }}</option>
                <option value="yes">{{ __('messages.with_sales') }}</option>
                <option value="no">{{ __('messages.without_sales') }}</option>
            </select>
            <select id="statusFilter" class="form-select form-select-sm" style="width: 120px;">
                <option value="">{{ __('messages.all_status') }}</option>
                <option value="active">{{ __('messages.active') }}</option>
                <option value="inactive">{{ __('messages.inactive') }}</option>
            </select>
            @can('customer.create')
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fas fa-plus-circle me-1"></i> {{ __('messages.add_customer') }}
            </button>
            @endcan
            @can('customer.export')
            <a href="{{ route('admin.customers.export') }}" class="btn btn-success btn-sm">
                <i class="fas fa-file-excel me-1"></i> {{ __('messages.export') }}
            </a>
            @endcan
            @can('customer.delete')
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
                        @can('customer.delete')
                        <th style="width: 35px;"><input type="checkbox" id="selectAll"></th>
                        @endcan
                        <th style="width: 35px;">#</th>
                        <th style="min-width: 160px;">{{ __('messages.customer') }}</th>
                        <th>{{ __('messages.email') }}</th>
                        <th>{{ __('messages.phone') }}</th>
                        <th>{{ __('messages.type') }}</th>
                        <th style="width: 80px; text-align: center;">{{ __('messages.sales') }}</th>
                        <th style="width: 100px;">{{ __('messages.status') }}</th>
                        <th style="width: 200px;">{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($customers as $customer)
                    <tr data-id="{{ $customer->id }}"
                        data-has-sales="{{ $customer->sales_count > 0 ? 'yes' : 'no' }}"
                        data-status="{{ $customer->is_active ? 'active' : 'inactive' }}">
                        @can('customer.delete')
                        <td>
                            @if($customer->sales_count == 0)
                            <input type="checkbox" class="row-checkbox" value="{{ $customer->id }}">
                            @endif
                        </td>
                        @endcan
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle bg-primary text-white me-2">
                                    {{ strtoupper(substr($customer->name, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="fw-bold small">{{ Str::limit($customer->name, 25) }}</div>
                                    <small class="text-muted">ID: #{{ $customer->id }}</small>
                                </div>
                            </div>
                        </td>
                        <td>{{ $customer->email ?? 'N/A' }}</td>
                        <td>{{ $customer->phone ?? 'N/A' }}</td>
                        <td>{{ $customer->type ? __('messages.' . $customer->type) : 'N/A' }}</td>
                        <td class="text-center">
                            <span class="badge bg-{{ $customer->sales_count > 0 ? 'success' : 'secondary' }}">
                                {{ $customer->sales_count ?? 0 }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $customer->is_active ? 'success' : 'secondary' }}">
                                {{ $customer->is_active ? __('messages.active') : __('messages.inactive') }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                @can('customer.view')
                                <button class="btn btn-outline-primary btn-sm view-btn" data-id="{{ $customer->id }}" title="{{ __('messages.view') }}">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @endcan
                                @can('customer.edit')
                                <button class="btn btn-outline-warning btn-sm edit-btn" data-id="{{ $customer->id }}" title="{{ __('messages.edit') }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @endcan
                                @can('customer.edit')
                                <button class="btn btn-outline-{{ $customer->is_active ? 'secondary' : 'success' }} btn-sm toggle-status-btn"
                                        data-id="{{ $customer->id }}"
                                        title="{{ $customer->is_active ? __('messages.deactivate') : __('messages.activate') }}">
                                    <i class="fas fa-{{ $customer->is_active ? 'pause' : 'play' }}"></i>
                                </button>
                                @endcan
                                @can('customer.delete')
                                @if($customer->sales_count == 0)
                                <button class="btn btn-outline-danger btn-sm delete-btn" data-id="{{ $customer->id }}" title="{{ __('messages.delete') }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ auth()->user()->can('customer.delete') ? 9 : 8 }}">
                            <div class="text-center py-5">
                                <i class="fas fa-users fa-4x text-muted mb-3 d-block"></i>
                                <h5 class="fw-bold">{{ __('messages.no_customers_found') }}</h5>
                                <p class="text-muted">{{ __('messages.click_add_customer') }}</p>
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
                {{ __('messages.showing') }} {{ $customers->firstItem() ?? 0 }}
                {{ __('messages.to') }} {{ $customers->lastItem() ?? 0 }}
                {{ __('messages.of') }} {{ $customers->total() }}
                {{ __('messages.entries') }}
            </span>
        </div>
        <div>{{ $customers->links() }}</div>
    </div>
</div>

<!-- CREATE MODAL -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white">
                    <i class="fas fa-plus-circle me-2"></i> {{ __('messages.add_customer') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="createForm" action="{{ route('admin.customers.store') }}" method="POST">
                @csrf
                <div class="modal-body" style="padding: 20px 25px;">
                    <div id="createFormErrors" class="alert alert-danger d-none"></div>
                    <div class="mb-3">
                        <label for="customer_name" class="form-label fw-bold">{{ __('messages.customer_name') }} <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="customer_name" class="form-control" required placeholder="{{ __('messages.enter_customer_name') }}">
                    </div>
                    <div class="mb-3">
                        <label for="customer_email" class="form-label fw-bold">{{ __('messages.email') }}</label>
                        <input type="email" name="email" id="customer_email" class="form-control" placeholder="{{ __('messages.enter_email') }}">
                    </div>
                    <div class="mb-3">
                        <label for="customer_phone" class="form-label fw-bold">{{ __('messages.phone') }}</label>
                        <input type="text" name="phone" id="customer_phone" class="form-control" placeholder="{{ __('messages.enter_phone') }}">
                    </div>
                    <div class="mb-3">
                        <label for="customer_address" class="form-label fw-bold">{{ __('messages.address') }}</label>
                        <textarea name="address" id="customer_address" class="form-control" rows="2" placeholder="{{ __('messages.enter_address') }}"></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="customer_tax_id" class="form-label fw-bold">{{ __('messages.tax_id') }}</label>
                            <input type="text" name="tax_id" id="customer_tax_id" class="form-control" placeholder="{{ __('messages.tax_id') }}">
                        </div>
                        <div class="col-md-6">
                            <label for="customer_type" class="form-label fw-bold">{{ __('messages.type') }}</label>
                            <select name="type" id="customer_type" class="form-select">
                                <option value="">{{ __('messages.select_type') }}</option>
                                <option value="individual">{{ __('messages.individual') }}</option>
                                <option value="company">{{ __('messages.company') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="customer_credit_limit" class="form-label fw-bold">{{ __('messages.credit_limit') }}</label>
                        <input type="number" name="credit_limit" id="customer_credit_limit" class="form-control" step="0.01" min="0" placeholder="0.00">
                    </div>
                    <div class="form-check form-switch">
                        <input type="checkbox" name="is_active" class="form-check-input" id="create_active" checked>
                        <label class="form-check-label" for="create_active">{{ __('messages.active') }}</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> {{ __('messages.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary" id="createSubmitBtn">
                        <i class="fas fa-save me-1"></i> {{ __('messages.save_customer') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- VIEW MODAL -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title text-white">
                    <i class="fas fa-eye me-2"></i> {{ __('messages.customer_details') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewModalBody" style="padding: 20px 25px;">
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
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title text-dark">
                    <i class="fas fa-edit me-2"></i> {{ __('messages.edit_customer') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body" id="editModalBody" style="padding: 20px 25px;">
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
                        <i class="fas fa-save me-1"></i> {{ __('messages.update_customer') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- DELETE MODAL -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white">
                    <i class="fas fa-exclamation-triangle me-2"></i> {{ __('messages.confirm_delete') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center" style="padding: 20px 25px;">
                <i class="fas fa-trash fa-4x text-danger mb-3 d-block"></i>
                <p class="fw-bold">{{ __('messages.are_you_sure_delete_customer') }}</p>
                <p class="text-muted small">{{ __('messages.this_action_cannot_be_undone') }}</p>
                <form id="deleteForm" method="POST">@csrf @method('DELETE')</form>
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
.avatar-circle { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 600; flex-shrink: 0; }
.small-box { border-radius: 12px; padding: 20px; margin-bottom: 0; position: relative; overflow: hidden; transition: transform 0.15s ease; }
.small-box:hover { transform: translateY(-2px); }
.small-box .inner { color: #fff; }
.small-box .inner h3 { font-size: 2rem; font-weight: 700; margin: 0 0 5px; }
.small-box .inner p { font-size: 0.9rem; opacity: 0.9; margin: 0; }
.small-box .icon { position: absolute; right: 15px; top: 15px; font-size: 2.5rem; opacity: 0.25; color: #fff; }
.small-box .small-box-footer { display: block; padding: 8px 0 0; color: rgba(255,255,255,0.8); text-decoration: none; font-size: 0.85rem; transition: all 0.15s ease; }
.small-box .small-box-footer:hover { color: #fff; }
.small-box .small-box-footer i { transition: transform 0.15s ease; }
.small-box:hover .small-box-footer i { transform: translateX(4px); }
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
    customer_name: @json(__('messages.customer_name')),
    email: @json(__('messages.email')),
    phone: @json(__('messages.phone')),
    address: @json(__('messages.address')),
    tax_id: @json(__('messages.tax_id')),
    type: @json(__('messages.type')),
    individual: @json(__('messages.individual')),
    company: @json(__('messages.company')),
    credit_limit: @json(__('messages.credit_limit')),
    active: @json(__('messages.active')),
    inactive: @json(__('messages.inactive')),
    status: @json(__('messages.status')),
    customer_details: @json(__('messages.customer_details')),
    statistics: @json(__('messages.statistics')),
    total_sales: @json(__('messages.total_sales')),
    total_sales_amount: @json(__('messages.total_sales_amount')),
    created: @json(__('messages.created')),
    last_updated: @json(__('messages.last_updated')),
    // Alerts
    no_items_selected: @json(__('messages.no_items_selected')),
    select_at_least_one_customer: @json(__('messages.select_at_least_one_customer')),
    delete_selected_customers_q: @json(__('messages.delete_selected_customers_q')),
    delete_customers_warning: @json(__('messages.delete_customers_warning')),
    yes_delete_them: @json(__('messages.yes_delete_them')),
    deleting: @json(__('messages.deleting')),
    deleted: @json(__('messages.deleted')),
    added: @json(__('messages.added')),
    updated: @json(__('messages.updated')),
    error_title: @json(__('messages.error')),
    something_went_wrong: @json(__('messages.something_went_wrong')),
    failed_to_load_details: @json(__('messages.failed_to_load_details')),
    failed_to_load: @json(__('messages.failed_to_load')),
    failed_to_create: @json(__('messages.failed_to_create')),
    failed_to_update: @json(__('messages.failed_to_update')),
    failed_to_delete: @json(__('messages.failed_to_delete')),
    failed_to_delete_selected: @json(__('messages.failed_to_delete_selected')),
    toggle_status_q: @json(__('messages.toggle_status_q')),
    toggle_status_warning_customer: @json(__('messages.toggle_status_warning_customer')),
    yes_toggle_it: @json(__('messages.yes_toggle_it')),
    are_you_sure: @json(__('messages.are_you_sure')),
    wont_be_able_to_revert: @json(__('messages.wont_be_able_to_revert')),
    yes_delete: @json(__('messages.yes_delete')),
    saving: @json(__('messages.saving')),
    updating: @json(__('messages.updating')),
    save_customer: @json(__('messages.save_customer')),
    update_customer: @json(__('messages.update_customer')),
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

    // BULK SELECT
    @can('customer.delete')
    $('#selectAll').on('change', function() { $('.row-checkbox').prop('checked', $(this).prop('checked')); });
    $('#bulkDeleteBtn').on('click', function() {
        var selected = $('.row-checkbox:checked');
        if (selected.length === 0) {
            Swal.fire({ icon: 'warning', title: trans.no_items_selected, text: trans.select_at_least_one_customer });
            return;
        }
        Swal.fire({
            title: trans.delete_selected_customers_q,
            text: trans.delete_customers_warning.replace(':count', selected.length),
            icon: 'warning', showCancelButton: true,
            confirmButtonColor: '#d33', cancelButtonColor: '#6c757d',
            confirmButtonText: trans.yes_delete_them, cancelButtonText: trans.cancel
        }).then((result) => {
            if (result.isConfirmed) {
                var ids = []; selected.each(function() { ids.push($(this).val()); });
                Swal.fire({ title: trans.deleting, allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                $.ajax({
                    url: '{{ route("admin.customers.bulk-delete") }}',
                    type: 'POST', data: { customer_ids: ids, _token: '{{ csrf_token() }}' }, dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({ icon: 'success', title: trans.deleted, text: response.message, timer: 2000, showConfirmButton: true }).then(() => window.location.reload());
                        } else {
                            Swal.fire({ icon: 'error', title: trans.error_title, text: response.message || trans.failed_to_delete_selected });
                        }
                    },
                    error: function(xhr) {
                        Swal.close();
                        Swal.fire({ icon: 'error', title: trans.error_title, text: xhr.responseJSON?.message || trans.failed_to_delete_selected });
                    }
                });
            }
        });
    });
    @endcan

    // VIEW
    @can('customer.view')
    $(document).on('click', '.view-btn', function() {
        var id = $(this).data('id');
        var body = $('#viewModalBody');
        body.html(`<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">${trans.loading}...</span></div></div>`);
        $('#viewModal').modal('show');
        $.ajax({
            url: '/admin/customers/' + id, type: 'GET', dataType: 'json',
            success: function(response) {
                if (!response.success) {
                    body.html(`<div class="text-center py-5 text-danger"><i class="fas fa-exclamation-circle fa-4x mb-3 d-block"></i><h5>${trans.error_title}</h5><p>${response.message || trans.failed_to_load_details}</p></div>`);
                    return;
                }
                var c = response.customer;
                var totalSales = response.total_sales || 0;
                var typeLabel = c.type ? (trans[c.type] || c.type) : 'N/A';
                var html = `
                    <div class="row g-3">
                        <div class="col-12"><label class="text-muted small fw-bold">${trans.customer_name}</label><p class="fw-bold mb-0">${c.name}</p></div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">${trans.email}</label><p class="mb-0">${c.email || 'N/A'}</p></div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">${trans.phone}</label><p class="mb-0">${c.phone || 'N/A'}</p></div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">${trans.type}</label><p class="mb-0">${typeLabel}</p></div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">${trans.status}</label>
                            <p class="mb-0"><span class="badge bg-${c.is_active ? 'success' : 'secondary'}">${c.is_active ? trans.active : trans.inactive}</span></p>
                        </div>
                        ${c.address ? `<div class="col-12"><label class="text-muted small fw-bold">${trans.address}</label><p class="mb-0">${c.address}</p></div>` : ''}
                        ${c.tax_id ? `<div class="col-md-6"><label class="text-muted small fw-bold">${trans.tax_id}</label><p class="mb-0">${c.tax_id}</p></div>` : ''}
                        ${c.credit_limit ? `<div class="col-md-6"><label class="text-muted small fw-bold">${trans.credit_limit}</label><p class="mb-0">${parseFloat(c.credit_limit).toFixed(2)}</p></div>` : ''}
                        <div class="col-12">
                            <hr>
                            <h6 class="fw-bold text-primary">${trans.statistics}</h6>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="text-center p-2 bg-light rounded">
                                        <span class="d-block fw-bold">${c.sales_count || 0}</span>
                                        <small class="text-muted">${trans.total_sales}</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-center p-2 bg-info bg-opacity-10 rounded">
                                        <span class="d-block fw-bold text-info">${parseFloat(totalSales).toFixed(2)}</span>
                                        <small class="text-muted">${trans.total_sales_amount}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12"><label class="text-muted small fw-bold">${trans.created}</label><p class="mb-0">${new Date(c.created_at).toLocaleString()}</p></div>
                        ${c.updated_at ? `<div class="col-12"><label class="text-muted small fw-bold">${trans.last_updated}</label><p class="mb-0">${new Date(c.updated_at).toLocaleString()}</p></div>` : ''}
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
    @can('customer.edit')
    $(document).on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        var body = $('#editModalBody');
        var form = $('#editForm');
        form.attr('action', '/admin/customers/' + id);
        body.html(`<div id="editFormErrors" class="alert alert-danger d-none"></div><div class="text-center py-5"><div class="spinner-border text-warning" role="status"><span class="visually-hidden">${trans.loading}...</span></div></div>`);
        $('#editModal').modal('show');
        $.ajax({
            url: '/admin/customers/' + id + '/edit', type: 'GET', dataType: 'json',
            success: function(response) {
                if (!response.success) {
                    body.html(`<div id="editFormErrors" class="alert alert-danger"></div><div class="text-center py-5 text-danger"><i class="fas fa-exclamation-circle fa-4x mb-3 d-block"></i><h5>${trans.error_title}</h5><p>${response.message || trans.failed_to_load}</p></div>`);
                    return;
                }
                var r = response.customer;
                body.html(`
                    <div id="editFormErrors" class="alert alert-danger d-none"></div>
                    <div class="mb-3"><label for="edit_name" class="form-label fw-bold">${trans.customer_name} <span class="text-danger">*</span></label><input type="text" name="name" id="edit_name" class="form-control" value="${r.name || ''}" required></div>
                    <div class="mb-3"><label for="edit_email" class="form-label fw-bold">${trans.email}</label><input type="email" name="email" id="edit_email" class="form-control" value="${r.email || ''}"></div>
                    <div class="mb-3"><label for="edit_phone" class="form-label fw-bold">${trans.phone}</label><input type="text" name="phone" id="edit_phone" class="form-control" value="${r.phone || ''}"></div>
                    <div class="mb-3"><label for="edit_address" class="form-label fw-bold">${trans.address}</label><textarea name="address" id="edit_address" class="form-control" rows="2">${r.address || ''}</textarea></div>
                    <div class="row g-3">
                        <div class="col-md-6"><label for="edit_tax_id" class="form-label fw-bold">${trans.tax_id}</label><input type="text" name="tax_id" id="edit_tax_id" class="form-control" value="${r.tax_id || ''}"></div>
                        <div class="col-md-6"><label for="edit_type" class="form-label fw-bold">${trans.type}</label>
                            <select name="type" id="edit_type" class="form-select">
                                <option value=""></option>
                                <option value="individual" ${r.type === 'individual' ? 'selected' : ''}>${trans.individual}</option>
                                <option value="company" ${r.type === 'company' ? 'selected' : ''}>${trans.company}</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3"><label for="edit_credit_limit" class="form-label fw-bold">${trans.credit_limit}</label><input type="number" name="credit_limit" id="edit_credit_limit" class="form-control" step="0.01" min="0" value="${r.credit_limit || 0}"></div>
                    <div class="form-check form-switch"><input type="checkbox" name="is_active" class="form-check-input" id="edit_active" ${r.is_active ? 'checked' : ''} value="1"><label class="form-check-label" for="edit_active">${trans.active}</label></div>
                    <div class="mt-2"><small class="text-muted">${trans.created}: ${r.created_at ? new Date(r.created_at).toLocaleString() : 'N/A'}</small></div>
                `);
            },
            error: function(xhr) {
                body.html(`<div id="editFormErrors" class="alert alert-danger"></div><div class="text-center py-5 text-danger"><i class="fas fa-exclamation-circle fa-4x mb-3 d-block"></i><h5>${trans.failed_to_load}</h5><p>${xhr.status} - ${xhr.statusText}</p></div>`);
            }
        });
    });
    @endcan

    // TOGGLE STATUS
    @can('customer.edit')
    $(document).on('click', '.toggle-status-btn', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: trans.toggle_status_q,
            text: trans.toggle_status_warning_customer,
            icon: 'question', showCancelButton: true,
            confirmButtonColor: '#198754', cancelButtonColor: '#6c757d',
            confirmButtonText: trans.yes_toggle_it, cancelButtonText: trans.cancel
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: trans.updating, allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                $.ajax({
                    url: '/admin/customers/' + id + '/toggle-status', type: 'POST',
                    data: { _token: '{{ csrf_token() }}' }, dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({ icon: 'success', title: trans.updated, text: response.message, timer: 2000, showConfirmButton: true }).then(() => window.location.reload());
                        } else {
                            Swal.fire({ icon: 'error', title: trans.error_title, text: response.message || trans.failed_to_update });
                        }
                    },
                    error: function(xhr) {
                        Swal.close();
                        Swal.fire({ icon: 'error', title: trans.error_title, text: xhr.responseJSON?.message || trans.failed_to_update });
                    }
                });
            }
        });
    });
    @endcan

    // CREATE
    @can('customer.create')
    $('#createForm').on('submit', function(e) {
        e.preventDefault();
        var btn = $('#createSubmitBtn');
        var errorContainer = $('#createFormErrors');
        clearErrors(errorContainer);
        btn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm me-1"></span> ${trans.saving}...`);
        $.ajax({
            url: $(this).attr('action'), type: 'POST', data: $(this).serialize(), dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#createModal').modal('hide');
                    Swal.fire({ icon: 'success', title: trans.added, text: response.message, timer: 2000, showConfirmButton: true }).then(() => window.location.reload());
                } else {
                    btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.save_customer}`);
                    if (response.errors) showErrors(errorContainer, response.errors);
                    else Swal.fire({ icon: 'error', title: trans.error_title, text: response.message || trans.something_went_wrong });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.save_customer}`);
                if (xhr.status === 422) showErrors(errorContainer, xhr.responseJSON.errors);
                else Swal.fire({ icon: 'error', title: trans.error_title, text: trans.failed_to_create });
            }
        });
    });
    @endcan

    // UPDATE
    @can('customer.edit')
    $('#editForm').on('submit', function(e) {
        e.preventDefault();
        var btn = $('#editSubmitBtn');
        var errorContainer = $('#editFormErrors');
        clearErrors(errorContainer);
        btn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm me-1"></span> ${trans.updating}...`);
        $.ajax({
            url: $(this).attr('action'), type: 'POST', data: $(this).serialize(), dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#editModal').modal('hide');
                    Swal.fire({ icon: 'success', title: trans.updated, text: response.message, timer: 2000, showConfirmButton: true }).then(() => window.location.reload());
                } else {
                    btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.update_customer}`);
                    if (response.errors) showErrors(errorContainer, response.errors);
                    else Swal.fire({ icon: 'error', title: trans.error_title, text: response.message || trans.something_went_wrong });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.update_customer}`);
                if (xhr.status === 422) showErrors(errorContainer, xhr.responseJSON.errors);
                else Swal.fire({ icon: 'error', title: trans.error_title, text: trans.failed_to_update });
            }
        });
    });
    @endcan

    // DELETE
    @can('customer.delete')
    $(document).on('click', '.delete-btn', function() {
        var id = $(this).data('id');
        $('#deleteForm').attr('action', '/admin/customers/' + id);
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
            data: { _token: '{{ csrf_token() }}', _method: 'DELETE' }, dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#deleteModal').modal('hide');
                    Swal.fire({ icon: 'success', title: trans.deleted, text: response.message, timer: 2000, showConfirmButton: true }).then(() => window.location.reload());
                } else {
                    btn.prop('disabled', false).html(`<i class="fas fa-trash me-1"></i> ${trans.delete}`);
                    Swal.fire({ icon: 'error', title: trans.error_title, text: response.message || trans.failed_to_delete });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(`<i class="fas fa-trash me-1"></i> ${trans.delete}`);
                Swal.fire({ icon: 'error', title: trans.error_title, text: xhr.responseJSON?.message || trans.failed_to_delete });
            }
        });
    });
    @endcan

    // SEARCH & FILTER
    $('#searchBtn').on('click', filterTable);
    $('#searchInput').on('keypress', function(e) { if (e.which === 13) filterTable(); });
    $('#hasSalesFilter, #statusFilter').on('change', filterTable);
    function filterTable() {
        var search = $('#searchInput').val().toLowerCase();
        var hasSales = $('#hasSalesFilter').val();
        var status = $('#statusFilter').val();
        $('#tableBody tr').each(function() {
            var row = $(this);
            var show = true;
            if (search && !row.text().toLowerCase().includes(search)) show = false;
            if (hasSales && row.data('has-sales') !== hasSales) show = false;
            if (status && row.data('status') !== status) show = false;
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