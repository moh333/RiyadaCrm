@extends('tenant::layout')

@section('title', __('tenant::settings.edit') . ' - ' . __('tenant::settings.user_preferences'))

@section('content')
    <div class="container-fluid py-4">
        {{-- Page Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-2 fw-bold">
                    <i class="bi bi-sliders text-primary me-2"></i>
                    {{ __('tenant::settings.edit') }} {{ __('tenant::settings.user_preferences') }}
                </h1>
                <p class="text-muted mb-0">{{ __('tenant::settings.user_preferences_description') }}</p>
            </div>
            <div>
                <a href="{{ route('tenant.settings.preferences.index') }}"
                    class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="bi bi-arrow-left me-2"></i>{{ __('tenant::settings.back') }}
                </a>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Error!</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form action="{{ route('tenant.settings.preferences.update') }}" method="POST">
            @csrf
            <div class="row g-4">
                {{-- Display & Localization --}}
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="fw-bold mb-0">
                                <i class="bi bi-globe me-2"></i>{{ __('tenant::settings.display_localization') }}
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                {{-- Language --}}
                                <div class="col-md-6">
                                    <label for="language" class="form-label fw-semibold">
                                        {{ __('tenant::settings.language') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('language') is-invalid @enderror" id="language"
                                        name="language" required>
                                        @foreach ($languages as $code => $name)
                                            <option value="{{ $code }}"
                                                {{ old('language', $user->language ?? 'en') == $code ? 'selected' : '' }}>
                                                {{ $name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('language')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Currency --}}
                                <div class="col-md-6">
                                    <label for="currency_id" class="form-label fw-semibold">
                                        {{ __('tenant::settings.currency') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('currency_id') is-invalid @enderror"
                                        id="currency_id" name="currency_id" required>
                                        @foreach ($currencies as $id => $name)
                                            <option value="{{ $id }}"
                                                {{ old('currency_id', $user->currency_id ?? 1) == $id ? 'selected' : '' }}>
                                                {{ $name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('currency_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Date Format --}}
                                <div class="col-md-6">
                                    <label for="date_format" class="form-label fw-semibold">
                                        {{ __('tenant::settings.date_format') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('date_format') is-invalid @enderror"
                                        id="date_format" name="date_format" required>
                                        @foreach ($dateFormats as $format => $example)
                                            <option value="{{ $format }}"
                                                {{ old('date_format', $user->date_format ?? 'yyyy-mm-dd') == $format ? 'selected' : '' }}>
                                                {{ $format }} ({{ $example }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('date_format')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Hour Format --}}
                                <div class="col-md-6">
                                    <label for="hour_format" class="form-label fw-semibold">
                                        {{ __('tenant::settings.hour_format') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('hour_format') is-invalid @enderror"
                                        id="hour_format" name="hour_format" required>
                                        @foreach ($hourFormats as $format => $label)
                                            <option value="{{ $format }}"
                                                {{ old('hour_format', $user->hour_format ?? '12') == $format ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('hour_format')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Timezone --}}
                                <div class="col-md-6">
                                    <label for="time_zone" class="form-label fw-semibold">
                                        {{ __('tenant::settings.time_zone') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('time_zone') is-invalid @enderror" id="time_zone"
                                        name="time_zone" required>
                                        @foreach ($timezones as $tz => $label)
                                            <option value="{{ $tz }}"
                                                {{ old('time_zone', $user->time_zone ?? 'America/New_York') == $tz ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('time_zone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Currency Decimals --}}
                                <div class="col-md-6">
                                    <label for="no_of_currency_decimals" class="form-label fw-semibold">
                                        {{ __('tenant::settings.currency_decimals') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('no_of_currency_decimals') is-invalid @enderror"
                                        id="no_of_currency_decimals" name="no_of_currency_decimals" required>
                                        <option value="2"
                                            {{ old('no_of_currency_decimals', $user->no_of_currency_decimals ?? 2) == 2 ? 'selected' : '' }}>
                                            2 (e.g., 100.00)</option>
                                        <option value="3"
                                            {{ old('no_of_currency_decimals', $user->no_of_currency_decimals ?? 2) == 3 ? 'selected' : '' }}>
                                            3 (e.g., 100.000)</option>
                                        <option value="4"
                                            {{ old('no_of_currency_decimals', $user->no_of_currency_decimals ?? 2) == 4 ? 'selected' : '' }}>
                                            4 (e.g., 100.0000)</option>
                                    </select>
                                    @error('no_of_currency_decimals')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- UI Preferences --}}
                    <div class="card border-0 shadow-sm rounded-4 mt-4">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="fw-bold mb-0">
                                <i class="bi bi-layout-text-window me-2"></i>{{ __('tenant::settings.ui_preferences') }}
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                {{-- Start Hour --}}
                                <div class="col-md-6">
                                    <label for="start_hour" class="form-label fw-semibold">
                                        {{ __('tenant::settings.start_hour') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('start_hour') is-invalid @enderror"
                                        id="start_hour" name="start_hour" required>
                                        @foreach ($startHours as $hour => $label)
                                            <option value="{{ $hour }}"
                                                {{ old('start_hour', $user->start_hour ?? '08:00') == $hour ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('start_hour')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- End Hour --}}
                                <div class="col-md-6">
                                    <label for="end_hour" class="form-label fw-semibold">
                                        {{ __('tenant::settings.end_hour') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('end_hour') is-invalid @enderror" id="end_hour"
                                        name="end_hour" required>
                                        @foreach ($endHours as $hour => $label)
                                            <option value="{{ $hour }}"
                                                {{ old('end_hour', $user->end_hour ?? '18:00') == $hour ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('end_hour')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Landing Page --}}
                                <div class="col-12">
                                    <label for="defaultlandingpage" class="form-label fw-semibold">
                                        {{ __('tenant::settings.landing_page') }}
                                    </label>
                                    <select class="form-select @error('defaultlandingpage') is-invalid @enderror"
                                        id="defaultlandingpage" name="defaultlandingpage">
                                        <option value="Dashboard"
                                            {{ old('defaultlandingpage', $user->defaultlandingpage ?? 'Dashboard') == 'Dashboard' ? 'selected' : '' }}>
                                            Dashboard</option>
                                        <option value="Home"
                                            {{ old('defaultlandingpage', $user->defaultlandingpage ?? 'Dashboard') == 'Home' ? 'selected' : '' }}>
                                            Home</option>
                                    </select>
                                    <div class="form-text">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Page to show after login
                                    </div>
                                    @error('defaultlandingpage')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('tenant.settings.preferences.index') }}"
                            class="btn btn-outline-secondary rounded-pill px-4">
                            {{ __('tenant::settings.cancel') }}
                        </a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">
                            <i class="bi bi-check-lg me-2"></i>{{ __('tenant::settings.save_changes') }}
                        </button>
                    </div>
                </div>

                {{-- Tips Sidebar --}}
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 bg-light sticky-top" style="top: 20px;">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-3">
                                <i class="bi bi-lightbulb text-warning me-2"></i>{{ __('tenant::settings.tips') }}
                            </h6>
                            <ul class="list-unstyled small">
                                <li class="mb-2">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    Choose your preferred language for the interface
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    Currency affects how amounts are displayed
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    Date format changes how dates appear in lists and forms
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    Timezone ensures correct time display for your location
                                </li>
                                <li class="mb-0">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    Start/End hours define your working day in calendar
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
