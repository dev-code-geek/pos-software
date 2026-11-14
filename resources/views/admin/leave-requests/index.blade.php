@extends('layouts.admin')

@section('title', __('messages.leave_requests'))
@section('page-title', __('messages.leave_request_management'))

@section('admin-content')
<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-primary">
            <div class="inner"><h3>{{ number_format($stats['total'] ?? 0) }}</h3><p>{{ __('messages.total') }}</p></div>
            <div class="icon"><i class="fas fa-calendar-check"></i></div>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-warning text-dark">
            <div class="inner"><h3>{{ number_format($stats['pending'] ?? 0) }}</h3><p>{{ __('messages.pending') }}</p></div>
            <div class="icon"><i class="fas fa-clock"></i></div>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-success">
            <div class="inner"><h3>{{ number_format($stats['approved'] ?? 0) }}</h3><p>{{ __('messages.approved') }}</p></div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-danger">
            <div class="inner"><h3>{{ number_format($stats['rejected'] ?? 0) }}</h3><p>{{ __('messages.rejected') }}</p></div>
            <div class="icon"><i class="fas fa-times-circle"></i></div>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-secondary">
            <div class="inner"><h3>{{ number_format($stats['cancelled'] ?? 0) }}</h3><p>{{ __('messages.cancelled') }}</p></div>
            <div class="icon"><i class="fas fa-ban"></i></div>
        </div>
    </div>
</div>

<!-- Main Card -->
<div class="card">
    <div class="card-header">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <h5 class="card-title mb-0"><i class="fas fa-calendar-check text-primary me-2"></i>{{ __('messages.all_leave_requests') }} <span class="badge bg-primary ms-1">{{ $leaveRequests->total() }}</span></h5>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <div class="input-group input-group-sm" style="width:200px;">
                <input type="text" id="searchInput" class="form-control" placeholder="{{ __('messages.search') }}..." value="{{ request('search') }}">
                <button class="btn btn-secondary" type="button" id="searchBtn"><i class="fas fa-search"></i></button>
            </div>
            <select id="statusFilter" class="form-select form-select-sm" style="width:120px;">
                <option value="">{{ __('messages.all_status') }}</option>
                @foreach($statuses as $s)
                    <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ __('messages.' . strtolower($s)) }}</option>
                @endforeach
            </select>
            <select id="leaveTypeFilter" class="form-select form-select-sm" style="width:150px;">
                <option value="">{{ __('messages.all_types') }}</option>
                @foreach($leaveTypes as $lt)
                    <option value="{{ $lt->id }}" {{ (string) request('leave_type_id') === (string) $lt->id ? 'selected' : '' }}>{{ $lt->leave_name }}</option>
                @endforeach
            </select>
            <input type="date" id="fromDate" class="form-control form-control-sm" style="width:130px;" placeholder="{{ __('messages.from') }}" value="{{ request('from_date') }}">
            <input type="date" id="toDate" class="form-control form-control-sm" style="width:130px;" placeholder="{{ __('messages.to') }}" value="{{ request('to_date') }}">
            @if(request()->hasAny(['search', 'status', 'leave_type_id', 'from_date', 'to_date']))
            <a href="{{ route('admin.leave.requests.index') }}" class="btn btn-outline-secondary btn-sm" title="{{ __('messages.clear_filters') }}"><i class="fas fa-times"></i></a>
            @endif
            @can('leave_request.create')
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fas fa-plus-circle me-1"></i> {{ __('messages.new_request') }}
            </button>
            @endcan
            @can('leave_request.bulk_approve')
            <button class="btn btn-success btn-sm" id="bulkApproveBtn"><i class="fas fa-check me-1"></i> {{ __('messages.bulk_approve') }}</button>
            @endcan
            @can('leave_request.bulk_reject')
            <button class="btn btn-danger btn-sm" id="bulkRejectBtn"><i class="fas fa-times me-1"></i> {{ __('messages.bulk_reject') }}</button>
            @endcan
            @can('leave_request.bulk_delete')
            <button class="btn btn-outline-danger btn-sm" id="bulkDeleteBtn"><i class="fas fa-trash me-1"></i> {{ __('messages.bulk_delete') }}</button>
            @endcan
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:35px;"><input type="checkbox" id="selectAll"></th>
                        <th style="width:35px;">#</th>
                        <th>{{ __('messages.employee') }}</th>
                        <th>{{ __('messages.leave_type') }}</th>
                        <th>{{ __('messages.from') }}</th>
                        <th>{{ __('messages.to') }}</th>
                        <th class="text-center">{{ __('messages.days') }}</th>
                        <th>{{ __('messages.status') }}</th>
                        <th style="width:220px;">{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($leaveRequests as $request)
                    <tr data-id="{{ $request->id }}" data-status="{{ $request->approval_status }}">
                        <td><input type="checkbox" class="row-checkbox" value="{{ $request->id }}"></td>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle bg-primary text-white me-2" style="width:32px;height:32px;font-size:12px;">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <div class="fw-bold small">{{ $request->employee->first_name ?? 'N/A' }} {{ $request->employee->last_name ?? '' }}</div>
                                    <small class="text-muted">{{ $request->employee->employee_code ?? '' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>{{ $request->leaveType->leave_name ?? 'N/A' }}</td>
                        <td>{{ $request->start_date ? $request->start_date->format('M d, Y') : 'N/A' }}</td>
                        <td>{{ $request->end_date ? $request->end_date->format('M d, Y') : 'N/A' }}</td>
                        <td class="text-center"><span class="fw-bold">{{ $request->total_days }}</span></td>
                        <td>
                            @php
                                $statusColors = ['Pending' => 'warning text-dark', 'Approved' => 'success', 'Rejected' => 'danger', 'Cancelled' => 'secondary'];
                                $statusKey = 'status_' . strtolower($request->approval_status);
                            @endphp
                            <span class="badge bg-{{ $statusColors[$request->approval_status] ?? 'secondary' }}">
                                {{ __('messages.' . $statusKey) }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                @can('leave_request.view')
                                <button class="btn btn-outline-primary btn-sm view-btn" data-id="{{ $request->id }}" title="{{ __('messages.view') }}"><i class="fas fa-eye"></i></button>
                                @endcan
                                @can('leave_request.edit')
                                @if($request->approval_status == 'Pending')
                                <button class="btn btn-outline-warning btn-sm edit-btn" data-id="{{ $request->id }}" title="{{ __('messages.edit') }}"><i class="fas fa-edit"></i></button>
                                @endif
                                @endcan
                                @can('leave_request.approve')
                                @if($request->approval_status == 'Pending')
                                <button class="btn btn-outline-success btn-sm approve-btn" data-id="{{ $request->id }}" title="{{ __('messages.approve') }}"><i class="fas fa-check"></i></button>
                                @endif
                                @endcan
                                @can('leave_request.reject')
                                @if($request->approval_status == 'Pending')
                                <button class="btn btn-outline-danger btn-sm reject-btn" data-id="{{ $request->id }}" title="{{ __('messages.reject') }}"><i class="fas fa-times"></i></button>
                                @endif
                                @endcan
                                @can('leave_request.cancel')
                                @if($request->approval_status == 'Pending')
                                <button class="btn btn-outline-secondary btn-sm cancel-btn" data-id="{{ $request->id }}" title="{{ __('messages.cancel') }}"><i class="fas fa-ban"></i></button>
                                @endif
                                @endcan
                                @can('leave_request.delete')
                                @if(in_array($request->approval_status, ['Pending', 'Cancelled']))
                                <button class="btn btn-outline-danger btn-sm delete-btn" data-id="{{ $request->id }}" title="{{ __('messages.delete') }}"><i class="fas fa-trash"></i></button>
                                @endif
                                @endcan
                                @if($request->medical_certificate_path)
                                <a href="{{ route('admin.leave.requests.certificate', $request->id) }}" class="btn btn-outline-info btn-sm" title="{{ __('messages.download_certificate') }}" target="_blank"><i class="fas fa-file-medical"></i></a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9"><div class="text-center py-5"><i class="fas fa-calendar-check fa-4x text-muted mb-3 d-block"></i><h5 class="fw-bold">{{ __('messages.no_leave_requests_found') }}</h5></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div><span class="text-muted small">{{ __('messages.showing') }} {{ $leaveRequests->firstItem() ?? 0 }} {{ __('messages.to') }} {{ $leaveRequests->lastItem() ?? 0 }} {{ __('messages.of') }} {{ $leaveRequests->total() }} {{ __('messages.entries') }}</span></div>
        <div>{{ $leaveRequests->links() }}</div>
    </div>
</div>

<!-- CREATE MODAL -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:600px;">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="fas fa-plus-circle me-2"></i> {{ __('messages.new_leave_request') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="createForm" action="{{ route('admin.leave.requests.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body" style="max-height:70vh; overflow-y:auto; padding:20px 25px;">
                    <div id="createFormErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">{{ __('messages.employee') }} <span class="text-danger">*</span></label>
                            <select name="employee_id" class="form-select" required>
                                <option value="">{{ __('messages.select_employee') }}</option>
                                @foreach($employees as $e)
                                    <option value="{{ $e->id }}">{{ $e->first_name }} {{ $e->last_name }} ({{ $e->employee_code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">{{ __('messages.leave_type') }} <span class="text-danger">*</span></label>
                            <select name="leave_type_id" class="form-select" required>
                                <option value="">{{ __('messages.select_leave_type') }}</option>
                                @foreach($leaveTypes as $lt)
                                    <option value="{{ $lt->id }}">{{ $lt->leave_name }} ({{ $lt->days_per_year }} {{ __('messages.days') }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.start_date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.end_date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">{{ __('messages.reason') }} <span class="text-danger">*</span></label>
                            <textarea name="reason" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.contact_during_leave') }}</label>
                            <input type="text" name="contact_during_leave" class="form-control" placeholder="{{ __('messages.phone_or_email') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.medical_certificate') }}</label>
                            <input type="file" name="medical_certificate" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> {{ __('messages.cancel') }}</button>
                    <button type="submit" class="btn btn-primary" id="createSubmitBtn"><i class="fas fa-save me-1"></i> {{ __('messages.submit_request') }}</button>
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
                <h5 class="modal-title text-white"><i class="fas fa-eye me-2"></i> {{ __('messages.leave_request_details') }}</h5>
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
                <h5 class="modal-title text-dark"><i class="fas fa-edit me-2"></i> {{ __('messages.edit_leave_request') }}</h5>
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
                    <button type="submit" class="btn btn-warning" id="editSubmitBtn"><i class="fas fa-save me-1"></i> {{ __('messages.update') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- REJECT MODAL -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:560px;">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white"><i class="fas fa-times me-2"></i> {{ __('messages.reject_request') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-body" style="padding:20px 25px;">
                    <div id="rejectFormErrors" class="alert alert-danger d-none"></div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">{{ __('messages.rejection_reason') }} <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" class="form-control" rows="3" required placeholder="{{ __('messages.provide_reason_rejection') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> {{ __('messages.cancel') }}</button>
                    <button type="submit" class="btn btn-danger" id="rejectSubmitBtn"><i class="fas fa-save me-1"></i> {{ __('messages.reject') }}</button>
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
                <p class="fw-bold">{{ __('messages.are_you_sure_delete_request') }}</p>
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
    delete: @json(__('messages.delete')),
    edit: @json(__('messages.edit')),
    view: @json(__('messages.view')),
    employee: @json(__('messages.employee')),
    leave_type: @json(__('messages.leave_type')),
    start_date: @json(__('messages.start_date')),
    end_date: @json(__('messages.end_date')),
    total_days: @json(__('messages.total_days')),
    status: @json(__('messages.status')),
    reason: @json(__('messages.reason')),
    contact: @json(__('messages.contact')),
    rejection_reason: @json(__('messages.rejection_reason')),
    requested_by: @json(__('messages.requested_by')),
    requested_at: @json(__('messages.requested_at')),
    approved_by: @json(__('messages.approved_by')),
    approved_at: @json(__('messages.approved_at')),
    download_certificate: @json(__('messages.download_certificate')),
    // Status values
    status_pending: @json(__('messages.status_pending')),
    status_approved: @json(__('messages.status_approved')),
    status_rejected: @json(__('messages.status_rejected')),
    status_cancelled: @json(__('messages.status_cancelled')),
    // Alerts
    submitted: @json(__('messages.submitted')),
    submitting: @json(__('messages.submitting')),
    updated: @json(__('messages.updated')),
    updating: @json(__('messages.updating')),
    approved_alert: @json(__('messages.approved_alert')),
    rejected_alert: @json(__('messages.rejected_alert')),
    cancelled_alert: @json(__('messages.cancelled_alert')),
    deleted: @json(__('messages.deleted')),
    deleting: @json(__('messages.deleting')),
    processing: @json(__('messages.processing')),
    done: @json(__('messages.done')),
    error_title: @json(__('messages.error')),
    validation_error: @json(__('messages.validation_error')),
    session_expired: @json(__('messages.session_expired')),
    session_expired_msg: @json(__('messages.session_expired_msg')),
    permission_denied: @json(__('messages.permission_denied')),
    permission_denied_msg: @json(__('messages.permission_denied_msg')),
    not_found: @json(__('messages.not_found')),
    not_found_msg: @json(__('messages.not_found_msg')),
    something_went_wrong: @json(__('messages.something_went_wrong')),
    failed_to_load_details: @json(__('messages.failed_to_load_details')),
    failed_to_load: @json(__('messages.failed_to_load')),
    // Confirmations
    approve_request_q: @json(__('messages.approve_request_q')),
    approve_request_warning: @json(__('messages.approve_request_warning')),
    yes_approve: @json(__('messages.yes_approve')),
    reject_request_q: @json(__('messages.reject_request_q')),
    reject_request_warning: @json(__('messages.reject_request_warning')),
    yes_reject: @json(__('messages.yes_reject')),
    cancel_request_q: @json(__('messages.cancel_request_q')),
    cancel_request_warning: @json(__('messages.cancel_request_warning')),
    yes_cancel: @json(__('messages.yes_cancel')),
    bulk_approve_q: @json(__('messages.bulk_approve_q')),
    bulk_approve_warning: @json(__('messages.bulk_approve_warning')),
    yes_approve_all: @json(__('messages.yes_approve_all')),
    bulk_reject_q: @json(__('messages.bulk_reject_q')),
    bulk_reject_warning: @json(__('messages.bulk_reject_warning')),
    yes_reject_all: @json(__('messages.yes_reject_all')),
    bulk_delete_q: @json(__('messages.bulk_delete_q')),
    bulk_delete_warning: @json(__('messages.bulk_delete_warning')),
    yes_delete_all: @json(__('messages.yes_delete_all')),
    select_at_least_one: @json(__('messages.select_at_least_one')),
    submit_request: @json(__('messages.submit_request')),
    update: @json(__('messages.update')),
    reject: @json(__('messages.reject')),
    select_employee: @json(__('messages.select_employee')),
    select_leave_type: @json(__('messages.select_leave_type')),
    info: @json(__('messages.info')),
};

$(document).ready(function() {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    var baseUrl        = "{{ route('admin.leave.requests.index') }}";
    var bulkApproveUrl = "{{ route('admin.leave.requests.bulk-approve') }}";
    var bulkRejectUrl  = "{{ route('admin.leave.requests.bulk-reject') }}";
    var bulkDeleteUrl  = "{{ route('admin.leave.requests.bulk-delete') }}";

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return $('<div>').text(str).html();
    }
    function toDateInputValue(d) { return d ? new Date(d).toISOString().slice(0, 10) : ''; }

    function showErrors(container, errors) {
        container.removeClass('d-none');
        var html = '<ul class="mb-0">';
        $.each(errors, function(key, messages) {
            $.each(messages, function(index, msg) { html += '<li>' + escapeHtml(msg) + '</li>'; });
        });
        html += '</ul>';
        container.html(html);
        container[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    function clearErrors(container) { container.addClass('d-none').empty(); }

    function handleAjaxFailure(xhr, errorContainer) {
        var response = xhr.responseJSON;
        if (xhr.status === 422 && response && response.errors) {
            if (errorContainer && errorContainer.length) showErrors(errorContainer, response.errors);
            else Swal.fire(trans.validation_error, Object.values(response.errors).flat().join('\n'), 'error');
        } else if (response && response.message) {
            Swal.fire(trans.error_title, response.message, 'error');
        } else if (xhr.status === 419) {
            Swal.fire(trans.session_expired, trans.session_expired_msg, 'warning');
        } else if (xhr.status === 403) {
            Swal.fire(trans.permission_denied, trans.permission_denied_msg, 'error');
        } else if (xhr.status === 404) {
            Swal.fire(trans.not_found, trans.not_found_msg, 'error').then(() => location.reload());
        } else {
            Swal.fire(trans.error_title, trans.something_went_wrong, 'error');
        }
    }

    // SELECT ALL
    $('#selectAll').on('change', function() { $('.row-checkbox').prop('checked', $(this).prop('checked')); });
    $(document).on('change', '.row-checkbox', function() {
        $('#selectAll').prop('checked', $('.row-checkbox').length === $('.row-checkbox:checked').length);
    });

    // VIEW
    @can('leave_request.view')
    $(document).on('click', '.view-btn', function() {
        var id = $(this).data('id');
        var body = $('#viewModalBody');
        body.html(`<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>`);
        $('#viewModal').modal('show');
        $.get(baseUrl + '/' + id).done(function(req) {
            var statusClass = req.approval_status == 'Pending' ? 'warning text-dark' : (req.approval_status == 'Approved' ? 'success' : (req.approval_status == 'Rejected' ? 'danger' : 'secondary'));
            var statusLabel = trans['status_' + req.approval_status.toLowerCase()] || req.approval_status;
            var html = `
                <div class="row g-3">
                    <div class="col-md-6"><label class="text-muted small fw-bold">${trans.employee}</label><p class="fw-bold mb-0">${req.employee ? escapeHtml(req.employee.first_name + ' ' + req.employee.last_name) : 'N/A'}</p></div>
                    <div class="col-md-6"><label class="text-muted small fw-bold">${trans.leave_type}</label><p class="mb-0">${req.leave_type ? escapeHtml(req.leave_type.leave_name) : 'N/A'}</p></div>
                    <div class="col-md-6"><label class="text-muted small fw-bold">${trans.start_date}</label><p class="mb-0">${new Date(req.start_date).toLocaleDateString()}</p></div>
                    <div class="col-md-6"><label class="text-muted small fw-bold">${trans.end_date}</label><p class="mb-0">${new Date(req.end_date).toLocaleDateString()}</p></div>
                    <div class="col-md-6"><label class="text-muted small fw-bold">${trans.total_days}</label><p class="fw-bold mb-0">${req.total_days}</p></div>
                    <div class="col-md-6"><label class="text-muted small fw-bold">${trans.status}</label><p class="mb-0"><span class="badge bg-${statusClass}">${statusLabel}</span></p></div>
                    <div class="col-12"><label class="text-muted small fw-bold">${trans.reason}</label><p class="mb-0">${escapeHtml(req.reason)}</p></div>
                    ${req.contact_during_leave ? `<div class="col-12"><label class="text-muted small fw-bold">${trans.contact}</label><p class="mb-0">${escapeHtml(req.contact_during_leave)}</p></div>` : ''}
                    ${req.rejection_reason ? `<div class="col-12"><label class="text-muted small fw-bold">${trans.rejection_reason}</label><p class="text-danger mb-0">${escapeHtml(req.rejection_reason)}</p></div>` : ''}
                    ${req.medical_certificate_path ? `<div class="col-12"><a href="${baseUrl}/${req.id}/certificate" class="btn btn-sm btn-info" target="_blank"><i class="fas fa-file-medical me-1"></i> ${trans.download_certificate}</a></div>` : ''}
                    <div class="col-md-6"><label class="text-muted small fw-bold">${trans.requested_by}</label><p class="mb-0">${req.requester ? escapeHtml(req.requester.name) : 'N/A'}</p></div>
                    <div class="col-md-6"><label class="text-muted small fw-bold">${trans.requested_at}</label><p class="mb-0">${new Date(req.requested_at).toLocaleString()}</p></div>
                    ${req.approved_at ? `<div class="col-md-6"><label class="text-muted small fw-bold">${trans.approved_by}</label><p class="mb-0">${req.approver ? escapeHtml(req.approver.name) : 'N/A'}</p></div><div class="col-md-6"><label class="text-muted small fw-bold">${trans.approved_at}</label><p class="mb-0">${new Date(req.approved_at).toLocaleString()}</p></div>` : ''}
                </div>`;
            body.html(html);
        }).fail(function(xhr) {
            var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : trans.failed_to_load_details;
            body.html('<div class="text-center py-5 text-danger"><i class="fas fa-exclamation-triangle fa-2x mb-2 d-block"></i>' + escapeHtml(msg) + '</div>');
        });
    });
    @endcan

    // EDIT
    @can('leave_request.edit')
    $(document).on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        var body = $('#editModalBody');
        var form = $('#editForm');
        form.attr('action', baseUrl + '/' + id);
        body.html(`<div id="editFormErrors" class="alert alert-danger d-none"></div><div class="text-center py-5"><div class="spinner-border text-warning" role="status"></div></div>`);
        $('#editModal').modal('show');
        $.get(baseUrl + '/' + id + '/edit').done(function(r) {
            var req = r.leave_request;
            var empOptions = '';
            r.employees.forEach(function(e) { empOptions += `<option value="${e.id}" ${req.employee_id == e.id ? 'selected' : ''}>${escapeHtml(e.first_name + ' ' + e.last_name)}</option>`; });
            var ltOptions = '';
            r.leave_types.forEach(function(lt) { ltOptions += `<option value="${lt.id}" ${req.leave_type_id == lt.id ? 'selected' : ''}>${escapeHtml(lt.leave_name)}</option>`; });
            body.html(`
                <div id="editFormErrors" class="alert alert-danger d-none"></div>
                <div class="row g-3">
                    <div class="col-12"><label class="form-label fw-bold">${trans.employee} <span class="text-danger">*</span></label><select name="employee_id" class="form-select" required>${empOptions}</select></div>
                    <div class="col-12"><label class="form-label fw-bold">${trans.leave_type} <span class="text-danger">*</span></label><select name="leave_type_id" class="form-select" required>${ltOptions}</select></div>
                    <div class="col-md-6"><label class="form-label fw-bold">${trans.start_date} <span class="text-danger">*</span></label><input type="date" name="start_date" class="form-control" value="${toDateInputValue(req.start_date)}" required></div>
                    <div class="col-md-6"><label class="form-label fw-bold">${trans.end_date} <span class="text-danger">*</span></label><input type="date" name="end_date" class="form-control" value="${toDateInputValue(req.end_date)}" required></div>
                    <div class="col-12"><label class="form-label fw-bold">${trans.reason} <span class="text-danger">*</span></label><textarea name="reason" class="form-control" rows="3" required>${escapeHtml(req.reason)}</textarea></div>
                    <div class="col-md-6"><label class="form-label fw-bold">${trans.contact}</label><input type="text" name="contact_during_leave" class="form-control" value="${escapeHtml(req.contact_during_leave || '')}"></div>
                    <div class="col-md-6"><label class="form-label fw-bold">${trans.download_certificate}</label><input type="file" name="medical_certificate" class="form-control" accept=".pdf,.jpg,.jpeg,.png"></div>
                </div>`);
        }).fail(function(xhr) {
            var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : trans.failed_to_load;
            body.html('<div class="text-center py-5 text-danger"><i class="fas fa-exclamation-triangle fa-2x mb-2 d-block"></i>' + escapeHtml(msg) + '</div>');
        });
    });
    @endcan

    // CREATE
    @can('leave_request.create')
    $('#createForm').on('submit', function(e) {
        e.preventDefault();
        var form = this; var data = new FormData(form);
        var btn = $('#createSubmitBtn'); var errorContainer = $('#createFormErrors');
        clearErrors(errorContainer);
        btn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm me-1"></span> ${trans.submitting}...`);
        $.ajax({ url: $(form).attr('action'), type: 'POST', data: data, processData: false, contentType: false, dataType: 'json' })
            .done(function(r) { $('#createModal').modal('hide'); Swal.fire({ icon: 'success', title: trans.submitted, text: r.message, timer: 2000 }).then(() => location.reload()); })
            .fail(function(xhr) { handleAjaxFailure(xhr, errorContainer); })
            .always(function() { btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.submit_request}`); });
    });
    @endcan

    // UPDATE
    @can('leave_request.edit')
    $('#editForm').on('submit', function(e) {
        e.preventDefault();
        var form = this; var data = new FormData(form);
        var btn = $('#editSubmitBtn'); var errorContainer = $('#editFormErrors');
        clearErrors(errorContainer);
        btn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm me-1"></span> ${trans.updating}...`);
        $.ajax({ url: $(form).attr('action'), type: 'POST', data: data, processData: false, contentType: false, dataType: 'json' })
            .done(function(r) { $('#editModal').modal('hide'); Swal.fire({ icon: 'success', title: trans.updated, text: r.message, timer: 2000 }).then(() => location.reload()); })
            .fail(function(xhr) { handleAjaxFailure(xhr, errorContainer); })
            .always(function() { btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.update}`); });
    });
    @endcan

    // APPROVE
    @can('leave_request.approve')
    $(document).on('click', '.approve-btn', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: trans.approve_request_q, text: trans.approve_request_warning, icon: 'question',
            showCancelButton: true, confirmButtonColor: '#28a745', cancelButtonColor: '#6c757d',
            confirmButtonText: trans.yes_approve, cancelButtonText: trans.cancel
        }).then((result) => {
            if (!result.isConfirmed) return;
            Swal.fire({ title: trans.processing, allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            $.post(baseUrl + '/' + id + '/approve')
                .done(function(r) { Swal.fire({ icon: 'success', title: trans.approved_alert, text: r.message, timer: 1500 }).then(() => location.reload()); })
                .fail(function(xhr) { handleAjaxFailure(xhr, $()); });
        });
    });
    @endcan

    // REJECT
    @can('leave_request.reject')
    $(document).on('click', '.reject-btn', function() {
        var id = $(this).data('id');
        $('#rejectForm').attr('action', baseUrl + '/' + id + '/reject');
        clearErrors($('#rejectFormErrors'));
        $('#rejectForm')[0].reset();
        $('#rejectModal').modal('show');
    });
    $('#rejectForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = $('#rejectSubmitBtn'); var errorContainer = $('#rejectFormErrors');
        clearErrors(errorContainer);
        btn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm me-1"></span> ${trans.rejecting || trans.processing}...`);
        $.post(form.attr('action'), form.serialize())
            .done(function(r) { $('#rejectModal').modal('hide'); Swal.fire({ icon: 'success', title: trans.rejected_alert, text: r.message, timer: 1500 }).then(() => location.reload()); })
            .fail(function(xhr) { handleAjaxFailure(xhr, errorContainer); })
            .always(function() { btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.reject}`); });
    });
    @endcan

    // CANCEL
    @can('leave_request.cancel')
    $(document).on('click', '.cancel-btn', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: trans.cancel_request_q, text: trans.cancel_request_warning, icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#6c757d', cancelButtonColor: '#d33',
            confirmButtonText: trans.yes_cancel, cancelButtonText: trans.no
        }).then((result) => {
            if (!result.isConfirmed) return;
            $.post(baseUrl + '/' + id + '/cancel')
                .done(function(r) { Swal.fire({ icon: 'success', title: trans.cancelled_alert, text: r.message, timer: 1500 }).then(() => location.reload()); })
                .fail(function(xhr) { handleAjaxFailure(xhr, $()); });
        });
    });
    @endcan

    // BULK APPROVE
    @can('leave_request.bulk_approve')
    $('#bulkApproveBtn').on('click', function() {
        var selected = $('.row-checkbox:checked');
        if (selected.length === 0) { Swal.fire(trans.info, trans.select_at_least_one, 'info'); return; }
        var ids = selected.map(function() { return $(this).val(); }).get();
        Swal.fire({
            title: trans.bulk_approve_q,
            text: trans.bulk_approve_warning.replace(':count', ids.length),
            icon: 'question',
            showCancelButton: true, confirmButtonColor: '#28a745', cancelButtonColor: '#6c757d',
            confirmButtonText: trans.yes_approve_all, cancelButtonText: trans.cancel
        }).then((result) => {
            if (!result.isConfirmed) return;
            Swal.fire({ title: trans.processing, allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            $.post(bulkApproveUrl, { ids: ids })
                .done(function(r) { Swal.fire({ icon: 'success', title: trans.done, text: r.message, timer: 2500 }).then(() => location.reload()); })
                .fail(function(xhr) { handleAjaxFailure(xhr, $()); });
        });
    });
    @endcan

    // BULK REJECT
    @can('leave_request.bulk_reject')
    $('#bulkRejectBtn').on('click', function() {
        var selected = $('.row-checkbox:checked');
        if (selected.length === 0) { Swal.fire(trans.info, trans.select_at_least_one, 'info'); return; }
        Swal.fire({
            title: trans.bulk_reject_q,
            text: trans.bulk_reject_warning.replace(':count', selected.length),
            icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#6c757d',
            confirmButtonText: trans.yes_reject_all, cancelButtonText: trans.cancel,
            input: 'textarea',
            inputLabel: trans.rejection_reason,
            inputPlaceholder: trans.provide_reason_rejection,
            inputValidator: (value) => !value && trans.rejection_reason
        }).then((result) => {
            if (!result.isConfirmed) return;
            var ids = selected.map(function() { return $(this).val(); }).get();
            Swal.fire({ title: trans.processing, allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            $.post(bulkRejectUrl, { ids: ids, rejection_reason: result.value })
                .done(function(r) { Swal.fire({ icon: 'success', title: trans.done, text: r.message, timer: 2500 }).then(() => location.reload()); })
                .fail(function(xhr) { handleAjaxFailure(xhr, $()); });
        });
    });
    @endcan

    // BULK DELETE
    @can('leave_request.bulk_delete')
    $('#bulkDeleteBtn').on('click', function() {
        var selected = $('.row-checkbox:checked');
        if (selected.length === 0) { Swal.fire(trans.info, trans.select_at_least_one, 'info'); return; }
        var ids = selected.map(function() { return $(this).val(); }).get();
        Swal.fire({
            title: trans.bulk_delete_q,
            text: trans.bulk_delete_warning.replace(':count', ids.length),
            icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#6c757d',
            confirmButtonText: trans.yes_delete_all, cancelButtonText: trans.cancel
        }).then((result) => {
            if (!result.isConfirmed) return;
            Swal.fire({ title: trans.processing, allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            $.post(bulkDeleteUrl, { ids: ids })
                .done(function(r) { Swal.fire({ icon: 'success', title: trans.done, text: r.message, timer: 2500 }).then(() => location.reload()); })
                .fail(function(xhr) { handleAjaxFailure(xhr, $()); });
        });
    });
    @endcan

    // DELETE (single)
    @can('leave_request.delete')
    $(document).on('click', '.delete-btn', function() {
        var id = $(this).data('id');
        $('#deleteForm').attr('action', baseUrl + '/' + id);
        $('#deleteModal').modal('show');
    });
    $('#deleteForm').on('submit', function(e) {
        e.preventDefault();
        var btn = $('#deleteSubmitBtn');
        btn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm me-1"></span> ${trans.deleting}...`);
        $.ajax({ url: $(this).attr('action'), type: 'DELETE', dataType: 'json' })
            .done(function(r) { $('#deleteModal').modal('hide'); Swal.fire({ icon: 'success', title: trans.deleted, text: r.message, timer: 2000 }).then(() => location.reload()); })
            .fail(function(xhr) { $('#deleteModal').modal('hide'); handleAjaxFailure(xhr, $()); })
            .always(function() { btn.prop('disabled', false).html(`<i class="fas fa-trash me-1"></i> ${trans.delete}`); });
    });
    @endcan

    // FILTERS (backend-driven)
    function applyFilters() {
        var params = new URLSearchParams();
        var search    = $('#searchInput').val().trim();
        var status    = $('#statusFilter').val();
        var leaveType = $('#leaveTypeFilter').val();
        var fromDate  = $('#fromDate').val();
        var toDate    = $('#toDate').val();
        if (search)    params.set('search', search);
        if (status)    params.set('status', status);
        if (leaveType) params.set('leave_type_id', leaveType);
        if (fromDate)  params.set('from_date', fromDate);
        if (toDate)    params.set('to_date', toDate);
        var qs = params.toString();
        window.location.href = baseUrl + (qs ? ('?' + qs) : '');
    }
    $('#searchBtn').on('click', applyFilters);
    $('#searchInput').on('keypress', function(e) { if (e.which === 13) { e.preventDefault(); applyFilters(); } });
    $('#statusFilter, #leaveTypeFilter, #fromDate, #toDate').on('change', applyFilters);

    // Session expiry
    $(document).ajaxError(function(event, jqxhr) {
        if (jqxhr.status === 401) {
            Swal.fire(trans.session_expired, trans.session_expired_msg, 'warning').then(() => window.location.reload());
        }
    });

    // Modal reset
    $('.modal').on('hidden.bs.modal', function() {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
        $(this).find('.alert-danger').addClass('d-none').empty();
        var f = $(this).find('form')[0];
        if (f) f.reset();
    });
});
</script>
@endpush
@endsection