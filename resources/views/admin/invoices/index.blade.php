@extends('layouts.admin')

@section('title', 'Invoices')
@section('page-title', 'Invoice Management')

@section('admin-content')
<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-primary">
            <div class="inner"><h3>{{ number_format($stats['total'] ?? 0) }}</h3><p>Total</p></div>
            <div class="icon"><i class="fas fa-file-invoice"></i></div>
            <a href="{{ route('admin.invoices.index') }}" class="small-box-footer">View All <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-warning text-dark">
            <div class="inner"><h3>{{ number_format($stats['pending'] ?? 0) }}</h3><p>Pending</p></div>
            <div class="icon"><i class="fas fa-clock"></i></div>
            <a href="{{ route('admin.invoices.index', ['status' => 'pending']) }}" class="small-box-footer">View Pending <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-info">
            <div class="inner"><h3>{{ number_format($stats['partial'] ?? 0) }}</h3><p>Partial</p></div>
            <div class="icon"><i class="fas fa-fill-drip"></i></div>
            <a href="{{ route('admin.invoices.index', ['status' => 'partial']) }}" class="small-box-footer">View Partial <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-success">
            <div class="inner"><h3>{{ number_format($stats['paid'] ?? 0) }}</h3><p>Paid</p></div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
            <a href="{{ route('admin.invoices.index', ['status' => 'paid']) }}" class="small-box-footer">View Paid <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-danger">
            <div class="inner"><h3>{{ number_format($stats['overdue'] ?? 0) }}</h3><p>Overdue</p></div>
            <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
            <a href="{{ route('admin.invoices.index', ['status' => 'overdue']) }}" class="small-box-footer">View Overdue <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<!-- Main Card -->
<div class="card">
    <div class="card-header">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <h5 class="card-title mb-0"><i class="fas fa-file-invoice text-primary me-2"></i>All Invoices <span class="badge bg-primary ms-1">{{ $invoices->total() }}</span></h5>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <div class="input-group input-group-sm" style="width:200px;">
                <input type="text" id="searchInput" class="form-control" placeholder="Search...">
                <button class="btn btn-secondary" type="button" id="searchBtn"><i class="fas fa-search"></i></button>
            </div>
            <select id="statusFilter" class="form-select form-select-sm" style="width:120px;">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="partial">Partial</option>
                <option value="paid">Paid</option>
                <option value="overdue">Overdue</option>
            </select>
            @can('invoice.create')
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fas fa-plus-circle me-1"></i> Create Invoice
            </button>
            @endcan
            @can('invoice.export')
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
                        <th>Invoice #</th>
                        <th>Customer</th>
                        <th class="text-end">Total</th>
                        <th>Payment Status</th>
                        <th>Due Date</th>
                        <th style="width:260px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($invoices as $invoice)
                    <tr data-id="{{ $invoice->id }}" data-status="{{ $invoice->payment_status }}">
                        <td>{{ $loop->iteration }}</td>
                        <td><span class="fw-bold">{{ $invoice->invoice_number }}</span></td>
                        <td>{{ $invoice->customer->name ?? 'N/A' }}</td>
                        <td class="text-end"><span class="fw-bold text-primary">${{ number_format($invoice->total, 2) }}</span></td>
                        <td>
                            @php
                                $statusColors = ['pending' => 'warning', 'partial' => 'info', 'paid' => 'success', 'overdue' => 'danger'];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$invoice->payment_status] ?? 'secondary' }}">{{ ucfirst($invoice->payment_status) }}</span>
                        </td>
                        <td>{{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'N/A' }}</td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                @can('invoice.view')
                                <button class="btn btn-outline-primary btn-sm view-btn" data-id="{{ $invoice->id }}" title="View"><i class="fas fa-eye"></i></button>
                                @endcan
                                @can('invoice.edit')
                                @if($invoice->payment_status != 'paid')
                                <button class="btn btn-outline-warning btn-sm edit-btn" data-id="{{ $invoice->id }}" title="Edit"><i class="fas fa-edit"></i></button>
                                @endif
                                @endcan
                                @can('sales_payment.create')
                                @if($invoice->payment_status != 'paid')
                                <button class="btn btn-outline-success btn-sm payment-btn" data-id="{{ $invoice->id }}" title="Record Payment"><i class="fas fa-money-bill-wave"></i></button>
                                @endif
                                @endcan
                                @can('invoice.mark_paid')
                                @if($invoice->payment_status != 'paid')
                                <button class="btn btn-outline-success btn-sm mark-paid-btn" data-id="{{ $invoice->id }}" title="Mark as Paid"><i class="fas fa-check-circle"></i></button>
                                @endif
                                @endcan
                                @can('invoice.send_reminder')
                                @if($invoice->payment_status == 'overdue' || $invoice->payment_status == 'pending')
                                <button class="btn btn-outline-info btn-sm send-reminder-btn" data-id="{{ $invoice->id }}" title="Send Reminder"><i class="fas fa-envelope"></i></button>
                                @endif
                                @endcan
                                @can('invoice.print')
                                <a href="{{ route('admin.invoices.print', $invoice->id) }}" class="btn btn-outline-secondary btn-sm" title="Print" target="_blank"><i class="fas fa-print"></i></a>
                                @endcan
                                @can('invoice.pdf')
                                <a href="{{ route('admin.invoices.pdf', $invoice->id) }}" class="btn btn-outline-secondary btn-sm" title="Download PDF"><i class="fas fa-file-pdf"></i></a>
                                @endcan
                                @can('invoice.delete')
                                <button class="btn btn-outline-danger btn-sm delete-btn" data-id="{{ $invoice->id }}" title="Delete"><i class="fas fa-trash"></i></button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7"><div class="text-center py-5"><i class="fas fa-file-invoice fa-4x text-muted mb-3 d-block"></i><h5 class="fw-bold">No Invoices Found</h5></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div><span class="text-muted small">Showing {{ $invoices->firstItem() ?? 0 }} to {{ $invoices->lastItem() ?? 0 }} of {{ $invoices->total() }} entries</span></div>
        <div>{{ $invoices->links() }}</div>
    </div>
