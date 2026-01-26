@extends('tenant::layout')

@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold mb-0">
                <i class="bi bi-pencil me-2"></i>{{ __('tenant::users.update_role') ?? 'Edit Role' }}
            </h3>
            <a href="{{ route('tenant.settings.users.roles.index') }}" class="btn btn-outline-secondary rounded-3">
                <i class="bi bi-arrow-left me-1"></i>{{ __('tenant::users.cancel') }}
            </a>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <form action="{{ route('tenant.settings.users.roles.update', $role->roleid) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label fw-bold">{{ __('tenant::users.role_name') ?? 'Role Name' }}</label>
                        <input type="text" name="rolename" class="form-control rounded-3" value="{{ $role->rolename }}"
                            required>
                    </div>
                    <div class="mb-4">
                        <label
                            class="form-label fw-bold text-muted">{{ __('tenant::users.parent_role') ?? 'Parent Role' }}</label>
                        <input type="text" class="form-control rounded-3 bg-light" value="{{ $role->parentrole }}" readonly>
                        <small class="text-muted">Parent role cannot be changed in this version.</small>
                    </div>
                    <!-- Profile Assignment could/should be here in full implementation -->

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary rounded-3 px-4 shadow-sm">
                            <i class="bi bi-save me-2"></i>{{ __('tenant::users.update') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection