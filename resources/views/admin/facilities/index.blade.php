@extends('layouts.admin')

@section('title', 'Facilities')
@section('page-title', 'Facility Management')

@section('admin-content')
<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ number_format($stats['total'] ?? 0) }}</h3>
                <p>Total Facilities</p>
            </div>
            <div class="icon">
                <i class="fas fa-building"></i>
            </div>
            <a href="{{ route('admin.facilities.index') }}" class="small-box-footer">
                View All <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ number_format($stats['active'] ?? 0) }}</h3>
                <p>Active</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <a href="{{ route('admin.facilities.index', ['status' => 'active']) }}" class="small-box-footer">
                View Active <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ number_format($stats['pending'] ?? 0) }}</h3>
                <p>Pending</p>
            </div>
            <div class="icon">
                <i class="fas fa-clock"></i>
            </div>
            <a href="{{ route('admin.facilities.index', ['status' => 'pending']) }}" class="small-box-footer">
                View Pending <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ number_format($stats['verified'] ?? 0) }}</h3>
                <p>Verified</p>
            </div>
            <div class="icon">
                <i class="fas fa-shield-alt"></i>
            </div>
            <a href="{{ route('admin.facilities.index', ['verified' => 'yes']) }}" class="small-box-footer">
                View Verified <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Main Card -->
