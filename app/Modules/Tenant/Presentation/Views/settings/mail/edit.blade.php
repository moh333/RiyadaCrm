@extends('tenant::layout')

@section('title', __('tenant::settings.edit') . ' - ' . __('tenant::settings.outgoing_server'))

@section('content')
    <div class="container-fluid py-4">
        {{-- Page Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-2 fw-bold">
                    <i class="bi bi-pencil text-primary me-2"></i>
                    {{ __('tenant::settings.edit') }} {{ __('tenant::settings.outgoing_server') }}
                </h1>
                <p class="text-muted mb-0">{{ __('tenant::settings.mail_description') }}</p>
            </div>
            <div>
                <a href="{{ route('tenant.settings.crm.mail.index') }}"
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

        <form action="{{ route('tenant.settings.crm.mail.save') }}" method="POST">
            @csrf

            <div class="row g-4">
                {{-- SMTP Configuration Form --}}
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="fw-bold mb-0">
                                <i class="bi bi-server text-primary me-2"></i>
                                {{ __('tenant::settings.smtp_configuration') }}
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            {{-- Server Settings Section --}}
                            <div class="mb-4">
                                <h6 class="fw-bold text-muted mb-3">
                                    <i class="bi bi-hdd-network me-2"></i>
                                    {{ __('tenant::settings.server_settings') }}
                                </h6>
                                <div class="row g-3">
                                    {{-- SMTP Server --}}
                                    <div class="col-md-8">
                                        <label for="smtp_server" class="form-label fw-semibold">
                                            {{ __('tenant::settings.smtp_server') }}
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="text"
                                            class="form-control @error('smtp_server') is-invalid @enderror"
                                            id="smtp_server" name="smtp_server"
                                            value="{{ old('smtp_server', 'smtp.example.com') }}"
                                            placeholder="smtp.example.com" required>
                                        @error('smtp_server')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- SMTP Port --}}
                                    <div class="col-md-4">
                                        <label for="smtp_port" class="form-label fw-semibold">
                                            {{ __('tenant::settings.smtp_port') }}
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="number"
                                            class="form-control @error('smtp_port') is-invalid @enderror"
                                            id="smtp_port" name="smtp_port" value="{{ old('smtp_port', '587') }}"
                                            placeholder="587" min="1" max="65535" required>
                                        <div class="form-text">
                                            <i class="bi bi-info-circle me-1"></i>
                                            {{ __('tenant::settings.smtp_port_help') }}
                                        </div>
                                        @error('smtp_port')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Authentication Settings Section --}}
                            <div class="mb-4">
                                <h6 class="fw-bold text-muted mb-3">
                                    <i class="bi bi-shield-lock me-2"></i>
                                    {{ __('tenant::settings.authentication_settings') }}
                                </h6>
                                <div class="row g-3">
                                    {{-- Require Authentication --}}
                                    <div class="col-12">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="smtp_auth"
                                                name="smtp_auth" value="1"
                                                {{ old('smtp_auth', true) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-semibold" for="smtp_auth">
                                                {{ __('tenant::settings.require_authentication') }}
                                            </label>
                                        </div>
                                    </div>

                                    {{-- SMTP Username --}}
                                    <div class="col-md-6">
                                        <label for="smtp_username" class="form-label fw-semibold">
                                            {{ __('tenant::settings.smtp_username') }}
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="text"
                                            class="form-control @error('smtp_username') is-invalid @enderror"
                                            id="smtp_username" name="smtp_username"
                                            value="{{ old('smtp_username', 'user@example.com') }}"
                                            placeholder="user@example.com" required>
                                        @error('smtp_username')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- SMTP Password --}}
                                    <div class="col-md-6">
                                        <label for="smtp_password" class="form-label fw-semibold">
                                            {{ __('tenant::settings.smtp_password') }}
                                            <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <input type="password"
                                                class="form-control @error('smtp_password') is-invalid @enderror"
                                                id="smtp_password" name="smtp_password"
                                                value="{{ old('smtp_password') }}" placeholder="••••••••" required>
                                            <button class="btn btn-outline-secondary" type="button"
                                                id="togglePassword">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            @error('smtp_password')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    {{-- From Email --}}
                                    <div class="col-md-12">
                                        <label for="from_email" class="form-label fw-semibold">
                                            {{ __('tenant::settings.from_email') }}
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="email"
                                            class="form-control @error('from_email') is-invalid @enderror"
                                            id="from_email" name="from_email"
                                            value="{{ old('from_email', 'noreply@example.com') }}"
                                            placeholder="noreply@example.com" required>
                                        <div class="form-text">
                                            <i class="bi bi-info-circle me-1"></i>
                                            This email address will appear as the sender for outgoing emails
                                        </div>
                                        @error('from_email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-0 py-3">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('tenant.settings.crm.mail.index') }}"
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

                {{-- Help & Common Providers --}}
                <div class="col-lg-4">
                    {{-- Common Email Providers --}}
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-white border-0 py-3">
                            <h6 class="fw-bold mb-0">
                                <i class="bi bi-envelope-check text-primary me-2"></i>
                                Common Email Providers
                            </h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="accordion accordion-flush" id="providersAccordion">
                                {{-- Gmail --}}
                                <div class="accordion-item border-0">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed px-0" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#gmail">
                                            <i class="bi bi-google me-2 text-danger"></i>Gmail
                                        </button>
                                    </h2>
                                    <div id="gmail" class="accordion-collapse collapse"
                                        data-bs-parent="#providersAccordion">
                                        <div class="accordion-body px-0 small">
                                            <strong>Server:</strong> smtp.gmail.com<br>
                                            <strong>Port:</strong> 587 (TLS)<br>
                                            <strong>Note:</strong> Use App Password
                                        </div>
                                    </div>
                                </div>

                                {{-- Outlook --}}
                                <div class="accordion-item border-0">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed px-0" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#outlook">
                                            <i class="bi bi-microsoft me-2 text-primary"></i>Outlook
                                        </button>
                                    </h2>
                                    <div id="outlook" class="accordion-collapse collapse"
                                        data-bs-parent="#providersAccordion">
                                        <div class="accordion-body px-0 small">
                                            <strong>Server:</strong> smtp.office365.com<br>
                                            <strong>Port:</strong> 587 (TLS)
                                        </div>
                                    </div>
                                </div>

                                {{-- Yahoo --}}
                                <div class="accordion-item border-0">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed px-0" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#yahoo">
                                            <i class="bi bi-envelope me-2 text-purple"></i>Yahoo
                                        </button>
                                    </h2>
                                    <div id="yahoo" class="accordion-collapse collapse"
                                        data-bs-parent="#providersAccordion">
                                        <div class="accordion-body px-0 small">
                                            <strong>Server:</strong> smtp.mail.yahoo.com<br>
                                            <strong>Port:</strong> 587 (TLS)
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tips Card --}}
                    <div class="card border-0 shadow-sm rounded-4 bg-light">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-3">
                                <i class="bi bi-lightbulb text-warning me-2"></i>
                                {{ __('tenant::settings.tips') }}
                            </h6>
                            <ul class="list-unstyled mb-0 small">
                                <li class="mb-3">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    Always test your configuration after saving
                                </li>
                                <li class="mb-3">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    Use TLS (port 587) for better security
                                </li>
                                <li class="mb-3">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    Some providers require app-specific passwords
                                </li>
                                <li class="mb-0">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    Check your provider's SMTP documentation
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('smtp_password');
            const icon = this.querySelector('i');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });

        // Toggle authentication fields
        document.getElementById('smtp_auth').addEventListener('change', function() {
            const authFields = ['smtp_username', 'smtp_password'];
            authFields.forEach(field => {
                document.getElementById(field).disabled = !this.checked;
            });
        });
    </script>
@endpush
