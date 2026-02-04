@extends('tenant::layout')

@section('title', __('tenant::settings.edit') . ' - ' . __('tenant::settings.calendar_settings'))

@section('content')
    <div class="container-fluid py-4">
        {{-- Page Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-2 fw-bold">
                    <i class="bi bi-calendar3 text-primary me-2"></i>
                    {{ __('tenant::settings.edit') }} {{ __('tenant::settings.calendar_settings') }}
                </h1>
                <p class="text-muted mb-0">{{ __('tenant::settings.calendar_settings_description') }}</p>
            </div>
            <div>
                <a href="{{ route('tenant.settings.calendar.index') }}"
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

        <form action="{{ route('tenant.settings.calendar.update') }}" method="POST">
            @csrf
            <div class="row g-4">
                <div class="col-lg-8">
                    {{-- Time Settings --}}
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="fw-bold mb-0">
                                <i class="bi bi-clock me-2"></i>{{ __('tenant::settings.time_settings') }}
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                {{-- Hour Format --}}
                                <div class="col-md-4">
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

                                {{-- Start Hour --}}
                                <div class="col-md-4">
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
                                <div class="col-md-4">
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
                            </div>
                        </div>
                    </div>

                    {{-- Default Values --}}
                    <div class="card border-0 shadow-sm rounded-4 mt-4">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="fw-bold mb-0">
                                <i class="bi bi-gear me-2"></i>{{ __('tenant::settings.default_values') }}
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                {{-- Default Activity Type --}}
                                <div class="col-md-6">
                                    <label for="defaultactivitytype" class="form-label fw-semibold">
                                        {{ __('tenant::settings.default_activity_type') }}
                                    </label>
                                    <select class="form-select @error('defaultactivitytype') is-invalid @enderror"
                                        id="defaultactivitytype" name="defaultactivitytype">
                                        @foreach ($activityTypes as $type => $label)
                                            <option value="{{ $type }}"
                                                {{ old('defaultactivitytype', $user->defaultactivitytype ?? 'Call') == $type ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('defaultactivitytype')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Default Event Status --}}
                                <div class="col-md-6">
                                    <label for="defaulteventstatus" class="form-label fw-semibold">
                                        {{ __('tenant::settings.default_event_status') }}
                                    </label>
                                    <select class="form-select @error('defaulteventstatus') is-invalid @enderror"
                                        id="defaulteventstatus" name="defaulteventstatus">
                                        @foreach ($eventStatuses as $status => $label)
                                            <option value="{{ $status }}"
                                                {{ old('defaulteventstatus', $user->defaulteventstatus ?? 'Planned') == $status ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('defaulteventstatus')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Call Duration --}}
                                <div class="col-md-6">
                                    <label for="callduration" class="form-label fw-semibold">
                                        {{ __('tenant::settings.call_duration') }}
                                    </label>
                                    <div class="input-group">
                                        <input type="number"
                                            class="form-control @error('callduration') is-invalid @enderror"
                                            id="callduration" name="callduration"
                                            value="{{ old('callduration', $user->callduration ?? 5) }}" min="1"
                                            max="1440">
                                        <span class="input-group-text">minutes</span>
                                        @error('callduration')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Other Event Duration --}}
                                <div class="col-md-6">
                                    <label for="othereventduration" class="form-label fw-semibold">
                                        {{ __('tenant::settings.event_duration') }}
                                    </label>
                                    <div class="input-group">
                                        <input type="number"
                                            class="form-control @error('othereventduration') is-invalid @enderror"
                                            id="othereventduration" name="othereventduration"
                                            value="{{ old('othereventduration', $user->othereventduration ?? 60) }}"
                                            min="1" max="1440">
                                        <span class="input-group-text">minutes</span>
                                        @error('othereventduration')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- View Settings --}}
                    <div class="card border-0 shadow-sm rounded-4 mt-4">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="fw-bold mb-0">
                                <i class="bi bi-eye me-2"></i>{{ __('tenant::settings.view_settings') }}
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                {{-- Calendar View --}}
                                <div class="col-md-6">
                                    <label for="defaultcalendarview" class="form-label fw-semibold">
                                        {{ __('tenant::settings.calendar_view') }}
                                    </label>
                                    <select class="form-select @error('defaultcalendarview') is-invalid @enderror"
                                        id="defaultcalendarview" name="defaultcalendarview">
                                        @foreach ($calendarViews as $view => $label)
                                            <option value="{{ $view }}"
                                                {{ old('defaultcalendarview', $user->defaultcalendarview ?? 'Calendar') == $view ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('defaultcalendarview')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Reminder Interval --}}
                                <div class="col-md-6">
                                    <label for="reminder_interval" class="form-label fw-semibold">
                                        {{ __('tenant::settings.reminder_interval') }}
                                    </label>
                                    <select class="form-select @error('reminder_interval') is-invalid @enderror"
                                        id="reminder_interval" name="reminder_interval">
                                        @foreach ($reminderIntervals as $interval => $label)
                                            <option value="{{ $interval }}"
                                                {{ old('reminder_interval', $user->reminder_interval ?? '15 Minutes') == $interval ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('reminder_interval')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('tenant.settings.calendar.index') }}"
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
                                    Set start/end hours to match your working schedule
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    Default values speed up activity creation
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    Call duration is typically shorter than meetings
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    Reminder intervals help you prepare for activities
                                </li>
                                <li class="mb-0">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    Calendar view shows events in a visual timeline
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