<div class="card">
    <div class="card-header">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <h5 class="card-title mb-0">
                <i class="fas fa-building text-primary me-2"></i>
                All Facilities
                <span class="badge bg-primary ms-1">{{ $facilities->total() }}</span>
            </h5>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <div class="input-group input-group-sm" style="width: 200px;">
                <input type="text" id="searchInput" class="form-control" placeholder="Search...">
                <button class="btn btn-secondary" type="button" id="searchBtn">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            <select id="typeFilter" class="form-select form-select-sm" style="width: 120px;">
                <option value="">All Types</option>
                <option value="hospital">Hospital</option>
                <option value="school">School</option>
                <option value="office">Office</option>
                <option value="warehouse">Warehouse</option>
                <option value="other">Other</option>
            </select>
            <select id="statusFilter" class="form-select form-select-sm" style="width: 120px;">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="pending">Pending</option>
                <option value="suspended">Suspended</option>
            </select>
            <select id="verifiedFilter" class="form-select form-select-sm" style="width: 130px;">
                <option value="">All Verification</option>
                <option value="verified">Verified</option>
                <option value="unverified">Unverified</option>
            </select>
            @can('facility.create')
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fas fa-plus-circle me-1"></i> Add Facility
            </button>
            @endcan
            @can('facility.export')
            <button class="btn btn-success btn-sm" id="exportBtn">
                <i class="fas fa-file-excel me-1"></i> Export
            </button>
            @endcan
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 35px;">#</th>
                        <th style="min-width: 160px;">Name</th>
                        <th>Code</th>
                        <th>Type</th>
                        <th>Contact Person</th>
                        <th>City</th>
                        <th style="width: 80px;">Status</th>
                        <th style="width: 100px;">Verified</th>
                        <th style="width: 180px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($facilities as $facility)
                    <tr data-id="{{ $facility->id }}" 
                        data-type="{{ $facility->type }}" 
                        data-status="{{ $facility->status }}"
                        data-verified="{{ $facility->is_verified ? 'verified' : 'unverified' }}">
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle bg-primary text-white me-2">
                                    {{ strtoupper(substr($facility->name, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="fw-bold small">{{ Str::limit($facility->name, 25) }}</div>
                                    <small class="text-muted">{{ $facility->type_label }}</small>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge bg-secondary">{{ $facility->code ?? 'N/A' }}</span></td>
                        <td>
                            @php
                                $typeColors = [
                                    'hospital' => 'danger',
                                    'school' => 'info',
                                    'office' => 'primary',
                                    'warehouse' => 'warning text-dark',
                                    'other' => 'secondary'
                                ];
                            @endphp
                            <span class="badge bg-{{ $typeColors[$facility->type] ?? 'secondary' }}">
                                {{ ucfirst($facility->type) }}
                            </span>
                        </td>
                        <td>{{ $facility->contact_person ?? 'N/A' }}</td>
                        <td>{{ $facility->city ?? 'N/A' }}</td>
                        <td>
                            @php
                                $statusColors = [
                                    'active' => 'bg-success',
                                    'inactive' => 'bg-secondary',
                                    'pending' => 'bg-warning text-dark',
                                    'suspended' => 'bg-danger'
                                ];
                            @endphp
                            <span class="badge {{ $statusColors[$facility->status] ?? 'bg-secondary' }}">
                                {{ ucfirst($facility->status) }}
                            </span>
                        </td>
                        <td>
                            @if($facility->is_verified)
                                <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Verified</span>
                            @else
                                <span class="badge bg-secondary"><i class="fas fa-clock me-1"></i> Pending</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                @can('facility.view')
                                <button class="btn btn-outline-primary btn-sm view-btn" data-id="{{ $facility->id }}" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @endcan
                                @can('facility.edit')
                                <button class="btn btn-outline-warning btn-sm edit-btn" data-id="{{ $facility->id }}" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @endcan
                                @can('facility.verify')
                                @if(!$facility->is_verified)
                                <button class="btn btn-outline-success btn-sm verify-btn" data-id="{{ $facility->id }}" title="Verify">
                                    <i class="fas fa-check-circle"></i>
                                </button>
                                @endif
                                @endcan
                                @can('facility.delete')
                                <button class="btn btn-outline-danger btn-sm delete-btn" data-id="{{ $facility->id }}" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9">
                            <div class="text-center py-5">
                                <i class="fas fa-building fa-4x text-muted mb-3 d-block"></i>
                                <h5 class="fw-bold">No Facilities Found</h5>
                                <p class="text-muted">Click "Add Facility" to create your first facility.</p>
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
                Showing {{ $facilities->firstItem() ?? 0 }} to {{ $facilities->lastItem() ?? 0 }} of {{ $facilities->total() }} entries
            </span>
        </div>
        <div>
            {{ $facilities->links() }}
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- CREATE MODAL (EXTENDED - Full Width) -->
<!-- ============================================ -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl" style="max-width: 1100px;">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white">
                    <i class="fas fa-plus-circle me-2"></i> Add New Facility
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="createForm" action="{{ route('admin.facilities.store') }}" method="POST">
                @csrf
                <div class="modal-body" style="max-height: 75vh; overflow-y: auto; padding: 25px 30px;">
                    <div id="createFormErrors" class="alert alert-danger d-none"></div>
                    
                    <!-- Basic Information Section -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="fw-bold mb-0"><i class="fas fa-info-circle text-primary me-2"></i>Basic Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="facility_name" class="form-label fw-bold">Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="facility_name" class="form-control" required placeholder="Enter facility name">
                                </div>
                                <div class="col-md-6">
                                    <label for="facility_type" class="form-label fw-bold">Type <span class="text-danger">*</span></label>
                                    <select name="type" id="facility_type" class="form-select" required>
                                        <option value="">Select Type</option>
                                        <option value="hospital">Hospital</option>
                                        <option value="school">School</option>
                                        <option value="office">Office</option>
                                        <option value="warehouse">Warehouse</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="facility_category" class="form-label fw-bold">Category</label>
                                    <input type="text" name="category" id="facility_category" class="form-control" placeholder="e.g., Medical, Education">
                                </div>
                                <div class="col-md-4">
                                    <label for="facility_sub_category" class="form-label fw-bold">Sub Category</label>
                                    <input type="text" name="sub_category" id="facility_sub_category" class="form-control" placeholder="e.g., General Hospital">
                                </div>
                                <div class="col-md-4">
                                    <label for="facility_code" class="form-label fw-bold">Code</label>
                                    <input type="text" name="code" id="facility_code" class="form-control" placeholder="Auto-generated if left blank">
                                </div>
                                <div class="col-md-6">
                                    <label for="facility_status" class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                                    <select name="status" id="facility_status" class="form-select" required>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                        <option value="pending">Pending</option>
                                        <option value="suspended">Suspended</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="facility_verified" class="form-label fw-bold">Verified</label>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="is_verified" id="facility_verified" value="1">
                                        <label class="form-check-label" for="facility_verified">Mark as verified</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information Section -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="fw-bold mb-0"><i class="fas fa-address-card text-success me-2"></i>Contact Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="facility_email" class="form-label fw-bold">Email</label>
                                    <input type="email" name="email" id="facility_email" class="form-control" placeholder="Enter email">
                                </div>
                                <div class="col-md-4">
                                    <label for="facility_phone" class="form-label fw-bold">Phone</label>
                                    <input type="text" name="phone" id="facility_phone" class="form-control" placeholder="Enter phone number">
                                </div>
                                <div class="col-md-4">
                                    <label for="facility_website" class="form-label fw-bold">Website</label>
                                    <input type="url" name="website" id="facility_website" class="form-control" placeholder="https://example.com">
                                </div>
                                <div class="col-md-4">
                                    <label for="facility_contact_person" class="form-label fw-bold">Contact Person</label>
                                    <input type="text" name="contact_person" id="facility_contact_person" class="form-control" placeholder="Enter contact person name">
                                </div>
                                <div class="col-md-4">
                                    <label for="facility_contact_phone" class="form-label fw-bold">Contact Phone</label>
                                    <input type="text" name="contact_phone" id="facility_contact_phone" class="form-control" placeholder="Enter contact phone">
                                </div>
                                <div class="col-md-4">
                                    <label for="facility_contact_email" class="form-label fw-bold">Contact Email</label>
                                    <input type="email" name="contact_email" id="facility_contact_email" class="form-control" placeholder="Enter contact email">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Address Section -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="fw-bold mb-0"><i class="fas fa-map-marker-alt text-danger me-2"></i>Address</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="facility_address" class="form-label fw-bold">Address</label>
                                    <textarea name="address" id="facility_address" class="form-control" rows="2" placeholder="Enter street address"></textarea>
                                </div>
                                <div class="col-md-3">
                                    <label for="facility_city" class="form-label fw-bold">City</label>
                                    <input type="text" name="city" id="facility_city" class="form-control" placeholder="City">
                                </div>
                                <div class="col-md-3">
                                    <label for="facility_state" class="form-label fw-bold">State</label>
                                    <input type="text" name="state" id="facility_state" class="form-control" placeholder="State/Region">
                                </div>
                                <div class="col-md-3">
                                    <label for="facility_country" class="form-label fw-bold">Country</label>
                                    <input type="text" name="country" id="facility_country" class="form-control" placeholder="Country">
                                </div>
                                <div class="col-md-3">
                                    <label for="facility_postal_code" class="form-label fw-bold">Postal Code</label>
                                    <input type="text" name="postal_code" id="facility_postal_code" class="form-control" placeholder="Postal code">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Description Section -->
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="fw-bold mb-0"><i class="fas fa-align-left text-secondary me-2"></i>Description & Notes</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="facility_description" class="form-label fw-bold">Description</label>
                                    <textarea name="description" id="facility_description" class="form-control" rows="2" placeholder="Facility description"></textarea>
                                </div>
                                <div class="col-12">
                                    <label for="facility_notes" class="form-label fw-bold">Notes</label>
                                    <textarea name="notes" id="facility_notes" class="form-control" rows="2" placeholder="Additional notes"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="createSubmitBtn">
                        <i class="fas fa-save me-1"></i> Save Facility
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- VIEW MODAL (EXTENDED) -->
<!-- ============================================ -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl" style="max-width: 1100px;">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title text-white">
                    <i class="fas fa-eye me-2"></i> Facility Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewModalBody" style="max-height: 75vh; overflow-y: auto; padding: 25px 30px;">
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
<!-- EDIT MODAL (EXTENDED - Full Width) -->
<!-- ============================================ -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl" style="max-width: 1100px;">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title text-dark">
                    <i class="fas fa-edit me-2"></i> Edit Facility
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body" id="editModalBody" style="max-height: 75vh; overflow-y: auto; padding: 25px 30px;">
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
                        <i class="fas fa-save me-1"></i> Update Facility
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
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white">
                    <i class="fas fa-exclamation-triangle me-2"></i> Confirm Delete
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-trash fa-4x text-danger mb-3 d-block"></i>
                <p class="fw-bold">Are you sure you want to delete this facility?</p>
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

/* Enhanced Modal Styles */
.modal-xl {
    max-width: 1100px;
}
.modal-body {
    padding: 20px 25px;
}
.modal-header {
    padding: 15px 25px;
}
.card {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
}
.card-header {
    background-color: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
    padding: 10px 15px;
}
.card-body {
    padding: 15px;
}
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
    // VIEW
    // ==========================================
    @can('facility.view')
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
            url: '/admin/facilities/' + id,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (!response.success) {
                    body.html(`
                        <div class="text-center py-5 text-danger">
                            <i class="fas fa-exclamation-circle fa-4x mb-3 d-block"></i>
                            <h5>Error</h5>
                            <p>${response.message || 'Failed to load facility details.'}</p>
                        </div>
                    `);
                    return;
                }

                var facility = response.facility;
                var productStats = response.product_stats || {};

                var typeColors = {
                    hospital: 'danger',
                    school: 'info',
                    office: 'primary',
                    warehouse: 'warning text-dark',
                    other: 'secondary'
                };

                var statusColors = {
                    active: 'success',
                    inactive: 'secondary',
                    pending: 'warning text-dark',
                    suspended: 'danger'
                };

                var html = `
                    <div class="row g-3">
                        <!-- Header -->
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="fw-bold mb-0">${facility.name}</h5>
                                <span class="badge ${statusColors[facility.status] ? 'bg-' + statusColors[facility.status] : 'bg-secondary'}">
                                    ${facility.status.toUpperCase()}
                                </span>
                            </div>
                            <hr>
                        </div>
                        
                        <!-- Basic Info -->
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Facility Name</label>
                            <p class="fw-bold mb-0">${facility.name}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Type</label>
                            <p class="mb-0">
                                <span class="badge bg-${typeColors[facility.type] || 'secondary'}">
                                    ${facility.type_label}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small fw-bold">Code</label>
                            <p class="mb-0"><span class="badge bg-secondary">${facility.code || 'N/A'}</span></p>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small fw-bold">Category</label>
                            <p class="mb-0">${facility.category || 'N/A'}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small fw-bold">Sub Category</label>
                            <p class="mb-0">${facility.sub_category || 'N/A'}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small fw-bold">Email</label>
                            <p class="mb-0">${facility.email || 'N/A'}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small fw-bold">Phone</label>
                            <p class="mb-0">${facility.phone || 'N/A'}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small fw-bold">Website</label>
                            <p class="mb-0">${facility.website ? `<a href="${facility.website}" target="_blank">${facility.website}</a>` : 'N/A'}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small fw-bold">Contact Person</label>
                            <p class="mb-0">${facility.contact_person || 'N/A'}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small fw-bold">Contact Phone</label>
                            <p class="mb-0">${facility.contact_phone || 'N/A'}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small fw-bold">Contact Email</label>
                            <p class="mb-0">${facility.contact_email || 'N/A'}</p>
                        </div>
                        
                        <!-- Address -->
                        <div class="col-12">
                            <hr>
                            <h6 class="fw-bold text-danger"><i class="fas fa-map-marker-alt me-2"></i>Address</h6>
                        </div>
                        <div class="col-12">
                            <label class="text-muted small fw-bold">Address</label>
                            <p class="mb-0">${facility.full_address || 'N/A'}</p>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted small fw-bold">City</label>
                            <p class="mb-0">${facility.city || 'N/A'}</p>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted small fw-bold">State</label>
                            <p class="mb-0">${facility.state || 'N/A'}</p>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted small fw-bold">Country</label>
                            <p class="mb-0">${facility.country || 'N/A'}</p>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted small fw-bold">Postal Code</label>
                            <p class="mb-0">${facility.postal_code || 'N/A'}</p>
                        </div>
                        
                        <!-- Description & Notes -->
                        ${facility.description ? `
                        <div class="col-12">
                            <hr>
                            <h6 class="fw-bold text-secondary"><i class="fas fa-align-left me-2"></i>Description</h6>
                            <p class="mb-0">${facility.description}</p>
                        </div>` : ''}
                        ${facility.notes ? `
                        <div class="col-12">
                            <hr>
                            <h6 class="fw-bold text-secondary"><i class="fas fa-sticky-note me-2"></i>Notes</h6>
                            <p class="mb-0">${facility.notes}</p>
                        </div>` : ''}
                        
                        <!-- Product Statistics -->
                        <div class="col-12">
                            <hr>
                            <h6 class="fw-bold text-primary"><i class="fas fa-boxes me-2"></i>Product Statistics</h6>
                            <div class="row g-2">
                                <div class="col-md-3 col-6">
                                    <div class="text-center p-2 bg-light rounded">
                                        <span class="d-block fw-bold">${productStats.total || 0}</span>
                                        <small class="text-muted">Total Products</small>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="text-center p-2 bg-primary bg-opacity-10 rounded">
                                        <span class="d-block fw-bold text-primary">${productStats.hardware || 0}</span>
                                        <small class="text-muted">Hardware</small>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="text-center p-2 bg-success bg-opacity-10 rounded">
                                        <span class="d-block fw-bold text-success">${productStats.software || 0}</span>
                                        <small class="text-muted">Software</small>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="text-center p-2 bg-info bg-opacity-10 rounded">
                                        <span class="d-block fw-bold text-info">${productStats.available || 0}</span>
                                        <small class="text-muted">Available</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Timestamps -->
                        <div class="col-12">
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="text-muted small fw-bold">Created</label>
                                    <p class="mb-0">${new Date(facility.created_at).toLocaleString()}</p>
                                </div>
                                ${facility.verified_at ? `
                                <div class="col-md-6">
                                    <label class="text-muted small fw-bold">Verified At</label>
                                    <p class="mb-0">${new Date(facility.verified_at).toLocaleString()}</p>
                                </div>` : ''}
                            </div>
                        </div>
                    </div>
                `;
                body.html(html);
            },
            error: function(xhr) {
                body.html(`
                    <div class="text-center py-5 text-danger">
                        <i class="fas fa-exclamation-circle fa-4x mb-3 d-block"></i>
                        <h5>Failed to load facility details</h5>
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
    @can('facility.edit')
    $(document).on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        var body = $('#editModalBody');
        var form = $('#editForm');

        form.attr('action', '/admin/facilities/' + id);
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
            url: '/admin/facilities/' + id + '/edit',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (!response.success) {
                    body.html(`
                        <div id="editFormErrors" class="alert alert-danger"></div>
                        <div class="text-center py-5 text-danger">
                            <i class="fas fa-exclamation-circle fa-4x mb-3 d-block"></i>
                            <h5>Error</h5>
                            <p>${response.message || 'Failed to load facility data.'}</p>
                        </div>
                    `);
                    return;
                }

                var r = response.facility;

                body.html(`
                    <div id="editFormErrors" class="alert alert-danger d-none"></div>
                    
                    <!-- Basic Information Section -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="fw-bold mb-0"><i class="fas fa-info-circle text-primary me-2"></i>Basic Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="edit_name" class="form-label fw-bold">Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="edit_name" class="form-control" value="${r.name}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="edit_type" class="form-label fw-bold">Type <span class="text-danger">*</span></label>
                                    <select name="type" id="edit_type" class="form-select" required>
                                        <option value="hospital" ${r.type == 'hospital' ? 'selected' : ''}>Hospital</option>
                                        <option value="school" ${r.type == 'school' ? 'selected' : ''}>School</option>
                                        <option value="office" ${r.type == 'office' ? 'selected' : ''}>Office</option>
                                        <option value="warehouse" ${r.type == 'warehouse' ? 'selected' : ''}>Warehouse</option>
                                        <option value="other" ${r.type == 'other' ? 'selected' : ''}>Other</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="edit_category" class="form-label fw-bold">Category</label>
                                    <input type="text" name="category" id="edit_category" class="form-control" value="${r.category || ''}" placeholder="e.g., Medical, Education">
                                </div>
                                <div class="col-md-4">
                                    <label for="edit_sub_category" class="form-label fw-bold">Sub Category</label>
                                    <input type="text" name="sub_category" id="edit_sub_category" class="form-control" value="${r.sub_category || ''}" placeholder="e.g., General Hospital">
                                </div>
                                <div class="col-md-4">
                                    <label for="edit_code" class="form-label fw-bold">Code</label>
                                    <input type="text" name="code" id="edit_code" class="form-control" value="${r.code || ''}" placeholder="Auto-generated">
                                </div>
                                <div class="col-md-6">
                                    <label for="edit_status" class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                                    <select name="status" id="edit_status" class="form-select" required>
                                        <option value="active" ${r.status == 'active' ? 'selected' : ''}>Active</option>
                                        <option value="inactive" ${r.status == 'inactive' ? 'selected' : ''}>Inactive</option>
                                        <option value="pending" ${r.status == 'pending' ? 'selected' : ''}>Pending</option>
                                        <option value="suspended" ${r.status == 'suspended' ? 'selected' : ''}>Suspended</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="edit_verified" class="form-label fw-bold">Verified</label>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="is_verified" id="edit_verified" value="1" ${r.is_verified ? 'checked' : ''}>
                                        <label class="form-check-label" for="edit_verified">Mark as verified</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information Section -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="fw-bold mb-0"><i class="fas fa-address-card text-success me-2"></i>Contact Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="edit_email" class="form-label fw-bold">Email</label>
                                    <input type="email" name="email" id="edit_email" class="form-control" value="${r.email || ''}" placeholder="Enter email">
                                </div>
                                <div class="col-md-4">
                                    <label for="edit_phone" class="form-label fw-bold">Phone</label>
                                    <input type="text" name="phone" id="edit_phone" class="form-control" value="${r.phone || ''}" placeholder="Enter phone number">
                                </div>
                                <div class="col-md-4">
                                    <label for="edit_website" class="form-label fw-bold">Website</label>
                                    <input type="url" name="website" id="edit_website" class="form-control" value="${r.website || ''}" placeholder="https://example.com">
                                </div>
                                <div class="col-md-4">
                                    <label for="edit_contact_person" class="form-label fw-bold">Contact Person</label>
                                    <input type="text" name="contact_person" id="edit_contact_person" class="form-control" value="${r.contact_person || ''}" placeholder="Enter contact person name">
                                </div>
                                <div class="col-md-4">
                                    <label for="edit_contact_phone" class="form-label fw-bold">Contact Phone</label>
                                    <input type="text" name="contact_phone" id="edit_contact_phone" class="form-control" value="${r.contact_phone || ''}" placeholder="Enter contact phone">
                                </div>
                                <div class="col-md-4">
                                    <label for="edit_contact_email" class="form-label fw-bold">Contact Email</label>
                                    <input type="email" name="contact_email" id="edit_contact_email" class="form-control" value="${r.contact_email || ''}" placeholder="Enter contact email">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Address Section -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="fw-bold mb-0"><i class="fas fa-map-marker-alt text-danger me-2"></i>Address</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="edit_address" class="form-label fw-bold">Address</label>
                                    <textarea name="address" id="edit_address" class="form-control" rows="2" placeholder="Enter street address">${r.address || ''}</textarea>
                                </div>
                                <div class="col-md-3">
                                    <label for="edit_city" class="form-label fw-bold">City</label>
                                    <input type="text" name="city" id="edit_city" class="form-control" value="${r.city || ''}" placeholder="City">
                                </div>
                                <div class="col-md-3">
                                    <label for="edit_state" class="form-label fw-bold">State</label>
                                    <input type="text" name="state" id="edit_state" class="form-control" value="${r.state || ''}" placeholder="State/Region">
                                </div>
                                <div class="col-md-3">
                                    <label for="edit_country" class="form-label fw-bold">Country</label>
                                    <input type="text" name="country" id="edit_country" class="form-control" value="${r.country || ''}" placeholder="Country">
                                </div>
                                <div class="col-md-3">
                                    <label for="edit_postal_code" class="form-label fw-bold">Postal Code</label>
                                    <input type="text" name="postal_code" id="edit_postal_code" class="form-control" value="${r.postal_code || ''}" placeholder="Postal code">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Description Section -->
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="fw-bold mb-0"><i class="fas fa-align-left text-secondary me-2"></i>Description & Notes</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="edit_description" class="form-label fw-bold">Description</label>
                                    <textarea name="description" id="edit_description" class="form-control" rows="2" placeholder="Facility description">${r.description || ''}</textarea>
                                </div>
                                <div class="col-12">
                                    <label for="edit_notes" class="form-label fw-bold">Notes</label>
                                    <textarea name="notes" id="edit_notes" class="form-control" rows="2" placeholder="Additional notes">${r.notes || ''}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                `);
            },
            error: function(xhr) {
                body.html(`
                    <div id="editFormErrors" class="alert alert-danger"></div>
                    <div class="text-center py-5 text-danger">
                        <i class="fas fa-exclamation-circle fa-4x mb-3 d-block"></i>
                        <h5>Failed to load facility data</h5>
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
    @can('facility.create')
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
            success: function(response) {
                if (response.success) {
                    $('#createModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Added!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: true
                    }).then(() => { window.location.reload(); });
                } else {
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save Facility');
                    if (response.errors) {
                        showErrors(errorContainer, response.errors);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message || 'Something went wrong.',
                        });
                    }
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save Facility');
                if (xhr.status === 422) {
                    showErrors(errorContainer, xhr.responseJSON.errors);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to create facility. Please try again.',
                    });
                }
            }
        });
    });
    @endcan

    // ==========================================
    // UPDATE
    // ==========================================
    @can('facility.edit')
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
            success: function(response) {
                if (response.success) {
                    $('#editModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: true
                    }).then(() => { window.location.reload(); });
                } else {
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Update Facility');
                    if (response.errors) {
                        showErrors(errorContainer, response.errors);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message || 'Something went wrong.',
                        });
                    }
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Update Facility');
                if (xhr.status === 422) {
                    showErrors(errorContainer, xhr.responseJSON.errors);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to update facility. Please try again.',
                    });
                }
            }
        });
    });
    @endcan

    // ==========================================
    // DELETE
    // ==========================================
    @can('facility.delete')
    $(document).on('click', '.delete-btn', function() {
        var id = $(this).data('id');
        var form = $('#deleteForm');
        form.attr('action', '/admin/facilities/' + id);

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
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: true
                    }).then(() => { window.location.reload(); });
                } else {
                    btn.prop('disabled', false).html('<i class="fas fa-trash me-1"></i> Delete');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message || 'Failed to delete facility.',
                    });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-trash me-1"></i> Delete');
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: xhr.responseJSON?.message || 'Failed to delete facility.',
                });
            }
        });
    });
    @endcan

    // ==========================================
    // VERIFY
    // ==========================================
    @can('facility.verify')
    $(document).on('click', '.verify-btn', function() {
        var id = $(this).data('id');
        
        Swal.fire({
            title: 'Verify Facility?',
            text: "This will mark the facility as verified.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, verify!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Verifying...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: '/admin/facilities/' + id + '/verify',
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Verified!',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: true
                            }).then(() => { window.location.reload(); });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: response.message || 'Failed to verify facility.',
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: xhr.responseJSON?.message || 'Failed to verify facility.',
                        });
                    }
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
    $('#typeFilter, #statusFilter, #verifiedFilter').on('change', function() { filterTable(); });

    function filterTable() {
        var search = $('#searchInput').val().toLowerCase();
        var type = $('#typeFilter').val();
        var status = $('#statusFilter').val();
        var verified = $('#verifiedFilter').val();

        $('#tableBody tr').each(function() {
            var row = $(this);
            var text = row.text().toLowerCase();
            var rowType = row.data('type');
            var rowStatus = row.data('status');
            var rowVerified = row.data('verified');

            var show = true;
            if (search && !text.includes(search)) show = false;
            if (type && rowType !== type) show = false;
            if (status && rowStatus !== status) show = false;
            if (verified && rowVerified !== verified) show = false;

            row.toggle(show);
        });
    }

    // ==========================================
    // EXPORT
    // ==========================================
    $('#exportBtn').on('click', function() {
        window.location.href = '{{ route("admin.facilities.export") }}';
    });

    // ==========================================
    // RESET MODAL FORMS
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