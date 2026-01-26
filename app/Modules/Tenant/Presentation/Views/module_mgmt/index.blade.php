@extends('tenant::layout')

@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-0"><i class="bi bi-grid-3x3-gap me-2"></i>Module Management</h3>
                <p class="text-muted mb-0">Customize modules, layouts, and settings for your CRM</p>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif



        <!-- Modules List -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h5 class="mb-0 fw-bold">All Modules</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted">
                        <tr>
                            <th class="ps-4 py-3">Module</th>
                            <th class="py-3">Status</th>
                            <th class="py-3">Type</th>
                            <th class="py-3 text-center">Fields</th>
                            <th class="py-3 text-center">Blocks</th>
                            <th class="pe-4 py-3 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($modules as $module)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-circle bg-primary bg-opacity-10 text-primary rounded-circle p-2 me-3">
                                            <i class="bi bi-folder"></i>
                                        </div>
                                        <div>
                                            <span class="fw-bold">{{ $module->getName() }}</span>
                                            <small class="text-muted d-block">{{ $module->getLabel() }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($module->isActive())
                                        <span class="badge bg-success rounded-pill px-3">
                                            <i class="bi bi-check-circle me-1"></i>Active
                                        </span>
                                    @else
                                        <span class="badge bg-secondary rounded-pill px-3">
                                            <i class="bi bi-x-circle me-1"></i>Inactive
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($module->isCustom())
                                        <span class="badge bg-info rounded-pill px-3">Custom</span>
                                    @else
                                        <span class="badge bg-light text-dark rounded-pill px-3">Standard</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark rounded-pill">
                                        {{ $module->fields()->count() }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark rounded-pill">
                                        {{ $module->blocks()->count() }}
                                    </span>
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="btn-group">
                                        <a href="{{ route('tenant.settings.modules.layout', $module->getName()) }}"
                                            class="btn btn-sm btn-outline-primary" title="Edit Layout">
                                            <i class="bi bi-layout-text-sidebar"></i>
                                        </a>
                                        <a href="{{ route('tenant.settings.modules.numbering', $module->getName()) }}"
                                            class="btn btn-sm btn-outline-info" title="Configure Numbering">
                                            <i class="bi bi-123"></i>
                                        </a>
                                        <a href="{{ route('tenant.custom-fields.index', $module->getName()) }}"
                                            class="btn btn-sm btn-outline-success" title="Custom Fields">
                                            <i class="bi bi-plus-square"></i>
                                        </a>
                                        <form action="{{ route('tenant.settings.modules.toggle', $module->getName()) }}"
                                            method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit"
                                                class="btn btn-sm btn-outline-{{ $module->isActive() ? 'danger' : 'success' }}"
                                                title="{{ $module->isActive() ? 'Deactivate' : 'Activate' }}">
                                                <i class="bi bi-{{ $module->isActive() ? 'toggle-on' : 'toggle-off' }}"></i>
                                            </button>
                                        </form>
                                    </div>
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
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .icon-circle {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
@endsection