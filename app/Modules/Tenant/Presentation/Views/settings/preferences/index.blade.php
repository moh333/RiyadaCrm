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

        <div class="row g-4 mb-5">
            {{-- Basic Information & More Information --}}
            <div class="col-lg-7">
                {{-- Basic info --}}
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
                                <label
                                    class="text-muted small fw-bold d-block">{{ __('tenant::settings.first_name') }}</label>
                                <span class="fw-semibold">{{ $user->first_name ?? '--' }}</span>
                            </div>
                            <div class="col-md-6">
                                <label
                                    class="text-muted small fw-bold d-block">{{ __('tenant::settings.last_name') }}</label>
                                <span class="fw-semibold text-primary">{{ $user->last_name ?? '--' }}</span>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small fw-bold d-block">{{ __('tenant::settings.email') }}</label>
                                <span class="fw-semibold">{{ $user->email1 ?? '--' }}</span>
                            </div>
                            <div class="col-md-6">
                                <label
                                    class="text-muted small fw-bold d-block">{{ __('tenant::settings.reports_to') }}</label>
                                <span class="fw-semibold">{{ $user->reports_to_id ?? '--' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- More Information --}}
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="fw-bold mb-0">
                            <i class="bi bi-info-circle me-2 text-primary"></i>{{ __('tenant::settings.more_information') }}
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="text-muted small fw-bold d-block">{{ __('tenant::settings.title') }}</label>
                                <span class="fw-semibold">{{ $user->title ?? '--' }}</span>
                            </div>
                            <div class="col-md-6">
                                <label
                                    class="text-muted small fw-bold d-block">{{ __('tenant::settings.department') }}</label>
                                <span class="fw-semibold">{{ $user->department ?? '--' }}</span>
                            </div>
                            <div class="col-md-6">
                                <label
                                    class="text-muted small fw-bold d-block">{{ __('tenant::settings.office_phone') }}</label>
                                <span class="fw-semibold text-primary"><i
                                        class="bi bi-telephone me-1"></i>{{ ($user->phone_work ?? null) ?: '--' }}</span>
                            </div>
                            <div class="col-md-6">
                                <label
                                    class="text-muted small fw-bold d-block">{{ __('tenant::settings.mobile_phone') }}</label>
                                <span class="fw-semibold text-success"><i
                                        class="bi bi-phone me-1"></i>{{ ($user->phone_mobile ?? null) ?: '--' }}</span>
                            </div>
                            <div class="col-md-6">
                                <label
                                    class="text-muted small fw-bold d-block">{{ __('tenant::settings.home_phone') }}</label>
                                <span class="fw-semibold">{{ $user->phone_home ?? '--' }}</span>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small fw-bold d-block">{{ __('tenant::settings.fax') }}</label>
                                <span class="fw-semibold">{{ $user->phone_fax ?? '--' }}</span>
                            </div>
                            <div class="col-12">
                                <label
                                    class="text-muted small fw-bold d-block">{{ __('tenant::settings.signature') }}</label>
                                <div class="bg-light p-3 rounded-3 mt-1 small">
                                    {{ ($user->signature ?? null) ?: 'No signature defined' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                {{-- Address Information --}}
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="fw-bold mb-0">
                            <i class="bi bi-geo-alt me-2 text-primary"></i>{{ __('tenant::settings.address_information') }}
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="text-muted small fw-bold d-block">{{ __('tenant::settings.street') }}</label>
                            <span class="fw-semibold">{{ $user->address_street ?? '--' }}</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="text-muted small fw-bold d-block">{{ __('tenant::settings.city') }}</label>
                                <span class="fw-semibold">{{ $user->address_city ?? '--' }}</span>
                            </div>
                            <div class="col-6">
                                <label class="text-muted small fw-bold d-block">{{ __('tenant::settings.state') }}</label>
                                <span class="fw-semibold">{{ $user->address_state ?? '--' }}</span>
                            </div>
                            <div class="col-6">
                                <label class="text-muted small fw-bold d-block">{{ __('tenant::settings.country') }}</label>
                                <span class="fw-semibold">{{ $user->address_country ?? '--' }}</span>
                            </div>
                            <div class="col-6">
                                <label
                                    class="text-muted small fw-bold d-block">{{ __('tenant::settings.postal_code') }}</label>
                                <span class="fw-semibold">{{ $user->address_postalcode ?? '--' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Advanced Options --}}
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="fw-bold mb-0">
                            <i class="bi bi-gear me-2 text-primary"></i>{{ __('tenant::settings.advanced_options') }}
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <table class="table table-borderless table-sm mb-0">
                            <tbody>
                                <tr>
                                    <td class="text-muted fw-semibold py-2">{{ __('tenant::settings.language') }}</td>
                                    <td class="py-2"><span
                                            class="badge bg-primary-subtle text-primary">{{ strtoupper($user->language ?: 'en') }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-semibold py-2">{{ __('tenant::settings.currency') }}</td>
                                    <td class="py-2">{{ $user->currency_id }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-semibold py-2">{{ __('tenant::settings.date_format') }}</td>
                                    <td class="py-2">{{ $user->date_format }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-semibold py-2">{{ __('tenant::settings.hour_format') }}</td>
                                    <td class="py-2">{{ $user->hour_format == '24' ? '24 Hour' : '12 Hour (AM/PM)' }}</td>
                                </tr>

                                <tr>
                                    <td class="text-muted fw-semibold py-2">{{ __('tenant::settings.time_zone') }}</td>
                                    <td class="py-2"><small>{{ $user->time_zone }}</small></td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-semibold py-2">{{ __('tenant::settings.currency_decimals') }}
                                    </td>
                                    <td class="py-2">{{ $user->no_of_currency_decimals }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-semibold py-2">{{ __('tenant::settings.start_hour') }}</td>
                                    <td class="py-2"><i class="bi bi-clock me-1 text-info"></i>{{ $user->start_hour }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-semibold py-2">{{ __('tenant::settings.end_hour') }}</td>
                                    <td class="py-2">{{ $user->end_hour }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-semibold py-2"> {{ __('tenant::settings.landing_page') }} </td>
                                    <td class="py-2 text-success fw-bold"> {{ ($user->defaultlandingpage ?? null) ?: 'Dashboard' }} </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection