@extends('layouts.admin')

@section('title', __('messages.attendance'))
@section('page-title', __('messages.attendance_management'))

@section('admin-content')
<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-primary">
            <div class="inner"><h3>{{ number_format($stats['total'] ?? 0) }}</h3><p>{{ __('messages.today_total') }}</p></div>
            <div class="icon"><i class="fas fa-users"></i></div>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-success">
            <div class="inner"><h3>{{ number_format($stats['present'] ?? 0) }}</h3><p>{{ __('messages.present') }}</p></div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-danger">
            <div class="inner"><h3>{{ number_format($stats['absent'] ?? 0) }}</h3><p>{{ __('messages.absent') }}</p></div>
            <div class="icon"><i class="fas fa-times-circle"></i></div>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-warning text-dark">
            <div class="inner"><h3>{{ number_format($stats['late'] ?? 0) }}</h3><p>{{ __('messages.late') }}</p></div>
            <div class="icon"><i class="fas fa-clock"></i></div>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-info">
            <div class="inner"><h3>{{ number_format($stats['half_day'] ?? 0) }}</h3><p>{{ __('messages.half_day') }}</p></div>
            <div class="icon"><i class="fas fa-adjust"></i></div>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="small-box bg-secondary">
            <div class="inner"><h3>{{ number_format($stats['on_leave'] ?? 0) }}</h3><p>{{ __('messages.on_leave') }}</p></div>
            <div class="icon"><i class="fas fa-umbrella-beach"></i></div>
        </div>
    </div>
</div>

<!-- Main Card -->
<div class="card">
    <div class="card-header">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <h5 class="card-title mb-0">
                <i class="fas fa-clipboard-check text-primary me-2"></i>
                {{ __('messages.all_attendance_records') }}
                <span class="badge bg-primary ms-1">{{ $attendances->total() }}</span>
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
                    <option value="{{ $e->id }}">{{ $e->first_name }} {{ $e->last_name }}</option>
                @endforeach
            </select>
            <select id="statusFilter" class="form-select form-select-sm" style="width:120px;">
                <option value="">{{ __('messages.all_status') }}</option>
                @foreach($statuses as $s)
                    <option value="{{ $s }}">{{ __('messages.' . strtolower(str_replace(' ', '_', $s))) }}</option>
                @endforeach
            </select>
            <input type="date" id="fromDate" class="form-control form-control-sm" style="width:130px;" placeholder="{{ __('messages.from') }}">
            <input type="date" id="toDate" class="form-control form-control-sm" style="width:130px;" placeholder="{{ __('messages.to') }}">
            @can('attendance.create')
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fas fa-plus-circle me-1"></i> {{ __('messages.record') }}
            </button>
            @endcan
            @can('attendance.bulk_create')
            <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#bulkModal">
                <i class="fas fa-layer-group me-1"></i> {{ __('messages.bulk_add') }}
            </button>
            @endcan
            @can('attendance.export')
            <div class="dropdown">
                <button class="btn btn-success btn-sm dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown">
                    <i class="fas fa-file-excel me-1"></i> {{ __('messages.export') }}
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#" onclick="exportAttendance('excel', 'current')"><i class="fas fa-file-excel text-success me-2"></i> {{ __('messages.current_page_excel') }}</a></li>
                    <li><a class="dropdown-item" href="#" onclick="exportAttendance('pdf', 'current')"><i class="fas fa-file-pdf text-danger me-2"></i> {{ __('messages.current_page_pdf') }}</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#" onclick="exportAttendance('excel', 'filtered')"><i class="fas fa-file-excel text-success me-2"></i> {{ __('messages.all_filtered_excel') }}</a></li>
                    <li><a class="dropdown-item" href="#" onclick="exportAttendance('pdf', 'filtered')"><i class="fas fa-file-pdf text-danger me-2"></i> {{ __('messages.all_filtered_pdf') }}</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#" onclick="exportAttendance('summary')"><i class="fas fa-chart-bar text-primary me-2"></i> {{ __('messages.summary_report') }}</a></li>
                </ul>
            </div>
            @endcan
            @if(request()->has('archived') && request()->archived == 1)
                <a href="{{ route('admin.attendance.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-undo me-1"></i> {{ __('messages.show_active') }}</a>
            @else
                <a href="{{ route('admin.attendance.index', ['archived' => 1]) }}" class="btn btn-secondary btn-sm"><i class="fas fa-archive me-1"></i> {{ __('messages.show_archived') }}</a>
            @endif
        </div>
    </div>

    <div class="card-body p-0">
        <div class="bulk-actions" id="bulkActionsBar" style="display:none; padding:10px 15px; background:#f8f9fa; border-bottom:1px solid #dee2e6;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-check-circle text-primary me-1"></i>
                    <span id="selectedCount">0</span> {{ __('messages.records_selected') }}
                </div>
                <div>
                    @can('attendance.approve')
                        <button class="btn btn-sm btn-success" onclick="bulkApprove()">
                            <i class="fas fa-check me-1"></i> {{ __('messages.approve') }}
                        </button>
                    @endcan
                    @can('attendance.delete')
                        <button class="btn btn-sm btn-danger" onclick="bulkDelete()">
                            <i class="fas fa-trash me-1"></i> {{ __('messages.delete') }}
                        </button>
                    @endcan
                    <button class="btn btn-sm btn-secondary" onclick="clearSelection()">
                        <i class="fas fa-times me-1"></i> {{ __('messages.clear') }}
                    </button>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:35px;"><input type="checkbox" id="selectAll"></th>
                        <th style="width:35px;">#</th>
                        <th>{{ __('messages.employee') }}</th>
                        <th>{{ __('messages.date') }}</th>
                        <th>{{ __('messages.check_in') }}</th>
                        <th>{{ __('messages.check_out') }}</th>
                        <th>{{ __('messages.hours') }}</th>
                        <th>{{ __('messages.status') }}</th>
                        <th>{{ __('messages.approved') }}</th>
                        <th style="width:220px;">{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($attendances as $attendance)
                    <tr data-id="{{ $attendance->id }}">
                        <td><input type="checkbox" class="row-checkbox" value="{{ $attendance->id }}"></td>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle bg-primary text-white me-2" style="width:32px;height:32px;font-size:12px;">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <div class="fw-bold small">{{ $attendance->employee->first_name ?? 'N/A' }} {{ $attendance->employee->last_name ?? '' }}</div>
                                    <small class="text-muted">{{ $attendance->employee->employee_code ?? '' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>{{ $attendance->attendance_date ? $attendance->attendance_date->format('M d, Y') : 'N/A' }}</td>
                        <td>{{ $attendance->check_in_time ?? '-' }}</td>
                        <td>{{ $attendance->check_out_time ?? '-' }}</td>
                        <td>{{ $attendance->total_worked_hours ? $attendance->total_worked_hours . ' ' . __('messages.hrs') : '-' }}</td>
                        <td>
                            @php
                                $statusColors = ['Present' => 'success', 'Absent' => 'danger', 'Late' => 'warning text-dark', 'Half Day' => 'info', 'On Leave' => 'secondary', 'On Travel' => 'primary'];
                                $statusKey = 'att_' . strtolower(str_replace(' ', '_', $attendance->attendance_status));
                            @endphp
                            <span class="badge bg-{{ $statusColors[$attendance->attendance_status] ?? 'secondary' }}">
                                {{ __('messages.' . $statusKey) }}
                            </span>
                        </td>
                        <td>
                            @if($attendance->is_approved)
                                <span class="badge bg-success"><i class="fas fa-check me-1"></i> {{ __('messages.yes') }}</span>
                            @else
                                <span class="badge bg-secondary">{{ __('messages.no') }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                @can('attendance.view')
                                <button class="btn btn-outline-primary btn-sm view-btn" data-id="{{ $attendance->id }}" title="{{ __('messages.view') }}"><i class="fas fa-eye"></i></button>
                                @endcan
                                @can('attendance.edit')
                                <button class="btn btn-outline-warning btn-sm edit-btn" data-id="{{ $attendance->id }}" title="{{ __('messages.edit') }}"><i class="fas fa-edit"></i></button>
                                @endcan
                                @can('attendance.approve')
                                @if(!$attendance->is_approved)
                                <button class="btn btn-outline-success btn-sm approve-btn" data-id="{{ $attendance->id }}" title="{{ __('messages.approve') }}"><i class="fas fa-check"></i></button>
                                @endif
                                @endcan
                                @can('attendance.delete')
                                <button class="btn btn-outline-danger btn-sm delete-btn" data-id="{{ $attendance->id }}" title="{{ __('messages.delete') }}"><i class="fas fa-trash"></i></button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="10"><div class="text-center py-5"><i class="fas fa-clipboard-check fa-4x text-muted mb-3 d-block"></i><h5 class="fw-bold">{{ __('messages.no_attendance_records_found') }}</h5></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div><span class="text-muted small">{{ __('messages.showing') }} {{ $attendances->firstItem() ?? 0 }} {{ __('messages.to') }} {{ $attendances->lastItem() ?? 0 }} {{ __('messages.of') }} {{ $attendances->total() }} {{ __('messages.entries') }}</span></div>
        <div>{{ $attendances->links() }}</div>
    </div>
</div>

<!-- CREATE MODAL -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:560px;">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="fas fa-plus-circle me-2"></i> {{ __('messages.record_attendance') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="createForm" action="{{ route('admin.attendance.store') }}" method="POST">
                @csrf
                <div class="modal-body" style="padding:20px 25px;">
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
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="attendance_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.status') }} <span class="text-danger">*</span></label>
                            <select name="attendance_status" class="form-select" required>
                                <option value="Present">{{ __('messages.att_present') }}</option>
                                <option value="Absent">{{ __('messages.att_absent') }}</option>
                                <option value="Late">{{ __('messages.att_late') }}</option>
                                <option value="Half Day">{{ __('messages.att_half_day') }}</option>
                                <option value="On Leave">{{ __('messages.att_on_leave') }}</option>
                                <option value="On Travel">{{ __('messages.att_on_travel') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.check_in') }}</label>
                            <input type="time" name="check_in_time" class="form-control" step="60">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('messages.check_out') }}</label>
                            <input type="time" name="check_out_time" class="form-control" step="60">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">{{ __('messages.remarks') }}</label>
                            <textarea name="remarks" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> {{ __('messages.cancel') }}</button>
                    <button type="submit" class="btn btn-primary" id="createSubmitBtn"><i class="fas fa-save me-1"></i> {{ __('messages.save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- BULK ADD MODAL -->
<div class="modal fade" id="bulkModal" tabindex="-1" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title text-white"><i class="fas fa-layer-group me-2"></i> {{ __('messages.bulk_add_attendance') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="max-height:70vh; overflow-y:auto;">
                <div class="bulk-toolbar" style="background:#f8f9fa; padding:15px; border-radius:5px; margin-bottom:15px; border-left:4px solid #0d6efd;">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <label class="fw-bold">{{ __('messages.attendance_date') }}</label>
                            <input type="date" class="form-control" id="bulkDate" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="fw-bold">{{ __('messages.default_status') }}</label>
                            <select class="form-select" id="bulkDefaultStatus">
                                <option value="Present">{{ __('messages.att_present') }}</option>
                                <option value="Absent">{{ __('messages.att_absent') }}</option>
                                <option value="Late">{{ __('messages.att_late') }}</option>
                                <option value="Half Day">{{ __('messages.att_half_day') }}</option>
                                <option value="On Leave">{{ __('messages.att_on_leave') }}</option>
                                <option value="On Travel">{{ __('messages.att_on_travel') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-bold">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="applyDefaultToAll()">
                                    <i class="fas fa-check-circle me-1"></i> {{ __('messages.apply_default_status') }}
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-success" onclick="setAllCheckIn()">
                                    <i class="fas fa-clock me-1"></i> {{ __('messages.set_check_in') }}
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-warning" onclick="setAllCheckOut()">
                                    <i class="fas fa-clock me-1"></i> {{ __('messages.set_check_out') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bulk-table-container" style="max-height:400px; overflow-y:auto; border:1px solid #dee2e6; border-radius:5px;">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light" style="position:sticky; top:0; z-index:10;">
                            <tr>
                                <th style="width:5%;">#</th>
                                <th style="width:25%;">{{ __('messages.employee') }}</th>
                                <th style="width:15%;">{{ __('messages.status') }}</th>
                                <th style="width:15%;">{{ __('messages.check_in') }}</th>
                                <th style="width:15%;">{{ __('messages.check_out') }}</th>
                                <th style="width:15%;">{{ __('messages.remarks') }}</th>
                                <th style="width:10%;">{{ __('messages.action') }}</th>
                            </tr>
                        </thead>
                        <tbody id="bulkTableBody">
                            @foreach($employees as $index => $emp)
                            <tr id="emp-row-{{ $emp->id }}" class="employee-row">
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>
                                    <input type="hidden" class="bulk-employee-id" value="{{ $emp->id }}">
                                    <div class="fw-bold">{{ $emp->first_name }} {{ $emp->last_name }}</div>
                                    <small class="text-muted">{{ $emp->employee_code }}</small>
                                </td>
                                <td>
                                    <select class="form-select form-select-sm bulk-status">
                                        <option value="Present">{{ __('messages.att_present') }}</option>
                                        <option value="Absent">{{ __('messages.att_absent') }}</option>
                                        <option value="Late">{{ __('messages.att_late') }}</option>
                                        <option value="Half Day">{{ __('messages.att_half_day') }}</option>
                                        <option value="On Leave">{{ __('messages.att_on_leave') }}</option>
                                        <option value="On Travel">{{ __('messages.att_on_travel') }}</option>
                                    </select>
                                </td>
                                <td><input type="time" class="form-control form-control-sm bulk-checkin" value="09:00"></td>
                                <td><input type="time" class="form-control form-control-sm bulk-checkout" value="18:00"></td>
                                <td><input type="text" class="form-control form-control-sm bulk-remarks" placeholder="{{ __('messages.remarks') }}"></td>
                                <td class="text-center">
                                    <i class="fas fa-times text-danger remove-row-btn" onclick="removeEmployee('{{ $emp->id }}')" title="{{ __('messages.remove') }}" style="cursor:pointer;"></i>
                                    <i class="fas fa-undo text-success restore-row-btn" onclick="restoreEmployee('{{ $emp->id }}')" title="{{ __('messages.restore') }}" style="cursor:pointer; display:none;"></i>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body py-2">
                                <strong>{{ __('messages.total_employees') }}:</strong> <span id="totalEmployees">{{ count($employees) }}</span> |
                                <strong>{{ __('messages.selected') }}:</strong> <span id="selectedEmployees">{{ count($employees) }}</span> |
                                <strong>{{ __('messages.removed') }}:</strong> <span id="removedEmployees">0</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                <button type="button" class="btn btn-info" onclick="saveBulkAttendance()"><i class="fas fa-save me-1"></i> {{ __('messages.save_all_records') }}</button>
            </div>
        </div>
    </div>
</div>

<!-- VIEW MODAL -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width:600px;">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title text-white"><i class="fas fa-eye me-2"></i> {{ __('messages.attendance_details') }}</h5>
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
    <div class="modal-dialog modal-dialog-centered" style="max-width:560px;">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title text-dark"><i class="fas fa-edit me-2"></i> {{ __('messages.edit_attendance') }}</h5>
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
                    <button type="submit" class="btn btn-warning" id="editSubmitBtn"><i class="fas fa-save me-1"></i> {{ __('messages.update') }}</button>
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
                <p class="fw-bold">{{ __('messages.are_you_sure_delete_record') }}</p>
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
.bulk-actions { background:#e8f4fd; padding:10px 15px; border-bottom:1px solid #dee2e6; border-left:4px solid #0d6efd; }
.bulk-toolbar { background:#f8f9fa; padding:15px; border-radius:5px; margin-bottom:15px; border-left:4px solid #0d6efd; }
.employee-row.removed td { background-color:#f8d7da; opacity:0.6; }
.employee-row.removed .form-control, .employee-row.removed .form-select { opacity:0.5; pointer-events:none; }
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
    save: @json(__('messages.save')),
    update: @json(__('messages.update')),
    // Fields
    employee: @json(__('messages.employee')),
    date: @json(__('messages.date')),
    check_in: @json(__('messages.check_in')),
    check_out: @json(__('messages.check_out')),
    hours: @json(__('messages.hours')),
    total_hours: @json(__('messages.total_hours')),
    overtime: @json(__('messages.overtime')),
    status: @json(__('messages.status')),
    approved: @json(__('messages.approved')),
    remarks: @json(__('messages.remarks')),
    created: @json(__('messages.created')),
    yes: @json(__('messages.yes')),
    no: @json(__('messages.no')),
    hrs: @json(__('messages.hrs')),
    select_employee: @json(__('messages.select_employee')),
    // Attendance statuses
    att_present: @json(__('messages.att_present')),
    att_absent: @json(__('messages.att_absent')),
    att_late: @json(__('messages.att_late')),
    att_half_day: @json(__('messages.att_half_day')),
    att_on_leave: @json(__('messages.att_on_leave')),
    att_on_travel: @json(__('messages.att_on_travel')),
    // Bulk actions
    info: @json(__('messages.info')),
    select_at_least_one: @json(__('messages.select_at_least_one')),
    processing: @json(__('messages.processing')),
    saving: @json(__('messages.saving')),
    deleting: @json(__('messages.deleting')),
    updating: @json(__('messages.updating')),
    saved: @json(__('messages.saved')),
    saved_alert: @json(__('messages.saved_alert')),
    updated: @json(__('messages.updated')),
    deleted: @json(__('messages.deleted')),
    approved_alert: @json(__('messages.approved_alert')),
    // Confirmations
    approve_selected_q: @json(__('messages.approve_selected_q')),
    approve_selected_warning: @json(__('messages.approve_selected_warning')),
    yes_approve: @json(__('messages.yes_approve')),
    delete_selected_q: @json(__('messages.delete_selected_q')),
    delete_selected_warning: @json(__('messages.delete_selected_warning')),
    yes_delete: @json(__('messages.yes_delete')),
    approve_q: @json(__('messages.approve_q')),
    approve_warning: @json(__('messages.approve_warning')),
    are_you_sure: @json(__('messages.are_you_sure')),
    wont_be_able_to_revert: @json(__('messages.wont_be_able_to_revert')),
    save_all_records_q: @json(__('messages.save_all_records_q')),
    save_all_records_warning: @json(__('messages.save_all_records_warning')),
    yes_save_all: @json(__('messages.yes_save_all')),
    no_employees_selected: @json(__('messages.no_employees_selected')),
    please_select_date: @json(__('messages.please_select_date')),
    // Errors
    error_title: @json(__('messages.error')),
    failed_to_save: @json(__('messages.failed_to_save')),
    failed_to_update: @json(__('messages.failed_to_update')),
    failed_to_delete: @json(__('messages.failed_to_delete')),
    failed_to_approve: @json(__('messages.failed_to_approve')),
    failed_to_save_records: @json(__('messages.failed_to_save_records')),
    something_went_wrong: @json(__('messages.something_went_wrong')),
    // Exports
    exporting: @json(__('messages.exporting')),
    exporting_wait: @json(__('messages.exporting_wait')),
    exported: @json(__('messages.exported')),
    summary_exported: @json(__('messages.summary_exported')),
    no_data_to_export: @json(__('messages.no_data_to_export')),
    failed_to_fetch_export: @json(__('messages.failed_to_fetch_export')),
    attendance_report: @json(__('messages.attendance_report')),
    generated: @json(__('messages.generated')),
    attendance_summary_report: @json(__('messages.attendance_summary_report')),
    generated_on: @json(__('messages.generated_on')),
    metric: @json(__('messages.metric')),
    value: @json(__('messages.value')),
    total_records: @json(__('messages.total_records')),
    average_hours: @json(__('messages.average_hours')),
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

    // SELECT ALL / BULK ACTIONS
    $('#selectAll').on('change', function() { $('.row-checkbox').prop('checked', $(this).prop('checked')); updateBulkActions(); });
    $(document).on('change', '.row-checkbox', function() { updateBulkActions(); });
    function updateBulkActions() {
        var count = $('.row-checkbox:checked').length;
        if (count > 0) { $('#bulkActionsBar').fadeIn(200); $('#selectedCount').text(count); }
        else { $('#bulkActionsBar').fadeOut(200); }
    }
    window.clearSelection = function() {
        $('.row-checkbox').prop('checked', false);
        $('#selectAll').prop('checked', false);
        $('#bulkActionsBar').fadeOut(200);
    };

    // BULK APPROVE
    window.bulkApprove = function() {
        var ids = [];
        $('.row-checkbox:checked').each(function() { ids.push($(this).val()); });
        if (ids.length === 0) { Swal.fire(trans.info, trans.select_at_least_one, 'info'); return; }
        Swal.fire({
            title: trans.approve_selected_q,
            text: trans.approve_selected_warning.replace(':count', ids.length),
            icon: 'question', showCancelButton: true,
            confirmButtonColor: '#28a745', cancelButtonColor: '#6c757d',
            confirmButtonText: trans.yes_approve, cancelButtonText: trans.cancel
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: trans.processing, allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                $.ajax({
                    url: '{{ route("admin.attendance.bulk-approve") }}',
                    type: 'POST', data: { ids: ids.join(','), _token: '{{ csrf_token() }}' },
                    success: function(r) {
                        if (r.success) Swal.fire({ icon: 'success', title: trans.approved_alert, text: r.message, timer: 2000 }).then(() => location.reload());
                        else Swal.fire(trans.error_title, r.message || trans.failed_to_approve, 'error');
                    },
                    error: function() { Swal.fire(trans.error_title, trans.failed_to_approve, 'error'); }
                });
            }
        });
    };

    // BULK DELETE
    window.bulkDelete = function() {
        var ids = [];
        $('.row-checkbox:checked').each(function() { ids.push($(this).val()); });
        if (ids.length === 0) { Swal.fire(trans.info, trans.select_at_least_one, 'info'); return; }
        Swal.fire({
            title: trans.delete_selected_q,
            text: trans.delete_selected_warning.replace(':count', ids.length),
            icon: 'warning', showCancelButton: true,
            confirmButtonColor: '#d33', cancelButtonColor: '#6c757d',
            confirmButtonText: trans.yes_delete, cancelButtonText: trans.cancel
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: trans.deleting, allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                $.ajax({
                    url: '{{ route("admin.attendance.bulk-delete") }}',
                    type: 'POST', data: { ids: ids.join(','), _token: '{{ csrf_token() }}' },
                    success: function(r) {
                        if (r.success) Swal.fire({ icon: 'success', title: trans.deleted, text: r.message, timer: 2000 }).then(() => location.reload());
                        else Swal.fire(trans.error_title, r.message || trans.failed_to_delete, 'error');
                    },
                    error: function() { Swal.fire(trans.error_title, trans.failed_to_delete, 'error'); }
                });
            }
        });
    };

    // VIEW
    @can('attendance.view')
    $(document).on('click', '.view-btn', function() {
        var id = $(this).data('id');
        var body = $('#viewModalBody');
        body.html(`<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>`);
        $('#viewModal').modal('show');
        $.get('/admin/attendance/' + id, function(a) {
            var statusKey = 'att_' + a.attendance_status.toLowerCase().replace(/\s+/g, '_');
            var statusLabel = trans[statusKey] || a.attendance_status;
            var html = `
                <div class="row g-3">
                    <div class="col-md-6"><label class="text-muted small fw-bold">${trans.employee}</label><p class="fw-bold mb-0">${a.employee ? a.employee.first_name + ' ' + a.employee.last_name : 'N/A'}</p></div>
                    <div class="col-md-6"><label class="text-muted small fw-bold">${trans.date}</label><p class="mb-0">${new Date(a.attendance_date).toLocaleDateString()}</p></div>
                    <div class="col-md-6"><label class="text-muted small fw-bold">${trans.check_in}</label><p class="mb-0">${a.check_in_time || '-'}</p></div>
                    <div class="col-md-6"><label class="text-muted small fw-bold">${trans.check_out}</label><p class="mb-0">${a.check_out_time || '-'}</p></div>
                    <div class="col-md-6"><label class="text-muted small fw-bold">${trans.total_hours}</label><p class="fw-bold mb-0">${a.total_worked_hours ? a.total_worked_hours + ' ' + trans.hrs : '-'}</p></div>
                    <div class="col-md-6"><label class="text-muted small fw-bold">${trans.overtime}</label><p class="mb-0">${a.overtime_hours ? a.overtime_hours + ' ' + trans.hrs : '-'}</p></div>
                    <div class="col-md-6"><label class="text-muted small fw-bold">${trans.status}</label><p class="mb-0"><span class="badge bg-${a.attendance_status == 'Present' ? 'success' : (a.attendance_status == 'Absent' ? 'danger' : (a.attendance_status == 'Late' ? 'warning text-dark' : (a.attendance_status == 'Half Day' ? 'info' : 'secondary')))}">${statusLabel}</span></p></div>
                    <div class="col-md-6"><label class="text-muted small fw-bold">${trans.approved}</label><p class="mb-0">${a.is_approved ? trans.yes : trans.no}</p></div>
                    ${a.remarks ? `<div class="col-12"><label class="text-muted small fw-bold">${trans.remarks}</label><p class="mb-0">${a.remarks}</p></div>` : ''}
                    <div class="col-md-6"><label class="text-muted small fw-bold">${trans.created}</label><p class="mb-0">${new Date(a.created_at).toLocaleString()}</p></div>
                </div>`;
            body.html(html);
        });
    });
    @endcan

    // EDIT
    @can('attendance.edit')
    $(document).on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        var body = $('#editModalBody');
        var form = $('#editForm');
        form.attr('action', '/admin/attendance/' + id);
        body.html(`<div id="editFormErrors" class="alert alert-danger d-none"></div><div class="text-center py-5"><div class="spinner-border text-warning" role="status"></div></div>`);
        $('#editModal').modal('show');
        $.get('/admin/attendance/' + id + '/edit', function(r) {
            var a = r.attendance;
            var empOptions = '';
            r.employees.forEach(function(e) { empOptions += `<option value="${e.id}" ${a.employee_id == e.id ? 'selected' : ''}>${e.first_name} ${e.last_name}</option>`; });
            body.html(`
                <div id="editFormErrors" class="alert alert-danger d-none"></div>
                <div class="row g-3">
                    <div class="col-12"><label class="form-label fw-bold">${trans.employee} <span class="text-danger">*</span></label><select name="employee_id" class="form-select" required>${empOptions}</select></div>
                    <div class="col-md-6"><label class="form-label fw-bold">${trans.date} <span class="text-danger">*</span></label><input type="date" name="attendance_date" class="form-control" value="${a.attendance_date}" required></div>
                    <div class="col-md-6"><label class="form-label fw-bold">${trans.status} <span class="text-danger">*</span></label>
                        <select name="attendance_status" class="form-select" required>
                            <option value="Present" ${a.attendance_status == 'Present' ? 'selected' : ''}>${trans.att_present}</option>
                            <option value="Absent" ${a.attendance_status == 'Absent' ? 'selected' : ''}>${trans.att_absent}</option>
                            <option value="Late" ${a.attendance_status == 'Late' ? 'selected' : ''}>${trans.att_late}</option>
                            <option value="Half Day" ${a.attendance_status == 'Half Day' ? 'selected' : ''}>${trans.att_half_day}</option>
                            <option value="On Leave" ${a.attendance_status == 'On Leave' ? 'selected' : ''}>${trans.att_on_leave}</option>
                            <option value="On Travel" ${a.attendance_status == 'On Travel' ? 'selected' : ''}>${trans.att_on_travel}</option>
                        </select>
                    </div>
                    <div class="col-md-6"><label class="form-label fw-bold">${trans.check_in}</label><input type="time" name="check_in_time" class="form-control" step="60" value="${a.check_in_time || ''}"></div>
                    <div class="col-md-6"><label class="form-label fw-bold">${trans.check_out}</label><input type="time" name="check_out_time" class="form-control" step="60" value="${a.check_out_time || ''}"></div>
                    <div class="col-12"><label class="form-label fw-bold">${trans.remarks}</label><textarea name="remarks" class="form-control" rows="2">${a.remarks || ''}</textarea></div>
                </div>`);
        });
    });
    @endcan

    // CREATE
    @can('attendance.create')
    $('#createForm').on('submit', function(e) {
        e.preventDefault();
        var btn = $('#createSubmitBtn'); var errorContainer = $('#createFormErrors');
        clearErrors(errorContainer);
        btn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm me-1"></span> ${trans.saving}...`);
        $.ajax({
            url: $(this).attr('action'), type: 'POST', data: $(this).serialize(), dataType: 'json',
            success: function(r) {
                if (r.success) { $('#createModal').modal('hide'); Swal.fire({ icon: 'success', title: trans.saved_alert, text: r.message, timer: 2000 }).then(() => location.reload()); }
                else { btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.save}`); if (r.errors) showErrors(errorContainer, r.errors); else Swal.fire({ icon: 'error', title: trans.error_title, text: r.message || trans.something_went_wrong }); }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.save}`);
                if (xhr.status === 422) showErrors(errorContainer, xhr.responseJSON.errors);
                else Swal.fire({ icon: 'error', title: trans.error_title, text: trans.failed_to_save });
            }
        });
    });
    @endcan

    // UPDATE
    @can('attendance.edit')
    $('#editForm').on('submit', function(e) {
        e.preventDefault();
        var btn = $('#editSubmitBtn'); var errorContainer = $('#editFormErrors');
        clearErrors(errorContainer);
        btn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm me-1"></span> ${trans.updating}...`);
        $.ajax({
            url: $(this).attr('action'), type: 'POST', data: $(this).serialize(), dataType: 'json',
            success: function(r) {
                if (r.success) { $('#editModal').modal('hide'); Swal.fire({ icon: 'success', title: trans.updated, text: r.message, timer: 2000 }).then(() => location.reload()); }
                else { btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.update}`); if (r.errors) showErrors(errorContainer, r.errors); else Swal.fire({ icon: 'error', title: trans.error_title, text: r.message || trans.something_went_wrong }); }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(`<i class="fas fa-save me-1"></i> ${trans.update}`);
                if (xhr.status === 422) showErrors(errorContainer, xhr.responseJSON.errors);
                else Swal.fire({ icon: 'error', title: trans.error_title, text: trans.failed_to_update });
            }
        });
    });
    @endcan

    // APPROVE (single)
    @can('attendance.approve')
    $(document).on('click', '.approve-btn', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: trans.approve_q, text: trans.approve_warning, icon: 'question',
            showCancelButton: true, confirmButtonColor: '#28a745', cancelButtonColor: '#6c757d',
            confirmButtonText: trans.yes_approve, cancelButtonText: trans.cancel
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('/admin/attendance/' + id + '/approve', { _token: '{{ csrf_token() }}' }, function(r) {
                    if (r.success) Swal.fire({ icon: 'success', title: trans.approved_alert, text: r.message, timer: 1500 }).then(() => location.reload());
                    else Swal.fire(trans.error_title, r.message || trans.failed_to_approve, 'error');
                });
            }
        });
    });
    @endcan

    // DELETE (single)
    @can('attendance.delete')
    $(document).on('click', '.delete-btn', function() {
        var id = $(this).data('id');
        $('#deleteForm').attr('action', '/admin/attendance/' + id);
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
            url: $(this).attr('action'), type: 'POST',
            data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
            success: function(r) {
                if (r.success) { $('#deleteModal').modal('hide'); Swal.fire({ icon: 'success', title: trans.deleted, text: r.message, timer: 2000 }).then(() => location.reload()); }
                else { btn.prop('disabled', false).html(`<i class="fas fa-trash me-1"></i> ${trans.delete}`); Swal.fire(trans.error_title, r.message || trans.failed_to_delete, 'error'); }
            },
            error: function(xhr) { btn.prop('disabled', false).html(`<i class="fas fa-trash me-1"></i> ${trans.delete}`); Swal.fire(trans.error_title, xhr.responseJSON?.message || trans.failed_to_delete, 'error'); }
        });
    });
    @endcan

    // BULK MODAL FUNCTIONS
    window.removeEmployee = function(employeeId) {
        $('#emp-row-' + employeeId).addClass('removed').find('.remove-row-btn').hide().end().find('.restore-row-btn').show();
        updateBulkCounters();
    };
    window.restoreEmployee = function(employeeId) {
        $('#emp-row-' + employeeId).removeClass('removed').find('.remove-row-btn').show().end().find('.restore-row-btn').hide();
        updateBulkCounters();
    };
    function updateBulkCounters() {
        var total = $('.employee-row').length;
        var removed = $('.employee-row.removed').length;
        $('#totalEmployees').text(total);
        $('#removedEmployees').text(removed);
        $('#selectedEmployees').text(total - removed);
    }
    window.applyDefaultToAll = function() { $('.employee-row:not(.removed) .bulk-status').val($('#bulkDefaultStatus').val()); };
    window.setAllCheckIn = function() { $('.employee-row:not(.removed) .bulk-checkin').val('09:00'); };
    window.setAllCheckOut = function() { $('.employee-row:not(.removed) .bulk-checkout').val('18:00'); };

    window.saveBulkAttendance = function() {
        var date = $('#bulkDate').val();
        if (!date) { Swal.fire(trans.error_title, trans.please_select_date, 'error'); return; }
        var attendances = [];
        $('.employee-row:not(.removed)').each(function() {
            var row = $(this);
            attendances.push({
                employee_id: row.find('.bulk-employee-id').val(),
                attendance_date: date,
                check_in_time: row.find('.bulk-checkin').val() || null,
                check_out_time: row.find('.bulk-checkout').val() || null,
                attendance_status: row.find('.bulk-status').val(),
                remarks: row.find('.bulk-remarks').val() || null
            });
        });
        if (attendances.length === 0) { Swal.fire(trans.error_title, trans.no_employees_selected, 'warning'); return; }
        Swal.fire({
            title: trans.save_all_records_q,
            text: trans.save_all_records_warning.replace(':count', attendances.length),
            icon: 'question', showCancelButton: true,
            confirmButtonColor: '#0dcaf0', cancelButtonColor: '#6c757d',
            confirmButtonText: trans.yes_save_all, cancelButtonText: trans.cancel
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: trans.saving, allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                $.ajax({
                    url: '{{ route("admin.attendance.bulk-store") }}',
                    type: 'POST', data: { attendances: attendances, _token: '{{ csrf_token() }}' },
                    success: function(r) {
                        if (r.success) { $('#bulkModal').modal('hide'); Swal.fire({ icon: 'success', title: trans.saved_alert, text: r.message, timer: 3000 }).then(() => location.reload()); }
                        else Swal.fire(trans.error_title, r.message || trans.failed_to_save_records, 'error');
                    },
                    error: function(xhr) { Swal.fire(trans.error_title, xhr.responseJSON?.message || trans.failed_to_save_records, 'error'); }
                });
            }
        });
    };

    // EXPORTS
    window.exportAttendance = function(format, type) {
        Swal.fire({ title: trans.exporting, text: trans.exporting_wait, allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        if (type === 'current') {
            var headers = ['#', trans.employee, trans.date, trans.check_in, trans.check_out, trans.hours, trans.status, trans.approved];
            var data = [];
            $('#tableBody tr:not(:has(td[colspan]))').each(function() {
                var cells = $(this).find('td');
                data.push([
                    cells.eq(1).text().trim(), cells.eq(2).text().trim(), cells.eq(3).text().trim(), cells.eq(4).text().trim(),
                    cells.eq(5).text().trim(), cells.eq(6).text().trim(), cells.eq(7).text().trim().replace(/\s+/g, ' '), cells.eq(8).text().trim()
                ]);
            });
            if (format === 'excel') exportToExcel(headers, data, 'current_page');
            else exportToPDF(headers, data, 'current_page');
        } else if (type === 'filtered') {
            var filters = {
                search: $('#searchInput').val(), employee_id: $('#employeeFilter').val(), status: $('#statusFilter').val(),
                from_date: $('#fromDate').val(), to_date: $('#toDate').val(), export: true, per_page: 10000
            };
            $.ajax({
                url: window.location.pathname, data: filters,
                success: function(response) {
                    Swal.close();
                    if (response.data && response.data.length > 0) {
                        var headers = ['ID', trans.employee, 'Code', trans.date, trans.check_in, trans.check_out, trans.hours, trans.status, trans.approved];
                        var data = response.data.map(function(item) {
                            return [
                                item.attendance_id,
                                (item.employee?.first_name || '') + ' ' + (item.employee?.last_name || ''),
                                item.employee?.employee_code || '',
                                item.attendance_date,
                                item.check_in_time || '-',
                                item.check_out_time || '-',
                                item.total_worked_hours ? item.total_worked_hours + ' ' + trans.hrs : '-',
                                item.attendance_status,
                                item.is_approved ? trans.yes : trans.no
                            ];
                        });
                        if (format === 'excel') exportToExcel(headers, data, 'filtered');
                        else exportToPDF(headers, data, 'filtered');
                    } else Swal.fire(trans.info, trans.no_data_to_export, 'info');
                },
                error: function() { Swal.close(); Swal.fire(trans.error_title, trans.failed_to_fetch_export, 'error'); }
            });
        } else if (type === 'summary') {
            var total = $('#tableBody tr:not(:has(td[colspan]))').length;
            var present = 0, absent = 0, late = 0, halfDay = 0, onLeave = 0, onTravel = 0;
            var totalHours = 0;
            $('#tableBody tr:not(:has(td[colspan]))').each(function() {
                var cells = $(this).find('td');
                var status = cells.eq(7).text().trim().replace(/\s+/g, ' ');
                var hours = parseFloat(cells.eq(6).text().trim()) || 0;
                if (status === trans.att_present) present++;
                else if (status === trans.att_absent) absent++;
                else if (status === trans.att_late) late++;
                else if (status === trans.att_half_day) halfDay++;
                else if (status === trans.att_on_leave) onLeave++;
                else if (status === trans.att_on_travel) onTravel++;
                totalHours += hours;
            });
            var summaryData = [
                [trans.attendance_summary_report],
                [trans.generated_on, new Date().toLocaleString()], [''],
                [trans.metric, trans.value],
                [trans.total_records, total],
                [trans.att_present, present],
                [trans.att_absent, absent],
                [trans.att_late, late],
                [trans.att_half_day, halfDay],
                [trans.att_on_leave, onLeave],
                [trans.att_on_travel, onTravel],
                [trans.total_hours, totalHours.toFixed(2) + ' ' + trans.hrs],
                [trans.average_hours, total > 0 ? (totalHours / total).toFixed(2) + ' ' + trans.hrs : '0 ' + trans.hrs]
            ];
            exportToExcelSummary(summaryData);
        }
    };

    function exportToExcel(headers, data, type) {
        var wb = XLSX.utils.book_new();
        var wsData = [headers, ...data];
        var ws = XLSX.utils.aoa_to_sheet(wsData);
        ws['!cols'] = [{ wch: 8 },{ wch: 25 },{ wch: 15 },{ wch: 12 },{ wch: 10 },{ wch: 10 },{ wch: 15 },{ wch: 12 },{ wch: 10 }];
        XLSX.utils.book_append_sheet(wb, ws, 'Attendance');
        XLSX.writeFile(wb, 'attendance_' + type + '_' + new Date().getTime() + '.xlsx');
        Swal.fire({ icon: 'success', title: trans.exported, text: trans.exported + ' ' + data.length + ' ' + trans.records, timer: 1500, showConfirmButton: false });
    }
    function exportToPDF(headers, data, type) {
        var { jsPDF } = window.jspdf;
        var doc = new jsPDF('landscape');
        doc.setFontSize(16);
        doc.text(trans.attendance_report, 14, 15);
        doc.setFontSize(10);
        doc.text(trans.generated + ': ' + new Date().toLocaleString(), 14, 22);
        doc.autoTable({ head: [headers], body: data, startY: 30, styles: { fontSize: 8, cellPadding: 2 }, headStyles: { fillColor: [41, 128, 185], textColor: 255 } });
        doc.save('attendance_' + type + '_' + new Date().getTime() + '.pdf');
        Swal.fire({ icon: 'success', title: trans.exported, text: trans.exported + ' ' + data.length + ' ' + trans.records, timer: 1500, showConfirmButton: false });
    }
    function exportToExcelSummary(data) {
        var wb = XLSX.utils.book_new();
        var ws = XLSX.utils.aoa_to_sheet(data);
        XLSX.utils.book_append_sheet(wb, ws, 'Summary');
        XLSX.writeFile(wb, 'attendance_summary_' + new Date().toISOString().slice(0,10) + '.xlsx');
        Swal.fire({ icon: 'success', title: trans.summary_exported, timer: 1500, showConfirmButton: false });
    }

    // SEARCH & FILTER
    $('#searchBtn').on('click', filterTable);
    $('#searchInput').on('keypress', function(e) { if (e.which === 13) filterTable(); });
    $('#employeeFilter, #statusFilter, #fromDate, #toDate').on('change', filterTable);
    function filterTable() {
        var url = new URL(window.location.href);
        url.searchParams.set('search', $('#searchInput').val());
        url.searchParams.set('employee_id', $('#employeeFilter').val());
        url.searchParams.set('status', $('#statusFilter').val());
        url.searchParams.set('from_date', $('#fromDate').val());
        url.searchParams.set('to_date', $('#toDate').val());
        url.searchParams.set('page', 1);
        window.location.href = url.toString();
    }

    // MODAL RESET
    $('.modal').on('hidden.bs.modal', function() {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
        $(this).find('.alert-danger').addClass('d-none').empty();
        $(this).find('form')[0]?.reset();
        if ($(this).attr('id') === 'bulkModal') {
            $('.employee-row').removeClass('removed').find('.remove-row-btn').show().end().find('.restore-row-btn').hide();
            updateBulkCounters();
        }
    });
});
</script>
@endpush
@endsection