@extends('tenant::layout')

@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-0">Find Duplicates</h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('tenant.contacts.index') }}">Contacts</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Find Duplicates</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-white border-bottom py-3 px-4">
                        <h5 class="mb-0">Select Matching Criteria</h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('tenant.contacts.duplicates.search') }}" method="GET">
                            <p class="text-muted mb-4">Select the fields you want to use to find duplicate contacts.
                                Contacts that have matching values in all selected fields will be grouped together.</p>

                            <div class="row g-3">
                                @foreach($fields as $fieldName => $fieldLabel)
                                    <div class="col-md-6">
                                        <div class="form-check p-3 border rounded-3 hover-bg">
                                            <input class="form-check-input ms-0 me-2" type="checkbox" name="match_fields[]"
                                                value="{{ $fieldName }}" id="field_{{ $fieldName }}" {{ in_array($fieldName, ['lastname', 'email']) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-semibold" for="field_{{ $fieldName }}">
                                                {{ $fieldLabel }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-5">
                                <a href="{{ route('tenant.contacts.index') }}"
                                    class="btn btn-outline-secondary px-4 rounded-pill">Cancel</a>
                                <button type="submit" class="btn btn-primary px-5 rounded-pill shadow-sm fw-bold">
                                    Find Duplicates <i class="bi bi-search ms-1"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .hover-bg:hover {
            background-color: #f8fafc;
            border-color: #6366f1 !important;
        }

        .form-check-input:checked+.form-check-label {
            color: #6366f1;
        }
    </style>
@endsection