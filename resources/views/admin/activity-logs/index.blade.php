@extends('layouts.admin')

@section('title', 'Activity Logs')
@section('page-title', 'Activity Logs')

@section('admin-content')
<style>
/* ============================================
   ACTIVITY LOGS CUSTOM STYLES
============================================ */
.filter-section {
    background: #f8fafc;
    border-radius: 12px;
    padding: 15px 20px;
    margin-bottom: 20px;
}

.stats-summary-card {
    border-radius: 12px;
    padding: 15px 20px;
    background: #fff;
    border: 1px solid #e2e8f0;
    transition: all 0.2s ease;
}
.stats-summary-card:hover {
    border-color: #4f46e5;
    box-shadow: 0 4px 12px rgba(79,70,229,0.08);
    transform: translateY(-2px);
}
.stats-summary-card .number {
    font-size: 1.8rem;
    font-weight: 700;
}
.stats-summary-card .label {
    font-size: 0.8rem;
    color: #64748b;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 600;
}
.status-badge-success { background: #dcfce7; color: #166534; }
.status-badge-failed { background: #fee2e2; color: #991b1b; }
.status-badge-warning { background: #fef3c7; color: #92400e; }

.activity-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    flex-shrink: 0;
}

/* Log Viewer Styles */
.log-viewer {
    background: #0f172a;
    color: #e2e8f0;
    padding: 15px;
    border-radius: 8px;
    font-family: 'Courier New', monospace;
    font-size: 0.75rem;
    max-height: 500px;
    overflow-y: auto;
    white-space: pre-wrap;
    word-break: break-word;
}
.log-viewer::-webkit-scrollbar {
    width: 6px;
}
.log-viewer::-webkit-scrollbar-track {
    background: #1e293b;
    border-radius: 4px;
}
.log-viewer::-webkit-scrollbar-thumb {
    background: #475569;
    border-radius: 4px;
}
.log-viewer .log-line {
    padding: 2px 0;
    border-bottom: 1px solid rgba(255,255,255,0.02);
}
.log-viewer .log-line.error {
    color: #f87171;
}
.log-viewer .log-line.warning {
    color: #fbbf24;
}
.log-viewer .log-line.info {
    color: #60a5fa;
}
.log-viewer .log-line.success {
    color: #34d399;
}
</style>

<!-- ============================================ -->
<!-- STATS SUMMARY -->
<!-- ============================================ -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="stats-summary-card text-center">
            <div class="number text-primary" id="totalActivities">0</div>
            <div class="label">Total Activities</div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="stats-summary-card text-center">
            <div class="number text-success" id="todayActivities">0</div>
            <div class="label">Today</div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="stats-summary-card text-center">
            <div class="number text-info" id="thisWeekActivities">0</div>
            <div class="label">This Week</div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="stats-summary-card text-center">
            <div class="number text-warning" id="thisMonthActivities">0</div>
            <div class="label">This Month</div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- FILTER SECTION -->
<!-- ============================================ -->
<div class="filter-section">
    <div class="row g-3 align-items-end">
        <div class="col-xl-2 col-lg-4 col-md-6">
            <label class="form-label fw-bold small">Date From</label>
            <input type="date" id="dateFrom" class="form-control form-control-sm" value="{{ now()->startOfMonth()->format('Y-m-d') }}">
        </div>
        <div class="col-xl-2 col-lg-4 col-md-6">
            <label class="form-label fw-bold small">Date To</label>
            <input type="date" id="dateTo" class="form-control form-control-sm" value="{{ now()->format('Y-m-d') }}">
        </div>
        <div class="col-xl-2 col-lg-4 col-md-6">
            <label class="form-label fw-bold small">Action</label>
            <select id="actionFilter" class="form-select form-select-sm">
                <option value="">All Actions</option>
                <option value="login">Login</option>
                <option value="logout">Logout</option>
                <option value="created">Created</option>
                <option value="updated">Updated</option>
                <option value="deleted">Deleted</option>
                <option value="viewed">Viewed</option>
                <option value="exported">Exported</option>
                <option value="imported">Imported</option>
                <option value="assigned">Assigned</option>
                <option value="returned">Returned</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
                <option value="forwarded">Forwarded</option>
                <option value="resolved">Resolved</option>
                <option value="backup">Backup</option>
            </select>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-6">
            <label class="form-label fw-bold small">Module</label>
            <select id="moduleFilter" class="form-select form-select-sm">
                <option value="">All Modules</option>
                <option value="auth">Authentication</option>
                <option value="user">User</option>
                <option value="role">Role</option>
                <option value="permission">Permission</option>
                <option value="product">Product</option>
                <option value="category">Category</option>
                <option value="supplier">Supplier</option>
                <option value="department">Department</option>
                <option value="assignment">Assignment</option>
                <option value="asset_request">Asset Request</option>
                <option value="direct_request">Direct Request</option>
                <option value="purchase_order">Purchase Order</option>
                <option value="procurement">Procurement</option>
                <option value="inspection">Inspection</option>
                <option value="maintenance">Maintenance</option>
                <option value="stock_movement">Stock Movement</option>
                <option value="settings">Settings</option>
                <option value="report">Report</option>
                <option value="backup">Backup</option>
            </select>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-6">
            <label class="form-label fw-bold small">User</label>
            <select id="userFilter" class="form-select form-select-sm">
                <option value="">All Users</option>
                @foreach($users ?? [] as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-6">
            <button class="btn btn-primary btn-sm w-100" id="applyFilters">
                <i class="fas fa-filter me-1"></i> Apply Filters
            </button>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- MAIN CARD -->
<!-- ============================================ -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fas fa-history text-primary me-2"></i>Activity Log</h6>
        <div class="d-flex gap-2 flex-wrap">
            @can('activity_log.delete')
            <button class="btn btn-danger btn-sm" id="clearLogsBtn">
                <i class="fas fa-trash me-1"></i> Clear Old Logs
            </button>
            @endcan
            @can('activity_log.export')
            <button class="btn btn-success btn-sm" id="exportLogsBtn">
                <i class="fas fa-file-excel me-1"></i> Export
            </button>
            @endcan
            <button class="btn btn-info btn-sm" id="refreshLogsBtn">
                <i class="fas fa-sync me-1"></i> Refresh
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0" id="logTable">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th style="min-width: 150px;">User</th>
                        <th style="min-width: 120px;">Action</th>
                        <th style="min-width: 150px;">Description</th>
                        <th style="width: 100px;">Module</th>
                        <th style="width: 80px;">Status</th>
                        <th style="width: 80px;">Duration</th>
                        <th style="width: 150px;">Time</th>
                        <th style="width: 60px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($logs ?? [] as $log)
                    <tr data-id="{{ $log->id }}">
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle bg-primary text-white me-2" style="width:28px;height:28px;font-size:0.6rem;">
                                    {{ strtoupper(substr($log->user_name ?? 'S', 0, 2)) }}
                                </div>
                                <div>
                                    <div class="fw-bold small">{{ $log->user_name ?? 'System' }}</div>
                                    <small class="text-muted">{{ $log->user_email ?? 'N/A' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-{{ $log->type_color ?? 'secondary' }}">
                                <i class="fas {{ $log->icon ?? 'fa-circle' }} me-1"></i>
                                {{ ucfirst($log->action) }}
                            </span>
                        </td>
                        <td>{{ Str::limit($log->description ?? 'N/A', 60) }}</td>
                        <td>
                            <span class="badge bg-secondary">{{ ucfirst($log->module) }}</span>
                        </td>
                        <td>
                            <span class="status-badge status-badge-{{ $log->status }}">
                                {{ ucfirst($log->status) }}
                            </span>
                        </td>
                        <td>{{ $log->duration_formatted ?? 'N/A' }}</td>
                        <td>
                            <small>{{ $log->performed_at ? $log->performed_at->format('M d, Y H:i:s') : 'N/A' }}</small>
                            <br>
                            <small class="text-muted">{{ $log->performed_at ? $log->performed_at->diffForHumans() : 'N/A' }}</small>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary view-log-btn" data-id="{{ $log->id }}" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                            <p class="text-muted">No activity logs found.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        <div class="row align-items-center g-3">
            <div class="col-md-4">
                <div class="text-muted small">
                    Showing 
                    <span class="fw-bold">{{ $logs->firstItem() ?? 0 }}</span>
                    to 
                    <span class="fw-bold">{{ $logs->lastItem() ?? 0 }}</span>
                    of 
                    <span class="fw-bold">{{ $logs->total() }}</span>
                    entries
                </div>
            </div>
            <div class="col-md-8">
                <div class="d-flex justify-content-md-end align-items-center gap-2 flex-wrap">
                    <div class="d-flex align-items-center gap-2">
                        <label class="text-muted small mb-0">Show:</label>
                        <select id="perPage" class="form-select form-select-sm" style="width: 70px;">
                            <option value="25" {{ ($logs->perPage() ?? 25) == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ ($logs->perPage() ?? 25) == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ ($logs->perPage() ?? 25) == 100 ? 'selected' : '' }}>100</option>
                            <option value="200" {{ ($logs->perPage() ?? 25) == 200 ? 'selected' : '' }}>200</option>
                        </select>
                    </div>
                    {{ $logs->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- VIEW LOG MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="viewLogModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 700px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-eye text-info me-2"></i> Activity Log Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewLogBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- CLEAR LOGS MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="clearLogsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i> Clear Old Logs
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="clearLogsForm" action="{{ route('admin.activity-logs.clear') }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        This action will permanently delete old activity logs. This cannot be undone.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Delete logs older than</label>
                        <select name="days" class="form-select">
                            <option value="7">7 days</option>
                            <option value="15">15 days</option>
                            <option value="30" selected>30 days</option>
                            <option value="60">60 days</option>
                            <option value="90">90 days</option>
                            <option value="180">180 days</option>
                            <option value="365">365 days</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Confirm action</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="confirmClear" required>
                            <label class="form-check-label small" for="confirmClear">
                                I understand that this action cannot be undone.
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-danger" id="clearLogsSubmitBtn" disabled>
                        <i class="fas fa-trash me-1"></i> Clear Logs
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- TOAST CONTAINER -->
<!-- ============================================ -->
<div id="toastContainer" class="position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>

@push('scripts')
<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- SheetJS for Excel Export -->
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

<script>
$(document).ready(function() {
    // ============================================
    // CSRF Setup
    // ============================================
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // ============================================
    // LOAD STATS
    // ============================================
    function loadStats() {
        $.ajax({
            url: '{{ route("admin.activity-logs.statistics") }}',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response) {
                    $('#totalActivities').text(response.total || 0);
                    $('#todayActivities').text(response.today || 0);
                    $('#thisWeekActivities').text(response.this_week || 0);
                    $('#thisMonthActivities').text(response.this_month || 0);
                }
            },
            error: function() {
                // Silent fail
            }
        });
    }

    loadStats();

    // ============================================
    // VIEW LOG DETAILS
    // ============================================
    $(document).on('click', '.view-log-btn', function() {
        var id = $(this).data('id');
        var body = $('#viewLogBody');

        body.html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `);
        $('#viewLogModal').modal('show');

        $.ajax({
            url: '/admin/activity-logs/' + id,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                var log = response.log || response;
                var oldData = response.old_data || {};
                var newData = response.new_data || {};

                var html = `
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">User</label>
                            <p class="fw-bold mb-0">${log.user_name || 'System'}</p>
                            <small class="text-muted">${log.user_email || 'N/A'}</small>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Role</label>
                            <p class="mb-0"><span class="badge bg-primary">${log.user_role || 'N/A'}</span></p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Action</label>
                            <p class="mb-0">
                                <span class="badge bg-${log.type_color || 'secondary'}">
                                    <i class="fas ${log.icon || 'fa-circle'} me-1"></i>
                                    ${log.action}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Module</label>
                            <p class="mb-0"><span class="badge bg-secondary">${log.module}</span></p>
                        </div>
                        <div class="col-12">
                            <label class="text-muted small fw-bold">Description</label>
                            <p class="mb-0">${log.description || 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Status</label>
                            <p class="mb-0">
                                <span class="status-badge status-badge-${log.status}">
                                    ${log.status}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Duration</label>
                            <p class="mb-0">${log.duration_formatted || 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">IP Address</label>
                            <p class="mb-0"><code>${log.ip_address || 'N/A'}</code></p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Method</label>
                            <p class="mb-0"><span class="badge bg-secondary">${log.method || 'N/A'}</span></p>
                        </div>
                        <div class="col-12">
                            <label class="text-muted small fw-bold">URL</label>
                            <p class="mb-0"><code class="text-break">${log.url || 'N/A'}</code></p>
                        </div>
                        <div class="col-12">
                            <label class="text-muted small fw-bold">User Agent</label>
                            <p class="mb-0 small text-muted">${log.user_agent || 'N/A'}</p>
                        </div>
                        ${Object.keys(oldData).length > 0 ? `
                        <div class="col-12">
                            <hr>
                            <h6 class="fw-bold text-danger"><i class="fas fa-arrow-left me-2"></i>Old Data</h6>
                            <pre class="bg-light p-2 rounded small">${JSON.stringify(oldData, null, 2)}</pre>
                        </div>` : ''}
                        ${Object.keys(newData).length > 0 ? `
                        <div class="col-12">
                            <hr>
                            <h6 class="fw-bold text-success"><i class="fas fa-arrow-right me-2"></i>New Data</h6>
                            <pre class="bg-light p-2 rounded small">${JSON.stringify(newData, null, 2)}</pre>
                        </div>` : ''}
                        <div class="col-12">
                            <hr>
                            <label class="text-muted small fw-bold">Performed At</label>
                            <p class="mb-0">${log.performed_at ? new Date(log.performed_at).toLocaleString() : 'N/A'}</p>
                        </div>
                    </div>
                `;
                body.html(html);
            },
            error: function(xhr) {
                body.html(`
                    <div class="text-center py-5 text-danger">
                        <i class="fas fa-exclamation-circle fa-4x mb-3 d-block"></i>
                        <h5>Failed to load log details</h5>
                        <p>${xhr.status} - ${xhr.statusText}</p>
                    </div>
                `);
            }
        });
    });

    // ============================================
    // APPLY FILTERS
    // ============================================
    $('#applyFilters').on('click', function() {
        applyFilters();
    });

    $('#dateFrom, #dateTo, #actionFilter, #moduleFilter, #userFilter').on('change', function() {
        applyFilters();
    });

    function applyFilters() {
        const dateFrom = $('#dateFrom').val();
        const dateTo = $('#dateTo').val();
        const action = $('#actionFilter').val();
        const module = $('#moduleFilter').val();
        const userId = $('#userFilter').val();
        const perPage = $('#perPage').val();

        let url = window.location.pathname + '?';
        if (dateFrom) url += 'date_from=' + dateFrom + '&';
        if (dateTo) url += 'date_to=' + dateTo + '&';
        if (action) url += 'action=' + action + '&';
        if (module) url += 'module=' + module + '&';
        if (userId) url += 'user_id=' + userId + '&';
        if (perPage) url += 'per_page=' + perPage + '&';

        window.location.href = url;
    }

    // ============================================
    // PER PAGE SELECTOR
    // ============================================
    $('#perPage').on('change', function() {
        const perPage = $(this).val();
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('per_page', perPage);
        window.location.href = currentUrl.toString();
    });

    // ============================================
    // CLEAR LOGS
    // ============================================
    @can('activity_log.delete')
    $('#clearLogsBtn').on('click', function() {
        $('#clearLogsModal').modal('show');
    });

    $('#confirmClear').on('change', function() {
        $('#clearLogsSubmitBtn').prop('disabled', !$(this).prop('checked'));
    });

    $('#clearLogsForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        var data = form.serialize();
        var btn = $('#clearLogsSubmitBtn');

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Clearing...');

        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#clearLogsModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Cleared!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: true
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    btn.prop('disabled', false).html('<i class="fas fa-trash me-1"></i> Clear Logs');
                    $('#confirmClear').prop('checked', false);
                    $('#clearLogsSubmitBtn').prop('disabled', true);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message || 'Failed to clear logs.'
                    });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-trash me-1"></i> Clear Logs');
                $('#confirmClear').prop('checked', false);
                $('#clearLogsSubmitBtn').prop('disabled', true);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to clear logs. Please try again.'
                });
            }
        });
    });
    @endcan

    // ============================================
    // EXPORT LOGS
    // ============================================
    @can('activity_log.export')
    $('#exportLogsBtn').on('click', function() {
        Swal.fire({
            title: 'Exporting Logs...',
            text: 'Please wait while we prepare the export.',
            allowOutsideClick: false,
            didOpen: function() {
                Swal.showLoading();
            }
        });

        const params = new URLSearchParams(window.location.search);
        params.set('export', 'true');

        $.ajax({
            url: window.location.pathname + '?' + params.toString(),
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                Swal.close();
                
                if (response && response.data) {
                    const wb = XLSX.utils.book_new();
                    const ws = XLSX.utils.aoa_to_sheet(response.data);
                    
                    ws['!cols'] = [
                        { wch: 5 }, { wch: 20 }, { wch: 25 }, { wch: 15 },
                        { wch: 15 }, { wch: 15 }, { wch: 20 }, { wch: 15 },
                        { wch: 20 }, { wch: 25 }, { wch: 15 }, { wch: 20 }
                    ];

                    XLSX.utils.book_append_sheet(wb, ws, 'Activity Logs');
                    const fileName = `activity_logs_${new Date().toISOString().slice(0,10)}.xlsx`;
                    XLSX.writeFile(wb, fileName);
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Exported!',
                        text: `Export completed. ${response.count || 0} records exported.`,
                        timer: 3000,
                        showConfirmButton: true
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'No data to export.'
                    });
                }
            },
            error: function(xhr) {
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to export logs. Please try again.'
                });
            }
        });
    });
    @endcan

    // ============================================
    // REFRESH LOGS
    // ============================================
    $('#refreshLogsBtn').on('click', function() {
        var btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Refreshing...');
        
        setTimeout(function() {
            window.location.reload();
        }, 500);
    });

    // ============================================
    // KEYBOARD SHORTCUTS
    // ============================================
    $(document).on('keydown', function(e) {
        if (e.ctrlKey && e.key === 'r') {
            e.preventDefault();
            window.location.reload();
        }
        if (e.ctrlKey && e.key === 'e') {
            e.preventDefault();
            @can('activity_log.export')
            $('#exportLogsBtn').click();
            @endcan
        }
    });

    // ============================================
    // RESET MODAL BACKDROP
    // ============================================
    $('.modal').on('hidden.bs.modal', function() {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
        $('#confirmClear').prop('checked', false);
        $('#clearLogsSubmitBtn').prop('disabled', true);
    });

    // ============================================
    // TOAST NOTIFICATIONS
    // ============================================
    function showToast(type, message) {
        const icons = { success: 'check-circle', error: 'exclamation-circle', warning: 'exclamation-triangle', info: 'info-circle' };
        const colors = { success: 'success', error: 'danger', warning: 'warning', info: 'info' };
        
        const toast = $(`
            <div class="toast show" role="alert" style="min-width:300px;">
                <div class="toast-header bg-${colors[type]} text-white">
                    <strong class="me-auto"><i class="fas fa-${icons[type]} me-2"></i> ${type.charAt(0).toUpperCase() + type.slice(1)}</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">${message}</div>
            </div>
        `);
        $('#toastContainer').append(toast);
        setTimeout(function() { toast.remove(); }, 5000);
    }

    console.log('Activity Logs page loaded successfully!');
    console.log('Keyboard shortcuts: Ctrl+R (Refresh), Ctrl+E (Export)');
});
</script>
@endpush
@endsection