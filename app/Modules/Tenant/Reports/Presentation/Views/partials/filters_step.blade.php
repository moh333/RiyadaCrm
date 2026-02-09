
<div class="py-2">
    <h5 class="fw-bold mb-4 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-funnel text-primary"></i>
            {{ __('reports::reports.advanced_filters') }}
        </div>
    </h5>
    <div class="px-3">
        {{-- All Conditions --}}
        <div class="mb-4">
            <h6 class="fw-bold d-flex align-items-center">
                {{ __('tenant::settings.all_conditions') }}
                <span class="text-muted small ms-2 fw-normal">{{ __('tenant::settings.all_conditions_desc') }}</span>
            </h6>
            <div id="allConditionsContainer" class="mt-3">
                {{-- All Conditions Rows --}}
            </div>
            <button type="button" class="btn btn-sm btn-soft-primary mt-2 add-condition-btn" data-type="all">
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
            <button type="button" class="btn btn-sm btn-soft-primary mt-2 add-condition-btn" data-type="any">
                <i class="bi bi-plus-circle me-1"></i>{{ __('tenant::settings.add_condition') }}
            </button>
        </div>
    </div>
</div>