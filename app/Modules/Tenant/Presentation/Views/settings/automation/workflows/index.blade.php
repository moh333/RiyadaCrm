@extends('tenant::layout')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="fw-bold mb-1">{{ __('tenant::settings.workflows') ?? 'Workflows' }}</h2>
                        <p class="text-muted">{{ __('tenant::settings.workflows_description') ?? 'Automate business processes with workflows' }}</p>
                    </div>
                    <a href="{{ route('tenant.settings.crm.automation.workflows.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>{{ __('tenant::settings.create_workflow') ?? 'Create Workflow' }}
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

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <label for="moduleFilter" class="form-label fw-semibold">
                            <i class="bi bi-funnel me-2"></i>{{ __('tenant::settings.filter_by_module') ?? 'Filter by Module' }}
                        </label>
                        <select id="moduleFilter" class="form-select">
                            <option value="all" {{ $moduleFilter == 'all' ? 'selected' : '' }}>
                                {{ __('tenant::settings.all_modules') ?? 'All Modules' }}
                            </option>
                            @foreach($modules as $module)
                                <option value="{{ $module }}" {{ $moduleFilter == $module ? 'selected' : '' }}>
                                    {{ $module }} ({{ $workflowCounts[$module] ?? 0 }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        @if($workflows->isEmpty())
                            <div class="text-center py-5">
                                <i class="bi bi-diagram-2 text-muted" style="font-size: 4rem;"></i>
                                <h5 class="mt-3 text-muted">{{ __('tenant::settings.no_workflows') ?? 'No workflows configured yet' }}</h5>
                                <p class="text-muted">{{ __('tenant::settings.create_first_workflow') ?? 'Create your first workflow to automate your business processes' }}</p>
                                <a href="{{ route('tenant.settings.crm.automation.workflows.create') }}" class="btn btn-primary mt-2">
                                    <i class="bi bi-plus-circle me-2"></i>{{ __('tenant::settings.create_workflow') ?? 'Create Workflow' }}
                                </a>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>{{ __('tenant::settings.workflow_name') ?? 'Workflow Name' }}</th>
                                            <th>{{ __('tenant::settings.module') ?? 'Module' }}</th>
                                            <th>{{ __('tenant::settings.description') ?? 'Description' }}</th>
                                            <th>{{ __('tenant::settings.execution_condition') ?? 'Execution' }}</th>
                                            <th>{{ __('tenant::settings.status') ?? 'Status' }}</th>
                                            <th>{{ __('tenant::settings.actions') ?? 'Actions' }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($workflows as $workflow)
                                            <tr>
                                                <td>
                                                    <div class="fw-semibold">{{ $workflow->workflowname }}</div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info">{{ $workflow->module_name }}</span>
                                                </td>
                                                <td>
                                                    <small class="text-muted">{{ $workflow->summary ?: '-' }}</small>
                                                </td>
                                                <td>
                                                    @php
                                                        $executionLabels = [
                                                            1 => __('tenant::settings.on_first_save') ?? 'On First Save',
                                                            2 => __('tenant::settings.once') ?? 'Once',
                                                            3 => __('tenant::settings.on_every_save') ?? 'On Every Save',
                                                            4 => __('tenant::settings.on_modify') ?? 'On Modify',
                                                            6 => __('tenant::settings.on_schedule') ?? 'Scheduled',
                                                            7 => __('tenant::settings.manual') ?? 'Manual',
                                                        ];
                                                    @endphp
                                                    <small>{{ $executionLabels[$workflow->execution_condition] ?? 'Unknown' }}</small>
                                                </td>
                                                <td>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input workflow-status-toggle" 
                                                               type="checkbox" 
                                                               role="switch"
                                                               data-workflow-id="{{ $workflow->workflow_id }}"
                                                               {{ $workflow->status ? 'checked' : '' }}>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a href="{{ route('tenant.settings.crm.automation.workflows.edit', $workflow->workflow_id) }}" 
                                                           class="btn btn-outline-primary"
                                                           title="{{ __('tenant::settings.edit') ?? 'Edit' }}">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        <button type="button" 
                                                                class="btn btn-outline-danger delete-workflow"
                                                                data-workflow-id="{{ $workflow->workflow_id }}"
                                                                data-workflow-name="{{ $workflow->workflowname }}"
                                                                title="{{ __('tenant::settings.delete') ?? 'Delete' }}">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('tenant::settings.confirm_delete') ?? 'Confirm Delete' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>{{ __('tenant::settings.confirm_delete_workflow_message') ?? 'Are you sure you want to delete this workflow?' }}</p>
                    <p class="fw-semibold" id="workflowNameToDelete"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        {{ __('tenant::settings.cancel') ?? 'Cancel' }}
                    </button>
                    <form id="deleteWorkflowForm" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            {{ __('tenant::settings.delete') ?? 'Delete' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Module filter
        const moduleFilter = document.getElementById('moduleFilter');
        if (moduleFilter) {
            moduleFilter.addEventListener('change', function() {
                const url = new URL(window.location.href);
                url.searchParams.set('module', this.value);
                window.location.href = url.toString();
            });
        }

        // Workflow status toggle
        document.querySelectorAll('.workflow-status-toggle').forEach(toggle => {
            toggle.addEventListener('change', function() {
                const workflowId = this.dataset.workflowId;
                const status = this.checked ? 1 : 0;

                fetch(`{{ route('tenant.settings.crm.automation.workflows.index') }}/${workflowId}/toggle-status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ status: status })
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        this.checked = !this.checked;
                        alert('{{ __("tenant::settings.error_updating_status") ?? "Error updating status" }}');
                    }
                })
                .catch(error => {
                    this.checked = !this.checked;
                    console.error('Error:', error);
                    alert('{{ __("tenant::settings.error_updating_status") ?? "Error updating status" }}');
                });
            });
        });

        // Delete workflow
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        document.querySelectorAll('.delete-workflow').forEach(button => {
            button.addEventListener('click', function() {
                const workflowId = this.dataset.workflowId;
                const workflowName = this.dataset.workflowName;
                
                document.getElementById('workflowNameToDelete').textContent = workflowName;
                document.getElementById('deleteWorkflowForm').action = 
                    `{{ route('tenant.settings.crm.automation.workflows.index') }}/${workflowId}`;
                
                deleteModal.show();
            });
        });
    });
</script>
@endpush
