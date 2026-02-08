@extends('tenant::layout')

@section('title', __('tenant::settings.user_preferences'))

@section('content')
    <div class="container-fluid py-4">
        {{-- Page Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-2 fw-bold">
                    <i class="bi bi-sliders text-primary me-2"></i>
                    {{ __('tenant::settings.user_preferences') }}
                </h1>
                <p class="text-muted mb-0">{{ __('tenant::settings.user_preferences_description') }}</p>
            </div>
            <div>
                <a href="{{ route('tenant.settings.preferences.edit') }}" class="btn btn-primary rounded-pill px-4">
                    <i class="bi bi-pencil me-2"></i>{{ __('tenant::settings.edit') }}
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row g-4">
            {{-- Display & Localization --}}
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="fw-bold mb-0">
                            <i class="bi bi-globe me-2"></i>{{ __('tenant::settings.display_localization') }}
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                <tr>
                                    <td class="text-muted fw-semibold" style="width: 40%;">
                                        {{ __('tenant::settings.language') }}
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ $user->language ?? 'en' }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-semibold">{{ __('tenant::settings.currency') }}</td>
                                    <td>
                                        {{ $user->currency_id ?? '1' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-semibold">{{ __('tenant::settings.date_format') }}</td>
                                    <td>
                                        {{ $user->date_format ?? 'yyyy-mm-dd' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-semibold">{{ __('tenant::settings.hour_format') }}</td>
                                    <td>
                                        {{ $user->hour_format ?? '12' }} Hour
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-semibold">{{ __('tenant::settings.time_zone') }}</td>
                                    <td>
                                        {{ $user->time_zone ?? 'UTC' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-semibold">{{ __('tenant::settings.currency_decimals') }}</td>
                                    <td>
                                        {{ $user->no_of_currency_decimals ?? 2 }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- UI Preferences --}}
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="fw-bold mb-0">
                            <i class="bi bi-layout-text-window me-2"></i>{{ __('tenant::settings.ui_preferences') }}
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <td class="text-muted fw-semibold" style="width: 40%;">
                                        {{ __('tenant::settings.start_hour') }}
                                    </td>
                                    <td>
                                        {{ $user->start_hour ?? '08:00' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-semibold">{{ __('tenant::settings.end_hour') }}</td>
                                    <td>
                                        {{ $user->end_hour ?? '18:00' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-semibold">{{ __('tenant::settings.landing_page') }}</td>
                                    <td>
                                        {{ $user->defaultlandingpage ?? 'Dashboard' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection