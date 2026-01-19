@if(isset($customFields) && $customFields->count() > 0)
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-bottom py-3 px-4">
            <h5 class="card-title fw-bold mb-0"><i class="bi bi-plus-circle me-2"></i>{{ __('contacts::contacts.custom_information') }}</h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                @foreach($customFields as $field)
                    <div class="col-md-6">
                        <label class="form-label text-muted fw-bold small uppercase">
                            {{ $field->getFieldLabel() }}
                            @if($field->isMandatory())
                                <span class="text-danger">*</span>
                            @endif
                        </label>
                        
                        @php
                            $fieldName = $field->getColumnName();
                            $value = old($fieldName, (isset($contact) ? $contact->getCustomField($fieldName) : null) ?? $field->getDefaultValue());
                            $uitype = $field->getUitype();
                        @endphp

                        @if($uitype->value == 15 || $uitype->value == 16) {{-- Picklist --}}
                            <select name="{{ $fieldName }}" class="form-select rounded-3" @if($field->isMandatory()) required @endif>
                                <option value="">{{ __('contacts::contacts.none') }}</option>
                                {{-- Picklist values would need to be fetched, but for now we assume simple text or handle later --}}
                            </select>
                        @elseif($uitype->value == 56) {{-- Checkbox --}}
                            <div class="form-check form-switch mt-1">
                                <input type="hidden" name="{{ $fieldName }}" value="0">
                                <input class="form-check-input" type="checkbox" name="{{ $fieldName }}" value="1" id="{{ $fieldName }}" @if($value) checked @endif>
                                <label class="form-check-label" for="{{ $fieldName }}">{{ $field->getFieldLabel() }}</label>
                            </div>
                        @elseif($uitype->value == 21) {{-- Textarea --}}
                            <textarea name="{{ $fieldName }}" class="form-control rounded-3" rows="3" @if($field->isMandatory()) required @endif>{{ $value }}</textarea>
                        @elseif($uitype->value == 5) {{-- Date --}}
                            <input type="date" name="{{ $fieldName }}" class="form-control rounded-3" value="{{ $value }}" @if($field->isMandatory()) required @endif>
                        @else {{-- Default text input --}}
                            <input type="text" name="{{ $fieldName }}" class="form-control rounded-3" value="{{ $value }}" placeholder="{{ $field->getFieldLabel() }}" @if($field->isMandatory()) required @endif>
                        @endif
                        
                        @if($field->getHelpInfo())
                            <div class="form-text mt-1 small text-muted"><i class="bi bi-info-circle me-1"></i>{{ $field->getHelpInfo() }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif
