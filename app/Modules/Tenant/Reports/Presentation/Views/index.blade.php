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
                                    data-folder="">
                                    <span>{{ __('reports::reports.all_reports') }}</span>
                                    <span class="badge bg-white text-primary rounded-pill" id="all-count">-</span>
                                </a>
                                @foreach($folders as $folder)
                                    <a href="#"
                                        class="list-group-item list-group-item-action py-3 px-4 d-flex align-items-center justify-content-between border-0"
                                        data-folder="{{ $folder->folderid }}">
                                        <span>{{ $folder->foldername }}</span>
                                        <span class="badge bg-soft-secondary text-muted rounded-pill folder-count"
                                            data-folder-id="{{ $folder->folderid }}">-</span>
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
                                <table class="table table-hover align-middle mb-0" id="reports-table" style="width: 100%;">
                                    <thead class="bg-light text-muted small text-uppercase">
                                        <tr>
                                            <th class="px-4 py-3">{{ __('reports::reports.report_name') }}</th>
                                            <th class="px-4 py-3">{{ __('reports::reports.primary_module') }}</th>
                                            <th class="px-4 py-3">{{ __('reports::reports.folder') }}</th>
                                            <th class="px-4 py-3 text-end" style="width: 180px;">
                                                {{ __('reports::reports.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title" id="deleteModalLabel">
                        <i class="bi bi-exclamation-triangle text-danger me-2"></i>
                        {{ __('reports::reports.confirm_delete') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">{{ __('reports::reports.delete_report_confirmation') }}</p>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">
                        {{ __('reports::reports.cancel') }}
                    </button>
                    <button type="button" class="btn btn-danger rounded-pill px-4" id="confirmDelete">
                        <i class="bi bi-trash me-2"></i>{{ __('reports::reports.delete') }}
                    </button>
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

        .btn-soft-info {
            color: #0DCAF0;
            background-color: rgba(13, 202, 240, 0.1);
            border: none;
        }

        .btn-soft-info:hover {
            color: #fff;
            background-color: #0DCAF0;
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

        /* DataTables custom styling */
        #reports-table_wrapper .dataTables_length,
        #reports-table_wrapper .dataTables_filter,
        #reports-table_wrapper .dataTables_info,
        #reports-table_wrapper .dataTables_paginate {
            padding: 1rem;
        }

        #reports-table_wrapper .dataTables_filter input {
            border-radius: 20px;
            padding: 0.5rem 1rem;
            border: 1px solid #e0e0e0;
        }

        #reports-table_wrapper .dataTables_length select {
            border-radius: 8px;
            padding: 0.25rem 0.5rem;
        }

        .dataTables_empty {
            padding: 3rem !important;
        }
    </style>

    @push('scripts')
        <script>
            $(document).ready(function () {
                let currentFolder = '';
                let deleteReportId = null;
                const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));

                // Initialize DataTable with server-side processing
                const reportsTable = $('#reports-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: '{{ route("tenant.reports.datatable") }}',
                        data: function (d) {
                            d.folder = currentFolder;
                        }
                    },
                    columns: [
                        {
                            data: 'reportname',
                            name: 'reportname',
                            render: function (data, type, row) {
                                return `
                                            <div class="fw-bold text-dark">${data}</div>
                                            <div class="small text-muted text-truncate" style="max-width: 300px;">
                                                ${row.description || ''}
                                            </div>
                                        `;
                            }
                        },
                        {
                            data: 'primarymodule',
                            name: 'primarymodule',
                            render: function (data, type, row) {
                                return `<span class="badge bg-soft-info text-info rounded-pill px-3">${data || '-'}</span>`;
                            }
                        },
                        {
                            data: 'foldername',
                            name: 'foldername',
                            render: function (data, type, row) {
                                return `<i class="bi bi-folder2 me-1"></i>${data || '{{ __("reports::reports.default") }}'}`;
                            }
                        },
                        {
                            data: 'actions',
                            name: 'actions',
                            orderable: false,
                            searchable: false,
                            className: 'text-end',
                            render: function (data, type, row) {
                                return `
                                            <div class="btn-group shadow-sm rounded-3">
                                                <a href="/reports/${row.reportid}" 
                                                    class="btn btn-sm btn-soft-info px-3" 
                                                    title="{{ __('reports::reports.view') }}">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="/reports/${row.reportid}/run" 
                                                    class="btn btn-sm btn-soft-success px-3" 
                                                    title="{{ __('reports::reports.run') }}">
                                                    <i class="bi bi-play-fill"></i>
                                                </a>
                                                <a href="/reports/${row.reportid}/edit" 
                                                    class="btn btn-sm btn-soft-primary px-3" 
                                                    title="{{ __('reports::reports.edit') }}">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                                <button type="button" 
                                                    class="btn btn-sm btn-soft-danger px-3 delete-report" 
                                                    data-id="${row.reportid}" 
                                                    title="{{ __('reports::reports.delete') }}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        `;
                            }
                        }
                    ],
                    order: [[0, 'asc']],
                    language: {
                        processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
                        emptyTable: `
                                    <div class="py-4">
                                        <div class="mb-3">
                                            <i class="bi bi-file-earmark-bar-graph display-4 text-muted"></i>
                                        </div>
                                        <h5 class="text-muted">{{ __('reports::reports.no_reports_found') }}</h5>
                                        <p class="text-muted small">{{ __('reports::reports.start_by_creating_new_report') }}</p>
                                        <a href="{{ route('tenant.reports.create') }}" class="btn btn-primary rounded-pill px-4 mt-2">
                                            {{ __('reports::reports.create_report') }}
                                        </a>
                                    </div>
                                `,
                        lengthMenu: '{{ __("reports::reports.show") }} _MENU_ {{ __("reports::reports.entries") }}',
                        info: '{{ __("reports::reports.showing") }} _START_ {{ __("reports::reports.to") }} _END_ {{ __("reports::reports.of") }} _TOTAL_ {{ __("reports::reports.entries") }}',
                        infoEmpty: '{{ __("reports::reports.no_entries") }}',
                        search: '{{ __("reports::reports.search") }}:',
                        paginate: {
                            first: '<i class="bi bi-chevron-double-left"></i>',
                            last: '<i class="bi bi-chevron-double-right"></i>',
                            next: '<i class="bi bi-chevron-right"></i>',
                            previous: '<i class="bi bi-chevron-left"></i>'
                        }
                    },
                    drawCallback: function (settings) {
                        // Update folder counts
                        if (settings.json && settings.json.folderCounts) {
                            const counts = settings.json.folderCounts;
                            $('#all-count').text(counts.total || 0);
                            Object.keys(counts.folders || {}).forEach(folderId => {
                                $(`.folder-count[data-folder-id="${folderId}"]`).text(counts.folders[folderId]);
                            });
                        }
                    }
                });

                // Folder filter click handler
                $('#folder-list a').on('click', function (e) {
                    e.preventDefault();
                    $('#folder-list a').removeClass('active');
                    $(this).addClass('active');

                    currentFolder = $(this).data('folder');
                    reportsTable.ajax.reload();
                });

                // Delete button click handler
                $(document).on('click', '.delete-report', function () {
                    deleteReportId = $(this).data('id');
                    deleteModal.show();
                });

                // Confirm delete button click handler
                $('#confirmDelete').on('click', function () {
                    if (!deleteReportId) return;

                    const btn = $(this);
                    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>{{ __("reports::reports.deleting") }}');

                    $.ajax({
                        url: `/reports/${deleteReportId}`,
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        success: function (response) {
                            deleteModal.hide();
                            reportsTable.ajax.reload();

                            // Show success toast
                            if (typeof toastr !== 'undefined') {
                                toastr.success('{{ __("reports::reports.report_deleted_success") }}');
                            }
                        },
                        error: function (xhr) {
                            console.error('Delete error:', xhr);
                            alert('{{ __("reports::reports.delete_error") }}');
                        },
                        complete: function () {
                            btn.prop('disabled', false).html('<i class="bi bi-trash me-2"></i>{{ __("reports::reports.delete") }}');
                            deleteReportId = null;
                        }
                    });
                });
            });
        </script>
    @endpush
@endsection