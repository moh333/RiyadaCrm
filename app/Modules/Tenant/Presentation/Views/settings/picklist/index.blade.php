@extends('tenant::layout')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="fw-bold mb-1">{{ __('tenant::settings.picklist_management') ?? 'Picklist Management' }}
                        </h2>
                        <p class="text-muted">
                            {{ __('tenant::settings.picklist_description') ?? 'Manage dropdown field values across all CRM modules' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="moduleSelect"
                                    class="form-label fw-semibold">{{ __('tenant::settings.select_module') ?? 'Select Module' }}</label>
                                <select id="moduleSelect" class="form-select">
                                    <option value="">{{ __('tenant::settings.choose_module') ?? 'Choose a module...' }}
                                    </option>
                                    @foreach($modules as $module)
                                        <option value="{{ $module->name }}">{{ vtranslate($module->tablabel, 'Vtiger') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="fieldSelect"
                                    class="form-label fw-semibold">{{ __('tenant::settings.select_field') ?? 'Select Picklist Field' }}</label>
                                <select id="fieldSelect" class="form-select" disabled>
                                    <option value="">{{ __('tenant::settings.choose_field') ?? 'Choose a field...' }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div id="picklistValuesSection" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">{{ __('tenant::settings.picklist_values') ?? 'Picklist Values' }}</h5>
                                <button type="button" class="btn btn-primary" id="addValueBtn">
                                    <i
                                        class="bi bi-plus-circle me-2"></i>{{ __('tenant::settings.add_value') ?? 'Add Value' }}
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover" id="picklistValuesTable">
                                    <thead>
                                        <tr>
                                            <th>{{ __('tenant::settings.value') ?? 'Value' }}</th>
                                            <th>{{ __('tenant::settings.color') ?? 'Color' }}</th>
                                            <th>{{ __('tenant::settings.actions') ?? 'Actions' }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="picklistValuesBody">
                                        <!-- Values will be loaded here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Value Modal -->
    <div class="modal fade" id="addValueModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('tenant::settings.add_picklist_value') ?? 'Add Picklist Value' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addValueForm">
                        <div class="mb-3">
                            <label for="newValue" class="form-label">{{ __('tenant::settings.value') ?? 'Value' }}</label>
                            <input type="text" class="form-control" id="newValue" required>
                        </div>
                        <div class="mb-3">
                            <label for="newColor" class="form-label">{{ __('tenant::settings.color') ?? 'Color' }}</label>
                            <input type="color" class="form-control" id="newColor" value="#6366f1">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">{{ __('tenant::settings.cancel') ?? 'Cancel' }}</button>
                    <button type="button" class="btn btn-primary"
                        id="saveValueBtn">{{ __('tenant::settings.save') ?? 'Save' }}</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Value Modal -->
    <div class="modal fade" id="editValueModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('tenant::settings.edit_picklist_value') ?? 'Edit Picklist Value' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editValueForm">
                        <input type="hidden" id="editOldValue">
                        <div class="mb-3">
                            <label for="editValue" class="form-label">{{ __('tenant::settings.value') ?? 'Value' }}</label>
                            <input type="text" class="form-control" id="editValue" required>
                        </div>
                        <div class="mb-3">
                            <label for="editColor" class="form-label">{{ __('tenant::settings.color') ?? 'Color' }}</label>
                            <input type="color" class="form-control" id="editColor">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">{{ __('tenant::settings.cancel') ?? 'Cancel' }}</button>
                    <button type="button" class="btn btn-primary"
                        id="updateValueBtn">{{ __('tenant::settings.update') ?? 'Update' }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let currentModule = '';
        let currentField = '';

        // Module selection
        $('#moduleSelect').on('change', function () {
            currentModule = $(this).val();
            $('#fieldSelect').prop('disabled', !currentModule).html('<option value="">{{ __("tenant::settings.choose_field") ?? "Choose a field..." }}</option>');
            $('#picklistValuesSection').hide();

            if (currentModule) {
                loadPicklistFields(currentModule);
            }
        });

        // Field selection
        $('#fieldSelect').on('change', function () {
            currentField = $(this).val();
            if (currentField) {
                loadPicklistValues(currentField);
                $('#picklistValuesSection').show();
            } else {
                $('#picklistValuesSection').hide();
            }
        });

        // Load picklist fields for module
        function loadPicklistFields(module) {
            $.ajax({
                url: '{{ route("tenant.settings.crm.picklist.fields") }}',
                method: 'POST',
                data: {
                    module: module,
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    let options = '<option value="">{{ __("tenant::settings.choose_field") ?? "Choose a field..." }}</option>';
                    response.fields.forEach(field => {
                        options += `<option value="${field.fieldname}">${field.fieldlabel}</option>`;
                    });
                    $('#fieldSelect').html(options);
                }
            });
        }

        // Load picklist values
        function loadPicklistValues(fieldname) {
            $.ajax({
                url: '{{ route("tenant.settings.crm.picklist.values") }}',
                method: 'POST',
                data: {
                    fieldname: fieldname,
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    renderPicklistValues(response.values, fieldname);
                }
            });
        }

        // Render picklist values
        function renderPicklistValues(values, fieldname) {
            let html = '';
            values.forEach(value => {
                const fieldValue = value[fieldname];
                const color = value.color || '#6366f1';
                html += `
                        <tr>
                            <td>
                                <span class="badge" style="background-color: ${color}; color: white;">${fieldValue}</span>
                            </td>
                            <td>
                                <input type="color" value="${color}" class="form-control form-control-sm" style="width: 60px;" disabled>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary edit-value" data-value="${fieldValue}" data-color="${color}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger delete-value" data-value="${fieldValue}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
            });
            $('#picklistValuesBody').html(html);
        }

        // Add value button
        $('#addValueBtn').on('click', function () {
            $('#addValueModal').modal('show');
        });

        // Save new value
        $('#saveValueBtn').on('click', function () {
            const value = $('#newValue').val();
            const color = $('#newColor').val();

            if (!value) return;

            $.ajax({
                url: '{{ route("tenant.settings.crm.picklist.add") }}',
                method: 'POST',
                data: {
                    fieldname: currentField,
                    value: value,
                    color: color,
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    $('#addValueModal').modal('hide');
                    $('#addValueForm')[0].reset();
                    loadPicklistValues(currentField);
                    alert('{{ __("tenant::settings.value_added_successfully") ?? "Value added successfully" }}');
                },
                error: function () {
                    alert('{{ __("tenant::settings.error_adding_value") ?? "Error adding value" }}');
                }
            });
        });

        // Edit value
        $(document).on('click', '.edit-value', function () {
            const value = $(this).data('value');
            const color = $(this).data('color');

            $('#editOldValue').val(value);
            $('#editValue').val(value);
            $('#editColor').val(color || '#6366f1');
            $('#editValueModal').modal('show');
        });

        // Update value
        $('#updateValueBtn').on('click', function () {
            const oldValue = $('#editOldValue').val();
            const newValue = $('#editValue').val();
            const color = $('#editColor').val();

            $.ajax({
                url: '{{ route("tenant.settings.crm.picklist.update") }}',
                method: 'POST',
                data: {
                    fieldname: currentField,
                    old_value: oldValue,
                    new_value: newValue,
                    color: color,
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    $('#editValueModal').modal('hide');
                    loadPicklistValues(currentField);
                    alert('{{ __("tenant::settings.value_updated_successfully") ?? "Value updated successfully" }}');
                },
                error: function () {
                    alert('{{ __("tenant::settings.error_updating_value") ?? "Error updating value" }}');
                }
            });
        });

        // Delete value
        $(document).on('click', '.delete-value', function () {
            if (!confirm('{{ __("tenant::settings.confirm_delete_value") ?? "Are you sure you want to delete this value?" }}')) return;

            const value = $(this).data('value');

            $.ajax({
                url: '{{ route("tenant.settings.crm.picklist.delete") }}',
                method: 'POST',
                data: {
                    fieldname: currentField,
                    value: value,
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    loadPicklistValues(currentField);
                    alert('{{ __("tenant::settings.value_deleted_successfully") ?? "Value deleted successfully" }}');
                },
                error: function () {
                    alert('{{ __("tenant::settings.error_deleting_value") ?? "Error deleting value" }}');
                }
            });
        });
    </script>
@endpush