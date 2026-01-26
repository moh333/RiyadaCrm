@extends('tenant::layout')

@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex justify-content-between align-items-center mb-4 icon-card-header">
            <div>
                <h3 class="fw-bold mb-0">
                    <i class="bi bi-grid-3x3-gap-fill text-primary me-2"></i>{{ __('tenant::tenant.module_management') }}
                </h3>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-0">
                <ul class="nav nav-pills nav-fill p-2 bg-light rounded-4">
                    <li class="nav-item">
                        <a class="nav-link active fw-bold" href="{{ route('tenant.settings.modules.list') }}">
                            <i class="bi bi-list-task me-2"></i>{{ __('tenant::tenant.modules') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-muted fw-bold" href="{{ route('tenant.settings.modules.layouts') }}">
                            <i class="bi bi-layout-sidebar-inset me-2"></i>{{ __('tenant::tenant.module_layouts_fields') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-muted fw-bold" href="{{ route('tenant.settings.modules.numbering.selection') }}">
                            <i class="bi bi-123 me-2"></i>{{ __('tenant::tenant.module_numbering') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-muted fw-bold" href="{{ route('tenant.settings.modules.relations.selection') }}">
                            <i class="bi bi-diagram-3 me-2"></i>{{ __('tenant::tenant.module_relations') }}
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">{{ __('tenant::tenant.modules') }}</th>
                            <th>{{ __('tenant::tenant.status') }}</th>
                            <th class="text-end pe-4">{{ __('tenant::tenant.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($modules as $module)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-box bg-soft-primary rounded-3 p-2 me-3">
                                            <i class="bi bi-box"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold">{{ $module->getName() }}</h6>
                                            <small class="text-muted">{{ $module->getLabel() }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($module->isActive())
                                        <span class="badge bg-soft-success rounded-pill px-3">{{ __('tenant::tenant.active') }}</span>
                                    @else
                                        <span class="badge bg-soft-danger rounded-pill px-3">{{ __('tenant::tenant.inactive') }}</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <form action="{{ route('tenant.settings.modules.toggle', $module->getName()) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm {{ $module->isActive() ? 'btn-soft-danger' : 'btn-soft-success' }} rounded-3 px-3">
                                            @if($module->isActive())
                                                <i class="bi bi-toggle-on me-1"></i>Deactivate
                                            @else
                                                <i class="bi bi-toggle-off me-1"></i>Activate
                                            @endif
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <style>
        .icon-box {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .btn-soft-success {
            background-color: #f0fdf4;
            color: #22c55e;
            border: none;
        }
        .btn-soft-success:hover {
            background-color: #dcfce7;
            color: #16a34a;
        }
        .btn-soft-danger {
            background-color: #fef2f2;
            color: #ef4444;
            border: none;
        }
        .btn-soft-danger:hover {
            background-color: #fee2e2;
            color: #dc2626;
        }
        .bg-soft-primary {
            background-color: #eef2ff;
            color: #6366f1;
        }
    </style>
@endsection
