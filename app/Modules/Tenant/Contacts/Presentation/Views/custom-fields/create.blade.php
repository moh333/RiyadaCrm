@extends('tenant::layout')

@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-0">{{ __('contacts::contacts.add_custom_field') }} - {{ $module }}</h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="{{ route('tenant.dashboard') }}">{{ __('contacts::contacts.dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ $module }}</li>
                        <li class="breadcrumb-item"><a
                                href="{{ route('tenant.custom-fields.index', ['module' => $module]) }}">{{ __('contacts::contacts.custom_fields') }}</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            {{ __('contacts::contacts.add_custom_field') }}
                        </li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="{{ route('tenant.custom-fields.index', ['module' => $module]) }}"
                    class="btn btn-outline-secondary px-4 py-2 rounded-3 shadow-sm">
                    {{ __('contacts::contacts.cancel') }}
                </a>
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form action="{{ route('tenant.custom-fields.store', ['module' => $module]) }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-white border-bottom py-3 px-4">
                            <h5 class="card-title fw-bold mb-0"><i
                                    class="bi bi-ui-checks me-2"></i>{{ __('contacts::contacts.field_definition') }}</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label
                                        class="form-label text-muted fw-bold small uppercase">{{ __('contacts::contacts.field_name') }}
                                        <span class="text-danger">*</span></label>
                                    <input type="text" name="fieldname" class="form-control rounded-3"
                                        value="{{ old('fieldname') }}" placeholder="e.g., linkedin_url" required>
                                    <small class="text-muted">{{ __('contacts::contacts.field_name_hint') }}</small>
                                </div>
                                <div class="col-md-6">
                                    <label
                                        class="form-label text-muted fw-bold small uppercase">{{ __('contacts::contacts.field_label') }}
                                        <span class="text-danger">*</span></label>
                                    <input type="text" name="fieldlabel" class="form-control rounded-3"
                                        value="{{ old('fieldlabel') }}" placeholder="e.g., LBL_LINKEDIN_PROFILE" required>
                                    <small class="text-muted">{{ __('contacts::contacts.field_label_hint') }}</small>
                                </div>

                                <div class="col-md-6">
                                    <label
                                        class="form-label text-muted fw-bold small uppercase">{{ __('contacts::contacts.field_type') }}
                                        <span class="text-danger">*</span></label>
                                    <select name="uitype" id="uitype_selector" class="form-select rounded-3" required>
                                        <option value="">{{ __('contacts::contacts.select_field_type') }}</option>
                                        @foreach($fieldTypes as $type)
                                            <option value="{{ $type->value }}" @if(old('uitype') == $type->value) selected @endif>
                                                {{ $type->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label
                                        class="form-label text-muted fw-bold small uppercase">{{ __('contacts::contacts.block') }}
                                        <span class="text-danger">*</span></label>
                                    <select name="block" class="form-select rounded-3" required>
                                        <option value="">{{ __('contacts::contacts.select_block') }}</option>
                                        @foreach($blocks as $block)
                                            <option value="{{ $block['id'] }}" @if(old('block') == $block['id']) selected @endif>
                                                {{ $block['label'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">{{ __('contacts::contacts.block_hint') }}</small>
                                </div>

                                <div id="picklist_values_container" class="col-md-12 d-none">
                                    <label class="form-label text-muted fw-bold small uppercase">Picklist Options
                                        <span class="text-danger">*</span></label>
                                    <textarea name="picklist_values" class="form-control rounded-3" rows="5"
                                        placeholder="Enter one option per line">{{ old('picklist_values') }}</textarea>
                                    <small class="text-muted">Enter the available options for this dropdown, one per
                                        line.</small>
                                </div>

                                <div class="col-md-6">
                                    <label
                                        class="form-label text-muted fw-bold small uppercase">{{ __('contacts::contacts.validation') }}</label>
                                    <select name="typeofdata" class="form-select rounded-3">
                                        <option value="V~O">{{ __('contacts::contacts.optional') }}</option>
                                        <option value="V~M">{{ __('contacts::contacts.mandatory') }}</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label text-muted fw-bold small uppercase">&nbsp;</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="quickcreate" value="1"
                                            id="quickcreate" @if(old('quickcreate')) checked @endif>
                                        <label class="form-check-label" for="quickcreate">
                                            {{ __('contacts::contacts.show_in_quick_create') }}
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <label
                                        class="form-label text-muted fw-bold small uppercase">{{ __('contacts::contacts.help_text') }}</label>
                                    <textarea name="helpinfo" class="form-control rounded-3" rows="2"
                                        placeholder="{{ __('contacts::contacts.help_text_placeholder') }}">{{ old('helpinfo') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 mb-4 sticky-top" style="top: 100px; z-index: 10;">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-3">{{ __('contacts::contacts.create_field') }}</h6>
                            <p class="small text-muted mb-4">{{ __('contacts::contacts.create_field_notice') }}</p>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg rounded-3 py-2">
                                    <i class="bi bi-save me-2"></i> {{ __('contacts::contacts.create_field') }}
                                </button>
                                <a href="{{ route('tenant.custom-fields.index', ['module' => $module]) }}"
                                    class="btn btn-light btn-lg rounded-3 py-2">
                                    {{ __('contacts::contacts.cancel') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const uitypeSelector = document.getElementById('uitype_selector');
            const picklistContainer = document.getElementById('picklist_values_container');

            function togglePicklist() {
                const val = uitypeSelector.value;
                // 15 = Picklist, 33 = MultiPicklist
                if (val === '15' || val === '33') {
                    picklistContainer.classList.remove('d-none');
                } else {
                    picklistContainer.classList.add('d-none');
                }
            }

            uitypeSelector.addEventListener('change', togglePicklist);
            togglePicklist(); // Initial check
        });
    </script>
@endsection