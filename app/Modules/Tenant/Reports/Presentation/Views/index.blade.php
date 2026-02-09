@extends('tenant::layout')

@section('title', __('reports::reports.reports'))

@section('content')
    <div class="content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="row align-items-center mb-4">
                <div class="col">
                    <h1 class="h3 mb-0 text-main fw-bold">{{ __('reports::reports.reports_list') }}</h1>
                    <p class="text-muted mb-0">{{ __('reports::reports.manage_and_generate_custom_reports') }}</p>
                </div>
                <div class="col-auto">
                    <a href="{{ route('tenant.reports.create') }}"
                        class="btn btn-primary d-flex align-items-center gap-2 px-4 shadow-sm rounded-pill">
                        <i class="bi bi-plus-lg"></i>
                        <span>{{ __('reports::reports.add_new_report') }}</span>
                    </a>
                </div>
            </div>

            <div class="row g-4">
                <!-- Sidebar for Folders -->
                <div class="col-lg-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                        <div class="card-header bg-white py-3 border-bottom border-light">
                            <h5 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
                                <i class="bi bi-folder2-open text-primary"></i>
                                {{ __('reports::reports.folders') }}
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush" id="folder-list">
                                <a href="#"
                                    class="list-group-item list-group-item-action active py-3 px-4 d-flex align-items-center justify-content-between border-0"
                                    data-folder="all">
                                    <span>{{ __('reports::reports.all_reports') }}</span>
                                    <span class="badge bg-white text-primary rounded-pill">{{ $reports->count() }}</span>
                                </a>
                                @foreach($folders as $folder)
                                    <a href="#"
                                        class="list-group-item list-group-item-action py-3 px-4 d-flex align-items-center justify-content-between border-0"
                                        data-folder="{{ $folder->folderid }}">
                                        <span>{{ $folder->foldername }}</span>
                                        <span
                                            class="badge bg-soft-secondary text-muted rounded-pill">{{ $reports->where('folderid', $folder->folderid)->count() }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                        <div class="card-footer bg-light p-3 border-0">
                            <button
                                class="btn btn-outline-primary btn-sm w-100 rounded-3 d-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-folder-plus"></i>
                                {{ __('reports::reports.new_folder') }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Main Content Area (Reports List) -->
                <div class="col-lg-9">
                    <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" id="reports-table">
                                    <thead class="bg-light text-muted small text-uppercase">
                                        <tr>
                                            <th class="px-4 py-3">{{ __('reports::reports.report_name') }}</th>
                                            <th class="px-4 py-3">{{ __('reports::reports.primary_module') }}</th>
                                            <th class="px-4 py-3">{{ __('reports::reports.folder') }}</th>
                                            <th class="px-4 py-3 text-end">{{ __('reports::reports.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($reports as $report)
                                            <tr data-folder-id="{{ $report->folderid }}">
                                                <td class="px-4 py-3">
                                                    <div class="fw-bold text-dark">{{ $report->reportname }}</div>
                                                    <div class="small text-muted text-truncate" style="max-width: 300px;">
                                                        {{ $report->description }}
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3">
                                                    <span
                                                        class="badge bg-soft-info text-info rounded-pill px-3">{{ vtranslate($report->modules->primarymodule ?? '-') }}</span>
                                                </td>
                                                <td class="px-4 py-3 text-muted">
                                                    <i class="bi bi-folder2 me-1"></i>
                                                    {{ $report->folder->foldername ?? __('reports::reports.default') }}
                                                </td>
                                                <td class="px-4 py-3 text-end">
                                                    <div class="btn-group shadow-sm rounded-3">
                                                        <a href="{{ route('tenant.reports.run', $report->reportid) }}"
                                                            class="btn btn-sm btn-soft-success px-3"
                                                            title="{{ __('reports::reports.run') }}">
                                                            <i class="bi bi-play-fill"></i>
                                                        </a>
                                                        <a href="{{ route('tenant.reports.edit', $report->reportid) }}"
                                                            class="btn btn-sm btn-soft-primary px-3"
                                                            title="{{ __('reports::reports.edit') }}">
                                                            <i class="bi bi-pencil-square"></i>
                                                        </a>
                                                        <button type="button"
                                                            class="btn btn-sm btn-soft-danger px-3 delete-report"
                                                            data-id="{{ $report->reportid }}"
                                                            title="{{ __('reports::reports.delete') }}">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-5">
                                                    <div class="py-4">
                                                        <div class="mb-3">
                                                            <i class="bi bi-file-earmark-bar-graph display-4 text-muted"></i>
                                                        </div>
                                                        <h5 class="text-muted">{{ __('reports::reports.no_reports_found') }}
                                                        </h5>
                                                        <p class="text-muted small">
                                                            {{ __('reports::reports.start_by_creating_new_report') }}
                                                        </p>
                                                        <a href="{{ route('tenant.reports.create') }}"
                                                            class="btn btn-primary rounded-pill px-4 mt-2">
                                                            {{ __('reports::reports.create_report') }}
                                                        </a>
                                                    </div>
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
        </div>
    </div>

    <style>
        .bg-soft-primary {
            background-color: rgba(99, 102, 241, 0.1);
        }

        .bg-soft-info {
            background-color: rgba(54, 162, 235, 0.1);
        }

        .bg-soft-success {
            background-color: rgba(75, 192, 192, 0.1);
        }

        .bg-soft-danger {
            background-color: rgba(255, 99, 132, 0.1);
        }

        .bg-soft-secondary {
            background-color: rgba(108, 117, 125, 0.1);
        }

        .btn-soft-primary {
            color: #4F46E5;
            background-color: rgba(79, 70, 229, 0.1);
            border: none;
        }

        .btn-soft-primary:hover {
            color: #fff;
            background-color: #4F46E5;
        }

        .btn-soft-success {
            color: #10B981;
            background-color: rgba(16, 185, 129, 0.1);
            border: none;
        }

        .btn-soft-success:hover {
            color: #fff;
            background-color: #10B981;
        }

        .btn-soft-danger {
            color: #EF4444;
            background-color: rgba(239, 68, 68, 0.1);
            border: none;
        }

        .btn-soft-danger:hover {
            color: #fff;
            background-color: #EF4444;
        }

        .list-group-item.active {
            background-color: #4F46E5;
            border-color: #4F46E5;
        }
    </style>

    @push('scripts')
        <script>
            $(document).ready(function () {
                $('#folder-list a').on('click', function (e) {
                    e.preventDefault();
                    $('#folder-list a').removeClass('active');
                    $(this).addClass('active');

                    var folderId = $(this).data('folder');
                    if (folderId === 'all') {
                        $('#reports-table tbody tr').show();
                    } else {
                        $('#reports-table tbody tr').hide();
                        $('#reports-table tbody tr[data-folder-id="' + folderId + '"]').show();
                    }
                });
            });
        </script>
    @endpush
@endsection