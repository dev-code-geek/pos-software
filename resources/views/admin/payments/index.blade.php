@extends('layouts.admin')

@section('title', 'Payments')
@section('page-title', 'Payment Management')

@section('admin-content')
<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-lg-6">
        <div class="small-box bg-primary">
            <div class="inner"><h3>{{ $stats['total'] ?? 0 }}</h3><p>Total Payments</p></div>
            <div class="icon"><i class="fas fa-credit-card"></i></div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6">
        <div class="small-box bg-success">
            <div class="inner"><h3>{{ number_format($stats['total_amount'] ?? 0, 2) }}</h3><p>Total Amount</p></div>
            <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6">
        <div class="small-box bg-info">
            <div class="inner"><h3>{{ $stats['today'] ?? 0 }}</h3><p>Today</p></div>
            <div class="icon"><i class="fas fa-calendar-day"></i></div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6">
        <div class="small-box bg-warning">
            <div class="inner"><h3>{{ $stats['this_month'] ?? 0 }}</h3><p>This Month</p></div>
            <div class="icon"><i class="fas fa-calendar-alt"></i></div>
        </div>
    </div>
</div>

<!-- Main Card -->
<div class="card">
    <div class="card-header">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <h5 class="card-title mb-0">
                <i class="fas fa-list text-primary me-2"></i>
                All Payments
                <span class="badge bg-primary ms-1">{{ $payments->total() }}</span>
            </h5>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <div class="input-group input-group-sm" style="width: 200px;">
                <input type="text" id="searchInput" class="form-control" placeholder="Search...">
                <button class="btn btn-secondary" type="button" id="searchBtn"><i class="fas fa-search"></i></button>
            </div>
            <select id="methodFilter" class="form-select form-select-sm" style="width:140px;">
                <option value="">All Methods</option>
                <option value="cash">Cash</option>
                <option value="bank_transfer">Bank Transfer</option>
                <option value="cheque">Cheque</option>
                <option value="mobile">Mobile</option>
            </select>
            <select id="entityFilter" class="form-select form-select-sm" style="width:180px;">
                <option value="">All Entities</option>
                @foreach($entityTypes as $class => $label)
                    <option value="{{ $class }}">{{ $label }}</option>
                @endforeach
            </select>
            @can('payment.create')
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fas fa-plus-circle me-1"></i> New Payment
            </button>
            @endcan
            @can('payment.view')
            <a href="{{ route('admin.payments.export') }}" class="btn btn-success btn-sm">
                <i class="fas fa-file-excel me-1"></i> Export
            </a>
            @endcan
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:35px;">#</th>
                        <th style="min-width:100px;">Date</th>
                        <th style="min-width:150px;">Entity</th>
                        <th style="min-width:120px;">Reference</th>
                        <th style="width:120px;">Amount</th>
                        <th style="width:100px;">Method</th>
                        <th style="width:100px;">Status</th>
                        <th style="width:100px;">Created By</th>
                        <th style="width:200px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($payments as $p)
                    <tr data-id="{{ $p->id }}" data-method="{{ $p->method }}" data-entity="{{ $p->paymentable_type }}">
                        <td>{{ $loop->iteration }}</td>
                        <td><small>{{ $p->payment_date->format('M d, Y') }}</small></td>
                        <td>
                            <span class="badge bg-secondary">{{ class_basename($p->paymentable_type) }}</span>
                            #{{ $p->paymentable_id }}
                            @if($p->paymentable && ($p->paymentable->po_number ?? $p->paymentable->invoice_number))
                                <small class="text-muted d-block">{{ $p->paymentable->po_number ?? $p->paymentable->invoice_number }}</small>
                            @endif
                        </td>
                        <td>{{ $p->reference ?? 'N/A' }}</td>
                        <td><strong>{{ $p->currency->symbol ?? '$' }}{{ number_format($p->amount, 2) }}</strong></td>
                        <td>
                            <span class="badge bg-{{ ['cash'=>'success','bank_transfer'=>'info','cheque'=>'warning','mobile'=>'primary'][$p->method] }}">
                                {{ ucfirst(str_replace('_',' ',$p->method)) }}
                            </span>
                        </td>
                        <td>
                            @php $status = $p->paymentable->payment_status ?? 'pending'; @endphp
                            <span class="badge bg-{{ ['pending'=>'secondary','partial'=>'warning','paid'=>'success'][$status] }}">
                                {{ ucfirst($status) }}
                            </span>
                        </td>
                        <td>{{ $p->createdBy->name ?? 'N/A' }}</td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                @can('payment.view')
                                <button class="btn btn-outline-primary btn-sm view-btn" data-id="{{ $p->id }}" title="View"><i class="fas fa-eye"></i></button>
                                @endcan
                                @can('payment.edit')
                                <button class="btn btn-outline-warning btn-sm edit-btn" data-id="{{ $p->id }}" title="Edit"><i class="fas fa-edit"></i></button>
                                @endcan
                                @can('payment.delete')
                                <button class="btn btn-outline-danger btn-sm delete-btn" data-id="{{ $p->id }}" title="Delete"><i class="fas fa-trash"></i></button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9">
                            <div class="text-center py-5">
                                <i class="fas fa-credit-card fa-4x text-muted mb-3 d-block"></i>
                                <h5 class="fw-bold">No Payments Found</h5>
                                <p class="text-muted">Click "New Payment" to record your first payment.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div><span class="text-muted small">Showing {{ $payments->firstItem() ?? 0 }} to {{ $payments->lastItem() ?? 0 }} of {{ $payments->total() }} entries</span></div>
        <div>{{ $payments->links() }}</div>
    </div>
