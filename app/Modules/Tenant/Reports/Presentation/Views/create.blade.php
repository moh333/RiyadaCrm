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
                                {{ __('reports::reports.create_report') }}
                            </li>
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
                        <div class="flex-fill py-3 border-end step-item" id="step-3-indicator">
                            <div class="step-icon mb-1 mx-auto bg-light text-muted rounded-circle">3</div>
                            <div class="small fw-bold">{{ __('reports::reports.filters') }}</div>
                        </div>
                        <div class="flex-fill py-3 border-end step-item" id="step-4-indicator">
                            <div class="step-icon mb-1 mx-auto bg-light text-muted rounded-circle">4</div>
                            <div class="small fw-bold">{{ __('reports::reports.sharing') }}</div>
                        </div>
                        <div class="flex-fill py-3 step-item" id="step-5-indicator">
                            <div class="step-icon mb-1 mx-auto bg-light text-muted rounded-circle">5</div>
                            <div class="small fw-bold">{{ __('reports::reports.scheduling') }}</div>
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

                        <!-- Step 4: Sharing -->
                        <div class="step-content d-none" id="step-4-content">
                            <div class="row justify-content-center">
                                <div class="col-md-10">
                                    <h5 class="fw-bold mb-4 d-flex align-items-center gap-2">
                                        <i class="bi bi-share text-primary"></i>
                                        {{ __('reports::reports.sharing_settings') }}
                                    </h5>

                                    <div class="card bg-light border-0 rounded-4">
                                        <div class="card-body p-4">
                                            <div class="row g-3 align-items-end mb-4">
                                                <div class="col-md-5">
                                                    <label
                                                        class="form-label small fw-bold">{{ __('reports::reports.share_with') }}</label>
                                                    <select id="share-type" class="form-select rounded-3">
                                                        <option value="users">{{ __('reports::reports.users') }}</option>
                                                        <option value="groups">{{ __('reports::reports.groups') }}</option>
                                                        <option value="roles">{{ __('reports::reports.roles') }}</option>
                                                        <option value="rolesandsubordinates">
                                                            {{ __('reports::reports.rolesandsubordinates') }}
                                                        </option>
                                                    </select>
                                                </div>
                                                <div class="col-md-5">
                                                    <label
                                                        class="form-label small fw-bold">{{ __('reports::reports.select_entity') }}</label>
                                                    <select id="share-entity" class="form-select rounded-3 select2-sharing">
                                                        <!-- Populated via JS -->
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
                                                    <button type="button" id="add-share-btn"
                                                        class="btn btn-primary w-100 rounded-3">
                                                        <i class="bi bi-plus-lg"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            <div id="sharing-list" class="d-flex flex-wrap gap-2">
                                                <!-- Selected sharing entities -->
                                                <div class="text-muted small w-100 text-center py-3 empty-sharing">
                                                    {{ __('reports::reports.not_shared_yet') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 5: Scheduling -->
                        <div class="step-content d-none" id="step-5-content">
                            <div class="row justify-content-center">
                                <div class="col-md-10">
                                    <h5 class="fw-bold mb-4 d-flex align-items-center gap-2">
                                        <i class="bi bi-alarm text-primary"></i>
                                        {{ __('reports::reports.schedule_report') }}
                                    </h5>

                                    <div class="form-check form-switch mb-4">
                                        <input class="form-check-input" type="checkbox" name="is_scheduled"
                                            id="is_scheduled" value="1">
                                        <label class="form-check-label fw-bold"
                                            for="is_scheduled">{{ __('reports::reports.enable_scheduling') }}</label>
                                    </div>

                                    <div id="scheduling-options" class="d-none border rounded-4 p-4 bg-light">
                                        <div class="row g-3">
                                            {{-- Schedule Type --}}
                                            <div class="col-md-6">
                                                <label
                                                    class="form-label small fw-bold">{{ __('reports::reports.run_report') }}</label>
                                                <select name="scheduleid" id="schtypeid" class="form-select rounded-3">
                                                    <option value="1">{{ __('reports::reports.daily') }}</option>
                                                    <option value="2">{{ __('reports::reports.weekly') }}</option>
                                                    <option value="3">{{ __('reports::reports.monthly_by_date') }}</option>
                                                    <option value="4">{{ __('reports::reports.yearly') }}</option>
                                                    <option value="5">{{ __('reports::reports.specific_date') }}</option>
                                                </select>
                                            </div>

                                            {{-- Time --}}
                                            <div class="col-md-6">
                                                <label
                                                    class="form-label small fw-bold">{{ __('reports::reports.at_time') }}</label>
                                                <input type="time" name="schtime" class="form-control rounded-3"
                                                    value="09:00">
                                            </div>

                                            {{-- Weekly: Day of Week --}}
                                            <div class="col-md-12 sch-option d-none" id="scheduledWeekDay">
                                                <label
                                                    class="form-label small fw-bold">{{ __('reports::reports.on_these_days') }}</label>
                                                <select name="schdayoftheweek[]" id="schdayoftheweek"
                                                    class="form-select rounded-3 select2" multiple>
                                                    <option value="7">{{ __('reports::reports.sunday') }}</option>
                                                    <option value="1">{{ __('reports::reports.monday') }}</option>
                                                    <option value="2">{{ __('reports::reports.tuesday') }}</option>
                                                    <option value="3">{{ __('reports::reports.wednesday') }}</option>
                                                    <option value="4">{{ __('reports::reports.thursday') }}</option>
                                                    <option value="5">{{ __('reports::reports.friday') }}</option>
                                                    <option value="6">{{ __('reports::reports.saturday') }}</option>
                                                </select>
                                            </div>

                                            {{-- Monthly: Day of Month --}}
                                            <div class="col-md-12 sch-option d-none" id="scheduleMonthByDates">
                                                <label
                                                    class="form-label small fw-bold">{{ __('reports::reports.on_these_days') }}</label>
                                                <select name="schdayofthemonth[]" id="schdayofthemonth"
                                                    class="form-select rounded-3 select2" multiple>
                                                    @for($i = 1; $i <= 31; $i++)
                                                        <option value="{{ $i }}">{{ $i }}</option>
                                                    @endfor
                                                </select>
                                            </div>

                                            {{-- Annually: Multiple Dates --}}
                                            <div class="col-md-12 sch-option d-none" id="scheduleAnually">
                                                <label
                                                    class="form-label small fw-bold">{{ __('reports::reports.select_dates') }}</label>
                                                <input type="date" id="annualDatePicker"
                                                    class="form-control rounded-3 mb-2">
                                                <div id="selectedAnnualDates" class="d-flex flex-wrap gap-2 mb-2"></div>
                                                <input type="hidden" name="schannualdates" id="schannualdates" value="[]">
                                            </div>

                                            {{-- Specific Date --}}
                                            <div class="col-md-12 sch-option d-none" id="scheduleByDate">
                                                <label
                                                    class="form-label small fw-bold">{{ __('reports::reports.choose_date') }}</label>
                                                <input type="date" name="schdate" id="schdate"
                                                    class="form-control rounded-3">
                                            </div>

                                            <hr class="my-3">

                                            {{-- Recipients --}}
                                            <div class="col-md-12">
                                                <label
                                                    class="form-label small fw-bold">{{ __('reports::reports.select_recipients') }}</label>
                                                <select name="recipients[]" id="recipients"
                                                    class="form-select rounded-3 select2" multiple>
                                                    <optgroup label="{{ __('reports::reports.users') }}">
                                                        @foreach($users as $user)
                                                            <option value="USER::{{ $user->id }}">{{ $user->first_name }}
                                                                {{ $user->last_name }}
                                                            </option>
                                                        @endforeach
                                                    </optgroup>
                                                    <optgroup label="{{ __('reports::reports.groups') }}">
                                                        @foreach($groups as $group)
                                                            <option value="GROUP::{{ $group->groupid }}">{{ $group->groupname }}
                                                            </option>
                                                        @endforeach
                                                    </optgroup>
                                                    <optgroup label="{{ __('reports::reports.roles') }}">
                                                        @foreach($roles as $role)
                                                            <option value="ROLE::{{ $role->roleid }}">{{ $role->rolename }}
                                                            </option>
                                                        @endforeach
                                                    </optgroup>
                                                </select>
                                            </div>

                                            {{-- Specific Emails --}}
                                            <div class="col-md-12">
                                                <label
                                                    class="form-label small fw-bold">{{ __('reports::reports.specific_email_address') }}</label>
                                                <input type="text" name="specificemails" id="specificemails"
                                                    class="form-control rounded-3"
                                                    placeholder="email1@example.com, email2@example.com">
                                                <small
                                                    class="text-muted">{{ __('reports::reports.separate_emails_with_comma') }}</small>
                                            </div>

                                            {{-- File Format --}}
                                            <div class="col-md-6">
                                                <label
                                                    class="form-label small fw-bold">{{ __('reports::reports.file_format') }}</label>
                                                <select name="fileformat" class="form-select rounded-3">
                                                    <option value="CSV">CSV</option>
                                                    <option value="XLS">Excel</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
            const totalSteps = 5;

            const users = @json($users);
            const groups = @json($groups);
            const roles = @json($roles);

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
                            // Only add if not already present to avoid duplicates (e.g. Calendar)
                            if (secondarySelect.find(`option[value="${rel.target}"]`).length === 0) {
                                secondarySelect.append(`<option value="${rel.target}">${rel.label || rel.target}</option>`);
                            }
                        });
                    }

                    if (secondarySelect.hasClass('select2-hidden-accessible')) {
                        secondarySelect.select2('destroy');
                    }

                    secondarySelect.select2({
                        maximumSelectionLength: 2,
                        placeholder: "{{ __('reports::reports.select_secondary_modules') }}"
                    });

                    secondarySelect.trigger('change');

                    // Store primary fields for later
                    window.primaryModuleData = data;
                });
            });

            // When moving to Step 2, populate all fields for primary + selected secondary modules
            $('#next-btn').on('click', function () {
                if (currentStep === 2) {
                    populateAllFields();
                }
            });

            function populateAllFields() {
                const accordion = $('#fields-accordion');
                accordion.empty();

                if (!window.primaryModuleData) return;

                // 1. Primary Module Fields
                addModuleToAccordion(window.primaryModuleData.module.name, window.primaryModuleData.module.label, window.primaryModuleData.fields, true);

                // 2. Secondary Modules Fields
                const secondaryModules = $('#secondary-modules').val() || [];
                secondaryModules.forEach(modName => {
                    $.get("{{ url('test/modules') }}/" + modName, function (data) {
                        addModuleToAccordion(data.module.name, data.module.label, data.fields, false);
                    });
                });
            }

            function addModuleToAccordion(moduleName, moduleLabel, fields, showDefault) {
                const accordion = $('#fields-accordion');
                const collapseId = `fields-${moduleName.replace(/[^a-zA-Z0-9]/g, '_')}`;

                let html = `
                                                                    <div class="accordion-item border-0 mb-2 overflow-hidden rounded-3">
                                                                        <h2 class="accordion-header">
                                                                            <button class="accordion-button bg-white fw-bold shadow-none ${showDefault ? '' : 'collapsed'}" type="button" data-bs-toggle="collapse" data-bs-target="#${collapseId}">
                                                                                ${moduleLabel}
                                                                            </button>
                                                                        </h2>
                                                                        <div id="${collapseId}" class="accordion-collapse collapse ${showDefault ? 'show' : ''}">
                                                                            <div class="accordion-body p-2">
                                                                                <div class="list-group list-group-flush">
                                                                `;

                fields.forEach(field => {
                    html += `
                                                                        <div class="list-group-item field-item py-2 px-3 border-0 small d-flex align-items-center justify-content-between" 
                                                                            data-module="${moduleName}" data-field="${field.name}" data-label="${field.label}">
                                                                            <span>${field.label}</span>
                                                                            <i class="bi bi-plus-circle text-primary opacity-50"></i>
                                                                        </div>
                                                                    `;
                });

                html += '</div></div></div></div>';
                accordion.append(html);

                // Re-attach click handlers for the new items
                $('.field-item').off('click').on('click', function () {
                    const mod = $(this).data('module');
                    const field = $(this).data('field');
                    const label = $(this).data('label');
                    addFieldToSelected(mod, field, label);
                });
            }

            function addFieldToSelected(mod, field, label) {
                $('.empty-msg').hide();
                const value = mod + ':' + field + ':' + label;
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
            // Sharing JS
            function populateShareEntities() {
                const type = $('#share-type').val();
                const entitySelect = $('#share-entity');
                entitySelect.empty();

                let data = [];
                if (type === 'users') {
                    data = users.map(u => ({ id: u.id, text: (u.first_name || '') + ' ' + (u.last_name || '') }));
                } else if (type === 'groups') {
                    data = groups.map(g => ({ id: g.groupid, text: g.groupname }));
                } else if (type === 'roles' || type === 'rolesandsubordinates') {
                    data = roles.map(r => ({ id: r.roleid, text: r.rolename }));
                }

                data.forEach(item => {
                    entitySelect.append(`<option value="${item.id}">${item.text}</option>`);
                });
            }

            $('#share-type').on('change', populateShareEntities);
            populateShareEntities();

            $('#add-share-btn').on('click', function () {
                const type = $('#share-type').val();
                const entityId = $('#share-entity').val();
                const entityText = $('#share-entity option:selected').text();

                if (!entityId) return;

                const value = type + ':' + entityId;
                if ($(`.share-item[data-value="${value}"]`).length) return;

                $('.empty-sharing').hide();
                $('#sharing-list').append(`
                                                                            <div class="share-item badge bg-white border text-dark p-2 rounded-pivot d-flex align-items-center gap-2" data-value="${value}">
                                                                                <span><strong>${type}:</strong> ${entityText}</span>
                                                                                <input type="hidden" name="sharing[]" value="${value}">
                                                                                <i class="bi bi-x-circle text-danger cursor-pointer delete-share"></i>
                                                                            </div>
                                                                        `);

                $('.delete-share').off('click').on('click', function () {
                    $(this).closest('.share-item').remove();
                    if ($('#sharing-list .share-item').length === 0) {
                        $('.empty-sharing').show();
                    }
                });
            });

            // Scheduling JS
            $('#is_scheduled').on('change', function () {
                if ($(this).is(':checked')) {
                    $('#scheduling-options').removeClass('d-none');
                } else {
                    $('#scheduling-options').addClass('d-none');
                }
            });

            // Schedule type change handler
            $('#schtypeid').on('change', function () {
                const value = $(this).val();

                // Hide all schedule-specific options
                $('.sch-option').addClass('d-none');

                // Show specific options based on schedule type
                if (value === '2') { // Weekly
                    $('#scheduledWeekDay').removeClass('d-none');
                } else if (value === '3') { // Monthly by date
                    $('#scheduleMonthByDates').removeClass('d-none');
                } else if (value === '4') { // Annually
                    $('#scheduleAnually').removeClass('d-none');
                } else if (value === '5') { // Specific date
                    $('#scheduleByDate').removeClass('d-none');
                }
            });

            // Annual dates picker - add date
            let annualDates = [];
            $('#annualDatePicker').on('change', function () {
                const date = $(this).val();
                if (date && !annualDates.includes(date)) {
                    annualDates.push(date);
                    updateAnnualDatesUI();
                }
                $(this).val('');
            });

            function updateAnnualDatesUI() {
                const container = $('#selectedAnnualDates');
                container.empty();

                annualDates.forEach(date => {
                    container.append(`
                                        <span class="badge bg-primary d-flex align-items-center gap-1">
                                            ${date}
                                            <i class="bi bi-x-circle cursor-pointer remove-annual-date" data-date="${date}" style="cursor: pointer;"></i>
                                        </span>
                                    `);
                });

                $('#schannualdates').val(JSON.stringify(annualDates));

                // Re-bind remove handlers
                $('.remove-annual-date').off('click').on('click', function () {
                    const dateToRemove = $(this).data('date');
                    annualDates = annualDates.filter(d => d !== dateToRemove);
                    updateAnnualDatesUI();
                });
            }

            // Initialize Select2 for scheduling selects
            $('#schdayoftheweek, #schdayofthemonth, #recipients').select2({
                placeholder: '{{ __("reports::reports.select") }}',
                width: '100%'
            });

            // Condition Builder JS
            let operators = {};
            let conditionIndex = 0;

            // Load Operators
            $.get("{{ route('tenant.reports.condition-operators') }}", function (data) {
                operators = data.operators;
            });

            // When moving to Step 3, ensure we have operators and field data
            $('#next-btn').on('click', function () {
                if (currentStep === 3) {
                    populateDateFields();
                }
            });

            function populateDateFields() {
                const dateSelect = $('.select-field-date');
                const currentValue = dateSelect.val();
                dateSelect.empty().append('<option value="">{{ __("reports::reports.none") }}</option>');

                if (!window.primaryModuleData) return;

                window.primaryModuleData.fields.forEach(f => {
                    if ([5, 6, 23, 70].includes(parseInt(f.uitype))) {
                        dateSelect.append(`<option value="${window.primaryModuleData.module.name}:${f.name}" ${currentValue === window.primaryModuleData.module.name + ':' + f.name ? 'selected' : ''}>${f.label}</option>`);
                    }
                });
            }

            $('.add-condition-btn').on('click', function () {
                const type = $(this).data('type');
                addConditionRow(type);
            });

            function addConditionRow(type) {
                const container = $(`#${type}ConditionsContainer`);
                const index = conditionIndex++;

                let fieldOptionsHtml = '';

                if (window.primaryModuleData) {
                    fieldOptionsHtml += `<optgroup label="${window.primaryModuleData.module.label}">`;
                    window.primaryModuleData.fields.forEach(f => {
                        fieldOptionsHtml += `<option value="${window.primaryModuleData.module.name}:${f.name}">${f.label}</option>`;
                    });
                    fieldOptionsHtml += `</optgroup>`;
                }

                $('#fields-accordion .accordion-item').each(function () {
                    const moduleLabel = $(this).find('.accordion-button').text().trim();
                    const moduleName = $(this).find('.accordion-collapse').attr('id').replace('fields-', '').replace(/_/g, ':');

                    if (window.primaryModuleData && moduleName === window.primaryModuleData.module.name) return;

                    fieldOptionsHtml += `<optgroup label="${moduleLabel}">`;
                    $(this).find('.field-item').each(function () {
                        const fName = $(this).data('field');
                        const fLabel = $(this).data('label');
                        const fMod = $(this).data('module');
                        fieldOptionsHtml += `<option value="${fMod}:${fName}">${fLabel}</option>`;
                    });
                    fieldOptionsHtml += `</optgroup>`;
                });

                const html = `
                                            <div class="condition-row row g-2 align-items-center mb-2" data-index="${index}" data-group="${type}">
                                                <div class="col-md-4">
                                                    <select class="form-select form-select-sm field-selector" name="conditions[${index}][columnname]" required>
                                                        <option value="">{{ __("tenant::settings.select_field") }}</option>
                                                        ${fieldOptionsHtml}
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <select class="form-select form-select-sm operator-selector" name="conditions[${index}][comparator]" required>
                                                        <option value="">{{ __("tenant::settings.select_operator") }}</option>
                                                        ${Object.entries(operators).map(([v, l]) => `<option value="${v}">${l}</option>`).join('')}
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="text" class="form-control form-control-sm" name="conditions[${index}][value]" placeholder="{{ __('tenant::settings.enter_value') }}">
                                                    <input type="hidden" name="conditions[${index}][groupid]" value="${type === 'all' ? 1 : 2}">
                                                </div>
                                                <div class="col-md-1 text-end">
                                                    <button type="button" class="btn btn-sm btn-link text-danger remove-condition p-0">
                                                        <i class="bi bi-x-circle fs-5"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        `;

                container.append(html);

                const row = container.find(`.condition-row[data-index="${index}"]`);
                row.find('.remove-condition').on('click', function () {
                    row.remove();
                });
            }
        </script>
    @endpush
@endsection