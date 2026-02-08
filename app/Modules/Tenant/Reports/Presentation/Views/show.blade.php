@extends('tenant::layout')

@section('title', $report->reportname)

@section('content')
    <div class="content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="row align-items-center mb-4">
                <div class="col">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1">
                            <li class="breadcrumb-item"><a
                                    href="{{ route('tenant.reports.index') }}">{{ __('reports::reports.reports') }}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $report->reportname }}</li>
                        </ol>
                    </nav>
                    <h1 class="h3 mb-0 text-main fw-bold">{{ $report->reportname }}</h1>
                    <p class="text-muted mb-0 small">{{ $report->description }}</p>
                </div>
                <div class="col-auto d-flex gap-2">
                    <a href="{{ route('tenant.reports.export', $report->reportid) }}"
                        class="btn btn-outline-primary rounded-pill px-4">
                        <i class="bi bi-download me-2"></i> {{ __('reports::reports.export') }}
                    </a>
                    <a href="{{ route('tenant.reports.edit', $report->reportid) }}"
                        class="btn btn-soft-primary rounded-pill px-4">
                        <i class="bi bi-pencil me-2"></i> {{ __('reports::reports.edit_report') }}
                    </a>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-muted small text-uppercase">
                                <tr>
                                    @if(count($data) > 0)
                                        @foreach(array_keys($data[0]) as $header)
                                            <th class="px-4 py-3">{{ str_replace('_', ' ', $header) }}</th>
                                        @endforeach
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $row)
                                    <tr>
                                        @foreach($row as $value)
                                            <td class="px-4 py-3">{{ $value }}</td>
                                        @endforeach
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-center py-5 text-muted">
                                            {{ __('reports::reports.no_data_available') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection