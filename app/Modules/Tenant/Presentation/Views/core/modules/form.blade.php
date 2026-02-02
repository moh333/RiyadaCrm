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
                                        @include('tenant::core.modules.partials.field_renderer', [
                                            'field' => $field,
                                            'metadata' => $metadata,
                                            'record' => $record ?? null
                                        ])
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
    @push('scripts')
        <script>
            $(document).ready(function() {
                // Initialize Select2 for standard selects
                $('.select2').each(function() {
                    $(this).select2({
                        theme: 'bootstrap-5',
                        width: '100%',
                        dropdownParent: $(this).parent()
                    });
                });

                // Picklist Dependencies logic
                const dependencies = @json($picklistDependencies ?? []);
                if (dependencies.length > 0) {
                    const groupedDeps = {};
                    dependencies.forEach(dep => {
                        const key = `${dep.sourcefield}__${dep.targetfield}`;
                        if (!groupedDeps[key]) {
                            groupedDeps[key] = {
                                source: dep.sourcefield,
                                target: dep.targetfield,
                                mappings: {}
                            };
                        }
                        try {
                            groupedDeps[key].mappings[dep.sourcevalue] = JSON.parse(dep.targetvalues);
                        } catch(e) {
                            console.error('Failed to parse dependency target values', e);
                        }
                    });

                    Object.values(groupedDeps).forEach(dep => {
                        const $source = $(`[data-fieldname="${dep.source}"]`);
                        const $target = $(`[data-fieldname="${dep.target}"]`);

                        if ($source.length && $target.length) {
                            $source.on('change', function() {
                                const val = $(this).val();
                                const allowedValues = dep.mappings[val];
                                
                                // Store current value to re-select if still allowed
                                const currentVal = $target.val();

                                if (val && allowedValues) {
                                    $target.find('option').each(function() {
                                        const optVal = $(this).val();
                                        if (optVal === "" || allowedValues.includes(optVal)) {
                                            $(this).prop('disabled', false).show();
                                        } else {
                                            $(this).prop('disabled', true).hide();
                                        }
                                    });
                                } else {
                                    // If source is empty, show all?
                                    // In Vtiger, typically if source is empty, target is also restricted or empty depending on config.
                                    // Let's show all for now if no specific mapping, or hide all if source has value but no mapping.
                                    if (!val) {
                                         $target.find('option').prop('disabled', false).show();
                                    } else {
                                         $target.find('option').each(function() {
                                             if ($(this).val() === "") $(this).prop('disabled', false).show();
                                             else $(this).prop('disabled', true).hide();
                                         });
                                    }
                                }

                                // Reset target if current value is now disabled
                                if (currentVal && $target.find(`option[value="${currentVal}"]`).prop('disabled')) {
                                    $target.val('').trigger('change.select2');
                                } else {
                                    $target.trigger('change.select2');
                                }
                                
                                // Re-initialize select2 to reflect hidden options (some browsers/versions need this)
                                if ($target.data('select2')) {
                                    $target.select2('destroy');
                                    $target.select2({
                                        theme: 'bootstrap-5',
                                        width: '100%',
                                        dropdownParent: $target.parent()
                                    });
                                }
                            });
                            
                            // Initialize on load
                            $source.trigger('change');
                        }
                    });
                }

                // Block Dependencies logic
                const blockDependencies = @json($blockDependencies ?? []);
                if (blockDependencies.length > 0) {
                    const blockDepsBySource = {};
                    blockDependencies.forEach(dep => {
                        if (!blockDepsBySource[dep.sourcefield]) {
                            blockDepsBySource[dep.sourcefield] = [];
                        }
                        blockDepsBySource[dep.sourcefield].push(dep);
                    });

                    Object.keys(blockDepsBySource).forEach(sourceField => {
                        const $source = $(`[data-fieldname="${sourceField}"]`);
                        if ($source.length) {
                            $source.on('change', function() {
                                const val = $(this).val();
                                const deps = blockDepsBySource[sourceField];
                                
                                // Group block labels by whether they should be shown or hidden for THIS value
                                const blocksToProcess = {};
                                deps.forEach(dep => {
                                    // If same block has multiple rules, last one wins or we can combine.
                                    // Usually it's: if value matches, show.
                                    if (dep.sourcevalue === val) {
                                        blocksToProcess[dep.blocklabel] = dep.display_status;
                                    } else if (!blocksToProcess[dep.blocklabel]) {
                                        // If no match found yet for this block, default to inverse of the rule?
                                        // Or just hide if it's a "controlled" block.
                                        blocksToProcess[dep.blocklabel] = !dep.display_status;
                                    }
                                });

                                Object.keys(blocksToProcess).forEach(blockLabel => {
                                    const $block = $(`[data-block-label="${blockLabel}"]`);
                                    if ($block.length) {
                                        if (blocksToProcess[blockLabel]) {
                                            $block.show();
                                        } else {
                                            $block.hide();
                                        }
                                    }
                                });
                            });
                            // Initialize on load
                            $source.trigger('change');
                        }
                    });
                }

                // Initialize AJAX Select2 for reference fields
                $('.select2-ajax').each(function() {
                    const module = $(this).data('module');
                    const field = $(this).data('field');

                    $(this).select2({
                        theme: 'bootstrap-5',
                        width: '100%',
                        dropdownParent: $(this).parent(),
                        ajax: {
                            url: `{{ url('modules') }}/${module}/reference-search/${field}`,
                            dataType: 'json',
                            delay: 250,
                            data: function (params) {
                                return {
                                    q: params.term, // search term
                                    page: params.page
                                };
                            },
                            processResults: function (data, params) {
                                params.page = params.page || 1;
                                return {
                                    results: data.items,
                                    pagination: {
                                        more: (params.page * 30) < data.total_count
                                    }
                                };
                            },
                            cache: true
                        },
                        placeholder: '{{ __("tenant::tenant.search_placeholder") }}',
                        minimumInputLength: 1,
                    });
                });

                // File validation
                $('form').on('submit', function (e) {
                    const fileInputs = $(this).find('.file-upload-input');
                    let hasError = false;

                    fileInputs.each(function () {
                        const acceptableTypes = $(this).data('acceptable-types');
                        if (acceptableTypes && this.files.length > 0) {
                            const allowedExtensions = acceptableTypes.split(/[\n,]+/).map(ext => ext.trim().toLowerCase()).filter(ext => ext);
                            
                            Array.from(this.files).forEach((file) => {
                                const fileName = file.name;
                                const fileExtension = fileName.substring(fileName.lastIndexOf('.') + 1).toLowerCase();

                                if (!allowedExtensions.includes(fileExtension)) {
                                    hasError = true;
                                    alert(`Invalid file extension: .${fileExtension}\nAllowed: ${allowedExtensions.join(', ')}`);
                                    $(this).val(''); 
                                }
                            });
                        }
                    });

                    if (hasError) {
                        e.preventDefault();
                    }
                });
            });
        </script>
    @endpush
@endsection