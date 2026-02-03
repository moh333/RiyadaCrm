@extends('tenant::layout')

@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold mb-0">
                <i class="bi bi-person-vcard me-2 text-primary"></i>
                {{ __('tenant::tenant.details') }}
                {{ vtranslate($metadata->name, $metadata->name) }}
            </h3>
            <div class="d-flex gap-2">
                <a href="{{ route('tenant.modules.index', $metadata->name) }}"
                    class="btn btn-outline-secondary rounded-3 shadow-sm px-4">
                    <i class="bi bi-arrow-left me-2"></i>{{ __('tenant::tenant.cancel') }}
                </a>
                @canModule($metadata->name, 'edit')
                <a href="{{ route('tenant.modules.edit', [$metadata->name, $record->crmid ?? $record->{$metadata->baseTableIndex}]) }}"
                    class="btn btn-primary rounded-3 shadow-sm px-4">
                    <i class="bi bi-pencil me-2"></i>{{ __('tenant::tenant.edit') }}
                </a>
                @endcanModule
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="overflow-auto mb-4 pb-2">
            <ul class="nav nav-pills gap-2 p-1 bg-white rounded-4 shadow-sm flex-nowrap text-nowrap" id="recordTabs"
                style="width: max-content; min-width: 100%;">
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab === 'details' ? 'active' : '' }} fw-bold px-4 py-2 rounded-3"
                        href="{{ route('tenant.modules.show', [$metadata->name, $record->crmid ?? $record->{$metadata->baseTableIndex}]) }}">
                        <i class="bi bi-info-circle me-2"></i>{{ __('tenant::tenant.details') }}
                    </a>
                </li>
                @foreach($relatedLists as $related)
                    @php
                        $isActivities = $related->relation_id === 'activities';
                        $tabKey = $isActivities ? 'activities' : 'rel-' . $related->relation_id;
                        $isActive = $activeTab === $tabKey;
                    @endphp
                    <li class="nav-item">
                        <a class="nav-link {{ $isActive ? 'active' : '' }} fw-bold px-4 py-2 rounded-3"
                            href="{{ route('tenant.modules.show', [$metadata->name, $record->crmid ?? $record->{$metadata->baseTableIndex}, $tabKey]) }}">
                            {{ vtranslate($related->label, $metadata->name) }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="tab-content" id="recordTabsContent">
            @if($activeTab === 'details')
                <!-- Details Tab -->
                <div class="tab-pane fade show active" role="tabpanel">
                    <div class="row">
                        <div class="col-lg-8">
                            @php
                                $groupedFields = [];
                                foreach ($fields as $field) {
                                    if (in_array($field->presence, [0, 2])) {
                                        $rawLabel = (string) ($field->blockLabel ?: 'LBL_GENERAL_INFORMATION');
                                        $translatedLabel = $field->getBlockLabel($metadata->name) ?: __('tenant::tenant.general_information');

                                        if (!isset($groupedFields[$rawLabel])) {
                                            $groupedFields[$rawLabel] = [
                                                'label' => $translatedLabel,
                                                'fields' => []
                                            ];
                                        }
                                        $groupedFields[$rawLabel]['fields'][] = $field;
                                    }
                                }
                            @endphp

                            @foreach($groupedFields as $rawLabel => $blockData)
                                <div class="card border-0 shadow-sm rounded-4 mb-4" data-block-label="{{ $rawLabel }}">
                                    <div class="card-header bg-transparent border-bottom p-4">
                                        <h5 class="fw-bold mb-0 text-primary">{{ $blockData['label'] }}</h5>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="row">
                                            @foreach($blockData['fields'] as $field)
                                                <div class="col-md-6 mb-3">
                                                    <label
                                                        class="form-label text-muted small text-uppercase fw-bold">{{ $field->getLabel($metadata->name) }}</label>
                                                    <div class="p-2 border rounded bg-light">
                                                        @php $val = $record->{$field->column} ?? '-'; @endphp
                                                        {{ in_array($field->uiType, [15, 16, 33]) ? vtranslate($val, $metadata->name) : $val }}
                                                    </div>
                                                </div>
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
            @else
                <!-- Related Lists Tab Content (Lazy Loaded) -->
                @php
                    $activeRelId = str_replace('rel-', '', $activeTab);
                    $activeRelation = $relatedLists->firstWhere('relation_id', $activeRelId);
                @endphp

                @if($activeRelation)
                    <div class="tab-pane fade show active" role="tabpanel">
                        <x-tenant::related-list-specific :module="$metadata" :record-id="$record->crmid" :relation="$activeRelation"
                            :activities="$activities ?? []" />
                    </div>
                @endif
            @endif
        </div>
    </div>

    <style>
        #recordTabs .nav-link {
            color: #6c757d;
            border: none;
            background: transparent;
            transition: all 0.2s;
        }

        #recordTabs .nav-link:hover {
            color: #4361ee;
            background: #f8f9ff;
        }

        #recordTabs .nav-link.active {
            color: #fff;
            background: #4361ee;
        }
    </style>
@endsection