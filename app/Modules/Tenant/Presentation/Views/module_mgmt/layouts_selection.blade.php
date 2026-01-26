@extends('tenant::layout')

@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-0">
                    <i
                        class="bi bi-layout-sidebar-inset text-primary me-2"></i>{{ __('tenant::tenant.module_layouts_fields') }}
                </h3>
            </div>
        </div>

        <div class="row g-4">
            @foreach($modules as $module)
                <div class="col-md-4 col-xl-3">
                    <a href="{{ route('tenant.settings.modules.layout', $module->getName()) }}" class="text-decoration-none">
                        <div class="card h-100 border-0 shadow-sm rounded-4 module-selection-card transition-all">
                            <div class="card-body text-center p-4">
                                <div class="icon-box bg-soft-primary rounded-circle mx-auto mb-3"
                                    style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                                    <i class="bi bi-file-earmark-text"></i>
                                </div>
                                <h6 class="fw-bold text-dark mb-1">{{ $module->getName() }}</h6>
                                <p class="text-muted small mb-0">{{ $module->getLabel() }}</p>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>

    <style>
        .module-selection-card {
            border: 2px solid transparent !important;
        }

        .module-selection-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary-color) !important;
            background-color: var(--primary-light);
        }

        .bg-soft-primary {
            background-color: #eef2ff;
            color: #6366f1;
        }
    </style>
@endsection