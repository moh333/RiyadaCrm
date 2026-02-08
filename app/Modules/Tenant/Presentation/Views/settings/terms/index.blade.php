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
            {{-- Module Selection and Terms Form --}}
            <div class="col-lg-12">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 py-3">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <label class="form-label fw-bold mb-0">{{ __('tenant::settings.select_module') }}</label>
                                <select id="moduleSelector" class="form-select rounded-pill px-3 mt-1">
                                    @forelse ($modules as $module)
                                        <option value="{{ $module['name'] }}">{{ $module['label'] }}</option>
                                    @empty
                                        <option value="">No Active Modules</option>
                                    @endforelse
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        @if($modules->isNotEmpty())
                            <form id="termsForm" action="{{ route('tenant.settings.crm.terms.save') }}" method="POST">
                                @csrf
                                <input type="hidden" name="module_name" id="moduleNameInput">

                                <div class="row g-4">
                                    {{-- English Terms --}}
                                    <div class="col-md-6">
                                        <label for="terms_en" class="form-label fw-semibold">
                                            <i
                                                class="bi bi-translate me-2 text-primary"></i>{{ __('tenant::settings.terms_en') }}
                                        </label>
                                        <textarea class="form-control" id="terms_en" name="terms_en" rows="15"
                                            placeholder="Enter English terms and conditions..."></textarea>
                                    </div>

                                    {{-- Arabic Terms --}}
                                    <div class="col-md-6">
                                        <label for="terms_ar" class="form-label fw-semibold">
                                            <i
                                                class="bi bi-translate me-2 text-primary"></i>{{ __('tenant::settings.terms_ar') }}
                                        </label>
                                        <textarea class="form-control text-end" id="terms_ar" name="terms_ar" rows="15"
                                            dir="rtl" placeholder="أدخل الشروط والأحكام باللغة العربية..."></textarea>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end mt-4">
                                    <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow-sm">
                                        <i class="bi bi-save me-2"></i>{{ __('tenant::settings.save_changes') }}
                                    </button>
                                </div>
                            </form>
                        @else
                            <div class="text-center py-5">
                                <i class="bi bi-exclamation-circle text-warning fs-1"></i>
                                <p class="mt-3 text-muted">No active Inventory modules found. Please enable modules in Module
                                    Management.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            const termsData = @json($termsMap);

            function loadModuleTerms(module) {
                const data = termsData[module] || { terms_en: '', terms_ar: '' };
                $('#moduleNameInput').val(module);
                $('#terms_en').val(data.terms_en || '');
                $('#terms_ar').val(data.terms_ar || '');
            }

            $(document).ready(function () {
                $('#moduleSelector').on('change', function () {
                    loadModuleTerms($(this).val());
                });

                // Initial load
                loadModuleTerms($('#moduleSelector').val());
            });
        </script>
    @endpush
@endsection