@extends('tenant::layout')

@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold mb-0">
                <i class="bi bi-person-badge me-2"></i>{{ __('tenant::users.profiles') ?? 'Profiles' }}
            </h3>
            <a href="{{ route('tenant.settings.users.profiles.create') }}" class="btn btn-primary rounded-3 shadow-sm">
                <i class="bi bi-plus-lg me-1"></i>{{ __('tenant::users.create') }}
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success rounded-3 shadow-sm mb-4">
                {{ session('success') }}
            </div>
        @endif

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    @if($profiles->isEmpty())
                        <div class="text-center p-5 text-muted">
                            <i class="bi bi-inbox fs-1 mb-3 d-block"></i>
                            <p>No profiles found.</p>
                        </div>
                    @else
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3">{{ __('tenant::users.profile_name') ?? 'Profile Name' }}</th>
                                <th class="py-3">{{ __('tenant::users.associated_role') ?? 'Role' }}</th>
                                <th class="py-3">{{ __('tenant::users.description') ?? 'Description' }}</th>
                                <th class="text-end pe-4 py-3">{{ __('tenant::users.actions') ?? 'Actions' }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($profiles as $profile)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold">{{ $profile->profilename }}</div>
                                        @if ($profile->directly_related_to_role)
                                            <span class="badge bg-soft-info text-info small border py-1">{{ __('tenant::users.role_specific') ?? 'Role Specific' }}</span>
                                        @else
                                            <span class="badge bg-soft-secondary text-secondary small border py-1">{{ __('tenant::users.global') ?? 'Global' }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($profile->role_name)
                                            <span class="text-primary fw-semibold"><i class="bi bi-diagram-3 me-1"></i>{{ $profile->role_name }}</span>
                                        @else
                                            <span class="text-muted small"><em>{{ __('tenant::users.not_assigned') ?? 'No Role' }}</em></span>
                                        @endif
                                    </td>
                                    <td class="text-muted small">{{ $profile->description }}</td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="{{ route('tenant.settings.users.profiles.edit', $profile->profileid) }}"
                                                class="btn btn-sm btn-outline-primary rounded-pill">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form
                                                action="{{ route('tenant.settings.users.profiles.destroy', $profile->profileid) }}"
                                                method="POST"
                                                onsubmit="return confirm('{{ __('tenant::users.are_you_sure') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection