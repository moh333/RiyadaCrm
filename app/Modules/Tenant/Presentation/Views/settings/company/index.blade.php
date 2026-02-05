@extends('tenant::layout')

@section('title', __('tenant::settings.company_details'))

@section('content')
    <div class="container-fluid py-4">
        {{-- Page Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-2 fw-bold">
                    <i class="bi bi-building text-primary me-2"></i>
                    {{ __('tenant::settings.company_details') }}
                </h1>
                <p class="text-muted mb-0">{{ __('tenant::settings.company_details_description') }}</p>
            </div>
            <div>
                <a href="{{ route('tenant.settings.crm.company.edit') }}" class="btn btn-primary rounded-pill px-4">
                    <i class="bi bi-pencil me-2"></i>{{ __('tenant::settings.edit') }}
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row g-4">
            {{-- Company Logo --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body text-center p-4">
                        <h5 class="card-title fw-bold mb-4">{{ __('tenant::settings.logo') }}</h5>
                        <div class="mb-3">
                            <img src="{{ $organization->logo ? tenant_asset($organization->logo) : global_asset('images/logo-placeholder.png') }}"
                                alt="Company Logo" class="img-fluid rounded-3" style="max-height: 200px;">
                        </div>
                        <p class="text-muted small mb-0">
                            <i class="bi bi-info-circle me-1"></i>
                            {{ __('tenant::settings.logo_format_help') }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Company Information --}}
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="fw-bold mb-0">
                            <i class="bi bi-info-circle text-primary me-2"></i>
                            {{ __('tenant::settings.company_information') }}
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0">
                                        <i class="bi bi-building fs-5 text-muted me-3"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <label class="form-label text-muted small mb-1">
                                            {{ __('tenant::settings.organization_name') }}
                                        </label>
                                        <p class="fw-semibold mb-0">
                                            {{ $organization->organizationname ?? 'Your Company Name' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0">
                                        <i class="bi bi-geo-alt fs-5 text-muted me-3"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <label class="form-label text-muted small mb-1">
                                            {{ __('tenant::settings.address') }}
                                        </label>
                                        <p class="mb-0">{{ $organization->address ?? '' }}</p>
                                        <p class="mb-0">{{ $organization->city ?? '' }}, {{ $organization->state ?? '' }}
                                            {{ $organization->code ?? '' }}
                                        </p>
                                        <p class="mb-0">{{ $organization->country ?? '' }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0">
                                        <i class="bi bi-telephone fs-5 text-muted me-3"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <label class="form-label text-muted small mb-1">
                                            {{ __('tenant::settings.phone') }}
                                        </label>
                                        <p class="fw-semibold mb-0">{{ $organization->phone ?? '' }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0">
                                        <i class="bi bi-printer fs-5 text-muted me-3"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <label class="form-label text-muted small mb-1">
                                            {{ __('tenant::settings.fax') }}
                                        </label>
                                        <p class="fw-semibold mb-0">{{ $organization->fax ?? '' }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0">
                                        <i class="bi bi-globe fs-5 text-muted me-3"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <label class="form-label text-muted small mb-1">
                                            {{ __('tenant::settings.website') }}
                                        </label>
                                        <p class="fw-semibold mb-0">{{ $organization->website ?? '' }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0">
                                        <i class="bi bi-receipt fs-5 text-muted me-3"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <label class="form-label text-muted small mb-1">
                                            {{ __('tenant::settings.vat_id') }}
                                        </label>
                                        <p class="fw-semibold mb-0">{{ $organization->vatid ?? '' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection