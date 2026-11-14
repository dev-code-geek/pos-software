@extends('layouts.admin')

@section('title', __('messages.categories'))
@section('page-title', __('messages.category_management'))

@section('admin-content')
<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ $stats['total'] ?? $categories->total() ?? 0 }}</h3>
                <p>{{ __('messages.total_categories') }}</p>
            </div>
            <div class="icon">
                <i class="fas fa-tags"></i>
            </div>
            <a href="{{ route('admin.categories.index') }}" class="small-box-footer">
                {{ __('messages.view_all') }} <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $stats['with_products'] ?? 0 }}</h3>
                <p>{{ __('messages.with_products') }}</p>
            </div>
            <div class="icon">
                <i class="fas fa-box"></i>
            </div>
            <a href="#" class="small-box-footer">
                <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $stats['without_products'] ?? 0 }}</h3>
                <p>{{ __('messages.empty_categories') }}</p>
            </div>
            <div class="icon">
                <i class="fas fa-box-open"></i>
            </div>
            <a href="#" class="small-box-footer">
                <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $categories->sum('products_count') }}</h3>
                <p>{{ __('messages.total_products') }}</p>
            </div>
            <div class="icon">
                <i class="fas fa-boxes"></i>
            </div>
            <a href="{{ route('admin.products.index') }}" class="small-box-footer">
                {{ __('messages.view_products') }} <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Main Card -->
