@extends('layouts.admin')

@section('title', 'Currencies')
@section('page-title', 'Currency Management')

@section('admin-content')
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="small-box bg-primary">
            <div class="inner"><h3>{{ number_format($stats['total'] ?? 0) }}</h3><p>Total</p></div>
            <div class="icon"><i class="fas fa-coins"></i></div>
            <a href="{{ route('admin.currencies.index') }}" class="small-box-footer">View All <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="small-box bg-success">
            <div class="inner"><h3>{{ number_format($stats['active'] ?? 0) }}</h3><p>Active</p></div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
            <a href="{{ route('admin.currencies.index', ['status' => 'active']) }}" class="small-box-footer">View Active <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="small-box bg-warning text-dark">
            <div class="inner"><h3>{{ number_format($stats['base'] ?? 0) }}</h3><p>Base</p></div>
            <div class="icon"><i class="fas fa-star"></i></div>
            <a href="#" class="small-box-footer">View Base <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="small-box bg-secondary">
            <div class="inner"><h3>{{ number_format($stats['inactive'] ?? 0) }}</h3><p>Inactive</p></div>
            <div class="icon"><i class="fas fa-pause-circle"></i></div>
            <a href="{{ route('admin.currencies.index', ['status' => 'inactive']) }}" class="small-box-footer">View Inactive <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <h5 class="card-title mb-0"><i class="fas fa-coins text-primary me-2"></i>All Currencies <span class="badge bg-primary ms-1">{{ $currencies->total() }}</span></h5>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <input type="text" id="searchInput" class="form-control form-control-sm" style="width:200px;" placeholder="Search...">
            <select id="statusFilter" class="form-select form-select-sm" style="width:120px;">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
            @can('currency.create')
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fas fa-plus-circle me-1"></i> Add Currency
            </button>
            @endcan
            @can('currency.export')
            <button class="btn btn-success btn-sm" id="exportBtn"><i class="fas fa-file-excel me-1"></i> Export</button>
            @endcan
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:35px;">#</th>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Symbol</th>
                        <th>Exchange Rate</th>
                        <th>Base</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($currencies as $currency)
                    <tr data-id="{{ $currency->id }}" data-status="{{ $currency->is_active ? 'active' : 'inactive' }}">
                        <td>{{ $loop->iteration }}</td>
                        <td><strong class="text-primary">{{ $currency->code }}</strong></td>
                        <td>{{ $currency->name }}</td>
                        <td><span class="badge bg-secondary">{{ $currency->symbol }}</span></td>
                        <td>{{ number_format($currency->exchange_rate, 4) }}</td>
                        <td>
                            @if($currency->is_base)
                                <span class="badge bg-success"><i class="fas fa-star me-1"></i> Base</span>
                            @else
                                <span class="badge bg-secondary">No</span>
                            @endif
                        </td>
                        <td>
                            @if($currency->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                @can('currency.view')
                                <button class="btn btn-outline-primary btn-sm view-btn" data-id="{{ $currency->id }}" title="View"><i class="fas fa-eye"></i></button>
                                @endcan
                                @can('currency.edit')
                                <button class="btn btn-outline-warning btn-sm edit-btn" data-id="{{ $currency->id }}" title="Edit"><i class="fas fa-edit"></i></button>
                                @endcan
                                @can('currency.toggle_status')
                                <button class="btn btn-outline-{{ $currency->is_active ? 'secondary' : 'success' }} toggle-status" data-id="{{ $currency->id }}" data-active="{{ $currency->is_active ? '1' : '0' }}" title="{{ $currency->is_active ? 'Deactivate' : 'Activate' }}">
                                    <i class="fas {{ $currency->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                                </button>
                                @endcan
                                @can('currency.set_base')
                                @if(!$currency->is_base)
                                <button class="btn btn-outline-info set-base" data-id="{{ $currency->id }}" title="Set as Base"><i class="fas fa-star"></i></button>
                                @endif
                                @endcan
                                @can('currency.delete')
                                <button class="btn btn-outline-danger btn-sm delete-btn" data-id="{{ $currency->id }}" title="Delete"><i class="fas fa-trash"></i></button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8"><div class="text-center py-5"><i class="fas fa-coins fa-4x text-muted mb-3 d-block"></i><h5 class="fw-bold">No Currencies Found</h5></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div><span class="text-muted small">Showing {{ $currencies->firstItem() ?? 0 }} to {{ $currencies->lastItem() ?? 0 }} of {{ $currencies->total() }} entries</span></div>
        <div>{{ $currencies->links() }}</div>
    </div>
</div>

<!-- ============================================ -->
<!-- CREATE MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:600px;">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="fas fa-plus-circle me-2"></i> Add Currency</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="createForm" action="{{ route('admin.currencies.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div id="createFormErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control" placeholder="e.g., USD" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Symbol <span class="text-danger">*</span></label>
                            <input type="text" name="symbol" class="form-control" placeholder="e.g., $" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g., US Dollar" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Exchange Rate <span class="text-danger">*</span></label>
                            <input type="number" name="exchange_rate" class="form-control" step="0.000001" min="0" value="1.000000" required>
                            <small class="text-muted">Rate against base currency</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Decimal Places</label>
                            <input type="number" name="decimal_places" class="form-control" min="0" max="4" value="2">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_base" id="create_is_base" value="1">
                                <label class="form-check-label" for="create_is_base">Set as Base Currency</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="create_is_active" value="1" checked>
                                <label class="form-check-label" for="create_is_active">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Cancel</button>
                    <button type="submit" class="btn btn-primary" id="createSubmitBtn"><i class="fas fa-save me-1"></i> Save Currency</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- VIEW MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:600px;">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title text-white"><i class="fas fa-eye me-2"></i> Currency Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewModalBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- EDIT MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:600px;">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title text-dark"><i class="fas fa-edit me-2"></i> Edit Currency</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body" id="editModalBody">
                    <div id="editFormErrors" class="alert alert-danger d-none"></div>
                    <div class="text-center py-5">
                        <div class="spinner-border text-warning" role="status"><span class="visually-hidden">Loading...</span></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Cancel</button>
                    <button type="submit" class="btn btn-warning" id="editSubmitBtn"><i class="fas fa-save me-1"></i> Update Currency</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- DELETE CONFIRM MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:560px;">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white"><i class="fas fa-exclamation-triangle me-2"></i> Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-trash fa-4x text-danger mb-3 d-block"></i>
                <p class="fw-bold">Are you sure you want to delete this currency?</p>
                <p class="text-muted small">This action cannot be undone.</p>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Cancel</button>
                <button type="submit" class="btn btn-danger" id="deleteSubmitBtn" form="deleteForm"><i class="fas fa-trash me-1"></i> Delete</button>
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
</style>

@push('scripts')
<script>
$(document).ready(function() {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    function showErrors(container, errors) {
        container.removeClass('d-none');
        var html = '<ul class="mb-0">';
        $.each(errors, function(key, messages) {
            $.each(messages, function(index, msg) { html += '<li>' + msg + '</li>'; });
        });
        html += '</ul>';
        container.html(html);
        container[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    function clearErrors(container) { container.addClass('d-none').empty(); }

    // CREATE
    @can('currency.create')
    $('#createForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var data = form.serialize();
        var btn = $('#createSubmitBtn');
        var errorContainer = $('#createFormErrors');
        clearErrors(errorContainer);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Creating...');

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(r) {
                if (r.success) {
                    $('#createModal').modal('hide');
                    Swal.fire({ icon: 'success', title: 'Added!', text: r.message, timer: 2000, showConfirmButton: true }).then(() => { location.reload(); });
                } else {
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save Currency');
                    if (r.errors) showErrors(errorContainer, r.errors);
                    else Swal.fire({ icon: 'error', title: 'Error!', text: r.message || 'Something went wrong.' });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save Currency');
                if (xhr.status === 422) showErrors(errorContainer, xhr.responseJSON.errors);
                else Swal.fire({ icon: 'error', title: 'Error!', text: 'Failed to create currency.' });
            }
        });
    });
    @endcan

    // VIEW
    @can('currency.view')
    $(document).on('click', '.view-btn', function() {
        var id = $(this).data('id');
        var body = $('#viewModalBody');
        body.html(`<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>`);
        $('#viewModal').modal('show');

        $.ajax({
            url: '/admin/currencies/' + id,
            type: 'GET',
            dataType: 'json',
            success: function(r) {
                body.html(`
                    <div class="row g-3">
                        <div class="col-md-6"><label class="text-muted small fw-bold">Code</label><p class="fw-bold mb-0">${r.code}</p></div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">Symbol</label><p class="fw-bold mb-0">${r.symbol}</p></div>
                        <div class="col-12"><label class="text-muted small fw-bold">Name</label><p class="fw-bold mb-0">${r.name}</p></div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">Exchange Rate</label><p class="mb-0">${parseFloat(r.exchange_rate).toFixed(6)}</p></div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">Decimal Places</label><p class="mb-0">${r.decimal_places}</p></div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">Base Currency</label><p class="mb-0">${r.is_base ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>'}</p></div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">Status</label><p class="mb-0">${r.is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'}</p></div>
                        <div class="col-12"><label class="text-muted small fw-bold">Created At</label><p class="mb-0">${new Date(r.created_at).toLocaleString()}</p></div>
                    </div>
                `);
            },
            error: function(xhr) {
                body.html(`<div class="text-center py-5 text-danger"><i class="fas fa-exclamation-circle fa-4x mb-3 d-block"></i><h5>Failed to load</h5></div>`);
            }
        });
    });
    @endcan

    // EDIT
    @can('currency.edit')
    $(document).on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        var body = $('#editModalBody');
        var form = $('#editForm');

        form.attr('action', '/admin/currencies/' + id);
        body.html(`<div id="editFormErrors" class="alert alert-danger d-none"></div><div class="text-center py-5"><div class="spinner-border text-warning" role="status"><span class="visually-hidden">Loading...</span></div></div>`);
        $('#editModal').modal('show');

        $.ajax({
            url: '/admin/currencies/' + id + '/edit',
            type: 'GET',
            dataType: 'json',
            success: function(r) {
                body.html(`
                    <div id="editFormErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label fw-bold">Code <span class="text-danger">*</span></label><input type="text" name="code" class="form-control" value="${r.code}" required></div>
                        <div class="col-md-6"><label class="form-label fw-bold">Symbol <span class="text-danger">*</span></label><input type="text" name="symbol" class="form-control" value="${r.symbol}" required></div>
                        <div class="col-12"><label class="form-label fw-bold">Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" value="${r.name}" required></div>
                        <div class="col-md-6"><label class="form-label fw-bold">Exchange Rate <span class="text-danger">*</span></label><input type="number" name="exchange_rate" class="form-control" step="0.000001" min="0" value="${r.exchange_rate}" required></div>
                        <div class="col-md-6"><label class="form-label fw-bold">Decimal Places</label><input type="number" name="decimal_places" class="form-control" min="0" max="4" value="${r.decimal_places}"></div>
                        <div class="col-12">
                            <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_base" id="edit_is_base" value="1" ${r.is_base ? 'checked' : ''}><label class="form-check-label" for="edit_is_base">Base Currency</label></div>
                            <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_active" id="edit_is_active" value="1" ${r.is_active ? 'checked' : ''}><label class="form-check-label" for="edit_is_active">Active</label></div>
                        </div>
                    </div>
                `);
            },
            error: function(xhr) {
                body.html(`<div id="editFormErrors" class="alert alert-danger"></div><div class="text-center py-5 text-danger"><i class="fas fa-exclamation-circle fa-4x mb-3 d-block"></i><h5>Failed to load</h5></div>`);
            }
        });
    });
    @endcan

    // UPDATE
    @can('currency.edit')
    $('#editForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var data = form.serialize();
        var btn = $('#editSubmitBtn');
        var errorContainer = $('#editFormErrors');
        clearErrors(errorContainer);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Updating...');

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(r) {
                if (r.success) {
                    $('#editModal').modal('hide');
                    Swal.fire({ icon: 'success', title: 'Updated!', text: r.message, timer: 2000, showConfirmButton: true }).then(() => { location.reload(); });
                } else {
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Update Currency');
                    if (r.errors) showErrors(errorContainer, r.errors);
                    else Swal.fire({ icon: 'error', title: 'Error!', text: r.message || 'Something went wrong.' });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Update Currency');
                if (xhr.status === 422) showErrors(errorContainer, xhr.responseJSON.errors);
                else Swal.fire({ icon: 'error', title: 'Error!', text: 'Failed to update currency.' });
            }
        });
    });
    @endcan

    // TOGGLE STATUS
    @can('currency.toggle_status')
    $(document).on('click', '.toggle-status', function() {
        var id = $(this).data('id');
        var active = $(this).data('active');
        Swal.fire({
            title: 'Confirm Status Change',
            text: active == 1 ? 'Deactivate this currency?' : 'Activate this currency?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: active == 1 ? '#d33' : '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: active == 1 ? 'Yes, deactivate' : 'Yes, activate'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/admin/currencies/' + id + '/toggle-status',
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(r) {
                        if (r.success) {
                            Swal.fire({ icon: 'success', title: 'Success', text: r.message, timer: 1500 }).then(() => { location.reload(); });
                        }
                    },
                    error: function() { Swal.fire('Error', 'Failed to toggle status.', 'error'); }
                });
            }
        });
    });
    @endcan

    // SET BASE
    @can('currency.set_base')
    $(document).on('click', '.set-base', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Set as Base Currency?',
            text: "This will set this currency as the base currency for all transactions.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#007bff',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, set as base'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/admin/currencies/' + id + '/set-base',
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(r) {
                        if (r.success) {
                            Swal.fire({ icon: 'success', title: 'Success', text: r.message, timer: 1500 }).then(() => { location.reload(); });
                        }
                    },
                    error: function() { Swal.fire('Error', 'Failed to set base currency.', 'error'); }
                });
            }
        });
    });
    @endcan

    // DELETE
    @can('currency.delete')
    $(document).on('click', '.delete-btn', function() {
        var id = $(this).data('id');
        var form = $('#deleteForm');
        form.attr('action', '/admin/currencies/' + id);
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) { $('#deleteModal').modal('show'); }
        });
    });
    $('#deleteForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = $('#deleteSubmitBtn');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Deleting...');
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
            success: function(r) {
                if (r.success) {
                    $('#deleteModal').modal('hide');
                    Swal.fire({ icon: 'success', title: 'Deleted!', text: r.message, timer: 1500, showConfirmButton: true }).then(() => { location.reload(); });
                } else {
                    btn.prop('disabled', false).html('<i class="fas fa-trash me-1"></i> Delete');
                    Swal.fire({ icon: 'error', title: 'Error!', text: r.message || 'Failed to delete.' });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-trash me-1"></i> Delete');
                Swal.fire({ icon: 'error', title: 'Error!', text: xhr.responseJSON?.message || 'Failed to delete.' });
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
            var rowStatus = row.data('status');
            var show = true;
            if (search && !text.includes(search)) show = false;
            if (status && rowStatus !== status) show = false;
            row.toggle(show);
        });
    }

    $('#exportBtn').on('click', function() { window.location.href = '{{ route("admin.currencies.export") }}'; });

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