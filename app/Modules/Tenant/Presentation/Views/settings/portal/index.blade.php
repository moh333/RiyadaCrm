@extends('tenant::layout')

@section('title', __('tenant::settings.customer_portal'))

@section('content')
    <div class="container-fluid py-4">
        {{-- Page Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-2 fw-bold">
                    <i class="bi bi-person-circle text-primary me-2"></i>
                    {{ __('tenant::settings.customer_portal') }}
                </h1>
                <p class="text-muted mb-0">{{ __('tenant::settings.portal_description') }}</p>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form id="portalConfigForm" method="POST" action="{{ route('tenant.settings.crm.portal.save') }}">
            @csrf

            <div class="row g-4">
                {{-- Portal Settings --}}
                <div class="col-lg-8">
                    {{-- General Settings Card --}}
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="fw-bold mb-0">
                                <i class="bi bi-gear text-primary me-2"></i>
                                {{ __('tenant::settings.general_settings') }}
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                {{-- Portal URL --}}
                                <div class="col-md-12">
                                    <label for="portal_url" class="form-label fw-semibold">
                                        {{ __('tenant::settings.portal_url') }}
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">https://</span>
                                        <input type="text" class="form-control" id="portal_url" name="portal_url"
                                            value="{{ old('portal_url', 'portal.example.com') }}" readonly>
                                        <button class="btn btn-outline-secondary" type="button" id="copyUrl">
                                            <i class="bi bi-clipboard"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Share this URL with your customers to access the portal
                                    </div>
                                </div>

                                {{-- Default Assignee --}}
                                <div class="col-md-6">
                                    <label for="default_assignee" class="form-label fw-semibold">
                                        {{ __('tenant::settings.default_assignee') }}
                                    </label>
                                    <select class="form-select" id="default_assignee" name="default_assignee">
                                        <option value="">Select User...</option>
                                        <option value="1" selected>Admin User</option>
                                        <option value="2">Support Team</option>
                                    </select>
                                </div>

                                {{-- Support Notification --}}
                                <div class="col-md-6">
                                    <label for="support_notification" class="form-label fw-semibold">
                                        {{ __('tenant::settings.support_notification') }}
                                    </label>
                                    <input type="number" class="form-control" id="support_notification"
                                        name="support_notification" value="{{ old('support_notification', '7') }}" min="1"
                                        max="365">
                                    <div class="form-text">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Days before sending notification
                                    </div>
                                </div>

                                {{-- Announcement --}}
                                <div class="col-md-12">
                                    <label for="announcement" class="form-label fw-semibold">
                                        {{ __('tenant::settings.announcement') }}
                                    </label>
                                    <textarea class="form-control" id="announcement" name="announcement" rows="3"
                                        placeholder="Enter portal announcement message...">{{ old('announcement', 'Welcome to our Customer Portal!') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Module Access Card --}}
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="fw-bold mb-0">
                                <i class="bi bi-grid text-primary me-2"></i>
                                {{ __('tenant::settings.portal_modules') }}
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="px-4 py-3 fw-semibold">{{ __('tenant::settings.module') }}</th>
                                            <th class="px-4 py-3 fw-semibold text-center">
                                                {{ __('tenant::settings.module_visibility') }}
                                            </th>
                                            <th class="px-4 py-3 fw-semibold text-center">
                                                {{ __('tenant::settings.can_create') }}
                                            </th>
                                            <th class="px-4 py-3 fw-semibold text-center">
                                                {{ __('tenant::settings.can_edit') }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- HelpDesk --}}
                                        <tr>
                                            <td class="px-4 py-3">
                                                <i class="bi bi-headset text-primary me-2"></i>
                                                <strong>HelpDesk</strong>
                                            </td>
                                            <td class="text-center">
                                                <div class="form-check form-switch d-inline-block">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="modules[helpdesk][visible]" id="helpdesk_visible" checked>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="form-check form-switch d-inline-block">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="modules[helpdesk][create]" id="helpdesk_create" checked>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="form-check form-switch d-inline-block">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="modules[helpdesk][edit]" id="helpdesk_edit">
                                                </div>
                                            </td>
                                        </tr>

                                        {{-- Contacts --}}
                                        <tr>
                                            <td class="px-4 py-3">
                                                <i class="bi bi-people text-success me-2"></i>
                                                <strong>Contacts</strong>
                                            </td>
                                            <td class="text-center">
                                                <div class="form-check form-switch d-inline-block">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="modules[contacts][visible]" id="contacts_visible" checked>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="form-check form-switch d-inline-block">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="modules[contacts][create]" id="contacts_create">
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="form-check form-switch d-inline-block">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="modules[contacts][edit]" id="contacts_edit" checked>
                                                </div>
                                            </td>
                                        </tr>

                                        {{-- Documents --}}
                                        <tr>
                                            <td class="px-4 py-3">
                                                <i class="bi bi-file-earmark-text text-info me-2"></i>
                                                <strong>Documents</strong>
                                            </td>
                                            <td class="text-center">
                                                <div class="form-check form-switch d-inline-block">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="modules[documents][visible]" id="documents_visible" checked>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="form-check form-switch d-inline-block">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="modules[documents][create]" id="documents_create">
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="form-check form-switch d-inline-block">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="modules[documents][edit]" id="documents_edit">
                                                </div>
                                            </td>
                                        </tr>

                                        {{-- Invoices --}}
                                        <tr>
                                            <td class="px-4 py-3">
                                                <i class="bi bi-receipt text-warning me-2"></i>
                                                <strong>Invoices</strong>
                                            </td>
                                            <td class="text-center">
                                                <div class="form-check form-switch d-inline-block">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="modules[invoices][visible]" id="invoices_visible" checked>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="form-check form-switch d-inline-block">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="modules[invoices][create]" id="invoices_create">
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="form-check form-switch d-inline-block">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="modules[invoices][edit]" id="invoices_edit">
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-0 py-3">
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary rounded-pill px-4">
                                    <i class="bi bi-check-lg me-2"></i>{{ __('tenant::settings.save_changes') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sidebar --}}
                <div class="col-lg-4">
                    {{-- Portal Status --}}
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4 text-center">
                            <div class="mb-3">
                                <div class="bg-success-subtle rounded-circle d-inline-flex p-3">
                                    <i class="bi bi-check-circle fs-1 text-success"></i>
                                </div>
                            </div>
                            <h5 class="fw-bold mb-2">Portal Status</h5>
                            <p class="text-muted mb-3">Customer Portal is Active</p>
                            <button type="button" class="btn btn-outline-danger btn-sm rounded-pill">
                                <i class="bi bi-x-circle me-1"></i>Disable Portal
                            </button>
                        </div>
                    </div>

                    {{-- Quick Stats --}}
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-white border-0 py-3">
                            <h6 class="fw-bold mb-0">
                                <i class="bi bi-graph-up text-primary me-2"></i>
                                Quick Stats
                            </h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-muted">Active Users</span>
                                <span class="fw-bold fs-5">24</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-muted">Open Tickets</span>
                                <span class="fw-bold fs-5">12</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Documents Shared</span>
                                <span class="fw-bold fs-5">48</span>
                            </div>
                        </div>
                    </div>

                    {{-- Help Card --}}
                    <div class="card border-0 shadow-sm rounded-4 bg-light">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-3">
                                <i class="bi bi-lightbulb text-warning me-2"></i>
                                {{ __('tenant::settings.tips') }}
                            </h6>
                            <ul class="list-unstyled mb-0 small">
                                <li class="mb-3">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    Enable only modules customers need access to
                                </li>
                                <li class="mb-3">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    Regularly update portal announcements
                                </li>
                                <li class="mb-3">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    Set appropriate permissions for data security
                                </li>
                                <li class="mb-0">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    Monitor portal usage and user feedback
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
        // Copy portal URL
        document.getElementById('copyUrl').addEventListener('click', function () {
            const urlInput = document.getElementById('portal_url');
            const fullUrl = 'https://' + urlInput.value;

            navigator.clipboard.writeText(fullUrl).then(() => {
                const icon = this.querySelector('i');
                icon.classList.remove('bi-clipboard');
                icon.classList.add('bi-check');

                setTimeout(() => {
                    icon.classList.remove('bi-check');
                    icon.classList.add('bi-clipboard');
                }, 2000);
            });
        });

        // Form submission
        document.getElementById('portalConfigForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const alert = `
                                <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm" role="alert">
                                    <i class="bi bi-check-circle me-2"></i>${data.message}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            `;
                        document.querySelector('.container-fluid').insertAdjacentHTML('afterbegin', alert);

                        setTimeout(() => {
                            document.querySelector('.alert').remove();
                        }, 3000);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        });
    </script>
@endpush