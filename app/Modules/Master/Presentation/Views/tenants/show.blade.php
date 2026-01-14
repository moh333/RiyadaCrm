@extends('master::layout')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Tenant Details</h2>
            <p class="text-muted">Overview of <strong>{{ $tenant->id }}</strong></p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('master.tenants.index') }}" class="btn btn-light border">
                <i class="bi bi-arrow-left me-2"></i>Back
            </a>
            <a href="{{ route('master.tenants.edit', $tenant->id) }}" class="btn btn-outline-primary shadow-sm">
                <i class="bi bi-pencil me-2"></i>Edit
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Info -->
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold">General Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted small text-uppercase fw-bold">Tenant ID</div>
                        <div class="col-sm-8 fw-medium">{{ $tenant->id }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted small text-uppercase fw-bold">Primary Domain</div>
                        <div class="col-sm-8">
                            <a href="http://{{ $tenant->domains[0] ?? '#' }}" target="_blank" class="text-decoration-none">
                                {{ $tenant->domains[0] ?? 'N/A' }}
                                <i class="bi bi-box-arrow-up-right ms-1 small"></i>
                            </a>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted small text-uppercase fw-bold">Created At</div>
                        <div class="col-sm-8">{{ $tenant->created_at }}</div>
                    </div>
                    <div class="row mb-0">
                        <div class="col-sm-4 text-muted small text-uppercase fw-bold">Status</div>
                        <div class="col-sm-8">
                            <span class="badge bg-success bg-opacity-10 text-success px-3 py-1 rounded-pill">Active</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold">Domains</h5>
                    <button class="btn btn-sm btn-light border" disabled><i class="bi bi-plus me-1"></i>Add Domain</button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Domain Name</th>
                                    <th>Type</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tenant->domains as $domain)
                                    <tr>
                                        <td class="ps-4 fw-medium">{{ $domain }}</td>
                                        <td><span class="badge bg-light text-dark fw-normal border">Primary</span></td>
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
                    <p class="text-muted small">Subscription Plan: <span class="text-primary fw-bold">Enterprise</span></p>
                    <hr>
                    <div class="d-grid">
                        <a href="http://{{ $tenant->domains[0] ?? '#' }}" target="_blank" class="btn btn-primary">
                            <i class="bi bi-speedometer2 me-2"></i>Login as Admin
                        </a>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-danger border-opacity-25">
                <div class="card-body p-4">
                    <h6 class="text-danger fw-bold mb-3">Danger Zone</h6>
                    <p class="small text-muted mb-4">Once you delete a tenant, there is no going back. All data related to
                        this tenant will be permanently removed.</p>
                    <form action="{{ route('master.tenants.destroy', $tenant->id) }}" method="POST"
                        onsubmit="return confirm('Are you sure you want to delete this tenant? This cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                            <i class="bi bi-trash me-2"></i>Delete Tenant
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection