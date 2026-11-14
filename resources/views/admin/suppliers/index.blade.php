@extends('layouts.admin')

@section('title', __('messages.suppliers'))
@section('page-title', __('messages.supplier_management'))

@section('admin-content')
<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ $stats['total'] ?? $suppliers->total() ?? 0 }}</h3>
                <p>{{ __('messages.total_suppliers') }}</p>
            </div>
            <div class="icon"><i class="fas fa-truck"></i></div>
            <a href="{{ route('admin.suppliers.index') }}" class="small-box-footer">
                {{ __('messages.view_all') }} <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $stats['with_products'] ?? 0 }}</h3>
                <p>{{ __('messages.with_products') }}</p>
            </div>
            <div class="icon"><i class="fas fa-box"></i></div>
            <a href="{{ route('admin.suppliers.index', ['has_products' => 'yes']) }}" class="small-box-footer">
                {{ __('messages.view_with_products') }} <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $stats['without_products'] ?? 0 }}</h3>
                <p>{{ __('messages.without_products') }}</p>
            </div>
            <div class="icon"><i class="fas fa-box-open"></i></div>
            <a href="{{ route('admin.suppliers.index', ['has_products' => 'no']) }}" class="small-box-footer">
                {{ __('messages.view_without') }} <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $stats['active_suppliers'] ?? 0 }}</h3>
                <p>{{ __('messages.active_suppliers') }}</p>
            </div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
            <a href="{{ route('admin.suppliers.index', ['is_active' => 1]) }}" class="small-box-footer">
                {{ __('messages.view_active') }} <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Main Card -->
