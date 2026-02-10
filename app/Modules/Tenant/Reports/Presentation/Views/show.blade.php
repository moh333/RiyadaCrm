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
                    @if($report->description)
                        <p class="text-muted mb-0 small">{{ $report->description }}</p>
                    @endif
                </div>
                <div class="col-auto d-flex gap-2">
                    <div class="dropdown">
                        <button class="btn btn-outline-primary rounded-pill px-4 dropdown-toggle" type="button"
                            id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-download me-2"></i> {{ __('reports::reports.export') }}
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                            <li>
                                <a class="dropdown-item"
                                    href="{{ route('tenant.reports.export', $report->reportid) }}?format=csv">
                                    <i class="bi bi-file-earmark-text me-2"></i> CSV
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item"
                                    href="{{ route('tenant.reports.export', $report->reportid) }}?format=xls">
                                    <i class="bi bi-file-earmark-excel me-2"></i> Excel
                                </a>
                            </li>
                        </ul>
                    </div>
                    <a href="{{ route('tenant.reports.edit', $report->reportid) }}"
                        class="btn btn-soft-primary rounded-pill px-4">
                        <i class="bi bi-pencil me-2"></i> {{ __('reports::reports.edit_report') }}
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- Report Details Sidebar -->
                <div class="col-md-3 mb-4">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white border-bottom py-3">
                            <h6 class="mb-0 fw-bold">{{ __('reports::reports.report_details') }}</h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                <li class="d-flex justify-content-between py-2 border-bottom">
                                    <span class="text-muted small">{{ __('reports::reports.folder') }}</span>
                                    <span class="small fw-bold">{{ $report->folder->foldername ?? '-' }}</span>
                                </li>
                                <li class="d-flex justify-content-between py-2 border-bottom">
                                    <span class="text-muted small">{{ __('reports::reports.primary_module') }}</span>
                                    <span
                                        class="small fw-bold">{{ vtranslate($report->modules->primarymodule ?? '-', 'Vtiger') }}</span>
                                </li>
                                @if(!empty($report->modules->secondarymodules))
                                    <li class="d-flex justify-content-between py-2 border-bottom">
                                        <span class="text-muted small">{{ __('reports::reports.secondary_modules') }}</span>
                                        <span class="small fw-bold">
                                            @php
                                                $secondaryStr = '';
                                                foreach (explode(':', $report->modules->secondarymodules) as $secMod) {
                                                    if ($secMod)
                                                        $secondaryStr .= vtranslate($secMod, 'Vtiger') . ', ';
                                                }
                                                echo rtrim($secondaryStr, ', ');
                                            @endphp
                                        </span>
                                    </li>
                                @endif
                                <li class="d-flex justify-content-between py-2 border-bottom">
                                    <span class="text-muted small">{{ __('reports::reports.total_rows') }}</span>
                                    <span class="small fw-bold">{{ $totalRows }}</span>
                                </li>
                                <li class="d-flex justify-content-between py-2 border-bottom">
                                    <span class="text-muted small">{{ __('reports::reports.total_columns') }}</span>
                                    <span class="small fw-bold">{{ count($columns) }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    @if($report->scheduledReport)
                        <div class="card border-0 shadow-sm rounded-4 mt-3">
                            <div class="card-header bg-white border-bottom py-3">
                                <h6 class="mb-0 fw-bold">
                                    <i class="bi bi-alarm text-primary me-2"></i>
                                    {{ __('reports::reports.scheduling') }}
                                </h6>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mb-0">
                                    <li class="d-flex justify-content-between py-2 border-bottom">
                                        <span class="text-muted small">{{ __('reports::reports.frequency') }}</span>
                                        <span class="small fw-bold">{{ $report->scheduledReport->schedule_type_label }}</span>
                                    </li>
                                    <li class="d-flex justify-content-between py-2 border-bottom">
                                        <span class="text-muted small">{{ __('reports::reports.at_time') }}</span>
                                        <span class="small fw-bold">{{ $report->scheduledReport->schtime }}</span>
                                    </li>
                                    <li class="d-flex justify-content-between py-2 border-bottom">
                                        <span class="text-muted small">{{ __('reports::reports.file_format') }}</span>
                                        <span class="small fw-bold">{{ $report->scheduledReport->fileformat ?? 'CSV' }}</span>
                                    </li>
                                    @if($report->scheduledReport->next_trigger_time)
                                        <li class="d-flex justify-content-between py-2">
                                            <span class="text-muted small">{{ __('reports::reports.next_trigger_time') }}</span>
                                            <span
                                                class="small fw-bold text-primary">{{ $report->scheduledReport->next_trigger_time_formatted }}</span>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    @endif

                    <!-- Columns Used -->
                    <div class="card border-0 shadow-sm rounded-4 mt-3">
                        <div class="card-header bg-white border-bottom py-3">
                            <h6 class="mb-0 fw-bold">{{ __('reports::reports.selected_columns') }}</h6>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                @foreach($columns as $column)
                                    <li class="list-group-item py-2 px-3">
                                        <div class="justify-content-between align-items-center">
                                            <span class="small fw-bold text-dark">{{ $column['label'] }}</span><br>
                                        </div>
                                        <div class="mt-1">
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-medium px-2 py-1" style="font-size: 9px; border-radius: 4px;">
                                                <i class="bi bi-box-seam me-1"></i>{{ $column['module'] }}
                                            </span>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Report Data -->
                <div class="col-md-9">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div
                            class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold">{{ __('reports::reports.report_data') }}</h6>
                            <span class="badge bg-primary">{{ $totalRows }} {{ __('reports::reports.records') }}</span>
                        </div>
                        <div class="card-body p-4">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 w-100" id="reportDataTable">
                                    <thead class="bg-light text-muted small text-uppercase">
                                        <tr>
                                            <th class="px-4 py-3" style="width: 50px;">#</th>
                                            @foreach($columns as $column)
                                                <th class="px-4 py-3">{{ $column['label'] }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="{{ count($columns) + 1 }}" class="text-center py-5">
                                                <div class="spinner-border text-primary" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        #reportDataTable thead th {
            white-space: nowrap;
            background: #f8f9fa;
        }

        #reportDataTable tbody td {
            white-space: nowrap;
            padding: 1rem 1.5rem;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.1em 0.5em;
        }

        .dataTables_processing {
            z-index: 10;
            background: rgba(255, 255, 255, 0.8) !important;
            border: none !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }
    </style>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            $('#reportDataTable').DataTable({
                processing: true,
                serverSide: true,
                scrollX: true,
                ajax: {
                    url: "{{ route('tenant.reports.report-data', $report->reportid) }}",
                    type: "GET",
                    error: function (xhr, error, thrown) {
                        console.error('DataTables error:', error, thrown);
                        alert('Error loading report data. Please check the console for details.');
                    }
                },
                columns: [
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    @foreach($columns as $column)
                                        {
                            data: "{{ $column['alias'] }}",
                            defaultContent: "-"
                        },
                    @endforeach
                    ],
                language: {
                    @if(app()->getLocale() == 'ar')
                        url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/ar.json'
                    @endif
                    },
            pageLength: 20,
            lengthMenu: [10, 20, 50, 100],
            dom: '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
            drawCallback: function () {
                $('.dataTables_paginate > .pagination').addClass('pagination-sm');
            }
                });
            });
    </script>
@endpush