@extends('tenant::layout')

@section('content')
    <div class="container-fluid p-0">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-0">{{ $contact->getFullName()->getDisplayName() }}</h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="{{ route('tenant.dashboard') }}">{{ __('contacts::contacts.dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a
                                href="{{ route('tenant.contacts.index') }}">{{ __('contacts::contacts.contacts') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $contact->getContactNo() }}</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-soft-success px-4 py-2 rounded-3 shadow-sm border-0">
                    <i class="bi bi-envelope-at me-1"></i> {{ __('contacts::contacts.send_email') }}
                </button>
                <button class="btn btn-soft-info px-4 py-2 rounded-3 shadow-sm border-0">
                    <i class="bi bi-shield-lock me-1"></i> {{ __('contacts::contacts.enable_portal') }}
                </button>
                <a href="{{ route('tenant.contacts.edit', $contact->getId()) }}"
                    class="btn btn-outline-primary px-4 py-2 rounded-3 shadow-sm">
                    <i class="bi bi-pencil me-1"></i> {{ __('contacts::contacts.edit') }}
                </a>
                <form action="{{ route('tenant.contacts.destroy', $contact->getId()) }}" method="POST"
                    onsubmit="return confirm('{{ __('contacts::contacts.are_you_sure') }}')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-soft-danger px-4 py-2 rounded-3 shadow-sm border-0">
                        <i class="bi bi-trash me-1"></i> {{ __('contacts::contacts.delete') }}
                    </button>
                </form>
            </div>
        </div>

        <div class="row">
            <!-- Sidebar: Summary & Stats -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 mb-4 text-center p-4">
                    <div class="position-relative d-inline-block mx-auto mb-3">
                        <img src="https://ui-avatars.com/api/?name={{ $contact->getFullName()->getDisplayName() }}&background=6366f1&color=fff&size=200"
                            class="rounded-circle border p-1" width="120" height="120" alt="">
                        <span
                            class="position-absolute bottom-0 end-0 p-2 bg-success border border-white border-3 rounded-circle"
                            title="Active"></span>
                    </div>
                    <h4 class="fw-bold mb-1">{{ $contact->getFullName()->getDisplayName() }}</h4>
                    <p class="text-muted mb-3">{{ $contact->getTitle() ?: __('contacts::contacts.not_specified') }}</p>
                    <div class="d-flex justify-content-center gap-2 mb-4">
                        <span
                            class="badge bg-soft-primary text-primary rounded-pill px-3">{{ $contact->getContactNo() }}</span>
                        @if($contact->isPortalEnabled())
                            <span
                                class="badge bg-soft-success text-success rounded-pill px-3">{{ __('contacts::contacts.portal_enabled') }}</span>
                        @endif
                    </div>

                    <hr class="my-4 op-1">

                    <div class="row g-2">
                        <div class="col-6">
                            <div class="p-3 bg-light rounded-4">
                                <h6 class="text-muted small fw-bold mb-1">
                                    {{ strtoupper(__('contacts::contacts.total_deals')) }}
                                </h6>
                                <h4 class="fw-bold mb-0">0</h4>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded-4">
                                <h6 class="text-muted small fw-bold mb-1">{{ strtoupper(__('contacts::contacts.revenue')) }}
                                </h6>
                                <h4 class="fw-bold mb-0">$0</h4>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Methods -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3">{{ __('contacts::contacts.communication') }}</h6>

                        <div class="mb-3 d-flex align-items-center">
                            <div class="icon-box-sm bg-soft-primary text-primary me-3">
                                <i class="bi bi-envelope"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">{{ __('contacts::contacts.email_address') }}</small>
                                <span
                                    class="fw-bold">{{ $contact->getEmail() ?: __('contacts::contacts.not_specified') }}</span>
                            </div>
                        </div>

                        <div class="mb-3 d-flex align-items-center">
                            <div class="icon-box-sm bg-soft-success text-success me-3">
                                <i class="bi bi-telephone"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">{{ __('contacts::contacts.work_phone') }}</small>
                                <span class="fw-bold">{{ __('contacts::contacts.na') }}</span>
                            </div>
                        </div>

                        <div class="d-flex align-items-center">
                            <div class="icon-box-sm bg-soft-info text-info me-3">
                                <i class="bi bi-phone"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">{{ __('contacts::contacts.mobile_phone') }}</small>
                                <span class="fw-bold">{{ __('contacts::contacts.na') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                    <div class="bg-white border-bottom">
                        <ul class="nav nav-tabs nav-fill border-0" id="contactTab" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active py-3 border-0 border-bottom-3 fw-bold" data-bs-toggle="tab"
                                    data-bs-target="#details">{{ strtoupper(__('contacts::contacts.details')) }}</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link py-3 border-0 border-bottom-3 fw-bold" data-bs-toggle="tab"
                                    data-bs-target="#timeline">{{ strtoupper(__('contacts::contacts.timeline')) }}</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link py-3 border-0 border-bottom-3 fw-bold" data-bs-toggle="tab"
                                    data-bs-target="#deals">{{ strtoupper(__('contacts::contacts.deals')) }}</button>
                            </li>
                        </ul>
                    </div>
                    <div class="tab-content p-4">
                        <!-- Details Tab -->
                        <div class="tab-pane fade show active" id="details">
                            @if(isset($module))
                                @foreach($module->blocks()->sortBy('sequence') as $block)
                                    <div class="col-12 {{ $loop->first ? '' : 'mt-5' }}">
                                        <h6 class="fw-bold mb-3 border-bottom pb-2">
                                            {{ app()->getLocale() == 'ar' ? ($block->getLabelAr() ?? $block->getLabel()) : ($block->getLabelEn() ?? $block->getLabel()) }}
                                        </h6>
                                        <div class="row">
                                            @foreach($module->fields()->filter(fn($f) => $f->getBlockId() === $block->getId() && $f->isVisible())->sortBy('sequence') as $field)
                                                @include('contacts_module::contacts.partials.detail_field', ['field' => $field, 'contact' => $contact])
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="alert alert-warning">Module definition not found. Cannot display details
                                    dynamically.</div>
                            @endif
                        </div>

                        <!-- Timeline Tab (Placeholder) -->
                        <div class="tab-pane fade" id="timeline">
                            <div class="text-center py-5">
                                <p class="text-muted">{{ __('contacts::contacts.no_activities') }}</p>
                            </div>
                        </div>

                        <!-- Deals Tab (Placeholder) -->
                        <div class="tab-pane fade" id="deals">
                            <div class="text-center py-5">
                                <p class="text-muted">{{ __('contacts::contacts.no_deals') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <style>
        .bg-soft-primary {
            background-color: #eef2ff;
            color: #6366f1;
        }

        .bg-soft-success {
            background-color: #f0fdf4;
            color: #22c55e;
        }

        .bg-soft-info {
            background-color: #ecfeff;
            color: #0891b2;
        }

        .btn-soft-success {
            background-color: #f0fdf4;
            color: #22c55e;
            border: none;
        }

        .btn-soft-success:hover {
            background-color: #22c55e;
            color: white;
        }

        .btn-soft-info {
            background-color: #ecfeff;
            color: #0891b2;
            border: none;
        }

        .btn-soft-info:hover {
            background-color: #0891b2;
            color: white;
        }

        .btn-soft-danger {
            background-color: #fef2f2;
            color: #ef4444;
            border: none;
        }

        .btn-soft-danger:hover {
            background-color: #ef4444;
            color: white;
        }

        .icon-box-sm {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .nav-tabs .nav-link {
            color: #64748b;
            background: none;
            border: none;
            border-bottom: 3px solid transparent;
            transition: all 0.2s;
        }

        .nav-tabs .nav-link:hover {
            color: #1e293b;
        }

        .nav-tabs .nav-link.active {
            color: #6366f1;
            border-bottom-color: #6366f1;
        }

        .fw-500 {
            font-weight: 500;
        }

        .op-1 {
            opacity: 0.1;
        }
    </style>
@endsection