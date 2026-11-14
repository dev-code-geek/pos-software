@extends('layouts.admin')

@section('title', __('messages.branches'))
@section('page-title', __('messages.branch_management'))

@section('admin-content')
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-lg-6">
        <div class="small-box bg-primary">
            <div class="inner"><h3>{{ number_format($stats['total'] ?? 0) }}</h3><p>{{ __('messages.total_branches') }}</p></div>
            <div class="icon"><i class="fas fa-store-alt"></i></div>
            <a href="{{ route('admin.branches.index') }}" class="small-box-footer">{{ __('messages.view_all') }} <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6">
        <div class="small-box bg-success">
            <div class="inner"><h3>{{ number_format($stats['active'] ?? 0) }}</h3><p>{{ __('messages.active') }}</p></div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
            <a href="#" class="small-box-footer">{{ __('messages.active') }} <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6">
        <div class="small-box bg-danger">
            <div class="inner"><h3>{{ number_format($stats['inactive'] ?? 0) }}</h3><p>{{ __('messages.inactive') }}</p></div>
            <div class="icon"><i class="fas fa-times-circle"></i></div>
            <a href="#" class="small-box-footer">{{ __('messages.inactive') }} <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6">
        <div class="small-box bg-info">
            <div class="inner"><h3>{{ number_format($stats['with_employees'] ?? 0) }}</h3><p>{{ __('messages.with_employees') }}</p></div>
            <div class="icon"><i class="fas fa-users"></i></div>
            <a href="#" class="small-box-footer">{{ __('messages.employees') }} <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <h5 class="card-title mb-0"><i class="fas fa-store-alt text-primary me-2"></i>{{ __('messages.all_branches') }} <span class="badge bg-primary ms-1">{{ $branches->total() }}</span></h5>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <div class="input-group input-group-sm" style="width:200px;">
                <input type="text" id="searchInput" class="form-control" placeholder="{{ __('messages.search') }}...">
                <button class="btn btn-secondary" type="button" id="searchBtn"><i class="fas fa-search"></i></button>
            </div>
            <select id="companyFilter" class="form-select form-select-sm" style="width:150px;">
                <option value="">{{ __('messages.all_companies') }}</option>
                @foreach($companies as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </select>
            <select id="statusFilter" class="form-select form-select-sm" style="width:120px;">
                <option value="">{{ __('messages.all_status') }}</option>
                <option value="1">{{ __('messages.active') }}</option>
                <option value="0">{{ __('messages.inactive') }}</option>
            </select>
            @can('branch.create')
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fas fa-plus-circle me-1"></i> {{ __('messages.add_branch') }}
            </button>
            @endcan
            @can('branch.export')
            <a href="{{ route('admin.branches.export') }}" class="btn btn-success btn-sm"><i class="fas fa-file-excel me-1"></i> {{ __('messages.export') }}</a>
            @endcan
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:35px;">#</th>
                        <th style="min-width:160px;">{{ __('messages.name') }}</th>
                        <th>{{ __('messages.code') }}</th>
                        <th>{{ __('messages.company') }}</th>
                        <th>{{ __('messages.city') }}</th>
                        <th>{{ __('messages.phone') }}</th>
                        <th>{{ __('messages.status') }}</th>
                        <th style="width:200px;">{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($branches as $branch)
                    <tr data-id="{{ $branch->id }}" data-status="{{ $branch->is_active ? '1' : '0' }}" data-company="{{ $branch->company_id }}">
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle bg-primary text-white me-2"><i class="fas fa-store"></i></div>
                                <div>
                                    <div class="fw-bold small">{{ Str::limit($branch->name, 25) }}</div>
                                    <small class="text-muted">ID: #{{ $branch->id }}</small>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge bg-secondary">{{ $branch->code }}</span></td>
                        <td>{{ $branch->company->name ?? 'N/A' }}</td>
                        <td>{{ $branch->city ?? 'N/A' }}</td>
                        <td>{{ $branch->phone ?? 'N/A' }}</td>
                        <td>
                            <span class="badge bg-{{ $branch->is_active ? 'success' : 'danger' }}">
                                {{ $branch->is_active ? __('messages.active') : __('messages.inactive') }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                @can('branch.view')
                                <button class="btn btn-outline-primary btn-sm view-btn" data-id="{{ $branch->id }}" title="{{ __('messages.view') }}"><i class="fas fa-eye"></i></button>
                                @endcan
                                @can('branch.edit')
                                <button class="btn btn-outline-warning btn-sm edit-btn" data-id="{{ $branch->id }}" title="{{ __('messages.edit') }}"><i class="fas fa-edit"></i></button>
                                @endcan
                                @can('branch.edit')
                                <button class="btn btn-outline-{{ $branch->is_active ? 'secondary' : 'success' }} btn-sm toggle-status" data-id="{{ $branch->id }}" title="{{ $branch->is_active ? __('messages.deactivate') : __('messages.activate') }}">
                                    <i class="fas {{ $branch->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                </button>
                                @endcan
                                @can('branch.delete')
                                <button class="btn btn-outline-danger btn-sm delete-btn" data-id="{{ $branch->id }}" title="{{ __('messages.delete') }}"><i class="fas fa-trash"></i></button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8"><div class="text-center py-5"><i class="fas fa-store-alt fa-4x text-muted mb-3 d-block"></i><h5 class="fw-bold">{{ __('messages.no_branches_found') }}</h5></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div><span class="text-muted small">{{ __('messages.showing') }} {{ $branches->firstItem() ?? 0 }} {{ __('messages.to') }} {{ $branches->lastItem() ?? 0 }} {{ __('messages.of') }} {{ $branches->total() }} {{ __('messages.entries') }}</span></div>
        <div>{{ $branches->links() }}</div>
    </div>
</div>

<!-- CREATE MODAL -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:600px;">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="fas fa-plus-circle me-2"></i> {{ __('messages.add_branch') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="createForm" action="{{ route('admin.branches.store') }}" method="POST">
                @csrf
                <div class="modal-body" style="padding:20px 25px;">
                    <div id="createFormErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.company') }} <span class="text-danger">*</span></label>
                            <select name="company_id" class="form-select" required>
                                <option value="">{{ __('messages.select_company') }}</option>
                                @foreach($companies as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.code') }} <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.city') }}</label>
                            <input type="text" name="city" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.phone') }}</label>
                            <input type="text" name="phone" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.email') }}</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">{{ __('messages.address') }}</label>
                            <textarea name="address" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.warehouse') }}</label>
                            <select name="warehouse_id" class="form-select">
                                <option value="">{{ __('messages.select_warehouse') }}</option>
                                @foreach($warehouses ?? [] as $w)
                                    <option value="{{ $w->id }}">{{ $w->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.manager') }}</label>
                            <select name="manager_id" class="form-select">
                                <option value="">{{ __('messages.select_manager') }}</option>
                                @foreach($managers ?? [] as $m)
                                    <option value="{{ $m->id }}">{{ $m->name }}</option>
                                @endforeach
                            </select>
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
                    <button type="submit" class="btn btn-primary" id="createSubmitBtn"><i class="fas fa-save me-1"></i> {{ __('messages.save_branch') }}</button>
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
                <h5 class="modal-title text-white"><i class="fas fa-eye me-2"></i> {{ __('messages.branch_details') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewModalBody" style="padding:20px 25px;">
                <div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.close') }}</button></div>
        </div>
    </div>
</div>

<!-- EDIT MODAL -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:600px;">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title text-dark"><i class="fas fa-edit me-2"></i> {{ __('messages.edit_branch') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-body" id="editModalBody" style="padding:20px 25px;">
                    <div id="editFormErrors" class="alert alert-danger d-none"></div>
                    <div class="text-center py-5"><div class="spinner-border text-warning" role="status"></div></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> {{ __('messages.cancel') }}</button>
                    <button type="submit" class="btn btn-warning" id="editSubmitBtn"><i class="fas fa-save me-1"></i> {{ __('messages.update_branch') }}</button>
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
                <p class="fw-bold">{{ __('messages.are_you_sure_delete_branch') }}</p>
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
    name: @json(__('messages.name')), code: @json(__('messages.code')), company: @json(__('messages.company')),
    city: @json(__('messages.city')), phone: @json(__('messages.phone')), email: @json(__('messages.email')),
    address: @json(__('messages.address')), warehouse: @json(__('messages.warehouse')), manager: @json(__('messages.manager')),
    active: @json(__('messages.active')), inactive: @json(__('messages.inactive')),
    status: @json(__('messages.status')), created: @json(__('messages.created')),
    created_alert: @json(__('messages.created_alert')), updated: @json(__('messages.updated')),
    deleted: @json(__('messages.deleted')),
    error_title: @json(__('messages.error')), something_went_wrong: @json(__('messages.something_went_wrong')),
    failed_to_create: @json(__('messages.failed_to_create')),
    failed_to_update: @json(__('messages.failed_to_update')),
    failed_to_delete: @json(__('messages.failed_to_delete')),
    are_you_sure: @json(__('messages.are_you_sure')),
    wont_be_able_to_revert: @json(__('messages.wont_be_able_to_revert')),
    yes_delete: @json(__('messages.yes_delete')),
    saving: @json(__('messages.saving')), updating: @json(__('messages.updating')), deleting: @json(__('messages.deleting')),
    save_branch: @json(__('messages.save_branch')), update_branch: @json(__('messages.update_branch')),
};
$(document).ready(function() {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
    function showErrors(c,e){c.removeClass('d-none');var h='<ul class="mb-0">';$.each(e,function(k,m){$.each(m,function(i,x){h+='<li>'+x+'</li>';});});h+='</ul>';c.html(h);c[0].scrollIntoView({behavior:'smooth',block:'center'});}
    function clearErrors(c){c.addClass('d-none').empty();}

    @can('branch.view')
    $(document).on('click','.view-btn',function(){
        var id=$(this).data('id'); var body=$('#viewModalBody');
        body.html(`<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>`);
        $('#viewModal').modal('show');
        $.get('/admin/branches/'+id,function(b){
            body.html(`
                <div class="row g-3">
                    <div class="col-6"><label class="text-muted small fw-bold">${trans.name}</label><p class="fw-bold mb-0">${b.name}</p></div>
                    <div class="col-6"><label class="text-muted small fw-bold">${trans.code}</label><p class="mb-0"><span class="badge bg-secondary">${b.code}</span></p></div>
                    <div class="col-6"><label class="text-muted small fw-bold">${trans.company}</label><p class="mb-0">${b.company ? b.company.name : 'N/A'}</p></div>
                    <div class="col-6"><label class="text-muted small fw-bold">${trans.city}</label><p class="mb-0">${b.city || 'N/A'}</p></div>
                    <div class="col-6"><label class="text-muted small fw-bold">${trans.phone}</label><p class="mb-0">${b.phone || 'N/A'}</p></div>
                    <div class="col-6"><label class="text-muted small fw-bold">${trans.email}</label><p class="mb-0">${b.email || 'N/A'}</p></div>
                    <div class="col-12"><label class="text-muted small fw-bold">${trans.address}</label><p class="mb-0">${b.address || 'N/A'}</p></div>
                    <div class="col-6"><label class="text-muted small fw-bold">${trans.warehouse}</label><p class="mb-0">${b.warehouse ? b.warehouse.name : 'N/A'}</p></div>
                    <div class="col-6"><label class="text-muted small fw-bold">${trans.manager}</label><p class="mb-0">${b.manager ? b.manager.name : 'N/A'}</p></div>
                    <div class="col-6"><label class="text-muted small fw-bold">${trans.status}</label><p class="mb-0"><span class="badge bg-${b.is_active ? 'success' : 'danger'}">${b.is_active ? trans.active : trans.inactive}</span></p></div>
                    <div class="col-6"><label class="text-muted small fw-bold">${trans.created}</label><p class="mb-0">${new Date(b.created_at).toLocaleString()}</p></div>
                </div>`);
        });
    });
    @endcan

    @can('branch.edit')
    $(document).on('click','.edit-btn',function(){
        var id=$(this).data('id'); var body=$('#editModalBody'); var form=$('#editForm');
        form.attr('action','/admin/branches/'+id);
        body.html('<div id="editFormErrors" class="alert alert-danger d-none"></div><div class="text-center py-5"><div class="spinner-border text-warning" role="status"></div></div>');
        $('#editModal').modal('show');
        $.get('/admin/branches/'+id+'/edit',function(r){
            var b=r.branch;
            var companyOptions=''; r.companies.forEach(function(c){ companyOptions += `<option value="${c.id}" ${b.company_id==c.id?'selected':''}>${c.name}</option>`; });
            var warehouseOptions=`<option value="">${trans.warehouse}</option>`; r.warehouses.forEach(function(w){ warehouseOptions += `<option value="${w.id}" ${b.warehouse_id==w.id?'selected':''}>${w.name}</option>`; });
            var managerOptions=`<option value="">${trans.manager}</option>`; r.managers.forEach(function(m){ managerOptions += `<option value="${m.id}" ${b.manager_id==m.id?'selected':''}>${m.name}</option>`; });
            body.html(`
                <div id="editFormErrors" class="alert alert-danger d-none"></div>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label fw-bold">${trans.company} <span class="text-danger">*</span></label><select name="company_id" class="form-select" required>${companyOptions}</select></div>
                    <div class="col-md-6"><label class="form-label fw-bold">${trans.name} <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" value="${b.name}" required></div>
                    <div class="col-md-6"><label class="form-label fw-bold">${trans.code} <span class="text-danger">*</span></label><input type="text" name="code" class="form-control" value="${b.code}" required></div>
                    <div class="col-md-6"><label class="form-label fw-bold">${trans.city}</label><input type="text" name="city" class="form-control" value="${b.city || ''}"></div>
                    <div class="col-md-6"><label class="form-label fw-bold">${trans.phone}</label><input type="text" name="phone" class="form-control" value="${b.phone || ''}"></div>
                    <div class="col-md-6"><label class="form-label fw-bold">${trans.email}</label><input type="email" name="email" class="form-control" value="${b.email || ''}"></div>
                    <div class="col-12"><label class="form-label fw-bold">${trans.address}</label><textarea name="address" class="form-control" rows="2">${b.address || ''}</textarea></div>
                    <div class="col-md-6"><label class="form-label fw-bold">${trans.warehouse}</label><select name="warehouse_id" class="form-select">${warehouseOptions}</select></div>
                    <div class="col-md-6"><label class="form-label fw-bold">${trans.manager}</label><select name="manager_id" class="form-select">${managerOptions}</select></div>
                    <div class="col-md-6"><div class="form-check form-switch mt-2"><input type="checkbox" name="is_active" class="form-check-input" id="editIsActive" value="1" ${b.is_active ? 'checked' : ''}><label class="form-check-label" for="editIsActive">${trans.active}</label></div></div>
                </div>`);
        });
    });
    @endcan

    @can('branch.create')
    $('#createForm').on('submit',function(e){
        e.preventDefault();
        var btn=$('#createSubmitBtn'); var err=$('#createFormErrors');
        clearErrors(err); btn.prop('disabled',true).html(`<span class="spinner-border spinner-border-sm me-1"></span> ${trans.saving}...`);
        $.ajax({url:$(this).attr('action'),type:'POST',data:$(this).serialize(),dataType:'json',
            success:function(r){
                if(r.success){$('#createModal').modal('hide');Swal.fire({icon:'success',title:trans.created_alert,text:r.message,timer:2000}).then(()=>location.reload());}
                else{btn.prop('disabled',false).html(`<i class="fas fa-save me-1"></i> ${trans.save_branch}`);if(r.errors)showErrors(err,r.errors);else Swal.fire({icon:'error',title:trans.error_title,text:r.message||trans.something_went_wrong});}
            },
            error:function(xhr){btn.prop('disabled',false).html(`<i class="fas fa-save me-1"></i> ${trans.save_branch}`);if(xhr.status===422)showErrors(err,xhr.responseJSON.errors);else Swal.fire({icon:'error',title:trans.error_title,text:trans.failed_to_create});}
        });
    });
    @endcan

    @can('branch.edit')
    $('#editForm').on('submit',function(e){
        e.preventDefault();
        var btn=$('#editSubmitBtn'); var err=$('#editFormErrors');
        clearErrors(err); btn.prop('disabled',true).html(`<span class="spinner-border spinner-border-sm me-1"></span> ${trans.updating}...`);
        $.ajax({url:$(this).attr('action'),type:'POST',data:$(this).serialize(),dataType:'json',
            success:function(r){
                if(r.success){$('#editModal').modal('hide');Swal.fire({icon:'success',title:trans.updated,text:r.message,timer:2000}).then(()=>location.reload());}
                else{btn.prop('disabled',false).html(`<i class="fas fa-save me-1"></i> ${trans.update_branch}`);if(r.errors)showErrors(err,r.errors);else Swal.fire({icon:'error',title:trans.error_title,text:r.message||trans.something_went_wrong});}
            },
            error:function(xhr){btn.prop('disabled',false).html(`<i class="fas fa-save me-1"></i> ${trans.update_branch}`);if(xhr.status===422)showErrors(err,xhr.responseJSON.errors);else Swal.fire({icon:'error',title:trans.error_title,text:trans.failed_to_update});}
        });
    });
    @endcan

    @can('branch.edit')
    $(document).on('click','.toggle-status',function(){
        var id=$(this).data('id');
        $.post('/admin/branches/'+id+'/toggle-status',{_token:'{{ csrf_token() }}'},function(r){
            if(r.success) Swal.fire({icon:'success',title:trans.updated,text:r.message,timer:1500}).then(()=>location.reload());
        });
    });
    @endcan

    @can('branch.delete')
    $(document).on('click','.delete-btn',function(){
        var id=$(this).data('id');
        $('#deleteForm').attr('action','/admin/branches/'+id);
        Swal.fire({title:trans.are_you_sure,text:trans.wont_be_able_to_revert,icon:'warning',showCancelButton:true,confirmButtonColor:'#d33',cancelButtonColor:'#6c757d',confirmButtonText:trans.yes_delete,cancelButtonText:trans.cancel}).then((result)=>{if(result.isConfirmed){$('#deleteModal').modal('show');}});
    });
    $('#deleteForm').on('submit',function(e){
        e.preventDefault();
        var btn=$('#deleteSubmitBtn');
        btn.prop('disabled',true).html(`<span class="spinner-border spinner-border-sm me-1"></span> ${trans.deleting}...`);
        $.ajax({url:$(this).attr('action'),type:'POST',data:{_token:'{{ csrf_token() }}',_method:'DELETE'},
            success:function(r){
                if(r.success){$('#deleteModal').modal('hide');Swal.fire({icon:'success',title:trans.deleted,text:r.message,timer:2000}).then(()=>location.reload());}
                else{btn.prop('disabled',false).html(`<i class="fas fa-trash me-1"></i> ${trans.delete}`);Swal.fire({icon:'error',title:trans.error_title,text:r.message||trans.failed_to_delete});}
            },
            error:function(xhr){btn.prop('disabled',false).html(`<i class="fas fa-trash me-1"></i> ${trans.delete}`);Swal.fire({icon:'error',title:trans.error_title,text:xhr.responseJSON?.message||trans.failed_to_delete});}
        });
    });
    @endcan

    $('#searchBtn').on('click',filterTable);
    $('#searchInput').on('keypress',function(e){if(e.which===13)filterTable();});
    $('#companyFilter, #statusFilter').on('change',filterTable);
    function filterTable(){
        var search=$('#searchInput').val().toLowerCase();
        var company=$('#companyFilter').val(); var status=$('#statusFilter').val();
        $('#tableBody tr').each(function(){
            var row=$(this); var show=true;
            if(search && !row.text().toLowerCase().includes(search)) show=false;
            if(company && row.data('company')!=company) show=false;
            if(status!=='' && row.data('status')!=status) show=false;
            row.toggle(show);
        });
    }

    $('.modal').on('hidden.bs.modal',function(){
        $('.modal-backdrop').remove(); $('body').removeClass('modal-open');
        $(this).find('.alert-danger').addClass('d-none').empty();
        $(this).find('form')[0]?.reset();
    });
});
</script>
@endpush
@endsection