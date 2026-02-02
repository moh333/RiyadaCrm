@extends('tenant::layout')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="fw-bold mb-1">{{ __('tenant::settings.picklist_dependency') ?? 'Picklist Dependency' }}</h2>
                    <p class="text-muted">{{ __('tenant::settings.picklist_dependency_description') ?? 'Create conditional relationships between picklist fields' }}</p>
                </div>
                <a href="{{ route('tenant.settings.crm.picklist-dependency.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>{{ __('tenant::settings.add_dependency') ?? 'Add Dependency' }}
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if($dependencies->isEmpty())
                        <div class="text-center py-5">
                            <i class="bi bi-diagram-3 text-muted" style="font-size: 4rem;"></i>
                            <h5 class="mt-3 text-muted">{{ __('tenant::settings.no_dependencies') ?? 'No dependencies configured yet' }}</h5>
                            <p class="text-muted">{{ __('tenant::settings.create_first_dependency') ?? 'Create your first dependency to get started' }}</p>
                            <a href="{{ route('tenant.settings.crm.picklist-dependency.create') }}" class="btn btn-primary mt-2">
                                <i class="bi bi-plus-circle me-2"></i>{{ __('tenant::settings.add_dependency') ?? 'Add Dependency' }}
                            </a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('tenant::settings.module') ?? 'Module' }}</th>
                                        <th>{{ __('tenant::settings.source_field') ?? 'Source Field' }}</th>
                                        <th>{{ __('tenant::settings.target_field') ?? 'Target Field' }}</th>
                                        <th>{{ __('tenant::settings.actions') ?? 'Actions' }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dependencies as $dependency)
                                        <tr>
                                            <td>{{ vtranslate($dependency->module_label, 'Vtiger') }}</td>
                                            <td><span class="badge bg-primary">{{ $dependency->sourcefield }}</span></td>
                                            <td><span class="badge bg-success">{{ $dependency->targetfield }}</span></td>
                                            <td>
                                                <a href="{{ route('tenant.settings.crm.picklist-dependency.edit', ['module' => $dependency->module_name, 'source_field' => $dependency->sourcefield, 'target_field' => $dependency->targetfield]) }}" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-pencil"></i> {{ __('tenant::settings.edit') ?? 'Edit' }}
                                                </a>
                                                <button class="btn btn-sm btn-outline-danger delete-dependency" 
                                                        data-module="{{ $dependency->module_name }}"
                                                        data-source="{{ $dependency->sourcefield }}"
                                                        data-target="{{ $dependency->targetfield }}">
                                                    <i class="bi bi-trash"></i> {{ __('tenant::settings.delete') ?? 'Delete' }}
                                                </button>
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
@endsection

@push('scripts')
<script>
    $(document).on('click', '.delete-dependency', function() {
        if (!confirm('{{ __("tenant::settings.confirm_delete_dependency") ?? "Are you sure you want to delete this dependency?" }}')) return;

        const module = $(this).data('module');
        const sourceField = $(this).data('source');
        const targetField = $(this).data('target');

        $.ajax({
            url: '{{ route("tenant.settings.crm.picklist-dependency.delete") }}',
            method: 'POST',
            data: {
                module: module,
                source_field: sourceField,
                target_field: targetField,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                location.reload();
            },
            error: function() {
                alert('{{ __("tenant::settings.error_deleting_dependency") ?? "Error deleting dependency" }}');
            }
        });
    });
</script>
@endpush
