@extends('tenant::layout')

@section('title', __('tenant::settings.edit_terms') . ' - ' . $module)

@section('content')
    <div class="container-fluid py-4">
        {{-- Page Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-2 fw-bold">
                    <i class="bi bi-file-text text-primary me-2"></i>
                    {{ __('tenant::settings.edit_terms') }} - {{ $module }}
                </h1>
                <p class="text-muted mb-0">{{ __('tenant::settings.module_terms') }}</p>
            </div>
            <div>
                <a href="{{ route('tenant.settings.crm.terms.index') }}"
                    class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="bi bi-arrow-left me-2"></i>{{ __('tenant::settings.back') }}
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

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
                        <h5 class="fw-bold mb-0">{{ __('tenant::settings.terms_text') }}</h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('tenant.settings.crm.terms.save') }}" method="POST">
                            @csrf
                            <input type="hidden" name="module" value="{{ $module }}">

                            <div class="mb-3">
                                <label for="terms" class="form-label fw-semibold">
                                    {{ __('tenant::settings.terms_conditions') }}
                                </label>
                                <textarea class="form-control @error('terms') is-invalid @enderror" id="terms" name="terms"
                                    rows="15"
                                    placeholder="Enter terms and conditions for {{ $module }}...">{{ old('terms', $terms ?? '') }}</textarea>
                                <div class="form-text">
                                    <i class="bi bi-info-circle me-1"></i>
                                    These terms will appear at the bottom of {{ $module }} documents
                                </div>
                                @error('terms')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('tenant.settings.crm.terms.index') }}"
                                    class="btn btn-outline-secondary rounded-pill px-4">
                                    {{ __('tenant::settings.cancel') }}
                                </a>
                                <button type="submit" class="btn btn-primary rounded-pill px-4">
                                    <i class="bi bi-check-lg me-2"></i>{{ __('tenant::settings.save_changes') }}
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
                                Keep terms clear and concise
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                Review terms with legal counsel
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                Update terms regularly as policies change
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                Terms appear in PDF exports and print views
                            </li>
                        </ul>

                        <div class="alert alert-info mt-3 mb-0">
                            <strong>Module: {{ $module }}</strong>
                            <p class="small mb-0 mt-1">
                                @if ($module === 'Quotes')
                                    Include quote validity period, pricing terms, and acceptance conditions.
                                @elseif($module === 'SalesOrder')
                                    Include delivery terms, payment schedule, and cancellation policy.
                                @elseif($module === 'PurchaseOrder')
                                    Include supplier terms, delivery expectations, and quality requirements.
                                @elseif($module === 'Invoice')
                                    Include payment terms, late fees, and dispute resolution process.
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Common Terms Examples --}}
                <div class="card border-0 shadow-sm rounded-4 mt-3">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3">
                            <i class="bi bi-clipboard-check text-primary me-2"></i>Common Terms
                        </h6>
                        <div class="accordion accordion-flush" id="examplesAccordion">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed small" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#payment">
                                        Payment Terms
                                    </button>
                                </h2>
                                <div id="payment" class="accordion-collapse collapse" data-bs-parent="#examplesAccordion">
                                    <div class="accordion-body small">
                                        Payment due within 30 days. Late payments subject to 1.5% monthly interest.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed small" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#delivery">
                                        Delivery Terms
                                    </button>
                                </h2>
                                <div id="delivery" class="accordion-collapse collapse" data-bs-parent="#examplesAccordion">
                                    <div class="accordion-body small">
                                        Delivery within 7-10 business days. Shipping costs are additional.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed small" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#warranty">
                                        Warranty
                                    </button>
                                </h2>
                                <div id="warranty" class="accordion-collapse collapse" data-bs-parent="#examplesAccordion">
                                    <div class="accordion-body small">
                                        All products covered by manufacturer's warranty. Returns accepted within 14 days.
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