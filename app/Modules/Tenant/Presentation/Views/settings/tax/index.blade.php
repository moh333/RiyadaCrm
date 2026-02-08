@extends('tenant::layout')

@section('title', __('tenant::settings.tax_management'))

@section('content')
    <div class="container-fluid py-4">
        {{-- Page Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-2 fw-bold text-dark">
                    <i class="bi bi-percent text-primary me-2"></i>
                    {{ __('tenant::settings.tax_management') }}
                </h1>
                <p class="text-muted mb-0">{{ __('tenant::settings.tax_management_description') }}</p>
            </div>
        </div>

        {{-- Alerts --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Tabs --}}
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-light border-0 p-0">
                <ul class="nav nav-pills nav-fill custom-tabs" id="taxTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link py-3 rounded-0 {{ $activeTab === 'taxes' ? 'active' : '' }}"
                            href="{{ route('tenant.settings.crm.tax.taxes') }}">
                            <i class="bi bi-cash-stack me-2"></i>{{ __('tenant::settings.taxes') }}
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link py-3 rounded-0 {{ $activeTab === 'charges' ? 'active' : '' }}"
                            href="{{ route('tenant.settings.crm.tax.charges') }}">
                            <i class="bi bi-receipt me-2"></i>{{ __('tenant::settings.charges_and_its_taxes') }}
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link py-3 rounded-0 {{ $activeTab === 'regions' ? 'active' : '' }}"
                            href="{{ route('tenant.settings.crm.tax.regions') }}">
                            <i class="bi bi-geo-alt me-2"></i>{{ __('tenant::settings.tax_regions') }}
                        </a>
                    </li>
                </ul>
            </div>

            <div class="card-body p-4">
                <div class="tab-content">
                    @if($activeTab === 'taxes')
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold mb-0">{{ __('tenant::settings.product_taxes') }}</h5>
                            <button class="btn btn-primary rounded-pill px-4" onclick="openTaxModal('product')">
                                <i class="bi bi-plus-lg me-2"></i>{{ __('tenant::settings.add_tax') }}
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table id="productTaxTable" class="table table-hover w-100">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('tenant::settings.tax_label') }}</th>
                                        <th>{{ __('tenant::settings.tax_calculation') }}</th>
                                        <th>{{ __('tenant::settings.tax_rate') }}</th>
                                        <th>{{ __('tenant::settings.status') }}</th>
                                        <th class="text-end">{{ __('tenant::settings.actions') }}</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>

                    @elseif($activeTab === 'charges')
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold mb-0">{{ __('tenant::settings.charges') }}</h5>
                            <button class="btn btn-primary rounded-pill px-4" onclick="openChargeModal()">
                                <i class="bi bi-plus-lg me-2"></i>{{ __('tenant::settings.add_charge') }}
                            </button>
                        </div>
                        <div class="table-responsive mb-5">
                            <table id="chargesTable" class="table table-hover w-100">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('tenant::settings.charge_name') }}</th>
                                        <th>{{ __('tenant::settings.charge_value') }}</th>
                                        <th>{{ __('tenant::settings.charge_format') }}</th>
                                        <th>{{ __('tenant::settings.charge_type') }}</th>
                                        <th>{{ __('tenant::settings.is_taxable') }}</th>
                                        <th class="text-end">{{ __('tenant::settings.actions') }}</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-4 pt-4 border-top">
                            <h5 class="fw-bold mb-0">{{ __('tenant::settings.shipping_taxes') }}</h5>
                            <button class="btn btn-primary rounded-pill px-4" onclick="openTaxModal('shipping')">
                                <i class="bi bi-plus-lg me-2"></i>{{ __('tenant::settings.add_tax') }}
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table id="shippingTaxTable" class="table table-hover w-100">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('tenant::settings.tax_label') }}</th>
                                        <th>{{ __('tenant::settings.tax_calculation') }}</th>
                                        <th>{{ __('tenant::settings.tax_rate') }}</th>
                                        <th>{{ __('tenant::settings.status') }}</th>
                                        <th class="text-end">{{ __('tenant::settings.actions') }}</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>

                    @elseif($activeTab === 'regions')
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold mb-0">{{ __('tenant::settings.tax_regions') }}</h5>
                            <button class="btn btn-primary rounded-pill px-4" onclick="openRegionModal()">
                                <i class="bi bi-plus-lg me-2"></i>{{ __('tenant::settings.add_region') }}
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table id="regionsTable" class="table table-hover w-100">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('tenant::settings.region_name') }}</th>
                                        <th class="text-end">{{ __('tenant::settings.actions') }}</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Modals --}}
    @include('tenant::settings.tax.modals')

    <style>
        .custom-tabs .nav-link {
            color: #6c757d;
            font-weight: 500;
            transition: all 0.3s ease;
            border-bottom: 3px solid transparent;
        }

        .custom-tabs .nav-link:hover {
            background-color: rgba(0, 123, 255, 0.05);
            color: #007bff;
        }

        .custom-tabs .nav-link.active {
            background-color: transparent !important;
            color: #007bff !important;
            border-bottom: 3px solid #007bff;
        }

        .rounded-4 {
            border-radius: 1rem !important;
        }

        .table-light th {
            background-color: #f8f9fa;
            border-top: none;
        }
    </style>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            @if($activeTab === 'taxes')
                initializeProductTaxesTable();
            @elseif($activeTab === 'charges')
                initializeChargesTable();
                initializeShippingTaxesTable();
            @elseif($activeTab === 'regions')
                initializeRegionsTable();
            @endif
                            });

        function initializeProductTaxesTable() {
            $('#productTaxTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: { url: '{{ route('tenant.settings.crm.tax.data') }}', data: { type: 'product' } },
                columns: [
                    { data: 'taxlabel' },
                    {
                        data: 'method',
                        render: data => {
                            let badgeClass = 'bg-info-subtle text-info';
                            if (data === 'Compound') badgeClass = 'bg-warning-subtle text-warning';
                            if (data === 'Deducted') badgeClass = 'bg-danger-subtle text-danger';
                            return `<span class="badge ${badgeClass} rounded-pill px-3">${data || 'Simple'}</span>`;
                        }
                    },
                    { data: 'percentage', render: data => data + '%' },
                    { data: 'deleted', render: data => (data == 0) ? '<span class="badge bg-success-subtle text-success rounded-pill px-3">{{ __('tenant::settings.active') }}</span>' : '<span class="badge bg-secondary-subtle text-secondary rounded-pill px-3">{{ __('tenant::settings.inactive') }}</span>' },
                    {
                        data: 'taxid',
                        className: 'text-end',
                        render: (id, type, row) => `
                                                    <button class="btn btn-sm btn-light rounded-pill me-1" onclick='openTaxModal("product", ${JSON.stringify(row).replace(/'/g, "&apos;")})'>
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-light-danger rounded-pill" onclick="deleteTax('product', ${id})">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                `
                    }
                ]
            });
        }

        function initializeShippingTaxesTable() {
            $('#shippingTaxTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: { url: '{{ route('tenant.settings.crm.tax.data') }}', data: { type: 'shipping' } },
                columns: [
                    { data: 'taxlabel' },
                    {
                        data: 'method',
                        render: data => {
                            let badgeClass = 'bg-info-subtle text-info';
                            if (data === 'Compound') badgeClass = 'bg-warning-subtle text-warning';
                            if (data === 'Deducted') badgeClass = 'bg-danger-subtle text-danger';
                            return `<span class="badge ${badgeClass} rounded-pill px-3">${data || 'Simple'}</span>`;
                        }
                    },
                    { data: 'percentage', render: data => data + '%' },
                    { data: 'deleted', render: data => (data == 0) ? '<span class="badge bg-success-subtle text-success rounded-pill px-3">{{ __('tenant::settings.active') }}</span>' : '<span class="badge bg-secondary-subtle text-secondary rounded-pill px-3">{{ __('tenant::settings.inactive') }}</span>' },
                    {
                        data: 'taxid',
                        className: 'text-end',
                        render: (id, type, row) => `
                                                    <button class="btn btn-sm btn-light rounded-pill me-1" onclick='openTaxModal("shipping", ${JSON.stringify(row).replace(/'/g, "&apos;")})'>
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-light-danger rounded-pill" onclick="deleteTax('shipping', ${id})">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                `
                    }
                ]
            });
        }

        function initializeChargesTable() {
            $('#chargesTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: { url: '{{ route('tenant.settings.crm.tax.charges.data') }}' },
                columns: [
                    { data: 'name' },
                    { data: 'value' },
                    { data: 'format' },
                    { data: 'type' },
                    { data: 'istaxable', render: data => data ? '<span class="badge bg-info-subtle text-info px-3">{{ __('tenant::settings.yes') }}</span>' : '<span class="badge bg-light text-dark px-3">{{ __('tenant::settings.no') }}</span>' },
                    {
                        data: 'chargeid',
                        className: 'text-end',
                        render: (id, type, row) => `
                                                    <button class="btn btn-sm btn-light rounded-pill me-1" onclick='openChargeModal(${JSON.stringify(row).replace(/'/g, "&apos;")})'>
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-light-danger rounded-pill" onclick="deleteCharge(${id})">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                `
                    }
                ]
            });
        }

        function initializeRegionsTable() {
            $('#regionsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: { url: '{{ route('tenant.settings.crm.tax.regions.data') }}' },
                columns: [
                    { data: 'name' },
                    {
                        data: 'regionid',
                        className: 'text-end',
                        render: (id, type, row) => `
                                                    <button class="btn btn-sm btn-light rounded-pill me-1" onclick='openRegionModal(${JSON.stringify(row).replace(/'/g, "&apos;")})'>
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-light-danger rounded-pill" onclick="deleteRegion(${id})">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                `
                    }
                ]
            });
        }

        // Global Action Functions
        function toggleTaxFields() {
            const calc = $('input[name="method"]:checked').val();
            const typeValue = $('input[name="tax_type"]:checked').val();

            // Compound On display
            if (calc === 'Compound') {
                $('#compoundOnContainer').removeClass('d-none');
            } else {
                $('#compoundOnContainer').addClass('d-none');
            }

            // Tax Type display
            if (calc === 'Deducted') {
                $('#taxTypeContainer').addClass('d-none');
            } else {
                $('#taxTypeContainer').removeClass('d-none');
            }

            // Variable Rates display
            if (typeValue === 'Variable' && calc !== 'Deducted') {
                $('#variableRatesContainer').removeClass('d-none');
                $('#taxValueContainer').addClass('d-none');
                $('#taxPercentageInput').removeAttr('required');
                $('.region-rate-input[data-region="default"]').attr('required', 'required');
            } else {
                $('#variableRatesContainer').addClass('d-none');
                $('#taxValueContainer').removeClass('d-none');
                $('#taxPercentageInput').attr('required', 'required');
                $('.region-rate-input[data-region="default"]').removeAttr('required');
            }
        }

        function openTaxModal(type, data = null) {
            const form = $('#taxModalForm');
            const url = data ? `/settings/crm/tax/${data.taxid}` : '/settings/crm/tax';
            form.attr('action', url);

            // Handle Method Spoofing
            if (data) {
                $('#taxMethodPlaceholder').html('<input type="hidden" name="_method" value="PUT">');
            } else {
                $('#taxMethodPlaceholder').empty();
            }

            $('#taxModalLabel').text(data ? '{{ __('tenant::settings.edit_tax') }}' : '{{ __('tenant::settings.add_tax') }}');
            $('#taxType').val(type);
            $('#taxLabelInput').val(data ? data.taxlabel : '');
            $('#taxStatusInput').prop('checked', data ? (data.deleted == 0) : true);

            // Set radio values
            const method = data ? (data.method || 'Simple') : 'Simple';
            $(`input[name="method"][value="${method}"]`).prop('checked', true);

            const taxType = data ? (data.type || 'Fixed') : 'Fixed';
            $(`input[name="tax_type"][value="${taxType}"]`).prop('checked', true);

            $('#taxPercentageInput').val(data ? data.percentage : '');

            // Load Other Taxes for Compound On
            loadOtherTaxes(type, data ? data.taxid : null, data ? data.compoundon : null);

            // Load Regions for Variable Rates
            loadRegionalRates(data ? data.regions : null);

            setTimeout(toggleTaxFields, 100);
            $('#taxModal').modal('show');
        }

        function loadOtherTaxes(type, excludeId, selectedIds) {
            $.get('{{ route('tenant.settings.crm.tax.data') }}', { type: type }, function (res) {
                const select = $('#compoundOnInput').empty();
                const selected = selectedIds ? selectedIds.split(',') : [];
                res.data.forEach(tax => {
                    if (tax.taxid != excludeId) {
                        select.append(`<option value="${tax.taxid}" ${selected.includes(tax.taxid.toString()) ? 'selected' : ''}>${tax.taxlabel}</option>`);
                    }
                });
                if ($.fn.select2) {
                    select.select2({
                        dropdownParent: $('#taxModal'),
                        placeholder: '{{ __('tenant::settings.select_taxes') }}',
                        width: '100%'
                    });
                }
            });
        }

        function loadRegionalRates(regionsJson) {
            const rates = regionsJson ? JSON.parse(regionsJson) : {};
            $.get('{{ route('tenant.settings.crm.tax.regions.data') }}', function (res) {
                const tbody = $('#regionsRatesTableBody');
                tbody.find('tr:not(:first)').remove();
                tbody.find('.region-rate-input[data-region="default"]').val(rates['default'] || '');

                res.data.forEach(region => {
                    tbody.append(`
                                            <tr>
                                                <td>${region.name}</td>
                                                <td><input type="number" step="0.001" class="form-control form-control-sm rounded-pill region-rate-input" data-region="${region.regionid}" value="${rates[region.regionid] || ''}"></td>
                                            </tr>
                                        `);
                });
            });
        }

        function prepareTaxSubmit() {
            const regions = {};
            $('.region-rate-input').each(function () {
                const rid = $(this).data('region');
                regions[rid] = $(this).val();
            });
            $('#taxRegionsJson').val(JSON.stringify(regions));
        }

        function deleteTax(type, id) {
            if (confirm('{{ __('tenant::settings.confirm_delete') }}')) {
                $.ajax({
                    url: `/settings/crm/tax/${id}`,
                    method: 'DELETE',
                    data: { _token: '{{ csrf_token() }}', type: type },
                    success: function (res) {
                        location.reload();
                    }
                });
            }
        }

        function toggleChargeFields() {
            const typeValue = $('#chargeTypeInput').val();
            const isTaxable = $('#chargeIsTaxableInput').is(':checked');

            // Handle Type (Fixed/Variable)
            if (typeValue === 'Variable') {
                $('#chargeVariableRatesContainer').removeClass('d-none');
                $('#chargeValueContainer').addClass('d-none');
                $('#chargeValueInput').removeAttr('required');
                $('.charge-region-rate-input[data-region="default"]').attr('required', 'required');
            } else {
                $('#chargeVariableRatesContainer').addClass('d-none');
                $('#chargeValueContainer').removeClass('d-none');
                $('#chargeValueInput').attr('required', 'required');
                $('.charge-region-rate-input[data-region="default"]').removeAttr('required');
            }

            // Handle Taxable (Select Tax visibility)
            if (isTaxable) {
                $('#chargeTaxesContainer').removeClass('d-none');
                $('#chargeTaxesInput').attr('required', 'required');
            } else {
                $('#chargeTaxesContainer').addClass('d-none');
                $('#chargeTaxesInput').removeAttr('required');
            }
        }

        function openChargeModal(data = null) {
            const form = $('#chargeModalForm');
            const url = data ? `/settings/crm/tax/charges/${data.chargeid}` : '/settings/crm/tax/charges';
            form.attr('action', url);

            if (data) {
                $('#chargeMethodPlaceholder').html('<input type="hidden" name="_method" value="PUT">');
            } else {
                $('#chargeMethodPlaceholder').empty();
            }

            $('#chargeModalLabel').text(data ? '{{ __('tenant::settings.edit_charge') }}' : '{{ __('tenant::settings.add_charge') }}');
            $('#chargeNameInput').val(data ? data.name : '');
            $('#chargeFormatInput').val(data ? data.format : 'Flat');
            $('#chargeTypeInput').val(data ? data.type : 'Fixed');
            $('#chargeValueInput').val(data ? data.value : '');
            $('#chargeIsTaxableInput').prop('checked', data ? data.istaxable : true);

            // Load Regional Rates if Variable
            loadChargeRegionalRates(data ? data.regions : null);

            // Load Shipping Taxes for the Charge
            loadChargeTaxes(data ? data.taxes : null);

            setTimeout(toggleChargeFields, 100);
            $('#chargeModal').modal('show');
        }

        function loadChargeTaxes(selectedIds) {
            $.get('{{ route('tenant.settings.crm.tax.data') }}', { type: 'shipping' }, function (res) {
                const select = $('#chargeTaxesInput').empty();
                const selected = selectedIds ? selectedIds.split(',') : [];
                res.data.forEach(tax => {
                    select.append(`<option value="${tax.taxname}" ${selected.includes(tax.taxname) ? 'selected' : ''}>${tax.taxlabel}</option>`);
                });
                if ($.fn.select2) {
                    select.select2({
                        dropdownParent: $('#chargeModal'),
                        placeholder: '{{ __('tenant::settings.select_taxes') }}',
                        width: '100%'
                    });
                }
            });
        }

        function loadChargeRegionalRates(regionsJson) {
            const rates = regionsJson ? JSON.parse(regionsJson) : {};
            $.get('{{ route('tenant.settings.crm.tax.regions.data') }}', function (res) {
                const tbody = $('#chargeRegionsRatesTableBody');
                tbody.find('tr:not(:first)').remove();
                tbody.find('.charge-region-rate-input[data-region="default"]').val(rates['default'] || '');

                res.data.forEach(region => {
                    tbody.append(`
                                    <tr>
                                        <td>${region.name}</td>
                                        <td><input type="number" step="0.001" class="form-control form-control-sm rounded-pill charge-region-rate-input" data-region="${region.regionid}" value="${rates[region.regionid] || ''}"></td>
                                    </tr>
                                `);
                });
            });
        }

        $('#chargeModalForm').on('submit', function () {
            const regions = {};
            $('.charge-region-rate-input').each(function () {
                const rid = $(this).data('region');
                regions[rid] = $(this).val();
            });
            $('#chargeRegionsJson').val(JSON.stringify(regions));
        });

        function deleteCharge(id) {
            if (confirm('{{ __('tenant::settings.confirm_delete') }}')) {
                $.ajax({
                    url: `/settings/crm/tax/charges/${id}`,
                    method: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function (res) {
                        location.reload();
                    }
                });
            }
        }

        function openRegionModal(data = null) {
            const form = $('#regionModalForm');
            const url = data ? `/settings/crm/tax/regions/${data.regionid}` : '/settings/crm/tax/regions';
            form.attr('action', url);

            if (data) {
                $('#regionMethodPlaceholder').html('<input type="hidden" name="_method" value="PUT">');
            } else {
                $('#regionMethodPlaceholder').empty();
            }

            $('#regionModalLabel').text(data ? '{{ __('tenant::settings.edit_region') }}' : '{{ __('tenant::settings.add_region') }}');
            $('#regionNameInput').val(data ? data.name : '');
            $('#regionModal').modal('show');
        }

        function deleteRegion(id) {
            if (confirm('{{ __('tenant::settings.confirm_delete') }}')) {
                $.ajax({
                    url: `/settings/crm/tax/regions/${id}`,
                    method: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function (res) {
                        location.reload();
                    }
                });
            }
        }

        function showToast(message) {
            alert(message);
        }
    </script>
@endpush