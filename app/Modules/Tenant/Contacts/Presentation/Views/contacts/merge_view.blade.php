@extends('tenant::layout')

@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-0">Merge Contacts</h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('tenant.contacts.index') }}">Contacts</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Merge</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h5 class="mb-0">Select values to preserve in the primary record</h5>
            </div>
            <div class="card-body p-0">
                <form action="{{ route('tenant.contacts.duplicates.process-merge') }}" method="POST">
                    @csrf
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="bg-light text-center">
                                <tr>
                                    <th style="width: 200px;">Field Name</th>
                                    @foreach($records as $index => $record)
                                        <th class="py-3">
                                            <div class="form-check d-flex justify-content-center align-items-center gap-2">
                                                <input class="form-check-input" type="radio" name="primary_id"
                                                    value="{{ $record->getId() }}" id="primary_{{ $record->getId() }}" {{ $index == 0 ? 'checked' : '' }} required>
                                                <label class="form-check-label fw-bold h6 mb-0"
                                                    for="primary_{{ $record->getId() }}">
                                                    Record #{{ $index + 1 }}
                                                    <span
                                                        class="d-block small text-muted font-normal mt-1">{{ $record->getContactNo() }}</span>
                                                </label>
                                            </div>
                                            @if($index == 0)
                                                <span class="badge bg-soft-primary text-primary mt-2">Recommended Primary</span>
                                            @endif
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($fields as $fieldName => $fieldLabel)
                                    <tr>
                                        <td class="bg-light fw-bold ps-4">{{ $fieldLabel }}</td>
                                        @foreach($records as $record)
                                            <td class="p-3">
                                                @php
                                                    $contactData = $record->toArray();
                                                    $value = $contactData[$fieldName] ?? '';
                                                    $isEmpty = empty($value);
                                                @endphp
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio"
                                                        name="merge_values[{{ $fieldName }}]" value="{{ $record->getId() }}"
                                                        id="val_{{ $fieldName }}_{{ $record->getId() }}" {{ $loop->first ? 'checked' : '' }} {{ $isEmpty ? 'disabled' : '' }}>
                                                    <label
                                                        class="form-check-label {{ $isEmpty ? 'text-muted fst-italic' : 'text-main' }}"
                                                        for="val_{{ $fieldName }}_{{ $record->getId() }}">
                                                        {{ $isEmpty ? 'Empty' : $value }}
                                                    </label>
                                                </div>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @foreach($records as $record)
                        <input type="hidden" name="all_ids[]" value="{{ $record->getId() }}">
                    @endforeach

                    <div class="p-4 bg-light border-top d-flex justify-content-between align-items-center">
                        <div class="text-danger small">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            Warning: Merged records will be deleted. This action cannot be undone.
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('tenant.contacts.duplicates.index') }}"
                                class="btn btn-outline-secondary px-4 rounded-pill">Cancel</a>
                            <button type="submit" class="btn btn-primary px-5 rounded-pill shadow-sm fw-bold">
                                Confirm & Merge <i class="bi bi-check-lg ms-1"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .font-normal {
            font-weight: 400;
        }

        .table-bordered> :not(caption)>*>* {
            border-width: 0 1px;
        }

        .bg-soft-primary {
            background-color: #eef2ff;
            color: #6366f1;
        }
    </style>
@endsection