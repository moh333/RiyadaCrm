@php
    $fieldName = $field->name;
    $columnName = $field->column;
    $uitype = $field->uiType;
    $inputName = $columnName;
    $isMandatory = $field->isMandatory;

    // Determine current value
    $value = null;
    if (isset($record)) {
        $value = $record->{$columnName} ?? null;
    }

    // Use old value if session has it (validation error fallback)
    if (session()->hasOldInput()) {
        $value = old($columnName);
    }
@endphp

<!-- Debug: Field {{ $fieldName }} ({{ $columnName }}), Logic: {{ $field->readonly ? 'Readonly' : 'Editable' }} -->

<div class="col-md-{{ in_array($uitype, [19, 21]) ? '12' : '6' }} mb-3">
    <label class="form-label fw-bold small text-uppercase">
        {{ $field->getLabel($metadata->name) }}
        @if($isMandatory) <span class="text-danger">*</span> @endif
    </label>

    @if(($uitype == 15 || $uitype == 16 || $uitype == 55) && ($uitype != 55 || !empty($field->picklistValues))) {{-- Picklist --}}
        <select name="{{ $inputName }}" 
            data-fieldname="{{ $fieldName }}"
            class="form-select select2 @error($columnName) is-invalid @enderror" 
            {{ $isMandatory ? 'required' : '' }} 
            {{ $field->readonly ? 'disabled' : '' }}>
            <option value="">{{ __('tenant::tenant.select_option') ?? '-- Select --' }}</option>
            @foreach($field->picklistValues as $val)
                <option value="{{ $val }}" {{ (string)$value === (string)$val ? 'selected' : '' }}>
                    {{ vtranslate($val, $metadata->name) }}
                </option>
            @endforeach
        </select>

    @elseif($uitype == 33) {{-- Multi-Select Picklist --}}
        @php
            $selectedValues = [];
            if (is_array($value)) {
                $selectedValues = $value;
            } elseif (is_string($value)) {
                $selectedValues = explode('|##|', $value);
            }
            $selectedValues = array_map('trim', $selectedValues);
        @endphp
        <select name="{{ $inputName }}[]" class="form-select select2 @error($columnName) is-invalid @enderror" multiple {{ $isMandatory ? 'required' : '' }} {{ $field->readonly ? 'disabled' : '' }}>
            @foreach($field->picklistValues as $val)
                <option value="{{ $val }}" {{ in_array($val, $selectedValues) ? 'selected' : '' }}>
                    {{ vtranslate($val, $metadata->name) }}
                </option>
            @endforeach
        </select>

    @elseif($uitype == 19 || $uitype == 21) {{-- Textarea --}}
        <textarea name="{{ $inputName }}" class="form-control @error($columnName) is-invalid @enderror" rows="{{ $uitype == 19 ? 5 : 3 }}" {{ $isMandatory ? 'required' : '' }} {{ $field->readonly ? 'readonly' : '' }}>{{ $value }}</textarea>

    @elseif($uitype == 5 || $uitype == 23) {{-- Date --}}
        <input type="date" name="{{ $inputName }}" class="form-control @error($columnName) is-invalid @enderror" value="{{ $value }}" {{ $isMandatory ? 'required' : '' }} {{ $field->readonly ? 'readonly' : '' }}>

    @elseif($uitype == 14) {{-- Time --}}
        <input type="time" name="{{ $inputName }}" class="form-control @error($columnName) is-invalid @enderror" value="{{ $value }}" {{ $isMandatory ? 'required' : '' }} {{ $field->readonly ? 'readonly' : '' }}>

    @elseif($uitype == 56) {{-- Checkbox --}}
        <div class="form-check form-switch mt-2">
            <input type="hidden" name="{{ $inputName }}" value="0">
            <input class="form-check-input" type="checkbox" name="{{ $inputName }}" value="1" {{ $value == 1 ? 'checked' : '' }} {{ $field->readonly ? 'disabled' : '' }}>
        </div>

    @elseif($uitype == 13) {{-- Email --}}
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input type="email" name="{{ $inputName }}" class="form-control @error($columnName) is-invalid @enderror" value="{{ $value }}" {{ $isMandatory ? 'required' : '' }} {{ $field->readonly ? 'readonly' : '' }}>
        </div>

    @elseif($uitype == 11) {{-- Phone --}}
        <input type="tel" name="{{ $inputName }}" class="form-control phone-input @error($columnName) is-invalid @enderror" value="{{ $value }}" {{ $isMandatory ? 'required' : '' }} {{ $field->readonly ? 'readonly' : '' }}>

    @elseif($uitype == 17) {{-- URL --}}
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-link-45deg"></i></span>
            <input type="url" name="{{ $inputName }}" class="form-control @error($columnName) is-invalid @enderror" value="{{ $value }}" {{ $isMandatory ? 'required' : '' }} {{ $field->readonly ? 'readonly' : '' }}>
        </div>

    @elseif(in_array($uitype, [10, 51, 52, 57, 58, 59, 66, 68, 73, 75, 76, 78, 80, 81])) {{-- Reference Fields --}}
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <select name="{{ $inputName }}" class="form-select select2-ajax @error($columnName) is-invalid @enderror" 
                data-module="{{ $metadata->name }}" 
                data-field="{{ $fieldName }}"
                {{ $isMandatory ? 'required' : '' }} {{ $field->readonly ? 'disabled' : '' }}>
                @if($value)
                    <option value="{{ $value }}" selected>{{ $record->{$columnName . '_label'} ?? "ID: $value" }}</option>
                @else
                    <option value="">{{ __('tenant::tenant.select_option') }}</option>
                @endif
            </select>
        </div>

    @elseif($uitype == 53 || $uitype == 77 || $uitype == 101) {{-- Assigned To / Owner / User --}}
        <select name="{{ $inputName }}" class="form-select select2 @error($columnName) is-invalid @enderror" {{ $isMandatory ? 'required' : '' }} {{ $field->readonly ? 'disabled' : '' }}>
            @if(!$isMandatory) <option value="">{{ __('tenant::tenant.select_option') }}</option> @endif
            <optgroup label="Users">
                @php
                    $users = DB::connection('tenant')->table('vtiger_users')->where('status', 'Active')->get();
                @endphp
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ $value == $user->id ? 'selected' : '' }}>
                        {{ trim($user->first_name . ' ' . $user->last_name) ?: $user->user_name }}
                    </option>
                @endforeach
            </optgroup>
            @if($uitype != 101) {{-- Groups --}}
            <optgroup label="Groups">
                @php
                    $groups = DB::connection('tenant')->table('vtiger_groups')->get();
                @endphp
                @foreach($groups as $group)
                    <option value="{{ $group->groupid }}" {{ $value == $group->groupid ? 'selected' : '' }}>
                        {{ $group->groupname }}
                    </option>
                @endforeach
            </optgroup>
            @endif
        </select>

    @elseif($uitype == 71 || $uitype == 72) {{-- Currency / Price --}}
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
            <input type="number" step="0.01" name="{{ $inputName }}" class="form-control @error($columnName) is-invalid @enderror" value="{{ $value }}" {{ $isMandatory ? 'required' : '' }} {{ $field->readonly ? 'readonly' : '' }}>
        </div>

    @elseif($uitype == 7 || $uitype == 9) {{-- Number / Percentage --}}
        <div class="input-group">
            <input type="number" name="{{ $inputName }}" class="form-control @error($columnName) is-invalid @enderror" value="{{ $value }}" {{ $isMandatory ? 'required' : '' }} {{ $field->readonly ? 'readonly' : '' }}>
            @if($uitype == 9) <span class="input-group-text">%</span> @endif
        </div>

    @elseif($uitype == 28 || $uitype == 69) {{-- File / Image --}}
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-{{ $uitype == 69 ? 'image' : 'file-earmark-arrow-up' }}"></i></span>
            <input type="file" name="{{ $inputName }}{{ $field->allowMultipleFiles ? '[]' : '' }}" 
                class="form-control file-upload-input @error($columnName) is-invalid @enderror" 
                @if($field->acceptableFileTypes) accept="{{ str_replace(["\n", "\r"], ',', $field->acceptableFileTypes) }}" @endif
                @if($field->allowMultipleFiles) multiple @endif
                data-acceptable-types="{{ $field->acceptableFileTypes }}"
                {{ $isMandatory && !$value ? 'required' : '' }} {{ $field->readonly ? 'disabled' : '' }}>
        </div>
        @if($value)
            <div class="mt-2">
                @if($uitype == 69)
                    <img src="{{ url('tenancy/assets/' . $value) }}" class="img-thumbnail" style="max-height: 100px;">
                @else
                    <a href="{{ url('tenancy/assets/' . $value) }}" target="_blank" class="small text-decoration-none">
                        <i class="bi bi-download me-1"></i>{{ basename($value) }}
                    </a>
                @endif
            </div>
        @endif

    @else {{-- Default: Text Input --}}
        <input type="text" name="{{ $inputName }}" class="form-control @error($columnName) is-invalid @enderror" value="{{ $value }}" {{ $isMandatory ? 'required' : '' }} {{ $field->readonly ? 'readonly' : '' }}>
    @endif

    @if($field->helpInfo)
        <div class="form-text small"><i class="bi bi-info-circle me-1"></i>{{ $field->helpInfo }}</div>
    @endif

    @error($columnName)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
