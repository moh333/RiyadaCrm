@extends('tenant::layout')

@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-0">{{ __('contacts::contacts.add_contact') }}</h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="{{ route('tenant.dashboard') }}">{{ __('contacts::contacts.dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a
                                href="{{ route('tenant.contacts.index') }}">{{ __('contacts::contacts.contacts') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('contacts::contacts.add_contact') }}
                        </li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="{{ route('tenant.contacts.index') }}"
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

        <form action="{{ route('tenant.contacts.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-lg-8">
                    <!-- Basic Information -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-white border-bottom py-3 px-4">
                            <h5 class="card-title fw-bold mb-0"><i
                                    class="bi bi-person me-2"></i>{{ __('contacts::contacts.personal_information') }}</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-2">
                                    <label
                                        class="form-label text-muted fw-bold small uppercase">{{ __('contacts::contacts.salutation') }}</label>
                                    <select name="salutation" class="form-select rounded-3">
                                        <option value="">{{ __('contacts::contacts.none') }}</option>
                                        <option value="Mr.">Mr.</option>
                                        <option value="Ms.">Ms.</option>
                                        <option value="Mrs.">Mrs.</option>
                                        <option value="Dr.">Dr.</option>
                                        <option value="Prof.">Prof.</option>
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <label
                                        class="form-label text-muted fw-bold small uppercase">{{ __('contacts::contacts.first_name') }}</label>
                                    <input type="text" name="firstname" class="form-control rounded-3"
                                        value="{{ old('firstname') }}"
                                        placeholder="{{ __('contacts::contacts.first_name') }}">
                                </div>
                                <div class="col-md-5">
                                    <label
                                        class="form-label text-muted fw-bold small uppercase">{{ __('contacts::contacts.last_name') }}
                                        <span class="text-danger">*</span></label>
                                    <input type="text" name="lastname" class="form-control rounded-3"
                                        value="{{ old('lastname') }}" placeholder="{{ __('contacts::contacts.last_name') }}"
                                        required>
                                </div>

                                <div class="col-md-6">
                                    <label
                                        class="form-label text-muted fw-bold small uppercase">{{ __('contacts::contacts.email_address') }}</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light rounded-start-3"><i
                                                class="bi bi-envelope"></i></span>
                                        <input type="email" name="email" class="form-control rounded-end-3"
                                            value="{{ old('email') }}" placeholder="email@example.com">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label
                                        class="form-label text-muted fw-bold small uppercase">{{ __('contacts::contacts.organization_account') }}</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light rounded-start-3"><i
                                                class="bi bi-building"></i></span>
                                        <select name="account_id" class="form-select rounded-end-3">
                                            <option value="">{{ __('contacts::contacts.select_account') }}</option>
                                            <!-- Accounts would be loaded here -->
                                            <option value="1">Admin Account</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label
                                        class="form-label text-muted fw-bold small uppercase">{{ __('contacts::contacts.primary_phone') }}</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light rounded-start-3"><i
                                                class="bi bi-telephone"></i></span>
                                        <input type="text" name="phone" class="form-control rounded-end-3"
                                            value="{{ old('phone') }}" placeholder="+1 (555) 000-0000">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label
                                        class="form-label text-muted fw-bold small uppercase">{{ __('contacts::contacts.mobile_phone') }}</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light rounded-start-3"><i
                                                class="bi bi-phone"></i></span>
                                        <input type="text" name="mobile" class="form-control rounded-end-3"
                                            value="{{ old('mobile') }}" placeholder="+1 (555) 000-0000">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label
                                        class="form-label text-muted fw-bold small uppercase">{{ __('contacts::contacts.title') }}</label>
                                    <input type="text" name="title" class="form-control rounded-3"
                                        value="{{ old('title') }}" placeholder="Manager, CEO, etc.">
                                </div>
                                <div class="col-md-6">
                                    <label
                                        class="form-label text-muted fw-bold small uppercase">{{ __('contacts::contacts.department') }}</label>
                                    <input type="text" name="department" class="form-control rounded-3"
                                        value="{{ old('department') }}" placeholder="Sales, Marketing, etc.">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Details -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-white border-bottom py-3 px-4">
                            <h5 class="card-title fw-bold mb-0"><i
                                    class="bi bi-info-circle me-2"></i>{{ __('contacts::contacts.additional_details') }}
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label
                                        class="form-label text-muted fw-bold small uppercase">{{ __('contacts::contacts.description_notes') }}</label>
                                    <textarea name="description" class="form-control rounded-3" rows="3"
                                        placeholder="{{ __('contacts::contacts.description_notes') }}"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Action Card -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4 sticky-top" style="top: 100px; z-index: 10;">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-3">{{ __('contacts::contacts.save_contact') }}</h6>
                            <p class="small text-muted mb-4">{{ __('contacts::contacts.mandatory_fields_notice') }}</p>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg rounded-3 py-2">
                                    <i class="bi bi-save me-2"></i> {{ __('contacts::contacts.save_contact') }}
                                </button>
                                <a href="{{ route('tenant.contacts.index') }}" class="btn btn-light btn-lg rounded-3 py-2">
                                    {{ __('contacts::contacts.cancel') }}
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Image Upload (Preview) -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4 text-center">
                            <div class="mb-3">
                                <img src="https://ui-avatars.com/api/?name=New+Contact&background=f1f5f9&color=64748b&size=128"
                                    class="rounded-circle border p-1" width="100" height="100" alt="Avatar Preview">
                            </div>
                            <h6 class="fw-bold mb-1">Contact Photo</h6>
                            <p class="small text-muted mb-3">Images will be uploaded after creation.</p>
                            <button type="button" class="btn btn-sm btn-outline-primary disabled" disabled>
                                <i class="bi bi-camera me-1"></i> Upload Photo
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </form>
    </div>
@endsection