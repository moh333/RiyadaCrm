@extends('tenant::layout')

@section('title', __('reports::reports.create_report'))

@section('content')
    <div class="content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="row align-items-center mb-4">
                <div class="col">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1">
                            <li class="breadcrumb-item"><a
                                    href="{{ route('tenant.reports.index') }}">{{ __('reports::reports.reports') }}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">
                                {{ __('reports::reports.create_report') }}</li>
                        </ol>
                    </nav>
                    <h1 class="h3 mb-0 text-main fw-bold">{{ __('reports::reports.add_new_report') }}</h1>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white p-0 border-bottom">
                    <!-- Stepper -->
                    <div class="d-flex text-center stepper">
                        <div class="flex-fill py-3 border-end step-item active" id="step-1-indicator">
                            <div class="step-icon mb-1 mx-auto bg-primary text-white rounded-circle">1</div>
                            <div class="small fw-bold">{{ __('reports::reports.report_details') }}</div>
                        </div>
                        <div class="flex-fill py-3 border-end step-item" id="step-2-indicator">
                            <div class="step-icon mb-1 mx-auto bg-light text-muted rounded-circle">2</div>
                            <div class="small fw-bold">{{ __('reports::reports.select_columns') }}</div>
                        </div>
                        <div class="flex-fill py-3 step-item" id="step-3-indicator">
                            <div class="step-icon mb-1 mx-auto bg-light text-muted rounded-circle">3</div>
                            <div class="small fw-bold">{{ __('reports::reports.filters') }}</div>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4 p-lg-5">
                    <form id="report-form" action="{{ route('tenant.reports.store') }}" method="POST">
                        @csrf

                        <!-- Step 1: Report Details -->
                        <div class="step-content" id="step-1-content">
                            <div class="row g-4 justify-content-center">
                                <div class="col-md-8">
                                    <div class="mb-4">
                                        <label class="form-label fw-bold">{{ __('reports::reports.report_name') }} <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="reportname"
                                            class="form-control form-control-lg rounded-3 @error('reportname') is-invalid @enderror"
                                            placeholder="{{ __('reports::reports.enter_report_name') }}" required>
                                        @error('reportname') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-bold">{{ __('reports::reports.report_folder') }} <span
                                                class="text-danger">*</span></label>
                                        <select name="folderid"
                                            class="form-select form-select-lg rounded-3 @error('folderid') is-invalid @enderror"
                                            required>
                                            @foreach($folders as $folder)
                                                <option value="{{ $folder->folderid }}">{{ $folder->foldername }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-bold">{{ __('reports::reports.primary_module') }} <span
                                                class="text-danger">*</span></label>
                                        <select name="primarymodule" id="primary-module"
                                            class="form-select form-select-lg rounded-3 select2 @error('primarymodule') is-invalid @enderror"
                                            required>
                                            <option value="">{{ __('reports::reports.select_module') }}</option>
                                            @foreach($activeModules as $module)
                                                <option value="{{ $module->getName() }}">{{ $module->getLabel() }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mb-4">
                                        <label
                                            class="form-label fw-bold">{{ __('reports::reports.secondary_modules') }}</label>
                                        <select name="secondarymodules[]" id="secondary-modules"
                                            class="form-select rounded-3 select2" multiple>
                                            <!-- Will be populated via AJAX based on primary module -->
                                        </select>
                                        <div class="form-text mt-2">{{ __('reports::reports.secondary_modules_help') }}
                                        </div>
                                    </div>

                                    <div class="">
                                        <label class="form-label fw-bold">{{ __('reports::reports.description') }}</label>
                                        <textarea name="description" class="form-control rounded-3" rows="3"
                                            placeholder="{{ __('reports::reports.enter_description') }}"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Select Columns -->
                        <div class="step-content d-none" id="step-2-content">
                            <!-- Column selection UI -->
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <h6 class="fw-bold mb-3 d-flex align-items-center gap-2">
                                        <i class="bi bi-list-check text-primary"></i>
                                        {{ __('reports::reports.available_fields') }}
                                    </h6>
                                    <div class="available-fields-container border rounded-4 p-3 bg-light"
                                        style="max-height: 400px; overflow-y: auto;">
                                        <div id="fields-accordion" class="accordion accordion-flush">
                                            <!-- Content populated via JS -->
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="fw-bold mb-3 d-flex align-items-center gap-2">
                                        <i class="bi bi-layout-three-columns text-primary"></i>
                                        {{ __('reports::reports.selected_columns') }}
                                    </h6>
                                    <div class="selected-fields-container border rounded-4 p-3 bg-white"
                                        style="min-height: 400px;">
                                        <div id="selected-fields-list" class="d-flex flex-column gap-2">
                                            <!-- Dropped items go here -->
                                            <div class="text-center py-5 text-muted empty-msg">
                                                <i class="bi bi-hand-index-thumb mb-2 display-6"></i>
                                                <p>{{ __('reports::reports.drag_and_drop_fields') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Filters -->
                        <div class="step-content d-none" id="step-3-content">
                            @include('reports::partials.filters_step')
                        </div>

                        <div class="card-footer bg-white border-top p-4 d-flex justify-content-between align-items-center">
                            <button type="button" class="btn btn-outline-secondary px-4 rounded-pill d-none" id="prev-btn">
                                <i class="bi bi-arrow-left me-2"></i> {{ __('reports::reports.previous') }}
                            </button>
                            <div class="ms-auto d-flex gap-2">
                                <a href="{{ route('tenant.reports.index') }}"
                                    class="btn btn-link link-secondary px-4 text-decoration-none">{{ __('reports::reports.cancel') }}</a>
                                <button type="button" class="btn btn-primary px-5 rounded-pill shadow-sm" id="next-btn">
                                    {{ __('reports::reports.next') }} <i class="bi bi-arrow-right ms-2"></i>
                                </button>
                                <button type="submit" class="btn btn-success px-5 rounded-pill shadow-sm d-none"
                                    id="save-btn">
                                    <i class="bi bi-check2-circle me-2"></i> {{ __('reports::reports.save_report') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        .stepper .step-item {
            transition: all 0.3s ease;
        }

        .stepper .step-icon {
            width: 28px;
            height: 28px;
            line-height: 28px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .stepper .active .step-icon {
            transform: scale(1.2);
            box-shadow: 0 0 10px rgba(79, 70, 229, 0.4);
        }

        .stepper .active {
            border-bottom: 3px solid #4F46E5 !important;
        }

        .field-item {
            cursor: pointer;
            transition: all 0.2s;
        }

        .field-item:hover {
            background-color: rgba(79, 70, 229, 0.05);
        }

        .selected-field-row {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-right: 4px solid #4F46E5;
            transition: all 0.2s;
        }

        .selected-field-row:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
    </style>

    @push('scripts')
        <script>
            let currentStep = 1;
            const totalSteps = 3;

            function updateStep() {
                $('.step-content').addClass('d-none');
                $('#step-' + currentStep + '-content').removeClass('d-none');

                $('.step-item').removeClass('active').find('.step-icon').removeClass('bg-primary text-white').addClass('bg-light text-muted');
                for (let i = 1; i <= currentStep; i++) {
                    $('#step-' + i + '-indicator').addClass('active').find('.step-icon').addClass('bg-primary text-white').removeClass('bg-light text-muted');
                }

                if (currentStep === 1) {
                    $('#prev-btn').addClass('d-none');
                    $('#next-btn').removeClass('d-none');
                    $('#save-btn').addClass('d-none');
                } else if (currentStep === totalSteps) {
                    $('#prev-btn').removeClass('d-none');
                    $('#next-btn').addClass('d-none');
                    $('#save-btn').removeClass('d-none');
                } else {
                    $('#prev-btn').removeClass('d-none');
                    $('#next-btn').removeClass('d-none');
                    $('#save-btn').addClass('d-none');
                }
            }

            $('#next-btn').on('click', function () {
                if (currentStep < totalSteps) {
                    currentStep++;
                    updateStep();
                }
            });

            $('#prev-btn').on('click', function () {
                if (currentStep > 1) {
                    currentStep--;
                    updateStep();
                }
            });

            // Populate secondary modules when primary module changes
            $('#primary-module').on('change', function () {
                const moduleName = $(this).val();
                if (!moduleName) return;

                // Fetch valid secondary modules via AJAX
                $.get("{{ url('test/modules') }}/" + moduleName, function (data) {
                    const secondarySelect = $('#secondary-modules');
                    secondarySelect.empty();
                    if (data.relations) {
                        data.relations.forEach(rel => {
                            secondarySelect.append(`<option value="${rel.target}">${rel.target}</option>`);
                        });
                    }
                    secondarySelect.trigger('change');

                    // Step 2: Populate fields
                    populateFieldsStep(moduleName, data.fields);
                });
            });

            function populateFieldsStep(primaryModule, primaryFields) {
                const accordion = $('#fields-accordion');
                accordion.empty();

                // Add Primary Module Section
                let primaryHtml = `
                    <div class="accordion-item border-0 mb-2 overflow-hidden rounded-3">
                        <h2 class="accordion-header">
                            <button class="accordion-button bg-white fw-bold shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#fields-${primaryModule}">
                                ${primaryModule}
                            </button>
                        </h2>
                        <div id="fields-${primaryModule}" class="accordion-collapse collapse show">
                            <div class="accordion-body p-2">
                                <div class="list-group list-group-flush">
                `;

                primaryFields.forEach(field => {
                    primaryHtml += `
                        <div class="list-group-item field-item py-2 px-3 border-0 small d-flex align-items-center justify-content-between" 
                            data-module="${primaryModule}" data-field="${field.name}" data-label="${field.label}">
                            <span>${field.label}</span>
                            <i class="bi bi-plus-circle text-primary opacity-50"></i>
                        </div>
                    `;
                });

                primaryHtml += '</div></div></div></div>';
                accordion.append(primaryHtml);

                // Click handler for field items
                $('.field-item').off('click').on('click', function () {
                    const mod = $(this).data('module');
                    const field = $(this).data('field');
                    const label = $(this).data('label');
                    addFieldToSelected(mod, field, label);
                });
            }

            function addFieldToSelected(mod, field, label) {
                $('.empty-msg').hide();
                const value = mod + ':' + field;
                const exists = $('#selected-fields-list input[value="' + value + '"]').length > 0;

                if (exists) return;

                $('#selected-fields-list').append(`
                    <div class="selected-field-row p-3 rounded-3 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <i class="bi bi-grip-vertical text-muted"></i>
                            <div>
                                <div class="fw-bold small">${label}</div>
                                <div class="text-muted" style="font-size: 10px;">${mod}:${field}</div>
                            </div>
                        </div>
                        <input type="hidden" name="columns[]" value="${value}">
                        <button type="button" class="btn btn-link btn-sm text-danger p-0 delete-field">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </div>
                `);

                $('.delete-field').off('click').on('click', function () {
                    $(this).closest('.selected-field-row').remove();
                    if ($('#selected-fields-list .selected-field-row').length === 0) {
                        $('.empty-msg').show();
                    }
                });
            }
        </script>
    @endpush
@endsection