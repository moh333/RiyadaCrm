@php
    $fieldName = $field->getFieldName();
    $columnName = $field->getColumnName();
    $uitype = $field->getUitype();
    $defaultValue = $field->getDefaultValue();

    // For custom fields, use column name (cf_xxx) for form input name
    // For standard fields, use field name
    $inputName = $field->isCustomField() ? $columnName : $fieldName;

    // For edit mode, handle current value
    $currentValue = null;
    if (isset($contact)) {
        try {
            if ($field->isCustomField()) {
                $currentValue = $contact->getCustomField($columnName);
            } else {
                // Property map for Contact entity getters
                $propMap = [
                    'firstname' => 'FirstName',
                    'lastname' => 'LastName',
                    'salutation' => 'Salutation',
                    'salutationtype' => 'Salutation',
                    'email' => 'Email',
                    'phone' => 'OfficePhone',
                    'mobile' => 'MobilePhone',
                    'homephone' => 'HomePhone',
                    'fax' => 'Fax',
                    'title' => 'Title',
                    'department' => 'Department',
                    'account_id' => 'AccountId',
                    'leadsource' => 'LeadSource',
                    'assistant' => 'Assistant',
                    'assistantphone' => 'AssistantPhone',
                    'birthday' => 'Birthday',
                    'description' => 'Description',
                    'smownerid' => 'OwnerId',
                    'mailingstreet' => 'MailingAddress',
                    'mailingcity' => 'MailingAddress',
                    'mailingstate' => 'MailingAddress',
                    'mailingzip' => 'MailingAddress',
                    'mailingcountry' => 'MailingAddress',
                    'mailingpobox' => 'MailingAddress',
                    'otherstreet' => 'AlternateAddress',
                    'othercity' => 'AlternateAddress',
                    'otherstate' => 'AlternateAddress',
                    'otherzip' => 'AlternateAddress',
                    'othercountry' => 'AlternateAddress',
                    'otherpobox' => 'AlternateAddress',
                    'imagename' => 'ImageName',
                ];

                $prop = $propMap[$fieldName] ?? str_replace('_', '', ucwords($fieldName, '_'));

                // Handle Salutation which is in FullName VO
                if (in_array($fieldName, ['salutation', 'salutationtype'])) {
                    $currentValue = $contact->getFullName()->getSalutation();
                } elseif ($fieldName === 'firstname') {
                    $currentValue = $contact->getFullName()->getFirstName();
                } elseif ($fieldName === 'lastname') {
                    $currentValue = $contact->getFullName()->getLastName();
                } else {
                    $method = 'get' . $prop;
                    if (method_exists($contact, $method)) {
                        $val = $contact->$method();
                        // Handle Value Objects
                        if (is_object($val)) {
                            if (method_exists($val, 'getEmail')) {
                                $currentValue = $val->getEmail();
                            } elseif (method_exists($val, 'getNumber')) {
                                $currentValue = $val->getNumber();
                            } elseif ($val instanceof \App\Modules\Tenant\Contacts\Domain\ValueObjects\Address) {
                                $currentValue = match ($fieldName) {
                                    'mailingstreet', 'otherstreet' => $val->getStreet(),
                                    'mailingcity', 'othercity' => $val->getCity(),
                                    'mailingstate', 'otherstate' => $val->getState(),
                                    'mailingzip', 'otherzip' => $val->getZip(),
                                    'mailingcountry', 'othercountry' => $val->getCountry(),
                                    'mailingpobox', 'otherpobox' => $val->getPoBox(),
                                    default => (string) $val,
                                };
                            } elseif ($val instanceof \DateTimeImmutable || $val instanceof \DateTime) {
                                $currentValue = $val->format('Y-m-d');
                            } elseif (method_exists($val, '__toString')) {
                                $currentValue = (string) $val;
                            } else {
                                $currentValue = $val;
                            }
                        } else {
                            $currentValue = $val;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $currentValue = null;
        }
    }

    // Value priority:
    // 1. old() - from validation errors (form resubmission)
    // 2. $currentValue - from existing contact (edit mode)
    // 3. $defaultValue - only for create mode (when no contact exists)
    
    // Check if we have old input (form was submitted with validation errors)
    $hasOldInput = session()->hasOldInput();
    
    if ($hasOldInput) {
        // Form was resubmitted - use old value (even if null/empty)
        $value = old($fieldName);
    } elseif (isset($contact)) {
        // Edit mode: use current value (even if null/empty)
        $value = $currentValue;
    } else {
        // Create mode: use default value
        $value = $defaultValue;
    }

    $isMandatory = $field->isMandatory();
    $helpInfo = $field->getHelpInfo();
@endphp

@php
    $colClass = 'col-md-6';
    if (in_array($uitype, [19, 21])) {
        $colClass = 'col-md-12';
    } elseif (in_array($fieldName, ['salutation', 'salutationtype'])) {
        $colClass = 'col-md-1';
    } elseif (in_array($fieldName, ['firstname'])) {
        $colClass = 'col-md-5';
    }
@endphp

<div class="{{ $colClass }} mb-3">
    <label class="form-label text-muted fw-bold small uppercase">
        {{ $field->getLabel() }}
        @if($isMandatory)
            <span class="text-danger">*</span>
        @endif
    </label>

    @if($fieldName === 'firstname' || $fieldName === 'lastname') {{-- Force name fields to be text --}}
        <input type="text" name="{{ $inputName }}" class="form-control rounded-3" value="{{ $value }}"
            placeholder="{{ $field->getLabel() }}" @if($isMandatory) required @endif>

    @elseif(in_array($fieldName, ['salutation', 'salutationtype']) || in_array($uitype, [15, 16, 55])) {{-- Force
        Salutation/Picklists to be dropdown --}}
        <select name="{{ $inputName }}" class="form-select rounded-3 select2"
            data-placeholder="{{ __('contacts::contacts.select_option') }}" @if($isMandatory) required @endif>
            <option value="">{{ __('contacts::contacts.none') }}</option>
            @php
                $options = [];
                try {
                    $tableName = 'vtiger_' . $fieldName;
                    // Check if table exists, or try vtiger_salutationtype if it's a salutation field
                    if (!\Schema::connection('tenant')->hasTable($tableName) && in_array($fieldName, ['salutation', 'salutationtype'])) {
                        $tableName = 'vtiger_salutationtype';
                    }

                    if (\Schema::connection('tenant')->hasTable($tableName)) {
                        $query = \DB::connection('tenant')->table($tableName);
                        // The column name is usually the same as field name, but for salutationtype it might be salutationtype
                        $column = \Schema::connection('tenant')->hasColumn($tableName, $fieldName) ? $fieldName :
                            (\Schema::connection('tenant')->hasColumn($tableName, 'salutationtype') ? 'salutationtype' :
                                (\Schema::connection('tenant')->hasColumn($tableName, 'salutation') ? 'salutation' : null));

                        if ($column) {
                            if (\Schema::connection('tenant')->hasColumn($tableName, 'sortorderid')) {
                                $query->orderBy('sortorderid');
                            } elseif (\Schema::connection('tenant')->hasColumn($tableName, 'sortid')) {
                                $query->orderBy('sortid');
                            }
                            $options = $query->pluck($column)->toArray();
                        }
                    }
                } catch (\Exception $e) {
                }

                if (in_array($fieldName, ['salutation', 'salutationtype'])) {
                    $options = ['Mr.', 'Ms.', 'Mrs.', 'Dr.', 'Prof.'];
                } elseif ($fieldName == 'leadsource') {
                    $options = ['Cold Call', 'Existing Customer', 'Self Generated', 'Employee', 'Partner', 'Public Relations', 'Direct Mail', 'Conference', 'Trade Show', 'Web Site', 'Word of mouth', 'Other'];
                }
            @endphp
            @foreach($options as $opt)
                <option value="{{ $opt }}" @if(trim((string) $value) === trim((string) $opt)) selected @endif>{{ $opt }}</option>
            @endforeach
        </select>

    @elseif($uitype == 33) {{-- Multi-Select --}}
        @php
            $options = [];
            try {
                $tableName = 'vtiger_' . $fieldName;
                if (\Schema::connection('tenant')->hasTable($tableName)) {
                    $query = \DB::connection('tenant')->table($tableName);
                    $column = \Schema::connection('tenant')->hasColumn($tableName, $fieldName) ? $fieldName :
                        (\Schema::connection('tenant')->hasColumn($tableName, $fieldName . 'type') ? $fieldName . 'type' : null);

                    if ($column) {
                        if (\Schema::connection('tenant')->hasColumn($tableName, 'sortorderid')) {
                            $query->orderBy('sortorderid');
                        } elseif (\Schema::connection('tenant')->hasColumn($tableName, 'sortid')) {
                            $query->orderBy('sortid');
                        }
                        $options = $query->pluck($column)->toArray();
                    }
                }
            } catch (\Exception $e) {
            }

            $rawValue = $value;
            // Handle JSON encoded array (from Laravel validation/old input)
            if (is_string($value) && str_starts_with($value, '[')) {
                $decoded = json_decode($value, true);
                if (is_array($decoded))
                    $rawValue = $decoded;
            }

            // Normal vtiger parsing
            if (is_array($rawValue)) {
                $selectedValues = $rawValue;
            } else {
                $strValue = (string) $rawValue;
                // Vtiger standard separator is |##|
                if (str_contains($strValue, '|##|')) {
                    $selectedValues = explode('|##|', $strValue);
                } else {
                    // Fallback or single value
                    $selectedValues = [$strValue];
                }
            }

            // Normalize values for comparison: trim and decode entities
            $selectedValues = array_map(function ($v) {
                return html_entity_decode(trim($v), ENT_QUOTES | ENT_HTML5);
            }, array_filter($selectedValues));
        @endphp
        <select name="{{ $inputName }}[]" class="form-select rounded-3 select2" multiple
            data-placeholder="{{ __('contacts::contacts.select_option') }}" @if($isMandatory) required @endif>
            @foreach($options as $opt)
                @php 
                    $normalizedOpt = html_entity_decode(trim($opt), ENT_QUOTES | ENT_HTML5); 
                @endphp
                <option value="{{ $opt }}" @if(in_array($normalizedOpt, $selectedValues)) selected @endif>{{ $opt }}
                </option>
            @endforeach
        </select>

    @elseif($uitype == 56) {{-- Checkbox --}}
        <div class="form-check form-switch mt-1">
            <input type="hidden" name="{{ $inputName }}" value="0">
            <input class="form-check-input" type="checkbox" name="{{ $inputName }}" value="1" id="field_{{ $fieldName }}"
                @if($value == '1' || $value === true) checked @endif>
            <label class="form-check-label" for="field_{{ $fieldName }}">{{ __('contacts::contacts.enabled') }}</label>
        </div>

    @elseif(in_array($uitype, [19, 21])) {{-- Textarea / Text Large --}}
        <textarea name="{{ $inputName }}" class="form-control rounded-3" rows="{{ $uitype == 19 ? 5 : 3 }}"
            placeholder="{{ $field->getLabel() }}" @if($isMandatory) required @endif>{{ $value }}</textarea>

    @elseif(in_array($uitype, [28, 69])) {{-- File / Image --}}
        @php
            // Get file upload configuration
            $allowMultiple = $field->getAllowMultipleFiles();
            $acceptableTypes = $field->getAcceptableFileTypes();

            // Build accept attribute
            $acceptAttr = '';
            if ($acceptableTypes) {
                // Parse acceptable types (stored as newline-separated or comma-separated)
                $types = array_filter(array_map('trim', preg_split('/[\n,]+/', $acceptableTypes)));
                $acceptAttr = '.' . implode(',.', $types);
            } else {
                // Default accept types
                $acceptAttr = $uitype == 69 ? 'image/*' : '.pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg';
            }

            // Determine input name for multiple files
            $fileInputName = $allowMultiple ? $inputName . '[]' : $inputName;
        @endphp

        <div class="input-group">
            <span class="input-group-text bg-light rounded-start-3">
                <i class="bi bi-{{ $uitype == 69 ? 'image' : 'file-earmark-arrow-up' }}"></i>
            </span>
            <input type="file" name="{{ $fileInputName }}" class="form-control rounded-end-3 file-upload-input"
                accept="{{ $acceptAttr }}" data-field-name="{{ $fieldName }}" data-uitype="{{ $uitype }}"
                data-acceptable-types="{{ $acceptableTypes ?? '' }}" @if($allowMultiple) multiple @endif @if($uitype == 69)
                onchange="if(typeof previewImage === 'function') previewImage(this, 'preview_{{ $fieldName }}')" @endif
                @if($isMandatory && !$value) required @endif>
        </div>

        @if($acceptableTypes)
            <small class="text-muted mt-1 d-block">
                <i class="bi bi-info-circle me-1"></i>
                {{ __('contacts::contacts.allowed_extensions') }}: {{ str_replace("\n", ', ', $acceptableTypes) }}
            </small>

        @endif

        {{-- Show current image in edit mode --}}
        @if($uitype == 69 && $value && isset($contact))
            <div class="mt-4">
                <div class="position-relative d-inline-block" id="current_image_{{ $fieldName }}">
                    <img src="{{ url('tenancy/assets/' . $value) }}" class="img-thumbnail rounded-3 shadow-sm"
                        style="max-height: 150px; max-width: 200px; object-fit: cover;">
                    <button type="button"
                        class="btn btn-sm btn-danger rounded-circle position-absolute top-0 start-100 translate-middle p-1"
                        style="line-height: 1;"
                        onclick="deleteFile('{{ $contact->getId() }}', '{{ $columnName }}', '{{ $value }}', 'current_image_{{ $fieldName }}')">
                        <i class="bi bi-x small"></i>
                    </button>
                </div>
            </div>
        @endif

        {{-- Preview for newly selected image --}}
        @if($uitype == 69)
            <div class="mt-4" id="preview_container_{{ $fieldName }}" style="display:none;">
                <div class="position-relative d-inline-block">
                    <img id="preview_{{ $fieldName }}" src="" class="img-thumbnail rounded-3 shadow-sm"
                        style="max-height: 150px; max-width: 200px; object-fit: cover;">
                    <button type="button"
                        class="btn btn-sm btn-danger rounded-circle position-absolute top-0 start-100 translate-middle p-1"
                        style="line-height: 1;" onclick="clearImagePreview('{{ $fieldName }}')">
                        <i class="bi bi-x small"></i>
                    </button>
                </div>
            </div>
        @endif

        @if($allowMultiple)
            <small class="text-muted mt-1 d-block">
                <i class="bi bi-files me-1"></i>
                {{ __('contacts::contacts.multiple_files_allowed') }}
            </small>
        @endif

        @once
            <script>
                if (typeof previewImage === 'undefined') {
                    window.previewImage = function (input, previewId) {
                        const preview = document.getElementById(previewId);
                        const container = document.getElementById('preview_container_' + input.getAttribute('data-field-name'));
                        if (input.files && input.files[0]) {
                            const reader = new FileReader();
                            reader.onload = function (e) {
                                preview.src = e.target.result;
                                container.style.display = 'block';
                            }
                            reader.readAsDataURL(input.files[0]);
                        }
                    }

                    window.clearImagePreview = function (fieldName) {
                        const container = document.getElementById('preview_container_' + fieldName);
                        const preview = document.getElementById('preview_' + fieldName);
                        const input = document.querySelector('input[data-field-name="' + fieldName + '"]');

                        if (container) container.style.display = 'none';
                        if (preview) preview.src = '';
                        if (input) input.value = '';
                    }
                }
            </script>
        @endonce

    @elseif($uitype == 5) {{-- Date --}}
        <input type="date" name="{{ $inputName }}" class="form-control rounded-3" value="{{ $value }}" @if($isMandatory)
        required @endif>

    @elseif(in_array($uitype, [6, 70, 50])) {{-- Date & Time --}}
        @php
            if ($value && str_contains($value, ' ')) {
                $value = str_replace(' ', 'T', $value);
            }
        @endphp
        <input type="datetime-local" name="{{ $inputName }}" class="form-control rounded-3" value="{{ $value }}"
            @if($isMandatory) required @endif>

    @elseif($uitype == 13) {{-- Email --}}
        <div class="input-group">
            <span class="input-group-text bg-light rounded-start-3"><i class="bi bi-envelope"></i></span>
            <input type="email" name="{{ $inputName }}" class="form-control rounded-end-3" value="{{ $value }}"
                placeholder="email@example.com" @if($isMandatory) required @endif>
        </div>

    @elseif($uitype == 11 || str_contains(strtolower($fieldName), 'phone') || str_contains(strtolower($fieldName), 'mobile'))
        {{-- Phone --}}
        <input type="tel" name="{{ $inputName }}" class="form-control rounded-3 phone-input" value="{{ $value }}"
            placeholder="{{ $field->getLabel() }}" @if($isMandatory) required @endif>

    @elseif($uitype == 1) {{-- Text --}}
        <input type="text" name="{{ $inputName }}" class="form-control rounded-3" value="{{ $value }}"
            placeholder="{{ $field->getLabel() }}" @if($isMandatory) required @endif>

    @elseif($uitype == 10) {{-- Reference --}}
        <div class="input-group">
            <span class="input-group-text bg-light rounded-start-3"><i class="bi bi-search"></i></span>
            <select name="{{ $inputName }}" class="form-select rounded-end-3 select2"
                data-placeholder="{{ __('contacts::contacts.select_option') }}" @if($isMandatory) required @endif>
                <option value="">{{ __('contacts::contacts.select_option') }}</option>
                @if($value)
                    @php
                        $label = 'ID: ' . $value;
                        if ($fieldName == 'account_id') {
                            try {
                                $account = \DB::connection('tenant')->table('vtiger_account')->where('accountid', $value)->first();
                                if ($account)
                                    $label = $account->accountname;
                            } catch (\Exception $e) {
                            }
                        }
                    @endphp
                    <option value="{{ $value }}" selected>{{ $label }}</option>
                @endif
                {{-- Standard options could be added here or via AJAX --}}
                @if($fieldName == 'account_id' && !$value)
                    {{-- Mock some options if empty for now --}}
                    <option value="1">Admin Account</option>
                @endif
            </select>
        </div>

    @elseif(in_array($uitype, [7, 71, 72, 9])) {{-- Numeric / Percent / Currency --}}
        <div class="input-group">
            @if($uitype == 72)
                <span class="input-group-text bg-light rounded-start-3"><i class="bi bi-currency-dollar"></i></span>
            @elseif($uitype == 9)
                <span class="input-group-text bg-light rounded-start-3"><i class="bi bi-percent"></i></span>
            @endif
            <input type="number" name="{{ $inputName }}" class="form-control rounded-3" value="{{ $value }}" @if($uitype == 7)
            step="1" @else step="0.01" @endif placeholder="{{ $field->getLabel() }}" @if($isMandatory) required @endif>
        </div>

    @elseif(in_array($uitype, [52, 53, 77])) {{-- User / Owner --}}
        <select name="{{ $inputName }}" class="form-select rounded-3 select2"
            data-placeholder="{{ __('contacts::contacts.select_option') }}" @if($isMandatory) required @endif>
            @php
                $users = \DB::connection('tenant')->table('users')->select('id', 'name')->get();
            @endphp
            @foreach($users as $user)
                <option value="{{ $user->id }}" @if($value == $user->id) selected @endif>{{ $user->name }}</option>
            @endforeach
        </select>

    @else {{-- Default text input --}}
        <input type="text" name="{{ $inputName }}" class="form-control rounded-3" value="{{ $value }}"
            placeholder="{{ $field->getLabel() }}" @if($isMandatory) required @endif>
    @endif

    @if($helpInfo)
        <div class="form-text mt-1 small text-muted"><i class="bi bi-info-circle me-1"></i>{{ $helpInfo }}</div>
    @endif
</div>