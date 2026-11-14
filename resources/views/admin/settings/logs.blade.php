@extends('layouts.admin')

@section('title', 'System Logs')
@section('page-title', 'System Logs')

@section('admin-content')
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="fas fa-clock text-warning me-2"></i> System Logs</h5>
        <div class="d-flex align-items-center gap-2">
            <form action="{{ route('admin.settings.logs.clear') }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to clear all logs?');">
                    <i class="fas fa-trash me-1"></i> Clear Logs
                </button>
            </form>
            <a href="{{ route('admin.settings.logs.download') }}" class="btn btn-sm btn-success">
                <i class="fas fa-download me-1"></i> Download
            </a>
            <a href="{{ route('admin.settings.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        @if(session('success'))
            <div class="alert alert-success m-3">
                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger m-3">
                <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
            </div>
        @endif

        <!-- Log Info -->
        <div class="p-3 bg-light border-bottom d-flex flex-wrap gap-3">
            <div>
                <span class="text-muted">File:</span>
                <code>{{ $logInfo['path'] ?? 'N/A' }}</code>
            </div>
            <div>
                <span class="text-muted">Size:</span>
                <span class="fw-bold">{{ $logInfo['size'] ?? 'N/A' }}</span>
            </div>
            <div>
                <span class="text-muted">Modified:</span>
                <span class="fw-bold">{{ $logInfo['modified'] ?? 'N/A' }}</span>
            </div>
            <div>
                <span class="text-muted">Errors Found:</span>
                <span class="badge bg-danger">{{ $errorCount ?? 0 }}</span>
            </div>
            <div>
                <span class="text-muted">Showing:</span>
                <span class="fw-bold">{{ count($lines) }} lines</span>
            </div>
        </div>

        @if(!$logInfo['exists'])
            <div class="text-center py-5">
                <i class="fas fa-file-circle-exclamation fa-4x text-muted mb-3 d-block"></i>
                <h5>No log file found</h5>
                <p class="text-muted">Expected at: <code>{{ $logInfo['path'] ?? 'N/A' }}</code></p>
            </div>
        @elseif(count($lines) === 0)
            <div class="text-center py-5">
                <i class="fas fa-file fa-4x text-muted mb-3 d-block"></i>
                <h5>Log file is empty</h5>
            </div>
        @else
            <!-- Recent Errors -->
            @if(count($errorLines) > 0)
                <div class="p-3 bg-danger bg-opacity-10 border-bottom">
                    <h6 class="text-danger"><i class="fas fa-exclamation-circle me-2"></i>Recent Errors</h6>
                    <div class="bg-dark text-white p-2 rounded" style="font-size:0.75rem;max-height:150px;overflow-y:auto;">
                        @foreach($errorLines as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            <pre class="p-3 mb-0" style="max-height: 70vh; overflow-y: auto; font-size: 0.75rem; background: #0f172a; color: #e2e8f0; white-space: pre-wrap; word-break: break-word;">@foreach($lines as $line){{ $line }}@endforeach</pre>
        @endif
    </div>
</div>
@endsection