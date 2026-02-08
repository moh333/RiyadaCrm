{{-- Tax Modal --}}
<div class="modal fade" id="taxModal" tabindex="-1" aria-labelledby="taxModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="taxModalLabel">Add/Edit Tax</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="taxModalForm" method="POST" action="">
                @csrf
                <div id="taxMethodPlaceholder"></div>
                <div class="modal-body p-4">
                    <input type="hidden" name="type" id="taxType">
                    {{-- Hidden field for regional rates JSON --}}
                    <input type="hidden" name="regions" id="taxRegionsJson">

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">{{ __('tenant::settings.tax_label') }} *</label>
                            <input type="text" name="taxlabel" id="taxLabelInput" class="form-control rounded-pill px-3"
                                required>
                        </div>
                        <div class="col-md-4">
                            <label
                                class="form-label fw-semibold d-block mb-3">{{ __('tenant::settings.status') }}</label>
                            <div class="form-check form-switch p-0 ps-5">
                                <input class="form-check-input ms-0" type="checkbox" name="status" id="taxStatusInput"
                                    value="1" checked>
                                <label class="form-check-label ms-2"
                                    for="taxStatusInput">{{ __('tenant::settings.active') }}</label>
                            </div>
                        </div>

                        <div class="col-12 mt-4">
                            <label
                                class="form-label fw-semibold d-block mb-2">{{ __('tenant::settings.tax_calculation') }}</label>
                            <div class="btn-group w-100 custom-radio-group" role="group">
                                <input type="radio" class="btn-check" name="method" id="calcSimple" value="Simple"
                                    checked onchange="toggleTaxFields()">
                                <label class="btn btn-outline-primary py-2" for="calcSimple">
                                    <i class="bi bi-circle-fill me-2 small"></i>{{ __('tenant::settings.simple') }}
                                </label>

                                <input type="radio" class="btn-check" name="method" id="calcCompound" value="Compound"
                                    onchange="toggleTaxFields()">
                                <label class="btn btn-outline-primary py-2" for="calcCompound">
                                    <i class="bi bi-stack me-2 small"></i>{{ __('tenant::settings.compound') }}
                                </label>

                                <input type="radio" class="btn-check" name="method" id="calcDeducted" value="Deducted"
                                    onchange="toggleTaxFields()">
                                <label class="btn btn-outline-primary py-2" for="calcDeducted">
                                    <i class="bi bi-dash-circle me-2 small"></i>{{ __('tenant::settings.deducted') }}
                                </label>
                            </div>
                        </div>

                        <div id="taxTypeContainer" class="col-12 mt-4">
                            <label
                                class="form-label fw-semibold d-block mb-2">{{ __('tenant::settings.tax_type') }}</label>
                            <div class="btn-group w-100 custom-radio-group" role="group">
                                <input type="radio" class="btn-check" name="tax_type" id="typeFixed" value="Fixed"
                                    checked onchange="toggleTaxFields()">
                                <label class="btn btn-outline-primary py-2" for="typeFixed">
                                    <i class="bi bi-lock me-2 small"></i>{{ __('tenant::settings.fixed') }}
                                </label>

                                <input type="radio" class="btn-check" name="tax_type" id="typeVariable" value="Variable"
                                    onchange="toggleTaxFields()">
                                <label class="btn btn-outline-primary py-2" for="typeVariable">
                                    <i class="bi bi-geo-alt me-2 small"></i>{{ __('tenant::settings.variable') }}
                                </label>
                            </div>
                        </div>

                        <div id="taxValueContainer" class="col-md-6 mt-3">
                            <label class="form-label fw-semibold">{{ __('tenant::settings.tax_rate') }}</label>
                            <div class="input-group">
                                <input type="number" step="0.001" name="percentage" id="taxPercentageInput"
                                    class="form-control px-3">
                                <span class="input-group-text px-3 font-monospace">%</span>
                            </div>
                        </div>

                        <div id="compoundOnContainer" class="col-12 d-none">
                            <label class="form-label fw-semibold">{{ __('tenant::settings.compound_on') }}</label>
                            <select name="compoundon[]" id="compoundOnInput" class="form-select select2 rounded-4 px-3"
                                multiple size="3">
                                {{-- Loaded via JS --}}
                            </select>
                            <small class="text-muted">{{ __('tenant::settings.select_taxes') }}</small>
                        </div>

                        <div id="variableRatesContainer" class="col-12 d-none">
                            <label class="form-label fw-semibold">{{ __('tenant::settings.regions_rates') }}</label>
                            <div class="table-responsive border rounded-4">
                                <table class="table table-sm mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>{{ __('tenant::settings.tax_regions') }}</th>
                                            <th style="width: 150px;">{{ __('tenant::settings.tax_rate') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="regionsRatesTableBody">
                                        {{-- Row for Default Region --}}
                                        <tr>
                                            <td class="align-middle fw-semibold">
                                                {{ __('tenant::settings.default_value') }}
                                            </td>
                                            <td><input type="number" step="0.001"
                                                    class="form-control form-control-sm rounded-pill region-rate-input"
                                                    data-region="default"></td>
                                        </tr>
                                        {{-- Other regions loaded via JS --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4"
                        data-bs-dismiss="modal">{{ __('tenant::settings.cancel') }}</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4" onclick="prepareTaxSubmit()">
                        <i class="bi bi-check-lg me-2"></i>{{ __('tenant::settings.save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Charge Modal --}}
<div class="modal fade" id="chargeModal" tabindex="-1" aria-labelledby="chargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="chargeModalLabel">Add/Edit Charge</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="chargeModalForm" method="POST" action="">
                @csrf
                <div id="chargeMethodPlaceholder"></div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">{{ __('tenant::settings.charge_name') }}</label>
                        <input type="text" name="name" id="chargeNameInput" class="form-control rounded-pill px-3"
                            required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ __('tenant::settings.charge_format') }}</label>
                            <select name="format" id="chargeFormatInput" class="form-select rounded-pill px-3">
                                <option value="Flat">{{ __('tenant::settings.direct_price') }}</option>
                                <option value="Percent">{{ __('tenant::settings.percent') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ __('tenant::settings.charge_type') }}</label>
                            <select name="type" id="chargeTypeInput" class="form-select rounded-pill px-3"
                                onchange="toggleChargeFields()">
                                <option value="Fixed">{{ __('tenant::settings.fixed') }}</option>
                                <option value="Variable">{{ __('tenant::settings.variable') }}</option>
                            </select>
                        </div>
                    </div>

                    <div id="chargeTaxesContainer" class="mb-3">
                        <label class="form-label fw-semibold">{{ __('tenant::settings.select_taxes') }} *</label>
                        <select name="taxes[]" id="chargeTaxesInput" class="form-select select2 rounded-4 px-3" multiple
                            required>
                            {{-- Loaded via JS --}}
                        </select>
                        <small
                            class="text-muted">{{ __('tenant::settings.select_taxes_on_charges') ?? 'Taxes applicable to this charge' }}</small>
                    </div>

                    <div id="chargeValueContainer" class="mb-3">
                        <label class="form-label fw-semibold">{{ __('tenant::settings.charge_value') }}</label>
                        <input type="number" step="0.001" name="value" id="chargeValueInput"
                            class="form-control rounded-pill px-3" required>
                    </div>

                    <div id="chargeVariableRatesContainer" class="col-12 mb-3 d-none">
                        <label class="form-label fw-semibold">{{ __('tenant::settings.regions_rates') }}</label>
                        <div class="table-responsive border rounded-4">
                            <table class="table table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('tenant::settings.tax_regions') }}</th>
                                        <th style="width: 150px;">{{ __('tenant::settings.charge_value') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="chargeRegionsRatesTableBody">
                                    {{-- Row for Default Region --}}
                                    <tr>
                                        <td class="align-middle fw-semibold">{{ __('tenant::settings.default_value') }}
                                        </td>
                                        <td><input type="number" step="0.001"
                                                class="form-control form-control-sm rounded-pill charge-region-rate-input"
                                                data-region="default"></td>
                                    </tr>
                                    {{-- Other regions loaded via JS --}}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch p-0 ps-5">
                            <input class="form-check-input ms-0" type="checkbox" name="istaxable"
                                id="chargeIsTaxableInput" value="1" checked onchange="toggleChargeFields()">
                            <label class="form-check-label ms-2 fw-semibold"
                                for="chargeIsTaxableInput">{{ __('tenant::settings.is_taxable') }}</label>
                        </div>
                    </div>
                    {{-- Hidden field for regional rates JSON --}}
                    <input type="hidden" name="regions" id="chargeRegionsJson">
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4"
                        data-bs-dismiss="modal">{{ __('tenant::settings.cancel') }}</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                        <i class="bi bi-check-lg me-2"></i>{{ __('tenant::settings.save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Region Modal --}}
<div class="modal fade" id="regionModal" tabindex="-1" aria-labelledby="regionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="regionModalLabel">Add/Edit Region</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="regionModalForm" method="POST" action="">
                @csrf
                <div id="regionMethodPlaceholder"></div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">{{ __('tenant::settings.region_name') }}</label>
                        <input type="text" name="name" id="regionNameInput" class="form-control rounded-pill px-3"
                            required>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4"
                        data-bs-dismiss="modal">{{ __('tenant::settings.cancel') }}</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                        <i class="bi bi-check-lg me-2"></i>{{ __('tenant::settings.save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .custom-radio-group .btn {
        border-color: #dee2e6;
        color: #6c757d;
        font-weight: 500;
        border-width: 1px;
    }

    .custom-radio-group .btn:hover {
        background-color: #f8f9fa;
        color: #007bff;
    }

    .custom-radio-group .btn-check:checked+.btn {
        background-color: #e7f1ff;
        border-color: #007bff;
        color: #007bff;
        z-index: 2;
    }

    .rounded-4 {
        border-radius: 1rem !important;
    }

    .btn-light-danger {
        color: #dc3545;
        background-color: #f8f9fa;
    }

    .btn-light-danger:hover {
        color: #fff;
        background-color: #dc3545;
    }
</style>