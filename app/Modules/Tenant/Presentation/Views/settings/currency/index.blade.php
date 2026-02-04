@extends('tenant::layout')

@section('title', __('tenant::settings.currencies'))

@section('content')
    <div class="container-fluid py-4">
        {{-- Page Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-2 fw-bold">
                    <i class="bi bi-currency-exchange text-primary me-2"></i>
                    {{ __('tenant::settings.currencies') }}
                </h1>
                <p class="text-muted mb-0">{{ __('tenant::settings.currencies_description') }}</p>
            </div>
            <div>
                <a href="{{ route('tenant.settings.crm.currency.create') }}" class="btn btn-primary rounded-pill px-4">
                    <i class="bi bi-plus-lg me-2"></i>{{ __('tenant::settings.add_currency') }}
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Currency Statistics --}}
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 bg-gradient"
                    style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="card-body p-4 text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 mb-2">{{ __('tenant::settings.total_tasks') }}</h6>
                                <h2 class="mb-0 fw-bold">5</h2>
                            </div>
                            <div class="bg-white bg-opacity-25 rounded-circle p-3">
                                <i class="bi bi-currency-exchange fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 bg-gradient"
                    style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <div class="card-body p-4 text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 mb-2">{{ __('tenant::settings.active_tasks') }}</h6>
                                <h2 class="mb-0 fw-bold">4</h2>
                            </div>
                            <div class="bg-white bg-opacity-25 rounded-circle p-3">
                                <i class="bi bi-check-circle fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 bg-gradient"
                    style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <div class="card-body p-4 text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 mb-2">{{ __('tenant::settings.base_currency') }}</h6>
                                <h2 class="mb-0 fw-bold">USD</h2>
                            </div>
                            <div class="bg-white bg-opacity-25 rounded-circle p-3">
                                <i class="bi bi-star-fill fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Currency Table --}}
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="fw-bold mb-0">
                    <i class="bi bi-list-ul text-primary me-2"></i>
                    {{ __('tenant::settings.currency_management') }}
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="currencyTable" class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3 fw-semibold">{{ __('tenant::settings.currency_name') }}</th>
                                <th class="px-4 py-3 fw-semibold">{{ __('tenant::settings.currency_code') }}</th>
                                <th class="px-4 py-3 fw-semibold">{{ __('tenant::settings.currency_symbol') }}</th>
                                <th class="px-4 py-3 fw-semibold">{{ __('tenant::settings.conversion_rate') }}</th>
                                <th class="px-4 py-3 fw-semibold">{{ __('tenant::settings.status') }}</th>
                                <th class="px-4 py-3 fw-semibold text-end">{{ __('tenant::settings.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- DataTables will populate this --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                        {{ __('tenant::settings.confirm_delete') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">{{ __('tenant::settings.confirm_delete_value') }}</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4"
                        data-bs-dismiss="modal">{{ __('tenant::settings.cancel') }}</button>
                    <button type="button" class="btn btn-danger rounded-pill px-4"
                        id="confirmDelete">{{ __('tenant::settings.delete') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function () {
            let deleteId = null;

            // Initialize DataTable
            const table = $('#currencyTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: '{{ route('tenant.settings.crm.currency.data') }}',
                columns: [{
                    data: 'currency_name',
                    name: 'currency_name'
                },
                {
                    data: 'currency_code',
                    name: 'currency_code'
                },
                {
                    data: 'currency_symbol',
                    name: 'currency_symbol',
                    render: function (data) {
                        return `<span class="badge bg-light text-dark border">${data}</span>`;
                    }
                },
                {
                    data: 'conversion_rate',
                    name: 'conversion_rate',
                    render: function (data) {
                        return parseFloat(data).toFixed(4);
                    }
                },
                {
                    data: 'currency_status',
                    name: 'currency_status',
                    render: function (data) {
                        return data === 'Active' ?
                            '<span class="badge bg-success-subtle text-success rounded-pill"><i class="bi bi-check-circle me-1"></i>Active</span>' :
                            '<span class="badge bg-secondary-subtle text-secondary rounded-pill"><i class="bi bi-x-circle me-1"></i>Inactive</span>';
                    }
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    className: 'text-end',
                    render: function (data, type, row) {
                        return `
                                    <div class="btn-group btn-group-sm">
                                        <a href="/settings/crm/currency/${row.id}/edit" 
                                           class="btn btn-outline-primary rounded-start-pill">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button class="btn btn-outline-danger rounded-end-pill delete-btn" 
                                                data-id="${row.id}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                `;
                    }
                }
                ],
                language: {
                    emptyTable: '<div class="text-center py-5"><i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i><p class="text-muted">{{ __('tenant::settings.no_workflows') }}</p></div>',
                    processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
                },
                order: [
                    [0, 'asc']
                ],
                pageLength: 25,
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
            });

            // Delete button click
            $(document).on('click', '.delete-btn', function () {
                deleteId = $(this).data('id');
                $('#deleteModal').modal('show');
            });

            // Confirm delete
            $('#confirmDelete').on('click', function () {
                if (deleteId) {
                    $.ajax({
                        url: `/settings/crm/currency/${deleteId}`,
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (response) {
                            $('#deleteModal').modal('hide');
                            table.ajax.reload();

                            // Show success message
                            const alert = `
                                    <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm" role="alert">
                                        <i class="bi bi-check-circle me-2"></i>${response.message}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                `;
                            $('.container-fluid').prepend(alert);

                            setTimeout(() => {
                                $('.alert').fadeOut();
                            }, 3000);
                        },
                        error: function (xhr) {
                            $('#deleteModal').modal('hide');
                            alert(xhr.responseJSON?.message || 'Error deleting currency');
                        }
                    });
                }
            });
        });
    </script>
@endpush