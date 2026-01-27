@extends('tenant::layout')

@section('content')
    <div class="container-fluid p-0">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 fw-bold mb-1 text-dark">{{ __('tenant::users.roles') ?? 'Roles' }}</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}"
                                class="text-decoration-none">{{ __('tenant::users.dashboard') ?? 'Dashboard' }}</a></li>
                        <li class="breadcrumb-item active">{{ __('tenant::users.role_management') }}</li>
                    </ol>
                </nav>

            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary rounded-pill px-3 shadow-sm transition-all" id="expand-all">
                    <i class="bi bi-arrows-expand me-1"></i> {{ __('tenant::users.expand_all') }}
                </button>
                <button class="btn btn-outline-secondary rounded-pill px-3 shadow-sm transition-all" id="collapse-all">
                    <i class="bi bi-arrows-collapse me-1"></i> {{ __('tenant::users.collapse_all') }}
                </button>
                <a href="{{ route('tenant.settings.users.roles.create') }}"
                    class="btn btn-primary rounded-pill px-4 shadow-sm hover-up">
                    <i class="bi bi-plus-lg me-2"></i>{{ __('tenant::users.create_role') }}
                </a>
            </div>
        </div>

        @if(session('success'))
            <div
                class="alert alert-success rounded-4 border-0 shadow-sm mb-4 animate__animated animate__fadeIn d-flex align-items-center">
                <div class="alert-icon bg-success text-white rounded-circle p-2 me-3 d-flex align-items-center justify-content-center"
                    style="width: 32px; height: 32px;">
                    <i class="bi bi-check-lg"></i>
                </div>
                <div class="fw-medium text-success">{{ session('success') }}</div>
                <button type="button" class="btn-close ms-auto shadow-none" data-bs-alert-dismiss="alert"
                    aria-label="Close"></button>
            </div>
        @endif

        <!-- Role List Wrapper -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden animate__animated animate__fadeInUp">
            <div class="card-header bg-white py-3 px-4 border-bottom">
                <h5 class="mb-0 fw-bold">{{ __('tenant::users.hierarchy_list') }}</h5>
                <p class="text-muted small mb-0">{{ __('tenant::users.hierarchy_list_description') }}</p>
            </div>
            <div class="card-body p-0">
                <div class="role-management-list">
                    <!-- Header Labels -->
                    <div
                        class="list-header bg-light py-2 px-4 d-none d-md-flex align-items-center border-bottom small fw-bold text-muted text-uppercase">
                        <div style="width: 40px;">#</div>
                        <div class="flex-grow-1 ps-4">{{ __('tenant::users.role_details') }}</div>
                        <div style="width: 150px;" class="text-center">{{ __('tenant::users.id_depth') }}</div>
                        <div style="width: 200px;" class="text-end pe-4">{{ __('tenant::users.actions') }}</div>
                    </div>

                    <div class="role-items-container">
                        @foreach($tree as $role)
                            @include('tenant::roles.tree_item', ['role' => $role])
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Deletion Form --}}
    <form id="delete-role-form" method="POST" style="display:none;">
        @csrf
        @method('DELETE')
    </form>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <style>
        .role-management-list {
            min-width: 800px;
        }

        .hover-up {
            transition: all 0.3s ease;
        }

        .hover-up:hover {
            transform: translateY(-2px);
        }

        .transition-all {
            transition: all 0.3s ease;
        }

        /* Indentation for recursive levels */
        .role-children-container {
            border-left: 2px solid #eef2ff;
            margin-left: 25px;
            transition: all 0.3s ease;
        }

        .role-row {
            transition: background-color 0.2s;
            border-bottom: 1px solid #f1f5f9;
            min-height: 72px;
        }

        .role-row:hover {
            background-color: #f8fafc;
        }

        .collapse-toggle {
            width: 30px !important;
            height: 30px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            border-radius: 50% !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            border: 1.5px solid #e2e8f0 !important;
            background-color: white !important;
            color: #64748b !important;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05) !important;
            cursor: pointer !important;
            padding: 0 !important;
            outline: none !important;
        }

        .collapse-toggle:hover {
            border-color: #6366f1 !important;
            color: #6366f1 !important;
            background-color: #f5f3ff !important;
            transform: scale(1.1) !important;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.15) !important;
        }

        .collapse-toggle:not(.collapsed) {
            background-color: #6366f1 !important;
            color: white !important;
            border-color: #6366f1 !important;
            box-shadow: 0 4px 10px rgba(99, 102, 241, 0.3) !important;
        }

        .collapse-toggle:not(.collapsed) i {
            transform: rotate(90deg) !important;
        }

        .collapse-toggle i {
            transition: transform 0.3s;
            font-size: 0.8rem;
        }

        .role-icon-box {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #eef2ff;
            color: #6366f1;
            transition: all 0.3s ease;
        }

        .role-id-badge {
            font-size: 0.75rem;
            background-color: #f1f5f9;
            border: 1px solid #e2e8f0;
            color: #64748b;
            padding: 2px 8px;
            border-radius: 6px;
            font-family: monospace;
        }

        /* Clearer Level-Specific Styling */
        .level-row-0 .role-name {
            font-size: 1.35rem !important;
            color: #1e1b4b !important;
            font-weight: 800 !important;
        }

        .level-row-0 .role-icon-box {
            background-color: #6366f1 !important;
            color: #ffffff !important;
            border: 2.5px solid #4338ca !important;
        }

        .level-row-1 .role-name {
            font-size: 1.15rem !important;
            color: #14532d !important;
            font-weight: 700 !important;
        }

        .level-row-1 .role-icon-box {
            background-color: #22c55e !important;
            color: #ffffff !important;
            border: 2px solid #16a34a !important;
        }

        .level-row-2 .role-name {
            font-size: 1.05rem !important;
            color: #7c2d12 !important;
            font-weight: 600 !important;
        }

        .level-row-2 .role-icon-box {
            background-color: #f59e0b !important;
            color: #ffffff !important;
            border: 2px solid #d97706 !important;
        }

        .level-row-3 .role-name {
            font-size: 0.95rem !important;
            color: #831843 !important;
            font-weight: 600 !important;
        }

        .level-row-3 .role-icon-box {
            background-color: #db2777 !important;
            color: #ffffff !important;
            border: 2px solid #be185d !important;
        }

        .level-row-4 .role-name,
        .level-row-5 .role-name {
            font-size: 0.9rem !important;
            color: #334155 !important;
        }

        .level-row-4 .role-icon-box,
        .level-row-5 .role-icon-box {
            background-color: #94a3b8 !important;
            color: #ffffff !important;
            border: 1.5px solid #64748b !important;
        }


        .depth-indicator {
            font-size: 0.65rem !important;
            color: #94a3b8;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-top: -2px;
            display: block;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function () {
            $(document).on('click', '.delete-role', function (e) {
                e.preventDefault();
                const id = $(this).data('id');
                const name = $(this).data('name');
                if (confirm(`{{ __('tenant::users.are_you_sure') }} - ${name}?`)) {
                    const form = $('#delete-role-form');
                    let url = "{{ route('tenant.settings.users.roles.destroy', ':id') }}";
                    form.attr('action', url.replace(':id', id));
                    form.submit();
                }
            });

            $('#expand-all').on('click', function () {
                $('.role-children-container').addClass('show');
                $('.collapse-toggle').removeClass('collapsed');
            });

            $('#collapse-all').on('click', function () {
                $('.role-children-container').removeClass('show');
                $('.collapse-toggle').addClass('collapsed');
            });
        });
    </script>
@endpush