<div class="card">
    <div class="card-header">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <h5 class="card-title mb-0">
                <i class="fas fa-tags text-primary me-2"></i>
                {{ __('messages.all_categories') }}
                <span class="badge bg-primary ms-1">{{ $categories->total() }}</span>
            </h5>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <div class="input-group input-group-sm" style="width: 200px;">
                <input type="text" id="searchInput" class="form-control" placeholder="{{ __('messages.search') }}...">
                <button class="btn btn-secondary" type="button" id="searchBtn">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            @can('category.create')
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fas fa-plus-circle me-1"></i> {{ __('messages.add_category') }}
            </button>
            @endcan
            @can('category.view')
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
                        <th style="min-width: 160px;">{{ __('messages.name') }}</th>
                        <th>{{ __('messages.description') }}</th>
                        <th style="width: 80px; text-align: center;">{{ __('messages.products') }}</th>
                        <th style="width: 100px;">{{ __('messages.created') }}</th>
                        <th style="width: 100px;">{{ __('messages.status') }}</th>
                        <th style="width: 130px;">{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($categories as $category)
                    <tr data-id="{{ $category->id }}">
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle bg-primary text-white me-2">
                                    <i class="fas fa-tag"></i>
                                </div>
                                <div>
                                    <div class="fw-bold small">{{ Str::limit($category->name, 25) }}</div>
                                    <small class="text-muted">ID: #{{ $category->id }}</small>
                                </div>
                            </div>
                        </td>
                        <td>{{ Str::limit($category->description, 50) ?? 'N/A' }}</td>
                        <td class="text-center">
                            <span class="badge bg-{{ $category->products_count > 0 ? 'success' : 'secondary' }}">
                                {{ $category->products_count ?? 0 }}
                            </span>
                        </td>
                        <td>
                            <small>{{ $category->created_at->format('M d, Y') }}</small>
                            <br>
                            <small class="text-muted">{{ $category->created_at->diffForHumans() }}</small>
                        </td>
                        <td>
                            @php
                                $statusColor = $category->is_active ? 'success' : 'danger';
                                $statusText = $category->is_active ? __('messages.active') : __('messages.inactive');
                            @endphp
                            <span class="badge bg-{{ $statusColor }}">{{ $statusText }}</span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                @can('category.view')
                                <button class="btn btn-outline-primary btn-sm view-btn" data-id="{{ $category->id }}" title="{{ __('messages.view') }}">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @endcan
                                @can('category.edit')
                                <button class="btn btn-outline-warning btn-sm edit-btn" data-id="{{ $category->id }}" title="{{ __('messages.edit') }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @endcan
                                @can('category.edit')
                                <button class="btn btn-outline-{{ $category->is_active ? 'secondary' : 'success' }} btn-sm toggle-status-btn" 
                                        data-id="{{ $category->id }}" 
                                        title="{{ $category->is_active ? __('messages.deactivate') : __('messages.activate') }}">
                                    <i class="fas {{ $category->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                </button>
                                @endcan
                                @can('category.delete')
                                <button class="btn btn-outline-danger btn-sm delete-btn" data-id="{{ $category->id }}" title="{{ __('messages.delete') }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7">
                            <div class="text-center py-5">
                                <i class="fas fa-tags fa-4x text-muted mb-3 d-block"></i>
                                <h5 class="fw-bold">{{ __('messages.no_categories_found') }}</h5>
                                <p class="text-muted">{{ __('messages.click_add_category') }}</p>
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
                {{ __('messages.showing') }} {{ $categories->firstItem() ?? 0 }} 
                {{ __('messages.to') }} {{ $categories->lastItem() ?? 0 }} 
                {{ __('messages.of') }} {{ $categories->total() }} 
                {{ __('messages.entries') }}
            </span>
        </div>
        <div>
            {{ $categories->links() }}
        </div>
    </div>
</div>

<!-- CREATE MODAL -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white">
                    <i class="fas fa-plus-circle me-2"></i> {{ __('messages.add_category') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="createForm" action="{{ route('admin.categories.store') }}" method="POST">
                @csrf
                <div class="modal-body" style="padding: 20px 25px;">
                    <div id="createFormErrors" class="alert alert-danger d-none"></div>
                    <div class="mb-3">
                        <label for="category_name" class="form-label fw-bold">{{ __('messages.category_name') }} <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="category_name" class="form-control" required placeholder="{{ __('messages.enter_category_name') }}">
                    </div>
                    <div class="mb-3">
                        <label for="category_description" class="form-label fw-bold">{{ __('messages.description') }}</label>
                        <textarea name="description" id="category_description" class="form-control" rows="3" placeholder="{{ __('messages.enter_description') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> {{ __('messages.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary" id="createSubmitBtn">
                        <i class="fas fa-save me-1"></i> {{ __('messages.save') }}
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
                    <i class="fas fa-eye me-2"></i> {{ __('messages.category_details') }}
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
                    <i class="fas fa-edit me-2"></i> {{ __('messages.edit_category') }}
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
                        <i class="fas fa-save me-1"></i> {{ __('messages.update') }}
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
                <p class="fw-bold">{{ __('messages.are_you_sure_delete_category') }}</p>
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
.avatar-circle {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 600;
    flex-shrink: 0;
}
.small-box {
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 0;
    position: relative;
    overflow: hidden;
    transition: transform 0.15s ease;
}
.small-box:hover {
    transform: translateY(-2px);
}
.small-box .inner { color: #fff; }
.small-box .inner h3 { font-size: 2rem; font-weight: 700; margin: 0 0 5px; }
.small-box .inner p { font-size: 0.9rem; opacity: 0.9; margin: 0; }
.small-box .icon {
    position: absolute;
    right: 15px;
    top: 15px;
    font-size: 2.5rem;
    opacity: 0.25;
    color: #fff;
}
.small-box .small-box-footer {
    display: block;
    padding: 8px 0 0;
    color: rgba(255,255,255,0.8);
    text-decoration: none;
    font-size: 0.85rem;
    transition: all 0.15s ease;
}
.small-box .small-box-footer:hover { color: #fff; }
.small-box .small-box-footer i { transition: transform 0.15s ease; }
.small-box:hover .small-box-footer i { transform: translateX(4px); }
</style>
@push('scripts')
<script>
// ====================== TRANSLATIONS ======================
window.trans = {
    loading:                    @json(__('messages.loading')),
    close:                      @json(__('messages.close')),
    cancel:                     @json(__('messages.cancel')),
    save:                       @json(__('messages.save')),
    update:                     @json(__('messages.update')),
    delete:                     @json(__('messages.delete')),
    edit:                       @json(__('messages.edit')),
    view:                       @json(__('messages.view')),
    active:                     @json(__('messages.active')),
    inactive:                   @json(__('messages.inactive')),
    name:                       @json(__('messages.name')),
    description:                @json(__('messages.description')),
    status:                     @json(__('messages.status')),
    confirm_delete:             @json(__('messages.confirm_delete')),
    are_you_sure:               @json(__('messages.are_you_sure')),
    this_action_cannot_be_undone: @json(__('messages.this_action_cannot_be_undone')),
    category_name:              @json(__('messages.category_name')),
    category_details:           @json(__('messages.category_details')),
    edit_category:              @json(__('messages.edit_category')),
    add_category:               @json(__('messages.add_category')),
    no_description:             @json(__('messages.no_description') ?? 'No description'),
    product_statistics:         @json(__('messages.product_statistics') ?? 'Product Statistics'),
    total_products:             @json(__('messages.total_products')),
    recent_products:            @json(__('messages.recent_products') ?? 'Recent Products'),
    deactivate:                 @json(__('messages.deactivate')),
    activate:                   @json(__('messages.activate')),
    toggle_status:              @json(__('messages.toggle_status') ?? 'Toggle Status'),
    are_you_sure_toggle:        @json(__('messages.are_you_sure_toggle') ?? 'Are you sure you want to change the status?'),
    slug:                       @json(__('messages.slug') ?? 'Slug'),
    order:                      @json(__('messages.order') ?? 'Order'),
    created:                    @json(__('messages.created')),
    hardware:                   @json(__('messages.hardware') ?? 'Hardware'),
    software:                   @json(__('messages.software') ?? 'Software'),
    no_items_selected:          @json(__('messages.no_items_selected') ?? 'No items selected'),
    export_failed:              @json(__('messages.export_failed') ?? 'Export Failed'),
};

$(document).ready(function() {

    // CSRF
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Helper: Show validation errors
    function showErrors(container, errors) {
        container.removeClass('d-none');
        let html = '<ul class="mb-0">';
        $.each(errors, function(key, messages) {
            $.each(messages, function(i, msg) {
                html += `<li>${msg}</li>`;
            });
        });
        html += '</ul>';
        container.html(html);
        container[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function clearErrors(container) {
        container.addClass('d-none').empty();
    }

    // ====================== EXPORT ======================
    @can('category.view')
    $('#exportBtn').on('click', function() {
        const btn = $(this);
        btn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm me-1"></span> ${trans.loading}...`);

        $.ajax({
            url: '{{ route("admin.categories.export") }}',
            type: 'GET',
            xhrFields: { responseType: 'blob' },
            success: function(data, status, xhr) {
                const contentType = xhr.getResponseHeader('Content-Type');
                if (contentType && contentType.includes('application/json')) {
                    const reader = new FileReader();
                    reader.onload = function() {
                        try {
                            const json = JSON.parse(reader.result);
                            Swal.fire({ icon: 'error', title: trans.export_failed, text: json.message || 'Failed' });
                        } catch (e) {
                            Swal.fire({ icon: 'error', title: trans.export_failed, text: 'Unexpected error' });
                        }
                        btn.prop('disabled', false).html(`<i class="fas fa-file-excel me-1"></i> ${trans.export || 'Export'}`);
                    };
                    reader.readAsText(data);
                    return;
                }

                let filename = 'categories_export.csv';
                const disposition = xhr.getResponseHeader('Content-Disposition');
                if (disposition && disposition.indexOf('filename=') !== -1) {
                    filename = disposition.split('filename=')[1].replace(/['"]/g, '');
                }

                const url = window.URL.createObjectURL(data);
                const a = document.createElement('a');
                a.href = url;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                a.remove();
                window.URL.revokeObjectURL(url);

                btn.prop('disabled', false).html(`<i class="fas fa-file-excel me-1"></i> ${trans.export || 'Export'}`);
            },
            error: function() {
                btn.prop('disabled', false).html(`<i class="fas fa-file-excel me-1"></i> ${trans.export || 'Export'}`);
                Swal.fire({ icon: 'error', title: trans.export_failed, text: 'Please try again.' });
            }
        });
    });
    @endcan

    // ====================== VIEW MODAL ======================
    @can('category.view')
    $(document).on('click', '.view-btn', function() {
        const id = $(this).data('id');
        const body = $('#viewModalBody');

        body.html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">${trans.loading}...</span>
                </div>
            </div>
        `);
        $('#viewModal').modal('show');

        $.ajax({
            url: '/admin/categories/' + id,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (!response.success) {
                    body.html(`
                        <div class="text-center py-5 text-danger">
                            <i class="fas fa-exclamation-circle fa-4x mb-3 d-block"></i>
                            <h5>Error</h5>
                            <p>${response.message || 'Failed to load'}</p>
                        </div>
                    `);
                    return;
                }

                const category = response.category;
                const productStats = response.product_stats || { hardware: 0, software: 0, total: 0 };

                const html = `
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="text-muted small fw-bold">${trans.category_name}</label>
                            <p class="fw-bold mb-0">${category?.name || 'N/A'}</p>
                        </div>
                        <div class="col-12">
                            <label class="text-muted small fw-bold">${trans.slug}</label>
                            <p class="mb-0">${category?.slug || 'N/A'}</p>
                        </div>
                        <div class="col-12">
                            <label class="text-muted small fw-bold">${trans.description}</label>
                            <p class="mb-0">${category?.description || trans.no_description}</p>
                        </div>
                        <div class="col-6">
                            <label class="text-muted small fw-bold">${trans.status}</label>
                            <p class="mb-0">
                                <span class="badge bg-${category?.is_active ? 'success' : 'danger'}">
                                    ${category?.is_active ? trans.active : trans.inactive}
                                </span>
                            </p>
                        </div>
                        <div class="col-6">
                            <label class="text-muted small fw-bold">${trans.order}</label>
                            <p class="mb-0">${category?.order || '0'}</p>
                        </div>
                        <div class="col-12"><hr><h6 class="fw-bold text-primary">${trans.product_statistics}</h6></div>
                        <div class="col-md-4">
                            <label class="text-muted small fw-bold">${trans.total_products}</label>
                            <p class="mb-0"><span class="badge bg-primary">${productStats.total || 0}</span></p>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small fw-bold">${trans.hardware}</label>
                            <p class="mb-0"><span class="badge bg-info">${productStats.hardware || 0}</span></p>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small fw-bold">${trans.software}</label>
                            <p class="mb-0"><span class="badge bg-success">${productStats.software || 0}</span></p>
                        </div>
                        <div class="col-12">
                            <label class="text-muted small fw-bold">${trans.created}</label>
                            <p class="mb-0">${category ? new Date(category.created_at).toLocaleString() : 'N/A'}</p>
                        </div>
                    </div>
                `;
                body.html(html);
            },
            error: function(xhr) {
                body.html(`
                    <div class="text-center py-5 text-danger">
                        <i class="fas fa-exclamation-circle fa-4x mb-3 d-block"></i>
                        <h5>Failed to load</h5>
                        <p>${xhr.status} - ${xhr.statusText}</p>
                    </div>
                `);
            }
        });
    });
    @endcan

    // ====================== EDIT MODAL ======================
    @can('category.edit')
    $(document).on('click', '.edit-btn', function() {
        const id = $(this).data('id');
        const body = $('#editModalBody');
        const form = $('#editForm');

        form.attr('action', '/admin/categories/' + id);
        body.html(`
            <div id="editFormErrors" class="alert alert-danger d-none"></div>
            <div class="text-center py-5">
                <div class="spinner-border text-warning" role="status">
                    <span class="visually-hidden">${trans.loading}...</span>
                </div>
            </div>
        `);
        $('#editModal').modal('show');

        $.ajax({
            url: '/admin/categories/' + id + '/edit',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (!response.success) {
                    body.html(`<div class="text-center py-5 text-danger"><h5>Error</h5><p>${response.message}</p></div>`);
                    return;
                }

                const category = response.category;
                body.html(`
                    <div id="editFormErrors" class="alert alert-danger d-none"></div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">${trans.category_name} <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="${category.name || ''}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">${trans.description}</label>
                        <textarea name="description" class="form-control" rows="3">${category.description || ''}</textarea>
                    </div>
                    <div class="mb-3 form-check form-switch">
                        <input type="checkbox" class="form-check-input" name="is_active" value="1" ${category.is_active ? 'checked' : ''}>
                        <label class="form-check-label">${trans.active}</label>
                    </div>
                    <div class="mt-2">
                        <small class="text-muted">${trans.slug}: ${category.slug || 'N/A'}</small><br>
                        <small class="text-muted">${trans.created}: ${category.created_at ? new Date(category.created_at).toLocaleString() : 'N/A'}</small>
                    </div>
                `);
            },
            error: function(xhr) {
                body.html(`<div class="text-center py-5 text-danger"><h5>Failed to load</h5><p>${xhr.status}</p></div>`);
            }
        });
    });
    @endcan

    // ====================== TOGGLE STATUS ======================
    @can('category.edit')
    $(document).on('click', '.toggle-status-btn', function() {
        const id = $(this).data('id');
        const title = $(this).attr('title');

        Swal.fire({
            title: trans.toggle_status + '?',
            text: trans.are_you_sure_toggle,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0d6efd',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes',
            cancelButtonText: trans.cancel
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: trans.loading + '...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                $.ajax({
                    url: '/admin/categories/' + id + '/toggle-status',
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({ icon: 'success', title: 'Updated!', text: response.message, timer: 1500 })
                                .then(() => location.reload());
                        } else {
                            Swal.fire({ icon: 'error', title: 'Error', text: response.message });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Failed' });
                    }
                });
            }
        });
    });
    @endcan

    // ====================== CREATE ======================
    @can('category.create')
    $('#createForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const btn = $('#createSubmitBtn');
        const errorContainer = $('#createFormErrors');

        clearErrors(errorContainer);
        btn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm me-1"></span> ${trans.loading}...`);

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#createModal').modal('hide');
                    Swal.fire({ icon: 'success', title: 'Added!', text: response.message, timer: 2000 })
                        .then(() => location.reload());
                } else {
                    btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.save}`);
                    if (response.errors) showErrors(errorContainer, response.errors);
                    else Swal.fire({ icon: 'error', title: 'Error', text: response.message });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.save}`);
                if (xhr.status === 422) showErrors(errorContainer, xhr.responseJSON.errors);
                else Swal.fire({ icon: 'error', title: 'Error', text: 'Something went wrong' });
            }
        });
    });
    @endcan

    // ====================== UPDATE ======================
    @can('category.edit')
    $('#editForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const btn = $('#editSubmitBtn');
        const errorContainer = $('#editFormErrors');

        clearErrors(errorContainer);
        btn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm me-1"></span> ${trans.loading}...`);

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#editModal').modal('hide');
                    Swal.fire({ icon: 'success', title: 'Updated!', text: response.message, timer: 2000 })
                        .then(() => location.reload());
                } else {
                    btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.update}`);
                    if (response.errors) showErrors(errorContainer, response.errors);
                    else Swal.fire({ icon: 'error', title: 'Error', text: response.message });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.update}`);
                if (xhr.status === 422) showErrors(errorContainer, xhr.responseJSON.errors);
                else Swal.fire({ icon: 'error', title: 'Error', text: 'Something went wrong' });
            }
        });
    });
    @endcan

    // ====================== DELETE ======================
    @can('category.delete')
    $(document).on('click', '.delete-btn', function() {
        const id = $(this).data('id');
        $('#deleteForm').attr('action', '/admin/categories/' + id);

        Swal.fire({
            title: trans.confirm_delete,
            text: trans.are_you_sure,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: trans.cancel
        }).then((result) => {
            if (result.isConfirmed) {
                $('#deleteModal').modal('show');
            }
        });
    });

    $('#deleteForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const btn = $('#deleteSubmitBtn');

        btn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm me-1"></span> ${trans.loading}...`);

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#deleteModal').modal('hide');
                    Swal.fire({ icon: 'success', title: 'Deleted!', text: response.message, timer: 2000 })
                        .then(() => location.reload());
                } else {
                    btn.prop('disabled', false).html(`<i class="fas fa-trash me-1"></i> ${trans.delete}`);
                    Swal.fire({ icon: 'error', title: 'Error', text: response.message });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(`<i class="fas fa-trash me-1"></i> ${trans.delete}`);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Failed to delete'
                });
            }
        });
    });
    @endcan

    // ====================== SEARCH ======================
    $('#searchBtn').on('click', filterTable);
    $('#searchInput').on('keypress', function(e) {
        if (e.which === 13) filterTable();
    });

    function filterTable() {
        const search = $('#searchInput').val().toLowerCase();
        $('#tableBody tr').each(function() {
            $(this).toggle($(this).text().toLowerCase().includes(search));
        });
    }

    // Fix modal backdrop
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