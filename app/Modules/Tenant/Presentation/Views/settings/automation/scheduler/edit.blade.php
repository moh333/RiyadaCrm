@extends('tenant::layout')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="fw-bold mb-1">{{ __('tenant::settings.edit_scheduler') }}</h2>
                        <p class="text-muted">{{ $cronTask->name }}</p>
                    </div>
                    <a href="{{ route('tenant.settings.crm.automation.scheduler.index') }}"
                        class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>{{ __('tenant::settings.back') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <form method="POST"
                            action="{{ route('tenant.settings.crm.automation.scheduler.update', $cronTask->id) }}">
                            @csrf
                            @method('PUT')

                            {{-- Task Information (Read-only) --}}
                            <div class="mb-4">
                                <h5 class="fw-semibold mb-3">{{ __('tenant::settings.basic_information') }}</h5>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">{{ __('tenant::settings.task_name') }}</label>
                                    <input type="text" class="form-control" value="{{ $cronTask->name }}" disabled>
                                </div>

                                @if($cronTask->description)
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">{{ __('tenant::settings.description') }}</label>
                                        <textarea class="form-control" rows="2" disabled>{{ $cronTask->description }}</textarea>
                                    </div>
                                @endif

                                @if($cronTask->module)
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">{{ __('tenant::settings.module') }}</label>
                                        <input type="text" class="form-control"
                                            value="{{ $modules[$cronTask->module] ?? $cronTask->module }}" disabled>
                                    </div>
                                @endif

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">{{ __('tenant::settings.handler_file') }}</label>
                                    <input type="text" class="form-control" value="{{ $cronTask->handler_file }}" disabled>
                                </div>
                            </div>

                            {{-- Editable Settings --}}
                            <div class="mb-4">
                                <h5 class="fw-semibold mb-3">{{ __('tenant::settings.settings') }}</h5>

                                <div class="mb-3">
                                    <label for="frequency" class="form-label fw-semibold">
                                        {{ __('tenant::settings.frequency_seconds') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" class="form-control @error('frequency') is-invalid @enderror"
                                        id="frequency" name="frequency" value="{{ old('frequency', $cronTask->frequency) }}"
                                        min="60" required>
                                    <small class="form-text text-muted">
                                        {{ __('tenant::settings.frequency_help') }}
                                    </small>
                                    @error('frequency')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="status"
                                            name="status" value="1" {{ old('status', $cronTask->status) == 1 ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="status">
                                            {{ __('tenant::settings.enabled') }}
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">
                                        {{ __('tenant::settings.workflow_status_help') }}
                                    </small>
                                </div>
                            </div>

                            {{-- Submit Buttons --}}
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-2"></i>{{ __('tenant::settings.save_changes') }}
                                </button>
                                <a href="{{ route('tenant.settings.crm.automation.scheduler.index') }}"
                                    class="btn btn-outline-secondary">
                                    {{ __('tenant::settings.cancel') }}
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Information Sidebar --}}
            <div class="col-lg-4">
                <div class="card bg-light">
                    <div class="card-body">
                        <h5 class="card-title fw-bold mb-3">
                            <i class="bi bi-info-circle me-2"></i>{{ __('tenant::settings.scheduler_statistics') }}
                        </h5>

                        <div class="mb-3">
                            <label class="text-muted small">{{ __('tenant::settings.status') }}</label>
                            <div>
                                @if($cronTask->isRunning())
                                    <span class="badge bg-info">{{ __('tenant::settings.running') }}</span>
                                @elseif($cronTask->isEnabled())
                                    <span class="badge bg-success">{{ __('tenant::settings.enabled') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ __('tenant::settings.disabled') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="text-muted small">{{ __('tenant::settings.frequency') }}</label>
                            <div><strong>{{ $cronTask->frequency_label }}</strong></div>
                        </div>

                        <div class="mb-3">
                            <label class="text-muted small">{{ __('tenant::settings.last_run') }}</label>
                            <div>
                                @if($cronTask->last_run)
                                    {{ $cronTask->last_run }}
                                @else
                                    <span class="text-muted">{{ __('tenant::settings.never') }}</span>
                                @endif
                            </div>
                        </div>

                        @if($cronTask->last_end_time)
                            <div class="mb-3">
                                <label class="text-muted small">{{ __('tenant::settings.last_end') }}</label>
                                <div>{{ $cronTask->last_end_time }}</div>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="text-muted small">{{ __('tenant::settings.sequence') }}</label>
                            <div><strong>{{ $cronTask->sequence }}</strong></div>
                        </div>

                        <hr>

                        <div class="alert alert-info">
                            <i class="bi bi-lightbulb me-2"></i>
                            <small>{{ __('tenant::settings.frequency_help') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection