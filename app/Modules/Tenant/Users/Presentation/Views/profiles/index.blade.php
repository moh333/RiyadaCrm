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
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3">{{ __('tenant::users.profile_name') ?? 'Profile Name' }}</th>
                                <th class="py-3">{{ __('tenant::users.description') ?? 'Description' }}</th>
                                <th class="text-end pe-4 py-3">{{ __('tenant::users.actions') ?? 'Actions' }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($profiles as $profile)
                                <tr>
                                    <td class="ps-4 fw-bold">{{ $profile->profilename }}</td>
                                    <td class="text-muted">{{ $profile->description }}</td>
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
                </div>
            </div>
        </div>
    </div>
@endsection