</div>

<!-- ============================================ -->
<!-- CREATE MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:600px;">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="fas fa-plus-circle me-2"></i> Create Invoice</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="createForm" action="{{ route('admin.invoices.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div id="createFormErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Select Sale <span class="text-danger">*</span></label>
                            <select name="sale_id" class="form-select" required>
                                <option value="">Select a sale to generate invoice</option>
                                @foreach($salesWithoutInvoice ?? [] as $sale)
                                    <option value="{{ $sale->id }}">
                                        {{ $sale->invoice_number }} - {{ $sale->customer->name ?? 'Walk-in' }} - ${{ number_format($sale->total, 2) }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Only sales without an invoice are shown.</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Invoice Date <span class="text-danger">*</span></label>
                            <input type="date" name="invoice_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Due Date</label>
                            <input type="date" name="due_date" class="form-control" value="{{ date('Y-m-d', strtotime('+30 days')) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Notes</label>
                            <textarea name="notes" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Cancel</button>
                    <button type="submit" class="btn btn-primary" id="createSubmitBtn"><i class="fas fa-save me-1"></i> Generate Invoice</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- VIEW MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width:750px;">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title text-white"><i class="fas fa-eye me-2"></i> Invoice Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewModalBody" style="max-height:70vh; overflow-y:auto;">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- EDIT MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width:600px;">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title text-dark"><i class="fas fa-edit me-2"></i> Edit Invoice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body" id="editModalBody" style="max-height:70vh; overflow-y:auto;">
                    <div id="editFormErrors" class="alert alert-danger d-none"></div>
                    <div class="text-center py-5">
                        <div class="spinner-border text-warning" role="status"><span class="visually-hidden">Loading...</span></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Cancel</button>
                    <button type="submit" class="btn btn-warning" id="editSubmitBtn"><i class="fas fa-save me-1"></i> Update Invoice</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- PAYMENT MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:560px;">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title text-white"><i class="fas fa-money-bill-wave me-2"></i> Record Payment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="paymentForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div id="paymentFormErrors" class="alert alert-danger d-none"></div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> Enter payment details. The invoice's paid amount and status will update automatically.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Amount <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control" step="0.01" min="0.01" placeholder="0.00" required>
                        <small class="text-muted">Remaining balance: <span id="remainingBalance">0.00</span></small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Method <span class="text-danger">*</span></label>
                        <select name="method" class="form-select" required>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cheque">Cheque</option>
                            <option value="mobile">Mobile Payment</option>
                            <option value="credit">Credit</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Reference</label>
                        <input type="text" name="reference" class="form-control" placeholder="Transaction reference (optional)">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Optional notes"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Cancel</button>
                    <button type="submit" class="btn btn-success" id="paymentSubmitBtn"><i class="fas fa-check me-1"></i> Record Payment</button>
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
                <p class="fw-bold">Are you sure you want to delete this invoice?</p>
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

    // ==========================================
    // HELPER FUNCTIONS
    // ==========================================
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

    // ==========================================
    // CREATE
    // ==========================================
    @can('invoice.create')
    $('#createForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var data = form.serialize();
        var btn = $('#createSubmitBtn');
        var errorContainer = $('#createFormErrors');
        clearErrors(errorContainer);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Generating...');

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(r) {
                if (r.success) {
                    $('#createModal').modal('hide');
                    Swal.fire({ icon: 'success', title: 'Invoice Generated!', text: r.message, timer: 2000, showConfirmButton: true }).then(() => { location.reload(); });
                } else {
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Generate Invoice');
                    if (r.errors) showErrors(errorContainer, r.errors);
                    else Swal.fire({ icon: 'error', title: 'Error!', text: r.message || 'Something went wrong.' });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Generate Invoice');
                if (xhr.status === 422) showErrors(errorContainer, xhr.responseJSON.errors);
                else Swal.fire({ icon: 'error', title: 'Error!', text: 'Failed to generate invoice.' });
            }
        });
    });
    @endcan

    // ==========================================
    // VIEW
    // ==========================================
    @can('invoice.view')
    $(document).on('click', '.view-btn', function() {
        var id = $(this).data('id');
        var body = $('#viewModalBody');
        body.html(`<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>`);
        $('#viewModal').modal('show');

        $.ajax({
            url: '/admin/invoices/' + id,
            type: 'GET',
            dataType: 'json',
            success: function(r) {
                var invoice = r;
                var sale = invoice.sale || {};
                var items = sale.items || [];
                var payments = invoice.sales_payments || []; // loaded via relation

                var html = `
                    <div class="row g-3">
                        <div class="col-md-6"><label class="text-muted small fw-bold">Invoice #</label><p class="fw-bold mb-0">${invoice.invoice_number}</p></div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">Customer</label><p class="mb-0">${invoice.customer ? invoice.customer.name : 'N/A'}</p></div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">Total</label><p class="fw-bold text-primary mb-0">$${parseFloat(invoice.total).toFixed(2)}</p></div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">Status</label><p class="mb-0"><span class="badge bg-${invoice.payment_status == 'paid' ? 'success' : (invoice.payment_status == 'pending' ? 'warning' : (invoice.payment_status == 'partial' ? 'info' : 'danger'))}">${invoice.payment_status.toUpperCase()}</span></p></div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">Invoice Date</label><p class="mb-0">${new Date(invoice.invoice_date).toLocaleDateString()}</p></div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">Due Date</label><p class="mb-0">${invoice.due_date ? new Date(invoice.due_date).toLocaleDateString() : 'N/A'}</p></div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">Amount Paid</label><p class="mb-0">$${parseFloat(invoice.amount_paid || 0).toFixed(2)}</p></div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">Balance</label><p class="fw-bold mb-0">$${parseFloat(invoice.balance || 0).toFixed(2)}</p></div>
                        ${invoice.notes ? `<div class="col-12"><label class="text-muted small fw-bold">Notes</label><p class="mb-0">${invoice.notes}</p></div>` : ''}
                        ${items.length > 0 ? `
                        <div class="col-12"><hr><h6 class="fw-bold text-primary"><i class="fas fa-boxes me-2"></i>Items</h6>
                        <table class="table table-sm table-bordered">
                            <thead><tr><th>#</th><th>Product</th><th class="text-end">Qty</th><th class="text-end">Price</th><th class="text-end">Total</th></tr></thead>
                            <tbody>
                                ${items.map(function(item, idx) {
                                    return `<tr><td>${idx+1}</td><td>${item.product_name || item.product?.name || 'N/A'}</td><td class="text-end">${item.quantity}</td><td class="text-end">$${parseFloat(item.unit_price).toFixed(2)}</td><td class="text-end">$${parseFloat(item.total).toFixed(2)}</td></tr>`;
                                }).join('')}
                            </tbody>
                        </table></div>` : ''}
                        ${payments.length > 0 ? `
                        <div class="col-12">
                            <hr>
                            <h6 class="fw-bold text-success"><i class="fas fa-credit-card me-2"></i>Payment History</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-striped">
                                    <thead><tr><th>Date</th><th>Method</th><th>Amount</th><th>Reference</th><th>By</th></tr></thead>
                                    <tbody>
                                        ${payments.map(function(p) {
                                            return `<tr>
                                                <td>${new Date(p.payment_date).toLocaleDateString()}</td>
                                                <td>${p.method}</td>
                                                <td>${p.currency ? p.currency.symbol : '$'}${parseFloat(p.amount).toFixed(2)}</td>
                                                <td>${p.reference || '-'}</td>
                                                <td>${p.created_by ? p.created_by.name : 'N/A'}</td>
                                            </tr>`;
                                        }).join('')}
                                    </tbody>
                                </table>
                            </div>
                        </div>` : ''}
                    </div>
                `;
                body.html(html);
            },
            error: function(xhr) {
                body.html(`<div class="text-center py-5 text-danger"><i class="fas fa-exclamation-circle fa-4x mb-3 d-block"></i><h5>Failed to load invoice details</h5><p>${xhr.status} - ${xhr.statusText}</p></div>`);
            }
        });
    });
    @endcan

    // ==========================================
    // EDIT
    // ==========================================
    @can('invoice.edit')
    $(document).on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        var body = $('#editModalBody');
        var form = $('#editForm');

        form.attr('action', '/admin/invoices/' + id);
        body.html(`<div id="editFormErrors" class="alert alert-danger d-none"></div><div class="text-center py-5"><div class="spinner-border text-warning" role="status"><span class="visually-hidden">Loading...</span></div></div>`);
        $('#editModal').modal('show');

        $.ajax({
            url: '/admin/invoices/' + id + '/edit',
            type: 'GET',
            dataType: 'json',
            success: function(r) {
                body.html(`
                    <div id="editFormErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Payment Status <span class="text-danger">*</span></label>
                            <select name="payment_status" class="form-select" required>
                                <option value="pending" ${r.payment_status == 'pending' ? 'selected' : ''}>Pending</option>
                                <option value="partial" ${r.payment_status == 'partial' ? 'selected' : ''}>Partial</option>
                                <option value="paid" ${r.payment_status == 'paid' ? 'selected' : ''}>Paid</option>
                                <option value="overdue" ${r.payment_status == 'overdue' ? 'selected' : ''}>Overdue</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Due Date</label>
                            <input type="date" name="due_date" class="form-control" value="${r.due_date ? new Date(r.due_date).toISOString().split('T')[0] : ''}">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Notes</label>
                            <textarea name="notes" class="form-control" rows="3">${r.notes || ''}</textarea>
                        </div>
                    </div>
                `);
            },
            error: function(xhr) {
                body.html(`<div id="editFormErrors" class="alert alert-danger">Failed to load invoice data.</div>`);
            }
        });
    });
    @endcan

    // ==========================================
    // UPDATE
    // ==========================================
    @can('invoice.edit')
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
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Update Invoice');
                    if (r.errors) showErrors(errorContainer, r.errors);
                    else Swal.fire({ icon: 'error', title: 'Error!', text: r.message || 'Something went wrong.' });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Update Invoice');
                if (xhr.status === 422) showErrors(errorContainer, xhr.responseJSON.errors);
                else Swal.fire({ icon: 'error', title: 'Error!', text: 'Failed to update invoice.' });
            }
        });
    });
    @endcan

    // ==========================================
    // PAYMENT
    // ==========================================
    @can('sales_payment.create')
    $(document).on('click', '.payment-btn', function() {
        var id = $(this).data('id');
        var form = $('#paymentForm');
        form.attr('action', '/admin/invoices/' + id + '/payment');
        clearErrors($('#paymentFormErrors'));

        // Fetch invoice details to show remaining balance
        $.ajax({
            url: '/admin/invoices/' + id,
            type: 'GET',
            dataType: 'json',
            success: function(r) {
                var invoice = r;
                var remaining = invoice.total - invoice.amount_paid;
                $('#remainingBalance').text(remaining.toFixed(2));
                $('#paymentForm input[name="amount"]').attr('max', remaining);
                $('#paymentForm input[name="amount"]').attr('placeholder', 'Max: ' + remaining.toFixed(2));
            }
        });

        $('#paymentModal').modal('show');
    });

    $('#paymentForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var data = form.serialize();
        var btn = $('#paymentSubmitBtn');
        var errorContainer = $('#paymentFormErrors');
        clearErrors(errorContainer);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Recording...');

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(r) {
                if (r.success) {
                    $('#paymentModal').modal('hide');
                    Swal.fire({ icon: 'success', title: 'Payment Recorded!', text: r.message, timer: 2000 }).then(() => { window.location.reload(); });
                } else {
                    btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i> Record Payment');
                    if (r.errors) showErrors(errorContainer, r.errors);
                    else Swal.fire({ icon: 'error', title: 'Error!', text: r.message || 'Something went wrong.' });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i> Record Payment');
                if (xhr.status === 422) showErrors(errorContainer, xhr.responseJSON.errors);
                else Swal.fire({ icon: 'error', title: 'Error!', text: xhr.responseJSON?.error || 'Failed to record payment.' });
            }
        });
    });
    @endcan

    // ==========================================
    // MARK AS PAID
    // ==========================================
    @can('invoice.mark_paid')
    $(document).on('click', '.mark-paid-btn', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Mark as Paid?',
            text: "This will mark the invoice as paid.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, mark paid!'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Processing...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
                $.ajax({
                    url: '/admin/invoices/' + id + '/mark-paid',
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(r) {
                        if (r.success) {
                            Swal.fire({ icon: 'success', title: 'Marked Paid!', text: r.message, timer: 1500, showConfirmButton: true }).then(() => { location.reload(); });
                        } else {
                            Swal.fire('Error', r.message || 'Failed to mark as paid.', 'error');
                        }
                    },
                    error: function() { Swal.fire('Error', 'Failed to mark as paid.', 'error'); }
                });
            }
        });
    });
    @endcan

    // ==========================================
    // SEND REMINDER
    // ==========================================
    @can('invoice.send_reminder')
    $(document).on('click', '.send-reminder-btn', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Send Reminder?',
            text: "This will send a payment reminder to the customer.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0dcaf0',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, send!'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Sending...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
                $.ajax({
                    url: '/admin/invoices/' + id + '/send-reminder',
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(r) {
                        if (r.success) {
                            Swal.fire({ icon: 'success', title: 'Sent!', text: r.message, timer: 1500, showConfirmButton: true }).then(() => { location.reload(); });
                        } else {
                            Swal.fire('Error', r.message || 'Failed to send reminder.', 'error');
                        }
                    },
                    error: function() { Swal.fire('Error', 'Failed to send reminder.', 'error'); }
                });
            }
        });
    });
    @endcan

    // ==========================================
    // DELETE
    // ==========================================
    @can('invoice.delete')
    $(document).on('click', '.delete-btn', function() {
        var id = $(this).data('id');
        var form = $('#deleteForm');
        form.attr('action', '/admin/invoices/' + id);
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

    // ==========================================
    // SEARCH & FILTER
    // ==========================================
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

    // ==========================================
    // EXPORT
    // ==========================================
    $('#exportBtn').on('click', function() {
        window.location.href = '{{ route("admin.invoices.export") }}';
    });

    // ==========================================
    // MODAL RESET
    // ==========================================
    $('.modal').on('hidden.bs.modal', function() {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
        $(this).find('.alert-danger').addClass('d-none').empty();
        $(this).find('form')[0]?.reset();
        // Reset payment modal specific fields
        if ($(this).attr('id') === 'paymentModal') {
            $('#remainingBalance').text('0.00');
            $('#paymentForm input[name="amount"]').attr('max', null).attr('placeholder', '0.00');
        }
    });
});
</script>
@endpush
@endsection