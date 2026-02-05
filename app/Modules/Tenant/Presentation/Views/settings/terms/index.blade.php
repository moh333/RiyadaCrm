@extends('tenant::layout')

@section('title', __('tenant::settings.terms_conditions'))

@section('content')
    <div class="container-fluid py-4">
        {{-- Page Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-2 fw-bold">
                    <i class="bi bi-file-text text-primary me-2"></i>
                    {{ __('tenant::settings.terms_conditions') }}
                </h1>
                <p class="text-muted mb-0">{{ __('tenant::settings.terms_conditions_description') }}</p>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row g-4">
            {{-- Quotes Terms --}}
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-gradient bg-primary text-white border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold mb-0">
                                <i class="bi bi-file-earmark-text me-2"></i>{{ __('tenant::settings.quotes_terms') }}
                            </h5>
                            <a href="{{ route('tenant.settings.crm.terms.edit', 'Quotes') }}"
                                class="btn btn-light btn-sm rounded-pill">
                                <i class="bi bi-pencil me-1"></i>{{ __('tenant::settings.edit') }}
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="terms-preview bg-light p-3 rounded-3"
                            style="min-height: 150px; max-height: 200px; overflow-y: auto;">
                            @if(isset($termsMap['Quotes']) && !empty($termsMap['Quotes']))
                                {!! nl2br(e($termsMap['Quotes'])) !!}
                            @else
                                <p class="small text-muted mb-0">No terms and conditions set for Quotes.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sales Order Terms --}}
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-gradient bg-success text-white border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold mb-0">
                                <i class="bi bi-cart-check me-2"></i>{{ __('tenant::settings.salesorder_terms') }}
                            </h5>
                            <a href="{{ route('tenant.settings.crm.terms.edit', 'SalesOrder') }}"
                                class="btn btn-light btn-sm rounded-pill">
                                <i class="bi bi-pencil me-1"></i>{{ __('tenant::settings.edit') }}
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="terms-preview bg-light p-3 rounded-3"
                            style="min-height: 150px; max-height: 200px; overflow-y: auto;">
                            @if(isset($termsMap['SalesOrder']) && !empty($termsMap['SalesOrder']))
                                {!! nl2br(e($termsMap['SalesOrder'])) !!}
                            @else
                                <p class="small text-muted mb-0">No terms and conditions set for Sales Orders.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Purchase Order Terms --}}
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-gradient bg-warning text-dark border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold mb-0">
                                <i class="bi bi-bag-check me-2"></i>{{ __('tenant::settings.purchaseorder_terms') }}
                            </h5>
                            <a href="{{ route('tenant.settings.crm.terms.edit', 'PurchaseOrder') }}"
                                class="btn btn-light btn-sm rounded-pill">
                                <i class="bi bi-pencil me-1"></i>{{ __('tenant::settings.edit') }}
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="terms-preview bg-light p-3 rounded-3"
                            style="min-height: 150px; max-height: 200px; overflow-y: auto;">
                            @if(isset($termsMap['PurchaseOrder']) && !empty($termsMap['PurchaseOrder']))
                                {!! nl2br(e($termsMap['PurchaseOrder'])) !!}
                            @else
                                <p class="small text-muted mb-0">No terms and conditions set for Purchase Orders.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Invoice Terms --}}
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-gradient bg-info text-white border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold mb-0">
                                <i class="bi bi-receipt me-2"></i>{{ __('tenant::settings.invoice_terms') }}
                            </h5>
                            <a href="{{ route('tenant.settings.crm.terms.edit', 'Invoice') }}"
                                class="btn btn-light btn-sm rounded-pill">
                                <i class="bi bi-pencil me-1"></i>{{ __('tenant::settings.edit') }}
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="terms-preview bg-light p-3 rounded-3"
                            style="min-height: 150px; max-height: 200px; overflow-y: auto;">
                            @if(isset($termsMap['Invoice']) && !empty($termsMap['Invoice']))
                                {!! nl2br(e($termsMap['Invoice'])) !!}
                            @else
                                <p class="small text-muted mb-0">No terms and conditions set for Invoices.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Info Card --}}
        <div class="card border-0 shadow-sm rounded-4 bg-light mt-4">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3">
                    <i class="bi bi-info-circle text-primary me-2"></i>About Terms & Conditions
                </h6>
                <ul class="list-unstyled small mb-0">
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Terms and conditions are displayed in PDF exports and print views
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Each inventory module can have its own specific terms
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Terms appear at the bottom of generated documents
                    </li>
                    <li class="mb-0">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        You can use rich text formatting for better presentation
                    </li>
                </ul>
            </div>
        </div>
    </div>
@endsection