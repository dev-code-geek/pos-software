@extends('layouts.admin')

@section('title', 'Settings')
@section('page-title', 'System Settings')

@section('admin-content')
<div class="row g-3">
    @can('settings.view')
    <div class="col-xl-4 col-lg-6 col-md-6">
        <div class="card h-100">
            <div class="card-body text-center p-4">
                <div class="icon-circle bg-primary bg-opacity-10 text-primary mx-auto mb-3" style="width:64px;height:64px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.8rem;">
                    <i class="fas fa-cog"></i>
                </div>
                <h5 class="fw-bold">General Settings</h5>
                <p class="text-muted small">Manage system-wide general settings</p>
                @can('settings.edit')
                <a href="{{ route('admin.settings.general') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-edit me-1"></i> Configure
                </a>
                @endcan
            </div>
        </div>
    </div>
    @endcan

    @can('settings.manage')
    <div class="col-xl-4 col-lg-6 col-md-6">
        <div class="card h-100">
            <div class="card-body text-center p-4">
                <div class="icon-circle bg-success bg-opacity-10 text-success mx-auto mb-3" style="width:64px;height:64px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.8rem;">
                    <i class="fas fa-database"></i>
                </div>
                <h5 class="fw-bold">Backup</h5>
                <p class="text-muted small">Manage database backups</p>
                <a href="{{ route('admin.settings.backup') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-download me-1"></i> Manage Backups
                </a>
            </div>
        </div>
    </div>
    @endcan

    @can('settings.view')
    <div class="col-xl-4 col-lg-6 col-md-6">
        <div class="card h-100">
            <div class="card-body text-center p-4">
                <div class="icon-circle bg-warning bg-opacity-10 text-warning mx-auto mb-3" style="width:64px;height:64px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.8rem;">
                    <i class="fas fa-clock"></i>
                </div>
                <h5 class="fw-bold">System Logs</h5>
                <p class="text-muted small">View system activity logs</p>
                <a href="{{ route('admin.settings.logs') }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-eye me-1"></i> View Logs
                </a>
            </div>
        </div>
    </div>
    @endcan

    @can('settings.edit')
    <div class="col-xl-4 col-lg-6 col-md-6">
        <div class="card h-100">
            <div class="card-body text-center p-4">
                <div class="icon-circle bg-info bg-opacity-10 text-info mx-auto mb-3" style="width:64px;height:64px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.8rem;">
                    <i class="fas fa-envelope"></i>
                </div>
                <h5 class="fw-bold">Email Settings</h5>
                <p class="text-muted small">Configure email notifications</p>
                <a href="{{ route('admin.settings.email') }}" class="btn btn-info btn-sm">
                    <i class="fas fa-edit me-1"></i> Configure
                </a>
            </div>
        </div>
    </div>
    @endcan

    @can('settings.edit')
    <div class="col-xl-4 col-lg-6 col-md-6">
        <div class="card h-100">
            <div class="card-body text-center p-4">
                <div class="icon-circle bg-danger bg-opacity-10 text-danger mx-auto mb-3" style="width:64px;height:64px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.8rem;">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h5 class="fw-bold">Security Settings</h5>
                <p class="text-muted small">Manage security settings</p>
                <a href="{{ route('admin.settings.security') }}" class="btn btn-danger btn-sm">
                    <i class="fas fa-edit me-1"></i> Configure
                </a>
            </div>
        </div>
    </div>
    @endcan

    @can('settings.view')
    <div class="col-xl-4 col-lg-6 col-md-6">
        <div class="card h-100">
            <div class="card-body text-center p-4">
                <div class="icon-circle bg-secondary bg-opacity-10 text-secondary mx-auto mb-3" style="width:64px;height:64px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.8rem;">
                    <i class="fas fa-info-circle"></i>
                </div>
                <h5 class="fw-bold">System Info</h5>
                <p class="text-muted small">View system information</p>
                <a href="{{ route('admin.settings.system-info') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-eye me-1"></i> View Info
                </a>
            </div>
        </div>
    </div>
    @endcan

    @can('settings.manage')
    <div class="col-xl-4 col-lg-6 col-md-6">
        <div class="card h-100">
            <div class="card-body text-center p-4">
                <div class="icon-circle bg-purple bg-opacity-10 text-purple mx-auto mb-3" style="width:64px;height:64px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.8rem;color:#7c3aed;">
                    <i class="fas fa-bolt"></i>
                </div>
                <h5 class="fw-bold">Cache Management</h5>
                <p class="text-muted small">Clear application cache</p>
                <a href="{{ route('admin.settings.cache') }}" class="btn btn-purple btn-sm" style="background:#7c3aed;border-color:#7c3aed;color:#fff;">
                    <i class="fas fa-bolt me-1"></i> Manage Cache
                </a>
            </div>
        </div>
    </div>
    @endcan

    @can('settings.manage')
    <div class="col-xl-4 col-lg-6 col-md-6">
        <div class="card h-100">
            <div class="card-body text-center p-4">
                <div class="icon-circle bg-orange bg-opacity-10 text-orange mx-auto mb-3" style="width:64px;height:64px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.8rem;color:#f97316;">
                    <i class="fas fa-power-off"></i>
                </div>
                <h5 class="fw-bold">Maintenance Mode</h5>
                <p class="text-muted small">Put application in maintenance mode</p>
                <a href="{{ route('admin.settings.maintenance') }}" class="btn btn-orange btn-sm" style="background:#f97316;border-color:#f97316;color:#fff;">
                    <i class="fas fa-power-off me-1"></i> Manage Maintenance
                </a>
            </div>
        </div>
    </div>
    @endcan
</div>

<style>
.btn-purple:hover {
    background: #6d28d9 !important;
    border-color: #6d28d9 !important;
    color: #fff !important;
}
.btn-orange:hover {
    background: #ea580c !important;
    border-color: #ea580c !important;
    color: #fff !important;
}
</style>
@endsection