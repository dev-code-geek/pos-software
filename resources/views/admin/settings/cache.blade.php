@extends('layouts.admin')

@section('title', 'Cache Management')
@section('page-title', 'Cache Management')

@section('admin-content')
<div class="row justify-content-center">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-bolt text-purple me-2" style="color:#7c3aed;"></i> Cache Management</h5>
                <a href="{{ route('admin.settings.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Settings
                </a>
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

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <div class="icon-circle bg-primary bg-opacity-10 text-primary mx-auto mb-3" style="width:64px;height:64px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.8rem;">
                                    <i class="fas fa-cogs"></i>
                                </div>
                                <h6 class="fw-bold">Configuration Cache</h6>
                                <p class="text-muted small">Cache application configuration</p>
                                <span class="badge bg-{{ $cacheInfo['config_cached'] ? 'success' : 'secondary' }}">
                                    {{ $cacheInfo['config_cached'] ? 'Cached' : 'Not Cached' }}
                                </span>
                                <div class="mt-2">
                                    <form action="{{ route('admin.settings.cache.clear') }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="type" value="config">
                                        <button type="submit" class="btn btn-sm btn-outline-primary">Clear</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <div class="icon-circle bg-success bg-opacity-10 text-success mx-auto mb-3" style="width:64px;height:64px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.8rem;">
                                    <i class="fas fa-route"></i>
                                </div>
                                <h6 class="fw-bold">Route Cache</h6>
                                <p class="text-muted small">Cache application routes</p>
                                <span class="badge bg-{{ $cacheInfo['routes_cached'] ? 'success' : 'secondary' }}">
                                    {{ $cacheInfo['routes_cached'] ? 'Cached' : 'Not Cached' }}
                                </span>
                                <div class="mt-2">
                                    <form action="{{ route('admin.settings.cache.clear') }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="type" value="route">
                                        <button type="submit" class="btn btn-sm btn-outline-success">Clear</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <div class="icon-circle bg-warning bg-opacity-10 text-warning mx-auto mb-3" style="width:64px;height:64px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.8rem;">
                                    <i class="fas fa-eye"></i>
                                </div>
                                <h6 class="fw-bold">View Cache</h6>
                                <p class="text-muted small">Cache compiled views</p>
                                <span class="badge bg-{{ $cacheInfo['views_cached'] ? 'success' : 'secondary' }}">
                                    {{ $cacheInfo['views_cached'] ? 'Cached' : 'Not Cached' }}
                                </span>
                                <div class="mt-2">
                                    <form action="{{ route('admin.settings.cache.clear') }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="type" value="view">
                                        <button type="submit" class="btn btn-sm btn-outline-warning">Clear</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <div class="icon-circle bg-danger bg-opacity-10 text-danger mx-auto mb-3" style="width:64px;height:64px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.8rem;">
                                    <i class="fas fa-trash-alt"></i>
                                </div>
                                <h6 class="fw-bold">Clear All Cache</h6>
                                <p class="text-muted small">Clear all cached data</p>
                                <span class="badge bg-secondary">All</span>
                                <div class="mt-2">
                                    <form action="{{ route('admin.settings.cache.clear') }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="type" value="all">
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to clear all cache?');">
                                            Clear All
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 p-3 bg-light rounded">
                    <h6 class="fw-bold"><i class="fas fa-info-circle me-2"></i>Cache Information</h6>
                    <p class="mb-0 small text-muted">Cache Driver: <span class="fw-bold">{{ $cacheInfo['cache_driver'] }}</span></p>
                    <p class="mb-0 small text-muted">Caching helps improve application performance by storing compiled data.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.btn-purple {
    background: #7c3aed;
    border-color: #7c3aed;
    color: #fff;
}
.btn-purple:hover {
    background: #6d28d9;
    border-color: #6d28d9;
    color: #fff;
}
</style>
@endsection