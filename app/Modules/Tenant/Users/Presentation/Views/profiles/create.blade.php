@extends('tenant::layout')

@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold mb-0">
                <i class="bi bi-person-badge me-2"></i>{{ __('tenant::users.create_profile') ?? 'Create Profile' }}
            </h3>
            <a href="{{ route('tenant.settings.users.profiles.index') }}" class="btn btn-outline-secondary rounded-3">
                <i class="bi bi-arrow-left me-1"></i>{{ __('tenant::users.cancel') }}
            </a>
        </div>

        @if($errors->any())
            <div class="alert alert-danger rounded-3 shadow-sm mb-4">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <form action="{{ route('tenant.settings.users.profiles.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold">{{ __('tenant::users.profile_name') ?? 'Profile Name' }}</label>
                        <input type="text" name="profilename" class="form-control rounded-3" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">{{ __('tenant::users.description') ?? 'Description' }}</label>
                        <textarea name="description" class="form-control rounded-3" rows="3"></textarea>
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