@extends('tenant::layout')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="fw-bold mb-1">{{ __('tenant::settings.add_scheduler') ?? 'Add Scheduler Task' }}</h2>
                        <p class="text-muted">
                            {{ __('tenant::settings.add_scheduler_desc') ?? 'Create a new scheduled cron task' }}</p>
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
                        <form method="POST" action="{{ route('tenant.settings.crm.automation.scheduler.store') }}">
                            @csrf

                            {{-- Task Information --}}
                            <div class="mb-4">
                                <h5 class="fw-semibold mb-3">{{ __('tenant::settings.basic_information') }}</h5>

                                <div class="mb-3">
                                    <label for="name" class="form-label fw-semibold">
                                        {{ __('tenant::settings.task_name') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                        name="name" value="{{ old('name') }}" required placeholder="e.g. Daily Reports">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="description"
                                        class="form-label fw-semibold">{{ __('tenant::settings.description') }}</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror"
                                        id="description" name="description" rows="2"
                                        placeholder="Describe what this task does">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="module"
                                            class="form-label fw-semibold">{{ __('tenant::settings.module') }}</label>
                                        <select class="form-select @error('module') is-invalid @enderror" id="module"
                                            name="module">
                                            <option value="">
                                                {{ __('tenant::settings.select_module') ?? 'Select Module (Optional)' }}
                                            </option>
                                            @foreach($modules as $key => $label)
                                                <option value="{{ $key }}" {{ old('module') == $key ? 'selected' : '' }}>
                                                    {{ $label }}</option>
                                            @endforeach
                                        </select>
                                        @error('module')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="handler_file" class="form-label fw-semibold">
                                            {{ __('tenant::settings.handler_file') }}
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control @error('handler_file') is-invalid @enderror"
                                            id="handler_file" name="handler_file" value="{{ old('handler_file') }}" required
                                            placeholder="e.g. cron/modules/Reports/Reports.service">
                                        @error('handler_file')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Settings --}}
                            <div class="mb-4">
                                <h5 class="fw-semibold mb-3">{{ __('tenant::settings.settings') }}</h5>

                                <div class="mb-3">
                                    <label for="frequency" class="form-label fw-semibold">
                                        {{ __('tenant::settings.frequency_seconds') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="number" class="form-control @error('frequency') is-invalid @enderror"
                                            id="frequency" name="frequency" value="{{ old('frequency', 900) }}" min="60"
                                            required>
                                        <span
                                            class="input-group-text">{{ __('tenant::settings.seconds') ?? 'seconds' }}</span>
                                    </div>
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
                                            name="status" value="1" {{ old('status', '1') == '1' ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="status">
                                            {{ __('tenant::settings.active') ?? 'Active' }}
                                        </label>
                                    </div>
                                </div>
                            </div>

                            {{-- Submit Buttons --}}
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i
                                        class="bi bi-plus-circle me-2"></i>{{ __('tenant::settings.create_task') ?? 'Create Task' }}
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

            {{-- Help Sidebar --}}
            <div class="col-lg-4">
                <div class="card bg-light border-0 shadow-none">
                    <div class="card-body">
                        <h5 class="card-title fw-bold mb-3 d-flex align-items-center">
                            <i class="bi bi-info-circle me-2 text-primary"></i>
                            {{ __('tenant::settings.scheduler_help') ?? 'About Scheduler' }}
                        </h5>

                        <div class="mb-4">
                            <p class="small text-muted">
                                {{ __('tenant::settings.scheduler_help_text') ?? 'Scheduled tasks allow you to automate background processes in your CRM.' }}
                            </p>
                        </div>

                        <div class="mb-3">
                            <h6 class="fw-bold small text-uppercase text-muted">{{ __('tenant::settings.handler_file') }}
                            </h6>
                            <p class="small mb-0">
                                {{ __('tenant::settings.handler_file_help') ?? 'The handler file path relative to the root or module directory that executes the task logic.' }}
                            </p>
                        </div>

                        <div class="alert alert-warning border-0 small mt-4">
                            <i class="bi bi-lightbulb me-2"></i>
                            {{ __('tenant::settings.frequency_tip') ?? 'Higher frequencies (smaller seconds) increase server load. Use at least 900s (15m) for non-critical tasks.' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection