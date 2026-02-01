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
                                $bLabel = $field->getBlockLabel($metadata->name);
                                if (empty($bLabel)) {
                                    $bLabel = __('tenant::tenant.general_information');
                                }
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