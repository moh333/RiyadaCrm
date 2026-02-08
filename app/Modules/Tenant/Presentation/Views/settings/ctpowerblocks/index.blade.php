@extends('tenant::layout')

@section('title', __('tenant::settings.ct_power_blocks'))

@section('content')
    <div class="container-fluid py-4">
        {{-- Page Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-2 fw-bold">
                    <i class="bi bi-magic text-primary me-2"></i>
                    {{ __('tenant::settings.ct_power_blocks') }}
                </h1>
                <p class="text-muted mb-0">
                    {{ __('tenant::settings.ct_power_blocks_description') }}
                </p>
            </div>
            <div>
                <a href="{{ route('tenant.settings.crm.ctpower-blocks-fields.create') }}"
                    class="btn btn-primary rounded-pill px-4 shadow-sm">
                    <i class="bi bi-plus-lg me-2"></i>{{ __('tenant::settings.add_rule') ?? 'Add Rule' }}
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Rules List --}}
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3 border-0">{{ __('tenant::settings.module') }}</th>
                                <th class="py-3 border-0">{{ __('tenant::settings.conditions') }}</th>
                                <th class="py-3 border-0 text-center">{{ __('tenant::settings.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rules as $rule)
                                <tr>
                                    <td class="px-4">
                                        <span class="fw-bold">{{ $rule->module_name }}</span>
                                    </td>
                                    <td>
                                        <div class="small text-muted text-truncate" style="max-width: 400px;">
                                            {{ $rule->conditions }}
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="{{ route('tenant.settings.crm.ctpower-blocks-fields.edit', $rule->ctpowerblockfieldsid) }}"
                                                class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                <i class="bi bi-pencil me-1"></i>{{ __('tenant::settings.edit') }}
                                            </a>
                                            <form
                                                action="{{ route('tenant.settings.crm.ctpower-blocks-fields.destroy', $rule->ctpowerblockfieldsid) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3"
                                                    onclick="return confirm('{{ __('tenant::settings.confirm_delete') }}')">
                                                    <i class="bi bi-trash me-1"></i>{{ __('tenant::settings.delete') }}
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="bi bi-puzzle fs-1 mb-3 d-block"></i>
                                            <p class="mb-0">No rules configured yet.</p>
                                            <a href="{{ route('tenant.settings.crm.ctpower-blocks-fields.create') }}"
                                                class="btn btn-link link-primary p-0">Create your first rule</a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection