@extends('tenant::layout')

@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-0">{{ __('contacts::contacts.contacts') }}</h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="{{ route('tenant.dashboard') }}">{{ __('contacts::contacts.dashboard') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('contacts::contacts.contacts') }}</li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="{{ route('tenant.contacts.create') }}"
                    class="btn btn-primary d-flex align-items-center gap-2 px-4 py-2 rounded-3 shadow-sm">
                    <i class="bi bi-plus-lg"></i>
                    <span>{{ __('contacts::contacts.add_contact') }}</span>
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <form action="{{ route('tenant.contacts.index') }}" method="GET" class="search-box mb-0 w-100">
                            <i class="bi bi-search text-muted"></i>
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="{{ __('contacts::contacts.search_placeholder') }}">
                        </form>
                    </div>
                    <div class="col-md-6 text-md-end mt-3 mt-md-0">
                        <div class="btn-group shadow-sm">
                            <button class="btn btn-outline-secondary dropdown-toggle rounded-3" type="button"
                                data-bs-toggle="dropdown">
                                <i class="bi bi-funnel me-1"></i> Filter
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">All Contacts</a></li>
                                <li><a class="dropdown-item" href="#">My Contacts</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="#">Portal Enabled</a></li>
                            </ul>
                        </div>
                        <button class="btn btn-outline-secondary ms-2 rounded-3">
                            <i class="bi bi-download me-1"></i> Export
                        </button>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted">
                        <tr>
                            <th class="ps-4 py-3">{{ __('contacts::contacts.contact_no') }}</th>
                            <th class="py-3">{{ __('contacts::contacts.full_name') }}</th>
                            <th class="py-3">{{ __('contacts::contacts.email') }}</th>
                            <th class="py-3">{{ __('contacts::contacts.account') }}</th>
                            <th class="py-3 text-center">Portal</th>
                            <th class="pe-4 py-3 text-end">{{ __('contacts::contacts.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($contacts as $contact)
                            <tr>
                                <td class="ps-4">
                                    <span
                                        class="badge bg-soft-primary text-primary rounded-pill px-3">{{ $contact->getContactNo() }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="https://ui-avatars.com/api/?name={{ $contact->getFullName()->getDisplayName() }}&background=6366f1&color=fff"
                                            class="rounded-circle me-3" width="36" height="36" alt="">
                                        <div>
                                            <a href="{{ route('tenant.contacts.show', $contact->getId()) }}"
                                                class="text-decoration-none fw-bold text-main d-block">
                                                {{ $contact->getFullName()->getDisplayName() }}
                                            </a>
                                            @if($contact->getTitle())
                                                <small class="text-muted">{{ $contact->getTitle() }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($contact->getEmail())
                                        <a href="mailto:{{ $contact->getEmail() }}" class="text-muted text-decoration-none">
                                            <i class="bi bi-envelope me-1"></i> {{ $contact->getEmail() }}
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($contact->getAccountId())
                                        <span class="text-main"><i class="bi bi-building me-1"></i> Admin Account</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($contact->isPortalEnabled())
                                        <span class="badge bg-soft-success text-success rounded-pill px-3">Enabled</span>
                                    @else
                                        <span class="badge bg-soft-secondary text-secondary rounded-pill px-3">Disabled</span>
                                    @endif
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('tenant.contacts.show', $contact->getId()) }}"
                                            class="btn btn-sm btn-soft-info rounded-2"
                                            title="{{ __('contacts::contacts.view') }}">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('tenant.contacts.edit', $contact->getId()) }}"
                                            class="btn btn-sm btn-soft-primary rounded-2"
                                            title="{{ __('contacts::contacts.edit') }}">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('tenant.contacts.destroy', $contact->getId()) }}" method="POST"
                                            onsubmit="return confirm('{{ __('contacts::contacts.are_you_sure') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-soft-danger rounded-2"
                                                title="{{ __('contacts::contacts.delete') }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="py-4">
                                        <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
                                        <p class="mt-3 text-muted">{{ __('contacts::contacts.no_contacts_found') }}</p>
                                        <a href="{{ route('tenant.contacts.create') }}" class="btn btn-primary mt-2">
                                            <i class="bi bi-plus-lg me-1"></i> {{ __('contacts::contacts.add_contact') }}
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white border-top py-3 px-4">
                {{ $contacts->links() }}
            </div>
        </div>
    </div>

    <style>
        .link-muted {
            color: #64748b;
        }

        .link-muted:hover {
            color: #1e293b;
        }

        .bg-soft-primary {
            background-color: #eef2ff;
            color: #6366f1;
        }

        .bg-soft-success {
            background-color: #f0fdf4;
            color: #22c55e;
        }

        .bg-soft-info {
            background-color: #ecfeff;
            color: #0891b2;
        }

        .btn-soft-primary {
            background-color: #eef2ff;
            color: #6366f1;
            border: none;
        }

        .btn-soft-primary:hover {
            background-color: #6366f1;
            color: white;
        }

        .btn-soft-info {
            background-color: #ecfeff;
            color: #0891b2;
            border: none;
        }

        .btn-soft-info:hover {
            background-color: #0891b2;
            color: white;
        }

        .btn-soft-danger {
            background-color: #fef2f2;
            color: #ef4444;
            border: none;
        }

        .btn-soft-danger:hover {
            background-color: #ef4444;
            color: white;
        }

        .search-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 0.5rem 1rem;
            display: flex;
            align-items: center;
            transition: all 0.2s;
        }

        .search-box:focus-within {
            background: white;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .search-box input {
            background: transparent;
            border: none;
            outline: none;
            margin-left: 0.75rem;
            width: 100%;
            color: #1e293b;
        }
    </style>
@endsection