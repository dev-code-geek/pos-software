@extends('layouts.admin')

@section('title', 'Backup')
@section('page-title', 'Database Backup')

@section('admin-content')
<style>
.backup-stat-card {
    border-radius: 12px;
    padding: 15px 20px;
    background: #fff;
    border: 1px solid #e2e8f0;
    transition: all 0.2s ease;
}
.backup-stat-card:hover {
    border-color: #4f46e5;
    box-shadow: 0 4px 12px rgba(79,70,229,0.08);
}
.backup-stat-card .number {
    font-size: 1.8rem;
    font-weight: 700;
}
.backup-stat-card .label {
    font-size: 0.8rem;
    color: #64748b;
}
</style>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="fas fa-database text-success me-2"></i> Database Backups</h5>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.settings.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
            @if(auth()->user()->can('settings.backup_create') || auth()->user()->can('backup.create'))
            <form action="{{ route('admin.settings.backup.run') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-success" id="runBackupBtn">
                    <i class="fas fa-download me-1"></i> Run Backup Now
                </button>
            </form>
            @endif
        </div>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Backup Stats -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="backup-stat-card text-center">
                    <div class="number text-primary">{{ $backupStats['total'] ?? 0 }}</div>
                    <div class="label">Total Backups</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="backup-stat-card text-center">
                    <div class="number text-success">{{ $backupStats['total_size'] ?? '0 B' }}</div>
                    <div class="label">Total Size</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="backup-stat-card text-center">
                    <div class="number text-info">{{ $backupStats['last_backup'] ?? 'Never' }}</div>
                    <div class="label">Last Backup</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="backup-stat-card text-center">
                    <div class="number text-warning">{{ $backupStats['total'] ?? 0 }}</div>
                    <div class="label">Available</div>
                </div>
            </div>
        </div>

        <!-- Backup Schedule -->
        @if(auth()->user()->can('settings.backup_schedule') || auth()->user()->can('backup.schedule'))
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="fas fa-clock me-2"></i> Backup Schedule</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.settings.backup.schedule') }}" method="POST">
                    @csrf
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">Frequency</label>
                            <select name="frequency" class="form-select">
                                <option value="daily" {{ $backupSchedule == 'daily' ? 'selected' : '' }}>Daily</option>
                                <option value="weekly" {{ $backupSchedule == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                <option value="monthly" {{ $backupSchedule == 'monthly' ? 'selected' : '' }}>Monthly</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">Time</label>
                            <input type="time" name="time" class="form-control" value="{{ $backupTime }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">Keep Days</label>
                            <input type="number" name="keep_days" class="form-control" value="{{ $backupKeepDays }}" min="1" max="365">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-save me-1"></i> Update Schedule
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        @endif

        <!-- Backup List -->
        @if(count($backups) > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>File</th>
                            <th>Size</th>
                            <th>Created</th>
                            <th style="width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($backups as $backup)
                        <tr>
                            <td>
                                <i class="fas fa-file-archive text-success me-2"></i>
                                {{ $backup['name'] ?? 'N/A' }}
                            </td>
                            <td>{{ $backup['size'] ?? 'N/A' }}</td>
                            <td>{{ $backup['date'] ?? 'N/A' }}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    @if(auth()->user()->can('settings.backup_download') || auth()->user()->can('backup.download'))
                                    <a href="{{ route('admin.settings.backup.download', ['filename' => $backup['name']]) }}" class="btn btn-outline-primary" title="Download">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    @endif
                                    @if(auth()->user()->can('settings.backup_delete') || auth()->user()->can('backup.delete'))
                                    <form action="{{ route('admin.settings.backup.delete', ['filename' => $backup['name']]) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this backup?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-database fa-4x text-muted mb-3 d-block"></i>
                <h5>No backups yet</h5>
                <p class="text-muted">Click "Run Backup Now" to create your first database backup.</p>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#runBackupBtn').on('click', function(e) {
        e.preventDefault();
        var btn = $(this);
        var form = btn.closest('form');
        
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Running...');
        
        $.ajax({
            url: form.attr('action'),
            type: form.attr('method'),
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 3000,
                        showConfirmButton: true
                    }).then(function() {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message || 'Failed to create backup.'
                    });
                    btn.prop('disabled', false).html('<i class="fas fa-download me-1"></i> Run Backup Now');
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: xhr.responseJSON?.message || 'Failed to create backup.'
                });
                btn.prop('disabled', false).html('<i class="fas fa-download me-1"></i> Run Backup Now');
            }
        });
    });
});
</script>
@endpush
@endsection