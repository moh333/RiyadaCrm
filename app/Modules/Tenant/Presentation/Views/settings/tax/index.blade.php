@extends('tenant::layout')

@section('title', __('tenant::settings.tax_management'))

@section('content')
    <div class="container-fluid py-4">
        {{-- Page Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-2 fw-bold">
                    <i class="bi bi-percent text-primary me-2"></i>
                    {{ __('tenant::settings.tax_management') }}
                </h1>
                <p class="text-muted mb-0">{{ __('tenant::settings.tax_management_description') }}</p>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Tabs for Product and Shipping Taxes --}}
        <ul class="nav nav-tabs mb-4" id="taxTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="product-tab" data-bs-toggle="tab" data-bs-target="#product-taxes"
                    type="button" role="tab">
                    <i class="bi bi-box-seam me-2"></i>{{ __('tenant::settings.product_taxes') }}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="shipping-tab" data-bs-toggle="tab" data-bs-target="#shipping-taxes"
                    type="button" role="tab">
                    <i class="bi bi-truck me-2"></i>{{ __('tenant::settings.shipping_taxes') }}
                </button>
            </li>
        </ul>

        <div class="tab-content" id="taxTabsContent">
            {{-- Product Taxes Tab --}}
            <div class="tab-pane fade show active" id="product-taxes" role="tabpanel">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold mb-0">{{ __('tenant::settings.product_service_tax') }}</h5>
                            <a href="{{ route('tenant.settings.crm.tax.create', ['type' => 'product']) }}"
                                class="btn btn-primary rounded-pill px-4">
                                <i class="bi bi-plus-lg me-2"></i>{{ __('tenant::settings.add_tax') }}
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <table id="productTaxTable" class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('tenant::settings.tax_label') }}</th>
                                    <th>{{ __('tenant::settings.tax_rate') }}</th>
                                    <th>{{ __('tenant::settings.status') }}</th>
                                    <th class="text-end">{{ __('tenant::settings.actions') }}</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Shipping Taxes Tab --}}
            <div class="tab-pane fade" id="shipping-taxes" role="tabpanel">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold mb-0">{{ __('tenant::settings.shipping_handling_tax') }}</h5>
                            <a href="{{ route('tenant.settings.crm.tax.create', ['type' => 'shipping']) }}"
                                class="btn btn-primary rounded-pill px-4">
                                <i class="bi bi-plus-lg me-2"></i>{{ __('tenant::settings.add_tax') }}
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <table id="shippingTaxTable" class="table table-hover w-100">
                            <thead>
                                <tr>
                                    <th>{{ __('tenant::settings.tax_label') }}</th>
                                    <th>{{ __('tenant::settings.tax_rate') }}</th>
                                    <th>{{ __('tenant::settings.status') }}</th>
                                    <th class="text-end">{{ __('tenant::settings.actions') }}</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Delete Confirmation Modal --}}
        <div class="modal fade" id="deleteTaxModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold">{{ __('tenant::settings.confirm_delete') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>{{ __('tenant::settings.confirm_delete_message') }}</p>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            This will soft-delete the tax. Existing records will retain their tax data.
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary rounded-pill"
                            data-bs-dismiss="modal">{{ __('tenant::settings.cancel') }}</button>
                        <button type="button" class="btn btn-danger rounded-pill"
                            id="confirmDelete">{{ __('tenant::settings.delete') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            let deleteUrl = '';
            let taxType = 'product';

            // Product Tax DataTable
            const productTable = $('#productTaxTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('tenant.settings.crm.tax.data') }}',
                    data: function(d) {
                        d.type = 'product';
                    }
                },
                columns: [{
                        data: 'taxlabel',
                        name: 'taxlabel'
                    },
                    {
                        data: 'percentage',
                        name: 'percentage',
                        render: function(data) {
                            return data + '%';
                        }
                    },
                    {
                        data: 'deleted',
                        name: 'deleted',
                        render: function(data) {
                            if (data == 0) {
                                return '<span class="badge bg-success">{{ __('tenant::settings.active') }}</span>';
                            }
                            return '<span class="badge bg-secondary">{{ __('tenant::settings.inactive') }}</span>';
                        }
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-end',
                        render: function(data, type, row) {
                            return `
                                <a href="/settings/crm/tax/${row.taxid}/edit?type=product" 
                                   class="btn btn-sm btn-outline-primary rounded-pill me-1">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-danger rounded-pill delete-tax" 
                                        data-id="${row.taxid}" data-type="product">
                                    <i class="bi bi-trash"></i>
                                </button>
                            `;
                        }
                    }
                ],
                order: [
                    [0, 'asc']
                ]
            });

            // Shipping Tax DataTable
            const shippingTable = $('#shippingTaxTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('tenant.settings.crm.tax.data') }}',
                    data: function(d) {
                        d.type = 'shipping';
                    }
                },
                columns: [{
                        data: 'taxlabel',
                        name: 'taxlabel'
                    },
                    {
                        data: 'percentage',
                        name: 'percentage',
                        render: function(data) {
                            return data + '%';
                        }
                    },
                    {
                        data: 'deleted',
                        name: 'deleted',
                        render: function(data) {
                            if (data == 0) {
                                return '<span class="badge bg-success">{{ __('tenant::settings.active') }}</span>';
                            }
                            return '<span class="badge bg-secondary">{{ __('tenant::settings.inactive') }}</span>';
                        }
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-end',
                        render: function(data, type, row) {
                            return `
                                <a href="/settings/crm/tax/${row.taxid}/edit?type=shipping" 
                                   class="btn btn-sm btn-outline-primary rounded-pill me-1">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-danger rounded-pill delete-tax" 
                                        data-id="${row.taxid}" data-type="shipping">
                                    <i class="bi bi-trash"></i>
                                </button>
                            `;
                        }
                    }
                ],
                order: [
                    [0, 'asc']
                ]
            });

            // Delete tax
            $(document).on('click', '.delete-tax', function() {
                const taxId = $(this).data('id');
                taxType = $(this).data('type');
                deleteUrl = `/settings/crm/tax/${taxId}`;
                $('#deleteTaxModal').modal('show');
            });

            // Confirm delete
            $('#confirmDelete').click(function() {
                $.ajax({
                    url: deleteUrl,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}',
                        type: taxType
                    },
                    success: function(response) {
                        $('#deleteTaxModal').modal('hide');
                        if (taxType === 'product') {
                            productTable.ajax.reload();
                        } else {
                            shippingTable.ajax.reload();
                        }
                        // Show success message
                        const alert = `
                            <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm" role="alert">
                                <i class="bi bi-check-circle me-2"></i>${response.message}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        `;
                        $('.container-fluid').prepend(alert);
                    },
                    error: function(xhr) {
                        $('#deleteTaxModal').modal('hide');
                        alert('Error deleting tax');
                    }
                });
            });
        });
    </script>
@endpush