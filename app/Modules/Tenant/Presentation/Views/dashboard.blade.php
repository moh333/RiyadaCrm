@extends('tenant::layout')

@section('content')
    <div class="row g-4 mb-4">
        <!-- Stats Cards -->
        <div class="col-md-3">
            <div class="card h-100">
                <div class="stats-card">
                    <div class="icon-box bg-soft-primary">
                        <i class="bi bi-file-earmark-text-fill"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0">{{ $data['contracts'] }}</h3>
                        <p class="text-muted small mb-0">{{ __('tenant::tenant.contracts') }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="stats-card">
                    <div class="icon-box bg-soft-success">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0">$45.2k</h3>
                        <p class="text-muted small mb-0">{{ __('tenant::tenant.revenue') }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="stats-card">
                    <div class="icon-box bg-soft-warning">
                        <i class="bi bi-briefcase-fill"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0">{{ $data['opportunities'] }}</h3>
                        <p class="text-muted small mb-0">{{ __('tenant::tenant.opportunities') }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="stats-card">
                    <div class="icon-box bg-soft-danger">
                        <i class="bi bi-clock-fill"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0">{{ $data['leads'] }}</h3>
                        <p class="text-muted small mb-0">{{ __('tenant::tenant.pending_tasks') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0">{{ __('tenant::tenant.recent_activities') }}</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">{{ __('tenant::tenant.activity') }}</th>
                                    <th>{{ __('tenant::tenant.status') }}</th>
                                    <th>{{ __('tenant::tenant.date') }}</th>
                                    <th class="{{ app()->getLocale() == 'ar' ? 'text-start ps-4' : 'text-end pe-4' }}">
                                        {{ __('tenant::tenant.action') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary text-white rounded-circle p-2 me-3 ms-3"
                                                style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; font-size: 0.8rem;">
                                                <i class="bi bi-telephone"></i>
                                            </div>
                                            <div>
                                                <p class="mb-0 fw-semibold">Call with Apple Inc.</p>
                                                <small class="text-muted">Regarding new project</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span
                                            class="badge bg-soft-success rounded-pill px-3">{{ __('tenant::tenant.completed') }}</span>
                                    </td>
                                    <td class="text-muted small">Oct 24, 2023</td>
                                    <td class="{{ app()->getLocale() == 'ar' ? 'text-start ps-4' : 'text-end pe-4' }}">
                                        <button class="btn btn-sm btn-light border">{{ __('tenant::tenant.view') }}</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-warning text-white rounded-circle p-2 me-3 ms-3"
                                                style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; font-size: 0.8rem;">
                                                <i class="bi bi-envelope"></i>
                                            </div>
                                            <div>
                                                <p class="mb-0 fw-semibold">Email to Microsoft</p>
                                                <small class="text-muted">Follow up on invoice</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span
                                            class="badge bg-soft-warning rounded-pill px-3">{{ __('tenant::tenant.pending') }}</span>
                                    </td>
                                    <td class="text-muted small">Oct 25, 2023</td>
                                    <td class="{{ app()->getLocale() == 'ar' ? 'text-start ps-4' : 'text-end pe-4' }}">
                                        <button class="btn btn-sm btn-light border">{{ __('tenant::tenant.view') }}</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0">{{ __('tenant::tenant.upcoming_tasks') }}</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex mb-4">
                        <div
                            class="border-{{ app()->getLocale() == 'ar' ? 'end' : 'start' }} border-4 border-primary ps-3 pe-3">
                            <p class="mb-1 fw-semibold">Product Sync with Shopify</p>
                            <p class="text-muted small mb-0">Tomorrow at 10:00 AM</p>
                        </div>
                    </div>
                    <div class="d-flex mb-4">
                        <div
                            class="border-{{ app()->getLocale() == 'ar' ? 'end' : 'start' }} border-4 border-success ps-3 pe-3">
                            <p class="mb-1 fw-semibold">Monthly Review Meeting</p>
                            <p class="text-muted small mb-0">Oct 28 at 02:30 PM</p>
                        </div>
                    </div>
                    <div class="d-flex">
                        <div
                            class="border-{{ app()->getLocale() == 'ar' ? 'end' : 'start' }} border-4 border-warning ps-3 pe-3">
                            <p class="mb-1 fw-semibold">Prepare Sales Report</p>
                            <p class="text-muted small mb-0">Oct 30 at 09:00 AM</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection