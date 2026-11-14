@extends('layouts.admin')

@section('title', 'Security Settings')
@section('page-title', 'Security Settings')

@section('admin-content')
<div class="row justify-content-center">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-shield-alt text-danger me-2"></i> Security Settings</h5>
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

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.settings.security.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="session_lifetime" class="form-label fw-bold">Session Lifetime (minutes) <span class="text-danger">*</span></label>
                            <input type="number" name="session_lifetime" id="session_lifetime" class="form-control @error('session_lifetime') is-invalid @enderror" value="{{ old('session_lifetime', $settings['session_lifetime']) }}" min="5" max="1440" required>
                            @error('session_lifetime')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">How long a user session should last (in minutes).</small>
                        </div>
                        <div class="col-md-6">
                            <label for="password_min_length" class="form-label fw-bold">Minimum Password Length <span class="text-danger">*</span></label>
                            <input type="number" name="password_min_length" id="password_min_length" class="form-control @error('password_min_length') is-invalid @enderror" value="{{ old('password_min_length', $settings['password_min_length']) }}" min="6" max="64" required>
                            @error('password_min_length')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Minimum number of characters required for passwords.</small>
                        </div>
                        <div class="col-md-6">
                            <label for="max_login_attempts" class="form-label fw-bold">Max Login Attempts <span class="text-danger">*</span></label>
                            <input type="number" name="max_login_attempts" id="max_login_attempts" class="form-control @error('max_login_attempts') is-invalid @enderror" value="{{ old('max_login_attempts', $settings['max_login_attempts']) }}" min="1" max="20" required>
                            @error('max_login_attempts')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Number of failed login attempts before lockout.</small>
                        </div>
                        <div class="col-md-6">
                            <label for="lockout_duration" class="form-label fw-bold">Lockout Duration (minutes) <span class="text-danger">*</span></label>
                            <input type="number" name="lockout_duration" id="lockout_duration" class="form-control @error('lockout_duration') is-invalid @enderror" value="{{ old('lockout_duration', $settings['lockout_duration']) }}" min="1" max="60" required>
                            @error('lockout_duration')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">How long a user should be locked out after max attempts.</small>
                        </div>
                        <div class="col-md-6">
                            <label for="password_expiry_days" class="form-label fw-bold">Password Expiry (days) <span class="text-danger">*</span></label>
                            <input type="number" name="password_expiry_days" id="password_expiry_days" class="form-control @error('password_expiry_days') is-invalid @enderror" value="{{ old('password_expiry_days', $settings['password_expiry_days']) }}" min="0" max="365" required>
                            @error('password_expiry_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Number of days after which password expires. 0 = never.</small>
                        </div>
                        <div class="col-md-6">
                            <label for="remember_me_days" class="form-label fw-bold">Remember Me Days <span class="text-danger">*</span></label>
                            <input type="number" name="remember_me_days" id="remember_me_days" class="form-control @error('remember_me_days') is-invalid @enderror" value="{{ old('remember_me_days', $settings['remember_me_days']) }}" min="0" max="365" required>
                            @error('remember_me_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">How long the "Remember Me" token should last. 0 = disabled.</small>
                        </div>
                        <div class="col-md-6 d-flex align-items-center">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="require_2fa" id="require_2fa" value="1" {{ old('require_2fa', $settings['require_2fa']) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="require_2fa">Require Two-Factor Authentication</label>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex align-items-center">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="session_expire_on_close" id="session_expire_on_close" value="1" {{ old('session_expire_on_close', $settings['session_expire_on_close']) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="session_expire_on_close">Expire Session on Browser Close</label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Save Changes
                        </button>
                        <a href="{{ route('admin.settings.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection