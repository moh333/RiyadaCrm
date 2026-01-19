@extends('tenant::layout')

@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-0">
                    <i class="bi bi-layout-text-sidebar-reverse me-2"></i>Menu Management
                </h3>
                <p class="text-muted mb-0">Configure which modules appear in the navigation menu</p>
            </div>
            <a href="{{ route('tenant.settings.modules.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Modules
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form action="{{ route('tenant.settings.modules.menu.update') }}" method="POST">
            @csrf
            
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h5 class="mb-0 fw-bold">Menu Items</h5>
                    <small class="text-muted">Drag to reorder, toggle visibility</small>
                </div>
                <div class="card-body p-0">
                    <div id="sortable-menu" class="list-group list-group-flush">
                        @foreach($modules->sortBy(fn($m) => \DB::connection('tenant')->table('vtiger_tab')->where('tabid', $m->getId())->value('tabsequence') ?? 999) as $index => $module)
                            <div class="list-group-item border-0 py-3 px-4 sortable-item" data-id="{{ $module->getId() }}">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <i class="bi bi-grip-vertical text-muted handle" style="cursor: move;"></i>
                                    </div>
                                    <div class="col">
                                        <div class="d-flex align-items-center">
                                            <div class="form-check form-switch me-3">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       name="modules[{{ $index }}][visible]" 
                                                       value="1"
                                                       id="module_{{ $module->getId() }}"
                                                       {{ $module->isActive() ? 'checked' : '' }}>
                                                <input type="hidden" name="modules[{{ $index }}][tabid]" value="{{ $module->getId() }}">
                                                <input type="hidden" name="modules[{{ $index }}][sequence]" value="{{ $index }}" class="sequence-input">
                                            </div>
                                            <label class="form-check-label fw-bold mb-0" for="module_{{ $module->getId() }}">
                                                {{ $module->getName() }}
                                            </label>
                                            <small class="text-muted ms-2">{{ $module->getLabel() }}</small>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        @if($module->isCustom())
                                            <span class="badge bg-info rounded-pill">Custom</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('tenant.settings.modules.index') }}" class="btn btn-light btn-lg rounded-3">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary btn-lg rounded-3">
                    <i class="bi bi-save me-2"></i>Save Menu Configuration
                </button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const el = document.getElementById('sortable-menu');
            
            const sortable = Sortable.create(el, {
                handle: '.handle',
                animation: 150,
                onEnd: function() {
                    // Update sequence inputs
                    const items = el.querySelectorAll('.sortable-item');
                    items.forEach((item, index) => {
                        const sequenceInput = item.querySelector('.sequence-input');
                        if (sequenceInput) {
                            sequenceInput.value = index;
                        }
                    });
                }
            });
        });
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
