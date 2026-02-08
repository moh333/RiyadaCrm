<!-- Filter UI -->
<div class="mb-5 py-4 border-bottom">
    <h5 class="fw-bold mb-4 d-flex align-items-center gap-2">
        <i class="bi bi-calendar-event text-primary"></i>
        {{ __('reports::reports.standard_filters') }}
    </h5>
    <div class="row g-3 px-3">
        <div class="col-md-4">
            <label class="form-label small fw-bold">{{ __('reports::reports.select_date_field') }}</label>
            <select name="std_field" class="form-select rounded-3 select-field-date">
                <option value="">{{ __('reports::reports.none') }}</option>
                <!-- This will be populated via JS when primary module is loaded -->
            </select>
        </div>
        <div class="col-md-8">
            <label class="form-label small fw-bold">{{ __('reports::reports.select_duration') }}</label>
            <select name="std_duration" class="form-select rounded-3">
                <option value="custom">{{ __('reports::reports.custom_range') }}</option>
                <option value="today">{{ __('reports::reports.today') }}</option>
                <option value="yesterday">{{ __('reports::reports.yesterday') }}</option>
                <option value="thismonth">{{ __('reports::reports.this_month') }}</option>
                <option value="lastmonth">{{ __('reports::reports.last_month') }}</option>
            </select>
        </div>
    </div>
</div>

<div class="py-2">
    <h5 class="fw-bold mb-4 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-funnel text-primary"></i>
            {{ __('reports::reports.advanced_filters') }}
        </div>
        <button type="button" class="btn btn-sm btn-soft-primary rounded-pill px-3" id="add-condition-btn">
            <i class="bi bi-plus-lg me-1"></i> {{ __('reports::reports.add_condition') }}
        </button>
    </h5>
    <div id="advanced-filters-list" class="px-3 d-flex flex-column gap-3">
        <!-- Dynamic conditions -->
    </div>
</div>