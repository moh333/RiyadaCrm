@extends('tenant::layout')

@section('title', __('tenant::settings.config_editor'))

@section('content')
    <div class="container-fluid py-4">
        {{-- Page Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-2 fw-bold">
                    <i class="bi bi-code-square text-primary me-2"></i>
                    {{ __('tenant::settings.config_editor') }}
                </h1>
                <p class="text-muted mb-0">{{ __('tenant::settings.config_editor_description') }}</p>
            </div>
            <div>
                <a href="{{ route('tenant.settings.crm.config.edit') }}" class="btn btn-primary rounded-pill px-4">
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

        {{-- Warning Alert --}}
        <div class="alert alert-warning alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>{{ __('tenant::settings.config_warning') }}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>

        <div class="row g-4">
            {{-- General Settings --}}
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="fw-bold mb-0">
                            <i class="bi bi-gear text-primary me-2"></i>
                            {{ __('tenant::settings.general_settings') }}
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded-3">
                                    <div>
                                        <label class="form-label text-muted small mb-1">
                                            {{ __('tenant::settings.default_module') }}
                                        </label>
                                        <p class="fw-semibold mb-0">{{ $config['default_module'] ?? 'Dashboard' }}</p>
                                    </div>
                                    <i class="bi bi-grid-3x3-gap fs-4 text-muted"></i>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded-3">
                                    <div>
                                        <label class="form-label text-muted small mb-1">
                                            {{ __('tenant::settings.max_entries_per_page') }}
                                        </label>
                                        <p class="fw-semibold mb-0">{{ $config['max_entries_per_page'] ?? '20' }}</p>
                                    </div>
                                    <i class="bi bi-list-ol fs-4 text-muted"></i>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded-3">
                                    <div>
                                        <label class="form-label text-muted small mb-1">
                                            {{ __('tenant::settings.max_text_length_listview') }}
                                        </label>
                                        <p class="fw-semibold mb-0">{{ $config['max_text_length_listview'] ?? '50' }}
                                            characters</p>
                                    </div>
                                    <i class="bi bi-text-paragraph fs-4 text-muted"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Upload Settings --}}
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="fw-bold mb-0">
                            <i class="bi bi-cloud-upload text-primary me-2"></i>
                            {{ __('tenant::settings.upload_settings') }}
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded-3">
                                    <div>
                                        <label class="form-label text-muted small mb-1">
                                            {{ __('tenant::settings.max_upload_size') }}
                                        </label>
                                        <p class="fw-semibold mb-0">{{ $config['max_upload_size'] ?? '5' }} MB</p>
                                    </div>
                                    <i class="bi bi-file-earmark-arrow-up fs-4 text-muted"></i>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="p-3 bg-light rounded-3">
                                    <label class="form-label text-muted small mb-2">
                                        Allowed File Types
                                    </label>
                                    <div class="d-flex flex-wrap gap-2">
                                        <span class="badge bg-primary-subtle text-primary">PDF</span>
                                        <span class="badge bg-primary-subtle text-primary">DOC</span>
                                        <span class="badge bg-primary-subtle text-primary">DOCX</span>
                                        <span class="badge bg-primary-subtle text-primary">XLS</span>
                                        <span class="badge bg-primary-subtle text-primary">XLSX</span>
                                        <span class="badge bg-primary-subtle text-primary">JPG</span>
                                        <span class="badge bg-primary-subtle text-primary">PNG</span>
                                        <span class="badge bg-primary-subtle text-primary">GIF</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Helpdesk Settings --}}
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="fw-bold mb-0">
                            <i class="bi bi-headset text-primary me-2"></i>
                            {{ __('tenant::settings.helpdesk_settings') }}
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded-3">
                                    <div class="flex-grow-1">
                                        <label class="form-label text-muted small mb-1">
                                            {{ __('tenant::settings.helpdesk_support_email') }}
                                        </label>
                                        <p class="fw-semibold mb-0">{{ $config['helpdesk_support_email'] ??
                                            'support@example.com' }}</p>
                                    </div>
                                    <i class="bi bi-envelope fs-4 text-muted"></i>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded-3">
                                    <div class="flex-grow-1">
                                        <label class="form-label text-muted small mb-1">
                                            {{ __('tenant::settings.helpdesk_support_name') }}
                                        </label>
                                        <p class="fw-semibold mb-0">{{ $config['helpdesk_support_name'] ?? 'Support Team' }}
                                        </p>
                                    </div>
                                    <i class="bi bi-person-badge fs-4 text-muted"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- List View Settings --}}
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="fw-bold mb-0">
                            <i class="bi bi-table text-primary me-2"></i>
                            {{ __('tenant::settings.listview_settings') }}
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="p-3 bg-light rounded-3">
                                    <label class="form-label text-muted small mb-2">
                                        {{ __('tenant::settings.display_options') }}
                                    </label>
                                    <div class="d-flex flex-column gap-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="show_icons"
                                                {{ ($config['show_icons'] ?? '1') == '1' ? 'checked' : '' }} disabled>
                                            <label class="form-check-label" for="show_icons">
                                                {{ __('tenant::settings.show_module_icons') }}
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="show_colors"
                                                {{ ($config['show_colors'] ?? '1') == '1' ? 'checked' : '' }} disabled>
                                            <label class="form-check-label" for="show_colors">
                                                {{ __('tenant::settings.enable_color_coding') }}
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="compact_view"
                                                {{ ($config['compact_view'] ?? '0') == '1' ? 'checked' : '' }} disabled>
                                            <label class="form-check-label" for="compact_view">
                                                {{ __('tenant::settings.compact_view_short') }}
                                            </label>
                                        </div>
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