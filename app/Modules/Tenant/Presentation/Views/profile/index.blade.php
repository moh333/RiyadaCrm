@extends('tenant::layout')

@section('content')
    <div class="container-fluid py-4">
        <div class="row g-4">
            <!-- Sidebar Profile Info -->
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                    <div class="card-body text-center p-5">
                        <div class="position-relative d-inline-block mb-4">
                            <img src="https://ui-avatars.com/api/?name={{ $user->user_name }}&background=6366f1&color=fff&size=128"
                                class="rounded-circle shadow-sm border border-4 border-white" alt="Avatar">
                        </div>
                        <h4 class="fw-bold mb-1">{{ $user->user_name }} [{{ $role ? $role->rolename : 'NoRole' }}]</h4>

                        <p class="text-muted mb-4">{{ $user->title ?: __('tenant::tenant.administrator') }}</p>

                        <div class="d-flex justify-content-center gap-2 flex-wrap">
                            <span class="badge bg-soft-primary px-3 py-2 rounded-pill">
                                <i class="bi bi-building me-1"></i>{{ $user->department ?: 'General' }}
                            </span>
                            @if ($role)
                                <span class="badge bg-soft-info px-3 py-2 rounded-pill">
                                    <i class="bi bi-briefcase me-1"></i>{{ $role->rolename }}
                                </span>
                            @else
                                <span class="badge bg-soft-warning px-3 py-2 rounded-pill">
                                    <i class="bi bi-exclamation-circle me-1"></i>{{ __('tenant::users.not_assigned') ?? 'No Role' }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Password Change Card -->
                <div class="card shadow-sm border-0 rounded-4 mt-4">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h5 class="fw-bold mb-0">{{ __('tenant::tenant.change_password') }}</h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('tenant.profile.password') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted text-uppercase">{{ __('tenant::tenant.new_password') }}</label>
                                <input type="password" name="password" class="form-control rounded-3" required>
                            </div>
                            <div class="mb-4">
                                <label class="form-label small fw-bold text-muted text-uppercase">{{ __('tenant::tenant.confirm_password') }}</label>
                                <input type="password" name="password_confirmation" class="form-control rounded-3" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 rounded-pill shadow-sm py-2">
                                {{ __('tenant::tenant.change_password') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Personal Info Form -->
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h5 class="fw-bold mb-0">{{ __('tenant::tenant.personal_info') }}</h5>
                    </div>
                    <div class="card-body p-4">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 mb-4" role="alert">
                                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form action="{{ route('tenant.profile.update') }}" method="POST">
                            @csrf
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label
                                        class="form-label small fw-bold text-muted text-uppercase">{{ __('tenant::tenant.first_name') }}</label>
                                    <input type="text" name="first_name" class="form-control rounded-3 py-2"
                                        value="{{ $user->first_name }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label
                                        class="form-label small fw-bold text-muted text-uppercase">{{ __('tenant::tenant.last_name') }}</label>
                                    <input type="text" name="last_name" class="form-control rounded-3 py-2"
                                        value="{{ $user->last_name }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label
                                        class="form-label small fw-bold text-muted text-uppercase">{{ __('tenant::tenant.email') }}</label>
                                    <input type="email" name="email1" class="form-control rounded-3 py-2"
                                        value="{{ $user->email1 }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label
                                        class="form-label small fw-bold text-muted text-uppercase">{{ __('tenant::tenant.phone') }}</label>
                                    <input type="text" name="phone_mobile" class="form-control rounded-3 py-2"
                                        value="{{ $user->phone_mobile }}">
                                </div>
                                <div class="col-md-6">
                                    <label
                                        class="form-label small fw-bold text-muted text-uppercase">{{ __('tenant::tenant.title') }}</label>
                                    <input type="text" name="title" class="form-control rounded-3 py-2"
                                        value="{{ $user->title }}">
                                </div>
                                <div class="col-md-6">
                                    <label
                                        class="form-label small fw-bold text-muted text-uppercase">{{ __('tenant::tenant.department') }}</label>
                                    <input type="text" name="department" class="form-control rounded-3 py-2"
                                        value="{{ $user->department }}">
                                </div>
                            </div>

                            <div class="mt-5 text-{{ app()->getLocale() == 'ar' ? 'start' : 'end' }}">
                                <button type="submit" class="btn btn-primary px-5 py-2.5 rounded-pill shadow-sm fw-bold">
                                    {{ __('tenant::tenant.update_profile') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection