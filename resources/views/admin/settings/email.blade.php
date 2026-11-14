@extends('layouts.admin')

@section('title', 'Email Settings')
@section('page-title', 'Email Settings')

@section('admin-content')
<div class="row justify-content-center">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-envelope text-info me-2"></i> Email Settings</h5>
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

                @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show">
                        <i class="fas fa-exclamation-triangle me-2"></i> {{ session('warning') }}
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

                <form action="{{ route('admin.settings.email.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="mail_mailer" class="form-label fw-bold">Mailer <span class="text-danger">*</span></label>
                            <select name="mail_mailer" id="mail_mailer" class="form-select @error('mail_mailer') is-invalid @enderror" required>
                                @foreach($mailers as $mailer)
                                    <option value="{{ $mailer }}" {{ old('mail_mailer', $settings['mail_mailer']) == $mailer ? 'selected' : '' }}>{{ ucfirst($mailer) }}</option>
                                @endforeach
                            </select>
                            @error('mail_mailer')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="mail_port" class="form-label fw-bold">Port <span class="text-danger">*</span></label>
                            <input type="number" name="mail_port" id="mail_port" class="form-control @error('mail_port') is-invalid @enderror" value="{{ old('mail_port', $settings['mail_port']) }}" min="1" max="65535" required>
                            @error('mail_port')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label for="mail_host" class="form-label fw-bold">Host <span class="text-danger">*</span></label>
                            <input type="text" name="mail_host" id="mail_host" class="form-control @error('mail_host') is-invalid @enderror" value="{{ old('mail_host', $settings['mail_host']) }}" required>
                            @error('mail_host')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="mail_encryption" class="form-label fw-bold">Encryption</label>
                            <select name="mail_encryption" id="mail_encryption" class="form-select">
                                <option value="">None</option>
                                @foreach($encryptions as $enc)
                                    @if($enc)
                                        <option value="{{ $enc }}" {{ old('mail_encryption', $settings['mail_encryption']) == $enc ? 'selected' : '' }}>{{ strtoupper($enc) }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="mail_username" class="form-label fw-bold">Username</label>
                            <input type="text" name="mail_username" id="mail_username" class="form-control" value="{{ old('mail_username', $settings['mail_username']) }}" placeholder="Enter username">
                        </div>
                        <div class="col-md-6">
                            <label for="mail_password" class="form-label fw-bold">Password</label>
                            <input type="password" name="mail_password" id="mail_password" class="form-control" placeholder="Enter password (leave blank to keep current)">
                        </div>
                        <div class="col-md-6">
                            <label for="mail_from_address" class="form-label fw-bold">From Address <span class="text-danger">*</span></label>
                            <input type="email" name="mail_from_address" id="mail_from_address" class="form-control @error('mail_from_address') is-invalid @enderror" value="{{ old('mail_from_address', $settings['mail_from_address']) }}" required>
                            @error('mail_from_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="mail_from_name" class="form-label fw-bold">From Name <span class="text-danger">*</span></label>
                            <input type="text" name="mail_from_name" id="mail_from_name" class="form-control @error('mail_from_name') is-invalid @enderror" value="{{ old('mail_from_name', $settings['mail_from_name']) }}" required>
                            @error('mail_from_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Save Changes
                        </button>
                        <button type="button" class="btn btn-info" id="testEmailBtn">
                            <i class="fas fa-paper-plane me-1"></i> Send Test Email
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

@push('scripts')
<script>
$(document).ready(function() {
    $('#testEmailBtn').on('click', function() {
        Swal.fire({
            title: 'Send Test Email',
            input: 'email',
            inputLabel: 'Email Address',
            inputPlaceholder: 'Enter email to send test to',
            inputValue: '{{ auth()->user()->email }}',
            showCancelButton: true,
            confirmButtonText: 'Send',
            cancelButtonText: 'Cancel',
            inputValidator: function(value) {
                if (!value) {
                    return 'Please enter an email address!';
                }
                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                    return 'Please enter a valid email address!';
                }
            }
        }).then(function(result) {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Sending...',
                    allowOutsideClick: false,
                    didOpen: function() {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: '{{ route("admin.settings.email.test") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        email: result.value
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Sent!',
                                text: response.message,
                                timer: 3000,
                                showConfirmButton: true
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Failed!',
                                text: response.message || 'Failed to send test email.'
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: xhr.responseJSON?.message || 'Failed to send test email.'
                        });
                    }
                });
            }
        });
    });
});
</script>
@endpush
@endsection