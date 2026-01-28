@extends('tenant::layout')

@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-0">
                    <i
                        class="bi bi-layout-text-sidebar-reverse me-2"></i>{{ __('tenant::tenant.menu_management') ?? 'Menu Management' }}
                </h3>
                <p class="text-muted mb-0">Group entity modules into applications and organize their order</p>
            </div>
            <a href="{{ route('tenant.settings.modules.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>{{ __('tenant::tenant.back') }}
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form id="menu-form" action="{{ route('tenant.settings.modules.menu.update') }}" method="POST">
            @csrf

            <!-- Horizontal Scrolling Container -->
            <div class="d-flex flex-nowrap overflow-auto pb-4 gap-4" id="app-container" style="min-height: 60vh;">
                @foreach($groupedModules as $appName => $modules)
                    <div class="app-group flex-shrink-0" style="width: 350px;" data-app="{{ $appName }}">
                        <div class="card border-0 shadow-sm rounded-4 h-100 bg-light">
                            <div class="card-header bg-white border-bottom py-3 px-3 d-flex justify-content-between align-items-center rounded-top-4 sticky-top transition-all" style="top: 0; z-index: 10;">
                                <h6 class="mb-0 fw-bold text-uppercase text-primary small d-flex align-items-center">
                                    <i class="bi bi-grid-3x3-gap-fill me-2"></i>{{ $appName }}
                                </h6>
                                <span class="badge bg-primary-subtle text-primary rounded-pill small">{{ count($modules) }}</span>
                                <input type="hidden" name="apps[{{ $loop->index }}][name]" value="{{ $appName }}">
                            </div>
                            <div class="card-body p-2 scroll-y-auto custom-scrollbar" style="max-height: 70vh; overflow-y: auto;">
                                <div class="list-group list-group-flush gap-2 sortable-modules min-h-100" 
                                     data-app-index="{{ $loop->index }}" 
                                     style="min-height: 150px;">
                                    @foreach($modules->sortBy(fn($m) => $m->getId()) as $modIndex => $module)
                                        <div class="list-group-item border-0 p-3 module-item rounded-3 shadow-sm bg-white" data-id="{{ $module->getId() }}">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="bi bi-grip-vertical text-muted handle fs-5" style="cursor: grab;"></i>
                                                <div class="flex-grow-1">
                                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                                        <span class="fw-semibold text-dark">{{ $module->getName() }}</span>
                                                        <div class="form-check form-switch m-0 min-h-unset">
                                                            <input class="form-check-input visibility-toggle shadow-none" 
                                                                   type="checkbox" 
                                                                   role="switch"
                                                                   value="1" 
                                                                   {{ $module->getPresence() == 0 ? 'checked' : '' }}
                                                                   title="{{ __('tenant::tenant.toggle_visibility') }}">
                                                        </div>
                                                    </div>
                                                    <div class="text-muted small text-truncate" style="max-width: 200px;">
                                                        {{ $module->getLabel() }}
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Hidden inputs -->
                                            <div class="hidden-inputs">
                                                <input type="hidden" class="input-tabid" value="{{ $module->getId() }}">
                                                <input type="hidden" class="input-sequence" value="{{ $loop->index }}">
                                                <input type="hidden" class="input-visible" value="{{ $module->getPresence() == 0 ? 1 : 0 }}">
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="sticky-bottom bg-white p-3 border-top mt-4 shadow-lg rounded-top-4">
                <div class="container-fluid d-flex justify-content-end gap-2">
                    <a href="{{ route('tenant.settings.modules.index') }}" class="btn btn-light btn-lg rounded-3 px-4">
                        {{ __('tenant::tenant.cancel') }}
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg rounded-3 px-5 shadow-sm">
                        <i class="bi bi-save me-2"></i>{{ __('tenant::tenant.save_settings') }}
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const moduleLists = document.querySelectorAll('.sortable-modules');
            const form = document.getElementById('menu-form');

            moduleLists.forEach(el => {
                Sortable.create(el, {
                    group: 'modules',
                    handle: '.handle',
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    onEnd: function () {
                        updateFormInputs();
                    }
                });
            });

            // Handle visibility toggles
            document.addEventListener('change', function (e) {
                if (e.target.classList.contains('visibility-toggle')) {
                    const moduleItem = e.target.closest('.module-item');
                    const visibleInput = moduleItem.querySelector('.input-visible');
                    visibleInput.value = e.target.checked ? 1 : 0;
                }
            });

            function updateFormInputs() {
                const appGroups = document.querySelectorAll('.app-group');

                appGroups.forEach((appGroup, appIndex) => {
                    const modules = appGroup.querySelectorAll('.module-item');
                    const appName = appGroup.dataset.app;

                    modules.forEach((module, modIndex) => {
                        const tabId = module.querySelector('.input-tabid').value;
                        const visible = module.querySelector('.input-visible').value;

                        // Clear old hidden inputs and create new ones with correct names for Laravel binding
                        const container = module.querySelector('.hidden-inputs');
                        container.innerHTML = `
                                    <input type="hidden" name="apps[${appIndex}][modules][${modIndex}][tabid]" value="${tabId}">
                                    <input type="hidden" name="apps[${appIndex}][modules][${modIndex}][sequence]" value="${modIndex}">
                                    <input type="hidden" name="apps[${appIndex}][modules][${modIndex}][visible]" value="${visible}">
                                `;
                    });
                });
            }

            // Initial input update
            updateFormInputs();

            form.addEventListener('submit', function () {
                updateFormInputs();
            });
        });
    </script>

    <style>
        .module-item {
            transition: all 0.2s;
            cursor: default;
        }

        .module-item:hover {
            background-color: #f8fafc;
        }

        .sortable-ghost {
            opacity: 0.4;
            background-color: #eef2ff !important;
            border: 2px dashed #6366f1 !important;
        }

        .handle {
            padding: 5px;
            border-radius: 4px;
        }

        .handle:hover {
            background-color: #e2e8f0;
        }

        /* Custom Scrollbar for columns */
        .custom-scrollbar::-webkit-scrollbar {
            width: 5px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(0,0,0,0.05);
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        .custom-scrollbar:hover::-webkit-scrollbar-thumb {
            background: rgba(0,0,0,0.2);
        }

        .sticky-bottom {
            z-index: 1020;
            margin-left: -2rem;
            margin-right: -2rem;
            margin-bottom: -2rem;
        }
    </style>
@endsection