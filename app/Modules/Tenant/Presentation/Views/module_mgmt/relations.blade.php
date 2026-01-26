@extends('tenant::layout')

@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-0">
                    <i class="bi bi-diagram-3 text-primary me-2"></i>{{ $moduleDefinition->getName() }} -
                    {{ __('tenant::tenant.module_relations') }}
                </h3>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-primary rounded-3" data-bs-toggle="modal"
                    data-bs-target="#addRelationModal">
                    <i class="bi bi-plus-circle me-2"></i>{{ __('tenant::tenant.add_relation') }}
                </button>
                <a href="{{ route('tenant.settings.modules.relations.selection') }}"
                    class="btn btn-outline-secondary rounded-3">
                    <i class="bi bi-arrow-left me-2"></i>{{ __('tenant::tenant.cancel') }}
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h5 class="mb-0 fw-bold">{{ __('tenant::tenant.module_relations') }}</h5>
                <small class="text-muted">Drag to reorder relations</small>
            </div>
            <div class="card-body p-0">
                @if($relations->isEmpty())
                    <div class="text-center py-5">
                        <i class="bi bi-diagram-3 text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-3">No relations configured yet.</p>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRelationModal">
                            <i class="bi bi-plus-circle me-2"></i>Add First Relation
                        </button>
                    </div>
                @else
                    <div id="sortable-relations" class="list-group list-group-flush">
                        @foreach($relations as $relation)
                            <div class="list-group-item border-0 py-3 px-4 sortable-item" data-id="{{ $relation->relation_id }}">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <i class="bi bi-grip-vertical text-muted handle" style="cursor: move;"></i>
                                    </div>
                                    <div class="col">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <h6 class="mb-1 fw-bold">{{ $relation->label }}</h6>
                                                <div class="d-flex gap-2 align-items-center">
                                                    <span class="badge bg-info">{{ $relation->target_module_name }}</span>
                                                    <span class="badge bg-secondary">{{ $relation->relationtype ?? '1:N' }}</span>
                                                    @if($relation->actions)
                                                        @foreach(explode(',', $relation->actions) as $action)
                                                            <span class="badge bg-success">{{ $action }}</span>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <button type="button" class="btn btn-sm btn-outline-primary rounded-3"
                                                    onclick="editRelation({{ json_encode($relation) }})">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <form
                                                    action="{{ route('tenant.settings.modules.relations.destroy', [$moduleDefinition->getName(), $relation->relation_id]) }}"
                                                    method="POST"
                                                    onsubmit="return confirm('Are you sure you want to delete this relation?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-3">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Add Relation Modal -->
    <div class="modal fade" id="addRelationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content rounded-4">
                <form action="{{ route('tenant.settings.modules.relations.store', $moduleDefinition->getName()) }}"
                    method="POST">
                    @csrf
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold">{{ __('tenant::tenant.add_relation') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('tenant::tenant.target_module') }} <span
                                    class="text-danger">*</span></label>
                            <select name="target_module" class="form-select rounded-3" required>
                                <option value="">Select module...</option>
                                @foreach($availableModules as $module)
                                    <option value="{{ $module->getName() }}">{{ $module->getLabel() }}
                                        ({{ $module->getName() }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('tenant::tenant.relation_label') }} <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="label" class="form-control rounded-3" placeholder="e.g., Opportunities"
                                required>
                            <small class="text-muted">Display name for this relation</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('tenant::tenant.relation_type') }} <span
                                    class="text-danger">*</span></label>
                            <select name="relation_type" class="form-select rounded-3" required>
                                <option value="1:N">One-to-Many (1:N)</option>
                                <option value="N:N">Many-to-Many (N:N)</option>
                            </select>
                            <small class="text-muted">Type of relationship</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('tenant::tenant.available_actions') }}</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="actions[]" value="ADD"
                                    id="action_add">
                                <label class="form-check-label" for="action_add">
                                    {{ __('tenant::tenant.action_add') }} - Allow creating new related records
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="actions[]" value="SELECT"
                                    id="action_select">
                                <label class="form-check-label" for="action_select">
                                    {{ __('tenant::tenant.action_select') }} - Allow linking existing records
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light rounded-3"
                            data-bs-dismiss="modal">{{ __('tenant::tenant.cancel') }}</button>
                        <button type="submit" class="btn btn-primary rounded-3">
                            <i class="bi bi-plus-circle me-2"></i>{{ __('tenant::tenant.add_relation') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Relation Modal -->
    <div class="modal fade" id="editRelationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content rounded-4">
                <form id="editRelationForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold">{{ __('tenant::tenant.edit_relation') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('tenant::tenant.relation_label') }} <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="label" id="edit_label" class="form-control rounded-3" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('tenant::tenant.available_actions') }}</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="actions[]" value="ADD"
                                    id="edit_action_add">
                                <label class="form-check-label" for="edit_action_add">
                                    {{ __('tenant::tenant.action_add') }}
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="actions[]" value="SELECT"
                                    id="edit_action_select">
                                <label class="form-check-label" for="edit_action_select">
                                    {{ __('tenant::tenant.action_select') }}
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light rounded-3"
                            data-bs-dismiss="modal">{{ __('tenant::tenant.cancel') }}</button>
                        <button type="submit" class="btn btn-primary rounded-3">
                            <i class="bi bi-save me-2"></i>{{ __('tenant::tenant.save_settings') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const el = document.getElementById('sortable-relations');

            if (el) {
                const sortable = Sortable.create(el, {
                    handle: '.handle',
                    animation: 150,
                    onEnd: function () {
                        // Update sequences
                        const items = el.querySelectorAll('.sortable-item');
                        const relations = [];

                        items.forEach((item, index) => {
                            relations.push({
                                relation_id: parseInt(item.dataset.id),
                                sequence: index + 1
                            });
                        });

                        // Send to server
                        fetch('{{ route("tenant.settings.modules.relations.reorder", $moduleDefinition->getName()) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ relations: relations })
                        });
                    }
                });
            }
        });

        function editRelation(relation) {
            const form = document.getElementById('editRelationForm');
            form.action = '{{ route("tenant.settings.modules.relations.update", [$moduleDefinition->getName(), ":id"]) }}'.replace(':id', relation.relation_id);

            document.getElementById('edit_label').value = relation.label;

            // Set checkboxes
            const actions = relation.actions ? relation.actions.split(',') : [];
            document.getElementById('edit_action_add').checked = actions.includes('ADD');
            document.getElementById('edit_action_select').checked = actions.includes('SELECT');

            const modal = new bootstrap.Modal(document.getElementById('editRelationModal'));
            modal.show();
        }
    </script>

    <style>
        .sortable-item {
            transition: background-color 0.2s;
        }

        .sortable-item:hover {
            background-color: #f8f9fa;
        }

        .sortable-ghost {
            opacity: 0.4;
            background-color: #e9ecef;
        }
    </style>
@endsection