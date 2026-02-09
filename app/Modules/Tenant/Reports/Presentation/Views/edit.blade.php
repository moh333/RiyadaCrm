@extends('tenant::layout')

@section('title', __('reports::reports.edit_report'))

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
                                {{ __('reports::reports.edit_report') }}
                            </li>
                        </ol>
                    </nav>
                    <h1 class="h3 mb-0 text-main fw-bold">{{ __('reports::reports.edit_report') }}:
                        {{ $report->reportname }}
                    </h1>
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
                    <form id="report-form" action="{{ route('tenant.reports.update', $report->reportid) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Step 1: Report Details -->
                        <div class="step-content" id="step-1-content">
                            <div class="row g-4 justify-content-center">
                                <div class="col-md-8">
                                    <div class="mb-4">
                                        <label class="form-label fw-bold">{{ __('reports::reports.report_name') }} <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="reportname"
                                            class="form-control form-control-lg rounded-3 @error('reportname') is-invalid @enderror"
                                            value="{{ old('reportname', $report->reportname) }}"
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
                                                <option value="{{ $folder->folderid }}" {{ $report->folderid == $folder->folderid ? 'selected' : '' }}>
                                                    {{ $folder->foldername }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-bold">{{ __('reports::reports.primary_module') }} <span
                                                class="text-danger">*</span></label>
                                        <select name="primarymodule" id="primary-module"
                                            class="form-select form-select-lg rounded-3 select2 @error('primarymodule') is-invalid @enderror"
                                            required disabled>
                                            <option value="">{{ __('reports::reports.select_module') }}</option>
                                            @foreach($activeModules as $module)
                                                <option value="{{ $module->getName() }}" {{ ($report->modules->primarymodule ?? '') == $module->getName() ? 'selected' : '' }}>
                                                    {{ $module->getLabel() }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="primarymodule"
                                            value="{{ $report->modules->primarymodule ?? '' }}">
                                        <div class="form-text mt-1 small text-warning"><i
                                                class="bi bi-exclamation-triangle me-1"></i>{{ __('reports::reports.primary_module_change_warning') }}
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label
                                            class="form-label fw-bold">{{ __('reports::reports.secondary_modules') }}</label>
                                        @php
                                            $secondary = explode(':', $report->modules->secondarymodules ?? '');
                                        @endphp
                                        <select name="secondarymodules[]" id="secondary-modules"
                                            class="form-select rounded-3 select2" multiple>
                                            @foreach($secondaryModules as $mod)
                                                <option value="{{ $mod['name'] }}" selected>{{ $mod['label'] }}</option>
                                            @endforeach
                                        </select>
                                        <div class="form-text mt-2">{{ __('reports::reports.secondary_modules_help') }}
                                        </div>
                                    </div>

                                    <div class="">
                                        <label class="form-label fw-bold">{{ __('reports::reports.description') }}</label>
                                        <textarea name="description" class="form-control rounded-3" rows="3"
                                            placeholder="{{ __('reports::reports.enter_description') }}">{{ old('description', $report->description) }}</textarea>
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
                                            @forelse($report->selectQuery->columns as $column)
                                                @php
                                                    $parts = explode(':', $column->columnname);
                                                    $mod = $parts[0] ?? '';
                                                    $field = $parts[1] ?? '';
                                                    $label = $parts[2] ?? $field;
                                                @endphp
                                                <div
                                                    class="selected-field-row p-3 rounded-3 d-flex align-items-center justify-content-between">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <i class="bi bi-grip-vertical text-muted"></i>
                                                        <div>
                                                            <div class="fw-bold small">{{ str_replace('_', ' ', $label) }}</div>
                                                            <div class="text-muted" style="font-size: 10px;">
                                                                {{ $mod }}:{{ $field }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <input type="hidden" name="columns[]" value="{{ $column->columnname }}">
                                                    <button type="button"
                                                        class="btn btn-link btn-sm text-danger p-0 delete-field">
                                                        <i class="bi bi-x-circle"></i>
                                                    </button>
                                                </div>
                                            @empty
                                                <div class="text-center py-5 text-muted empty-msg">
                                                    <i class="bi bi-hand-index-thumb mb-2 display-6"></i>
                                                    <p>{{ __('reports::reports.drag_and_drop_fields') }}</p>
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Filters -->
                        <div class="step-content d-none" id="step-3-content">
                            <!-- Filter UI -->
                            @include('reports::partials.filters_step', ['selectQuery' => $report->selectQuery])
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
                                                @php
                                                    $sharedEntities = [];
                                                    foreach ($report->shareUsers as $u)
                                                        $sharedEntities[] = ['type' => 'users', 'id' => $u->userid, 'text' => ($u->user->first_name ?? '') . ' ' . ($u->user->last_name ?? 'User ' . $u->userid)];
                                                    foreach ($report->shareGroups as $g)
                                                        $sharedEntities[] = ['type' => 'groups', 'id' => $g->groupid, 'text' => $g->groupname ?? 'Group ' . $g->groupid];
                                                    foreach ($report->shareRoles as $r)
                                                        $sharedEntities[] = ['type' => 'roles', 'id' => $r->roleid, 'text' => $r->rolename ?? 'Role ' . $r->roleid];
                                                @endphp

                                                @foreach($sharedEntities as $entity)
                                                    <div class="share-item badge bg-white border text-dark p-2 rounded-pivot d-flex align-items-center gap-2"
                                                        data-value="{{ $entity['type'] }}:{{ $entity['id'] }}">
                                                        <span><strong>{{ $entity['type'] }}:</strong>
                                                            {{ $entity['text'] }}</span>
                                                        <input type="hidden" name="sharing[]"
                                                            value="{{ $entity['type'] }}:{{ $entity['id'] }}">
                                                        <i class="bi bi-x-circle text-danger cursor-pointer delete-share"></i>
                                                    </div>
                                                @endforeach

                                                <div
                                                    class="text-muted small w-100 text-center py-3 empty-sharing {{ count($sharedEntities) > 0 ? 'd-none' : '' }}">
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
                                <div class="col-md-8">
                                    <h5 class="fw-bold mb-4 d-flex align-items-center gap-2">
                                        <i class="bi bi-alarm text-primary"></i>
                                        {{ __('reports::reports.schedule_report') }}
                                    </h5>

                                    @php $isSched = !empty($report->scheduledReport); @endphp
                                    <div class="form-check form-switch mb-4">
                                        <input class="form-check-input" type="checkbox" name="is_scheduled"
                                            id="is_scheduled" value="1" {{ $isSched ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold"
                                            for="is_scheduled">{{ __('reports::reports.enable_scheduling') }}</label>
                                    </div>

                                    <div id="scheduling-options"
                                        class="{{ $isSched ? '' : 'd-none' }} border rounded-4 p-4 bg-light">
                                        <div class="mb-3">
                                            <label
                                                class="form-label small fw-bold">{{ __('reports::reports.frequency') }}</label>
                                            <select name="sch_frequency" class="form-select rounded-3">
                                                <option value="1" {{ ($report->scheduledReport->sch_frequency ?? '') == 1 ? 'selected' : '' }}>{{ __('reports::reports.daily') }}</option>
                                                <option value="2" {{ ($report->scheduledReport->sch_frequency ?? '') == 2 ? 'selected' : '' }}>{{ __('reports::reports.weekly') }}</option>
                                                <option value="3" {{ ($report->scheduledReport->sch_frequency ?? '') == 3 ? 'selected' : '' }}>{{ __('reports::reports.monthly') }}</option>
                                                <option value="4" {{ ($report->scheduledReport->sch_frequency ?? '') == 4 ? 'selected' : '' }}>{{ __('reports::reports.annually') }}</option>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label
                                                class="form-label small fw-bold">{{ __('reports::reports.time') }}</label>
                                            <input type="time" name="schtime" class="form-control rounded-3"
                                                value="{{ $report->scheduledReport->schtime ?? '09:00' }}">
                                        </div>

                                        <div class="mb-3">
                                            <label
                                                class="form-label small fw-bold">{{ __('reports::reports.recipients') }}</label>
                                            @php
                                                $recipients = explode(',', $report->scheduledReport->recipients ?? '');
                                            @endphp
                                            <select name="sch_recipients[]" class="form-select rounded-3 select2" multiple>
                                                @foreach($users as $user)
                                                    <option value="{{ $user->id }}" {{ in_array($user->id, $recipients) ? 'selected' : '' }}>
                                                        {{ $user->first_name }} {{ $user->last_name }}
                                                    </option>
                                                @endforeach
                                            </select>
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
                                    <i class="bi bi-check2-circle me-2"></i> {{ __('reports::reports.update_report') }}
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

            // On Load: Populate secondary modules and fields for the primary module
            $(document).ready(function () {
                const primaryModule = '{{ $report->modules->primarymodule ?? '' }}';
                if (primaryModule) {
                    loadModuleMetadata(primaryModule);
                }

                $('#secondary-modules').select2({
                    maximumSelectionLength: 2,
                    placeholder: "{{ __('reports::reports.select_secondary_modules') }}"
                });

                $('.delete-field').on('click', function () {
                    $(this).closest('.selected-field-row').remove();
                    if ($('#selected-fields-list .selected-field-row').length === 0) {
                        $('.empty-msg').show();
                    }
                });
            });

            function loadModuleMetadata(moduleName) {
                // Fetch valid secondary modules via AJAX
                $.get("{{ url('test/modules') }}/" + moduleName, function (data) {
                    const secondarySelect = $('#secondary-modules');
                    // We don't empty here on edit page to keep existing selected options
                    if (data.relations) {
                        data.relations.forEach(rel => {
                            if (!secondarySelect.find(`option[value="${rel.target}"]`).length) {
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

                    // Store primary fields for later
                    window.primaryModuleData = data;
                });
            }

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

                // Check if already exists in accordion to avoid duplicates on re-entry
                if ($(`#${collapseId}`).length) return;

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

                attachDeleteShareEvents();
            });

            function attachDeleteShareEvents() {
                $('.delete-share').off('click').on('click', function () {
                    $(this).closest('.share-item').remove();
                    if ($('#sharing-list .share-item').length === 0) {
                        $('.empty-sharing').show();
                    }
                });
            }
            attachDeleteShareEvents();

            // Scheduling JS
            $('#is_scheduled').on('change', function () {
                if ($(this).is(':checked')) {
                    $('#scheduling-options').removeClass('d-none');
                } else {
                    $('#scheduling-options').addClass('d-none');
                }
            });

            // Condition Builder JS
            let operators = {};
            let conditionIndex = 0;

            // Load Operators
            $.get("{{ route('tenant.reports.condition-operators') }}", function (data) {
                operators = data.operators;
                populateExistingConditions();
            });

            const existingConditions = @json($report->selectQuery->criteria ?? []);

            function populateExistingConditions() {
                if (!existingConditions || existingConditions.length === 0) return;

                if ($('#fields-accordion .accordion-item').length === 0) {
                    setTimeout(populateExistingConditions, 500);
                    return;
                }

                existingConditions.forEach(cond => {
                    const type = (cond.groupid == 1) ? 'all' : 'any';
                    addConditionRow(type, cond);
                });
            }

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

            function addConditionRow(type, data = null) {
                const container = $(`#${type}ConditionsContainer`);
                const index = conditionIndex++;
                
                let fieldOptionsHtml = '';
                
                if (window.primaryModuleData) {
                    fieldOptionsHtml += `<optgroup label="${window.primaryModuleData.module.label}">`;
                    window.primaryModuleData.fields.forEach(f => {
                        const val = `${window.primaryModuleData.module.name}:${f.name}`;
                        const selected = (data && data.columnname === val) ? 'selected' : '';
                        fieldOptionsHtml += `<option value="${val}" ${selected}>${f.label}</option>`;
                    });
                    fieldOptionsHtml += `</optgroup>`;
                }

                $('#fields-accordion .accordion-item').each(function() {
                    const moduleLabel = $(this).find('.accordion-button').text().trim();
                    const moduleName = $(this).find('.accordion-collapse').attr('id').replace('fields-', '').replace(/_/g, ':');
                    
                    if (window.primaryModuleData && moduleName === window.primaryModuleData.module.name) return;

                    fieldOptionsHtml += `<optgroup label="${moduleLabel}">`;
                    $(this).find('.field-item').each(function() {
                        const fName = $(this).data('field');
                        const fLabel = $(this).data('label');
                        const fMod = $(this).data('module');
                        const val = `${fMod}:${fName}`;
                        const selected = (data && data.columnname === val) ? 'selected' : '';
                        fieldOptionsHtml += `<option value="${val}" ${selected}>${fLabel}</option>`;
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
                                ${Object.entries(operators).map(([v, l]) => `<option value="${v}" ${data && data.comparator === v ? 'selected' : ''}>${l}</option>`).join('')}
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control form-control-sm" name="conditions[${index}][value]" value="${data ? (data.value || '') : ''}" placeholder="{{ __('tenant::settings.enter_value') }}">
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
                row.find('.remove-condition').on('click', function() {
                    row.remove();
                });
            }
        </script>
    @endpush
@endsection