@extends('tenant::layout')

@section('title', __('tenant::settings.calendar_settings'))

@section('content')
    <div class="container-fluid py-4">
        {{-- Page Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-2 fw-bold">
                    <i class="bi bi-calendar3 text-primary me-2"></i>
                    {{ __('tenant::settings.calendar_settings') }}
                </h1>
                <p class="text-muted mb-0">{{ __('tenant::settings.calendar_settings_description') }}</p>
            </div>
            <div>
                <a href="{{ route('tenant.settings.calendar.edit') }}" class="btn btn-primary rounded-pill px-4">
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
            {{-- Time Settings --}}
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="fw-bold mb-0">
                            <i class="bi bi-clock me-2"></i>{{ __('tenant::settings.time_settings') }}
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <td class="text-muted fw-semibold" style="width: 50%;">
                                        {{ __('tenant::settings.hour_format') }}
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ $user->hour_format ?? '12' }} Hour</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-semibold">{{ __('tenant::settings.start_hour') }}</td>
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
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Default Values --}}
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="fw-bold mb-0">
                            <i class="bi bi-gear me-2"></i>{{ __('tenant::settings.default_values') }}
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <td class="text-muted fw-semibold" style="width: 50%;">
                                        {{ __('tenant::settings.default_activity_type') }}
                                    </td>
                                    <td>
                                        {{ $user->defaultactivitytype ?? 'Call' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-semibold">{{ __('tenant::settings.default_event_status') }}
                                    </td>
                                    <td>
                                        {{ $user->defaulteventstatus ?? 'Planned' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-semibold">{{ __('tenant::settings.call_duration') }}</td>
                                    <td>
                                        {{ $user->callduration ?? 5 }} minutes
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-semibold">{{ __('tenant::settings.event_duration') }}</td>
                                    <td>
                                        {{ $user->othereventduration ?? 60 }} minutes
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- View Settings --}}
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="fw-bold mb-0">
                            <i class="bi bi-eye me-2"></i>{{ __('tenant::settings.view_settings') }}
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <td class="text-muted fw-semibold" style="width: 50%;">
                                        {{ __('tenant::settings.calendar_view') }}
                                    </td>
                                    <td>
                                        {{ $user->activity_view ?? 'Calendar View' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-semibold">{{ __('tenant::settings.reminder_interval') }}</td>
                                    <td>
                                        {{ $user->reminder_interval ?? '15 Minutes' }}
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