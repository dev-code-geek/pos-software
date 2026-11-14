@extends('layouts.admin')

@section('title', 'Software License Management')
@section('page-title', 'Software License Management')

@section('admin-content')
<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ $stats['total'] ?? 0 }}</h3>
                <p>Total Licenses</p>
            </div>
            <div class="icon">
                <i class="fas fa-key"></i>
            </div>
            <a href="{{ route('admin.software-licenses.index') }}" class="small-box-footer">
                View All <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $stats['active'] ?? 0 }}</h3>
                <p>Active Licenses</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <a href="{{ route('admin.software-licenses.index', ['status' => 'active']) }}" class="small-box-footer">
                View Active <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-warning text-dark">
            <div class="inner">
                <h3>{{ $stats['assigned_to_user'] ?? 0 }}</h3>
                <p>Assigned to Users</p>
            </div>
            <div class="icon">
                <i class="fas fa-user"></i>
            </div>
            <a href="{{ route('admin.software-licenses.index', ['assigned_to_type' => 'user']) }}" class="small-box-footer">
                View Users <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $stats['assigned_to_facility'] ?? 0 }}</h3>
                <p>Assigned to Facilities</p>
            </div>
            <div class="icon">
                <i class="fas fa-building"></i>
            </div>
            <a href="{{ route('admin.software-licenses.index', ['assigned_to_type' => 'facility']) }}" class="small-box-footer">
                View Facilities <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Main Card -->
