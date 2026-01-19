@extends('tenant::layout')

@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-0">
                    <i class="bi bi-123 text-primary me-2"></i>{{ $moduleDefinition->getName() }} - {{ __('tenant::tenant.module_numbering') }}
                </h3>
            </div>
            <a href="{{ route('tenant.settings.modules.numbering.selection') }}" class="btn btn-outline-secondary rounded-3">
                <i class="bi bi-arrow-left me-2"></i>{{ __('tenant::tenant.cancel') }}
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-8">
                <form action="{{ route('tenant.settings.modules.numbering.update', $moduleDefinition->getName()) }}" method="POST">
                    @csrf
                    
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-white border-bottom py-3 px-4">
                            <h5 class="mb-0 fw-bold">{{ __('tenant::tenant.module_numbering') }}</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">{{ __('tenant::tenant.prefix') }} <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           name="prefix" 
                                           class="form-control rounded-3" 
                                           value="{{ old('prefix', $numberingConfig->prefix ?? strtoupper(substr($moduleDefinition->getName(), 0, 3))) }}"
                                           placeholder="e.g., CON, ACC, LEA"
                                           required>
                                    <small class="text-muted">Prefix for auto-generated numbers</small>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">{{ __('tenant::tenant.start_sequence') }} <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           name="start_id" 
                                           class="form-control rounded-3" 
                                           value="{{ old('start_id', $numberingConfig->start_id ?? 1) }}"
                                           min="1"
                                           required>
                                    <small class="text-muted">First number in sequence</small>
                                </div>

                                <div class="col-12">
                                    <div class="alert alert-info rounded-3 border-0 bg-soft-primary text-primary">
                                        <i class="bi bi-info-circle me-2"></i>
                                        <strong>Preview:</strong> 
                                        <span id="preview-number" class="fw-bold">
                                            {{ old('prefix', $numberingConfig->prefix ?? 'CON') }}{{ old('start_id', $numberingConfig->start_id ?? 1) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="submit" class="btn btn-primary btn-lg rounded-3 px-5 shadow-sm">
                            <i class="bi bi-save me-2"></i>{{ __('tenant::tenant.save_settings') }}
                        </button>
                    </div>
                </form>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3">
                            <i class="bi bi-lightbulb me-2 text-warning"></i>Numbering Tips
                        </h6>
                        <ul class="list-unstyled small">
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                Keep prefixes short (2-4 characters)
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                Use uppercase for consistency
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                                Changing sequence won't affect existing records
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const prefixInput = document.querySelector('input[name="prefix"]');
            const curIdInput = document.querySelector('input[name="start_id"]');
            const preview = document.getElementById('preview-number');

            function updatePreview() {
                const prefix = prefixInput.value || 'XXX';
                const curId = curIdInput.value || '1';
                preview.textContent = prefix + curId;
            }

            prefixInput.addEventListener('input', updatePreview);
            curIdInput.addEventListener('input', updatePreview);
        });
    </script>

    <style>
        .bg-soft-primary {
            background-color: #eef2ff !important;
            color: #6366f1 !important;
        }
    </style>
@endsection