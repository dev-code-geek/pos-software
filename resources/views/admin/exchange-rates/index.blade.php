@extends('layouts.admin')

@section('title', 'Exchange Rates')
@section('page-title', 'Exchange Rate Management')

@section('admin-content')
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="small-box bg-primary">
            <div class="inner"><h3>{{ number_format($stats['total'] ?? 0) }}</h3><p>Total Rates</p></div>
            <div class="icon"><i class="fas fa-exchange-alt"></i></div>
            <a href="{{ route('admin.exchange-rates.index') }}" class="small-box-footer">View All <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="small-box bg-success">
            <div class="inner"><h3>{{ number_format($stats['today'] ?? 0) }}</h3><p>Today</p></div>
            <div class="icon"><i class="fas fa-calendar-day"></i></div>
            <a href="{{ route('admin.exchange-rates.index', ['date' => date('Y-m-d')]) }}" class="small-box-footer">View Today <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="small-box bg-warning text-dark">
            <div class="inner"><h3>{{ number_format($stats['this_week'] ?? 0) }}</h3><p>This Week</p></div>
            <div class="icon"><i class="fas fa-calendar-week"></i></div>
            <a href="#" class="small-box-footer">View Week <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="small-box bg-info">
            <div class="inner"><h3>{{ number_format($stats['currencies'] ?? 0) }}</h3><p>Currencies</p></div>
            <div class="icon"><i class="fas fa-coins"></i></div>
            <a href="{{ route('admin.currencies.index') }}" class="small-box-footer">Manage Currencies <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <h5 class="card-title mb-0"><i class="fas fa-exchange-alt text-primary me-2"></i>All Exchange Rates <span class="badge bg-primary ms-1">{{ $exchangeRates->total() }}</span></h5>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <div class="input-group input-group-sm" style="width:200px;">
                <input type="text" id="searchInput" class="form-control" placeholder="Search...">
                <button class="btn btn-secondary" type="button" id="searchBtn"><i class="fas fa-search"></i></button>
            </div>
            <input type="date" id="dateFilter" class="form-control form-control-sm" style="width:140px;">
            <select id="currencyFilter" class="form-select form-select-sm" style="width:150px;">
                <option value="">All Currencies</option>
                @foreach($currencies ?? [] as $currency)
                    <option value="{{ $currency->id }}">{{ $currency->code }}</option>
                @endforeach
            </select>
            @can('exchange_rate.create')
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fas fa-plus-circle me-1"></i> Add Rate
            </button>
            @endcan
            @can('exchange_rate.fetch_latest')
            <button class="btn btn-info btn-sm" id="fetchLatestBtn"><i class="fas fa-sync me-1"></i> Fetch Latest</button>
            @endcan
            @can('exchange_rate.export')
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
                        <th>From</th>
                        <th>To</th>
                        <th class="text-end">Rate</th>
                        <th>Date</th>
                        <th>Created By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($exchangeRates as $rate)
                    <tr data-id="{{ $rate->id }}">
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <span class="fw-bold">{{ $rate->fromCurrency->code ?? 'N/A' }}</span>
                            <small class="text-muted d-block">{{ $rate->fromCurrency->name ?? '' }}</small>
                        </td>
                        <td>
                            <span class="fw-bold">{{ $rate->toCurrency->code ?? 'N/A' }}</span>
                            <small class="text-muted d-block">{{ $rate->toCurrency->name ?? '' }}</small>
                        </td>
                        <td class="text-end"><span class="fw-bold text-primary">{{ number_format($rate->rate, 6) }}</span></td>
                        <td>{{ $rate->date ? $rate->date->format('M d, Y') : 'N/A' }}</td>
                        <td>{{ $rate->createdBy->name ?? 'System' }}</td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                @can('exchange_rate.view')
                                <button class="btn btn-outline-primary btn-sm view-btn" data-id="{{ $rate->id }}" title="View"><i class="fas fa-eye"></i></button>
                                @endcan
                                @can('exchange_rate.edit')
                                <button class="btn btn-outline-warning btn-sm edit-btn" data-id="{{ $rate->id }}" title="Edit"><i class="fas fa-edit"></i></button>
                                @endcan
                                @can('exchange_rate.delete')
                                <button class="btn btn-outline-danger btn-sm delete-btn" data-id="{{ $rate->id }}" title="Delete"><i class="fas fa-trash"></i></button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7"><div class="text-center py-5"><i class="fas fa-exchange-alt fa-4x text-muted mb-3 d-block"></i><h5 class="fw-bold">No Exchange Rates Found</h5></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div><span class="text-muted small">Showing {{ $exchangeRates->firstItem() ?? 0 }} to {{ $exchangeRates->lastItem() ?? 0 }} of {{ $exchangeRates->total() }} entries</span></div>
        <div>{{ $exchangeRates->links() }}</div>
    </div>
</div>

<!-- ============================================ -->
<!-- CREATE MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:600px;">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="fas fa-plus-circle me-2"></i> Add Exchange Rate</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="createForm" action="{{ route('admin.exchange-rates.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div id="createFormErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">From Currency <span class="text-danger">*</span></label>
                            <select name="from_currency_id" class="form-select" required>
                                <option value="">Select Currency</option>
                                @foreach($currencies ?? [] as $currency)
                                    <option value="{{ $currency->id }}">{{ $currency->code }} - {{ $currency->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">To Currency <span class="text-danger">*</span></label>
                            <select name="to_currency_id" class="form-select" required>
                                <option value="">Select Currency</option>
                                @foreach($currencies ?? [] as $currency)
                                    <option value="{{ $currency->id }}">{{ $currency->code }} - {{ $currency->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Rate <span class="text-danger">*</span></label>
                            <input type="number" name="rate" class="form-control" step="0.000001" min="0.000001" placeholder="1.000000" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Cancel</button>
                    <button type="submit" class="btn btn-primary" id="createSubmitBtn"><i class="fas fa-save me-1"></i> Save Rate</button>
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
                <h5 class="modal-title text-white"><i class="fas fa-eye me-2"></i> Exchange Rate Details</h5>
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
                <h5 class="modal-title text-dark"><i class="fas fa-edit me-2"></i> Edit Exchange Rate</h5>
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
                    <button type="submit" class="btn btn-warning" id="editSubmitBtn"><i class="fas fa-save me-1"></i> Update Rate</button>
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
                <p class="fw-bold">Are you sure you want to delete this exchange rate?</p>
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
    @can('exchange_rate.create')
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
            success: function(r) {
                if (r.success) {
                    $('#createModal').modal('hide');
                    Swal.fire({ icon: 'success', title: 'Added!', text: r.message, timer: 2000, showConfirmButton: true }).then(() => { location.reload(); });
                } else {
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save Rate');
                    if (r.errors) showErrors(errorContainer, r.errors);
                    else Swal.fire({ icon: 'error', title: 'Error!', text: r.message || 'Something went wrong.' });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save Rate');
                if (xhr.status === 422) showErrors(errorContainer, xhr.responseJSON.errors);
                else Swal.fire({ icon: 'error', title: 'Error!', text: 'Failed to create exchange rate.' });
            }
        });
    });
    @endcan

    // VIEW
    @can('exchange_rate.view')
    $(document).on('click', '.view-btn', function() {
        var id = $(this).data('id');
        var body = $('#viewModalBody');
        body.html(`<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>`);
        $('#viewModal').modal('show');

        $.ajax({
            url: '/admin/exchange-rates/' + id,
            type: 'GET',
            dataType: 'json',
            success: function(r) {
                body.html(`
                    <div class="row g-3">
                        <div class="col-md-6"><label class="text-muted small fw-bold">From Currency</label><p class="fw-bold mb-0">${r.from_currency ? r.from_currency.code + ' - ' + r.from_currency.name : 'N/A'}</p></div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">To Currency</label><p class="fw-bold mb-0">${r.to_currency ? r.to_currency.code + ' - ' + r.to_currency.name : 'N/A'}</p></div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">Exchange Rate</label><p class="fw-bold text-primary mb-0">${parseFloat(r.rate).toFixed(6)}</p></div>
                        <div class="col-md-6"><label class="text-muted small fw-bold">Date</label><p class="mb-0">${new Date(r.date).toLocaleDateString()}</p></div>
                        <div class="col-12"><label class="text-muted small fw-bold">Created By</label><p class="mb-0">${r.created_by ? r.created_by.name : 'System'}</p></div>
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
    @can('exchange_rate.edit')
    $(document).on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        var body = $('#editModalBody');
        var form = $('#editForm');

        form.attr('action', '/admin/exchange-rates/' + id);
        body.html(`<div id="editFormErrors" class="alert alert-danger d-none"></div><div class="text-center py-5"><div class="spinner-border text-warning" role="status"><span class="visually-hidden">Loading...</span></div></div>`);
        $('#editModal').modal('show');

        $.ajax({
            url: '/admin/exchange-rates/' + id + '/edit',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (!response.success) {
                    body.html(`<div id="editFormErrors" class="alert alert-danger">${response.error || 'Failed to load'}</div>`);
                    return;
                }
                var rate = response.exchange_rate;
                var currencies = response.currencies || [];

                body.html(`
                    <div id="editFormErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">From Currency</label>
                            <select name="from_currency_id" class="form-select" disabled>
                                <option value="${rate.from_currency_id}">${rate.from_currency ? rate.from_currency.code + ' - ' + rate.from_currency.name : 'N/A'}</option>
                            </select>
                            <small class="text-muted">From currency cannot be changed</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">To Currency</label>
                            <select name="to_currency_id" class="form-select" disabled>
                                <option value="${rate.to_currency_id}">${rate.to_currency ? rate.to_currency.code + ' - ' + rate.to_currency.name : 'N/A'}</option>
                            </select>
                            <small class="text-muted">To currency cannot be changed</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Rate <span class="text-danger">*</span></label>
                            <input type="number" name="rate" class="form-control" step="0.000001" min="0.000001" value="${rate.rate}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control" value="${rate.date ? new Date(rate.date).toISOString().split('T')[0] : ''}" required>
                        </div>
                    </div>
                `);
            },
            error: function(xhr) {
                body.html(`<div id="editFormErrors" class="alert alert-danger">Failed to load exchange rate data.</div>`);
            }
        });
    });
    @endcan

    // UPDATE
    @can('exchange_rate.edit')
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
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Update Rate');
                    if (r.errors) showErrors(errorContainer, r.errors);
                    else Swal.fire({ icon: 'error', title: 'Error!', text: r.message || 'Something went wrong.' });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Update Rate');
                if (xhr.status === 422) showErrors(errorContainer, xhr.responseJSON.errors);
                else Swal.fire({ icon: 'error', title: 'Error!', text: 'Failed to update exchange rate.' });
            }
        });
    });
    @endcan

    // DELETE
    @can('exchange_rate.delete')
    $(document).on('click', '.delete-btn', function() {
        var id = $(this).data('id');
        var form = $('#deleteForm');
        form.attr('action', '/admin/exchange-rates/' + id);
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

    // FETCH LATEST
    @can('exchange_rate.fetch_latest')
    $('#fetchLatestBtn').on('click', function() {
        var btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Fetching...');
        $.ajax({
            url: '{{ route("admin.exchange-rates.fetch-latest") }}',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            dataType: 'json',
            success: function(r) {
                if (r.success) {
                    Swal.fire({ icon: 'success', title: 'Success', text: r.message, timer: 2000, showConfirmButton: true }).then(() => { location.reload(); });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error!', text: r.message || 'Failed to fetch rates.' });
                }
                btn.prop('disabled', false).html('<i class="fas fa-sync me-1"></i> Fetch Latest');
            },
            error: function() {
                btn.prop('disabled', false).html('<i class="fas fa-sync me-1"></i> Fetch Latest');
                Swal.fire({ icon: 'error', title: 'Error!', text: 'Failed to fetch latest rates.' });
            }
        });
    });
    @endcan

    // SEARCH & FILTER
    $('#searchBtn').on('click', filterTable);
    $('#searchInput').on('keypress', function(e) { if (e.which === 13) filterTable(); });
    $('#dateFilter, #currencyFilter').on('change', filterTable);

    function filterTable() {
        var search = $('#searchInput').val().toLowerCase();
        var date = $('#dateFilter').val();
        var currency = $('#currencyFilter').val();

        $('#tableBody tr').each(function() {
            var row = $(this);
            var text = row.text().toLowerCase();
            var rowDate = row.find('td:nth-child(5)').text().trim();
            var show = true;
            if (search && !text.includes(search)) show = false;
            if (date && !rowDate.includes(date)) show = false;
            if (currency) {
                var fromCode = row.find('td:nth-child(2) .fw-bold').text().trim();
                var toCode = row.find('td:nth-child(3) .fw-bold').text().trim();
                var currencyName = row.find('td:nth-child(2) .text-muted').text().trim() || row.find('td:nth-child(3) .text-muted').text().trim();
                if (fromCode !== currency && toCode !== currency && !currencyName.includes(currency)) show = false;
            }
            row.toggle(show);
        });
    }

    $('#exportBtn').on('click', function() { window.location.href = '{{ route("admin.exchange-rates.export") }}'; });

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