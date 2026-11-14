@extends('layouts.admin')

@section('title', 'System Info')
@section('page-title', 'System Information')

@section('admin-content')
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="fas fa-info-circle text-secondary me-2"></i> System Information</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.settings.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
            <button class="btn btn-sm btn-outline-primary" onclick="window.location.reload();">
                <i class="fas fa-sync me-1"></i> Refresh
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <tbody>
                    <tr><th style="width: 260px; background: #f8fafc;">PHP Version</th><td><span class="badge bg-primary">{{ $info['php_version'] }}</span></td></tr>
                    <tr><th style="background: #f8fafc;">Laravel Version</th><td><span class="badge bg-primary">{{ $info['laravel_version'] }}</span></td></tr>
                    <tr><th style="background: #f8fafc;">Server Software</th><td>{{ $info['server_software'] }}</td></tr>
                    <tr><th style="background: #f8fafc;">Server IP</th><td>{{ $info['server_ip'] }}</td></tr>
                    <tr><th style="background: #f8fafc;">Environment</th><td><span class="badge bg-{{ $info['environment'] === 'production' ? 'danger' : ($info['environment'] === 'staging' ? 'warning' : 'info') }}">{{ ucfirst($info['environment']) }}</span></td></tr>
                    <tr><th style="background: #f8fafc;">Debug Mode</th><td><span class="badge bg-{{ $info['debug_mode'] === 'Enabled' ? 'warning text-dark' : 'success' }}">{{ $info['debug_mode'] }}</span></td></tr>
                    <tr><th style="background: #f8fafc;">Timezone</th><td>{{ $info['timezone'] }}</td></tr>
                    <tr><th style="background: #f8fafc;">Default Database Connection</th><td>{{ $info['default_db'] }}</td></tr>
                    <tr><th style="background: #f8fafc;">Database Connection</th><td>{{ $info['db_connection'] }}</td></tr>
                    <tr><th style="background: #f8fafc;">Database Host</th><td>{{ $info['db_host'] }}</td></tr>
                    <tr><th style="background: #f8fafc;">Database Name</th><td><span class="badge bg-secondary">{{ $info['db_database'] }}</span></td></tr>
                    <tr><th style="background: #f8fafc;">Database Tables</th><td><span class="badge bg-info">{{ $info['db_tables'] ?? 'N/A' }}</span></td></tr>
                    <tr><th style="background: #f8fafc;">Cache Driver</th><td>{{ $info['cache_driver'] }}</td></tr>
                    <tr><th style="background: #f8fafc;">Queue Driver</th><td>{{ $info['queue_driver'] }}</td></tr>
                    <tr><th style="background: #f8fafc;">Session Driver</th><td>{{ $info['session_driver'] }}</td></tr>
                    <tr><th style="background: #f8fafc;">PHP Memory Limit</th><td>{{ $info['memory_limit'] }}</td></tr>
                    <tr><th style="background: #f8fafc;">Max Execution Time</th><td>{{ $info['max_execution_time'] }}</td></tr>
                    <tr><th style="background: #f8fafc;">Upload Max Filesize</th><td>{{ $info['upload_max_filesize'] }}</td></tr>
                    <tr><th style="background: #f8fafc;">Post Max Size</th><td>{{ $info['post_max_size'] }}</td></tr>
                    <tr><th style="background: #f8fafc;">Disk Free Space</th><td><span class="text-success">{{ $info['disk_free_space'] }}</span></td></tr>
                    <tr><th style="background: #f8fafc;">Disk Total Space</th><td>{{ $info['disk_total_space'] }}</td></tr>
                    <tr><th style="background: #f8fafc;">Storage Used</th><td><span class="text-warning">{{ $info['storage_used'] }}</span></td></tr>
                    <tr><th style="background: #f8fafc;">PHP Extensions</th><td><small>{{ $info['php_extensions'] }}</small></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection