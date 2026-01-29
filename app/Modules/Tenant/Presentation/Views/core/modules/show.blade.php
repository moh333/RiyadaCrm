@extends('tenant::layout')

@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold mb-0">
                <i class="bi bi-person-vcard me-2 text-primary"></i>{{ vtranslate($metadata->name, $metadata->name) }}
                {{ __('tenant::tenant.details') }}
            </h3>
            <div class="d-flex gap-2">
                <a href="{{ route('tenant.modules.index', $metadata->name) }}"
                    class="btn btn-outline-secondary rounded-3 shadow-sm px-4">
                    <i class="bi bi-arrow-left me-2"></i>{{ __('tenant::tenant.cancel') }}
                </a>
                @canModule($metadata->name, 'edit')
                <a href="{{ route('tenant.modules.edit', [$metadata->name, $record->{$metadata->baseTableIndex}]) }}"
                    class="btn btn-primary rounded-3 shadow-sm px-4">
                    <i class="bi bi-pencil me-2"></i>{{ __('tenant::tenant.edit') }}
                </a>
                @endcanModule
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                @php
                    $groupedFields = [];
                    foreach ($fields as $field) {
                        $bLabel = $field->blockLabel ? vtranslate($field->blockLabel, $metadata->name) : __('tenant::tenant.general_information');
                        $groupedFields[$bLabel][] = $field;
                    }
                @endphp

                @foreach($groupedFields as $blockLabel => $blockFields)
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-transparent border-bottom p-4">
                            <h5 class="fw-bold mb-0">{{ $blockLabel }}</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row">
                                @foreach($blockFields as $field)
                                    @if($field->presence == 0)
                                        <div class="col-md-6 mb-3">
                                            <label
                                                class="form-label text-muted small text-uppercase fw-bold">{{ $field->getLabel($metadata->name) }}</label>
                                            <div class="p-2 border rounded bg-light">
                                                @php $val = $record->{$field->column} ?? '-'; @endphp
                                                {{ in_array($field->uiType, [15, 16, 33]) ? vtranslate($val, $metadata->name) : $val }}
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-transparent border-bottom p-4">
                        <h5 class="fw-bold mb-0">{{ __('tenant::tenant.system_info') }}</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label
                                class="form-label text-muted small text-uppercase fw-bold">{{ __('tenant::tenant.id') }}</label>
                            <div class="fs-6">{{ $record->{$metadata->baseTableIndex} }}</div>
                        </div>
                        <div class="mb-3">
                            <label
                                class="form-label text-muted small text-uppercase fw-bold">{{ __('tenant::tenant.created_time') }}</label>
                            <div class="fs-6 text-muted">{{ $record->createdtime ?? 'N/A' }}</div>
                        </div>
                        <div class="mb-0">
                            <label
                                class="form-label text-muted small text-uppercase fw-bold">{{ __('tenant::tenant.modified_time') }}</label>
                            <div class="fs-6 text-muted">{{ $record->modifiedtime ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection