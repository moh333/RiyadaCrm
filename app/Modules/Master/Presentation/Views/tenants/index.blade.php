@extends('master::layout')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">{{ __('master::master.tenants') }}</h2>
            <p class="text-muted">{{ __('master::master.manage_saas') }}</p>
        </div>
        <a href="{{ route('master.tenants.create') }}" class="btn btn-primary shadow-sm">
            <i class="bi bi-plus-lg me-2"></i>{{ __('master::master.create_tenant') }}
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">{{ __('master::master.tenant_id') }}</th>
                            <th>{{ __('master::master.domains') }}</th>
                            <th>{{ __('master::master.status') }}</th>
                            <th>{{ __('master::master.created_at') }}</th>
                            <th class="text-end pe-4">{{ __('master::master.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tenants as $tenant)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-initials bg-light text-primary me-3"
                                            style="width: 35px; height: 35px; font-size: 0.9rem;">
                                            {{ substr($tenant->id, 0, 2) }}
                                        </div>
                                        <span class="fw-medium text-dark">{{ $tenant->id }}</span>
                                    </div>
                                </td>
                                <td>
                                    @foreach($tenant->domains as $domain)
                                        <span
                                            class="badge bg-indigo-50 text-indigo-700 border border-indigo-100 rounded-pill px-3 py-1 fw-normal text-primary bg-light">
                                            {{ $domain }}
                                        </span>
                                    @endforeach
                                </td>
                                <td>
                                    <span
                                        class="badge bg-success bg-opacity-10 text-success px-3 py-1 rounded-pill">{{ __('master::master.active') }}</span>
                                </td>
                                <td class="text-muted small">{{ $tenant->created_at }}</td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-2 text-nowrap">
                                        <a href="{{ route('master.tenants.show', $tenant->id) }}"
                                            class="btn btn-sm btn-outline-info border-0"
                                            title="{{ __('master::master.view') }}">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('master.tenants.edit', $tenant->id) }}"
                                            class="btn btn-sm btn-outline-primary border-0"
                                            title="{{ __('master::master.edit') }}">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('master.tenants.destroy', $tenant->id) }}" method="POST"
                                            onsubmit="return confirm('{{ __('master::master.sure_delete') }}');"
                                            class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger border-0"
                                                title="{{ __('master::master.delete') }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-3 opacity-25"></i>
                                    {{ __('master::master.no_tenants') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection