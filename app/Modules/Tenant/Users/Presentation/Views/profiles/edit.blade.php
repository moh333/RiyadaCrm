@extends('tenant::layout')

@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold mb-0">
                <i class="bi bi-pencil me-2"></i>{{ __('tenant::users.update_profile') ?? 'Edit Profile' }}
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
                <form action="{{ route('tenant.settings.users.profiles.update', $profile->profileid) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label fw-bold">{{ __('tenant::users.profile_name') ?? 'Profile Name' }}</label>
                        <input type="text" name="profilename" class="form-control rounded-3"
                            value="{{ $profile->profilename }}" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">{{ __('tenant::users.description') ?? 'Description' }}</label>
                        <textarea name="description" class="form-control rounded-3"
                            rows="3">{{ $profile->description }}</textarea>
                    </div>

                    <div class="alert alert-info rounded-3">
                        <i class="bi bi-info-circle me-2"></i>Permissions editor is disabled in this view.
                    </div>

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