<div class="card">
    <div class="card-header">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <h5 class="card-title mb-0">
                <i class="fas fa-key text-primary me-2"></i>
                Software Licenses
                <span class="badge bg-primary ms-1">{{ $licenses->total() }}</span>
            </h5>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <div class="input-group input-group-sm" style="width: 180px;">
                <input type="text" id="searchInput" class="form-control" placeholder="Search...">
                <button class="btn btn-secondary" type="button" id="searchBtn">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            <select id="typeFilter" class="form-select form-select-sm" style="width: 130px;">
                <option value="">All Types</option>
                <option value="user">Users</option>
                <option value="facility">Facilities</option>
            </select>
            <select id="statusFilter" class="form-select form-select-sm" style="width: 120px;">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="revoked">Revoked</option>
            </select>
            <select id="productFilter" class="form-select form-select-sm" style="width: 150px;">
                <option value="">All Products</option>
                @foreach($products ?? [] as $product)
                    <option value="{{ $product->id }}">{{ Str::limit($product->name, 20) }}</option>
                @endforeach
            </select>
            @can('license.create')
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fas fa-plus-circle me-1"></i> Assign License
            </button>
            @endcan
            @can('license.view')
            <a href="{{ route('admin.software-licenses.export') }}" class="btn btn-success btn-sm">
                <i class="fas fa-file-excel me-1"></i> Export
            </a>
            @endcan
            @can('license.revoke')
            <button class="btn btn-warning btn-sm" id="bulkRevokeBtn">
                <i class="fas fa-undo me-1"></i> Bulk Revoke
            </button>
            @endcan
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        @can('license.revoke')
                        <th style="width: 35px;">
                            <input type="checkbox" id="selectAll">
                        </th>
                        @endcan
                        <th style="width: 35px;">#</th>
                        <th style="min-width: 150px;">Product</th>
                        <th style="min-width: 160px;">Assigned To</th>
                        <th style="min-width: 120px;">License Key</th>
                        <th style="width: 100px;">Assigned Date</th>
                        <th style="width: 100px;">Revoked Date</th>
                        <th style="width: 100px;">Status</th>
                        <th style="width: 180px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($licenses as $license)
                    <tr data-id="{{ $license->id }}"
                        data-type="{{ $license->assigned_to_type }}"
                        data-status="{{ $license->revoked_at ? 'revoked' : 'active' }}"
                        data-product="{{ $license->product_id }}">
                        @can('license.revoke')
                        <td>
                            <input type="checkbox" class="row-checkbox" value="{{ $license->id }}"
                                   {{ $license->revoked_at ? 'disabled' : '' }}>
                        </td>
                        @endcan
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle bg-info text-white me-2">
                                    <i class="fas fa-cube"></i>
                                </div>
                                <div>
                                    <div class="fw-bold small">{{ Str::limit($license->product->name ?? 'N/A', 25) }}</div>
                                    <small class="text-muted">{{ $license->product->sku ?? '' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle {{ $license->assigned_to_type === 'user' ? 'bg-success' : 'bg-warning text-dark' }} text-white me-2"
                                     style="width:32px;height:32px;font-size:0.6rem;">
                                    <i class="fas {{ $license->assigned_to_type === 'user' ? 'fa-user' : 'fa-building' }}"></i>
                                </div>
                                <div>
                                    <div class="fw-bold small">{{ $license->assignee_name }}</div>
                                    <small class="text-muted">{{ $license->assignee_type_label }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <code class="small bg-light px-2 py-1 rounded">{{ $license->license_key }}</code>
                        </td>
                        <td>
                            <small>{{ $license->assigned_at ? $license->assigned_at->format('M d, Y') : 'N/A' }}</small>
                        </td>
                        <td>
                            <small>{{ $license->revoked_at ? $license->revoked_at->format('M d, Y') : '—' }}</small>
                        </td>
                        <td>
                            @if($license->revoked_at)
                                <span class="badge bg-secondary">Revoked</span>
                            @else
                                <span class="badge bg-success">Active</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                @can('license.view')
                                <button class="btn btn-outline-primary btn-sm view-btn" data-id="{{ $license->id }}" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @endcan
                                @can('license.edit')
                                <button class="btn btn-outline-warning btn-sm edit-btn" data-id="{{ $license->id }}" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @endcan
                                @if(!$license->revoked_at && auth()->user()->can('license.revoke'))
                                    <button class="btn btn-outline-danger btn-sm revoke-btn" data-id="{{ $license->id }}" title="Revoke">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                @endif
                                @can('license.delete')
                                <button class="btn btn-outline-danger btn-sm delete-btn" data-id="{{ $license->id }}" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ auth()->user()->can('license.revoke') ? 9 : 8 }}">
                            <div class="text-center py-5">
                                <i class="fas fa-key fa-4x text-muted mb-3 d-block"></i>
                                <h5 class="fw-bold">No Software Licenses Found</h5>
                                <p class="text-muted">Click "Assign License" to assign your first software license.</p>
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
                Showing {{ $licenses->firstItem() ?? 0 }} to {{ $licenses->lastItem() ?? 0 }} of {{ $licenses->total() }} entries
            </span>
        </div>
        <div>
            {{ $licenses->links() }}
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
                    <i class="fas fa-plus-circle text-primary me-2"></i> Assign Software License
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createForm" action="{{ route('admin.software-licenses.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div id="createFormErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Product <span class="text-danger">*</span></label>
                            <select name="product_id" class="form-select" required>
                                <option value="">Select Software</option>
                                @foreach($products ?? [] as $product)
                                    <option value="{{ $product->id }}" data-available="{{ $product->available_licenses }}">
                                        {{ $product->name }} ({{ $product->available_licenses }} available)
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Only products with available licenses are shown.</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Assign To <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input assign-type-radio" type="radio" name="assigned_to_type" id="assignToUser" value="user" checked>
                                    <label class="form-check-label" for="assignToUser">
                                        <i class="fas fa-user text-success"></i> User
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input assign-type-radio" type="radio" name="assigned_to_type" id="assignToFacility" value="facility">
                                    <label class="form-check-label" for="assignToFacility">
                                        <i class="fas fa-building text-warning"></i> Facility
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-12" id="userSelectContainer">
                            <label class="form-label fw-bold">User <span class="text-danger">*</span></label>
                            <select name="user_id" class="form-select">
                                <option value="">Select User</option>
                                @foreach($users ?? [] as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 d-none" id="facilitySelectContainer">
                            <label class="form-label fw-bold">Facility <span class="text-danger">*</span></label>
                            <select name="facility_id" class="form-select">
                                <option value="">Select Facility</option>
                                @foreach($facilities ?? [] as $facility)
                                    <option value="{{ $facility->id }}">{{ $facility->name }} ({{ $facility->code ?? 'N/A' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">License Key <span class="text-danger">*</span></label>
                            <input type="text" name="license_key" class="form-control" placeholder="Enter license key (e.g., XYZ-123-456)" required>
                            <small class="text-muted">Must be unique. Example: MS-001-2024</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Assigned Date <span class="text-danger">*</span></label>
                            <input type="date" name="assigned_at" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="createSubmitBtn">
                        <i class="fas fa-save me-1"></i> Assign License
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
                    <i class="fas fa-eye text-info me-2"></i> License Details
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
                    <i class="fas fa-edit text-warning me-2"></i> Edit License
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
                        <i class="fas fa-save me-1"></i> Update License
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- REVOKE MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="revokeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">
                    <i class="fas fa-undo me-2"></i> Revoke License
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="revokeForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div id="revokeFormErrors" class="alert alert-danger d-none"></div>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> Revoking a license will make it available for reassignment.
                    </div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Revoked Date <span class="text-danger">*</span></label>
                            <input type="date" name="revoked_at" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Reason (Optional)</label>
                            <input type="text" name="revoke_reason" class="form-control" placeholder="Why is this license being revoked?">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-danger" id="revokeSubmitBtn">
                        <i class="fas fa-undo me-1"></i> Revoke License
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
                <p class="fw-bold">Are you sure you want to delete this license record?</p>
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

<!-- ============================================ -->
<!-- BULK REVOKE CONFIRM MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="bulkRevokeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-warning">
                    <i class="fas fa-undo me-2"></i> Bulk Revoke Licenses
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="bulkRevokeForm" method="POST" action="{{ route('admin.software-licenses.bulk-revoke') }}">
                @csrf
                <div class="modal-body">
                    <div id="bulkRevokeFormErrors" class="alert alert-danger d-none"></div>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> You are about to revoke <span id="bulkRevokeCount">0</span> license(s).
                    </div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Revoked Date <span class="text-danger">*</span></label>
                            <input type="date" name="revoked_at" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                    <input type="hidden" name="license_ids" id="bulkRevokeIds">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-warning" id="bulkRevokeSubmitBtn">
                        <i class="fas fa-undo me-1"></i> Revoke Selected
                    </button>
                </div>
            </form>
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
.small-box.bg-warning .inner { color: #212529; }
.small-box.bg-warning .small-box-footer { color: rgba(33,37,41,0.8); }
.small-box.bg-warning .small-box-footer:hover { color: #212529; }
</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
    // TOGGLE USER/FACILITY FIELDS (CREATE MODAL)
    // FIX: previously only hid the inactive field and dropped its
    // `required` attribute, but left its selected <option value> intact.
    // That meant a stale user_id (or facility_id) could still be
    // submitted alongside the active field, causing validation/
    // "Invalid user selected" style errors on the backend even though
    // the visible form looked correct. Now the hidden field's value is
    // reset to '' whenever the assignment type is switched.
    // ==========================================
    $('.assign-type-radio').on('change', function() {
        if ($(this).val() === 'user') {
            $('#userSelectContainer').removeClass('d-none');
            $('#facilitySelectContainer').addClass('d-none');
            $('#userSelectContainer select').prop('required', true);
            $('#facilitySelectContainer select').prop('required', false).val('');
        } else {
            $('#userSelectContainer').addClass('d-none');
            $('#facilitySelectContainer').removeClass('d-none');
            $('#userSelectContainer select').prop('required', false).val('');
            $('#facilitySelectContainer select').prop('required', true);
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
    @can('license.revoke')
    $('#selectAll').on('change', function() {
        $('.row-checkbox:not(:disabled)').prop('checked', $(this).prop('checked'));
    });

    $('#bulkRevokeBtn').on('click', function() {
        var selected = $('.row-checkbox:checked:not(:disabled)');
        if (selected.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Licenses Selected',
                text: 'Please select at least one active license to revoke.'
            });
            return;
        }

        var ids = [];
        selected.each(function() {
            ids.push($(this).val());
        });

        $('#bulkRevokeCount').text(selected.length);
        $('#bulkRevokeIds').val(JSON.stringify(ids));
        $('#bulkRevokeModal').modal('show');
    });
    @endcan

    // ==========================================
    // VIEW
    // ==========================================
    @can('license.view')
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
            url: '/admin/software-licenses/' + id,
            type: 'GET',
            dataType: 'json',
            success: function(r) {
                var license = r;
                var product = license.product || {};
                var assignee = license.assigned_to_type === 'user' ? license.user : license.facility;
                var assigneeName = assignee ? assignee.name : 'N/A';

                var html = `
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Product</label>
                            <p class="fw-bold mb-0">${product.name || 'N/A'}</p>
                            <small class="text-muted">SKU: ${product.sku || 'N/A'}</small>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">License Key</label>
                            <p class="mb-0"><code>${license.license_key}</code></p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Assigned To</label>
                            <p class="mb-0">${assigneeName}</p>
                            <span class="badge bg-${license.assigned_to_type === 'user' ? 'success' : 'warning text-dark'}">${license.assigned_to_type === 'user' ? 'User' : 'Facility'}</span>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Status</label>
                            <p class="mb-0">
                                ${license.revoked_at ?
                                    `<span class="badge bg-secondary">Revoked</span>` :
                                    `<span class="badge bg-success">Active</span>`}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Assigned Date</label>
                            <p class="mb-0">${license.assigned_at ? new Date(license.assigned_at).toLocaleDateString() : 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Revoked Date</label>
                            <p class="mb-0">${license.revoked_at ? new Date(license.revoked_at).toLocaleDateString() : '—'}</p>
                        </div>
                        ${license.revoked_at ? `
                        <div class="col-12">
                            <label class="text-muted small fw-bold">Revoke Reason</label>
                            <p class="mb-0">${license.revoke_reason || 'No reason provided'}</p>
                        </div>` : ''}
                        <div class="col-12">
                            <label class="text-muted small fw-bold">Created At</label>
                            <p class="mb-0">${new Date(license.created_at).toLocaleString()}</p>
                        </div>
                    </div>
                `;
                body.html(html);
            },
            error: function(xhr) {
                body.html(`
                    <div class="text-center py-5 text-danger">
                        <i class="fas fa-exclamation-circle fa-4x mb-3 d-block"></i>
                        <h5>Failed to load license details</h5>
                        <p>${xhr.status} - ${xhr.statusText}</p>
                    </div>
                `);
            }
        });
    });
    @endcan

    // ==========================================
    // EDIT
    // ==========================================
    @can('license.edit')
    $(document).on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        var body = $('#editModalBody');
        var form = $('#editForm');

        form.attr('action', '/admin/software-licenses/' + id);
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
            url: '/admin/software-licenses/' + id + '/edit',
            type: 'GET',
            dataType: 'json',
            success: function(r) {
                var license = r;
                var isUser = license.assigned_to_type === 'user';

                body.html(`
                    <div id="editFormErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Product <span class="text-danger">*</span></label>
                            <select name="product_id" class="form-select" required>
                                @foreach($products ?? [] as $product)
                                    <option value="{{ $product->id }}" ${license.product_id == {{ $product->id }} ? 'selected' : ''}>
                                        {{ $product->name }} ({{ $product->available_licenses }} available)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Assign To <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input edit-type-radio" type="radio" name="assigned_to_type" id="editAssignToUser" value="user" ${isUser ? 'checked' : ''}>
                                    <label class="form-check-label" for="editAssignToUser">
                                        <i class="fas fa-user text-success"></i> User
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input edit-type-radio" type="radio" name="assigned_to_type" id="editAssignToFacility" value="facility" ${!isUser ? 'checked' : ''}>
                                    <label class="form-check-label" for="editAssignToFacility">
                                        <i class="fas fa-building text-warning"></i> Facility
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-12" id="editUserSelectContainer">
                            <label class="form-label fw-bold">User <span class="text-danger">*</span></label>
                            <select name="user_id" class="form-select">
                                <option value="">Select User</option>
                                @foreach($users ?? [] as $user)
                                    <option value="{{ $user->id }}" ${license.user_id == {{ $user->id }} ? 'selected' : ''}>
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 ${isUser ? 'd-none' : ''}" id="editFacilitySelectContainer">
                            <label class="form-label fw-bold">Facility <span class="text-danger">*</span></label>
                            <select name="facility_id" class="form-select">
                                <option value="">Select Facility</option>
                                @foreach($facilities ?? [] as $facility)
                                    <option value="{{ $facility->id }}" ${license.facility_id == {{ $facility->id }} ? 'selected' : ''}>
                                        {{ $facility->name }} ({{ $facility->code ?? 'N/A' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">License Key <span class="text-danger">*</span></label>
                            <input type="text" name="license_key" class="form-control" value="${license.license_key || ''}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Assigned Date <span class="text-danger">*</span></label>
                            <input type="date" name="assigned_at" class="form-control" value="${license.assigned_at ? new Date(license.assigned_at).toISOString().split('T')[0] : ''}" required>
                        </div>
                    </div>
                `);

                // Re-bind toggle events for edit modal.
                // FIX: same stale-value bug as the create modal — clear the
                // hidden field's value on toggle so an old user_id/facility_id
                // can't ride along with the submitted form.
                $('.edit-type-radio').on('change', function() {
                    if ($(this).val() === 'user') {
                        $('#editUserSelectContainer').removeClass('d-none');
                        $('#editFacilitySelectContainer').addClass('d-none');
                        $('#editUserSelectContainer select').prop('required', true);
                        $('#editFacilitySelectContainer select').prop('required', false).val('');
                    } else {
                        $('#editUserSelectContainer').addClass('d-none');
                        $('#editFacilitySelectContainer').removeClass('d-none');
                        $('#editUserSelectContainer select').prop('required', false).val('');
                        $('#editFacilitySelectContainer select').prop('required', true);
                    }
                });
            },
            error: function(xhr) {
                body.html(`
                    <div id="editFormErrors" class="alert alert-danger d-none"></div>
                    <div class="text-center py-5 text-danger">
                        <i class="fas fa-exclamation-circle fa-4x mb-3 d-block"></i>
                        <h5>Failed to load license data</h5>
                        <p>${xhr.status} - ${xhr.statusText}</p>
                    </div>
                `);
            }
        });
    });
    @endcan

    // ==========================================
    // CREATE
    // ==========================================
    @can('license.create')
    $('#createForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        var data = form.serialize();
        var btn = $('#createSubmitBtn');
        var errorContainer = $('#createFormErrors');

        clearErrors(errorContainer);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Assigning...');

        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(r) {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Assign License');

                if (r.success) {
                    $('#createModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Assigned!',
                        text: r.message || 'License assigned successfully!',
                        timer: 2000,
                        showConfirmButton: true
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: r.message || r.error || 'Something went wrong.',
                    });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Assign License');

                console.log('Error Response:', xhr);

                if (xhr.status === 422) {
                    var errors = (xhr.responseJSON && xhr.responseJSON.errors) || {};
                    var errorHtml = '<ul class="mb-0">';
                    $.each(errors, function(key, messages) {
                        $.each(messages, function(index, msg) {
                            errorHtml += '<li>' + msg + '</li>';
                        });
                    });
                    errorHtml += '</ul>';

                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error!',
                        html: errorHtml,
                    });
                } else {
                    var errorMsg = (xhr.responseJSON && (xhr.responseJSON.error || xhr.responseJSON.message)) || 'Failed to assign license. Please try again.';
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: errorMsg,
                    });
                }
            }
        });
    });
    @endcan

    // ==========================================
    // UPDATE
    // ==========================================
    @can('license.edit')
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
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Update License');

                if (r.success) {
                    $('#editModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated!',
                        text: r.message || 'License updated successfully!',
                        timer: 2000,
                        showConfirmButton: true
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: r.message || r.error || 'Something went wrong.',
                    });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Update License');

                console.log('Error Response:', xhr);

                if (xhr.status === 422) {
                    var errors = (xhr.responseJSON && xhr.responseJSON.errors) || {};
                    var errorHtml = '<ul class="mb-0">';
                    $.each(errors, function(key, messages) {
                        $.each(messages, function(index, msg) {
                            errorHtml += '<li>' + msg + '</li>';
                        });
                    });
                    errorHtml += '</ul>';

                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error!',
                        html: errorHtml,
                    });
                } else {
                    var errorMsg = (xhr.responseJSON && (xhr.responseJSON.error || xhr.responseJSON.message)) || 'Failed to update license. Please try again.';
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: errorMsg,
                    });
                }
            }
        });
    });
    @endcan

    // ==========================================
    // REVOKE
    // ==========================================
    @can('license.revoke')
    $(document).on('click', '.revoke-btn', function() {
        var id = $(this).data('id');
        var form = $('#revokeForm');
        form.attr('action', '/admin/software-licenses/' + id + '/revoke');
        clearErrors($('#revokeFormErrors'));
        $('#revokeModal').modal('show');
    });

    $('#revokeForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        var data = form.serialize();
        var btn = $('#revokeSubmitBtn');
        var errorContainer = $('#revokeFormErrors');

        clearErrors(errorContainer);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Revoking...');

        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(r) {
                btn.prop('disabled', false).html('<i class="fas fa-undo me-1"></i> Revoke License');

                if (r.success) {
                    $('#revokeModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Revoked!',
                        text: r.message || 'License revoked successfully!',
                        timer: 2000,
                        showConfirmButton: true
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: r.message || r.error || 'Failed to revoke license.',
                    });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-undo me-1"></i> Revoke License');

                console.log('Error Response:', xhr);

                var errorMsg = (xhr.responseJSON && (xhr.responseJSON.error || xhr.responseJSON.message)) || 'Failed to revoke license. Please try again.';
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: errorMsg,
                });
            }
        });
    });

    // ==========================================
    // BULK REVOKE
    // ==========================================
    $('#bulkRevokeForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        var data = form.serialize();
        var btn = $('#bulkRevokeSubmitBtn');
        var errorContainer = $('#bulkRevokeFormErrors');

        clearErrors(errorContainer);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Revoking...');

        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(r) {
                btn.prop('disabled', false).html('<i class="fas fa-undo me-1"></i> Revoke Selected');

                if (r.success) {
                    $('#bulkRevokeModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Revoked!',
                        text: r.message || 'Licenses revoked successfully!',
                        timer: 2000,
                        showConfirmButton: true
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: r.message || r.error || 'Failed to revoke licenses.',
                    });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-undo me-1"></i> Revoke Selected');

                console.log('Error Response:', xhr);

                var errorMsg = (xhr.responseJSON && (xhr.responseJSON.error || xhr.responseJSON.message)) || 'Failed to revoke licenses. Please try again.';
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: errorMsg,
                });
            }
        });
    });
    @endcan

    // ==========================================
    // DELETE
    // ==========================================
    @can('license.delete')
    $(document).on('click', '.delete-btn', function() {
        var id = $(this).data('id');
        var form = $('#deleteForm');
        form.attr('action', '/admin/software-licenses/' + id);

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
                        text: r.message || 'License deleted successfully!',
                        timer: 2000,
                        showConfirmButton: true
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: r.message || r.error || 'Failed to delete license.',
                    });
                }
            },
            error: function(xhr) {
                Swal.close();
                console.log('Error Response:', xhr);

                var errorMsg = (xhr.responseJSON && (xhr.responseJSON.error || xhr.responseJSON.message)) || 'Failed to delete license.';
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: errorMsg,
                });
            }
        });
    });
    @endcan

    // ==========================================
    // SEARCH & FILTER
    // ==========================================
    $('#searchBtn').on('click', function() { filterTable(); });
    $('#searchInput').on('keypress', function(e) {
        if (e.which === 13) filterTable();
    });
    $('#statusFilter').on('change', function() { filterTable(); });
    $('#typeFilter').on('change', function() { filterTable(); });
    $('#productFilter').on('change', function() { filterTable(); });

    function filterTable() {
        var search = $('#searchInput').val().toLowerCase();
        var status = $('#statusFilter').val();
        var type = $('#typeFilter').val();
        var product = $('#productFilter').val();

        $('#tableBody tr').each(function() {
            var row = $(this);
            var text = row.text().toLowerCase();
            var rowStatus = row.data('status');
            var rowType = row.data('type');
            var rowProduct = String(row.data('product'));

            var show = true;
            if (search && !text.includes(search)) show = false;
            if (status && rowStatus !== status) show = false;
            if (type && rowType !== type) show = false;
            if (product && rowProduct !== product) show = false;

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