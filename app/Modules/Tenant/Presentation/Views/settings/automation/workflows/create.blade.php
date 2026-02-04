@extends('tenant::layout')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .flatpickr-calendar.static { border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }
        .flatpickr-day.selected { background: var(--primary-color) !important; border-color: var(--primary-color) !important; }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="fw-bold mb-1">{{ __('tenant::settings.create_workflow') }}</h2>
                        <p class="text-muted">{{ __('tenant::settings.create_workflow_description') }}</p>
                    </div>
                    <a href="{{ route('tenant.settings.crm.automation.workflows.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>{{ __('tenant::settings.back') }}
                    </a>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('tenant.settings.crm.automation.workflows.store') }}" id="workflowForm">
            @csrf
            
            <div class="row">
                <div class="col-lg-8">
                    {{-- 1. Basic Information --}}
                    <div class="card mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="fw-bold mb-0">{{ __('tenant::settings.basic_information') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="workflowname" class="form-label fw-semibold">
                                        {{ __('tenant::settings.workflow_name') }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('workflowname') is-invalid @enderror" 
                                           id="workflowname" name="workflowname" value="{{ old('workflowname') }}" required>
                                    @error('workflowname') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="module_name" class="form-label fw-semibold">
                                        {{ __('tenant::settings.module') }} <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('module_name') is-invalid @enderror" 
                                            id="module_name" name="module_name" required>
                                        <option value="">{{ __('tenant::settings.select_module') }}</option>
                                        @foreach($modules as $key => $label)
                                            <option value="{{ $key }}" {{ old('module_name', $moduleName) == $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('module_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="summary" class="form-label fw-semibold">{{ __('tenant::settings.description') }}</label>
                                    <textarea class="form-control" id="summary" name="summary" rows="2">{{ old('summary') }}</textarea>
                                </div>
                                <div class="col-12 mb-0">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="status" name="status" value="1" checked>
                                        <label class="form-check-label fw-semibold" for="status">{{ __('tenant::settings.active') }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Workflow Trigger --}}
                    <div class="card mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="fw-bold mb-0">{{ __('tenant::settings.workflow_trigger') }}</h5>
                        </div>
                        <div class="card-body">
                            {{-- Exclude Days & Working Hours (Always Visible) --}}
                            <div class="mb-4">
                                <label class="form-label fw-semibold">{{ __('tenant::settings.exclude_days') }}</label>
                                <div class="d-flex flex-wrap gap-3 mt-1">
                                    @foreach(['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'] as $day)
                                        <div class="form-check">
                                            <input class="form-check-input exclude-day-checkbox" type="checkbox" value="{{ $day }}" id="exclude_{{ $day }}">
                                            <label class="form-check-label small" for="exclude_{{ $day }}">{{ __('tenant::settings.' . $day) }}</label>
                                        </div>
                                    @endforeach
                                </div>
                                <input type="hidden" name="schdayofweekexclude" id="schdayofweekexclude">
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label for="timefrom" class="form-label fw-semibold">{{ __('tenant::settings.working_from') }}</label>
                                    <input type="time" class="form-control" id="timefrom" name="timefrom">
                                </div>
                                <div class="col-md-6">
                                    <label for="timeto" class="form-label fw-semibold">{{ __('tenant::settings.working_to') }}</label>
                                    <input type="time" class="form-control" id="timeto" name="timeto">
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="mb-4">
                                <label class="form-label fw-semibold">{{ __('tenant::settings.trigger_workflow_on') }}</label>
                                <div class="d-flex flex-wrap gap-3 mt-2">
                                    <div class="form-check">
                                        <input class="form-check-input trigger-radio" type="radio" name="trigger_type" id="trigger_creation" value="creation" checked>
                                        <label class="form-check-label" for="trigger_creation">{{ __('tenant::settings.module_creation') }}</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input trigger-radio" type="radio" name="trigger_type" id="trigger_update" value="update">
                                        <label class="form-check-label" for="trigger_update">{{ __('tenant::settings.module_updated_includes_creation') }}</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input trigger-radio" type="radio" name="trigger_type" id="trigger_time" value="time">
                                        <label class="form-check-label" for="trigger_time">{{ __('tenant::settings.time_interval') }}</label>
                                    </div>
                                </div>
                                <input type="hidden" name="execution_condition" id="execution_condition" value="1">
                            </div>

                            {{-- Recurrence (Only for Update) --}}
                            <div id="recurrence_section" class="mb-4 d-none">
                                <label class="form-label fw-semibold">{{ __('tenant::settings.recurrence') }}</label>
                                <div class="mt-2">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input recurrence-radio" type="radio" name="recurrence_type" id="rec_once" value="once">
                                        <label class="form-check-label" for="rec_once">{{ __('tenant::settings.only_first_time_conditions_met') }}</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input recurrence-radio" type="radio" name="recurrence_type" id="rec_every" value="every" checked>
                                        <label class="form-check-label" for="rec_every">{{ __('tenant::settings.every_time_conditions_met') }}</label>
                                    </div>
                                </div>
                            </div>

                            {{-- Time Interval Section --}}
                            <div id="time_interval_section" class="d-none">
                                <div class="bg-light p-3 rounded-2 border">
                                    <label class="form-label fw-bold text-primary mb-3">{{ __('tenant::settings.run_workflow') }}</label>
                                    <div class="row align-items-end">
                                        <div class="col-md-5 mb-3">
                                            <label for="schtypeid" class="form-label fw-semibold">{{ __('tenant::settings.frequency') }}</label>
                                            <select class="form-select" id="schtypeid" name="schtypeid">
                                                @foreach($scheduleTypes as $id => $label)
                                                    <option value="{{ $id }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        
                                        {{-- Weekly Days --}}
                                        <div id="weekly_days_container" class="col-md-12 mb-3 d-none">
                                            <label class="form-label fw-semibold">{{ __('tenant::settings.on_these_days') }}</label>
                                            <div class="d-flex flex-wrap gap-2">
                                                @foreach(['1'=>'sunday', '2'=>'monday', '3'=>'tuesday', '4'=>'wednesday', '5'=>'thursday', '6'=>'friday', '7'=>'saturday'] as $val => $day)
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input schdayofweek-checkbox" type="checkbox" value="{{ $val }}" id="schday_{{ $day }}">
                                                        <label class="form-check-label small" for="schday_{{ $day }}">{{ __('tenant::settings.' . $day) }}</label>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <input type="hidden" name="schdayofweek" id="schdayofweek">
                                        </div>

                                        {{-- Specific Date --}}
                                        <div id="specific_date_container" class="col-md-4 mb-3 d-none">
                                            <label for="schdate_input" class="form-label fw-semibold">{{ __('tenant::settings.choose_date') }}</label>
                                            <input type="date" class="form-control" id="schdate_input">
                                        </div>

                                        {{-- Monthly Days --}}
                                        <div id="monthly_days_container" class="col-md-12 mb-3 d-none">
                                            <label for="schdayofmonth" class="form-label fw-semibold">{{ __('tenant::settings.on_these_days') }}</label>
                                            <div class="d-flex flex-wrap gap-1">
                                                @for($i=1; $i<=31; $i++)
                                                    <div class="form-check form-check-inline m-0">
                                                        <input class="form-check-input schdayofmonth-checkbox" type="checkbox" value="{{ $i }}" id="schmonthday_{{ $i }}">
                                                        <label class="form-check-label small" for="schmonthday_{{ $i }}">{{ $i }}</label>
                                                    </div>
                                                @endfor
                                            </div>
                                            <input type="hidden" name="schdayofmonth" id="schdayofmonth">
                                        </div>

                                        {{-- Annual Dates --}}
                                        <div id="annual_dates_container" class="col-md-12 mb-3 d-none">
                                            <label class="form-label fw-semibold">{{ __('tenant::settings.select_month_and_date') }}</label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-white border-end-0"><i class="bi bi-calendar-event"></i></span>
                                                <input type="text" class="form-control ps-0 border-start-0" id="ann_date_multiple" placeholder="Select dates...">
                                            </div>
                                            <input type="hidden" name="schannualdates" id="schannualdates">
                                            <div id="annualDatesSummary" class="mt-2 d-flex flex-wrap gap-1"></div>
                                        </div>

                                        <div id="schtime_container" class="col-md-3 mb-3 d-none">
                                            <label for="schtime" class="form-label fw-semibold">{{ __('tenant::settings.at_time') }}</label>
                                            <input type="time" class="form-control" id="schtime" name="schtime">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Workflow Condition --}}
                    <div class="card mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="fw-bold mb-0">{{ __('tenant::settings.conditions') }}</h5>
                        </div>
                        <div class="card-body">
                            {{-- All Conditions --}}
                            <div class="mb-4">
                                <h6 class="fw-bold d-flex align-items-center">
                                    {{ __('tenant::settings.all_conditions') }}
                                    <span class="text-muted small ms-2 fw-normal">{{ __('tenant::settings.all_conditions_desc') }}</span>
                                </h6>
                                <div id="allConditionsContainer" class="mt-3">
                                    {{-- All Conditions Rows --}}
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary mt-2 add-condition-btn" data-type="all">
                                    <i class="bi bi-plus-circle me-1"></i>{{ __('tenant::settings.add_condition') }}
                                </button>
                            </div>

                            <hr class="my-4">

                            {{-- Any Conditions --}}
                            <div class="mb-0">
                                <h6 class="fw-bold d-flex align-items-center">
                                    {{ __('tenant::settings.any_conditions') }}
                                    <span class="text-muted small ms-2 fw-normal">{{ __('tenant::settings.any_conditions_desc') }}</span>
                                </h6>
                                <div id="anyConditionsContainer" class="mt-3">
                                    {{-- Any Conditions Rows --}}
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary mt-2 add-condition-btn" data-type="any">
                                    <i class="bi bi-plus-circle me-1"></i>{{ __('tenant::settings.add_condition') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mb-5">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-circle me-2"></i>{{ __('tenant::settings.create_and_configure') }}
                        </button>
                        <a href="{{ route('tenant.settings.crm.automation.workflows.index') }}" class="btn btn-outline-secondary px-4">
                            {{ __('tenant::settings.cancel') }}
                        </a>
                    </div>
                </div>

                {{-- Help Sidebar --}}
                <div class="col-lg-4">
                    <div class="card bg-light border-0 shadow-none">
                        <div class="card-body">
                            <h5 class="card-title fw-bold mb-3 d-flex align-items-center">
                                <i class="bi bi-info-circle me-2 text-primary"></i>
                                {{ __('tenant::settings.workflow_help') }}
                            </h5>
                            
                            <div class="mb-4">
                                <h6 class="fw-bold small text-uppercase text-muted">{{ __('tenant::settings.tips') }}</h6>
                                <ul class="list-unstyled small">
                                    <li class="mb-2"><i class="bi bi-check2 text-success me-2"></i>{{ __('tenant::settings.workflow_tip_1') }}</li>
                                    <li class="mb-2"><i class="bi bi-check2 text-success me-2"></i>{{ __('tenant::settings.workflow_tip_2') }}</li>
                                    <li class="mb-2"><i class="bi bi-check2 text-success me-2"></i>{{ __('tenant::settings.workflow_tip_3') }}</li>
                                </ul>
                            </div>

                            <div class="alert alert-warning border-0 small">
                                <i class="bi bi-lightbulb me-2"></i>
                                {{ __('tenant::settings.workflow_tip') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const moduleSelect = document.getElementById('module_name');
        const triggerRadios = document.querySelectorAll('.trigger-radio');
        const recurrenceRadios = document.querySelectorAll('.recurrence-radio');
        const executionConditionInput = document.getElementById('execution_condition');
        const recurrenceSection = document.getElementById('recurrence_section');
        const timeIntervalSection = document.getElementById('time_interval_section');
        const excludeDaysCheckboxes = document.querySelectorAll('.exclude-day-checkbox');
        const excludeDaysHidden = document.getElementById('schdayofweekexclude');

        // Frequency Elements
        const schtypeidSelect = document.getElementById('schtypeid');
        const schtimeContainer = document.getElementById('schtime_container');
        const weeklyDaysContainer = document.getElementById('weekly_days_container');
        const specificDateContainer = document.getElementById('specific_date_container');
        const monthlyDaysContainer = document.getElementById('monthly_days_container');
        const annualDatesContainer = document.getElementById('annual_dates_container');
        
        const schdayofweekCheckboxes = document.querySelectorAll('.schdayofweek-checkbox');
        const schdayofweekHidden = document.getElementById('schdayofweek');
        const schdayofmonthCheckboxes = document.querySelectorAll('.schdayofmonth-checkbox');
        const schdayofmonthHidden = document.getElementById('schdayofmonth');
        const schannualdatesHidden = document.getElementById('schannualdates');

        let moduleFields = [];
        let operators = {};
        let conditionIndex = 0;

        // Load Operators
        fetch('{{ route("tenant.settings.crm.automation.workflows.condition-operators") }}')
            .then(res => res.json())
            .then(data => { operators = data.operators; });

        // Module Change
        moduleSelect.addEventListener('change', function() {
            if (this.value) loadModuleFields(this.value);
        });

        function loadModuleFields(moduleName) {
            fetch(`{{ route('tenant.settings.crm.automation.workflows.module-fields') }}?module=${moduleName}`)
                .then(res => res.json())
                .then(data => {
                    moduleFields = data.fields;
                    updateAllFieldSelectors();
                });
        }

        function updateAllFieldSelectors() {
            document.querySelectorAll('.field-selector').forEach(select => {
                const val = select.value;
                select.innerHTML = '<option value="">{{ __("tenant::settings.select_field") }}</option>';
                moduleFields.forEach(f => {
                    select.innerHTML += `<option value="${f.name}" ${f.name === val ? 'selected' : ''}>${f.label}</option>`;
                });
            });
        }

        // Trigger Logic
        triggerRadios.forEach(radio => {
            radio.addEventListener('change', updateTriggerVisibility);
        });

        recurrenceRadios.forEach(radio => {
            radio.addEventListener('change', updateExecutionCondition);
        });

        function updateTriggerVisibility() {
            const selected = document.querySelector('.trigger-radio:checked').value;
            
            recurrenceSection.classList.add('d-none');
            timeIntervalSection.classList.add('d-none');

            if (selected === 'creation') {
                executionConditionInput.value = 1;
            } else if (selected === 'update') {
                recurrenceSection.classList.remove('d-none');
                updateExecutionCondition();
            } else if (selected === 'time') {
                executionConditionInput.value = 6;
                timeIntervalSection.classList.remove('d-none');
            }
        }

        function updateExecutionCondition() {
            const rec = document.querySelector('.recurrence-radio:checked').value;
            executionConditionInput.value = (rec === 'once') ? 2 : 3;
        }

        // Exclude Days Logic
        excludeDaysCheckboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                const checked = Array.from(excludeDaysCheckboxes)
                    .filter(c => c.checked)
                    .map(c => c.value);
                excludeDaysHidden.value = JSON.stringify(checked);
            });
        });

        // Frequency Logic
        schtypeidSelect.addEventListener('change', updateFrequencyFields);

        function updateFrequencyFields() {
            const freq = schtypeidSelect.value;
            
            // Default hide all
            schtimeContainer.classList.add('d-none');
            weeklyDaysContainer.classList.add('d-none');
            specificDateContainer.classList.add('d-none');
            monthlyDaysContainer.classList.add('d-none');
            annualDatesContainer.classList.add('d-none');

            // Show time for all except Hourly (1)
            if (freq !== '1') {
                schtimeContainer.classList.remove('d-none');
            }

            if (freq == '3') { // Weekly
                weeklyDaysContainer.classList.remove('d-none');
            } else if (freq == '4') { // Specific Date
                specificDateContainer.classList.remove('d-none');
            } else if (freq == '5') { // Monthly by date
                monthlyDaysContainer.classList.remove('d-none');
            } else if (freq == '6') { // Annually (Updated ID)
                annualDatesContainer.classList.remove('d-none');
            }
        }

        // Weekly Checkboxes
        schdayofweekCheckboxes.forEach(cb => {
            cb.addEventListener('change', () => {
                const vals = Array.from(schdayofweekCheckboxes).filter(c => c.checked).map(c => c.value);
                schdayofweekHidden.value = JSON.stringify(vals);
            });
        });

        // Monthly Checkboxes
        schdayofmonthCheckboxes.forEach(cb => {
            cb.addEventListener('change', () => {
                const vals = Array.from(schdayofmonthCheckboxes).filter(c => c.checked).map(c => c.value);
                schdayofmonthHidden.value = JSON.stringify(vals);
            });
        });

        // Specific Date Logic
        document.getElementById('schdate_input').addEventListener('change', function() {
            if (this.value) {
                schannualdatesHidden.value = JSON.stringify([this.value]);
            }
        });

        // Annual Date Logic with Flatpickr
        const annDatePicker = flatpickr("#ann_date_multiple", {
            mode: "multiple",
            dateFormat: "m-d", 
            altInput: true,
            altFormat: "M J",
            conjunction: ", ",
            static: true,
            monthSelectorType: "static",
            onChange: function(selectedDates, dateStr, instance) {
                const dates = selectedDates.map(date => {
                    const m = date.getMonth() + 1;
                    const d = date.getDate();
                    return `${m}-${d}`;
                });
                schannualdatesHidden.value = JSON.stringify(dates);
                renderAnnualSummary(selectedDates);
            }
        });

        function renderAnnualSummary(selectedDates) {
            const summary = document.getElementById('annualDatesSummary');
            if(!summary) return;
            summary.innerHTML = '';
            [...selectedDates].sort((a,b) => a-b).forEach(date => {
                const badge = document.createElement('span');
                badge.className = 'badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-1 small';
                badge.textContent = date.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
                summary.appendChild(badge);
            });
        }

        // Condition Add Buttons
        document.querySelectorAll('.add-condition-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                if (!moduleSelect.value) {
                    alert('{{ __("tenant::settings.please_select_module_first") }}');
                    return;
                }
                const type = this.dataset.type;
                addConditionRow(type);
            });
        });

        function addConditionRow(type) {
            const container = document.getElementById(`${type}ConditionsContainer`);
            const index = conditionIndex++;
            
            const html = `
                <div class="condition-row row g-2 align-items-center mb-2" data-index="${index}" data-group="${type}">
                    <div class="col-md-4">
                        <select class="form-select form-select-sm field-selector" name="conditions[${index}][fieldname]" required>
                            <option value="">{{ __("tenant::settings.select_field") }}</option>
                            ${moduleFields.map(f => `<option value="${f.name}">${f.label}</option>`).join('')}
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select form-select-sm operator-selector" name="conditions[${index}][operation]" required>
                            <option value="">{{ __("tenant::settings.select_operator") }}</option>
                            ${Object.entries(operators).map(([v, l]) => `<option value="${v}">${l}</option>`).join('')}
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control form-control-sm" name="conditions[${index}][value]" placeholder="{{ __('tenant::settings.enter_value') }}">
                        <input type="hidden" name="conditions[${index}][group]" value="${type}">
                    </div>
                    <div class="col-md-1 text-end">
                        <button type="button" class="btn btn-sm btn-link text-danger remove-condition p-0">
                            <i class="bi bi-x-circle fs-5"></i>
                        </button>
                    </div>
                </div>
            `;
            
            container.insertAdjacentHTML('beforeend', html);
            
            const row = container.lastElementChild;
            row.querySelector('.remove-condition').addEventListener('click', () => row.remove());
        }

        // Initial setup
        updateTriggerVisibility();
    });
</script>
@endpush