<div class="card">
    <div class="card-header">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <h5 class="card-title mb-0">
                <i class="fas fa-truck text-primary me-2"></i>
                {{ __('messages.all_suppliers') }}
                <span class="badge bg-primary ms-1">{{ $suppliers->total() }}</span>
            </h5>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <div class="input-group input-group-sm" style="width: 200px;">
                <input type="text" id="searchInput" class="form-control" placeholder="{{ __('messages.search') }}...">
                <button class="btn btn-secondary" type="button" id="searchBtn"><i class="fas fa-search"></i></button>
            </div>
            <select id="hasProductsFilter" class="form-select form-select-sm" style="width: 140px;">
                <option value="">{{ __('messages.all_suppliers') }}</option>
                <option value="yes">{{ __('messages.with_products') }}</option>
                <option value="no">{{ __('messages.without_products') }}</option>
            </select>
            <select id="statusFilter" class="form-select form-select-sm" style="width: 120px;">
                <option value="">{{ __('messages.all_status') }}</option>
                <option value="active">{{ __('messages.active') }}</option>
                <option value="inactive">{{ __('messages.inactive') }}</option>
            </select>
            @can('supplier.create')
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fas fa-plus-circle me-1"></i> {{ __('messages.add_supplier') }}
            </button>
            @endcan
            @can('supplier.view')
            <a href="{{ route('admin.suppliers.export') }}" class="btn btn-success btn-sm">
                <i class="fas fa-file-excel me-1"></i> {{ __('messages.export') }}
            </a>
            @endcan
            @can('supplier.delete')
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
                        @can('supplier.delete')
                        <th style="width: 35px;"><input type="checkbox" id="selectAll"></th>
                        @endcan
                        <th style="width: 35px;">#</th>
                        <th style="min-width: 160px;">{{ __('messages.supplier') }}</th>
                        <th>{{ __('messages.contact_person') }}</th>
                        <th>{{ __('messages.email') }}</th>
                        <th>{{ __('messages.phone') }}</th>
                        <th style="width: 80px; text-align: center;">{{ __('messages.products') }}</th>
                        <th style="width: 100px;">{{ __('messages.status') }}</th>
                        <th style="width: 200px;">{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($suppliers as $supplier)
                    <tr data-id="{{ $supplier->id }}"
                        data-has-products="{{ $supplier->products_count > 0 ? 'yes' : 'no' }}"
                        data-status="{{ $supplier->is_active ? 'active' : 'inactive' }}">
                        @can('supplier.delete')
                        <td>
                            @if($supplier->products_count == 0 && $supplier->purchase_orders_count == 0)
                            <input type="checkbox" class="row-checkbox" value="{{ $supplier->id }}">
                            @endif
                        </td>
                        @endcan
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle bg-primary text-white me-2">
                                    {{ strtoupper(substr($supplier->name, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="fw-bold small">{{ Str::limit($supplier->name, 25) }}</div>
                                    <small class="text-muted">ID: #{{ $supplier->id }}</small>
                                </div>
                            </div>
                        </td>
                        <td>{{ $supplier->contact_person ?? 'N/A' }}</td>
                        <td>{{ $supplier->email ?? 'N/A' }}</td>
                        <td>{{ $supplier->phone ?? 'N/A' }}</td>
                        <td class="text-center">
                            <span class="badge bg-{{ $supplier->products_count > 0 ? 'success' : 'secondary' }}">
                                {{ $supplier->products_count ?? 0 }}
                            </span>
                        </td>
                        <td>
                            @if($supplier->is_active)
                                <span class="badge bg-success">{{ __('messages.active') }}</span>
                            @else
                                <span class="badge bg-secondary">{{ __('messages.inactive') }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                @can('supplier.view')
                                <button class="btn btn-outline-primary btn-sm view-btn" data-id="{{ $supplier->id }}" title="{{ __('messages.view') }}">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @endcan
                                @can('supplier.edit')
                                <button class="btn btn-outline-warning btn-sm edit-btn" data-id="{{ $supplier->id }}" title="{{ __('messages.edit') }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @endcan
                                @can('supplier.edit')
                                <button class="btn btn-outline-{{ $supplier->is_active ? 'secondary' : 'success' }} btn-sm toggle-status-btn"
                                        data-id="{{ $supplier->id }}"
                                        title="{{ $supplier->is_active ? __('messages.deactivate') : __('messages.activate') }}">
                                    <i class="fas fa-{{ $supplier->is_active ? 'pause' : 'play' }}"></i>
                                </button>
                                @endcan
                                @can('supplier.delete')
                                @if($supplier->products_count == 0 && $supplier->purchase_orders_count == 0)
                                <button class="btn btn-outline-danger btn-sm delete-btn" data-id="{{ $supplier->id }}" title="{{ __('messages.delete') }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ auth()->user()->can('supplier.delete') ? 9 : 8 }}">
                            <div class="text-center py-5">
                                <i class="fas fa-truck fa-4x text-muted mb-3 d-block"></i>
                                <h5 class="fw-bold">{{ __('messages.no_suppliers_found') }}</h5>
                                <p class="text-muted">{{ __('messages.click_add_supplier') }}</p>
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
                {{ __('messages.showing') }} {{ $suppliers->firstItem() ?? 0 }}
                {{ __('messages.to') }} {{ $suppliers->lastItem() ?? 0 }}
                {{ __('messages.of') }} {{ $suppliers->total() }}
                {{ __('messages.entries') }}
            </span>
        </div>
        <div>{{ $suppliers->links() }}</div>
    </div>
</div>

<!-- CREATE MODAL -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="fas fa-plus-circle me-2"></i> {{ __('messages.add_supplier') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="createForm" action="{{ route('admin.suppliers.store') }}" method="POST">
                @csrf
                <div class="modal-body" style="padding: 20px 25px;">
                    <div id="createFormErrors" class="alert alert-danger d-none"></div>
                    <div class="mb-3">
                        <label for="supplier_name" class="form-label fw-bold">{{ __('messages.supplier_name') }} <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="supplier_name" class="form-control" required placeholder="{{ __('messages.enter_supplier_name') }}">
                    </div>
                    <div class="mb-3">
                        <label for="supplier_contact" class="form-label fw-bold">{{ __('messages.contact_person') }}</label>
                        <input type="text" name="contact_person" id="supplier_contact" class="form-control" placeholder="{{ __('messages.enter_contact_person') }}">
                    </div>
                    <div class="mb-3">
                        <label for="supplier_email" class="form-label fw-bold">{{ __('messages.email') }}</label>
                        <input type="email" name="email" id="supplier_email" class="form-control" placeholder="{{ __('messages.enter_email') }}">
                    </div>
                    <div class="mb-3">
                        <label for="supplier_phone" class="form-label fw-bold">{{ __('messages.phone') }}</label>
                        <input type="text" name="phone" id="supplier_phone" class="form-control" placeholder="{{ __('messages.enter_phone') }}">
                    </div>
                    <div class="mb-3">
                        <label for="supplier_address" class="form-label fw-bold">{{ __('messages.address') }}</label>
                        <textarea name="address" id="supplier_address" class="form-control" rows="2" placeholder="{{ __('messages.enter_address') }}"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="supplier_website" class="form-label fw-bold">{{ __('messages.website') }}</label>
                        <input type="url" name="website" id="supplier_website" class="form-control" placeholder="https://example.com">
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="supplier_tax_id" class="form-label fw-bold">{{ __('messages.tax_id') }}</label>
                            <input type="text" name="tax_id" id="supplier_tax_id" class="form-control" placeholder="{{ __('messages.tax_id') }}">
                        </div>
                        <div class="col-md-6">
                            <label for="supplier_registration" class="form-label fw-bold">{{ __('messages.registration_number') }}</label>
                            <input type="text" name="registration_number" id="supplier_registration" class="form-control" placeholder="{{ __('messages.registration_number') }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="supplier_notes" class="form-label fw-bold">{{ __('messages.notes') }}</label>
                        <textarea name="notes" id="supplier_notes" class="form-control" rows="2" placeholder="{{ __('messages.additional_notes') }}"></textarea>
                    </div>
                    <div class="form-check form-switch">
                        <input type="checkbox" name="is_active" class="form-check-input" id="create_active" checked>
                        <label class="form-check-label" for="create_active">{{ __('messages.active') }}</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> {{ __('messages.cancel') }}</button>
                    <button type="submit" class="btn btn-primary" id="createSubmitBtn"><i class="fas fa-save me-1"></i> {{ __('messages.save_supplier') }}</button>
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
                <h5 class="modal-title text-white"><i class="fas fa-eye me-2"></i> {{ __('messages.supplier_details') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewModalBody" style="padding: 20px 25px;">
                <div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">{{ __('messages.loading') }}...</span></div></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.close') }}</button></div>
        </div>
    </div>
</div>

<!-- EDIT MODAL -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title text-dark"><i class="fas fa-edit me-2"></i> {{ __('messages.edit_supplier') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body" id="editModalBody" style="padding: 20px 25px;">
                    <div id="editFormErrors" class="alert alert-danger d-none"></div>
                    <div class="text-center py-5"><div class="spinner-border text-warning" role="status"><span class="visually-hidden">{{ __('messages.loading') }}...</span></div></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> {{ __('messages.cancel') }}</button>
                    <button type="submit" class="btn btn-warning" id="editSubmitBtn"><i class="fas fa-save me-1"></i> {{ __('messages.update_supplier') }}</button>
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
                <h5 class="modal-title text-white"><i class="fas fa-exclamation-triangle me-2"></i> {{ __('messages.confirm_delete') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center" style="padding: 20px 25px;">
                <i class="fas fa-trash fa-4x text-danger mb-3 d-block"></i>
                <p class="fw-bold">{{ __('messages.are_you_sure_delete_supplier') }}</p>
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
    edit: @json(__('messages.edit')),
    view: @json(__('messages.view')),
    active: @json(__('messages.active')),
    inactive: @json(__('messages.inactive')),
    supplier: @json(__('messages.supplier')),
    supplier_name: @json(__('messages.supplier_name')),
    contact_person: @json(__('messages.contact_person')),
    email: @json(__('messages.email')),
    phone: @json(__('messages.phone')),
    address: @json(__('messages.address')),
    website: @json(__('messages.website')),
    tax_id: @json(__('messages.tax_id')),
    registration_number: @json(__('messages.registration_number')),
    notes: @json(__('messages.notes')),
    status: @json(__('messages.status')),
    statistics: @json(__('messages.statistics')),
    products: @json(__('messages.products')),
    purchase_orders: @json(__('messages.purchase_orders')),
    procurements: @json(__('messages.procurements')),
    created: @json(__('messages.created')),
    last_updated: @json(__('messages.last_updated')),
    // Alerts
    no_items_selected: @json(__('messages.no_items_selected')),
    select_at_least_one_supplier: @json(__('messages.select_at_least_one_supplier')),
    delete_selected_suppliers_q: @json(__('messages.delete_selected_suppliers_q')),
    delete_suppliers_warning: @json(__('messages.delete_suppliers_warning')),
    yes_delete_them: @json(__('messages.yes_delete_them')),
    deleting: @json(__('messages.deleting')),
    deleted: @json(__('messages.deleted')),
    added: @json(__('messages.added')),
    updated: @json(__('messages.updated')),
    error_title: @json(__('messages.error')),
    failed_to_delete_selected: @json(__('messages.failed_to_delete_selected')),
    failed_to_load_details: @json(__('messages.failed_to_load_details')),
    failed_to_load: @json(__('messages.failed_to_load')),
    failed_to_create: @json(__('messages.failed_to_create')),
    failed_to_update: @json(__('messages.failed_to_update')),
    failed_to_delete: @json(__('messages.failed_to_delete')),
    something_went_wrong: @json(__('messages.something_went_wrong')),
    save_supplier: @json(__('messages.save_supplier')),
    update_supplier: @json(__('messages.update_supplier')),
    saving: @json(__('messages.saving')),
    updating: @json(__('messages.updating')),
    toggle_status_q: @json(__('messages.toggle_status_q')),
    toggle_status_warning: @json(__('messages.toggle_status_warning')),
    yes_toggle_it: @json(__('messages.yes_toggle_it')),
    are_you_sure: @json(__('messages.are_you_sure')),
    wont_be_able_to_revert: @json(__('messages.wont_be_able_to_revert')),
    yes_delete: @json(__('messages.yes_delete')),
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
    @can('supplier.delete')
    $('#selectAll').on('change', function() { $('.row-checkbox').prop('checked', $(this).prop('checked')); });
    $('#bulkDeleteBtn').on('click', function() {
        var selected = $('.row-checkbox:checked');
        if (selected.length === 0) {
            Swal.fire({ icon: 'warning', title: trans.no_items_selected, text: trans.select_at_least_one_supplier });
            return;
        }
        Swal.fire({
            title: trans.delete_selected_suppliers_q,
            text: trans.delete_suppliers_warning.replace(':count', selected.length),
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
                    url: '{{ route("admin.suppliers.bulk-delete") }}',
                    type: 'POST', data: { supplier_ids: ids, _token: '{{ csrf_token() }}' }, dataType: 'json',
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
    @can('supplier.view')
    $(document).on('click', '.view-btn', function() {
        var id = $(this).data('id');
        var body = $('#viewModalBody');
        body.html(`<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">${trans.loading}...</span></div></div>`);
        $('#viewModal').modal('show');
        $.ajax({
            url: '/admin/suppliers/' + id, type: 'GET', dataType: 'json',
            success: function(response) {
                if (!response.success) {
                    body.html(`<div class="text-center py-5 text-danger"><i class="fas fa-exclamation-circle fa-4x mb-3 d-block"></i><h5>${trans.error_title}</h5><p>${response.message || trans.failed_to_load_details}</p></div>`);
                    return;
                }
                var supplier = response.supplier;
                var poStats = response.purchase_order_stats || {};
                var html = `
                    <div class="row g-3">
                        <div class="col-12"><label class="text-muted small fw-bold">${trans.supplier_name}</label><p class="fw-bold mb-0">${supplier.name}</p></div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">${trans.contact_person}</label><p class="mb-0">${supplier.contact_person || 'N/A'}</p></div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">${trans.email}</label><p class="mb-0">${supplier.email || 'N/A'}</p></div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">${trans.phone}</label><p class="mb-0">${supplier.phone || 'N/A'}</p></div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">${trans.status}</label>
                            <p class="mb-0"><span class="badge bg-${supplier.is_active ? 'success' : 'secondary'}">${supplier.is_active ? trans.active : trans.inactive}</span></p>
                        </div>
                        ${supplier.address ? `<div class="col-12"><label class="text-muted small fw-bold">${trans.address}</label><p class="mb-0">${supplier.address}</p></div>` : ''}
                        ${supplier.website ? `<div class="col-12"><label class="text-muted small fw-bold">${trans.website}</label><p class="mb-0"><a href="${supplier.website}" target="_blank">${supplier.website}</a></p></div>` : ''}
                        ${supplier.tax_id ? `<div class="col-md-6"><label class="text-muted small fw-bold">${trans.tax_id}</label><p class="mb-0">${supplier.tax_id}</p></div>` : ''}
                        ${supplier.registration_number ? `<div class="col-md-6"><label class="text-muted small fw-bold">${trans.registration_number}</label><p class="mb-0">${supplier.registration_number}</p></div>` : ''}
                        ${supplier.notes ? `<div class="col-12"><label class="text-muted small fw-bold">${trans.notes}</label><p class="mb-0">${supplier.notes}</p></div>` : ''}
                        <div class="col-12">
                            <hr>
                            <h6 class="fw-bold text-primary">${trans.statistics}</h6>
                            <div class="row g-2">
                                <div class="col-md-4 col-6">
                                    <div class="text-center p-2 bg-light rounded">
                                        <span class="d-block fw-bold">${supplier.products_count || 0}</span>
                                        <small class="text-muted">${trans.products}</small>
                                    </div>
                                </div>
                                <div class="col-md-4 col-6">
                                    <div class="text-center p-2 bg-info bg-opacity-10 rounded">
                                        <span class="d-block fw-bold text-info">${poStats.total || 0}</span>
                                        <small class="text-muted">${trans.purchase_orders}</small>
                                    </div>
                                </div>
                                <div class="col-md-4 col-6">
                                    <div class="text-center p-2 bg-success bg-opacity-10 rounded">
                                        <span class="d-block fw-bold text-success">${response.total_procurements || 0}</span>
                                        <small class="text-muted">${trans.procurements}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12"><label class="text-muted small fw-bold">${trans.created}</label><p class="mb-0">${new Date(supplier.created_at).toLocaleString()}</p></div>
                        ${supplier.updated_at ? `<div class="col-12"><label class="text-muted small fw-bold">${trans.last_updated}</label><p class="mb-0">${new Date(supplier.updated_at).toLocaleString()}</p></div>` : ''}
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
    @can('supplier.edit')
    $(document).on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        var body = $('#editModalBody');
        var form = $('#editForm');
        form.attr('action', '/admin/suppliers/' + id);
        body.html(`<div id="editFormErrors" class="alert alert-danger d-none"></div><div class="text-center py-5"><div class="spinner-border text-warning" role="status"><span class="visually-hidden">${trans.loading}...</span></div></div>`);
        $('#editModal').modal('show');
        $.ajax({
            url: '/admin/suppliers/' + id + '/edit', type: 'GET', dataType: 'json',
            success: function(response) {
                if (!response.success) {
                    body.html(`<div id="editFormErrors" class="alert alert-danger"></div><div class="text-center py-5 text-danger"><i class="fas fa-exclamation-circle fa-4x mb-3 d-block"></i><h5>${trans.error_title}</h5><p>${response.message || trans.failed_to_load}</p></div>`);
                    return;
                }
                var r = response.supplier;
                body.html(`
                    <div id="editFormErrors" class="alert alert-danger d-none"></div>
                    <div class="mb-3"><label for="edit_name" class="form-label fw-bold">${trans.supplier_name} <span class="text-danger">*</span></label><input type="text" name="name" id="edit_name" class="form-control" value="${r.name || ''}" required></div>
                    <div class="mb-3"><label for="edit_contact" class="form-label fw-bold">${trans.contact_person}</label><input type="text" name="contact_person" id="edit_contact" class="form-control" value="${r.contact_person || ''}"></div>
                    <div class="mb-3"><label for="edit_email" class="form-label fw-bold">${trans.email}</label><input type="email" name="email" id="edit_email" class="form-control" value="${r.email || ''}"></div>
                    <div class="mb-3"><label for="edit_phone" class="form-label fw-bold">${trans.phone}</label><input type="text" name="phone" id="edit_phone" class="form-control" value="${r.phone || ''}"></div>
                    <div class="mb-3"><label for="edit_address" class="form-label fw-bold">${trans.address}</label><textarea name="address" id="edit_address" class="form-control" rows="2">${r.address || ''}</textarea></div>
                    <div class="mb-3"><label for="edit_website" class="form-label fw-bold">${trans.website}</label><input type="url" name="website" id="edit_website" class="form-control" value="${r.website || ''}" placeholder="https://example.com"></div>
                    <div class="row g-3">
                        <div class="col-md-6"><label for="edit_tax_id" class="form-label fw-bold">${trans.tax_id}</label><input type="text" name="tax_id" id="edit_tax_id" class="form-control" value="${r.tax_id || ''}"></div>
                        <div class="col-md-6"><label for="edit_registration" class="form-label fw-bold">${trans.registration_number}</label><input type="text" name="registration_number" id="edit_registration" class="form-control" value="${r.registration_number || ''}"></div>
                    </div>
                    <div class="mb-3"><label for="edit_notes" class="form-label fw-bold">${trans.notes}</label><textarea name="notes" id="edit_notes" class="form-control" rows="2">${r.notes || ''}</textarea></div>
                    <div class="form-check form-switch">
                        <input type="checkbox" name="is_active" class="form-check-input" id="edit_active" ${r.is_active ? 'checked' : ''} value="1">
                        <label class="form-check-label" for="edit_active">${trans.active}</label>
                    </div>
                    <div class="mt-2"><small class="text-muted">${trans.created}: ${r.created_at ? new Date(r.created_at).toLocaleString() : 'N/A'}</small></div>
                `);
            },
            error: function(xhr) {
                body.html(`<div id="editFormErrors" class="alert alert-danger"></div><div class="text-center py-5 text-danger"><i class="fas fa-exclamation-circle fa-4x mb-3 d-block"></i><h5>${trans.failed_to_load}</h5><p>${xhr.status} - ${xhr.statusText}</p><p class="small">${xhr.responseJSON?.message || ''}</p></div>`);
            }
        });
    });
    @endcan

    // TOGGLE STATUS
    @can('supplier.edit')
    $(document).on('click', '.toggle-status-btn', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: trans.toggle_status_q,
            text: trans.toggle_status_warning,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: trans.yes_toggle_it,
            cancelButtonText: trans.cancel
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: trans.updating, allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                $.ajax({
                    url: '/admin/suppliers/' + id + '/toggle-status', type: 'POST',
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
    @can('supplier.create')
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
                    btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.save_supplier}`);
                    if (response.errors) showErrors(errorContainer, response.errors);
                    else Swal.fire({ icon: 'error', title: trans.error_title, text: response.message || trans.something_went_wrong });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.save_supplier}`);
                if (xhr.status === 422) showErrors(errorContainer, xhr.responseJSON.errors);
                else Swal.fire({ icon: 'error', title: trans.error_title, text: trans.failed_to_create });
            }
        });
    });
    @endcan

    // UPDATE
    @can('supplier.edit')
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
                    btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.update_supplier}`);
                    if (response.errors) showErrors(errorContainer, response.errors);
                    else Swal.fire({ icon: 'error', title: trans.error_title, text: response.message || trans.something_went_wrong });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.update_supplier}`);
                if (xhr.status === 422) showErrors(errorContainer, xhr.responseJSON.errors);
                else Swal.fire({ icon: 'error', title: trans.error_title, text: trans.failed_to_update });
            }
        });
    });
    @endcan

    // DELETE
    @can('supplier.delete')
    $(document).on('click', '.delete-btn', function() {
        var id = $(this).data('id');
        $('#deleteForm').attr('action', '/admin/suppliers/' + id);
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
    $('#hasProductsFilter, #statusFilter').on('change', filterTable);

    function filterTable() {
        var search = $('#searchInput').val().toLowerCase();
        var hasProducts = $('#hasProductsFilter').val();
        var status = $('#statusFilter').val();
        $('#tableBody tr').each(function() {
            var row = $(this);
            var show = true;
            if (search && !row.text().toLowerCase().includes(search)) show = false;
            if (hasProducts && row.data('has-products') !== hasProducts) show = false;
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