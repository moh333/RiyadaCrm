@extends('tenant::layout')

@section('title', __('tenant::settings.edit') . ' - ' . __('tenant::settings.config_editor'))

@section('content')
    <div class="container-fluid py-4">
        {{-- Page Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-2 fw-bold">
                    <i class="bi bi-pencil text-primary me-2"></i>
                    {{ __('tenant::settings.edit') }} {{ __('tenant::settings.config_editor') }}
                </h1>
                <p class="text-muted mb-0">{{ __('tenant::settings.config_editor_description') }}</p>
            </div>
            <div>
                <a href="{{ route('tenant.settings.crm.config.index') }}"
                    class="btn btn-outline-secondary rounded-pill px-4">
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

        {{-- Warning Alert --}}
        <div class="alert alert-warning alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-start">
                <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                <div>
                    <strong>{{ __('tenant::settings.config_warning') }}</strong>
                    <p class="mb-0 mt-1 small">
                        Incorrect configuration values may cause system errors. Make sure you understand each setting
                        before making changes.
                    </p>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>

        <form action="{{ route('tenant.settings.crm.config.save') }}" method="POST">
            @csrf

            <div class="row g-4">
                {{-- Left Column --}}
                <div class="col-lg-6">
                    {{-- General Settings --}}
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="fw-bold mb-0">
                                <i class="bi bi-gear text-primary me-2"></i>
                                {{ __('tenant::settings.general_settings') }}
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                {{-- Default Module --}}
                                <div class="col-12">
                                    <label for="default_module" class="form-label fw-semibold">
                                        {{ __('tenant::settings.default_module') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('default_module') is-invalid @enderror"
                                        id="default_module" name="default_module" required>
                                        <option value="Dashboard" {{ ($config['default_module'] ?? 'Dashboard') == 'Dashboard' ? 'selected' : '' }}>Dashboard</option>
                                        <option value="Contacts" {{ ($config['default_module'] ?? '') == 'Contacts' ? 'selected' : '' }}>Contacts</option>
                                        <option value="Leads" {{ ($config['default_module'] ?? '') == 'Leads' ? 'selected' : '' }}>Leads</option>
                                        <option value="Accounts" {{ ($config['default_module'] ?? '') == 'Accounts' ? 'selected' : '' }}>Accounts</option>
                                        <option value="HelpDesk" {{ ($config['default_module'] ?? '') == 'HelpDesk' ? 'selected' : '' }}>HelpDesk</option>
                                    </select>
                                    <div class="form-text">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Module to display after login
                                    </div>
                                    @error('default_module')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Max Entries Per Page --}}
                                <div class="col-md-6">
                                    <label for="max_entries_per_page" class="form-label fw-semibold">
                                        {{ __('tenant::settings.max_entries_per_page') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="number"
                                        class="form-control @error('max_entries_per_page') is-invalid @enderror"
                                        id="max_entries_per_page" name="max_entries_per_page"
                                        value="{{ $config['max_entries_per_page'] ?? '20' }}" min="5" max="100" required>
                                    @error('max_entries_per_page')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Max Text Length --}}
                                <div class="col-md-6">
                                    <label for="max_text_length" class="form-label fw-semibold">
                                        {{ __('tenant::settings.max_text_length_listview') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" class="form-control @error('max_text_length') is-invalid @enderror"
                                        id="max_text_length" name="max_text_length"
                                        value="{{ $config['max_text_length'] ?? '50' }}" min="10" max="200" required>
                                    @error('max_text_length')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Upload Settings --}}
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="fw-bold mb-0">
                                <i class="bi bi-cloud-upload text-primary me-2"></i>
                                {{ __('tenant::settings.upload_settings') }}
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                {{-- Max Upload Size --}}
                                <div class="col-12">
                                    <label for="max_upload_size" class="form-label fw-semibold">
                                        {{ __('tenant::settings.max_upload_size') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="number"
                                            class="form-control @error('max_upload_size') is-invalid @enderror"
                                            id="max_upload_size" name="max_upload_size"
                                            value="{{ $config['max_upload_size'] ?? '5' }}" min="1" max="100" required>
                                        <span class="input-group-text">MB</span>
                                        @error('max_upload_size')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-text">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Maximum file size for uploads
                                    </div>
                                </div>

                                {{-- Allowed File Types --}}
                                <div class="col-12">
                                    <label for="allowed_file_types" class="form-label fw-semibold">
                                        Allowed File Types
                                    </label>
                                    <input type="text" class="form-control" id="allowed_file_types"
                                        name="allowed_file_types"
                                        value="{{ $config['allowed_file_types'] ?? 'pdf,doc,docx,xls,xlsx,jpg,png,gif' }}">
                                    <div class="form-text">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Comma-separated file extensions
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right Column --}}
                <div class="col-lg-6">
                    {{-- Helpdesk Settings --}}
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="fw-bold mb-0">
                                <i class="bi bi-headset text-primary me-2"></i>
                                {{ __('tenant::settings.helpdesk_settings') }}
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                {{-- Support Email --}}
                                <div class="col-12">
                                    <label for="helpdesk_support_email" class="form-label fw-semibold">
                                        {{ __('tenant::settings.helpdesk_support_email') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="email"
                                        class="form-control @error('helpdesk_support_email') is-invalid @enderror"
                                        id="helpdesk_support_email" name="helpdesk_support_email"
                                        value="{{ $config['helpdesk_support_email'] ?? 'support@example.com' }}" required>
                                    @error('helpdesk_support_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Support Name --}}
                                <div class="col-12">
                                    <label for="helpdesk_support_name" class="form-label fw-semibold">
                                        {{ __('tenant::settings.helpdesk_support_name') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                        class="form-control @error('helpdesk_support_name') is-invalid @enderror"
                                        id="helpdesk_support_name" name="helpdesk_support_name"
                                        value="{{ $config['helpdesk_support_name'] ?? 'Support Team' }}" required>
                                    @error('helpdesk_support_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- List View Settings --}}
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
                                    <label class="form-label fw-semibold mb-3">{{ __('tenant::settings.display_options') }}</label>

                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="show_icons" name="show_icons"
                                            value="1" {{ ($config['show_icons'] ?? '1') == '1' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="show_icons">
                                            {{ __('tenant::settings.show_listview_icons') }}
                                        </label>
                                    </div>

                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="show_colors" name="show_colors"
                                            value="1" {{ ($config['show_colors'] ?? '1') == '1' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="show_colors">
                                                {{ __('tenant::settings.enable_status_colors') }}
                                        </label>
                                    </div>

                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="compact_view"
                                            name="compact_view" value="1" {{ ($config['compact_view'] ?? '0') == '1' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="compact_view">
                                                {{ __('tenant::settings.compact_view_mode') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Save Button --}}
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="mb-0 text-muted">
                                        <i class="bi bi-info-circle me-2"></i>
                                        Changes will take effect immediately after saving
                                    </p>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('tenant.settings.crm.config.index') }}"
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
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        // Form validation
        document.querySelector('form').addEventListener('submit', function (e) {
            const maxUpload = parseInt(document.getElementById('max_upload_size').value);
            const maxEntries = parseInt(document.getElementById('max_entries_per_page').value);

            if (maxUpload > 100) {
                e.preventDefault();
                alert('{{ __('tenant::settings.invalid_number') }}: Maximum upload size cannot exceed 100MB');
                return false;
            }

            if (maxEntries < 5 || maxEntries > 100) {
                e.preventDefault();
                alert('{{ __('tenant::settings.invalid_number') }}: Entries per page must be between 5 and 100');
                return false;
            }
        });
    </script>
@endpush