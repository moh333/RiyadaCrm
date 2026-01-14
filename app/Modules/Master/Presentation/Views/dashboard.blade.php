@extends('master::layout')

@section('content')
    <div class="row">
        <!-- Stats Cards -->
        <div class="col-md-4 mb-4">
            <div class="card h-100 border-0 shadow-sm bg-primary text-white">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="mb-1 opacity-75 fw-medium">Total Revenue</p>
                            <h2 class="fw-bold mb-0">
                                @if(is_array($stats) && isset($stats['revenue']))
                                    {{ $stats['revenue'] }}
                                @else
                                    $0.00
                                @endif
                            </h2>
                        </div>
                        <div class="bg-white bg-opacity-25 p-2 rounded-3">
                            <i class="bi bi-currency-dollar fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 fw-medium text-uppercase small">Active Tenants</p>
                            <h2 class="fw-bold text-dark mb-0">{{ $stats['tenants'] ?? 0 }}</h2>
                        </div>
                        <div class="bg-indigo-50 text-primary p-2 rounded-3" style="background-color: #eef2ff;">
                            <i class="bi bi-buildings fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 fw-medium text-uppercase small">Total Users</p>
                            <h2 class="fw-bold text-dark mb-0">{{ $stats['users'] ?? 0 }}</h2>
                        </div>
                        <div class="bg-green-50 text-success p-2 rounded-3" style="background-color: #ecfdf5;">
                            <i class="bi bi-people fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection