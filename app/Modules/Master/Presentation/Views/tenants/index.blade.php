@extends('master::layout')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Tenants</h2>
            <p class="text-muted">Manage your SaaS tenants</p>
        </div>
        <a href="{{ route('master.tenants.create') }}" class="btn btn-primary shadow-sm">
            <i class="bi bi-plus-lg me-2"></i>New Tenant
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Tenant ID</th>
                            <th>Domains</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th class="text-end pe-4">Actions</th>
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
                                        class="badge bg-success bg-opacity-10 text-success px-3 py-1 rounded-pill">Active</span>
                                </td>
                                <td class="text-muted small">{{ $tenant->created_at }}</td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('master.tenants.show', $tenant->id) }}"
                                            class="btn btn-sm btn-outline-info border-0" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('master.tenants.edit', $tenant->id) }}"
                                            class="btn btn-sm btn-outline-primary border-0" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('master.tenants.destroy', $tenant->id) }}" method="POST"
                                            onsubmit="return confirm('Are you sure you want to delete this tenant? This cannot be undone.');"
                                            class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger border-0" title="Delete">
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
                                    No tenants found. Create your first one!
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection