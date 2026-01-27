@extends('tenant::layout')

@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-0">Import Contacts</h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('tenant.contacts.index') }}">Contacts</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Map Fields</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h5 class="mb-0">Step 2: Map CSV Columns to Fields</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('tenant.contacts.import.process') }}" method="POST">
                    @csrf
                    <input type="hidden" name="file_path" value="{{ $file_path }}">
                    <input type="hidden" name="duplicate_handling" value="{{ $duplicate_handling }}">

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">CSV Column Header</th>
                                    <th>Sample Data (Row 1)</th>
                                    <th>Map to Contact Field</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($headers as $index => $header)
                                    <tr>
                                        <td class="ps-4 fw-bold text-muted">{{ $header }}</td>
                                        <td class="text-main">{{ $sample_row[$index] ?? 'N/A' }}</td>
                                        <td>
                                            <select name="mapping[{{ $index }}]" class="form-select rounded-3">
                                                <option value="">-- Ignore this column --</option>
                                                @foreach($fields as $fieldName => $fieldLabel)
                                                    <option value="{{ $fieldName }}" {{ strtolower($header) == strtolower($fieldLabel) || strtolower($header) == strtolower($fieldName) ? 'selected' : '' }}>
                                                        {{ $fieldLabel }} ({{ $fieldName }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-5">
                        <p class="text-muted small mb-0">
                            Required fields (if not mapped, import might fail): <strong>Last Name</strong>
                        </p>
                        <div class="d-flex gap-2">
                            <a href="{{ route('tenant.contacts.import.step1') }}"
                                class="btn btn-outline-secondary px-4 rounded-pill">Back</a>
                            <button type="submit" class="btn btn-success px-5 rounded-pill shadow-sm fw-bold">
                                Finish: Start Import <i class="bi bi-play-fill ms-1"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection