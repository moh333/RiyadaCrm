@extends('tenant::layout')

@section('title', isset($tax) ? __('tenant::settings.edit_tax') : __('tenant::settings.add_tax'))

@section('content')
    <div class="container-fluid py-4">
        {{-- Page Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-2 fw-bold">
                    <i class="bi bi-percent text-primary me-2"></i>
                    {{ isset($tax) ? __('tenant::settings.edit_tax') : __('tenant::settings.add_tax') }}
                </h1>
                <p class="text-muted mb-0">
                    {{ $type === 'shipping' ? __('tenant::settings.shipping_handling_tax') : __('tenant::settings.product_service_tax') }}
                </p>
            </div>
            <div>
                <a href="{{ route('tenant.settings.crm.tax.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="bi bi-arrow-left me-2"></i>{{ __('tenant::settings.back') }}
                </a>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Error!</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="fw-bold mb-0">{{ __('tenant::settings.tax_information') }}</h5>
                    </div>
                    <div class="card-body p-4">
                        <form
                            action="{{ isset($tax) ? route('tenant.settings.crm.tax.update', $tax->taxid) : route('tenant.settings.crm.tax.store') }}"
                            method="POST" id="taxForm">
                            @csrf
                            @if (isset($tax))
                                @method('PUT')
                            @endif
                            <input type="hidden" name="type" value="{{ $type }}">

                            <div class="row g-3">
                                {{-- Tax Label --}}
                                <div class="col-md-6">
                                    <label for="taxlabel" class="form-label fw-semibold">
                                        {{ __('tenant::settings.tax_label') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('taxlabel') is-invalid @enderror"
                                        id="taxlabel" name="taxlabel" value="{{ old('taxlabel', $tax->taxlabel ?? '') }}"
                                        required>
                                    <div class="form-text">
                                        <i class="bi bi-info-circle me-1"></i>
                                        e.g., "VAT 15%", "Sales Tax", "GST"
                                    </div>
                                    @error('taxlabel')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Tax Percentage --}}
                                <div class="col-md-6">
                                    <label for="percentage" class="form-label fw-semibold">
                                        {{ __('tenant::settings.tax_percentage') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="number" step="0.001"
                                            class="form-control @error('percentage') is-invalid @enderror" id="percentage"
                                            name="percentage" value="{{ old('percentage', $tax->percentage ?? '') }}"
                                            min="0" max="100" required>
                                        <span class="input-group-text">%</span>
                                        @error('percentage')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-text">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Enter tax rate (e.g., 15.000 for 15%)
                                    </div>
                                </div>

                                {{-- Tax Status (only for edit) --}}
                                @if (isset($tax))
                                    <div class="col-12">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="active" name="active" value="1"
                                                {{ old('active', $tax->deleted == 0) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-semibold" for="active">
                                                {{ __('tenant::settings.active') }}
                                            </label>
                                        </div>
                                        <div class="form-text">
                                            <i class="bi bi-info-circle me-1"></i>
                                            Inactive taxes won't be available for new records
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <a href="{{ route('tenant.settings.crm.tax.index') }}"
                                    class="btn btn-outline-secondary rounded-pill px-4">
                                    {{ __('tenant::settings.cancel') }}
                                </a>
                                <button type="submit" class="btn btn-primary rounded-pill px-4">
                                    <i class="bi bi-check-lg me-2"></i>{{ __('tenant::settings.save') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Tips Sidebar --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 bg-light">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3">
                            <i class="bi bi-lightbulb text-warning me-2"></i>{{ __('tenant::settings.tips') }}
                        </h6>
                        <ul class="list-unstyled small">
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                Use descriptive tax labels (e.g., "VAT 15%" instead of just "VAT")
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                Tax percentages support up to 3 decimal places
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                Product taxes apply to line items in quotes, orders, and invoices
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                Shipping taxes apply only to shipping charges
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                                Deleting a tax soft-deletes it - existing records keep their data
                            </li>
                        </ul>

                        @if ($type === 'product')
                            <div class="alert alert-info mt-3 mb-0">
                                <strong>Product Tax</strong>
                                <p class="small mb-0 mt-1">This tax will be available for Products and Services in
                                    inventory modules.</p>
                            </div>
                        @else
                            <div class="alert alert-info mt-3 mb-0">
                                <strong>Shipping Tax</strong>
                                <p class="small mb-0 mt-1">This tax will be applied to shipping and handling charges.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            // Validate tax label uniqueness
            $('#taxlabel').on('blur', function () {
                const label = $(this).val();
                const excludeId = '{{ $tax->taxid ?? '' }}';
                const type = '{{ $type }}';

                if (label) {
                    $.ajax({
                        url: '{{ route('tenant.settings.crm.tax.check-duplicate') }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            label: label,
                            exclude_id: excludeId,
                            type: type
                        },
                        success: function (response) {
                            if (response.exists) {
                                $('#taxlabel').addClass('is-invalid');
                                if (!$('#taxlabel').next('.invalid-feedback').length) {
                                    $('#taxlabel').after(
                                        '<div class="invalid-feedback">{{ __('tenant::settings.duplicate_tax_label') }}</div>'
                                    );
                                }
                            } else {
                                $('#taxlabel').removeClass('is-invalid');
                                $('#taxlabel').next('.invalid-feedback').remove();
                            }
                        }
                    });
                }
            });

            // Form validation
            $('#taxForm').on('submit', function (e) {
                const percentage = parseFloat($('#percentage').val());
                if (percentage < 0 || percentage > 100) {
                    e.preventDefault();
                    alert('Tax percentage must be between 0 and 100');
                    return false;
                }
            });
        });
    </script>
@endpush