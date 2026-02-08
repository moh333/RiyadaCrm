@extends('tenant::layout')

@section('title', __('tenant::settings.edit_rule') ?? 'Edit Rule')

@section('content')
    <div class="container-fluid py-4">
        {{-- Page Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('tenant.settings.crm.ctpower-blocks-fields.index') }}"
                                class="text-decoration-none">{{ __('tenant::settings.ct_power_blocks') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('tenant::settings.edit_rule') ?? 'Edit Rule' }}</li>
                    </ol>
                </nav>
                <h1 class="h3 mb-0 fw-bold">
                    <i class="bi bi-pencil-square text-primary me-2"></i>
                    {{ __('tenant::settings.edit_rule') ?? 'Edit Rule' }}
                </h1>
            </div>
            <div>
                <a href="{{ route('tenant.settings.crm.ctpower-blocks-fields.index') }}"
                    class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="bi bi-x-lg me-2"></i>{{ __('tenant::settings.cancel') }}
                </a>
            </div>
        </div>

        <form action="{{ route('tenant.settings.crm.ctpower-blocks-fields.update', $rule->ctpowerblockfieldsid) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row g-4">
                <div class="col-lg-8">
                    {{-- Basic Rule Settings --}}
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="fw-bold mb-0">{{ __('tenant::settings.basic_information') }}</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-4">
                                <label for="moduleid" class="form-label fw-semibold">{{ __('tenant::settings.select_module') }} <span class="text-danger">*</span></label>
                                <select class="form-select rounded-pill @error('moduleid') is-invalid @enderror" id="moduleid" name="moduleid" required>
                                    <option value="">{{ __('tenant::settings.choose_module') }}</option>
                                    @foreach($modules as $module)
                                        <option value="{{ $module->tabid }}" {{ $rule->moduleid == $module->tabid ? 'selected' : '' }}>
                                            {{ vtranslate($module->name, $module->name) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('moduleid')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="conditions" class="form-label fw-semibold">{{ __('tenant::settings.conditions') }}</label>
                                <div class="bg-light p-4 rounded-4 text-center border-dashed border-2">
                                    <i class="bi bi-funnel fs-1 text-muted mb-2 d-block"></i>
                                    <p class="text-muted small mb-0">Condition builder:</p>
                                    <code class="d-block bg-white p-2 rounded small border mb-2">{{ $rule->conditions }}</code>
                                    <input type="hidden" name="conditions" value="{{ $rule->conditions }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Actions Configuration --}}
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="fw-bold mb-0">{{ __('tenant::settings.actions_configuration') ?? 'Actions Configuration' }}</h5>
                        </div>
                        <div class="card-body p-4">
                            <p class="text-muted small mb-4">Define what happens when the conditions are met.</p>
                            
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('tenant::settings.hide_fields') ?? 'Hide Fields' }}</label>
                                    <select class="form-select select2" name="hidefieldid[]" multiple data-placeholder="Select fields to hide">
                                        {{-- In a full implementation, these would be prepopulated --}}
                                        @foreach(explode(',', $rule->hidefieldid) as $fid)
                                            @if($fid) <option value="{{ $fid }}" selected>{{ $fid }}</option> @endif
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('tenant::settings.show_fields') ?? 'Show Fields' }}</label>
                                    <select class="form-select select2" name="showfieldid[]" multiple data-placeholder="Select fields to show">
                                        @foreach(explode(',', $rule->showfieldid) as $fid)
                                            @if($fid) <option value="{{ $fid }}" selected>{{ $fid }}</option> @endif
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('tenant::settings.mandatory_fields') ?? 'Mandatory Fields' }}</label>
                                    <select class="form-select select2" name="mandatoryfieldid[]" multiple data-placeholder="Select fields to make mandatory">
                                        @foreach(explode(',', $rule->mandatoryfieldid) as $fid)
                                            @if($fid) <option value="{{ $fid }}" selected>{{ $fid }}</option> @endif
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('tenant::settings.readonly_fields') ?? 'Read-Only Fields' }}</label>
                                    <select class="form-select select2" name="readonlyfieldid[]" multiple data-placeholder="Select fields to make read-only">
                                        @foreach(explode(',', $rule->readonlyfieldid) as $fid)
                                            @if($fid) <option value="{{ $fid }}" selected>{{ $fid }}</option> @endif
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 mb-4 sticky-top" style="top: 100px;">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-3">{{ __('tenant::settings.summary') ?? 'Summary' }}</h5>
                            <p class="text-muted small">Update this rule to change its behavior across the CRM.</p>
                            
                            <hr class="my-4">
                            
                            <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 mb-3 shadow-sm">
                                <i class="bi bi-save me-2"></i>{{ __('tenant::settings.save_changes') ?? 'Save Changes' }}
                            </button>
                            <a href="{{ route('tenant.settings.crm.ctpower-blocks-fields.index') }}" class="btn btn-light w-100 rounded-pill py-2">
                                {{ __('tenant::settings.back_to_list') ?? 'Back to List' }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });
    });
</script>
@endpush
