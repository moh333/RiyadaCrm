@extends('tenant::layout')

@section('title', __('tenant::settings.outgoing_server'))

@section('content')
    <div class="container-fluid py-4">
        {{-- Page Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-2 fw-bold">
                    <i class="bi bi-envelope-at text-primary me-2"></i>
                    {{ __('tenant::settings.outgoing_server') }}
                </h1>
                <p class="text-muted mb-0">{{ __('tenant::settings.mail_description') }}</p>
            </div>
            <div>
                <a href="{{ route('tenant.settings.crm.mail.edit') }}" class="btn btn-primary rounded-pill px-4">
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

        <div class="row g-4">
            {{-- SMTP Configuration Display --}}
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="fw-bold mb-0">
                            <i class="bi bi-server text-primary me-2"></i>
                            {{ __('tenant::settings.smtp_configuration') }}
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            {{-- Server Settings --}}
                            <div class="col-12">
                                <h6 class="fw-bold text-muted mb-3">
                                    <i class="bi bi-hdd-network me-2"></i>
                                    {{ __('tenant::settings.server_settings') }}
                                </h6>
                            </div>

                            <div class="col-md-8">
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0">
                                        <i class="bi bi-server fs-5 text-muted me-3"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <label class="form-label text-muted small mb-1">
                                            {{ __('tenant::settings.smtp_server') }}
                                        </label>
                                        <p class="fw-semibold mb-0">smtp.example.com</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0">
                                        <i class="bi bi-hdd-network fs-5 text-muted me-3"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <label class="form-label text-muted small mb-1">
                                            {{ __('tenant::settings.smtp_port') }}
                                        </label>
                                        <p class="fw-semibold mb-0">587</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Authentication Settings --}}
                            <div class="col-12 mt-4">
                                <h6 class="fw-bold text-muted mb-3">
                                    <i class="bi bi-shield-lock me-2"></i>
                                    {{ __('tenant::settings.authentication_settings') }}
                                </h6>
                            </div>

                            <div class="col-md-6">
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0">
                                        <i class="bi bi-person fs-5 text-muted me-3"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <label class="form-label text-muted small mb-1">
                                            {{ __('tenant::settings.smtp_username') }}
                                        </label>
                                        <p class="fw-semibold mb-0">user@example.com</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0">
                                        <i class="bi bi-key fs-5 text-muted me-3"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <label class="form-label text-muted small mb-1">
                                            {{ __('tenant::settings.smtp_password') }}
                                        </label>
                                        <p class="fw-semibold mb-0">••••••••</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0">
                                        <i class="bi bi-envelope fs-5 text-muted me-3"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <label class="form-label text-muted small mb-1">
                                            {{ __('tenant::settings.from_email') }}
                                        </label>
                                        <p class="fw-semibold mb-0">noreply@example.com</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0">
                                        <i class="bi bi-shield-check fs-5 text-muted me-3"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <label class="form-label text-muted small mb-1">
                                            {{ __('tenant::settings.smtp_auth') }}
                                        </label>
                                        <p class="mb-0">
                                            <span class="badge bg-success-subtle text-success rounded-pill">
                                                <i class="bi bi-check-circle me-1"></i>
                                                {{ __('tenant::settings.enabled') }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Test Email & Status --}}
            <div class="col-lg-4">
                {{-- Connection Status --}}
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4 text-center">
                        <div class="mb-3">
                            <div class="bg-success-subtle rounded-circle d-inline-flex p-3">
                                <i class="bi bi-check-circle fs-1 text-success"></i>
                            </div>
                        </div>
                        <h5 class="fw-bold mb-2">{{ __('tenant::settings.status') }}</h5>
                        <p class="text-muted mb-0">SMTP server is configured</p>
                    </div>
                </div>

                {{-- Test Email Card --}}
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="fw-bold mb-0">
                            <i class="bi bi-send text-primary me-2"></i>
                            {{ __('tenant::settings.test_email') }}
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <p class="text-muted small mb-3">
                            Send a test email to verify your SMTP configuration is working correctly.
                        </p>
                        <form id="testEmailForm">
                            @csrf
                            <div class="mb-3">
                                <label for="test_email" class="form-label fw-semibold">
                                    {{ __('tenant::settings.test_email') }}
                                </label>
                                <input type="email" class="form-control" id="test_email" name="test_email"
                                    placeholder="test@example.com" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 rounded-pill">
                                <i class="bi bi-send me-2"></i>{{ __('tenant::settings.send_test_email') }}
                            </button>
                        </form>
                        <div id="testResult" class="mt-3"></div>
                    </div>
                </div>

                {{-- Help Card --}}
                <div class="card border-0 shadow-sm rounded-4 bg-light mt-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3">
                            <i class="bi bi-info-circle text-primary me-2"></i>
                            Common SMTP Ports
                        </h6>
                        <ul class="list-unstyled mb-0 small">
                            <li class="mb-2">
                                <span class="badge bg-secondary me-2">25</span>
                                Plain (Not recommended)
                            </li>
                            <li class="mb-2">
                                <span class="badge bg-primary me-2">587</span>
                                TLS (Recommended)
                            </li>
                            <li class="mb-0">
                                <span class="badge bg-success me-2">465</span>
                                SSL
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('testEmailForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const resultDiv = document.getElementById('testResult');
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;

            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';
            resultDiv.innerHTML = '';

            fetch('{{ route('tenant.settings.crm.mail.test') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    test_email: document.getElementById('test_email').value
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        resultDiv.innerHTML = `
                                <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
                                    <i class="bi bi-check-circle me-2"></i>${data.message}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            `;
                    } else {
                        resultDiv.innerHTML = `
                                <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
                                    <i class="bi bi-exclamation-triangle me-2"></i>${data.message}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            `;
                    }
                })
                .catch(error => {
                    resultDiv.innerHTML = `
                            <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
                                <i class="bi bi-exclamation-triangle me-2"></i>{{ __('tenant::settings.test_email_failed') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        `;
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                });
        });
    </script>
@endpush