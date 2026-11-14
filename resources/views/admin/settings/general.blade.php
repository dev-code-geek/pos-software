@extends('layouts.admin')

@section('title', 'General Settings')
@section('page-title', 'General Settings')

@section('admin-content')
<div class="row justify-content-center">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-cog text-primary me-2"></i> General Settings</h5>
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

                <form action="{{ route('admin.settings.general.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label for="app_name" class="form-label fw-bold">Application Name <span class="text-danger">*</span></label>
                        <input type="text" name="app_name" id="app_name" class="form-control @error('app_name') is-invalid @enderror" value="{{ old('app_name', $settings['app_name']) }}" required>
                        @error('app_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="timezone" class="form-label fw-bold">Timezone <span class="text-danger">*</span></label>
                            <select name="timezone" id="timezone" class="form-select @error('timezone') is-invalid @enderror" required>
                                @foreach($timezones as $tz)
                                    <option value="{{ $tz }}" {{ old('timezone', $settings['timezone']) == $tz ? 'selected' : '' }}>{{ $tz }}</option>
                                @endforeach
                            </select>
                            @error('timezone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="locale" class="form-label fw-bold">Locale <span class="text-danger">*</span></label>
                            <select name="locale" id="locale" class="form-select @error('locale') is-invalid @enderror" required>
                                @foreach($locales as $key => $label)
                                    <option value="{{ $key }}" {{ old('locale', $settings['locale']) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('locale')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="currency" class="form-label fw-bold">Currency <span class="text-danger">*</span></label>
                            <select name="currency" id="currency" class="form-select @error('currency') is-invalid @enderror" required>
                                <option value="USD" {{ old('currency', $settings['currency']) == 'USD' ? 'selected' : '' }}>USD ($)</option>
                                <option value="EUR" {{ old('currency', $settings['currency']) == 'EUR' ? 'selected' : '' }}>EUR (€)</option>
                                <option value="GBP" {{ old('currency', $settings['currency']) == 'GBP' ? 'selected' : '' }}>GBP (£)</option>
                                <option value="KES" {{ old('currency', $settings['currency']) == 'KES' ? 'selected' : '' }}>KES (KSh)</option>
                                <option value="TZS" {{ old('currency', $settings['currency']) == 'TZS' ? 'selected' : '' }}>TZS (TSh)</option>
                            </select>
                            @error('currency')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="items_per_page" class="form-label fw-bold">Items Per Page <span class="text-danger">*</span></label>
                            <input type="number" name="items_per_page" id="items_per_page" class="form-control @error('items_per_page') is-invalid @enderror" value="{{ old('items_per_page', $settings['items_per_page']) }}" min="5" max="100" required>
                            @error('items_per_page')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="date_format" class="form-label fw-bold">Date Format <span class="text-danger">*</span></label>
                            <select name="date_format" id="date_format" class="form-select @error('date_format') is-invalid @enderror" required>
                                <option value="Y-m-d" {{ old('date_format', $settings['date_format']) == 'Y-m-d' ? 'selected' : '' }}>YYYY-MM-DD</option>
                                <option value="m/d/Y" {{ old('date_format', $settings['date_format']) == 'm/d/Y' ? 'selected' : '' }}>MM/DD/YYYY</option>
                                <option value="d/m/Y" {{ old('date_format', $settings['date_format']) == 'd/m/Y' ? 'selected' : '' }}>DD/MM/YYYY</option>
                                <option value="M d, Y" {{ old('date_format', $settings['date_format']) == 'M d, Y' ? 'selected' : '' }}>Mon DD, YYYY</option>
                                <option value="d M Y" {{ old('date_format', $settings['date_format']) == 'd M Y' ? 'selected' : '' }}>DD Mon YYYY</option>
                            </select>
                            @error('date_format')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="time_format" class="form-label fw-bold">Time Format <span class="text-danger">*</span></label>
                            <select name="time_format" id="time_format" class="form-select @error('time_format') is-invalid @enderror" required>
                                <option value="H:i:s" {{ old('time_format', $settings['time_format']) == 'H:i:s' ? 'selected' : '' }}>24-Hour (HH:MM:SS)</option>
                                <option value="h:i:s A" {{ old('time_format', $settings['time_format']) == 'h:i:s A' ? 'selected' : '' }}>12-Hour (HH:MM:SS AM/PM)</option>
                                <option value="H:i" {{ old('time_format', $settings['time_format']) == 'H:i' ? 'selected' : '' }}>24-Hour (HH:MM)</option>
                                <option value="h:i A" {{ old('time_format', $settings['time_format']) == 'h:i A' ? 'selected' : '' }}>12-Hour (HH:MM AM/PM)</option>
                            </select>
                            @error('time_format')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 d-flex align-items-center">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="enable_registration" id="enable_registration" value="1" {{ old('enable_registration', $settings['enable_registration']) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="enable_registration">Enable User Registration</label>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex align-items-center">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="maintenance_mode" id="maintenance_mode" value="1" {{ old('maintenance_mode', $settings['maintenance_mode']) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold text-danger" for="maintenance_mode">Maintenance Mode</label>
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