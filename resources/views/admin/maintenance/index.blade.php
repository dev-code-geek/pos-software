@extends('layouts.admin')

@section('title', 'Maintenance')
@section('page-title', 'Maintenance Management')

@section('admin-content')
<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ $stats['total'] ?? $logs->total() ?? 0 }}</h3>
                <p>Total Records</p>
            </div>
            <div class="icon">
                <i class="fas fa-tools"></i>
            </div>
            <a href="{{ route('admin.maintenance.index') }}" class="small-box-footer">
                View All <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ ($stats['reported'] ?? 0) + ($stats['in_progress'] ?? 0) }}</h3>
                <p>Pending Repair</p>
            </div>
            <div class="icon">
                <i class="fas fa-clock"></i>
            </div>
            <a href="{{ route('admin.maintenance.index', ['status' => 'reported']) }}" class="small-box-footer">
                View Pending <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $stats['fixed'] ?? 0 }}</h3>
                <p>Fixed / Resolved</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <a href="{{ route('admin.maintenance.index', ['status' => 'fixed']) }}" class="small-box-footer">
                View Fixed <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $stats['unrepairable'] ?? 0 }}</h3>
                <p>Unrepairable</p>
            </div>
            <div class="icon">
                <i class="fas fa-times-circle"></i>
            </div>
            <a href="{{ route('admin.maintenance.index', ['status' => 'unrepairable']) }}" class="small-box-footer">
                View Unrepairable <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Main Card -->
<div class="card">
    <div class="card-header">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <h5 class="card-title mb-0">
                <i class="fas fa-tools text-primary me-2"></i>
                Maintenance Logs
                <span class="badge bg-primary ms-1">{{ $logs->total() }}</span>
            </h5>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <div class="input-group input-group-sm" style="width: 200px;">
                <input type="text" id="searchInput" class="form-control" placeholder="Search...">
                <button class="btn btn-secondary" type="button" id="searchBtn">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            <select id="statusFilter" class="form-select form-select-sm" style="width: 130px;">
                <option value="">All Status</option>
                <option value="reported">Reported</option>
                <option value="in_progress">In Progress</option>
                <option value="fixed">Fixed</option>
                <option value="unrepairable">Unrepairable</option>
            </select>
            <select id="priorityFilter" class="form-select form-select-sm" style="width: 120px;">
                <option value="">All Priority</option>
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
                <option value="critical">Critical</option>
            </select>
            @can('maintenance.create')
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fas fa-plus-circle me-1"></i> Report Issue
            </button>
            @endcan
            @can('maintenance.view')
            <a href="{{ route('admin.maintenance.export') }}" class="btn btn-success btn-sm">
                <i class="fas fa-file-excel me-1"></i> Export
            </a>
            @endcan
            @can('maintenance.delete')
            <button class="btn btn-danger btn-sm" id="bulkDeleteBtn">
                <i class="fas fa-trash me-1"></i> Bulk Delete
            </button>
            @endcan
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        @can('maintenance.delete')
                        <th style="width: 35px;">
                            <input type="checkbox" id="selectAll">
                        </th>
                        @endcan
                        <th style="width: 35px;">#</th>
                        <th style="min-width: 140px;">Asset</th>
                        <th style="min-width: 140px;">Issue</th>
                        <th>Priority</th>
                        <th>Reported By</th>
                        <th style="width: 100px;">Date</th>
                        <th style="width: 100px;">Status</th>
                        <th style="width: 180px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($logs as $log)
                    <tr data-id="{{ $log->id }}" data-status="{{ $log->status }}" data-priority="{{ $log->priority }}">
                        @can('maintenance.delete')
                        <td>
                            <input type="checkbox" class="row-checkbox" value="{{ $log->id }}">
                        </td>
                        @endcan
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle bg-primary text-white me-2">
                                    <i class="fas fa-box"></i>
                                </div>
                                <div>
                                    <div class="fw-bold small">{{ Str::limit($log->product->name ?? 'N/A', 20) }}</div>
                                    <small class="text-muted">{{ $log->product->sku ?? 'N/A' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="fw-bold small">{{ Str::limit($log->issue_description, 30) }}</div>
                            @if($log->cost)
                            <small class="text-muted">Cost: ${{ number_format($log->cost, 2) }}</small>
                            @endif
                        </td>
                        <td>
                            @php
                                $priorityColors = [
                                    'critical' => 'danger',
                                    'high' => 'warning text-dark',
                                    'medium' => 'info',
                                    'low' => 'secondary'
                                ];
                            @endphp
                            <span class="badge bg-{{ $priorityColors[$log->priority] ?? 'secondary' }}">
                                <i class="fas fa-{{ $log->priority == 'critical' ? 'exclamation-circle' : ($log->priority == 'high' ? 'arrow-up' : ($log->priority == 'medium' ? 'minus' : 'arrow-down')) }} me-1"></i>
                                {{ ucfirst($log->priority) }}
                            </span>
                        </td>
                        <td>
                            <small>{{ $log->user->name ?? 'N/A' }}</small>
                        </td>
                        <td>
                            <small>{{ $log->created_at->format('M d, Y') }}</small>
                            <br>
                            <small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                        </td>
                        <td>
                            @php
                                $statusClasses = [
                                    'reported' => 'bg-warning text-dark',
                                    'in_progress' => 'bg-info text-white',
                                    'fixed' => 'bg-success text-white',
                                    'unrepairable' => 'bg-danger text-white'
                                ];
                            @endphp
                            <span class="badge {{ $statusClasses[$log->status] ?? 'bg-secondary' }}">
                                {{ str_replace('_', ' ', ucfirst($log->status)) }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                @can('maintenance.view')
                                <button class="btn btn-outline-primary btn-sm view-btn" data-id="{{ $log->id }}" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @endcan
                                @can('maintenance.edit')
                                <button class="btn btn-outline-warning btn-sm edit-btn" data-id="{{ $log->id }}" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @endcan
                                @if(in_array($log->status, ['reported', 'in_progress']))
                                    @can('maintenance.edit')
                                    <button class="btn btn-outline-success btn-sm resolve-btn" data-id="{{ $log->id }}" title="Resolve Issue">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    @endcan
                                    @can('maintenance.edit')
                                    <button class="btn btn-outline-danger btn-sm unrepairable-btn" data-id="{{ $log->id }}" title="Mark Unrepairable">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    @endcan
                                @endif
                                @can('maintenance.delete')
                                <button class="btn btn-outline-danger btn-sm delete-btn" data-id="{{ $log->id }}" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ auth()->user()->can('maintenance.delete') ? 9 : 8 }}">
                            <div class="text-center py-5">
                                <i class="fas fa-tools fa-4x text-muted mb-3 d-block"></i>
                                <h5 class="fw-bold">No Maintenance Records</h5>
                                <p class="text-muted">Click "Report Issue" to create your first maintenance record.</p>
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
                Showing {{ $logs->firstItem() ?? 0 }} to {{ $logs->lastItem() ?? 0 }} of {{ $logs->total() }} entries
            </span>
        </div>
        <div>
            {{ $logs->links() }}
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- CREATE MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle text-primary me-2"></i> Report Maintenance Issue
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createForm" action="{{ route('admin.maintenance.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div id="createFormErrors" class="alert alert-danger d-none"></div>
                    <div class="mb-3">
                        <label for="log_product" class="form-label fw-bold">Product <span class="text-danger">*</span></label>
                        <select name="product_id" id="log_product" class="form-select" required>
                            <option value="">Select Product</option>
                            @foreach($products ?? [] as $product)
                                <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="log_issue" class="form-label fw-bold">Issue Description <span class="text-danger">*</span></label>
                        <textarea name="issue_description" id="log_issue" class="form-control" rows="3" required placeholder="Describe the issue..."></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="log_priority" class="form-label fw-bold">Priority <span class="text-danger">*</span></label>
                            <select name="priority" id="log_priority" class="form-select" required>
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="log_status" class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                            <select name="status" id="log_status" class="form-select" required>
                                <option value="reported" selected>Reported</option>
                                <option value="in_progress">In Progress</option>
                                <option value="fixed">Fixed</option>
                                <option value="unrepairable">Unrepairable</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3 mt-3">
                        <label for="log_repaired_by" class="form-label fw-bold">Repaired By</label>
                        <input type="text" name="repaired_by" id="log_repaired_by" class="form-control" placeholder="Name of repairer">
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="log_repair_date" class="form-label fw-bold">Repair Date</label>
                            <input type="date" name="repair_date" id="log_repair_date" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label for="log_cost" class="form-label fw-bold">Cost</label>
                            <input type="number" name="cost" id="log_cost" class="form-control" step="0.01" min="0" placeholder="0.00">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="createSubmitBtn">
                        <i class="fas fa-save me-1"></i> Report Issue
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- VIEW MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-eye text-info me-2"></i> Maintenance Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewModalBody">
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
<!-- EDIT MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit text-warning me-2"></i> Edit Maintenance Record
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body" id="editModalBody">
                    <div id="editFormErrors" class="alert alert-danger d-none"></div>
                    <div class="text-center py-5">
                        <div class="spinner-border text-warning" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-warning" id="editSubmitBtn">
                        <i class="fas fa-save me-1"></i> Update Record
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- RESOLVE MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="resolveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-success">
                    <i class="fas fa-check-circle me-2"></i> Resolve Maintenance Issue
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="resolveForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div id="resolveFormErrors" class="alert alert-danger d-none"></div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        This will mark the issue as resolved/fixed.
                    </div>
                    <div class="mb-3">
                        <label for="resolve_resolved_date" class="form-label fw-bold">Resolved Date <span class="text-danger">*</span></label>
                        <input type="date" name="resolved_date" id="resolve_resolved_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="resolve_cost" class="form-label fw-bold">Cost</label>
                        <input type="number" name="cost" id="resolve_cost" class="form-control" step="0.01" min="0" placeholder="0.00">
                    </div>
                    <div class="mb-3">
                        <label for="resolve_notes" class="form-label fw-bold">Notes</label>
                        <textarea name="notes" id="resolve_notes" class="form-control" rows="2" placeholder="Additional notes (optional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-success" id="resolveSubmitBtn">
                        <i class="fas fa-check me-1"></i> Resolve Issue
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- UNREPAIRABLE MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="unrepairableModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">
                    <i class="fas fa-times-circle me-2"></i> Mark as Unrepairable
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="unrepairableForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div id="unrepairableFormErrors" class="alert alert-danger d-none"></div>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        This will mark the product as unrepairable and retire it.
                    </div>
                    <div class="mb-3">
                        <label for="unrepairable_reason" class="form-label fw-bold text-danger">Reason <span class="text-danger">*</span></label>
                        <textarea name="reason" id="unrepairable_reason" class="form-control" rows="3" required placeholder="Please provide a reason..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-danger" id="unrepairableSubmitBtn">
                        <i class="fas fa-times me-1"></i> Mark Unrepairable
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- DELETE CONFIRM MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i> Confirm Delete
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-trash fa-4x text-danger mb-3 d-block"></i>
                <p class="fw-bold">Are you sure you want to delete this maintenance record?</p>
                <p class="text-muted small">This action cannot be undone.</p>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <button type="submit" class="btn btn-danger" id="deleteSubmitBtn" form="deleteForm">
                    <i class="fas fa-trash me-1"></i> Delete
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
$(document).ready(function() {
    // ==========================================
    // CSRF Setup
    // ==========================================
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // ==========================================
    // HELPER: Show Errors
    // ==========================================
    function showErrors(container, errors) {
        container.removeClass('d-none');
        var html = '<ul class="mb-0">';
        $.each(errors, function(key, messages) {
            $.each(messages, function(index, msg) {
                html += '<li>' + msg + '</li>';
            });
        });
        html += '</ul>';
        container.html(html);
        container[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function clearErrors(container) {
        container.addClass('d-none').empty();
    }

    // ==========================================
    // BULK SELECT
    // ==========================================
    @can('maintenance.delete')
    $('#selectAll').on('change', function() {
        $('.row-checkbox').prop('checked', $(this).prop('checked'));
    });

    $('#bulkDeleteBtn').on('click', function() {
        var selected = $('.row-checkbox:checked');
        if (selected.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Items Selected',
                text: 'Please select at least one record to delete.'
            });
            return;
        }

        Swal.fire({
            title: 'Delete Selected Records?',
            text: 'You are about to delete ' + selected.length + ' record(s). This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete them!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                var ids = [];
                selected.each(function() {
                    ids.push($(this).val());
                });

                Swal.fire({
                    title: 'Deleting...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: '{{ route("admin.maintenance.bulk-delete") }}',
                    type: 'POST',
                    data: {
                        maintenance_ids: ids
                    },
                    dataType: 'json',
                    success: function(r) {
                        if (r.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: r.message,
                                timer: 2000,
                                showConfirmButton: true
                            }).then(() => { window.location.reload(); });
                        }
                    },
                    error: function(xhr) {
                        Swal.close();
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Failed to delete selected records.'
                        });
                    }
                });
            }
        });
    });
    @endcan

    // ==========================================
    // VIEW
    // ==========================================
    $(document).on('click', '.view-btn', function() {
        var id = $(this).data('id');
        var body = $('#viewModalBody');

        body.html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `);
        $('#viewModal').modal('show');

        $.ajax({
            url: '/admin/maintenance/' + id,
            type: 'GET',
            dataType: 'json',
            success: function(r) {
                var html = `
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="text-muted small fw-bold">Product</label>
                            <p class="fw-bold mb-0">${r.product ? r.product.name : 'N/A'}</p>
                        </div>
                        <div class="col-12">
                            <label class="text-muted small fw-bold">Issue Description</label>
                            <p class="mb-0">${r.issue_description || 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Priority</label>
                            <p class="mb-0">
                                <span class="badge bg-${r.priority == 'critical' ? 'danger' : (r.priority == 'high' ? 'warning text-dark' : (r.priority == 'medium' ? 'info' : 'secondary'))}">
                                    ${r.priority ? r.priority.charAt(0).toUpperCase() + r.priority.slice(1) : 'N/A'}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Status</label>
                            <p class="mb-0">
                                <span class="badge bg-${r.status == 'reported' ? 'warning text-dark' : (r.status == 'in_progress' ? 'info' : (r.status == 'fixed' ? 'success' : 'danger'))}">
                                    ${r.status ? r.status.replace(/_/g, ' ').toUpperCase() : 'N/A'}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Reported By</label>
                            <p class="mb-0">${r.user ? r.user.name : 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Reported Date</label>
                            <p class="mb-0">${r.created_at ? new Date(r.created_at).toLocaleString() : 'N/A'}</p>
                        </div>
                        ${r.repaired_by ? `
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Repaired By</label>
                            <p class="mb-0">${r.repaired_by}</p>
                        </div>` : ''}
                        ${r.repair_date ? `
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Repair Date</label>
                            <p class="mb-0">${new Date(r.repair_date).toLocaleDateString()}</p>
                        </div>` : ''}
                        ${r.resolved_date ? `
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Resolved Date</label>
                            <p class="mb-0">${new Date(r.resolved_date).toLocaleDateString()}</p>
                        </div>` : ''}
                        ${r.cost ? `
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Cost</label>
                            <p class="mb-0">$${parseFloat(r.cost).toFixed(2)}</p>
                        </div>` : ''}
                        ${r.notes ? `
                        <div class="col-12">
                            <label class="text-muted small fw-bold">Notes</label>
                            <p class="mb-0">${r.notes}</p>
                        </div>` : ''}
                    </div>
                `;
                body.html(html);
            },
            error: function(xhr) {
                body.html(`
                    <div class="text-center py-5 text-danger">
                        <i class="fas fa-exclamation-circle fa-4x mb-3 d-block"></i>
                        <h5>Failed to load maintenance details</h5>
                        <p>${xhr.status} - ${xhr.statusText}</p>
                    </div>
                `);
            }
        });
    });

    // ==========================================
    // EDIT
    // ==========================================
    $(document).on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        var body = $('#editModalBody');
        var form = $('#editForm');

        form.attr('action', '/admin/maintenance/' + id);
        body.html(`
            <div id="editFormErrors" class="alert alert-danger d-none"></div>
            <div class="text-center py-5">
                <div class="spinner-border text-warning" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `);
        $('#editModal').modal('show');

        $.ajax({
            url: '/admin/maintenance/' + id + '/edit',
            type: 'GET',
            dataType: 'json',
            success: function(r) {
                body.html(`
                    <div id="editFormErrors" class="alert alert-danger d-none"></div>
                    <div class="mb-3">
                        <label for="edit_product" class="form-label fw-bold">Product <span class="text-danger">*</span></label>
                        <select name="product_id" id="edit_product" class="form-select" required>
                            <option value="">Select Product</option>
                            @foreach($products ?? [] as $product)
                                <option value="{{ $product->id }}" ${r.product_id == {{ $product->id }} ? 'selected' : ''}>
                                    {{ $product->name }} ({{ $product->sku }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_issue" class="form-label fw-bold">Issue Description <span class="text-danger">*</span></label>
                        <textarea name="issue_description" id="edit_issue" class="form-control" rows="3" required>${r.issue_description || ''}</textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="edit_priority" class="form-label fw-bold">Priority <span class="text-danger">*</span></label>
                            <select name="priority" id="edit_priority" class="form-select" required>
                                <option value="low" ${r.priority == 'low' ? 'selected' : ''}>Low</option>
                                <option value="medium" ${r.priority == 'medium' ? 'selected' : ''}>Medium</option>
                                <option value="high" ${r.priority == 'high' ? 'selected' : ''}>High</option>
                                <option value="critical" ${r.priority == 'critical' ? 'selected' : ''}>Critical</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_status" class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                            <select name="status" id="edit_status" class="form-select" required>
                                <option value="reported" ${r.status == 'reported' ? 'selected' : ''}>Reported</option>
                                <option value="in_progress" ${r.status == 'in_progress' ? 'selected' : ''}>In Progress</option>
                                <option value="fixed" ${r.status == 'fixed' ? 'selected' : ''}>Fixed</option>
                                <option value="unrepairable" ${r.status == 'unrepairable' ? 'selected' : ''}>Unrepairable</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3 mt-3">
                        <label for="edit_repaired_by" class="form-label fw-bold">Repaired By</label>
                        <input type="text" name="repaired_by" id="edit_repaired_by" class="form-control" value="${r.repaired_by || ''}" placeholder="Name of repairer">
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="edit_repair_date" class="form-label fw-bold">Repair Date</label>
                            <input type="date" name="repair_date" id="edit_repair_date" class="form-control" value="${r.repair_date ? r.repair_date.substring(0, 10) : ''}">
                        </div>
                        <div class="col-md-6">
                            <label for="edit_cost" class="form-label fw-bold">Cost</label>
                            <input type="number" name="cost" id="edit_cost" class="form-control" step="0.01" min="0" value="${r.cost || ''}" placeholder="0.00">
                        </div>
                    </div>
                    ${r.resolved_date ? `
                    <div class="mb-3 mt-2">
                        <label for="edit_resolved_date" class="form-label fw-bold">Resolved Date</label>
                        <input type="date" name="resolved_date" id="edit_resolved_date" class="form-control" value="${r.resolved_date ? r.resolved_date.substring(0, 10) : ''}">
                    </div>` : ''}
                `);
            },
            error: function(xhr) {
                body.html(`
                    <div id="editFormErrors" class="alert alert-danger d-none"></div>
                    <div class="text-center py-5 text-danger">
                        <i class="fas fa-exclamation-circle fa-4x mb-3 d-block"></i>
                        <h5>Failed to load maintenance record</h5>
                        <p>${xhr.status} - ${xhr.statusText}</p>
                    </div>
                `);
            }
        });
    });

    // ==========================================
    // CREATE
    // ==========================================
    $('#createForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        var data = form.serialize();
        var btn = $('#createSubmitBtn');
        var errorContainer = $('#createFormErrors');

        clearErrors(errorContainer);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Reporting...');

        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(r) {
                if (r.success) {
                    $('#createModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Reported!',
                        text: r.message || 'Maintenance issue reported successfully.',
                        timer: 2000,
                        showConfirmButton: true
                    }).then(() => { window.location.reload(); });
                } else {
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Report Issue');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: r.message || 'Something went wrong.',
                    });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Report Issue');
                if (xhr.status === 422) {
                    showErrors(errorContainer, xhr.responseJSON.errors);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to report issue. Please try again.'
                    });
                }
            }
        });
    });

    // ==========================================
    // UPDATE
    // ==========================================
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
            success: function(r) {
                if (r.success) {
                    $('#editModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated!',
                        text: r.message || 'Maintenance record updated successfully.',
                        timer: 2000,
                        showConfirmButton: true
                    }).then(() => { window.location.reload(); });
                } else {
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Update Record');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: r.message || 'Something went wrong.',
                    });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Update Record');
                if (xhr.status === 422) {
                    showErrors(errorContainer, xhr.responseJSON.errors);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to update record. Please try again.'
                    });
                }
            }
        });
    });

    // ==========================================
    // RESOLVE
    // ==========================================
    $(document).on('click', '.resolve-btn', function() {
        var id = $(this).data('id');
        var form = $('#resolveForm');
        form.attr('action', '/admin/maintenance/' + id + '/resolve');
        clearErrors($('#resolveFormErrors'));
        $('#resolveModal').modal('show');
    });

    $('#resolveForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        var data = form.serialize();
        var btn = $('#resolveSubmitBtn');
        var errorContainer = $('#resolveFormErrors');

        clearErrors(errorContainer);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Resolving...');

        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(r) {
                if (r.success) {
                    $('#resolveModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Resolved!',
                        text: r.message,
                        timer: 2000,
                        showConfirmButton: true
                    }).then(() => { window.location.reload(); });
                } else {
                    btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i> Resolve Issue');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: r.message || 'Failed to resolve issue.',
                    });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i> Resolve Issue');
                if (xhr.status === 422) {
                    showErrors(errorContainer, xhr.responseJSON.errors);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to resolve issue. Please try again.'
                    });
                }
            }
        });
    });

    // ==========================================
    // UNREPAIRABLE
    // ==========================================
    $(document).on('click', '.unrepairable-btn', function() {
        var id = $(this).data('id');
        var form = $('#unrepairableForm');
        form.attr('action', '/admin/maintenance/' + id + '/unrepairable');
        clearErrors($('#unrepairableFormErrors'));
        $('#unrepairableModal').modal('show');
    });

    $('#unrepairableForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        var data = form.serialize();
        var btn = $('#unrepairableSubmitBtn');
        var errorContainer = $('#unrepairableFormErrors');

        clearErrors(errorContainer);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Processing...');

        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(r) {
                if (r.success) {
                    $('#unrepairableModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Marked!',
                        text: r.message,
                        timer: 2000,
                        showConfirmButton: true
                    }).then(() => { window.location.reload(); });
                } else {
                    btn.prop('disabled', false).html('<i class="fas fa-times me-1"></i> Mark Unrepairable');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: r.message || 'Failed to mark as unrepairable.',
                    });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-times me-1"></i> Mark Unrepairable');
                if (xhr.status === 422) {
                    showErrors(errorContainer, xhr.responseJSON.errors);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to mark as unrepairable. Please try again.'
                    });
                }
            }
        });
    });

    // ==========================================
    // DELETE
    // ==========================================
    $(document).on('click', '.delete-btn', function() {
        var id = $(this).data('id');
        var form = $('#deleteForm');
        form.attr('action', '/admin/maintenance/' + id);

        Swal.fire({
            title: 'Are you sure?',
            text: "This maintenance record will be permanently deleted.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#deleteModal').modal('show');
            }
        });
    });

    $('#deleteForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');

        Swal.fire({
            title: 'Deleting...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        $.ajax({
            url: url,
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
            dataType: 'json',
            success: function(r) {
                if (r.success) {
                    $('#deleteModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: r.message,
                        timer: 2000,
                        showConfirmButton: true
                    }).then(() => { window.location.reload(); });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: r.message || 'Failed to delete record.',
                    });
                }
            },
            error: function(xhr) {
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to delete record. Please try again.'
                });
            }
        });
    });

    // ==========================================
    // SEARCH & FILTER
    // ==========================================
    $('#searchBtn').on('click', function() { filterTable(); });
    $('#searchInput').on('keypress', function(e) {
        if (e.which === 13) filterTable();
    });
    $('#statusFilter').on('change', function() { filterTable(); });
    $('#priorityFilter').on('change', function() { filterTable(); });

    function filterTable() {
        var search = $('#searchInput').val().toLowerCase();
        var status = $('#statusFilter').val();
        var priority = $('#priorityFilter').val();

        $('#tableBody tr').each(function() {
            var row = $(this);
            var text = row.text().toLowerCase();
            var rowStatus = row.data('status');
            var rowPriority = row.data('priority');

            var show = true;
            if (search && !text.includes(search)) show = false;
            if (status && rowStatus !== status) show = false;
            if (priority && rowPriority !== priority) show = false;

            row.toggle(show);
        });
    }

    // ==========================================
    // FIX MODAL BACKDROP
    // ==========================================
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