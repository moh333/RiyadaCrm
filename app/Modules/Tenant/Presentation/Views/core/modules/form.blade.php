@extends('tenant::layout')

@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold mb-0">
                <i class="bi bi-pencil-square me-2 text-primary"></i>
                {{ isset($record) ? __('tenant::tenant.edit') : __('tenant::tenant.add_new_record') }}
                {{ vtranslate('SINGLE_' . $metadata->name, $metadata->name) }}
            </h3>
            <a href="{{ route('tenant.modules.index', $metadata->name) }}"
                class="btn btn-outline-secondary rounded-3 shadow-sm px-4">
                <i class="bi bi-arrow-left me-2"></i>{{ __('tenant::tenant.cancel') }}
            </a>
        </div>

        <form
            action="{{ isset($record) ? route('tenant.modules.update', [$metadata->name, $record->{$metadata->baseTableIndex}]) : route('tenant.modules.store', $metadata->name) }}"
            method="POST" class="needs-validation" novalidate>
            @csrf
            @if(isset($record))
                @method('PUT')
            @endif

            <div class="row">
                <div class="col-lg-12">
                    @php
                        $groupedFields = [];
                        foreach ($fields as $field) {
                            if ($field->presence == 0) {
                                $bLabel = $field->blockLabel ? vtranslate($field->blockLabel, $metadata->name) : __('tenant::tenant.general_information');
                                $groupedFields[$bLabel][] = $field;
                            }
                        }
                    @endphp

                    @foreach($groupedFields as $blockLabel => $blockFields)
                        <div class="card border-0 shadow-sm rounded-4 mb-4">
                            <div class="card-header bg-transparent border-bottom p-4">
                                <h5 class="fw-bold mb-0 text-primary">{{ $blockLabel }}</h5>
                            </div>
                            <div class="card-body p-4">
                                <div class="row">
                                    @foreach($blockFields as $field)
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold small text-uppercase">
                                                {{ $field->getLabel($metadata->name) }}
                                                @if($field->isMandatory) <span class="text-danger">*</span> @endif
                                            </label>

                                            @php
                                                $value = isset($record) ? ($record->{$field->column} ?? '') : '';
                                                $fieldName = $field->column;
                                            @endphp

                                            @if($field->uiType == 15 || $field->uiType == 16)
                                                <select name="{{ $fieldName }}"
                                                    class="form-select @error($fieldName) is-invalid @enderror" {{ $field->isMandatory ? 'required' : '' }}>
                                                    <option value="">{{ __('tenant::tenant.select_option') ?? '-- Select --' }}</option>
                                                    @foreach($field->picklistValues as $val)
                                                        <option value="{{ $val }}" {{ $value == $val ? 'selected' : '' }}>
                                                            {{ vtranslate($val, $metadata->name) }}</option>
                                                    @endforeach
                                                </select>
                                            @elseif($field->uiType == 19 || $field->uiType == 21)
                                                <textarea name="{{ $fieldName }}"
                                                    class="form-control @error($fieldName) is-invalid @enderror" rows="3" {{ $field->isMandatory ? 'required' : '' }}>{{ $value }}</textarea>
                                            @elseif($field->uiType == 5 || $field->uiType == 23)
                                                <input type="date" name="{{ $fieldName }}"
                                                    class="form-control @error($fieldName) is-invalid @enderror" value="{{ $value }}" {{ $field->isMandatory ? 'required' : '' }}>
                                            @elseif($field->uiType == 56)
                                                <div class="form-check form-switch mt-2">
                                                    <input type="hidden" name="{{ $fieldName }}" value="0">
                                                    <input class="form-check-input" type="checkbox" name="{{ $fieldName }}" value="1" {{ $value == 1 ? 'checked' : '' }}>
                                                </div>
                                            @else
                                                <input type="text" name="{{ $fieldName }}"
                                                    class="form-control @error($fieldName) is-invalid @enderror" value="{{ $value }}" {{ $field->isMandatory ? 'required' : '' }}>
                                            @endif

                                            @error($fieldName)
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-5">
                <div class="card-body p-4 text-end">
                    <button type="submit" class="btn btn-primary rounded-3 px-5 py-2 shadow-sm">
                        <i class="bi bi-save me-2"></i>{{ __('tenant::tenant.save_settings') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection