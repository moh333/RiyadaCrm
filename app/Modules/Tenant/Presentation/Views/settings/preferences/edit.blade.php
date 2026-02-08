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
                <div class="col-lg-8">
                    {{-- Basic Information --}}
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="fw-bold mb-0">
                                <i
                                    class="bi bi-person-badge me-2 text-primary"></i>{{ __('tenant::settings.basic_information') }}
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="first_name"
                                        class="form-label fw-semibold">{{ __('tenant::settings.first_name') }}</label>
                                    <input type="text"
                                        class="form-control rounded-pill px-3 @error('first_name') is-invalid @enderror"
                                        id="first_name" name="first_name"
                                        value="{{ old('first_name', $user->first_name ?? '') }}">
                                    @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="last_name" class="form-label fw-semibold">
                                        {{ __('tenant::settings.last_name') }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                        class="form-control rounded-pill px-3 @error('last_name') is-invalid @enderror"
                                        id="last_name" name="last_name"
                                        value="{{ old('last_name', $user->last_name ?? '') }}" required>
                                    @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="email1" class="form-label fw-semibold">
                                        {{ __('tenant::settings.email') }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="email"
                                        class="form-control rounded-pill px-3 @error('email1') is-invalid @enderror"
                                        id="email1" name="email1" value="{{ old('email1', $user->email1 ?? '') }}" required>
                                    @error('email1') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="reports_to_id"
                                        class="form-label fw-semibold">{{ __('tenant::settings.reports_to') }}</label>
                                    <select
                                        class="form-select rounded-pill px-3 @error('reports_to_id') is-invalid @enderror"
                                        id="reports_to_id" name="reports_to_id">
                                        <option value="">-- None --</option>
                                        @foreach($users as $id => $name)
                                            <option value="{{ $id }}" {{ old('reports_to_id', $user->reports_to_id ?? null) == $id ? 'selected' : '' }}>
                                                {{ $name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('reports_to_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- More Information --}}
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="fw-bold mb-0">
                                <i
                                    class="bi bi-info-circle me-2 text-primary"></i>{{ __('tenant::settings.more_information') }}
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="title"
                                        class="form-label fw-semibold">{{ __('tenant::settings.title') }}</label>
                                    <input type="text"
                                        class="form-control rounded-pill px-3 @error('title') is-invalid @enderror"
                                        id="title" name="title" value="{{ old('title', $user->title ?? '') }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="department"
                                        class="form-label fw-semibold">{{ __('tenant::settings.department') }}</label>
                                    <input type="text"
                                        class="form-control rounded-pill px-3 @error('department') is-invalid @enderror"
                                        id="department" name="department"
                                        value="{{ old('department', $user->department ?? '') }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="phone_work"
                                        class="form-label fw-semibold">{{ __('tenant::settings.office_phone') }}</label>
                                    <input type="text"
                                        class="form-control rounded-pill px-3 @error('phone_work') is-invalid @enderror"
                                        id="phone_work" name="phone_work"
                                        value="{{ old('phone_work', $user->phone_work ?? '') }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="phone_mobile"
                                        class="form-label fw-semibold">{{ __('tenant::settings.mobile_phone') }}</label>
                                    <input type="text"
                                        class="form-control rounded-pill px-3 @error('phone_mobile') is-invalid @enderror"
                                        id="phone_mobile" name="phone_mobile"
                                        value="{{ old('phone_mobile', $user->phone_mobile ?? '') }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="phone_home"
                                        class="form-label fw-semibold">{{ __('tenant::settings.home_phone') }}</label>
                                    <input type="text"
                                        class="form-control rounded-pill px-3 @error('phone_home') is-invalid @enderror"
                                        id="phone_home" name="phone_home"
                                        value="{{ old('phone_home', $user->phone_home ?? '') }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="phone_fax"
                                        class="form-label fw-semibold">{{ __('tenant::settings.fax') }}</label>
                                    <input type="text"
                                        class="form-control rounded-pill px-3 @error('phone_fax') is-invalid @enderror"
                                        id="phone_fax" name="phone_fax" value="{{ old('phone_fax', $user->phone_fax ?? '') }}">
                                </div>
                                <div class="col-12">
                                    <label for="signature"
                                        class="form-label fw-semibold">{{ __('tenant::settings.signature') }}</label>
                                    <textarea class="form-control rounded-4 px-3 @error('signature') is-invalid @enderror"
                                        id="signature" name="signature"
                                        rows="3">{{ old('signature', $user->signature ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Address Information --}}
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="fw-bold mb-0">
                                <i
                                    class="bi bi-geo-alt me-2 text-primary"></i>{{ __('tenant::settings.address_information') }}
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="address_street"
                                        class="form-label fw-semibold">{{ __('tenant::settings.street') }}</label>
                                    <textarea
                                        class="form-control rounded-4 px-3 @error('address_street') is-invalid @enderror"
                                        id="address_street" name="address_street"
                                        rows="2">{{ old('address_street', $user->address_street ?? '') }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label for="address_city"
                                        class="form-label fw-semibold">{{ __('tenant::settings.city') }}</label>
                                    <input type="text"
                                        class="form-control rounded-pill px-3 @error('address_city') is-invalid @enderror"
                                        id="address_city" name="address_city"
                                        value="{{ old('address_city', $user->address_city ?? '') }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="address_state"
                                        class="form-label fw-semibold">{{ __('tenant::settings.state') }}</label>
                                    <input type="text"
                                        class="form-control rounded-pill px-3 @error('address_state') is-invalid @enderror"
                                        id="address_state" name="address_state"
                                        value="{{ old('address_state', $user->address_state ?? '') }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="address_country"
                                        class="form-label fw-semibold">{{ __('tenant::settings.country') }}</label>
                                    <input type="text"
                                        class="form-control rounded-pill px-3 @error('address_country') is-invalid @enderror"
                                        id="address_country" name="address_country"
                                        value="{{ old('address_country', $user->address_country ?? '') }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="address_postalcode"
                                        class="form-label fw-semibold">{{ __('tenant::settings.postal_code') }}</label>
                                    <input type="text"
                                        class="form-control rounded-pill px-3 @error('address_postalcode') is-invalid @enderror"
                                        id="address_postalcode" name="address_postalcode"
                                        value="{{ old('address_postalcode', $user->address_postalcode ?? '') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Display & Localization --}}
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="fw-bold mb-0">
                                <i class="bi bi-globe me-2 text-primary"></i>{{ __('tenant::settings.advanced_options') }}
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                {{-- Language --}}
                                <div class="col-md-6">
                                    <label for="language"
                                        class="form-label fw-semibold">{{ __('tenant::settings.language') }} <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select rounded-pill px-3" id="language" name="language" required>
                                        @foreach ($languages as $code => $name)
                                            <option value="{{ $code }}" {{ old('language', $user->language ?? 'en') == $code ? 'selected' : '' }}>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Currency --}}
                                <div class="col-md-6">
                                    <label for="currency_id"
                                        class="form-label fw-semibold">{{ __('tenant::settings.currency') }} <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select rounded-pill px-3" id="currency_id" name="currency_id"
                                        required>
                                        @foreach ($currencies as $id => $name)
                                            <option value="{{ $id }}" {{ old('currency_id', $user->currency_id ?? 1) == $id ? 'selected' : '' }}>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Date Format --}}
                                <div class="col-md-6">
                                    <label for="date_format"
                                        class="form-label fw-semibold">{{ __('tenant::settings.date_format') }} <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select rounded-pill px-3" id="date_format" name="date_format"
                                        required>
                                        @foreach ($dateFormats as $format => $example)
                                            <option value="{{ $format }}" {{ old('date_format', $user->date_format ?? 'yyyy-mm-dd') == $format ? 'selected' : '' }}>{{ $format }} ({{ $example }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Hour Format --}}
                                <div class="col-md-6">
                                    <label for="hour_format"
                                        class="form-label fw-semibold">{{ __('tenant::settings.hour_format') }} <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select rounded-pill px-3" id="hour_format" name="hour_format"
                                        required>
                                        @foreach ($hourFormats as $val => $label)
                                            <option value="{{ $val }}" {{ old('hour_format', $user->hour_format ?? '12') == $val ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>


                                {{-- Timezone --}}
                                <div class="col-md-6">
                                    <label for="time_zone"
                                        class="form-label fw-semibold">{{ __('tenant::settings.time_zone') }} <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select rounded-pill px-3" id="time_zone" name="time_zone" required>
                                        @foreach ($timezones as $tz => $label)
                                            <option value="{{ $tz }}" {{ old('time_zone', $user->time_zone ?? 'UTC') == $tz ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Currency Decimals --}}
                                <div class="col-md-6">
                                    <label for="no_of_currency_decimals"
                                        class="form-label fw-semibold">{{ __('tenant::settings.currency_decimals') }}</label>
                                    <select class="form-select rounded-pill px-3" id="no_of_currency_decimals"
                                        name="no_of_currency_decimals">
                                        @foreach([0, 1, 2, 3, 4, 5] as $val)
                                            <option value="{{ $val }}" {{ old('no_of_currency_decimals', $user->no_of_currency_decimals ?? 2) == $val ? 'selected' : '' }}>{{ $val }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Truncate Trailing Zeros --}}
                                <div class="col-md-6 d-flex align-items-end">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="truncate_trailing_zeros"
                                            name="truncate_trailing_zeros" value="1" {{ old('truncate_trailing_zeros', $user->truncate_trailing_zeros ?? 0) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold"
                                            for="truncate_trailing_zeros">{{ __('tenant::settings.truncate_trailing_zeros') }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- UI Preferences --}}
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="fw-bold mb-0">
                                <i
                                    class="bi bi-layout-text-window me-2 text-primary"></i>{{ __('tenant::settings.ui_preferences') }}
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="start_hour"
                                        class="form-label fw-semibold">{{ __('tenant::settings.start_hour') }}</label>
                                    <select class="form-select rounded-pill px-3" id="start_hour" name="start_hour">
                                        @foreach ($startHours as $hour => $label)
                                            <option value="{{ $hour }}" {{ old('start_hour', $user->start_hour ?? '09:00') == $hour ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="end_hour"
                                        class="form-label fw-semibold">{{ __('tenant::settings.end_hour') }}</label>
                                    <select class="form-select rounded-pill px-3" id="end_hour" name="end_hour">
                                        @foreach ($endHours as $hour => $label)
                                            <option value="{{ $hour }}" {{ old('end_hour', $user->end_hour ?? '18:00') == $hour ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label for="defaultlandingpage"
                                        class="form-label fw-semibold">{{ __('tenant::settings.landing_page') }}</label>
                                    <input type="text" class="form-control rounded-pill px-3" id="defaultlandingpage"
                                        name="defaultlandingpage"
                                        value="{{ old('defaultlandingpage', $user->defaultlandingpage ?? '') }}"
                                        placeholder="e.g. Dashboard">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mb-5">
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