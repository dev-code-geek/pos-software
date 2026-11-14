@extends('layouts.admin')

@section('title', __('messages.leave_balances'))
@section('page-title', __('messages.leave_balance_management'))

@section('admin-content')
<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-4 col-lg-6">
        <div class="small-box bg-primary">
            <div class="inner"><h3>{{ number_format($stats['total_employees'] ?? 0) }}</h3><p>{{ __('messages.active_employees') }}</p></div>
            <div class="icon"><i class="fas fa-users"></i></div>
        </div>
    </div>
    <div class="col-xl-4 col-lg-6">
        <div class="small-box bg-success">
            <div class="inner"><h3>{{ number_format($stats['total_balances'] ?? 0) }}</h3><p>{{ __('messages.total_balances') }}</p></div>
            <div class="icon"><i class="fas fa-balance-scale"></i></div>
        </div>
    </div>
    <div class="col-xl-4 col-lg-6">
        <div class="small-box bg-info">
            <div class="inner"><h3>{{ $stats['current_year'] ?? date('Y') }}</h3><p>{{ __('messages.current_year') }}</p></div>
            <div class="icon"><i class="fas fa-calendar"></i></div>
        </div>
    </div>
</div>

<!-- Main Card -->
<div class="card">
    <div class="card-header">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <h5 class="card-title mb-0">
                <i class="fas fa-balance-scale text-primary me-2"></i>
                {{ __('messages.leave_balances') }}
                <span class="badge bg-primary ms-1">{{ $balances->count() }}</span>
            </h5>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <div class="input-group input-group-sm" style="width:200px;">
                <input type="text" id="searchInput" class="form-control" placeholder="{{ __('messages.search') }}...">
                <button class="btn btn-secondary" type="button" id="searchBtn"><i class="fas fa-search"></i></button>
            </div>
            <select id="employeeFilter" class="form-select form-select-sm" style="width:180px;">
                <option value="">{{ __('messages.all_employees') }}</option>
                @foreach($employees as $e)
                    <option value="{{ $e->id }}">{{ $e->first_name }} {{ $e->last_name }} ({{ $e->employee_code }})</option>
                @endforeach
            </select>
            <select id="leaveTypeFilter" class="form-select form-select-sm" style="width:150px;">
                <option value="">{{ __('messages.all_leave_types') }}</option>
                @foreach($leaveTypes as $lt)
                    <option value="{{ $lt->id }}">{{ $lt->leave_name }}</option>
                @endforeach
            </select>
            <select id="yearFilter" class="form-select form-select-sm" style="width:100px;">
                <option value="">{{ __('messages.year') }}</option>
                @foreach($years as $y)
                    <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
            @can('leave_balance.initialize')
            <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#initializeModal">
                <i class="fas fa-sync me-1"></i> {{ __('messages.initialize_year') }}
            </button>
            @endcan
            @can('leave_balance.export')
            <a href="#" class="btn btn-success btn-sm">
                <i class="fas fa-file-excel me-1"></i> {{ __('messages.export') }}
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
                        <th>{{ __('messages.employee') }}</th>
                        <th>{{ __('messages.leave_type') }}</th>
                        <th class="text-center">{{ __('messages.year') }}</th>
                        <th class="text-center">{{ __('messages.entitled') }}</th>
                        <th class="text-center">{{ __('messages.used') }}</th>
                        <th class="text-center">{{ __('messages.carried_over') }}</th>
                        <th class="text-center">{{ __('messages.remaining') }}</th>
                        <th style="width:100px;">{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($balances as $balance)
                    <tr data-id="{{ $balance->id }}"
                        data-employee="{{ $balance->employee_id }}"
                        data-leave-type="{{ $balance->leave_type_id }}"
                        data-year="{{ $balance->year }}">
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle bg-primary text-white me-2" style="width:32px;height:32px;font-size:12px;">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <div class="fw-bold small">{{ $balance->employee->first_name ?? 'N/A' }} {{ $balance->employee->last_name ?? '' }}</div>
                                    <small class="text-muted">{{ $balance->employee->employee_code ?? '' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>{{ $balance->leaveType->leave_name ?? 'N/A' }}</td>
                        <td class="text-center"><span class="badge bg-secondary">{{ $balance->year }}</span></td>
                        <td class="text-center"><span class="fw-bold">{{ number_format($balance->entitled_days, 1) }}</span></td>
                        <td class="text-center">{{ number_format($balance->used_days, 1) }}</td>
                        <td class="text-center">{{ number_format($balance->carried_over_days, 1) }}</td>
                        <td class="text-center">
                            <span class="fw-bold {{ $balance->remaining_days > 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($balance->remaining_days, 1) }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                @can('leave_balance.edit')
                                <button class="btn btn-outline-warning btn-sm edit-btn" data-id="{{ $balance->id }}" title="{{ __('messages.edit') }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9">
                            <div class="text-center py-5">
                                <i class="fas fa-balance-scale fa-4x text-muted mb-3 d-block"></i>
                                <h5 class="fw-bold">{{ __('messages.no_leave_balances_found') }}</h5>
                                <p class="text-muted">{{ __('messages.click_initialize_year') }}</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div><span class="text-muted small">{{ __('messages.showing') }} {{ $balances->count() }} {{ __('messages.records') }}</span></div>
    </div>
</div>

<!-- INITIALIZE MODAL -->
<div class="modal fade" id="initializeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:600px;">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title text-white"><i class="fas fa-sync me-2"></i> {{ __('messages.initialize_yearly_balances') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="initializeForm" action="{{ route('admin.leave.balances.initialize') }}" method="POST">
                @csrf
                <div class="modal-body" style="padding:20px 25px;">
                    <div id="initializeFormErrors" class="alert alert-danger d-none"></div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> {{ __('messages.initialize_info') }}
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.year') }} <span class="text-danger">*</span></label>
                            <select name="year" class="form-select" required>
                                @for($i = date('Y'); $i <= date('Y') + 2; $i++)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.select_employees') }}</label>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="selectAllEmployees">
                                <label class="form-check-label" for="selectAllEmployees">{{ __('messages.select_all') }}</label>
                            </div>
                            <div style="max-height:200px; overflow-y:auto; border:1px solid #ddd; padding:10px; border-radius:4px;">
                                @foreach($employees as $e)
                                    <div class="form-check">
                                        <input type="checkbox" name="employee_ids[]" class="form-check-input employee-checkbox" value="{{ $e->id }}" id="emp{{ $e->id }}">
                                        <label class="form-check-label" for="emp{{ $e->id }}">{{ $e->first_name }} {{ $e->last_name }} ({{ $e->employee_code }})</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> {{ __('messages.cancel') }}</button>
                    <button type="submit" class="btn btn-info" id="initializeSubmitBtn"><i class="fas fa-save me-1"></i> {{ __('messages.initialize') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- EDIT BALANCE MODAL -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:560px;">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title text-dark"><i class="fas fa-edit me-2"></i> {{ __('messages.edit_leave_balance') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-body" id="editModalBody" style="padding:20px 25px;">
                    <div id="editFormErrors" class="alert alert-danger d-none"></div>
                    <div class="text-center py-5"><div class="spinner-border text-warning" role="status"><span class="visually-hidden">{{ __('messages.loading') }}...</span></div></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> {{ __('messages.cancel') }}</button>
                    <button type="submit" class="btn btn-warning" id="editSubmitBtn"><i class="fas fa-save me-1"></i> {{ __('messages.update') }}</button>
                </div>
            </form>
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
</style>

@push('scripts')
<script>
window.trans = {
    loading: @json(__('messages.loading')),
    close: @json(__('messages.close')),
    cancel: @json(__('messages.cancel')),
    save: @json(__('messages.save')),
    update: @json(__('messages.update')),
    edit: @json(__('messages.edit')),
    employee: @json(__('messages.employee')),
    leave_type: @json(__('messages.leave_type')),
    year: @json(__('messages.year')),
    entitled_days: @json(__('messages.entitled_days')),
    used_days: @json(__('messages.used_days')),
    carried_over: @json(__('messages.carried_over')),
    // Alerts
    initialized: @json(__('messages.initialized')),
    initializing: @json(__('messages.initializing')),
    initialize: @json(__('messages.initialize')),
    updated: @json(__('messages.updated')),
    updating: @json(__('messages.updating')),
    error_title: @json(__('messages.error')),
    failed_to_initialize: @json(__('messages.failed_to_initialize')),
    failed_to_update_balance: @json(__('messages.failed_to_update_balance')),
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

    $('#selectAllEmployees').on('change', function() {
        $('.employee-checkbox').prop('checked', $(this).prop('checked'));
    });

    // INITIALIZE
    @can('leave_balance.initialize')
    $('#initializeForm').on('submit', function(e) {
        e.preventDefault();
        var btn = $('#initializeSubmitBtn');
        var errorContainer = $('#initializeFormErrors');
        clearErrors(errorContainer);
        btn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm me-1"></span> ${trans.initializing}...`);
        $.ajax({
            url: $(this).attr('action'), type: 'POST', data: $(this).serialize(), dataType: 'json',
            success: function(r) {
                if (r.success) {
                    $('#initializeModal').modal('hide');
                    Swal.fire({ icon: 'success', title: trans.initialized, text: r.message, timer: 3000, showConfirmButton: true }).then(() => location.reload());
                } else {
                    btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.initialize}`);
                    Swal.fire({ icon: 'error', title: trans.error_title, text: r.message || trans.failed_to_initialize });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.initialize}`);
                Swal.fire({ icon: 'error', title: trans.error_title, text: xhr.responseJSON?.message || trans.failed_to_initialize });
            }
        });
    });
    @endcan

    // EDIT
    @can('leave_balance.edit')
    $(document).on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        var body = $('#editModalBody');
        var form = $('#editForm');
        form.attr('action', '/admin/leave/balances/' + id);
        body.html(`<div id="editFormErrors" class="alert alert-danger d-none"></div><div class="text-center py-5"><div class="spinner-border text-warning" role="status"><span class="visually-hidden">${trans.loading}...</span></div></div>`);
        $('#editModal').modal('show');
        $.get('/admin/leave/balances/' + id + '/edit', function(b) {
            body.html(`
                <div id="editFormErrors" class="alert alert-danger d-none"></div>
                <div class="row g-3">
                    <div class="col-12"><strong>${trans.employee}:</strong> ${b.employee ? b.employee.first_name + ' ' + b.employee.last_name : 'N/A'}</div>
                    <div class="col-12"><strong>${trans.leave_type}:</strong> ${b.leave_type ? b.leave_type.leave_name : 'N/A'}</div>
                    <div class="col-12"><strong>${trans.year}:</strong> ${b.year}</div>
                    <div class="col-md-4"><label class="form-label fw-bold">${trans.entitled_days}</label><input type="number" name="entitled_days" class="form-control" step="0.5" min="0" value="${b.entitled_days}" required></div>
                    <div class="col-md-4"><label class="form-label fw-bold">${trans.used_days}</label><input type="number" name="used_days" class="form-control" step="0.5" min="0" value="${b.used_days}" required></div>
                    <div class="col-md-4"><label class="form-label fw-bold">${trans.carried_over}</label><input type="number" name="carried_over_days" class="form-control" step="0.5" min="0" value="${b.carried_over_days}" required></div>
                </div>
            `);
        });
    });
    @endcan

    // UPDATE
    @can('leave_balance.edit')
    $('#editForm').on('submit', function(e) {
        e.preventDefault();
        var btn = $('#editSubmitBtn');
        var errorContainer = $('#editFormErrors');
        clearErrors(errorContainer);
        btn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm me-1"></span> ${trans.updating}...`);
        $.ajax({
            url: $(this).attr('action'), type: 'POST', data: $(this).serialize(), dataType: 'json',
            success: function(r) {
                if (r.success) {
                    $('#editModal').modal('hide');
                    Swal.fire({ icon: 'success', title: trans.updated, text: r.message, timer: 2000, showConfirmButton: true }).then(() => location.reload());
                } else {
                    btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.update}`);
                    if (r.errors) showErrors(errorContainer, r.errors);
                    else Swal.fire({ icon: 'error', title: trans.error_title, text: r.message || trans.failed_to_update_balance });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.update}`);
                if (xhr.status === 422) showErrors(errorContainer, xhr.responseJSON.errors);
                else Swal.fire({ icon: 'error', title: trans.error_title, text: trans.failed_to_update_balance });
            }
        });
    });
    @endcan

    // FILTERS
    $('#searchBtn').on('click', filterTable);
    $('#searchInput').on('keypress', function(e) { if (e.which === 13) filterTable(); });
    $('#employeeFilter, #leaveTypeFilter, #yearFilter').on('change', filterTable);
    function filterTable() {
        var search = $('#searchInput').val().toLowerCase();
        var employee = $('#employeeFilter').val();
        var leaveType = $('#leaveTypeFilter').val();
        var year = $('#yearFilter').val();
        $('#tableBody tr').each(function() {
            var row = $(this);
            var show = true;
            if (search && !row.text().toLowerCase().includes(search)) show = false;
            if (employee && row.data('employee') != employee) show = false;
            if (leaveType && row.data('leave-type') != leaveType) show = false;
            if (year && row.data('year') != year) show = false;
            row.toggle(show);
        });
    }

    $('.modal').on('hidden.bs.modal', function() {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
        $(this).find('.alert-danger').addClass('d-none').empty();
        $(this).find('form')[0]?.reset();
        if ($(this).attr('id') === 'initializeModal') {
            $('#selectAllEmployees').prop('checked', false);
            $('.employee-checkbox').prop('checked', false);
        }
    });
});
</script>
@endpush
@endsection