</div>

<!-- ============================================ -->
<!-- CREATE MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 700px;">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="fas fa-plus-circle me-2"></i> New Payment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="createForm" action="{{ route('admin.payments.store') }}" method="POST">
                @csrf
                <div class="modal-body" style="padding:20px 25px;">
                    <div id="createFormErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Entity Type <span class="text-danger">*</span></label>
                            <select name="paymentable_type" id="entityType" class="form-select" required>
                                <option value="">Select Type</option>
                                @foreach($entityTypes as $class => $label)
                                    <option value="{{ $class }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Entity ID <span class="text-danger">*</span></label>
                            <input type="number" name="paymentable_id" id="entityId" class="form-control" placeholder="Enter ID" required>
                        </div>
                        <div class="col-12">
                            <button type="button" id="loadEntityBtn" class="btn btn-info btn-sm">Load Entity Details</button>
                            <div id="entityDetails" class="mt-2 alert alert-info d-none">
                                <strong>Reference:</strong> <span id="entityRef"></span><br>
                                <strong>Total:</strong> <span id="entityTotal"></span><br>
                                <strong>Paid:</strong> <span id="entityPaid"></span><br>
                                <strong>Remaining:</strong> <span id="entityRemaining"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Amount <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control" step="0.01" min="0.01" placeholder="0.00" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Method <span class="text-danger">*</span></label>
                            <select name="method" class="form-select" required>
                                @foreach($methods as $m)
                                    <option value="{{ $m }}">{{ ucfirst(str_replace('_',' ',$m)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Currency <span class="text-danger">*</span></label>
                            <select name="currency_id" class="form-select" required>
                                @foreach($currencies as $c)
                                    <option value="{{ $c->id }}" {{ $c->is_base ? 'selected' : '' }}>{{ $c->code }} ({{ $c->symbol }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Exchange Rate</label>
                            <input type="number" name="exchange_rate" class="form-control" step="0.000001" min="0" value="1">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Reference</label>
                            <input type="text" name="reference" class="form-control" placeholder="Transaction reference">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Optional notes"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Cancel</button>
                    <button type="submit" class="btn btn-primary" id="createSubmitBtn"><i class="fas fa-save me-1"></i> Record Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- VIEW MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 700px;">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title text-white"><i class="fas fa-eye me-2"></i> Payment Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewModalBody" style="padding:20px 25px;">
                <div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- EDIT MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 700px;">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title text-dark"><i class="fas fa-edit me-2"></i> Edit Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body" id="editModalBody" style="padding:20px 25px;">
                    <div id="editFormErrors" class="alert alert-danger d-none"></div>
                    <div class="text-center py-5"><div class="spinner-border text-warning" role="status"><span class="visually-hidden">Loading...</span></div></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Cancel</button>
                    <button type="submit" class="btn btn-warning" id="editSubmitBtn"><i class="fas fa-save me-1"></i> Update Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- DELETE MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white"><i class="fas fa-exclamation-triangle me-2"></i> Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-trash fa-4x text-danger mb-3 d-block"></i>
                <p class="fw-bold">Are you sure you want to delete this payment?</p>
                <p class="text-muted small">This will update the parent entity's payment status.</p>
                <form id="deleteForm" method="POST">@csrf @method('DELETE')</form>
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
</style>

@push('scripts')
<script>
// Pass PHP data to JavaScript
var methods = @json($methods);
var currencies = @json($currencies);

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

    function clearErrors(container) {
        container.addClass('d-none').empty();
    }

    // === VIEW ===
    @can('payment.view')
    $(document).on('click', '.view-btn', function() {
        var id = $(this).data('id');
        var body = $('#viewModalBody');
        body.html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
        $('#viewModal').modal('show');
        $.ajax({
            url: '/admin/payments/' + id,
            type: 'GET',
            dataType: 'json',
            success: function(p) {
                var html = `
                    <div class="row g-3">
                        <div class="col-6"><label class="text-muted small fw-bold">Amount</label><p class="fw-bold mb-0">${p.currency ? p.currency.symbol : '$'}${parseFloat(p.amount).toFixed(2)}</p></div>
                        <div class="col-6"><label class="text-muted small fw-bold">Date</label><p class="mb-0">${new Date(p.payment_date).toLocaleDateString()}</p></div>
                        <div class="col-6"><label class="text-muted small fw-bold">Method</label><p class="mb-0">${p.method.replace('_',' ').toUpperCase()}</p></div>
                        <div class="col-6"><label class="text-muted small fw-bold">Reference</label><p class="mb-0">${p.reference || 'N/A'}</p></div>
                        <div class="col-12"><label class="text-muted small fw-bold">Entity</label><p class="mb-0">${p.paymentable ? class_basename(p.paymentable_type) + ' #' + p.paymentable_id : 'N/A'}</p></div>
                        <div class="col-12"><label class="text-muted small fw-bold">Notes</label><p class="mb-0">${p.notes || 'None'}</p></div>
                        <div class="col-6"><label class="text-muted small fw-bold">Currency</label><p class="mb-0">${p.currency ? p.currency.code : 'N/A'}</p></div>
                        <div class="col-6"><label class="text-muted small fw-bold">Exchange Rate</label><p class="mb-0">${p.exchange_rate || 1}</p></div>
                        <div class="col-6"><label class="text-muted small fw-bold">Created By</label><p class="mb-0">${p.created_by ? p.created_by.name : 'N/A'}</p></div>
                        <div class="col-6"><label class="text-muted small fw-bold">Created At</label><p class="mb-0">${new Date(p.created_at).toLocaleString()}</p></div>
                    </div>
                `;
                body.html(html);
            },
            error: function(xhr) {
                body.html('<div class="text-center py-5 text-danger"><i class="fas fa-exclamation-circle fa-4x mb-3 d-block"></i><h5>Failed to load payment details</h5><p>' + xhr.status + ' - ' + xhr.statusText + '</p></div>');
            }
        });
    });
    @endcan

    // === EDIT ===
    @can('payment.edit')
    $(document).on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        var body = $('#editModalBody');
        var form = $('#editForm');
        form.attr('action', '/admin/payments/' + id);
        body.html('<div id="editFormErrors" class="alert alert-danger d-none"></div><div class="text-center py-5"><div class="spinner-border text-warning" role="status"><span class="visually-hidden">Loading...</span></div></div>');
        $('#editModal').modal('show');
        $.ajax({
            url: '/admin/payments/' + id + '/edit',
            type: 'GET',
            dataType: 'json',
            success: function(p) {
                // Build currency options using JavaScript
                var currencyOptions = '<option value="">Select Currency</option>';
                currencies.forEach(function(c) {
                    currencyOptions += `<option value="${c.id}" ${p.currency_id == c.id ? 'selected' : ''}>${c.code} (${c.symbol})</option>`;
                });

                // Build method options using JavaScript
                var methodOptions = '<option value="">Select Method</option>';
                methods.forEach(function(m) {
                    var label = m.replace('_', ' ').toUpperCase();
                    methodOptions += `<option value="${m}" ${p.method == m ? 'selected' : ''}>${label}</option>`;
                });

                body.html(`
                    <div id="editFormErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Amount <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control" step="0.01" min="0.01" value="${p.amount}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="form-control" value="${p.payment_date}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Method <span class="text-danger">*</span></label>
                            <select name="method" class="form-select" required>${methodOptions}</select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Currency <span class="text-danger">*</span></label>
                            <select name="currency_id" class="form-select" required>${currencyOptions}</select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Exchange Rate</label>
                            <input type="number" name="exchange_rate" class="form-control" step="0.000001" min="0" value="${p.exchange_rate || 1}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Reference</label>
                            <input type="text" name="reference" class="form-control" value="${p.reference || ''}">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Notes</label>
                            <textarea name="notes" class="form-control" rows="2">${p.notes || ''}</textarea>
                        </div>
                        <div class="col-12">
                            <small class="text-muted">Entity: ${p.paymentable ? class_basename(p.paymentable_type) + ' #' + p.paymentable_id : 'N/A'}</small>
                        </div>
                    </div>
                `);
            },
            error: function(xhr) {
                body.html('<div id="editFormErrors" class="alert alert-danger"></div><div class="text-center py-5 text-danger"><i class="fas fa-exclamation-circle fa-4x mb-3 d-block"></i><h5>Failed to load payment data</h5><p>' + xhr.status + ' - ' + xhr.statusText + '</p><p class="small">' + (xhr.responseJSON?.message || '') + '</p></div>');
            }
        });
    });
    @endcan

    // === CREATE - Entity Details ===
    $('#loadEntityBtn').on('click', function() {
        var type = $('#entityType').val();
        var id = $('#entityId').val();
        if (!type || !id) {
            Swal.fire('Error', 'Please select Entity Type and enter ID.', 'warning');
            return;
        }
        $.ajax({
            url: '{{ route("admin.payments.entity-details") }}',
            data: { type: type, id: id },
            dataType: 'json',
            success: function(data) {
                $('#entityRef').text(data.reference);
                $('#entityTotal').text(data.total_amount);
                $('#entityPaid').text(data.paid_amount);
                $('#entityRemaining').text(data.remaining);
                $('#entityDetails').removeClass('d-none');
                if (data.currency_id) {
                    $('select[name="currency_id"]').val(data.currency_id);
                }
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.error || 'Entity not found.', 'error');
            }
        });
    });

    // === CREATE ===
    @can('payment.create')
    $('#createForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var data = form.serialize();
        var btn = $('#createSubmitBtn');
        var errorContainer = $('#createFormErrors');
        clearErrors(errorContainer);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#createModal').modal('hide');
                    Swal.fire({ icon: 'success', title: 'Recorded!', text: response.message, timer: 2000 }).then(function() { window.location.reload(); });
                } else {
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Record Payment');
                    if (response.errors) showErrors(errorContainer, response.errors);
                    else Swal.fire({ icon: 'error', title: 'Error!', text: response.message || 'Something went wrong.' });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Record Payment');
                if (xhr.status === 422) showErrors(errorContainer, xhr.responseJSON.errors);
                else Swal.fire({ icon: 'error', title: 'Error!', text: 'Failed to record payment.' });
            }
        });
    });
    @endcan

    // === UPDATE ===
    @can('payment.edit')
    $('#editForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        var data = form.serialize();
        var btn = $('#editSubmitBtn');
        var errorContainer = $('#editFormErrors');
        clearErrors(errorContainer);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Updating...');
        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#editModal').modal('hide');
                    Swal.fire({ icon: 'success', title: 'Updated!', text: response.message, timer: 2000 }).then(function() { window.location.reload(); });
                } else {
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Update Payment');
                    if (response.errors) showErrors(errorContainer, response.errors);
                    else Swal.fire({ icon: 'error', title: 'Error!', text: response.message || 'Something went wrong.' });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Update Payment');
                if (xhr.status === 422) showErrors(errorContainer, xhr.responseJSON.errors);
                else Swal.fire({ icon: 'error', title: 'Error!', text: 'Failed to update payment.' });
            }
        });
    });
    @endcan

    // === DELETE ===
    @can('payment.delete')
    $(document).on('click', '.delete-btn', function() {
        var id = $(this).data('id');
        var form = $('#deleteForm');
        form.attr('action', '/admin/payments/' + id);
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then(function(result) {
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
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#deleteModal').modal('hide');
                    Swal.fire({ icon: 'success', title: 'Deleted!', text: response.message, timer: 2000 }).then(function() { window.location.reload(); });
                } else {
                    btn.prop('disabled', false).html('<i class="fas fa-trash me-1"></i> Delete');
                    Swal.fire({ icon: 'error', title: 'Error!', text: response.message || 'Failed to delete payment.' });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-trash me-1"></i> Delete');
                Swal.fire({ icon: 'error', title: 'Error!', text: xhr.responseJSON?.message || 'Failed to delete payment.' });
            }
        });
    });
    @endcan

    // === FILTER ===
    $('#searchBtn').on('click', filterTable);
    $('#searchInput').on('keypress', function(e) { if (e.which === 13) filterTable(); });
    $('#methodFilter, #entityFilter').on('change', filterTable);

    function filterTable() {
        var search = $('#searchInput').val().toLowerCase();
        var method = $('#methodFilter').val();
        var entity = $('#entityFilter').val();
        $('#tableBody tr').each(function() {
            var row = $(this);
            var text = row.text().toLowerCase();
            var rowMethod = row.data('method');
            var rowEntity = row.data('entity');
            var show = true;
            if (search && !text.includes(search)) show = false;
            if (method && rowMethod !== method) show = false;
            if (entity && rowEntity !== entity) show = false;
            row.toggle(show);
        });
    }

    // === MODAL RESET ===
    $('.modal').on('hidden.bs.modal', function() {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
        $(this).find('.alert-danger').addClass('d-none').empty();
        $(this).find('form')[0]?.reset();
        if ($(this).attr('id') === 'createModal') {
            $('#entityDetails').addClass('d-none');
            $('#entityRef, #entityTotal, #entityPaid, #entityRemaining').text('');
        }
    });
});
</script>
@endpush
@endsection