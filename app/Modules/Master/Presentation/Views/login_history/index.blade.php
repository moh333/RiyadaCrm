@extends('master::layout')

@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold mb-0">
                <i class="bi bi-clock-history me-2"></i>{{ __('master::master.login_history') ?? 'Login History' }}
            </h3>
        </div>

        <!-- DataTables CSS -->
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table id="loginHistoryTable" class="table table-hover align-middle mb-0" style="width:100%">
                        <thead class="bg-light">
                            <tr>
                                <th>{{ __('master::master.user_name') ?? 'User Name' }}</th>
                                <th>{{ __('master::master.user_ip') ?? 'User IP' }}</th>
                                <th>{{ __('master::master.login_time') ?? 'Login Time' }}</th>
                                <th>{{ __('master::master.logout_time') ?? 'Logout Time' }}</th>
                                <th>{{ __('master::master.status') ?? 'Status' }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- jQuery and DataTables JS (jQuery is assumed to be available or added if missing) -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#loginHistoryTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('master.login-history.index') }}",
                columns: [
                    { data: 'user_name', name: 'user_name', className: 'fw-bold' },
                    {
                        data: 'user_ip',
                        name: 'user_ip',
                        render: function (data) {
                            return `<code>${data}</code>`;
                        }
                    },
                    { data: 'login_time', name: 'login_time', className: 'text-muted small' },
                    {
                        data: 'logout_time',
                        name: 'logout_time',
                        className: 'text-muted small'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        render: function (data) {
                            let badgeClass = (data === 'Signed in') ? 'success' : 'secondary';
                            return `<span class="badge bg-${badgeClass} rounded-pill">${data}</span>`;
                        }
                    }
                ],
                order: [[2, 'desc']],
                language: {
                    url: "{{ app()->getLocale() == 'ar' ? 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/ar.json' : '' }}"
                },
                pageLength: 25,
                dom: '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center mt-3"ip>'
            });
        });
    </script>
@endpush