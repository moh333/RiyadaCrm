@extends('tenant::layout')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="fw-bold mb-1">{{ __('tenant::settings.create_workflow') ?? 'Create Workflow' }}</h2>
                        <p class="text-muted">{{ __('tenant::settings.create_workflow_description') ?? 'Set up automation rules for your business processes' }}</p>
                    </div>
                    <a href="{{ route('tenant.settings.crm.automation.workflows.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>{{ __('tenant::settings.back') ?? 'Back' }}
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ route('tenant.settings.crm.automation.workflows.store') }}">
                            @csrf

                            {{-- Workflow Name --}}
                            <div class="mb-4">
                                <label for="workflowname" class="form-label fw-semibold">
                                    {{ __('tenant::settings.workflow_name') ?? 'Workflow Name' }}
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('workflowname') is-invalid @enderror" 
                                       id="workflowname" 
                                       name="workflowname" 
                                       value="{{ old('workflowname') }}"
                                       placeholder="{{ __('tenant::settings.enter_workflow_name') ?? 'Enter workflow name' }}"
                                       required>
                                @error('workflowname')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Module Selection --}}
                            <div class="mb-4">
                                <label for="module_name" class="form-label fw-semibold">
                                    {{ __('tenant::settings.module') ?? 'Module' }}
                                    <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('module_name') is-invalid @enderror" 
                                        id="module_name" 
                                        name="module_name" 
                                        required>
                                    <option value="">{{ __('tenant::settings.select_module') ?? 'Select Module' }}</option>
                                    @foreach($modules as $key => $label)
                                        <option value="{{ $key }}" {{ old('module_name', $moduleName) == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('module_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Description --}}
                            <div class="mb-4">
                                <label for="summary" class="form-label fw-semibold">
                                    {{ __('tenant::settings.description') ?? 'Description' }}
                                </label>
                                <textarea class="form-control @error('summary') is-invalid @enderror" 
                                          id="summary" 
                                          name="summary" 
                                          rows="3"
                                          placeholder="{{ __('tenant::settings.enter_workflow_description') ?? 'Enter workflow description' }}">{{ old('summary') }}</textarea>
                                @error('summary')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Execution Condition --}}
                            <div class="mb-4">
                                <label for="execution_condition" class="form-label fw-semibold">
                                    {{ __('tenant::settings.execution_condition') ?? 'Execution Condition' }}
                                    <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('execution_condition') is-invalid @enderror" 
                                        id="execution_condition" 
                                        name="execution_condition" 
                                        required>
                                    <option value="">{{ __('tenant::settings.select_execution_condition') ?? 'Select Execution Condition' }}</option>
                                    <option value="1" {{ old('execution_condition') == '1' ? 'selected' : '' }}>
                                        {{ __('tenant::settings.on_first_save') ?? 'On First Save' }} - {{ __('tenant::settings.on_first_save_desc') ?? 'Execute only when record is created' }}
                                    </option>
                                    <option value="2" {{ old('execution_condition') == '2' ? 'selected' : '' }}>
                                        {{ __('tenant::settings.once') ?? 'Once' }} - {{ __('tenant::settings.once_desc') ?? 'Execute once per record' }}
                                    </option>
                                    <option value="3" {{ old('execution_condition') == '3' ? 'selected' : '' }}>
                                        {{ __('tenant::settings.on_every_save') ?? 'On Every Save' }} - {{ __('tenant::settings.on_every_save_desc') ?? 'Execute on create and every update' }}
                                    </option>
                                    <option value="4" {{ old('execution_condition') == '4' ? 'selected' : '' }}>
                                        {{ __('tenant::settings.on_modify') ?? 'On Modify' }} - {{ __('tenant::settings.on_modify_desc') ?? 'Execute only when record is updated' }}
                                    </option>
                                    <option value="6" {{ old('execution_condition') == '6' ? 'selected' : '' }}>
                                        {{ __('tenant::settings.on_schedule') ?? 'Scheduled' }} - {{ __('tenant::settings.on_schedule_desc') ?? 'Execute at scheduled times' }}
                                    </option>
                                    <option value="7" {{ old('execution_condition') == '7' ? 'selected' : '' }}>
                                        {{ __('tenant::settings.manual') ?? 'Manual' }} - {{ __('tenant::settings.manual_desc') ?? 'Execute manually by user' }}
                                    </option>
                                </select>
                                @error('execution_condition')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    {{ __('tenant::settings.execution_condition_help') ?? 'Choose when this workflow should be triggered' }}
                                </small>
                            </div>

                            {{-- Status --}}
                            <div class="mb-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           role="switch" 
                                           id="status" 
                                           name="status" 
                                           value="1"
                                           {{ old('status', '1') == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="status">
                                        {{ __('tenant::settings.active') ?? 'Active' }}
                                    </label>
                                </div>
                                <small class="form-text text-muted">
                                    {{ __('tenant::settings.workflow_status_help') ?? 'Enable or disable this workflow' }}
                                </small>
                            </div>

                            {{-- Submit Buttons --}}
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-2"></i>{{ __('tenant::settings.create_and_configure') ?? 'Create & Configure' }}
                                </button>
                                <a href="{{ route('tenant.settings.crm.automation.workflows.index') }}" class="btn btn-outline-secondary">
                                    {{ __('tenant::settings.cancel') ?? 'Cancel' }}
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Help Sidebar --}}
            <div class="col-lg-4">
                <div class="card bg-light">
                    <div class="card-body">
                        <h5 class="card-title fw-bold mb-3">
                            <i class="bi bi-info-circle me-2"></i>{{ __('tenant::settings.workflow_help') ?? 'Workflow Help' }}
                        </h5>
                        
                        <h6 class="fw-semibold mt-3">{{ __('tenant::settings.execution_conditions') ?? 'Execution Conditions' }}</h6>
                        <ul class="small">
                            <li><strong>{{ __('tenant::settings.on_first_save') ?? 'On First Save' }}:</strong> {{ __('tenant::settings.on_first_save_help') ?? 'Triggers only when a new record is created' }}</li>
                            <li><strong>{{ __('tenant::settings.once') ?? 'Once' }}:</strong> {{ __('tenant::settings.once_help') ?? 'Triggers only once when conditions are first met' }}</li>
                            <li><strong>{{ __('tenant::settings.on_every_save') ?? 'On Every Save' }}:</strong> {{ __('tenant::settings.on_every_save_help') ?? 'Triggers on both creation and updates' }}</li>
                            <li><strong>{{ __('tenant::settings.on_modify') ?? 'On Modify' }}:</strong> {{ __('tenant::settings.on_modify_help') ?? 'Triggers only when existing records are updated' }}</li>
                            <li><strong>{{ __('tenant::settings.on_schedule') ?? 'Scheduled' }}:</strong> {{ __('tenant::settings.on_schedule_help') ?? 'Triggers at specific times (requires cron setup)' }}</li>
                            <li><strong>{{ __('tenant::settings.manual') ?? 'Manual' }}:</strong> {{ __('tenant::settings.manual_help') ?? 'Triggered manually by users' }}</li>
                        </ul>

                        <div class="alert alert-info mt-3">
                            <i class="bi bi-lightbulb me-2"></i>
                            <small>{{ __('tenant::settings.workflow_tip') ?? 'After creating the workflow, you can add conditions and tasks to define the automation logic.' }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
