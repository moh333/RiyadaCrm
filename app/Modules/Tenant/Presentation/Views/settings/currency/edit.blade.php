@extends('tenant::layout')

@section('title', isset($currency) ? __('tenant::settings.edit_currency') : __('tenant::settings.add_currency'))

@section('content')
    <div class="container-fluid py-4">
        {{-- Page Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-2 fw-bold">
                    <i class="bi bi-{{ isset($currency) ? 'pencil' : 'plus-lg' }} text-primary me-2"></i>
                    {{ isset($currency) ? __('tenant::settings.edit_currency') : __('tenant::settings.add_currency') }}
                </h1>
                <p class="text-muted mb-0">{{ __('tenant::settings.currencies_description') }}</p>
            </div>
            <div>
                <a href="{{ route('tenant.settings.crm.currency.index') }}"
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

        <form
            action="{{ isset($currency) ? route('tenant.settings.crm.currency.update', $currency->id ?? 1) : route('tenant.settings.crm.currency.store') }}"
            method="POST">
            @csrf
            @if (isset($currency))
                @method('PUT')
            @endif

            <div class="row g-4">
                {{-- Currency Information --}}
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="fw-bold mb-0">
                                <i class="bi bi-info-circle text-primary me-2"></i>
                                {{ __('tenant::settings.currency_information') }}
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                {{-- Currency Name --}}
                                <div class="col-md-6">
                                    <label for="currency_name" class="form-label fw-semibold">
                                        {{ __('tenant::settings.currency_name') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('currency_name') is-invalid @enderror"
                                        id="currency_name" name="currency_name"
                                        value="{{ old('currency_name', $currency->currency_name ?? '') }}"
                                        placeholder="US Dollar" required>
                                    @error('currency_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Currency Code --}}
                                <div class="col-md-6">
                                    <label for="currency_code" class="form-label fw-semibold">
                                        {{ __('tenant::settings.currency_code') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                        class="form-control text-uppercase @error('currency_code') is-invalid @enderror"
                                        id="currency_code" name="currency_code"
                                        value="{{ old('currency_code', $currency->currency_code ?? '') }}" placeholder="USD"
                                        maxlength="3" required>
                                    <div class="form-text">
                                        <i class="bi bi-info-circle me-1"></i>
                                        {{ __('tenant::settings.iso_code') }} (3 characters)
                                    </div>
                                    @error('currency_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Currency Symbol --}}
                                <div class="col-md-6">
                                    <label for="currency_symbol" class="form-label fw-semibold">
                                        {{ __('tenant::settings.currency_symbol') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('currency_symbol') is-invalid @enderror"
                                        id="currency_symbol" name="currency_symbol"
                                        value="{{ old('currency_symbol', $currency->currency_symbol ?? '') }}"
                                        placeholder="$" maxlength="5" required>
                                    @error('currency_symbol')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Conversion Rate --}}
                                <div class="col-md-6">
                                    <label for="conversion_rate" class="form-label fw-semibold">
                                        {{ __('tenant::settings.conversion_rate') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" class="form-control @error('conversion_rate') is-invalid @enderror"
                                        id="conversion_rate" name="conversion_rate"
                                        value="{{ old('conversion_rate', $currency->conversion_rate ?? '1.0000') }}"
                                        step="0.0001" min="0" placeholder="1.0000" required>
                                    <div class="form-text">
                                        <i class="bi bi-info-circle me-1"></i>
                                        {{ __('tenant::settings.conversion_rate_help') }}
                                    </div>
                                    @error('conversion_rate')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Currency Status --}}
                                <div class="col-md-12">
                                    <label for="currency_status" class="form-label fw-semibold">
                                        {{ __('tenant::settings.currency_status') }}
                                    </label>
                                    <select class="form-select @error('currency_status') is-invalid @enderror"
                                        id="currency_status" name="currency_status">
                                        <option value="Active" {{ old('currency_status', $currency->currency_status ?? 'Active') == 'Active' ? 'selected' : '' }}>
                                            {{ __('tenant::settings.active') }}
                                        </option>
                                        <option value="Inactive" {{ old('currency_status', $currency->currency_status ?? '') == 'Inactive' ? 'selected' : '' }}>
                                            {{ __('tenant::settings.inactive') }}
                                        </option>
                                    </select>
                                    @error('currency_status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-0 py-3">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('tenant.settings.crm.currency.index') }}"
                                    class="btn btn-outline-secondary rounded-pill px-4">
                                    {{ __('tenant::settings.cancel') }}
                                </a>
                                <button type="submit" class="btn btn-primary rounded-pill px-4">
                                    <i class="bi bi-check-lg me-2"></i>
                                    {{ isset($currency) ? __('tenant::settings.update') : __('tenant::settings.save') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Help & Tips --}}
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 bg-light">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-3">
                                <i class="bi bi-lightbulb text-warning me-2"></i>
                                {{ __('tenant::settings.tips') }}
                            </h5>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-3">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    <small>Use standard ISO 4217 currency codes (e.g., USD, EUR, GBP)</small>
                                </li>
                                <li class="mb-3">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    <small>Conversion rate is relative to your base currency</small>
                                </li>
                                <li class="mb-3">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    <small>Update conversion rates regularly for accuracy</small>
                                </li>
                                <li class="mb-0">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    <small>Inactive currencies won't appear in currency selection</small>
                                </li>
                            </ul>
                        </div>
                    </div>

                    {{-- Common Currencies Reference --}}
                    <div class="card border-0 shadow-sm rounded-4 mt-4">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-3">
                                <i class="bi bi-info-circle text-primary me-2"></i>
                                Common Currencies
                            </h6>
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th class="border-0">Code</th>
                                            <th class="border-0">Symbol</th>
                                            <th class="border-0">Name</th>
                                        </tr>
                                    </thead>
                                    <tbody class="small">
                                        <tr>
                                            <td>USD</td>
                                            <td>$</td>
                                            <td>US Dollar</td>
                                        </tr>
                                        <tr>
                                            <td>EUR</td>
                                            <td>€</td>
                                            <td>Euro</td>
                                        </tr>
                                        <tr>
                                            <td>GBP</td>
                                            <td>£</td>
                                            <td>British Pound</td>
                                        </tr>
                                        <tr>
                                            <td>SAR</td>
                                            <td>﷼</td>
                                            <td>Saudi Riyal</td>
                                        </tr>
                                        <tr>
                                            <td>AED</td>
                                            <td>د.إ</td>
                                            <td>UAE Dirham</td>
                                        </tr>
                                    </tbody>
                                </table>
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
        // Auto-uppercase currency code
        document.getElementById('currency_code').addEventListener('input', function (e) {
            this.value = this.value.toUpperCase();
        });
    </script>
@endpush