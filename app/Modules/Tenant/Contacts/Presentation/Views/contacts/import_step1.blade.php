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
                        <li class="breadcrumb-item active" aria-current="page">Import</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-white border-bottom py-3 px-4">
                        <h5 class="mb-0">Step 1: Upload CSV File</h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('tenant.contacts.import.step2') }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="mb-4">
                                <label class="form-label fw-bold">Select CSV File</label>
                                <input type="file" name="file" class="form-control rounded-3" accept=".csv" required>
                                <div class="form-text mt-2">
                                    Only .csv files are supported. Make sure your file has a header row.
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Duplicate Handling</label>
                                <select name="duplicate_handling" class="form-select rounded-3">
                                    <option value="skip">Skip duplicates</option>
                                    <option value="overwrite">Overwrite existing records</option>
                                </select>
                                <div class="form-text mt-2">
                                    Duplicates will be identified by <strong>Email</strong>.
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-5">
                                <a href="{{ route('tenant.contacts.index') }}"
                                    class="btn btn-outline-secondary px-4 rounded-pill">Cancel</a>
                                <button type="submit" class="btn btn-primary px-5 rounded-pill shadow-sm fw-bold">
                                    Next: Map Fields <i class="bi bi-arrow-right ms-1"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection