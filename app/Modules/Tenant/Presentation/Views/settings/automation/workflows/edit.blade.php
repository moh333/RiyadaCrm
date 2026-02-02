@extends('tenant::layout')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="fw-bold mb-1">{{ __('tenant::settings.edit_workflow') ?? 'Edit Workflow' }}</h2>
                        <p class="text-muted">{{ $workflow->workflowname }}</p>
                    </div>
                    <a href="{{ route('tenant.settings.crm.automation.workflows.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>{{ __('tenant::settings.back') ?? 'Back' }}
                    </a>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-8">
                {{-- Basic Information Card --}}
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0 fw-semibold">
                            <i class="bi bi-info-circle me-2"></i>{{ __('tenant::settings.basic_information') ?? 'Basic Information' }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('tenant.settings.crm.automation.workflows.update', $workflow->workflow_id) }}">
                            @csrf
                            @method('PUT')

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
                                       value="{{ old('workflowname', $workflow->workflowname) }}"
                                       required>
                                @error('workflowname')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Module (Read-only) --}}
                            <div class="mb-4">
                                <label for="module_name" class="form-label fw-semibold">
                                    {{ __('tenant::settings.module') ?? 'Module' }}
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       value="{{ $workflow->module_name }}"
                                       readonly>
                                <small class="form-text text-muted">
                                    {{ __('tenant::settings.module_cannot_be_changed') ?? 'Module cannot be changed after creation' }}
                                </small>
                            </div>

                            {{-- Description --}}
                            <div class="mb-4">
                                <label for="summary" class="form-label fw-semibold">
                                    {{ __('tenant::settings.description') ?? 'Description' }}
                                </label>
                                <textarea class="form-control @error('summary') is-invalid @enderror" 
                                          id="summary" 
                                          name="summary" 
                                          rows="3">{{ old('summary', $workflow->summary) }}</textarea>
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
                                    @foreach($executionConditions as $value => $label)
                                        <option value="{{ $value }}" {{ old('execution_condition', $workflow->execution_condition) == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('execution_condition')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
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
                                           {{ old('status', $workflow->status) == 1 ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="status">
                                        {{ __('tenant::settings.active') ?? 'Active' }}
                                    </label>
                                </div>
                            </div>

                            {{-- Submit Buttons --}}
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-2"></i>{{ __('tenant::settings.save_changes') ?? 'Save Changes' }}
                                </button>
                                <a href="{{ route('tenant.settings.crm.automation.workflows.index') }}" class="btn btn-outline-secondary">
                                    {{ __('tenant::settings.cancel') ?? 'Cancel' }}
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Conditions Card --}}
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0 fw-semibold">
                            <i class="bi bi-filter me-2"></i>{{ __('tenant::settings.conditions') ?? 'Conditions' }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            {{ __('tenant::settings.conditions_coming_soon') ?? 'Advanced condition builder coming soon. You can currently set basic workflow parameters.' }}
                        </div>
                    </div>
                </div>

                {{-- Tasks Card --}}
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-semibold">
                            <i class="bi bi-list-task me-2"></i>{{ __('tenant::settings.tasks') ?? 'Tasks' }}
                        </h5>
                        <button class="btn btn-sm btn-primary" disabled>
                            <i class="bi bi-plus-circle me-2"></i>{{ __('tenant::settings.add_task') ?? 'Add Task' }}
                        </button>
                    </div>
                    <div class="card-body">
                        @if($tasks->isEmpty())
                            <div class="text-center py-4">
                                <i class="bi bi-list-task text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-3">{{ __('tenant::settings.no_tasks_configured') ?? 'No tasks configured yet' }}</p>
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    {{ __('tenant::settings.tasks_coming_soon') ?? 'Task management interface coming soon. Tasks will allow you to send emails, update fields, create records, and more.' }}
                                </div>
                            </div>
                        @else
                            <div class="list-group">
                                @foreach($tasks as $task)
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1">{{ $task->summary }}</h6>
                                                <small class="text-muted">Task ID: {{ $task->task_id }}</small>
                                            </div>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary" disabled>
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="btn btn-outline-danger" disabled>
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Info Sidebar --}}
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-3">{{ __('tenant::settings.workflow_information') ?? 'Workflow Information' }}</h6>
                        <dl class="row mb-0">
                            <dt class="col-sm-5 small">{{ __('tenant::settings.workflow_id') ?? 'Workflow ID' }}:</dt>
                            <dd class="col-sm-7 small">{{ $workflow->workflow_id }}</dd>

                            <dt class="col-sm-5 small">{{ __('tenant::settings.module') ?? 'Module' }}:</dt>
                            <dd class="col-sm-7 small"><span class="badge bg-info">{{ $workflow->module_name }}</span></dd>

                            <dt class="col-sm-5 small">{{ __('tenant::settings.type') ?? 'Type' }}:</dt>
                            <dd class="col-sm-7 small">{{ $workflow->type ?? 'basic' }}</dd>

                            <dt class="col-sm-5 small">{{ __('tenant::settings.status') ?? 'Status' }}:</dt>
                            <dd class="col-sm-7 small">
                                @if($workflow->status)
                                    <span class="badge bg-success">{{ __('tenant::settings.active') ?? 'Active' }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ __('tenant::settings.inactive') ?? 'Inactive' }}</span>
                                @endif
                            </dd>
                        </dl>
                    </div>
                </div>

                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-3">
                            <i class="bi bi-lightbulb me-2"></i>{{ __('tenant::settings.tips') ?? 'Tips' }}
                        </h6>
                        <ul class="small mb-0">
                            <li>{{ __('tenant::settings.workflow_tip_1') ?? 'Use descriptive names for your workflows' }}</li>
                            <li>{{ __('tenant::settings.workflow_tip_2') ?? 'Test workflows with inactive status first' }}</li>
                            <li>{{ __('tenant::settings.workflow_tip_3') ?? 'Add clear descriptions to help team members understand the purpose' }}</li>
                            <li>{{ __('tenant::settings.workflow_tip_4') ?? 'Choose the right execution condition for your use case' }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
