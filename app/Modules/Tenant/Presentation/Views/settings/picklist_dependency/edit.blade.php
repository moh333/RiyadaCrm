@extends('tenant::layout')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="fw-bold mb-1">{{ __('tenant::settings.configure_dependency') ?? 'Configure Dependency' }}
                        </h2>
                        <p class="text-muted">
                            {{ __('tenant::settings.dependency_for') ?? 'Dependency for' }}:
                            <strong>{{ vtranslate($sourceFieldLabel, $module) }}</strong> →
                            <strong>{{ vtranslate($targetFieldLabel, $module) }}</strong>
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
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            {{ __('tenant::settings.dependency_instruction') ?? 'Select which target field values should be available for each source field value. Click on cells to toggle selection.' }}
                        </div>

                        <form id="dependencyMappingForm">
                            <input type="hidden" name="module" value="{{ $module }}">
                            <input type="hidden" name="source_field" value="{{ $sourceField }}">
                            <input type="hidden" name="target_field" value="{{ $targetField }}">

                            <div class="table-responsive">
                                <table class="table table-bordered dependency-matrix">
                                    <thead>
                                        <tr>
                                            <th class="bg-light">{{ vtranslate($sourceFieldLabel, $module) }} \
                                                {{ vtranslate($targetFieldLabel, $module) }}
                                            </th>
                                            @foreach($targetValues as $targetValue)
                                                <th class="text-center bg-light">
                                                    {{ vtranslate($targetValue->{$targetField}, $module) }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($sourceValues as $sourceValue)
                                            @php
                                                $sourceVal = $sourceValue->{$sourceField};
                                                $existingMapping = $existingMappings->get($sourceVal);
                                                $selectedTargets = $existingMapping ? json_decode($existingMapping->targetvalues, true) : [];
                                            @endphp
                                            <tr>
                                                <td class="bg-light fw-semibold">{{ vtranslate($sourceVal, $module) }}</td>
                                                @foreach($targetValues as $targetValue)
                                                    @php
                                                        $targetVal = $targetValue->{$targetField};
                                                        $isSelected = in_array($targetVal, $selectedTargets);
                                                    @endphp
                                                    <td class="text-center dependency-cell {{ $isSelected ? 'selected' : '' }}"
                                                        data-source="{{ $sourceVal }}" data-target="{{ $targetVal }}"
                                                        style="cursor: pointer;">
                                                        @if($isSelected)
                                                            <i class="bi bi-check-circle-fill text-success"></i>
                                                        @else
                                                            <i class="bi bi-circle text-muted"></i>
                                                        @endif
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="text-end mt-4">
                                <button type="button" class="btn btn-outline-secondary me-2" id="selectAllBtn">
                                    <i
                                        class="bi bi-check-all me-2"></i>{{ __('tenant::settings.select_all') ?? 'Select All' }}
                                </button>
                                <button type="button" class="btn btn-outline-secondary me-2" id="clearAllBtn">
                                    <i class="bi bi-x-circle me-2"></i>{{ __('tenant::settings.clear_all') ?? 'Clear All' }}
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i
                                        class="bi bi-save me-2"></i>{{ __('tenant::settings.save_dependency') ?? 'Save Dependency' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .dependency-matrix th,
        .dependency-matrix td {
            padding: 1rem;
            vertical-align: middle;
        }

        .dependency-cell {
            transition: all 0.2s;
        }

        .dependency-cell:hover {
            background-color: #f8f9fa;
        }

        .dependency-cell.selected {
            background-color: #d1fae5;
        }

        .dependency-cell i {
            font-size: 1.5rem;
        }
    </style>
@endsection

@push('scripts')
    <script>
        // Toggle cell selection
        $(document).on('click', '.dependency-cell', function () {
            $(this).toggleClass('selected');

            if ($(this).hasClass('selected')) {
                $(this).html('<i class="bi bi-check-circle-fill text-success"></i>');
            } else {
                $(this).html('<i class="bi bi-circle text-muted"></i>');
            }
        });

        // Select all
        $('#selectAllBtn').on('click', function () {
            $('.dependency-cell').addClass('selected').html('<i class="bi bi-check-circle-fill text-success"></i>');
        });

        // Clear all
        $('#clearAllBtn').on('click', function () {
            $('.dependency-cell').removeClass('selected').html('<i class="bi bi-circle text-muted"></i>');
        });

        // Form submission
        $('#dependencyMappingForm').on('submit', function (e) {
            e.preventDefault();

            const module = $('input[name="module"]').val();
            const sourceField = $('input[name="source_field"]').val();
            const targetField = $('input[name="target_field"]').val();

            // Collect mappings
            const mappings = {};
            $('.dependency-cell.selected').each(function () {
                const source = $(this).data('source');
                const target = $(this).data('target');

                if (!mappings[source]) {
                    mappings[source] = [];
                }
                mappings[source].push(target);
            });

            $.ajax({
                url: '{{ route("tenant.settings.crm.picklist-dependency.store") }}',
                method: 'POST',
                data: {
                    module: module,
                    source_field: sourceField,
                    target_field: targetField,
                    mappings: mappings,
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    alert('{{ __("tenant::settings.dependency_saved_successfully") ?? "Dependency saved successfully" }}');
                    window.location.href = '{{ route("tenant.settings.crm.picklist-dependency.index") }}';
                },
                error: function (xhr) {
                    const error = xhr.responseJSON?.error || '{{ __("tenant::settings.error_saving_dependency") ?? "Error saving dependency" }}';
                    alert(error);
                }
            });
        });
    </script>
@endpush