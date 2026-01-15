@extends('tenant::layout')

@section('content')
    <div class="container-fluid py-4">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h5 class="fw-bold mb-0">{{ __('tenant::tenant.general_settings') }}</h5>
                    </div>
                    <div class="card-body p-4">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 mb-4" role="alert">
                                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form action="{{ route('tenant.settings.update') }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <label
                                    class="form-label small fw-bold text-muted text-uppercase">{{ __('tenant::tenant.company_name') }}</label>
                                <input type="text" name="company_name" class="form-control rounded-3 py-2"
                                    value="Riyada CRM Tenant" required>
                            </div>

                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <label
                                        class="form-label small fw-bold text-muted text-uppercase">{{ __('tenant::tenant.timezone') }}</label>
                                    <select name="timezone" class="form-select rounded-3 py-2">
                                        <option value="UTC">UTC</option>
                                        <option value="Asia/Riyadh" selected>Asia/Riyadh</option>
                                        <option value="Africa/Cairo">Africa/Cairo</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label
                                        class="form-label small fw-bold text-muted text-uppercase">{{ __('tenant::tenant.language') }}</label>
                                    <select name="default_language" class="form-select rounded-3 py-2">
                                        <option value="en" {{ app()->getLocale() == 'en' ? 'selected' : '' }}>English</option>
                                        <option value="ar" {{ app()->getLocale() == 'ar' ? 'selected' : '' }}>العربية</option>
                                    </select>
                                </div>
                            </div>

                            <hr class="my-4 text-muted">

                            <h6 class="fw-bold mb-4">{{ __('tenant::tenant.system_settings') }}</h6>

                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="emailNotifications" checked>
                                <label class="form-check-label ms-2" for="emailNotifications">Email Notifications</label>
                            </div>
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="autoBackup">
                                <label class="form-check-label ms-2" for="autoBackup">Automatic Daily Backup</label>
                            </div>

                            <div class="mt-5 text-{{ app()->getLocale() == 'ar' ? 'start' : 'end' }}">
                                <button type="submit" class="btn btn-primary px-5 py-2.5 rounded-pill shadow-sm fw-bold">
                                    {{ __('tenant::tenant.save_settings') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3">System Information</h6>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">PHP Version</span>
                            <span class="small fw-bold">{{ PHP_VERSION }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Laravel Version</span>
                            <span class="small fw-bold">{{ app()->version() }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Database</span>
                            <span class="small fw-bold">MySQL 8.0</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted small">Last Full Backup</span>
                            <span class="small fw-bold text-success">Never</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection