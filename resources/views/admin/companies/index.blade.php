@extends('layouts.admin')

@section('title', __('messages.companies'))
@section('page-title', __('messages.company_management'))

@section('admin-content')
<div class="row g-3 mb-4">
    <div class="col-xl-4 col-lg-6">
        <div class="small-box bg-primary">
            <div class="inner"><h3>{{ number_format($stats['total'] ?? 0) }}</h3><p>{{ __('messages.total_companies') }}</p></div>
            <div class="icon"><i class="fas fa-building"></i></div>
            <a href="{{ route('admin.companies.index') }}" class="small-box-footer">{{ __('messages.view_all') }} <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-xl-4 col-lg-6">
        <div class="small-box bg-success">
            <div class="inner"><h3>{{ number_format($stats['active'] ?? 0) }}</h3><p>{{ __('messages.active') }}</p></div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
            <a href="#" class="small-box-footer">{{ __('messages.active') }} <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-xl-4 col-lg-6">
        <div class="small-box bg-danger">
            <div class="inner"><h3>{{ number_format($stats['inactive'] ?? 0) }}</h3><p>{{ __('messages.inactive') }}</p></div>
            <div class="icon"><i class="fas fa-times-circle"></i></div>
            <a href="#" class="small-box-footer">{{ __('messages.inactive') }} <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <h5 class="card-title mb-0"><i class="fas fa-building text-primary me-2"></i>{{ __('messages.all_companies') }} <span class="badge bg-primary ms-1">{{ $companies->total() }}</span></h5>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <div class="input-group input-group-sm" style="width:200px;">
                <input type="text" id="searchInput" class="form-control" placeholder="{{ __('messages.search') }}...">
                <button class="btn btn-secondary" type="button" id="searchBtn"><i class="fas fa-search"></i></button>
            </div>
            <select id="statusFilter" class="form-select form-select-sm" style="width:120px;">
                <option value="">{{ __('messages.all_status') }}</option>
                <option value="1">{{ __('messages.active') }}</option>
                <option value="0">{{ __('messages.inactive') }}</option>
            </select>
            @can('company.create')
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fas fa-plus-circle me-1"></i> {{ __('messages.add_company') }}
            </button>
            @endcan
            @can('company.export')
            <a href="{{ route('admin.companies.export') }}" class="btn btn-success btn-sm"><i class="fas fa-file-excel me-1"></i> {{ __('messages.export') }}</a>
            @endcan
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:35px;">#</th>
                        <th style="min-width:180px;">{{ __('messages.company') }}</th>
                        <th>{{ __('messages.tax_id') }}</th>
                        <th>{{ __('messages.registration_number') }}</th>
                        <th>{{ __('messages.phone') }}</th>
                        <th>{{ __('messages.city') }}</th>
                        <th>{{ __('messages.status') }}</th>
                        <th style="width:200px;">{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($companies as $company)
                    <tr data-id="{{ $company->id }}" data-status="{{ $company->is_active ? '1' : '0' }}">
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                @if($company->logo)
                                    <img src="{{ Storage::url($company->logo) }}" alt="Logo" class="rounded me-2" style="width:40px;height:40px;object-fit:cover;">
                                @else
                                    <div class="avatar-circle bg-primary text-white me-2"><i class="fas fa-building"></i></div>
                                @endif
                                <div>
                                    <div class="fw-bold small">{{ Str::limit($company->name, 25) }}</div>
                                    <small class="text-muted">{{ $company->short_name ?? 'N/A' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>{{ $company->tax_id ?? 'N/A' }}</td>
                        <td>{{ $company->registration_number ?? 'N/A' }}</td>
                        <td>{{ $company->phone ?? 'N/A' }}</td>
                        <td>{{ $company->city ?? 'N/A' }}</td>
                        <td>
                            <span class="badge bg-{{ $company->is_active ? 'success' : 'danger' }}">
                                {{ $company->is_active ? __('messages.active') : __('messages.inactive') }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                @can('company.view')
                                <button class="btn btn-outline-primary btn-sm view-btn" data-id="{{ $company->id }}" title="{{ __('messages.view') }}"><i class="fas fa-eye"></i></button>
                                @endcan
                                @can('company.edit')
                                <button class="btn btn-outline-warning btn-sm edit-btn" data-id="{{ $company->id }}" title="{{ __('messages.edit') }}"><i class="fas fa-edit"></i></button>
                                @endcan
                                @can('company.edit')
                                <button class="btn btn-outline-{{ $company->is_active ? 'secondary' : 'success' }} btn-sm toggle-status" data-id="{{ $company->id }}" title="{{ $company->is_active ? __('messages.deactivate') : __('messages.activate') }}">
                                    <i class="fas {{ $company->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                </button>
                                @endcan
                                @can('company.delete')
                                <button class="btn btn-outline-danger btn-sm delete-btn" data-id="{{ $company->id }}" title="{{ __('messages.delete') }}"><i class="fas fa-trash"></i></button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8"><div class="text-center py-5"><i class="fas fa-building fa-4x text-muted mb-3 d-block"></i><h5 class="fw-bold">{{ __('messages.no_companies_found') }}</h5><p class="text-muted">{{ __('messages.click_add_company') }}</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div><span class="text-muted small">{{ __('messages.showing') }} {{ $companies->firstItem() ?? 0 }} {{ __('messages.to') }} {{ $companies->lastItem() ?? 0 }} {{ __('messages.of') }} {{ $companies->total() }} {{ __('messages.entries') }}</span></div>
        <div>{{ $companies->links() }}</div>
    </div>
</div>

<!-- CREATE MODAL -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width:700px;">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="fas fa-plus-circle me-2"></i> {{ __('messages.add_company') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="createForm" action="{{ route('admin.companies.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body" style="max-height:70vh; overflow-y:auto; padding:20px 25px;">
                    <div id="createFormErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.short_name') }}</label>
                            <input type="text" name="short_name" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.registration_number') }}</label>
                            <input type="text" name="registration_number" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.tax_id') }}</label>
                            <input type="text" name="tax_id" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.email') }}</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.phone') }}</label>
                            <input type="text" name="phone" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">{{ __('messages.address') }}</label>
                            <textarea name="address" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">{{ __('messages.city') }}</label>
                            <input type="text" name="city" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">{{ __('messages.state') }}</label>
                            <input type="text" name="state" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">{{ __('messages.country') }}</label>
                            <input type="text" name="country" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">{{ __('messages.postal_code') }}</label>
                            <input type="text" name="postal_code" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">{{ __('messages.currency') }}</label>
                            <select name="currency_id" class="form-select">
                                <option value="">{{ __('messages.select_currency') }}</option>
                                @foreach($currencies as $c)
                                    <option value="{{ $c->id }}">{{ $c->code }} ({{ $c->symbol }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">{{ __('messages.default_tax') }}</label>
                            <input type="number" name="default_tax" class="form-control" step="0.01" min="0" max="100" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.invoice_prefix') }}</label>
                            <input type="text" name="invoice_prefix" class="form-control" value="INV">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.quotation_prefix') }}</label>
                            <input type="text" name="quotation_prefix" class="form-control" value="QUO">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">{{ __('messages.logo') }}</label>
                            <input type="file" name="logo" class="form-control" accept="image/*">
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-2">
                                <input type="checkbox" name="is_active" class="form-check-input" id="createIsActive" value="1" checked>
                                <label class="form-check-label" for="createIsActive">{{ __('messages.active') }}</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> {{ __('messages.cancel') }}</button>
                    <button type="submit" class="btn btn-primary" id="createSubmitBtn"><i class="fas fa-save me-1"></i> {{ __('messages.save_company') }}</button>
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
                <h5 class="modal-title text-white"><i class="fas fa-eye me-2"></i> {{ __('messages.company_details') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewModalBody" style="max-height:70vh; overflow-y:auto; padding:20px 25px;">
                <div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.close') }}</button></div>
        </div>
    </div>
</div>

<!-- EDIT MODAL -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width:700px;">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title text-dark"><i class="fas fa-edit me-2"></i> {{ __('messages.edit_company') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="modal-body" id="editModalBody" style="max-height:70vh; overflow-y:auto; padding:20px 25px;">
                    <div id="editFormErrors" class="alert alert-danger d-none"></div>
                    <div class="text-center py-5"><div class="spinner-border text-warning" role="status"></div></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> {{ __('messages.cancel') }}</button>
                    <button type="submit" class="btn btn-warning" id="editSubmitBtn"><i class="fas fa-save me-1"></i> {{ __('messages.update_company') }}</button>
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
                <p class="fw-bold">{{ __('messages.are_you_sure_delete_company') }}</p>
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
.avatar-circle { width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:16px; font-weight:600; flex-shrink:0; }
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
</style>

@push('scripts')
<script>
window.trans = {
    loading: @json(__('messages.loading')), close: @json(__('messages.close')), cancel: @json(__('messages.cancel')),
    delete: @json(__('messages.delete')), edit: @json(__('messages.edit')), view: @json(__('messages.view')),
    name: @json(__('messages.name')), short_name: @json(__('messages.short_name')),
    registration_number: @json(__('messages.registration_number')), tax_id: @json(__('messages.tax_id')),
    email: @json(__('messages.email')), phone: @json(__('messages.phone')), address: @json(__('messages.address')),
    city: @json(__('messages.city')), state: @json(__('messages.state')), country: @json(__('messages.country')),
    postal_code: @json(__('messages.postal_code')), currency: @json(__('messages.currency')),
    default_tax: @json(__('messages.default_tax')), invoice_prefix: @json(__('messages.invoice_prefix')),
    quotation_prefix: @json(__('messages.quotation_prefix')), logo: @json(__('messages.logo')),
    active: @json(__('messages.active')), inactive: @json(__('messages.inactive')),
    status: @json(__('messages.status')), created: @json(__('messages.created')),
    created_alert: @json(__('messages.created_alert')), updated: @json(__('messages.updated')),
    deleted: @json(__('messages.deleted')),
    error_title: @json(__('messages.error')), something_went_wrong: @json(__('messages.something_went_wrong')),
    failed_to_load: @json(__('messages.failed_to_load')),
    failed_to_create: @json(__('messages.failed_to_create')),
    failed_to_update: @json(__('messages.failed_to_update')),
    failed_to_delete: @json(__('messages.failed_to_delete')),
    are_you_sure: @json(__('messages.are_you_sure')),
    wont_be_able_to_revert: @json(__('messages.wont_be_able_to_revert')),
    yes_delete: @json(__('messages.yes_delete')),
    saving: @json(__('messages.saving')), updating: @json(__('messages.updating')), deleting: @json(__('messages.deleting')),
    save_company: @json(__('messages.save_company')), update_company: @json(__('messages.update_company')),
    toggle_status_q: @json(__('messages.toggle_status_q')),
    toggle_status_warning_company: @json(__('messages.toggle_status_warning_company')),
    yes_toggle: @json(__('messages.yes_toggle')),
};
$(document).ready(function() {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
    function showErrors(c, e) { c.removeClass('d-none'); var h = '<ul class="mb-0">'; $.each(e, function(k,m){$.each(m,function(i,x){h+='<li>'+x+'</li>';});}); h+='</ul>'; c.html(h); c[0].scrollIntoView({behavior:'smooth',block:'center'}); }
    function clearErrors(c){ c.addClass('d-none').empty(); }

    @can('company.view')
    $(document).on('click', '.view-btn', function() {
        var id = $(this).data('id');
        var body = $('#viewModalBody');
        body.html(`<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>`);
        $('#viewModal').modal('show');
        $.get('/admin/companies/' + id, function(c) {
            var html = `
                <div class="row g-3">
                    ${c.logo ? `<div class="col-12 text-center"><img src="{{ Storage::url('') }}${c.logo}" style="max-height:100px;max-width:200px;" class="mb-2"></div>` : ''}
                    <div class="col-md-6"><label class="text-muted small fw-bold">${trans.name}</label><p class="fw-bold mb-0">${c.name}</p></div>
                    <div class="col-md-6"><label class="text-muted small fw-bold">${trans.short_name}</label><p class="mb-0">${c.short_name || 'N/A'}</p></div>
                    <div class="col-md-6"><label class="text-muted small fw-bold">${trans.registration_number}</label><p class="mb-0">${c.registration_number || 'N/A'}</p></div>
                    <div class="col-md-6"><label class="text-muted small fw-bold">${trans.tax_id}</label><p class="mb-0">${c.tax_id || 'N/A'}</p></div>
                    <div class="col-md-6"><label class="text-muted small fw-bold">${trans.email}</label><p class="mb-0">${c.email || 'N/A'}</p></div>
                    <div class="col-md-6"><label class="text-muted small fw-bold">${trans.phone}</label><p class="mb-0">${c.phone || 'N/A'}</p></div>
                    <div class="col-12"><label class="text-muted small fw-bold">${trans.address}</label><p class="mb-0">${c.address || 'N/A'}</p></div>
                    <div class="col-md-4"><label class="text-muted small fw-bold">${trans.city}</label><p class="mb-0">${c.city || 'N/A'}</p></div>
                    <div class="col-md-4"><label class="text-muted small fw-bold">${trans.state}</label><p class="mb-0">${c.state || 'N/A'}</p></div>
                    <div class="col-md-4"><label class="text-muted small fw-bold">${trans.country}</label><p class="mb-0">${c.country || 'N/A'}</p></div>
                    <div class="col-md-6"><label class="text-muted small fw-bold">${trans.currency}</label><p class="mb-0">${c.currency ? c.currency.code : 'N/A'}</p></div>
                    <div class="col-md-6"><label class="text-muted small fw-bold">${trans.default_tax}</label><p class="mb-0">${c.default_tax || 0}%</p></div>
                    <div class="col-md-6"><label class="text-muted small fw-bold">${trans.status}</label><p class="mb-0"><span class="badge bg-${c.is_active ? 'success' : 'danger'}">${c.is_active ? trans.active : trans.inactive}</span></p></div>
                    <div class="col-md-6"><label class="text-muted small fw-bold">${trans.created}</label><p class="mb-0">${new Date(c.created_at).toLocaleString()}</p></div>
                </div>`;
            body.html(html);
        });
    });
    @endcan

    @can('company.edit')
    $(document).on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        var body = $('#editModalBody');
        var form = $('#editForm');
        form.attr('action', '/admin/companies/' + id);
        body.html('<div id="editFormErrors" class="alert alert-danger d-none"></div><div class="text-center py-5"><div class="spinner-border text-warning" role="status"></div></div>');
        $('#editModal').modal('show');
        $.get('/admin/companies/' + id + '/edit', function(r) {
            var c = r.company;
            var currencyOptions = '';
            r.currencies.forEach(function(cc) { currencyOptions += `<option value="${cc.id}" ${c.currency_id == cc.id ? 'selected' : ''}>${cc.code} (${cc.symbol})</option>`; });
            body.html(`
                <div id="editFormErrors" class="alert alert-danger d-none"></div>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label fw-bold">${trans.name} <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" value="${c.name}" required></div>
                    <div class="col-md-6"><label class="form-label fw-bold">${trans.short_name}</label><input type="text" name="short_name" class="form-control" value="${c.short_name || ''}"></div>
                    <div class="col-md-6"><label class="form-label fw-bold">${trans.registration_number}</label><input type="text" name="registration_number" class="form-control" value="${c.registration_number || ''}"></div>
                    <div class="col-md-6"><label class="form-label fw-bold">${trans.tax_id}</label><input type="text" name="tax_id" class="form-control" value="${c.tax_id || ''}"></div>
                    <div class="col-md-6"><label class="form-label fw-bold">${trans.email}</label><input type="email" name="email" class="form-control" value="${c.email || ''}"></div>
                    <div class="col-md-6"><label class="form-label fw-bold">${trans.phone}</label><input type="text" name="phone" class="form-control" value="${c.phone || ''}"></div>
                    <div class="col-12"><label class="form-label fw-bold">${trans.address}</label><textarea name="address" class="form-control" rows="2">${c.address || ''}</textarea></div>
                    <div class="col-md-4"><label class="form-label fw-bold">${trans.city}</label><input type="text" name="city" class="form-control" value="${c.city || ''}"></div>
                    <div class="col-md-4"><label class="form-label fw-bold">${trans.state}</label><input type="text" name="state" class="form-control" value="${c.state || ''}"></div>
                    <div class="col-md-4"><label class="form-label fw-bold">${trans.country}</label><input type="text" name="country" class="form-control" value="${c.country || ''}"></div>
                    <div class="col-md-4"><label class="form-label fw-bold">${trans.postal_code}</label><input type="text" name="postal_code" class="form-control" value="${c.postal_code || ''}"></div>
                    <div class="col-md-4"><label class="form-label fw-bold">${trans.currency}</label><select name="currency_id" class="form-select">${currencyOptions}</select></div>
                    <div class="col-md-4"><label class="form-label fw-bold">${trans.default_tax}</label><input type="number" name="default_tax" class="form-control" step="0.01" min="0" max="100" value="${c.default_tax || 0}"></div>
                    <div class="col-md-6"><label class="form-label fw-bold">${trans.invoice_prefix}</label><input type="text" name="invoice_prefix" class="form-control" value="${c.invoice_prefix || 'INV'}"></div>
                    <div class="col-md-6"><label class="form-label fw-bold">${trans.quotation_prefix}</label><input type="text" name="quotation_prefix" class="form-control" value="${c.quotation_prefix || 'QUO'}"></div>
                    <div class="col-md-12"><label class="form-label fw-bold">${trans.logo}</label><input type="file" name="logo" class="form-control" accept="image/*"></div>
                    <div class="col-md-6"><div class="form-check form-switch mt-2"><input type="checkbox" name="is_active" class="form-check-input" id="editIsActive" value="1" ${c.is_active ? 'checked' : ''}><label class="form-check-label" for="editIsActive">${trans.active}</label></div></div>
                </div>`);
        });
    });
    @endcan

    @can('company.create')
    $('#createForm').on('submit', function(e) {
        e.preventDefault();
        var form = this; var data = new FormData(form);
        var btn = $('#createSubmitBtn'); var errorContainer = $('#createFormErrors');
        clearErrors(errorContainer);
        btn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm me-1"></span> ${trans.saving}...`);
        $.ajax({
            url: $(form).attr('action'), type: 'POST', data: data, processData: false, contentType: false, dataType: 'json',
            success: function(r) {
                if (r.success) { $('#createModal').modal('hide'); Swal.fire({ icon: 'success', title: trans.created_alert, text: r.message, timer: 2000 }).then(() => location.reload()); }
                else { btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.save_company}`); if (r.errors) showErrors(errorContainer, r.errors); else Swal.fire({ icon: 'error', title: trans.error_title, text: r.message || trans.something_went_wrong }); }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.save_company}`);
                if (xhr.status === 422) showErrors(errorContainer, xhr.responseJSON.errors);
                else Swal.fire({ icon: 'error', title: trans.error_title, text: trans.failed_to_create });
            }
        });
    });
    @endcan

    @can('company.edit')
    $('#editForm').on('submit', function(e) {
        e.preventDefault();
        var form = this; var data = new FormData(form);
        var btn = $('#editSubmitBtn'); var errorContainer = $('#editFormErrors');
        clearErrors(errorContainer);
        btn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm me-1"></span> ${trans.updating}...`);
        $.ajax({
            url: $(form).attr('action'), type: 'POST', data: data, processData: false, contentType: false, dataType: 'json',
            success: function(r) {
                if (r.success) { $('#editModal').modal('hide'); Swal.fire({ icon: 'success', title: trans.updated, text: r.message, timer: 2000 }).then(() => location.reload()); }
                else { btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.update_company}`); if (r.errors) showErrors(errorContainer, r.errors); else Swal.fire({ icon: 'error', title: trans.error_title, text: r.message || trans.something_went_wrong }); }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.update_company}`);
                if (xhr.status === 422) showErrors(errorContainer, xhr.responseJSON.errors);
                else Swal.fire({ icon: 'error', title: trans.error_title, text: trans.failed_to_update });
            }
        });
    });
    @endcan

    @can('company.edit')
    $(document).on('click', '.toggle-status', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: trans.toggle_status_q, text: trans.toggle_status_warning_company, icon: 'question',
            showCancelButton: true, confirmButtonColor: '#0d6efd', cancelButtonColor: '#6c757d',
            confirmButtonText: trans.yes_toggle, cancelButtonText: trans.cancel
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('/admin/companies/' + id + '/toggle-status', { _token: '{{ csrf_token() }}' }, function(r) {
                    if (r.success) Swal.fire({ icon: 'success', title: trans.updated, text: r.message, timer: 1500 }).then(() => location.reload());
                });
            }
        });
    });
    @endcan

    @can('company.delete')
    $(document).on('click', '.delete-btn', function() {
        var id = $(this).data('id');
        $('#deleteForm').attr('action', '/admin/companies/' + id);
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
            url: $(this).attr('action'), type: 'POST', data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
            success: function(r) {
                if (r.success) { $('#deleteModal').modal('hide'); Swal.fire({ icon: 'success', title: trans.deleted, text: r.message, timer: 2000 }).then(() => location.reload()); }
                else { btn.prop('disabled', false).html(`<i class="fas fa-trash me-1"></i> ${trans.delete}`); Swal.fire({ icon: 'error', title: trans.error_title, text: r.message || trans.failed_to_delete }); }
            },
            error: function(xhr) { btn.prop('disabled', false).html(`<i class="fas fa-trash me-1"></i> ${trans.delete}`); Swal.fire({ icon: 'error', title: trans.error_title, text: xhr.responseJSON?.message || trans.failed_to_delete }); }
        });
    });
    @endcan

    $('#searchBtn').on('click', filterTable);
    $('#searchInput').on('keypress', function(e) { if (e.which === 13) filterTable(); });
    $('#statusFilter').on('change', filterTable);
    function filterTable() {
        var search = $('#searchInput').val().toLowerCase(); var status = $('#statusFilter').val();
        $('#tableBody tr').each(function() {
            var row = $(this); var show = true;
            if (search && !row.text().toLowerCase().includes(search)) show = false;
            if (status !== '' && row.data('status') != status) show = false;
            row.toggle(show);
        });
    }

    $('.modal').on('hidden.bs.modal', function() {
        $('.modal-backdrop').remove(); $('body').removeClass('modal-open');
        $(this).find('.alert-danger').addClass('d-none').empty();
        $(this).find('form')[0]?.reset();
    });
});
</script>
@endpush
@endsection