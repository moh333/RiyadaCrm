@extends('tenant::layout')

@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-0">{{ __('contacts::contacts.edit_contact') }}:
                    {{ $contact->getFullName()->getDisplayName() }}
                </h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="{{ route('tenant.dashboard') }}">{{ __('contacts::contacts.dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a
                                href="{{ route('tenant.contacts.index') }}">{{ __('contacts::contacts.contacts') }}</a></li>
                        <li class="breadcrumb-item"><a
                                href="{{ route('tenant.contacts.show', $contact->getId()) }}">{{ $contact->getContactNo() }}</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('contacts::contacts.edit') }}</li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="{{ route('tenant.contacts.show', $contact->getId()) }}"
                    class="btn btn-outline-secondary px-4 py-2 rounded-3 shadow-sm">
                    {{ __('contacts::contacts.cancel') }}
                </a>
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form action="{{ route('tenant.contacts.update', $contact->getId()) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-lg-8">
                    @foreach($module->blocks()->sortBy('sequence') as $block)
                        @php
                            $fields = $module->fields()
                                ->filter(fn($f) => $f->getBlockId() === $block->getId() && $f->isVisible() && $f->isEditable())
                                ->sortBy('sequence');
                        @endphp

                        @if($fields->count() > 0)
                            <div class="card border-0 shadow-sm rounded-4 mb-4">
                                <div class="card-header bg-white border-bottom py-3 px-4">
                                    <h5 class="card-title fw-bold mb-0">
                                        <i class="bi bi-grid me-2 text-primary"></i>
                                        {{ app()->getLocale() == 'ar' ? ($block->getLabelAr() ?? $block->getLabel()) : ($block->getLabelEn() ?? $block->getLabel()) }}
                                    </h5>
                                </div>
                                <div class="card-body p-4">
                                    <div class="row g-1">
                                        @foreach($fields as $field)
                                            @include('contacts_module::contacts.partials.field_renderer', ['field' => $field, 'contact' => $contact])
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>

                <div class="col-lg-4">
                    <!-- Action Card -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4 sticky-top" style="top: 100px; z-index: 10;">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-3">{{ __('contacts::contacts.update_contact') }}</h6>
                            <p class="small text-muted mb-4">
                                {{ __('contacts::contacts.editing_notice', ['name' => $contact->getFullName()->getDisplayName()]) }}
                            </p>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg rounded-3 py-2">
                                    <i class="bi bi-save me-2"></i> {{ __('contacts::contacts.update_contact') }}
                                </button>
                                <a href="{{ route('tenant.contacts.show', $contact->getId()) }}"
                                    class="btn btn-light btn-lg rounded-3 py-2">
                                    {{ __('contacts::contacts.cancel') }}
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Status Info -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-3">{{ __('contacts::contacts.record_details') }}</h6>
                            <div class="mb-2">
                                <small class="text-muted d-block">{{ __('contacts::contacts.created_by') }}</small>
                                <span class="small fw-500">Administrator</span>
                            </div>
                            <div class="mb-2">
                                <small class="text-muted d-block">{{ __('contacts::contacts.last_modified') }}</small>
                                <span class="small fw-500">Today</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </form>
    </div>
@endsection