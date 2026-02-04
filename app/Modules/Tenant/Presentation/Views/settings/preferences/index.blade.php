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
                                    <td class="text-muted fw-semibold" style="width: 40%;">
                                        {{ __('tenant::settings.language') }}
                                    </td>
                                    <td>
                                        {{-- TODO: Load from user --}}
                                        <span class="badge bg-primary">English</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-semibold">{{ __('tenant::settings.currency') }}</td>
                                    <td>
                                        {{-- TODO: Load from user --}}
                                        USD - US Dollar
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-semibold">{{ __('tenant::settings.date_format') }}</td>
                                    <td>
                                        {{-- TODO: Load from user --}}
                                        yyyy-mm-dd
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-semibold">{{ __('tenant::settings.hour_format') }}</td>
                                    <td>
                                        {{-- TODO: Load from user --}}
                                        12 Hour (AM/PM)
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-semibold">{{ __('tenant::settings.time_zone') }}</td>
                                    <td>
                                        {{-- TODO: Load from user --}}
                                        America/New_York
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-semibold">{{ __('tenant::settings.currency_decimals') }}</td>
                                    <td>
                                        {{-- TODO: Load from user --}}
                                        2
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
                                        {{-- TODO: Load from user --}}
                                        08:00 AM
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-semibold">{{ __('tenant::settings.end_hour') }}</td>
                                    <td>
                                        {{-- TODO: Load from user --}}
                                        06:00 PM
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-semibold">{{ __('tenant::settings.landing_page') }}</td>
                                    <td>
                                        {{-- TODO: Load from user --}}
                                        Dashboard
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Info Card --}}
        <div class="card border-0 shadow-sm rounded-4 bg-light mt-4">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3">
                    <i class="bi bi-info-circle text-primary me-2"></i>About User Preferences
                </h6>
                <ul class="list-unstyled small mb-0">
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        These settings are personal and don't affect other users
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Language and date format affect how you view data throughout the CRM
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Currency settings affect financial displays and reports
                    </li>
                    <li class="mb-0">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Start and end hours define your working day in calendar views
                    </li>
                </ul>
            </div>
        </div>
    </div>
@endsection