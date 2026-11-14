@extends('layouts.admin')

@section('title', 'My Profile')
@section('page-title', 'Profile Settings')

@section('admin-content')
<div class="row">
    <div class="col-12">
        <!-- Profile Header -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center flex-wrap gap-4">
                    <!-- Avatar -->
                    <div class="position-relative">
                        <img src="{{ auth()->user()->avatar_url }}" 
                             alt="{{ auth()->user()->name }}" 
                             class="rounded-circle border border-3 border-primary"
                             style="width: 80px; height: 80px; object-fit: cover;">
                        <button class="btn btn-sm btn-primary rounded-circle position-absolute bottom-0 end-0 p-1" 
                                data-bs-toggle="modal" data-bs-target="#avatarModal"
                                style="width: 28px; height: 28px; font-size: 0.7rem;">
                            <i class="fas fa-camera"></i>
                        </button>
                    </div>
                    <div>
                        <h4 class="mb-0">{{ auth()->user()->name }}</h4>
                        <p class="text-muted mb-1">
                            <i class="fas fa-envelope me-1"></i> {{ auth()->user()->email }}
                        </p>
                        <p class="text-muted mb-0">
                            <i class="fas fa-user-tag me-1"></i> 
                            <span class="badge bg-primary">{{ auth()->user()->role ?? 'User' }}</span>
                        </p>
                    </div>
                    <div class="ms-auto">
                        <span class="badge bg-success">
                            <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i> Active
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <!-- Profile Information -->
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">
                            <i class="fas fa-user text-primary me-2"></i> Profile Information
                        </span>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('profile.update') }}">
                            @csrf
                            @method('patch')

                            <div class="mb-3">
                                <label for="name" class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                                       value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label fw-bold">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" 
                                       value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="phone" class="form-label fw-bold">Phone Number</label>
                                <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" 
                                       value="{{ old('phone', $user->phone) }}" placeholder="+1 234 567 8900">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="bio" class="form-label fw-bold">Bio</label>
                                <textarea name="bio" id="bio" class="form-control @error('bio') is-invalid @enderror" 
                                          rows="3" placeholder="Tell us a little about yourself...">{{ old('bio', $user->bio) }}</textarea>
                                @error('bio')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            @if(auth()->user()->department)
                            <div class="mb-3">
                                <label class="form-label fw-bold">Department</label>
                                <p class="form-control-static">
                                    <i class="fas fa-building me-1 text-primary"></i> 
                                    {{ auth()->user()->department->name }}
                                </p>
                            </div>
                            @endif

                            <div class="mb-3">
                                <label class="form-label fw-bold">Member Since</label>
                                <p class="form-control-static">
                                    <i class="fas fa-calendar-alt me-1 text-primary"></i> 
                                    {{ auth()->user()->created_at->format('F d, Y') }}
                                </p>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Update Profile
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Change Password -->
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">
                            <i class="fas fa-lock text-warning me-2"></i> Change Password
                        </span>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('profile.password.update') }}">
                            @csrf

                            <div class="mb-3">
                                <label for="current_password" class="form-label fw-bold">Current Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" name="current_password" id="current_password" 
                                           class="form-control @error('current_password') is-invalid @enderror" 
                                           placeholder="Enter current password" required>
                                </div>
                                @error('current_password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label fw-bold">New Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                                    <input type="password" name="password" id="password" 
                                           class="form-control @error('password') is-invalid @enderror" 
                                           placeholder="Enter new password" required>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label fw-bold">Confirm New Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-check-circle"></i></span>
                                    <input type="password" name="password_confirmation" id="password_confirmation" 
                                           class="form-control" placeholder="Confirm new password" required>
                                </div>
                            </div>

                            <div class="alert alert-info small">
                                <i class="fas fa-info-circle me-2"></i>
                                Password must be at least 8 characters long.
                            </div>

                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-key me-1"></i> Change Password
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Account Actions -->
                <div class="card mt-3">
                    <div class="card-header">
                        <span class="card-title text-danger">
                            <i class="fas fa-exclamation-triangle text-danger me-2"></i> Account Actions
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                                <i class="fas fa-trash me-1"></i> Delete Account
                            </button>
                        </div>
                        <p class="text-muted small mt-2 mb-0">
                            <i class="fas fa-info-circle me-1"></i>
                            Deleting your account will permanently remove all your data.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- AVATAR MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="avatarModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-camera text-primary me-2"></i> Update Profile Photo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('profile.avatar.update') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body text-center">
                    <div class="mb-3">
                        <img src="{{ auth()->user()->avatar_url }}" 
                             alt="{{ auth()->user()->name }}" 
                             class="rounded-circle mb-3 border border-3 border-primary"
                             style="width: 120px; height: 120px; object-fit: cover;">
                    </div>
                    <div class="mb-3">
                        <label for="avatar" class="form-label fw-bold">Choose Image</label>
                        <input type="file" name="avatar" id="avatar" class="form-control @error('avatar') is-invalid @enderror" 
                               accept="image/*" required>
                        @error('avatar')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Max file size: 2MB (JPEG, PNG, GIF)</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload me-1"></i> Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- DELETE ACCOUNT MODAL -->
<!-- ============================================ -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">
                    <i class="fas fa-exclamation-triangle text-danger me-2"></i> Delete Account
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('profile.destroy') }}">
                @csrf
                @method('delete')
                <div class="modal-body">
                    <p class="fw-bold">Are you sure you want to delete your account?</p>
                    <p class="text-muted small">This action cannot be undone. All your data will be permanently removed.</p>
                    <div class="mb-3">
                        <label for="delete_password" class="form-label fw-bold">Enter your password to confirm <span class="text-danger">*</span></label>
                        <input type="password" name="password" id="delete_password" class="form-control" placeholder="Enter your password" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i> Delete Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);

    // Password strength indicator (optional)
    $('#password').on('input', function() {
        var password = $(this).val();
        var strength = 0;
        
        if (password.length >= 8) strength++;
        if (password.match(/[a-z]+/)) strength++;
        if (password.match(/[A-Z]+/)) strength++;
        if (password.match(/[0-9]+/)) strength++;
        if (password.match(/[$@#&!]+/)) strength++;
        
        var color = '';
        var text = '';
        
        if (strength <= 2) {
            color = 'danger';
            text = 'Weak';
        } else if (strength <= 3) {
            color = 'warning';
            text = 'Medium';
        } else if (strength <= 4) {
            color = 'info';
            text = 'Good';
        } else {
            color = 'success';
            text = 'Strong';
        }
        
        // You can add a strength indicator here
    });
});
</script>
@endpush
@endsection