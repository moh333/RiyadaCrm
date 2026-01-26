@extends('tenant::layout')

@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold mb-0">
                <i class="bi bi-diagram-3 me-2"></i>{{ __('tenant::users.create_role') ?? 'Create Role' }}
            </h3>
            <a href="{{ route('tenant.settings.users.roles.index') }}" class="btn btn-outline-secondary rounded-3">
                <i class="bi bi-arrow-left me-1"></i>{{ __('tenant::users.cancel') }}
            </a>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <form action="{{ route('tenant.settings.users.roles.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold">{{ __('tenant::users.role_name') ?? 'Role Name' }}</label>
                        <input type="text" name="rolename" class="form-control rounded-3" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">{{ __('tenant::users.reports_to') ?? 'Reports To' }}</label>
                        <select name="parent_role_id" class="form-select rounded-3" required>
                            @foreach($parentRoles as $pRole)
                                <option value="{{ $pRole->roleid }}">{{ $pRole->rolename }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary rounded-3 px-4 shadow-sm">
                            <i class="bi bi-save me-2"></i>{{ __('tenant::users.create') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection