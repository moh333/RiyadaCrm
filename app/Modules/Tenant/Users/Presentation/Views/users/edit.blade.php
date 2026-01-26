@extends('tenant::layout')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold mb-0">{{ __('tenant::users.edit_user') }}: {{ $user->getFullName() }}</h3>
                <a href="{{ route('tenant.settings.users.index') }}"
                    class="btn btn-outline-secondary rounded-3">{{ __('tenant::users.cancel') ?? 'Cancel' }}</a>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <form action="{{ route('tenant.settings.users.update', $user->getId()) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <h5 class="mb-4 text-muted border-bottom pb-2">
                            {{ __('tenant::users.login_details') ?? 'Login Details' }}
                        </h5>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">{{ __('tenant::users.user_name') }} <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="user_name" class="form-control rounded-3"
                                    value="{{ old('user_name', $user->getUserName()) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">{{ __('tenant::users.email') }} <span
                                        class="text-danger">*</span></label>
                                <input type="email" name="email1" class="form-control rounded-3"
                                    value="{{ old('email1', $user->getEmail()) }}" required>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label
                                    class="form-label fw-bold small text-muted">{{ __('tenant::users.password') ?? 'Password' }}
                                    <small
                                        class="fw-normal text-muted">({{ __('tenant::users.leave_blank') ?? 'Leave blank to keep current' }})</small></label>
                                <input type="password" name="user_password" class="form-control rounded-3">
                            </div>
                            <div class="col-md-6">
                                <label
                                    class="form-label fw-bold small text-muted">{{ __('tenant::users.confirm_password') ?? 'Confirm Password' }}</label>
                                <input type="password" name="user_password_confirmation" class="form-control rounded-3">
                            </div>
                        </div>

                        <h5 class="mb-4 text-muted border-bottom pb-2">
                            {{ __('tenant::users.user_info') ?? 'User Information' }}
                        </h5>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label
                                    class="form-label fw-bold small text-muted">{{ __('tenant::users.first_name') ?? 'First Name' }}</label>
                                <input type="text" name="first_name" class="form-control rounded-3"
                                    value="{{ old('first_name', $user->getFirstName()) }}">
                            </div>
                            <div class="col-md-6">
                                <label
                                    class="form-label fw-bold small text-muted">{{ __('tenant::users.last_name') ?? 'Last Name' }}
                                    <span class="text-danger">*</span></label>
                                <input type="text" name="last_name" class="form-control rounded-3"
                                    value="{{ old('last_name', $user->getLastName()) }}" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">{{ __('tenant::users.role') }} <span
                                        class="text-danger">*</span></label>
                                <select name="roleid" class="form-select rounded-3" required>
                                    <option value="">{{ __('tenant::users.select_role') ?? 'Select Role' }}</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->roleid }}" {{ old('roleid', $user->getRoleId()) == $role->roleid ? 'selected' : '' }}>
                                            {{ $role->rolename }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">{{ __('tenant::users.status') }} <span
                                        class="text-danger">*</span></label>
                                <select name="status" class="form-select rounded-3" required>
                                    <option value="Active" {{ old('status', $user->getStatus()) == 'Active' ? 'selected' : '' }}>{{ __('tenant::users.active') }}</option>
                                    <option value="Inactive" {{ old('status', $user->getStatus()) == 'Inactive' ? 'selected' : '' }}>{{ __('tenant::users.inactive') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label
                                    class="form-label fw-bold small text-muted">{{ __('tenant::users.title') ?? 'Title' }}</label>
                                <input type="text" name="title" class="form-control rounded-3"
                                    value="{{ old('title', $user->getTitle()) }}">
                            </div>
                            <div class="col-md-6">
                                <label
                                    class="form-label fw-bold small text-muted">{{ __('tenant::users.department') ?? 'Department' }}</label>
                                <input type="text" name="department" class="form-control rounded-3"
                                    value="{{ old('department', $user->getDepartment()) }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label
                                    class="form-label fw-bold small text-muted">{{ __('tenant::users.office_phone') ?? 'Office Phone' }}</label>
                                <input type="text" name="phone_work" class="form-control rounded-3 phone-input" dir="ltr"
                                    value="{{ old('phone_work', $user->getPhoneWork()) }}">
                            </div>
                            <div class="col-md-6">
                                <label
                                    class="form-label fw-bold small text-muted">{{ __('tenant::users.mobile_phone') ?? 'Mobile Phone' }}</label>
                                <input type="text" name="phone_mobile" class="form-control rounded-3 phone-input" dir="ltr"
                                    value="{{ old('phone_mobile', $user->getPhoneMobile()) }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label
                                    class="form-label fw-bold small text-muted">{{ __('tenant::users.reports_to') ?? 'Reports To' }}</label>
                                <select name="reports_to_id" class="form-select rounded-3 select2">
                                    <option value="">{{ __('tenant::users.select_reports_to') ?? 'Select Manager' }}
                                    </option>
                                    @foreach($users as $u)
                                        <option value="{{ $u->id }}" {{ old('reports_to_id', $user->getReportsToId()) == $u->id ? 'selected' : '' }}>
                                            {{ $u->first_name }} {{ $u->last_name }} ({{ $u->user_name }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label
                                class="form-label fw-bold small text-muted">{{ __('tenant::users.signature') ?? 'Signature' }}</label>
                            <textarea name="signature" class="form-control rounded-3"
                                rows="3">{{ old('signature', $user->getSignature()) }}</textarea>
                        </div>

                        <h5 class="mb-4 text-muted border-bottom pb-2">
                            {{ __('tenant::users.address_info') ?? 'Address Information' }}
                        </h5>
                        <div class="row mb-3">
                            <div class="col-12">
                                <label
                                    class="form-label fw-bold small text-muted">{{ __('tenant::users.street') ?? 'Street' }}</label>
                                <textarea name="address_street" class="form-control rounded-3"
                                    rows="2">{{ old('address_street', $user->getAddressStreet()) }}</textarea>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label
                                    class="form-label fw-bold small text-muted">{{ __('tenant::users.city') ?? 'City' }}</label>
                                <input type="text" name="address_city" class="form-control rounded-3"
                                    value="{{ old('address_city', $user->getAddressCity()) }}">
                            </div>
                            <div class="col-md-6">
                                <label
                                    class="form-label fw-bold small text-muted">{{ __('tenant::users.state') ?? 'State' }}</label>
                                <input type="text" name="address_state" class="form-control rounded-3"
                                    value="{{ old('address_state', $user->getAddressState()) }}">
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label
                                    class="form-label fw-bold small text-muted">{{ __('tenant::users.postal_code') ?? 'Postal Code' }}</label>
                                <input type="text" name="address_postalcode" class="form-control rounded-3"
                                    value="{{ old('address_postalcode', $user->getAddressPostalCode()) }}">
                            </div>
                            <div class="col-md-6">
                                <label
                                    class="form-label fw-bold small text-muted">{{ __('tenant::users.country') ?? 'Country' }}</label>
                                <input type="text" name="address_country" class="form-control rounded-3"
                                    value="{{ old('address_country', $user->getAddressCountry()) }}">
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_admin" value="1" id="isAdmin" {{ old('is_admin', $user->isAdmin()) ? 'checked' : '' }}>
                                <label class="form-check-label"
                                    for="isAdmin">{{ __('tenant::users.is_admin') ?? 'Admin User (Global Access)' }}</label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('tenant.settings.users.index') }}"
                                class="btn btn-light rounded-3 px-4">{{ __('tenant::users.cancel') ?? 'Cancel' }}</a>
                            <button type="submit"
                                class="btn btn-primary rounded-3 px-4">{{ __('tenant::users.update') ?? 'Update User' }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection