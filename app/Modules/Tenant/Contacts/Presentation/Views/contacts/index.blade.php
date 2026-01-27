@extends('tenant::layout')

@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-0">{{ __('contacts::contacts.contacts') }}</h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="{{ route('tenant.dashboard') }}">{{ __('contacts::contacts.dashboard') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('contacts::contacts.contacts') }}</li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="{{ route('tenant.contacts.create') }}"
                    class="btn btn-primary d-flex align-items-center gap-2 px-4 py-2 rounded-3 shadow-sm">
                    <i class="bi bi-plus-lg"></i>
                    <span>{{ __('contacts::contacts.add_contact') }}</span>
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="search-box mb-0 w-100">
                            <i class="bi bi-search text-muted"></i>
                            <input type="text" id="custom-search"
                                placeholder="{{ __('contacts::contacts.search_placeholder') }}">
                        </div>
                    </div>
                    <div class="col-md-6 text-md-end mt-3 mt-md-0">
                        <div class="btn-group shadow-sm">
                            <button class="btn btn-outline-secondary dropdown-toggle rounded-3" type="button"
                                data-bs-toggle="dropdown">
                                <i class="bi bi-funnel me-1"></i> Filter
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">All Contacts</a></li>
                                <li><a class="dropdown-item" href="#">My Contacts</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="#">Portal Enabled</a></li>
                            </ul>
                        </div>
                        <a href="{{ route('tenant.contacts.export') }}" class="btn btn-outline-secondary ms-2 rounded-3">
                            <i class="bi bi-download me-1"></i> Export
                        </a>
                        <a href="{{ route('tenant.contacts.import.step1') }}"
                            class="btn btn-outline-secondary ms-2 rounded-3">
                            <i class="bi bi-upload me-1"></i> Import
                        </a>
                        <a href="{{ route('tenant.contacts.duplicates.index') }}"
                            class="btn btn-outline-secondary ms-2 rounded-3">
                            <i class="bi bi-intersect me-1"></i> Duplicates
                        </a>
                    </div>
                </div>
            </div>
            <div class="table-responsive p-4">
                <table id="contacts-table" class="table table-hover align-middle mb-0 w-100">
                    <thead class="bg-light text-muted">
                        <tr>
                            <th class="ps-4 py-3">{{ __('contacts::contacts.contact_no') }}</th>
                            <th class="py-3">{{ __('contacts::contacts.full_name') }}</th>
                            <th class="py-3">{{ __('contacts::contacts.email') }}</th>
                            <th class="py-3">{{ __('contacts::contacts.account') }}</th>
                            <th class="pe-4 py-3 text-end">{{ __('contacts::contacts.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

    <style>
        .link-muted {
            color: #64748b;
        }

        .link-muted:hover {
            color: #1e293b;
        }

        .bg-soft-primary {
            background-color: #eef2ff;
            color: #6366f1;
        }

        .bg-soft-success {
            background-color: #f0fdf4;
            color: #22c55e;
        }

        .bg-soft-info {
            background-color: #ecfeff;
            color: #0891b2;
        }

        .btn-soft-primary {
            background-color: #eef2ff;
            color: #6366f1;
            border: none;
        }

        .btn-soft-primary:hover {
            background-color: #6366f1;
            color: white;
        }

        .btn-soft-info {
            background-color: #ecfeff;
            color: #0891b2;
            border: none;
        }

        .btn-soft-info:hover {
            background-color: #0891b2;
            color: white;
        }

        .btn-soft-danger {
            background-color: #fef2f2;
            color: #ef4444;
            border: none;
        }

        .btn-soft-danger:hover {
            background-color: #ef4444;
            color: white;
        }

        .search-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 0.5rem 1rem;
            display: flex;
            align-items: center;
            transition: all 0.2s;
        }

        .search-box:focus-within {
            background: white;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .search-box input {
            background: transparent;
            border: none;
            outline: none;
            margin-left: 0.75rem;
            width: 100%;
            color: #1e293b;
        }
    </style>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(function () {
            var table = $('#contacts-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('tenant.contacts.data') }}",
                columns: [
                    { data: 'contact_no', name: 'cd.contact_no' },
                    { data: 'full_name', name: 'cd.lastname' },
                    { data: 'email', name: 'cd.email' },
                    { data: 'account_name', name: 'acc.accountname' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                dom: 'tr<"d-flex justify-content-between align-items-center mt-4"ip>',
                language: {
                    @if(app()->getLocale() == 'ar')
                        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/ar.json'
                    @endif
                    },
            order: [[1, 'asc']]
                });

        $('#custom-search').keyup(function () {
            table.search($(this).val()).draw();
        });
            });
    </script>
@endsection