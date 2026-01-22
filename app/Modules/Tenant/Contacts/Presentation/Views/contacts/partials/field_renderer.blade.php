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
                    'email' => 'Email',
                    'phone' => 'OfficePhone',
                    'mobile' => 'MobilePhone',
                    'homephone' => 'HomePhone',
                    'fax' => 'Fax',
                    'title' => 'Title',
                    'department' => 'Department',
                    'account_id' => 'AccountId',
                    'leadsource' => 'LeadSource',
                    'smownerid' => 'OwnerId',
                ];

                $prop = $propMap[$fieldName] ?? str_replace('_', '', ucwords($fieldName, '_'));

                // Handle Salutation which is in FullName VO
                if ($fieldName === 'salutation') {
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

    $value = old($fieldName, $currentValue ?? $defaultValue);

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
        <select name="{{ $inputName }}" class="form-select rounded-3" @if($isMandatory) required @endif>
            <option value="">{{ __('contacts::contacts.none') }}</option>
            @php
                $options = [];
                try {
                    $tableName = 'vtiger_' . $fieldName;
                    if (\Schema::connection('tenant')->hasTable($tableName)) {
                        $query = \DB::connection('tenant')->table($tableName);
                        if (\Schema::connection('tenant')->hasColumn($tableName, 'sortorderid')) {
                            $query->orderBy('sortorderid');
                        } elseif (\Schema::connection('tenant')->hasColumn($tableName, 'sortid')) {
                            $query->orderBy('sortid');
                        }
                        $options = $query->pluck($fieldName)->toArray();
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
                    if (\Schema::connection('tenant')->hasColumn($tableName, 'sortorderid')) {
                        $query->orderBy('sortorderid');
                    } elseif (\Schema::connection('tenant')->hasColumn($tableName, 'sortid')) {
                        $query->orderBy('sortid');
                    }
                    $options = $query->pluck($fieldName)->toArray();
                }
            } catch (\Exception $e) {
            }

            $selectedValues = is_array($value) ? $value : (is_string($value) ? explode('|##|', trim((string) $value, '|##|')) : []);
            $selectedValues = array_map('trim', array_filter($selectedValues));
        @endphp
        <select name="{{ $inputName }}[]" class="form-select rounded-3" multiple @if($isMandatory) required @endif>
            @foreach($options as $opt)
                <option value="{{ $opt }}" @if(in_array(trim((string) $opt), $selectedValues)) selected @endif>{{ $opt }}
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

        @if($allowMultiple)
            <small class="text-muted mt-1 d-block">
                <i class="bi bi-files me-1"></i>
                {{ __('contacts::contacts.multiple_files_allowed') }}
            </small>
        @endif

        {{-- Preview for new uploads (Single only for preview usually) --}}
        @if($uitype == 69)
            <div class="mt-2" id="preview_container_{{ $fieldName }}" style="display:none;">
                <label class="form-label small fw-bold text-primary">{{ __('contacts::contacts.new_preview') }}</label>
                <img id="preview_{{ $fieldName }}" src="" class="img-thumbnail rounded-3 shadow-sm" style="max-height: 150px;">
            </div>
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
                    }
                </script>
            @endonce
        @endif

        {{-- Display existing files/images in edit mode --}}
        @if($value && isset($contact))
            @php
                $existingFiles = [];
                if (is_string($value) && str_starts_with($value, '[')) {
                    $existingFiles = json_decode($value, true) ?? [$value];
                } else {
                    $existingFiles = [$value];
                }
            @endphp

            <div class="mt-3">
                <label
                    class="form-label fw-bold small text-muted text-uppercase">{{ $uitype == 69 ? __('contacts::contacts.existing_images') : __('contacts::contacts.existing_files') }}</label>
                <div class="row g-2">
                    @foreach($existingFiles as $index => $filePath)
                        @php $fileName = basename($filePath); @endphp
                        @if($uitype == 69)
                            <div class="col-md-4" id="edit-image-{{ $fieldName }}-{{ $index }}">
                                <div class="card border shadow-none rounded-3 overflow-hidden">
                                    <img src="{{ url('tenancy/assets/' . $filePath) }}" class="card-img-top"
                                        style="height: 100px; object-fit: cover;">
                                    <div class="card-footer bg-white p-1 d-flex justify-content-between">
                                        <a href="{{ url('tenancy/assets/' . $filePath) }}" target="_blank"
                                            class="btn btn-sm btn-link p-0 text-decoration-none"><i class="bi bi-eye"></i></a>
                                        <button type="button" class="btn btn-sm btn-link p-0 text-danger text-decoration-none"
                                            onclick="deleteFile('{{ $contact->getId() }}', '{{ $columnName }}', '{{ $filePath }}', 'edit-image-{{ $fieldName }}-{{ $index }}')"><i
                                                class="bi bi-trash"></i></button>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="col-12" id="edit-file-{{ $fieldName }}-{{ $index }}">
                                <div class="d-flex align-items-center justify-content-between bg-light p-2 rounded-3 border">
                                    <div class="d-flex align-items-center overflow-hidden">
                                        <i class="bi bi-file-earmark-text text-primary me-2 fs-5"></i>
                                        <span class="small text-truncate" title="{{ $fileName }}">{{ $fileName }}</span>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="{{ url('tenancy/assets/' . $filePath) }}" target="_blank" class="text-primary"><i
                                                class="bi bi-eye"></i></a>
                                        <button type="button" class="btn btn-sm p-0 text-danger border-0 bg-transparent"
                                            onclick="deleteFile('{{ $contact->getId() }}', '{{ $columnName }}', '{{ $filePath }}', 'edit-file-{{ $fieldName }}-{{ $index }}')"><i
                                                class="bi bi-trash"></i></button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif

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

    @elseif(in_array($uitype, [11, 1])) {{-- Phone / Text --}}
        <div class="input-group">
            @if($uitype == 11)
                <span class="input-group-text bg-light rounded-start-3"><i class="bi bi-telephone"></i></span>
            @endif
            <input type="text" name="{{ $inputName }}"
                class="form-control {{ $uitype == 11 ? 'rounded-end-3' : 'rounded-3' }}" value="{{ $value }}"
                placeholder="{{ $field->getLabel() }}" @if($isMandatory) required @endif>
        </div>

    @elseif($uitype == 10) {{-- Reference --}}
        <div class="input-group">
            <span class="input-group-text bg-light rounded-start-3"><i class="bi bi-search"></i></span>
            <select name="{{ $inputName }}" class="form-select rounded-end-3" @if($isMandatory) required @endif>
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
        <select name="{{ $inputName }}" class="form-select rounded-3" @if($isMandatory) required @endif>
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