@extends('layouts.admin')

@section('title', 'Bulk Upload Products')
@section('page-title', 'Bulk Upload Products')

@section('admin-content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-upload text-info me-2"></i>
                        Bulk Upload Products
                    </h5>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Back to Products
                    </a>
                    <a href="{{ route('admin.products.bulk-template') }}" class="btn btn-success btn-sm">
                        <i class="fas fa-download me-1"></i> Download Template
                    </a>
                </div>
            </div>

            <div class="card-body">
                <!-- Instructions -->
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle me-2"></i> Instructions</h6>
                    <ul class="mb-0">
                        <li>Upload a CSV or Excel file with product data</li>
                        <li>Required columns: <strong>name</strong>, <strong>sku</strong></li>
                        <li>Download the template for the correct format</li>
                        <li>Maximum file size: <strong>5MB</strong></li>
                        <li>Supported formats: <strong>CSV, XLSX, XLS</strong></li>
                        <li>Products will be created with status <strong>"Available"</strong> by default</li>
                        <li>Stock movements will be automatically recorded</li>
                    </ul>
                </div>

                <!-- Sample Data Preview -->
                <div class="alert alert-secondary">
                    <h6><i class="fas fa-table me-2"></i> Sample Data Format</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>name</th>
                                    <th>sku</th>
                                    <th>barcode</th>
                                    <th>description</th>
                                    <th>type</th>
                                    <th>category</th>
                                    <th>supplier</th>
                                    <th>quantity</th>
                                    <th>purchase_price</th>
                                    <th>selling_price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Sample Laptop</td>
                                    <td>LAP-001</td>
                                    <td>123456789</td>
                                    <td>High performance laptop</td>
                                    <td>hardware</td>
                                    <td>Electronics</td>
                                    <td>Tech Suppliers Ltd</td>
                                    <td>10</td>
                                    <td>500.00</td>
                                    <td>750.00</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <small class="text-muted mt-2 d-block">
                        <i class="fas fa-chevron-right me-1"></i> 
                        <a href="{{ route('admin.products.bulk-template') }}" class="text-primary">Download full template</a> 
                        with all supported columns
                    </small>
                </div>

                <!-- Upload Form -->
                <form id="bulkUploadForm" action="{{ route('admin.products.bulk-upload.process') }}" method="POST" enctype="multipart/form-data" class="mt-4">
                    @csrf
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3"><i class="fas fa-file-upload text-primary me-2"></i> File Upload</h6>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Upload File <span class="text-danger">*</span></label>
                                        <input type="file" name="file" class="form-control" accept=".csv,.xlsx,.xls" required>
                                        <small class="text-muted">CSV, XLSX, or XLS files only (Max: 5MB)</small>
                                    </div>
                                    <div id="fileInfo" class="d-none">
                                        <div class="alert alert-success">
                                            <i class="fas fa-check-circle me-2"></i>
                                            <span id="fileName"></span> (<span id="fileSize"></span>) selected
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3"><i class="fas fa-cog text-warning me-2"></i> Default Settings</h6>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Default Category</label>
                                        <select name="category_id" class="form-select">
                                            <option value="">No Default Category</option>
                                            @foreach($categories ?? [] as $category)
                                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">Used if category column is empty in the file</small>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Default Supplier</label>
                                        <select name="supplier_id" class="form-select">
                                            <option value="">No Default Supplier</option>
                                            @foreach($suppliers ?? [] as $supplier)
                                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">Used if supplier column is empty in the file</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div id="uploadProgress" class="d-none">
                                <div class="progress" style="height: 25px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                         role="progressbar" 
                                         style="width: 0%;" 
                                         id="progressBar">
                                        0%
                                    </div>
                                </div>
                                <small class="text-muted mt-1 d-block" id="progressText">Preparing upload...</small>
                            </div>
                        </div>

                        <div class="col-12">
                            <div id="uploadErrors" class="alert alert-danger d-none"></div>
                            <div id="uploadSuccess" class="alert alert-success d-none"></div>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-info" id="uploadSubmitBtn">
                                <i class="fas fa-upload me-1"></i> Upload Products
                            </button>
                            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary ms-2">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- RESULTS MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="resultsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:700px;">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title text-white">
                    <i class="fas fa-check-circle me-2"></i> Upload Results
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="resultsModalBody" style="max-height:70vh; overflow-y:auto;">
                <div id="resultsContent"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="viewProductsBtn">
                    <i class="fas fa-box me-1"></i> View Products
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.progress {
    background-color: #e9ecef;
    border-radius: 4px;
    overflow: hidden;
}
.progress-bar {
    background-color: #0dcaf0;
    color: #fff;
    font-weight: 600;
    transition: width 0.3s ease;
}
.progress-bar-striped {
    background-image: linear-gradient(45deg, rgba(255,255,255,.15) 25%, transparent 25%, transparent 50%, rgba(255,255,255,.15) 50%, rgba(255,255,255,.15) 75%, transparent 75%, transparent);
    background-size: 1rem 1rem;
}
</style>

@push('scripts')
<script>
$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // ==========================================
    // FILE INPUT HANDLER
    // ==========================================
    $('input[name="file"]').on('change', function() {
        var file = this.files[0];
        if (file) {
            var size = (file.size / 1024 / 1024).toFixed(2);
            $('#fileName').text(file.name);
            $('#fileSize').text(size + ' MB');
            $('#fileInfo').removeClass('d-none');
            
            // Validate file size
            if (file.size > 5 * 1024 * 1024) {
                Swal.fire({
                    icon: 'error',
                    title: 'File Too Large',
                    text: 'Maximum file size is 5MB. Please compress your file.'
                });
                $(this).val('');
                $('#fileInfo').addClass('d-none');
            }
        }
    });

    // ==========================================
    // SHOW ERRORS HELPER
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
    // UPDATE PROGRESS
    // ==========================================
    function updateProgress(percentage, text) {
        $('#progressBar').css('width', percentage + '%').text(percentage + '%');
        $('#progressText').text(text || 'Processing...');
    }

    // ==========================================
    // FORM SUBMIT
    // ==========================================
    $('#bulkUploadForm').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var formData = new FormData(this);
        var btn = $('#uploadSubmitBtn');
        var errorContainer = $('#uploadErrors');
        var successContainer = $('#uploadSuccess');
        var progressContainer = $('#uploadProgress');
        
        // Validate file is selected
        var file = $('input[name="file"]')[0].files[0];
        if (!file) {
            Swal.fire({
                icon: 'warning',
                title: 'No File Selected',
                text: 'Please select a file to upload.'
            });
            return;
        }
        
        // Show progress
        progressContainer.removeClass('d-none');
        updateProgress(0, 'Starting upload...');
        
        clearErrors(errorContainer);
        successContainer.addClass('d-none');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Uploading...');
        
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener('progress', function(evt) {
                    if (evt.lengthComputable) {
                        var percentComplete = Math.round((evt.loaded / evt.total) * 100);
                        updateProgress(percentComplete, 'Uploading... ' + percentComplete + '%');
                    }
                }, false);
                return xhr;
            },
            success: function(response) {
                updateProgress(100, 'Complete!');
                btn.prop('disabled', false).html('<i class="fas fa-upload me-1"></i> Upload Products');
                progressContainer.addClass('d-none');
                
                if (response.success) {
                    // Show results modal
                    showResults(response);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Upload Failed',
                        text: response.message || 'Something went wrong.'
                    });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-upload me-1"></i> Upload Products');
                progressContainer.addClass('d-none');
                
                if (xhr.status === 422) {
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        showErrors(errorContainer, xhr.responseJSON.errors);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            text: xhr.responseJSON?.message || 'Please check your file format.'
                        });
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'Failed to upload file. Please try again.'
                    });
                }
            }
        });
    });

    // ==========================================
    // SHOW RESULTS
    // ==========================================
    function showResults(response) {
        var results = response.results || {};
        var created = results.created || 0;
        var skipped = results.skipped || 0;
        var errors = results.errors || [];
        
        var html = `
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h3 class="mb-0">${created}</h3>
                            <small>Products Created</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-warning text-dark">
                        <div class="card-body text-center">
                            <h3 class="mb-0">${skipped}</h3>
                            <small>Skipped</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h3 class="mb-0">${created + skipped}</h3>
                            <small>Total Processed</small>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        if (errors && errors.length > 0) {
            html += `
                <div class="alert alert-danger mt-3">
                    <h6><i class="fas fa-exclamation-triangle me-2"></i> Errors (${errors.length})</h6>
                    <ul class="mb-0" style="max-height:200px; overflow-y:auto;">
                        ${errors.map(function(error) {
                            return `<li>${error}</li>`;
                        }).join('')}
                    </ul>
                </div>
            `;
        }
        
        if (created > 0) {
            html += `
                <div class="alert alert-success mt-3">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Success!</strong> ${created} product(s) have been created successfully.
                    ${created > 0 ? ' Stock movements have been recorded.' : ''}
                </div>
            `;
        }
        
        $('#resultsContent').html(html);
        $('#resultsModal').modal('show');
    }

    // ==========================================
    // VIEW PRODUCTS BUTTON
    // ==========================================
    $('#viewProductsBtn').on('click', function() {
        window.location.href = '{{ route("admin.products.index") }}';
    });

    // ==========================================
    // MODAL RESET
    // ==========================================
    $('#resultsModal').on('hidden.bs.modal', function() {
        $('html, body').css('overflow', 'auto');
    });

    // ==========================================
    // DRAG & DROP HIGHLIGHT
    // ==========================================
    $('input[name="file"]').on('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('border-primary');
    }).on('dragleave', function(e) {
        e.preventDefault();
        $(this).removeClass('border-primary');
    }).on('drop', function(e) {
        e.preventDefault();
        $(this).removeClass('border-primary');
        var files = e.originalEvent.dataTransfer.files;
        if (files.length) {
            this.files = files;
            $(this).trigger('change');
        }
    });
});
</script>
@endpush
@endsection