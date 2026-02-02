@extends('tenant::layout')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="fw-bold mb-1">
                            {{ __('tenant::settings.create_dependency') ?? 'Create Picklist Dependency' }}
                        </h2>
                        <p class="text-muted">
                            {{ __('tenant::settings.select_fields_dependency') ?? 'Select source and target fields to create a dependency' }}
                        </p>
                    </div>
                    <a href="{{ route('tenant.settings.crm.picklist-dependency.index') }}"
                        class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>{{ __('tenant::settings.back') ?? 'Back' }}
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form id="dependencyForm">
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <label for="moduleSelect"
                                        class="form-label fw-semibold">{{ __('tenant::settings.module') ?? 'Module' }}</label>
                                    <select id="moduleSelect" name="module" class="form-select" required>
                                        <option value="">{{ __('tenant::settings.choose_module') ?? 'Choose a module...' }}
                                        </option>
                                        @foreach($modules as $module)
                                            <option value="{{ $module->name }}">{{ vtranslate($module->tablabel, 'Vtiger') }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="sourceFieldSelect"
                                        class="form-label fw-semibold">{{ __('tenant::settings.source_field') ?? 'Source Field' }}</label>
                                    <select id="sourceFieldSelect" name="source_field" class="form-select" disabled
                                        required>
                                        <option value="">{{ __('tenant::settings.choose_field') ?? 'Choose a field...' }}
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="targetFieldSelect"
                                        class="form-label fw-semibold">{{ __('tenant::settings.target_field') ?? 'Target Field' }}</label>
                                    <select id="targetFieldSelect" name="target_field" class="form-select" disabled
                                        required>
                                        <option value="">{{ __('tenant::settings.choose_field') ?? 'Choose a field...' }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn btn-primary">
                                    <i
                                        class="bi bi-arrow-right me-2"></i>{{ __('tenant::settings.configure_dependency') ?? 'Configure Dependency' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let availableFields = [];

        // Module selection
        $('#moduleSelect').on('change', function () {
            const module = $(this).val();
            $('#sourceFieldSelect, #targetFieldSelect').prop('disabled', !module).html('<option value="">{{ __("tenant::settings.choose_field") ?? "Choose a field..." }}</option>');

            if (module) {
                loadPicklistFields(module);
            }
        });

        // Source field selection
        $('#sourceFieldSelect').on('change', function () {
            const sourceField = $(this).val();
            updateTargetFieldOptions(sourceField);
        });

        // Load picklist fields for module
        function loadPicklistFields(module) {
            $.ajax({
                url: '{{ route("tenant.settings.crm.picklist-dependency.fields") }}',
                method: 'POST',
                data: {
                    module: module,
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    availableFields = response.fields;
                    let options = '<option value="">{{ __("tenant::settings.choose_field") ?? "Choose a field..." }}</option>';
                    response.fields.forEach(field => {
                        options += `<option value="${field.fieldname}">${field.fieldlabel}</option>`;
                    });
                    $('#sourceFieldSelect').html(options);
                }
            });
        }

        // Update target field options (exclude source field)
        function updateTargetFieldOptions(sourceField) {
            let options = '<option value="">{{ __("tenant::settings.choose_field") ?? "Choose a field..." }}</option>';
            availableFields.forEach(field => {
                if (field.fieldname !== sourceField) {
                    options += `<option value="${field.fieldname}">${field.fieldlabel}</option>`;
                }
            });
            $('#targetFieldSelect').html(options).prop('disabled', !sourceField);
        }

        // Form submission
        $('#dependencyForm').on('submit', function (e) {
            e.preventDefault();

            const module = $('#moduleSelect').val();
            const sourceField = $('#sourceFieldSelect').val();
            const targetField = $('#targetFieldSelect').val();

            if (module && sourceField && targetField) {
                window.location.href = `{{ route('tenant.settings.crm.picklist-dependency.edit') }}?module=${module}&source_field=${sourceField}&target_field=${targetField}`;
            }
        });
    </script>
@endpush