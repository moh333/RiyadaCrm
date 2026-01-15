@extends('master::layout')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">{{ __('master::master.tenant_details') }}</h2>
            <p class="text-muted">{{ __('master::master.overview_of') }} <strong>{{ $tenant->id }}</strong></p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('master.tenants.index') }}" class="btn btn-light border">
                <i class="bi bi-arrow-left me-2"></i>{{ __('master::master.back') }}
            </a>
            <a href="{{ route('master.tenants.edit', $tenant->id) }}" class="btn btn-outline-primary shadow-sm">
                <i class="bi bi-pencil me-2"></i>{{ __('master::master.edit') }}
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Info -->
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold">{{ __('master::master.general_info') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted small text-uppercase fw-bold">{{ __('master::master.tenant_id') }}
                        </div>
                        <div class="col-sm-8 fw-medium">{{ $tenant->id }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted small text-uppercase fw-bold">
                            {{ __('master::master.primary_domain') }}</div>
                        <div class="col-sm-8">
                            <a href="http://{{ $tenant->domains[0] ?? '#' }}" target="_blank" class="text-decoration-none">
                                {{ $tenant->domains[0] ?? 'N/A' }}
                                <i class="bi bi-box-arrow-up-right ms-1 small"></i>
                            </a>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted small text-uppercase fw-bold">{{ __('master::master.created_at') }}
                        </div>
                        <div class="col-sm-8">{{ $tenant->created_at }}</div>
                    </div>
                    <div class="row mb-0">
                        <div class="col-sm-4 text-muted small text-uppercase fw-bold">{{ __('master::master.status') }}
                        </div>
                        <div class="col-sm-8">
                            <span
                                class="badge bg-success bg-opacity-10 text-success px-3 py-1 rounded-pill">{{ __('master::master.active') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold">{{ __('master::master.domains') }}</h5>
                    <button class="btn btn-sm btn-light border" disabled><i
                            class="bi bi-plus me-1"></i>{{ __('master::master.add_domain') }}</button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">{{ __('master::master.domain_name') }}</th>
                                    <th>{{ __('master::master.type') }}</th>
                                    <th class="text-end pe-4">{{ __('master::master.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tenant->domains as $domain)
                                    <tr>
                                        <td class="ps-4 fw-medium">{{ $domain }}</td>
                                        <td><span
                                                class="badge bg-light text-dark fw-normal border">{{ __('master::master.primary') }}</span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <button class="btn btn-sm btn-light border-0" disabled><i
                                                    class="bi bi-three-dots"></i></button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4 text-center">
                    <div class="avatar-initials bg-primary text-white mx-auto mb-3"
                        style="width: 80px; height: 80px; font-size: 2rem;">
                        {{ substr($tenant->id, 0, 2) }}
                    </div>
                    <h4 class="fw-bold mb-1">{{ ucfirst($tenant->id) }}</h4>
                    <p class="text-muted small">{{ __('master::master.subscription_plan') }}: <span
                            class="text-primary fw-bold">{{ __('master::master.enterprise') }}</span></p>
                    <hr>
                    <div class="d-grid gap-2">
                        <a href="{{ route('master.tenants.impersonate', $tenant->id) }}" target="_blank" class="btn btn-primary d-flex align-items-center justify-content-center py-2 shadow-sm">
                            <i class="bi bi-box-arrow-in-right me-2"></i>{{ __('master::master.login_as_admin') }}
                        </a>
                        <a href="http://{{ $tenant->domains[0] ?? '#' }}" target="_blank" class="btn btn-outline-primary d-flex align-items-center justify-content-center py-2">
                            <i class="bi bi-speedometer2 me-2"></i>{{ __('master::master.visit_dashboard') }}
                        </a>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-danger border-opacity-25">
                <div class="card-body p-4">
                    <h6 class="text-danger fw-bold mb-3">{{ __('master::master.danger_zone') }}</h6>
                    <p class="small text-muted mb-4">{{ __('master::master.delete_tenant_warning') }}</p>
                    <form action="{{ route('master.tenants.destroy', $tenant->id) }}" method="POST"
                        onsubmit="return confirm('{{ __('master::master.sure_delete') }}');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                            <i class="bi bi-trash me-2"></i>{{ __('master::master.delete_tenant') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection