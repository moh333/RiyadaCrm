@extends('tenant::layout')

@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold mb-0">{{ __('tenant::users.users') }}</h3>
            <a href="{{ route('tenant.settings.users.create') }}" class="btn btn-primary rounded-3">
                <i class="bi bi-plus-circle me-2"></i>{{ __('tenant::users.create_user') }}
            </a>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle w-100" id="users-table">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3 border-0">{{ __('tenant::users.name') ?? 'Name' }}</th>
                                <th class="px-4 py-3 border-0">{{ __('tenant::users.email') ?? 'Email' }}</th>
                                <th class="px-4 py-3 border-0">{{ __('tenant::users.role') ?? 'Role' }}</th>
                                <th class="px-4 py-3 border-0">{{ __('tenant::users.status') ?? 'Status' }}</th>
                                <th class="px-4 py-3 border-0 text-end">{{ __('tenant::users.actions') ?? 'Actions' }}</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#users-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('tenant.settings.users.index') }}",
                columns: [
                    { data: 'full_name', name: 'full_name', orderable: false },
                    { data: 'email1', name: 'vtiger_users.email1' },
                    { data: 'rolename', name: 'vtiger_role.rolename' },
                    { data: 'status', name: 'vtiger_users.status' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' }
                ],
                language: {
                    url: "{{ app()->getLocale() == 'ar' ? '//cdn.datatables.net/plug-ins/1.13.7/i18n/ar.json' : '' }}"
                },
                dom: '<"d-flex justify-content-between align-items-center p-3"f>rt<"d-flex justify-content-between align-items-center p-3"ip>',
                drawCallback: function () {
                    // Re-initialize tooltips or other JS components if needed
                }
            });
        });
    </script>
@endpush