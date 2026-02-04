@extends('tenant::layout')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="fw-bold mb-1">{{ __('tenant::settings.scheduler') }}</h2>
                        <p class="text-muted">{{ __('tenant::settings.scheduler_description') }}</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('tenant.settings.crm.automation.scheduler.create') }}"
                            class="btn btn-primary px-4 py-2 rounded-3 shadow-sm">
                            <i class="bi bi-plus-circle me-2"></i>{{ __('tenant::settings.add_task') ?? 'Add Task' }}
                        </a>
                        <a href="{{ route('tenant.settings.crm.automation.workflows.index') }}"
                            class="btn btn-outline-secondary px-4 py-2 rounded-3">
                            <i class="bi bi-arrow-left me-2"></i>{{ __('tenant::settings.back') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Statistics Cards --}}
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body py-4">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 bg-soft-primary p-3 rounded-circle">
                                <i class="bi bi-clock-history text-primary fs-3"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1">{{ __('tenant::settings.total_tasks') }}</h6>
                                <h3 class="mb-0 fw-bold">{{ $stats['total'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body py-4">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 bg-soft-success p-3 rounded-circle">
                                <i class="bi bi-check-circle text-success fs-3"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1">{{ __('tenant::settings.active_tasks') }}</h6>
                                <h3 class="mb-0 fw-bold">{{ $stats['active'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body py-4">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 bg-soft-danger p-3 rounded-circle">
                                <i class="bi bi-x-circle text-danger fs-3"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1">{{ __('tenant::settings.disabled_tasks') }}</h6>
                                <h3 class="mb-0 fw-bold">{{ $stats['disabled'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body py-4">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 bg-soft-info p-3 rounded-circle">
                                <i class="bi bi-arrow-repeat text-info fs-3"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1">{{ __('tenant::settings.running_tasks') }}</h6>
                                <h3 class="mb-0 fw-bold">{{ $stats['running'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Cron Tasks Table --}}
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h5 class="mb-0 fw-bold">
                    <i class="bi bi-list-task me-2 text-primary"></i>{{ __('tenant::settings.scheduled_tasks') }}
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table id="scheduler-table" class="table table-hover align-middle mb-0 w-100">
                        <thead class="bg-light text-muted">
                            <tr>
                                <th>{{ __('tenant::settings.task_name') }}</th>
                                <th>{{ __('tenant::settings.module') }}</th>
                                <th>{{ __('tenant::settings.frequency') }}</th>
                                <th>{{ __('tenant::settings.last_run') }}</th>
                                <th>{{ __('tenant::settings.last_end') ?? 'Last End' }}</th>
                                <th>{{ __('tenant::settings.status') }}</th>
                                <th class="text-end">{{ __('tenant::settings.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <style>
        .bg-soft-primary {
            background-color: rgba(13, 110, 253, 0.1);
        }

        .bg-soft-success {
            background-color: rgba(25, 135, 84, 0.1);
        }

        .bg-soft-danger {
            background-color: rgba(220, 53, 69, 0.1);
        }

        .bg-soft-info {
            background-color: rgba(13, 202, 240, 0.1);
        }
    </style>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(function () {
            const table = $('#scheduler-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('tenant.settings.crm.automation.scheduler.data') }}",
                columns: [
                    { data: 'name', name: 'name' },
                    { data: 'module', name: 'module', className: 'text-center' },
                    { data: 'frequency_label', name: 'frequency', searchable: false },
                    { data: 'laststart', name: 'laststart' },
                    { data: 'lastend', name: 'lastend' },
                    { data: 'status', name: 'status', orderable: false, searchable: false },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' }
                ],
                order: [[0, 'asc']],
                language: {
                    @if(app()->getLocale() == 'ar')
                        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/ar.json'
                    @endif
                    },
            dom: 'frt<"d-flex justify-content-between align-items-center mt-4"ip>',
                });

        // Status toggle (using event delegation)
        $('#scheduler-table').on('change', '.status-toggle', function () {
            const taskId = $(this).data('task-id');
            const status = this.checked ? 1 : 0;
            const $toggle = $(this);

            $.ajax({
                url: `{{ route('tenant.settings.crm.automation.scheduler.index') }}/${taskId}/toggle-status`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    status: status
                },
                success: function (data) {
                    if (!data.success) {
                        $toggle.prop('checked', !status);
                    }
                },
                error: function () {
                    $toggle.prop('checked', !status);
                }
            });
        });

        // Run now button (using event delegation)
        $('#scheduler-table').on('click', '.run-task-btn', function () {
            const taskId = $(this).data('task-id');
            const $btn = $(this);

            if (!confirm('{{ __("tenant::settings.run_now") }}?')) {
                return;
            }

            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

            $.ajax({
                url: `{{ route('tenant.settings.crm.automation.scheduler.index') }}/${taskId}/run-now`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function (data) {
                    if (data.success) {
                        table.ajax.reload(null, false);
                    } else {
                        alert(data.message);
                        $btn.prop('disabled', false).html('<i class="bi bi-play-fill"></i>');
                    }
                },
                error: function (xhr) {
                    alert(xhr.responseJSON?.message || 'Error');
                    $btn.prop('disabled', false).html('<i class="bi bi-play-fill"></i>');
                }
            });
        });

        // Delete task button (using event delegation)
        $('#scheduler-table').on('click', '.delete-task-btn', function () {
            const taskId = $(this).data('task-id');

            if (!confirm('{{ __("tenant::settings.delete_task_confirm") ?? "Are you sure you want to delete this task?" }}')) {
                return;
            }

            $.ajax({
                url: `{{ route('tenant.settings.crm.automation.scheduler.index') }}/${taskId}`,
                method: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function (data) {
                    if (data.success) {
                        table.ajax.reload(null, false);
                    } else {
                        alert(data.message);
                    }
                },
                error: function (xhr) {
                    alert(xhr.responseJSON?.message || 'Error deleting task');
                }
            });
        });
            });
    </script>
@endsection