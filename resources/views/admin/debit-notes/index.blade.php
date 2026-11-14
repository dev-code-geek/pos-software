@extends('layouts.admin')

@section('title', 'Debit Notes')
@section('page-title', 'Debit Note Management')

@section('admin-content')
<div class="row g-3 mb-4">
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-primary">
            <div class="inner"><h3>{{ number_format($stats['total'] ?? 0) }}</h3><p>Total</p></div>
            <div class="icon"><i class="fas fa-file-invoice"></i></div>
            <a href="{{ route('admin.debit-notes.index') }}" class="small-box-footer">View All <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-secondary">
            <div class="inner"><h3>{{ number_format($stats['draft'] ?? 0) }}</h3><p>Draft</p></div>
            <div class="icon"><i class="fas fa-pen"></i></div>
            <a href="{{ route('admin.debit-notes.index', ['status' => 'draft']) }}" class="small-box-footer">View Draft <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-success">
            <div class="inner"><h3>{{ number_format($stats['approved'] ?? 0) }}</h3><p>Approved</p></div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
            <a href="{{ route('admin.debit-notes.index', ['status' => 'approved']) }}" class="small-box-footer">View Approved <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-danger">
            <div class="inner"><h3>{{ number_format($stats['rejected'] ?? 0) }}</h3><p>Rejected</p></div>
            <div class="icon"><i class="fas fa-times-circle"></i></div>
            <a href="{{ route('admin.debit-notes.index', ['status' => 'rejected']) }}" class="small-box-footer">View Rejected <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-warning text-dark">
            <div class="inner"><h3>{{ number_format($stats['cancelled'] ?? 0) }}</h3><p>Cancelled</p></div>
            <div class="icon"><i class="fas fa-times"></i></div>
            <a href="{{ route('admin.debit-notes.index', ['status' => 'cancelled']) }}" class="small-box-footer">View Cancelled <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <h5 class="card-title mb-0"><i class="fas fa-file-invoice text-primary me-2"></i>All Debit Notes <span class="badge bg-primary ms-1">{{ $debitNotes->total() }}</span></h5>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <div class="input-group input-group-sm" style="width:200px;">
                <input type="text" id="searchInput" class="form-control" placeholder="Search...">
                <button class="btn btn-secondary" type="button" id="searchBtn"><i class="fas fa-search"></i></button>
            </div>
            <select id="statusFilter" class="form-select form-select-sm" style="width:120px;">
                <option value="">All Status</option>
                <option value="draft">Draft</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
                <option value="cancelled">Cancelled</option>
            </select>
            @can('debit_note.create')
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fas fa-plus-circle me-1"></i> New Debit Note
            </button>
            @endcan
            @can('debit_note.export')
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
                        <th>Debit Note #</th>
                        <th>Supplier</th>
                        <th class="text-end">Total</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th style="width:200px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($debitNotes as $note)
                    <tr data-id="{{ $note->id }}" data-status="{{ $note->status }}">
                        <td>{{ $loop->iteration }}</td>
                        <td><span class="fw-bold">{{ $note->debit_note_number }}</span></td>
                        <td>{{ $note->supplier->name ?? 'N/A' }}</td>
                        <td class="text-end"><span class="fw-bold text-primary">${{ number_format($note->total, 2) }}</span></td>
                        <td>
                            @php
                                $statusColors = ['draft' => 'secondary', 'approved' => 'success', 'rejected' => 'danger', 'cancelled' => 'warning'];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$note->status] ?? 'secondary' }}">{{ ucfirst($note->status) }}</span>
                        </td>
                        <td>{{ $note->debit_note_date ? $note->debit_note_date->format('M d, Y') : 'N/A' }}</td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                @can('debit_note.view')
                                <button class="btn btn-outline-primary btn-sm view-btn" data-id="{{ $note->id }}" title="View"><i class="fas fa-eye"></i></button>
                                @endcan
                                @can('debit_note.edit')
                                @if($note->status == 'draft')
                                <button class="btn btn-outline-warning btn-sm edit-btn" data-id="{{ $note->id }}" title="Edit"><i class="fas fa-edit"></i></button>
                                @endif
                                @endcan
                                @can('debit_note.approve')
                                @if($note->status == 'draft')
                                <button class="btn btn-outline-success btn-sm approve-btn" data-id="{{ $note->id }}" title="Approve"><i class="fas fa-check"></i></button>
                                @endif
                                @endcan
                                @can('debit_note.reject')
                                @if($note->status == 'draft')
                                <button class="btn btn-outline-danger btn-sm reject-btn" data-id="{{ $note->id }}" title="Reject"><i class="fas fa-times"></i></button>
                                @endif
                                @endcan
                                @can('debit_note.print')
                                <a href="{{ route('admin.debit-notes.print', $note->id) }}" class="btn btn-outline-secondary btn-sm" title="Print" target="_blank"><i class="fas fa-print"></i></a>
                                @endcan
                                @can('debit_note.pdf')
                                <a href="{{ route('admin.debit-notes.pdf', $note->id) }}" class="btn btn-outline-secondary btn-sm" title="Download PDF"><i class="fas fa-file-pdf"></i></a>
                                @endcan
                                @can('debit_note.delete')
                                @if(in_array($note->status, ['draft', 'rejected']))
                                <button class="btn btn-outline-danger btn-sm delete-btn" data-id="{{ $note->id }}" title="Delete"><i class="fas fa-trash"></i></button>
                                @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7"><div class="text-center py-5"><i class="fas fa-file-invoice fa-4x text-muted mb-3 d-block"></i><h5 class="fw-bold">No Debit Notes Found</h5></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div><span class="text-muted small">Showing {{ $debitNotes->firstItem() ?? 0 }} to {{ $debitNotes->lastItem() ?? 0 }} of {{ $debitNotes->total() }} entries</span></div>
        <div>{{ $debitNotes->links() }}</div>
    </div>
</div>

<!-- ============================================ -->
<!-- CREATE MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width:700px;">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="fas fa-plus-circle me-2"></i> New Debit Note</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="createForm" action="{{ route('admin.debit-notes.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div id="createFormErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Supplier <span class="text-danger">*</span></label>
                            <select name="supplier_id" class="form-select" required>
                                <option value="">Select Supplier</option>
                                @foreach($suppliers ?? [] as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Currency <span class="text-danger">*</span></label>
                            <select name="currency_id" class="form-select" required>
                                <option value="">Select Currency</option>
                                @foreach($currencies ?? [] as $cur)
                                    <option value="{{ $cur->id }}">{{ $cur->code }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Debit Note Date <span class="text-danger">*</span></label>
                            <input type="date" name="debit_note_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Reason <span class="text-danger">*</span></label>
                            <input type="text" name="reason" class="form-control" placeholder="Reason for debit note" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Notes</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <hr>
                            <h6 class="fw-bold"><i class="fas fa-boxes me-2"></i>Items</h6>
                            <div id="createItems">
                                <div class="row g-2 item-row align-items-end">
                                    <div class="col-4">
                                        <select name="items[0][product_id]" class="form-select" required>
                                            <option value="">Product</option>
                                            @foreach($products ?? [] as $prod)
                                                <option value="{{ $prod->id }}">{{ $prod->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-2">
                                        <input type="number" name="items[0][quantity]" class="form-control" placeholder="Qty" step="0.01" min="0.01" required>
                                    </div>
                                    <div class="col-2">
                                        <input type="number" name="items[0][unit_price]" class="form-control" placeholder="Price" step="0.01" min="0" required>
                                    </div>
                                    <div class="col-3">
                                        <input type="text" name="items[0][reason]" class="form-control" placeholder="Item reason">
                                    </div>
                                    <div class="col-1">
                                        <button type="button" class="btn btn-danger btn-sm remove-item-btn" style="display:none;"><i class="fas fa-trash"></i></button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-secondary mt-2" id="addItemBtn"><i class="fas fa-plus me-1"></i> Add Item</button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Cancel</button>
                    <button type="submit" class="btn btn-primary" id="createSubmitBtn"><i class="fas fa-save me-1"></i> Create Debit Note</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- VIEW, EDIT, DELETE MODALS -->
<!-- Similar to previous modules – include them -->
<!-- ============================================ -->

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

    // Add/Remove Items (Create)
    var itemIndex = 1;
    $('#addItemBtn').on('click', function() {
        var html = `
            <div class="row g-2 item-row align-items-end mt-2">
                <div class="col-4">
                    <select name="items[${itemIndex}][product_id]" class="form-select" required>
                        <option value="">Product</option>
                        @foreach($products ?? [] as $prod)
                            <option value="{{ $prod->id }}">{{ $prod->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-2">
                    <input type="number" name="items[${itemIndex}][quantity]" class="form-control" placeholder="Qty" step="0.01" min="0.01" required>
                </div>
                <div class="col-2">
                    <input type="number" name="items[${itemIndex}][unit_price]" class="form-control" placeholder="Price" step="0.01" min="0" required>
                </div>
                <div class="col-3">
                    <input type="text" name="items[${itemIndex}][reason]" class="form-control" placeholder="Item reason">
                </div>
                <div class="col-1">
                    <button type="button" class="btn btn-danger btn-sm remove-item-btn"><i class="fas fa-trash"></i></button>
                </div>
            </div>
        `;
        $('#createItems').append(html);
        if ($('#createItems .item-row').length > 1) {
            $('#createItems .remove-item-btn').show();
        }
        itemIndex++;
    });

    $(document).on('click', '.remove-item-btn', function() {
        $(this).closest('.item-row').remove();
        if ($('#createItems .item-row').length <= 1) {
            $('#createItems .remove-item-btn').hide();
        }
    });
    setTimeout(() => $('#createItems .remove-item-btn').hide(), 100);

    // CREATE
    @can('debit_note.create')
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
                    Swal.fire({ icon: 'success', title: 'Created!', text: r.message, timer: 2000, showConfirmButton: true }).then(() => { location.reload(); });
                } else {
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Create Debit Note');
                    if (r.errors) showErrors(errorContainer, r.errors);
                    else Swal.fire({ icon: 'error', title: 'Error!', text: r.message || 'Something went wrong.' });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Create Debit Note');
                if (xhr.status === 422) showErrors(errorContainer, xhr.responseJSON.errors);
                else Swal.fire({ icon: 'error', title: 'Error!', text: 'Failed to create debit note.' });
            }
        });
    });
    @endcan

    // VIEW
    @can('debit_note.view')
    $(document).on('click', '.view-btn', function() {
        var id = $(this).data('id');
        var body = $('#viewModalBody');
        body.html(`<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>`);
        $('#viewModal').modal('show');

        $.ajax({
            url: '/admin/debit-notes/' + id,
            type: 'GET',
            dataType: 'json',
            success: function(r) {
                var note = r;
                var items = note.items || [];
                var html = `
                    <div class="row g-3">
                        <div class="col-md-6"><label class="text-muted small fw-bold">Debit Note #</label><p class="fw-bold mb-0">${note.debit_note_number}</p></div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">Supplier</label><p class="mb-0">${note.supplier ? note.supplier.name : 'N/A'}</p></div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">Total</label><p class="fw-bold text-primary mb-0">$${parseFloat(note.total).toFixed(2)}</p></div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">Status</label><p class="mb-0"><span class="badge bg-${note.status == 'approved' ? 'success' : (note.status == 'draft' ? 'secondary' : (note.status == 'rejected' ? 'danger' : 'warning'))}">${note.status.toUpperCase()}</span></p></div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">Date</label><p class="mb-0">${new Date(note.debit_note_date).toLocaleDateString()}</p></div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">Reason</label><p class="mb-0">${note.reason}</p></div>
                        ${note.notes ? `<div class="col-12"><label class="text-muted small fw-bold">Notes</label><p class="mb-0">${note.notes}</p></div>` : ''}
                        <div class="col-12"><hr><h6 class="fw-bold text-primary"><i class="fas fa-boxes me-2"></i>Items</h6>
                        <table class="table table-sm table-bordered">
                            <thead><tr><th>#</th><th>Product</th><th class="text-end">Qty</th><th class="text-end">Price</th><th class="text-end">Total</th><th>Reason</th></tr></thead>
                            <tbody>
                                ${items.map(function(item, idx) {
                                    return `<tr><td>${idx+1}</td><td>${item.product ? item.product.name : 'N/A'}</td><td class="text-end">${item.quantity}</td><td class="text-end">$${parseFloat(item.unit_price).toFixed(2)}</td><td class="text-end">$${parseFloat(item.total).toFixed(2)}</td><td>${item.reason || '-'}</td></tr>`;
                                }).join('')}
                            </tbody>
                        </table></div>
                    </div>
                `;
                body.html(html);
            },
            error: function(xhr) {
                body.html(`<div class="text-center py-5 text-danger"><i class="fas fa-exclamation-circle fa-4x mb-3 d-block"></i><h5>Failed to load</h5><p>${xhr.status} - ${xhr.statusText}</p></div>`);
            }
        });
    });
    @endcan

    // EDIT
    @can('debit_note.edit')
    $(document).on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        var body = $('#editModalBody');
        var form = $('#editForm');

        form.attr('action', '/admin/debit-notes/' + id);
        body.html(`<div id="editFormErrors" class="alert alert-danger d-none"></div><div class="text-center py-5"><div class="spinner-border text-warning" role="status"><span class="visually-hidden">Loading...</span></div></div>`);
        $('#editModal').modal('show');

        $.ajax({
            url: '/admin/debit-notes/' + id + '/edit',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (!response.success) {
                    body.html(`<div id="editFormErrors" class="alert alert-danger">${response.error || 'Failed to load'}</div>`);
                    return;
                }
                var note = response.debit_note;
                var suppliers = response.suppliers || [];
                var products = response.products || [];
                var currencies = response.currencies || [];
                var items = note.items || [];

                var supplierOptions = suppliers.map(function(s) {
                    return `<option value="${s.id}" ${note.supplier_id == s.id ? 'selected' : ''}>${s.name}</option>`;
                }).join('');
                var currencyOptions = currencies.map(function(c) {
                    return `<option value="${c.id}" ${note.currency_id == c.id ? 'selected' : ''}>${c.code}</option>`;
                }).join('');
                var productOptions = products.map(function(p) {
                    return `<option value="${p.id}">${p.name}</option>`;
                }).join('');

                var itemsHtml = items.map(function(item, idx) {
                    return `
                        <div class="row g-2 item-row align-items-end">
                            <div class="col-4">
                                <select name="items[${idx}][product_id]" class="form-select" required>
                                    <option value="">Product</option>
                                    ${products.map(function(p) {
                                        return `<option value="${p.id}" ${item.product_id == p.id ? 'selected' : ''}>${p.name}</option>`;
                                    }).join('')}
                                </select>
                            </div>
                            <div class="col-2">
                                <input type="number" name="items[${idx}][quantity]" class="form-control" placeholder="Qty" step="0.01" min="0.01" value="${item.quantity}" required>
                            </div>
                            <div class="col-2">
                                <input type="number" name="items[${idx}][unit_price]" class="form-control" placeholder="Price" step="0.01" min="0" value="${item.unit_price}" required>
                            </div>
                            <div class="col-3">
                                <input type="text" name="items[${idx}][reason]" class="form-control" placeholder="Item reason" value="${item.reason || ''}">
                            </div>
                            <div class="col-1">
                                <button type="button" class="btn btn-danger btn-sm remove-item-btn ${items.length <= 1 ? 'd-none' : ''}"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                    `;
                }).join('');

                body.html(`
                    <div id="editFormErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Supplier <span class="text-danger">*</span></label>
                            <select name="supplier_id" class="form-select" required>${supplierOptions}</select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Currency <span class="text-danger">*</span></label>
                            <select name="currency_id" class="form-select" required>${currencyOptions}</select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Debit Note Date <span class="text-danger">*</span></label>
                            <input type="date" name="debit_note_date" class="form-control" value="${note.debit_note_date ? new Date(note.debit_note_date).toISOString().split('T')[0] : ''}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Reason <span class="text-danger">*</span></label>
                            <input type="text" name="reason" class="form-control" value="${note.reason}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Notes</label>
                            <textarea name="notes" class="form-control" rows="2">${note.notes || ''}</textarea>
                        </div>
                        <div class="col-12">
                            <hr>
                            <h6 class="fw-bold"><i class="fas fa-boxes me-2"></i>Items</h6>
                            <div id="editItems">${itemsHtml}</div>
                            <button type="button" class="btn btn-sm btn-secondary mt-2" id="editAddItemBtn"><i class="fas fa-plus me-1"></i> Add Item</button>
                        </div>
                    </div>
                `);

                var editItemIndex = items.length;
                $('#editAddItemBtn').off('click').on('click', function() {
                    var html = `
                        <div class="row g-2 item-row align-items-end mt-2">
                            <div class="col-4">
                                <select name="items[${editItemIndex}][product_id]" class="form-select" required>
                                    <option value="">Product</option>
                                    ${productOptions}
                                </select>
                            </div>
                            <div class="col-2">
                                <input type="number" name="items[${editItemIndex}][quantity]" class="form-control" placeholder="Qty" step="0.01" min="0.01" required>
                            </div>
                            <div class="col-2">
                                <input type="number" name="items[${editItemIndex}][unit_price]" class="form-control" placeholder="Price" step="0.01" min="0" required>
                            </div>
                            <div class="col-3">
                                <input type="text" name="items[${editItemIndex}][reason]" class="form-control" placeholder="Item reason">
                            </div>
                            <div class="col-1">
                                <button type="button" class="btn btn-danger btn-sm remove-item-btn"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                    `;
                    $('#editItems').append(html);
                    editItemIndex++;
                });
                $(document).on('click', '.remove-item-btn', function() {
                    $(this).closest('.item-row').remove();
                    if ($('#editItems .item-row').length <= 1) {
                        $('#editItems .remove-item-btn').addClass('d-none');
                    }
                });
                if ($('#editItems .item-row').length <= 1) {
                    $('#editItems .remove-item-btn').addClass('d-none');
                }
            },
            error: function(xhr) {
                body.html(`<div id="editFormErrors" class="alert alert-danger">Failed to load debit note data.</div>`);
            }
        });
    });
    @endcan

    // UPDATE
    @can('debit_note.edit')
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
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Update Debit Note');
                    if (r.errors) showErrors(errorContainer, r.errors);
                    else Swal.fire({ icon: 'error', title: 'Error!', text: r.message || 'Something went wrong.' });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Update Debit Note');
                if (xhr.status === 422) showErrors(errorContainer, xhr.responseJSON.errors);
                else Swal.fire({ icon: 'error', title: 'Error!', text: 'Failed to update debit note.' });
            }
        });
    });
    @endcan

    // DELETE
    @can('debit_note.delete')
    $(document).on('click', '.delete-btn', function() {
        var id = $(this).data('id');
        var form = $('#deleteForm');
        form.attr('action', '/admin/debit-notes/' + id);
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

    // APPROVE
    @can('debit_note.approve')
    $(document).on('click', '.approve-btn', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Approve Debit Note?',
            text: "This will apply the stock changes.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, approve!'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Approving...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
                $.ajax({
                    url: '/admin/debit-notes/' + id + '/approve',
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(r) {
                        if (r.success) {
                            Swal.fire({ icon: 'success', title: 'Approved!', text: r.message, timer: 1500, showConfirmButton: true }).then(() => { location.reload(); });
                        } else {
                            Swal.fire('Error', r.message || 'Failed to approve.', 'error');
                        }
                    },
                    error: function() { Swal.fire('Error', 'Failed to approve.', 'error'); }
                });
            }
        });
    });
    @endcan

    // REJECT
    @can('debit_note.reject')
    $(document).on('click', '.reject-btn', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Reject Debit Note?',
            input: 'textarea',
            inputLabel: 'Reason (optional)',
            inputPlaceholder: 'Enter reason...',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, reject!'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Rejecting...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
                $.ajax({
                    url: '/admin/debit-notes/' + id + '/reject',
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}', reason: result.value || '' },
                    success: function(r) {
                        if (r.success) {
                            Swal.fire({ icon: 'success', title: 'Rejected!', text: r.message, timer: 1500, showConfirmButton: true }).then(() => { location.reload(); });
                        } else {
                            Swal.fire('Error', r.message || 'Failed to reject.', 'error');
                        }
                    },
                    error: function() { Swal.fire('Error', 'Failed to reject.', 'error'); }
                });
            }
        });
    });
    @endcan

    // SEARCH & FILTER
    $('#searchBtn').on('click', filterTable);
    $('#searchInput').on('keypress', function(e) { if (e.which === 13) filterTable(); });
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

    $('#exportBtn').on('click', function() { window.location.href = '{{ route("admin.debit-notes.export") }}'; });

    // MODAL RESET
    $('.modal').on('hidden.bs.modal', function() {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
        $(this).find('.alert-danger').addClass('d-none').empty();
        $(this).find('form')[0]?.reset();
        // Reset items container
        if ($(this).find('#createItems').length) {
            $('#createItems').html(`
                <div class="row g-2 item-row align-items-end">
                    <div class="col-4">
                        <select name="items[0][product_id]" class="form-select" required>
                            <option value="">Product</option>
                            @foreach($products ?? [] as $prod)
                                <option value="{{ $prod->id }}">{{ $prod->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-2">
                        <input type="number" name="items[0][quantity]" class="form-control" placeholder="Qty" step="0.01" min="0.01" required>
                    </div>
                    <div class="col-2">
                        <input type="number" name="items[0][unit_price]" class="form-control" placeholder="Price" step="0.01" min="0" required>
                    </div>
                    <div class="col-3">
                        <input type="text" name="items[0][reason]" class="form-control" placeholder="Item reason">
                    </div>
                    <div class="col-1">
                        <button type="button" class="btn btn-danger btn-sm remove-item-btn" style="display:none;"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            `);
            itemIndex = 1;
        }
    });
});
</script>
@endpush
@endsection