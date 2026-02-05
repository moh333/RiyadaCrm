@extends('tenant::layout')

@section('title', __('tenant::settings.edit') . ' - ' . __('tenant::settings.company_details'))

@section('content')
    <div class="container-fluid py-4">
        {{-- Page Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-2 fw-bold">
                    <i class="bi bi-pencil text-primary me-2"></i>
                    {{ __('tenant::settings.edit') }} {{ __('tenant::settings.company_details') }}
                </h1>
                <p class="text-muted mb-0">{{ __('tenant::settings.company_details_description') }}</p>
            </div>
            <div>
                <a href="{{ route('tenant.settings.crm.company.index') }}"
                    class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="bi bi-arrow-left me-2"></i>{{ __('tenant::settings.back') }}
                </a>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>{{ __('tenant::settings.error') }}</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form action="{{ route('tenant.settings.crm.company.update') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="row g-4">
                {{-- Logo Upload Section --}}
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="fw-bold mb-0">
                                <i class="bi bi-image text-primary me-2"></i>
                                {{ __('tenant::settings.logo') }}
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="text-center mb-3">
                                <img id="logoPreview"
                                    src="{{ $organization->logo ? tenant_asset($organization->logo) : global_asset('images/logo-placeholder.png') }}"
                                    alt="Logo Preview" class="img-fluid rounded-3 mb-3" style="max-height: 200px;">
                            </div>

                            <div class="mb-3">
                                <label for="logo" class="form-label fw-semibold">
                                    {{ __('tenant::settings.upload_logo') }}
                                </label>
                                <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                                <div class="form-text">
                                    <i class="bi bi-info-circle me-1"></i>
                                    {{ __('tenant::settings.logo_format_help') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Company Information Form --}}
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="fw-bold mb-0">
                                <i class="bi bi-building text-primary me-2"></i>
                                {{ __('tenant::settings.company_information') }}
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                {{-- Organization Name --}}
                                <div class="col-md-12">
                                    <label for="organizationname" class="form-label fw-semibold">
                                        {{ __('tenant::settings.organization_name') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('organizationname') is-invalid @enderror"
                                        id="organizationname" name="organizationname"
                                        value="{{ old('organizationname', $organization->organizationname ?? '') }}"
                                        required>
                                    @error('organizationname')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Address --}}
                                <div class="col-md-12">
                                    <label for="address" class="form-label fw-semibold">
                                        {{ __('tenant::settings.address') }}
                                    </label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" id="address"
                                        name="address"
                                        rows="2">{{ old('address', $organization->address ?? '') }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- City --}}
                                <div class="col-md-6">
                                    <label for="city" class="form-label fw-semibold">
                                        {{ __('tenant::settings.city') }}
                                    </label>
                                    <input type="text" class="form-control @error('city') is-invalid @enderror" id="city"
                                        name="city" value="{{ old('city', $organization->city ?? '') }}">
                                    @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- State --}}
                                <div class="col-md-6">
                                    <label for="state" class="form-label fw-semibold">
                                        {{ __('tenant::settings.state') }}
                                    </label>
                                    <input type="text" class="form-control @error('state') is-invalid @enderror" id="state"
                                        name="state" value="{{ old('state', $organization->state ?? '') }}">
                                    @error('state')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Postal Code --}}
                                <div class="col-md-6">
                                    <label for="code" class="form-label fw-semibold">
                                        {{ __('tenant::settings.postal_code') }}
                                    </label>
                                    <input type="text" class="form-control @error('code') is-invalid @enderror" id="code"
                                        name="code" value="{{ old('code', $organization->code ?? '') }}">
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Country --}}
                                <div class="col-md-6">
                                    <label for="country" class="form-label fw-semibold">
                                        {{ __('tenant::settings.country') }}
                                    </label>
                                    <input type="text" class="form-control @error('country') is-invalid @enderror"
                                        id="country" name="country"
                                        value="{{ old('country', $organization->country ?? '') }}">
                                    @error('country')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Phone --}}
                                <div class="col-md-6">
                                    <label for="phone" class="form-label fw-semibold">
                                        {{ __('tenant::settings.phone') }}
                                    </label>
                                    <input type="tel" class="form-control @error('phone') is-invalid @enderror" id="phone"
                                        name="phone" value="{{ old('phone', $organization->phone ?? '') }}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Fax --}}
                                <div class="col-md-6">
                                    <label for="fax" class="form-label fw-semibold">
                                        {{ __('tenant::settings.fax') }}
                                    </label>
                                    <input type="tel" class="form-control @error('fax') is-invalid @enderror" id="fax"
                                        name="fax" value="{{ old('fax', $organization->fax ?? '') }}">
                                    @error('fax')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Website --}}
                                <div class="col-md-6">
                                    <label for="website" class="form-label fw-semibold">
                                        {{ __('tenant::settings.website') }}
                                    </label>
                                    <input type="url" class="form-control @error('website') is-invalid @enderror"
                                        id="website" name="website"
                                        value="{{ old('website', $organization->website ?? '') }}">
                                    @error('website')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- VAT ID --}}
                                <div class="col-md-6">
                                    <label for="vatid" class="form-label fw-semibold">
                                        {{ __('tenant::settings.vat_id') }}
                                    </label>
                                    <input type="text" class="form-control @error('vatid') is-invalid @enderror" id="vatid"
                                        name="vatid" value="{{ old('vatid', $organization->vatid ?? '') }}">
                                    @error('vatid')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-0 py-3">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('tenant.settings.crm.company.index') }}"
                                    class="btn btn-outline-secondary rounded-pill px-4">
                                    {{ __('tenant::settings.cancel') }}
                                </a>
                                <button type="submit" class="btn btn-primary rounded-pill px-4">
                                    <i class="bi bi-check-lg me-2"></i>{{ __('tenant::settings.save_changes') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        // Logo preview
        document.getElementById('logo').addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('logoPreview').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
@endpush