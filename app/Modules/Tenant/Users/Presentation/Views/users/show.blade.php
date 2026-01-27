@extends('tenant::layout')

@section('content')
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a
                                href="{{ route('tenant.dashboard') }}">{{ __('tenant::users.dashboard') ?? 'Dashboard' }}</a>
                        </li>
                        <li class="breadcrumb-item"><a
                                href="{{ route('tenant.settings.users.index') }}">{{ __('tenant::users.users') ?? 'Users' }}</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            {{ $user->getFullName() ?: $user->getUserName() }}
                        </li>
                    </ol>
                </nav>
                <h4 class="mb-0 fw-bold">{{ __('tenant::users.user_details') ?? 'User Details' }}</h4>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('tenant.settings.users.edit', $user->getId()) }}"
                    class="btn btn-primary d-flex align-items-center gap-2 rounded-3">
                    <i class="bi bi-pencil"></i>
                    <span>{{ __('tenant::users.edit') ?? 'Edit' }}</span>
                </a>
                <a href="{{ route('tenant.settings.users.index') }}" class="btn btn-outline-secondary rounded-3">
                    {{ __('tenant::users.back_to_list') ?? 'Back to List' }}
                </a>
            </div>
        </div>

        <div class="row g-4">
            <!-- Sidebar Info -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-body text-center pt-5">
                        <div class="avatar-xl mx-auto mb-3">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($user->getFullName() ?: $user->getUserName()) }}&background=6366f1&color=fff&size=128"
                                class="rounded-circle shadow-sm border border-4 border-white" width="128" alt="User Avatar">
                        </div>
                        <h5 class="mb-1 fw-bold">{{ $user->getFullName() }}</h5>
                        <p class="text-muted mb-3">
                            {{ $user->getTitle() ?: ($user->isAdmin() ? __('tenant::users.administrator') : __('tenant::users.user')) }}
                        </p>

                        <div class="d-flex justify-content-center gap-2 mb-4">
                            @if($user->isActive())
                                <span class="badge bg-soft-success text-success px-3 py-2 rounded-pill">
                                    <i class="bi bi-check-circle me-1"></i> {{ __('tenant::users.active') ?? 'Active' }}
                                </span>
                            @else
                                <span class="badge bg-soft-danger text-danger px-3 py-2 rounded-pill">
                                    <i class="bi bi-x-circle me-1"></i> {{ __('tenant::users.inactive') ?? 'Inactive' }}
                                </span>
                            @endif
                            @if($user->isAdmin())
                                <span class="badge bg-soft-primary text-primary px-3 py-2 rounded-pill">
                                    <i class="bi bi-shield-check me-1"></i> {{ __('tenant::users.admin_access') ?? 'Admin' }}
                                </span>
                            @endif
                        </div>

                        <hr class="my-4 mx-n3 opacity-50">

                        <div class="text-start">
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon-shape icon-sm bg-soft-secondary text-secondary rounded-3 me-3">
                                    <i class="bi bi-envelope"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">{{ __('tenant::users.email') ?? 'Email' }}</small>
                                    <a href="mailto:{{ $user->getEmail() }}"
                                        class="text-dark fw-medium text-decoration-none">{{ $user->getEmail() }}</a>
                                </div>
                            </div>
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon-shape icon-sm bg-soft-secondary text-secondary rounded-3 me-3">
                                    <i class="bi bi-phone"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">{{ __('tenant::users.mobile') ?? 'Mobile' }}</small>
                                    <span class="text-dark fw-medium">{{ $user->getPhoneMobile() ?: '-' }}</span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon-shape icon-sm bg-soft-secondary text-secondary rounded-3 me-3">
                                    <i class="bi bi-telephone"></i>
                                </div>
                                <div>
                                    <small
                                        class="text-muted d-block">{{ __('tenant::users.office_phone') ?? 'Office Phone' }}</small>
                                    <span class="text-dark fw-medium">{{ $user->getPhoneWork() ?: '-' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Role & Reporting -->
                <div class="card border-0 shadow-sm rounded-4 mt-4">
                    <div class="card-header bg-transparent border-0 pt-4 pb-0">
                        <h6 class="mb-0 fw-bold">{{ __('tenant::users.role_reporting') ?? 'Role & Reporting' }}</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label
                                class="small text-uppercase text-muted fw-bold mb-2 d-block">{{ __('tenant::users.role') ?? 'Role' }}</label>
                            <div class="d-flex align-items-center p-3 bg-light rounded-3">
                                <i class="bi bi-briefcase text-primary fs-4 me-3"></i>
                                <div>
                                    <span class="fw-bold d-block">{{ $role->rolename ?? '-' }}</span>
                                    <small class="text-muted">{{ $role->roleid ?? '-' }}</small>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label
                                class="small text-uppercase text-muted fw-bold mb-2 d-block">{{ __('tenant::users.reports_to') ?? 'Reports To' }}</label>
                            @if($reportsTo)
                                <div class="d-flex align-items-center p-3 border rounded-3">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode(($reportsTo->first_name ?? '') . ' ' . ($reportsTo->last_name ?? '')) }}&background=e2e8f0&color=475569&size=40"
                                        class="rounded-circle me-3" width="40" alt="">
                                    <div>
                                        <span
                                            class="fw-bold d-block text-dark">{{ trim(($reportsTo->first_name ?? '') . ' ' . ($reportsTo->last_name ?? '')) ?: $reportsTo->user_name }}</span>
                                        <small class="text-muted">{{ $reportsTo->user_name }}</small>
                                    </div>
                                </div>
                            @else
                                <span class="text-muted italic">{{ __('tenant::users.none') ?? 'None' }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Information -->
            <div class="col-lg-8">
                <!-- Profile Information -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-transparent border-0 pt-4 pb-0 d-flex justify-content-between">
                        <h6 class="mb-0 fw-bold">{{ __('tenant::users.profile_information') ?? 'Profile Information' }}</h6>
                    </div>
                    <div class="card-body pt-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label
                                    class="small text-muted mb-1 d-block">{{ __('tenant::users.user_name') ?? 'User Name' }}</label>
                                <span class="fw-medium">{{ $user->getUserName() }}</span>
                            </div>
                            <div class="col-md-6">
                                <label
                                    class="small text-muted mb-1 d-block">{{ __('tenant::users.department') ?? 'Department' }}</label>
                                <span class="fw-medium">{{ $user->getDepartment() ?: '-' }}</span>
                            </div>
                            <div class="col-md-6">
                                <label
                                    class="small text-muted mb-1 d-block">{{ __('tenant::users.first_name') ?? 'First Name' }}</label>
                                <span class="fw-medium">{{ $user->getFirstName() ?: '-' }}</span>
                            </div>
                            <div class="col-md-6">
                                <label
                                    class="small text-muted mb-1 d-block">{{ __('tenant::users.last_name') ?? 'Last Name' }}</label>
                                <span class="fw-medium">{{ $user->getLastName() }}</span>
                            </div>
                            <div class="col-md-12">
                                <label
                                    class="small text-muted mb-1 d-block">{{ __('tenant::users.signature') ?? 'Signature' }}</label>
                                <div class="p-3 bg-light rounded-3 min-height-50">
                                    {!! nl2br(e($user->getSignature() ?: 'No signature provided.')) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Address Information -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-transparent border-0 pt-4 pb-0">
                        <h6 class="mb-0 fw-bold">{{ __('tenant::users.address_information') ?? 'Address Information' }}</h6>
                    </div>
                    <div class="card-body pt-4">
                        <div class="row g-4">
                            <div class="col-md-12">
                                <label
                                    class="small text-muted mb-1 d-block">{{ __('tenant::users.street_address') ?? 'Street Address' }}</label>
                                <span class="fw-medium">{{ $user->getAddressStreet() ?: '-' }}</span>
                            </div>
                            <div class="col-md-6">
                                <label
                                    class="small text-muted mb-1 d-block">{{ __('tenant::users.city') ?? 'City' }}</label>
                                <span class="fw-medium">{{ $user->getAddressCity() ?: '-' }}</span>
                            </div>
                            <div class="col-md-6">
                                <label
                                    class="small text-muted mb-1 d-block">{{ __('tenant::users.state') ?? 'State' }}</label>
                                <span class="fw-medium">{{ $user->getAddressState() ?: '-' }}</span>
                            </div>
                            <div class="col-md-6">
                                <label
                                    class="small text-muted mb-1 d-block">{{ __('tenant::users.postal_code') ?? 'Postal Code' }}</label>
                                <span class="fw-medium">{{ $user->getAddressPostalCode() ?: '-' }}</span>
                            </div>
                            <div class="col-md-6">
                                <label
                                    class="small text-muted mb-1 d-block">{{ __('tenant::users.country') ?? 'Country' }}</label>
                                <span class="fw-medium">{{ $user->getAddressCountry() ?: '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Information -->
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-transparent border-0 pt-4 pb-0">
                        <h6 class="mb-0 fw-bold">{{ __('tenant::users.system_information') ?? 'System Information' }}</h6>
                    </div>
                    <div class="card-body pt-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label
                                    class="small text-muted mb-1 d-block">{{ __('tenant::users.date_entered') ?? 'Date Entered' }}</label>
                                <span class="fw-medium">{{ $user->getDateEntered()->format('Y-m-d H:i:s') }}</span>
                            </div>
                            <div class="col-md-6">
                                <label
                                    class="small text-muted mb-1 d-block">{{ __('tenant::users.date_modified') ?? 'Date Modified' }}</label>
                                <span class="fw-medium">{{ $user->getDateModified()->format('Y-m-d H:i:s') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .icon-shape {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .icon-sm {
            width: 32px;
            height: 32px;
        }

        .bg-soft-primary {
            background-color: rgba(99, 102, 241, 0.1);
        }

        .text-primary {
            color: #6366f1;
        }

        .bg-soft-success {
            background-color: rgba(16, 185, 129, 0.1);
        }

        .text-success {
            color: #10b981;
        }

        .bg-soft-danger {
            background-color: rgba(239, 68, 68, 0.1);
        }

        .text-danger {
            color: #ef4444;
        }

        .bg-soft-secondary {
            background-color: rgba(107, 114, 128, 0.1);
        }

        .text-secondary {
            color: #6b7280;
        }

        .min-height-50 {
            min-height: 50px;
        }
    </style>
@endsection