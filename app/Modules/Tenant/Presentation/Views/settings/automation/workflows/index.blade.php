@extends('tenant::layout')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="fw-bold mb-1">{{ __('tenant::settings.workflows') ?? 'Workflows' }}</h2>
                        <p class="text-muted">
                            {{ __('tenant::settings.workflows_description') ?? 'Automate business processes with workflows' }}
                        </p>
                    </div>
                    <a href="{{ route('tenant.settings.crm.automation.workflows.create') }}"
                        class="btn btn-primary px-4 py-2 rounded-3 shadow-sm">
                        <i
                            class="bi bi-plus-circle me-2"></i>{{ __('tenant::settings.create_workflow') ?? 'Create Workflow' }}
                    </a>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body">
                        <label for="moduleFilter" class="form-label fw-semibold">
                            <i
                                class="bi bi-funnel me-2 text-primary"></i>{{ __('tenant::settings.filter_by_module') ?? 'Filter by Module' }}
                        </label>
                        <select id="moduleFilter" class="form-select border-0 bg-light rounded-3">
                            <option value="all">{{ __('tenant::settings.all_modules') ?? 'All Modules' }}</option>
                            @foreach($modules as $name => $label)
                                <option value="{{ $name }}">
                                    {{ $label }} ({{ $workflowCounts[$name] ?? 0 }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table id="workflows-table" class="table table-hover align-middle mb-0 w-100">
                                <thead class="bg-light text-muted">
                                    <tr>
                                        <th>{{ __('tenant::settings.workflow_name') ?? 'Workflow Name' }}</th>
                                        <th>{{ __('tenant::settings.module') ?? 'Module' }}</th>
                                        <th>{{ __('tenant::settings.summary') ?? 'Summary' }}</th>
                                        <th>{{ __('tenant::settings.execution_condition') ?? 'Execution' }}</th>
                                        <th class="text-center">{{ __('tenant::settings.status') ?? 'Status' }}</th>
                                        <th class="text-center">{{ __('tenant::settings.actions') ?? 'Actions' }}</th>
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

    {{-- Delete Confirmation Modal --}}
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow rounded-4">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title fw-bold">{{ __('tenant::settings.confirm_delete') ?? 'Confirm Delete' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <i class="bi bi-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                    <p class="mt-3 mb-1">
                        {{ __('tenant::settings.confirm_delete_workflow_message') ?? 'Are you sure you want to delete this workflow?' }}
                    </p>
                    <p class="fw-bold fs-5 text-dark" id="workflowNameToDelete"></p>
                </div>
                <div class="modal-footer border-top-0 justify-content-center">
                    <button type="button" class="btn btn-light px-4 rounded-3" data-bs-dismiss="modal">
                        {{ __('tenant::settings.cancel') ?? 'Cancel' }}
                    </button>
                    <button type="button" id="confirmDeleteBtn" class="btn btn-danger px-4 rounded-3">
                        {{ __('tenant::settings.delete') ?? 'Delete' }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <style>
        .bg-soft-info {
            background-color: rgba(13, 202, 240, 0.1);
            color: #0dcaf0;
        }

        .btn-soft-primary {
            background-color: rgba(13, 110, 253, 0.1);
            color: #0d6efd;
            border: none;
        }

        .btn-soft-primary:hover {
            background-color: #0d6efd;
            color: white;
        }

        .btn-soft-danger {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border: none;
        }

        .btn-soft-danger:hover {
            background-color: #dc3545;
            color: white;
        }
    </style>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(function () {
            const executionLabels = {
                1: '{{ __("tenant::settings.on_first_save") ?? "On First Save" }}',
                2: '{{ __("tenant::settings.once") ?? "Once" }}',
                3: '{{ __("tenant::settings.on_every_save") ?? "On Every Save" }}',
                4: '{{ __("tenant::settings.on_modify") ?? "On Modify" }}',
                6: '{{ __("tenant::settings.on_schedule") ?? "Scheduled" }}',
                7: '{{ __("tenant::settings.manual") ?? "Manual" }}',
            };

            const table = $('#workflows-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('tenant.settings.crm.automation.workflows.data') }}",
                    data: function (d) {
                        d.module = $('#moduleFilter').val();
                    }
                },
                columns: [
                    { data: 'workflowname', name: 'workflowname', className: 'fw-semibold' },
                    { data: 'module_name', name: 'module_name' },
                    {
                        data: 'summary', name: 'summary', render: function (data) {
                            return data ? `<small class="text-muted">${data}</small>` : '-';
                        }
                    },
                    {
                        data: 'execution_condition', name: 'execution_condition', render: function (data) {
                            return `<small>${executionLabels[data] || 'Unknown'}</small>`;
                        }
                    },
                    { data: 'status', name: 'status', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' }
                ],
                order: [[1, 'asc'], [0, 'asc']],
                language: {
                    @if(app()->getLocale() == 'ar')
                        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/ar.json'
                    @endif
                        },
            dom: 'frt<"d-flex justify-content-between align-items-center mt-4"ip>',
                    });

        // Module filter change
        $('#moduleFilter').on('change', function () {
            table.ajax.reload();
        });

        // Status toggle (event delegation)
        $('#workflows-table').on('change', '.status-toggle', function () {
            const workflowId = $(this).data('workflow-id');
            const status = this.checked ? 1 : 0;
            const $toggle = $(this);

            $.ajax({
                url: `{{ route('tenant.settings.crm.automation.workflows.index') }}/${workflowId}/toggle-status`,
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

        // Delete workflow
        let workflowIdToDelete = null;
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));

        $('#workflows-table').on('click', '.delete-workflow', function () {
            workflowIdToDelete = $(this).data('workflow-id');
            const row = table.row($(this).closest('tr')).data();

            $('#workflowNameToDelete').text(row.workflowname);
            deleteModal.show();
        });

        $('#confirmDeleteBtn').on('click', function () {
            if (!workflowIdToDelete) return;

            const $btn = $(this);
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

            $.ajax({
                url: `{{ route('tenant.settings.crm.automation.workflows.index') }}/${workflowIdToDelete}`,
                method: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function (data) {
                    deleteModal.hide();
                    table.ajax.reload();
                },
                error: function (xhr) {
                    alert('Error deleting workflow');
                },
                complete: function () {
                    $btn.prop('disabled', false).text('{{ __("tenant::settings.delete") ?? "Delete" }}');
                }
            });
        });
                });
    </script>
@endsection