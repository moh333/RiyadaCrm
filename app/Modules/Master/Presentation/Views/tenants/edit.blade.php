@extends('master::layout')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Edit Tenant</h2>
            <p class="text-muted">Modify tenant configuration for <strong>{{ $tenant->id }}</strong></p>
        </div>
        <a href="{{ route('master.tenants.index') }}" class="btn btn-light border">
            <i class="bi bi-arrow-left me-2"></i>Back
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('master.tenants.update', $tenant->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label class="form-label fw-bold small text-muted text-uppercase">Tenant Identifier</label>
                            <input type="text" class="form-control form-control-lg bg-light" value="{{ $tenant->id }}"
                                readonly>
                            <div class="form-text text-warning"><i class="bi bi-exclamation-triangle me-1"></i>Identifier
                                cannot be changed.</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold small text-muted text-uppercase">Domain</label>
                            <div class="input-group">
                                <input type="text" name="domain" id="tenantDomain"
                                    class="form-control form-control-lg @error('domain') is-invalid @enderror"
                                    placeholder="e.g. apple.riyadacrm.test"
                                    value="{{ old('domain', $tenant->domains[0] ?? '') }}">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-globe"></i></span>
                            </div>
                            @error('domain')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Primary domain used to access this instance.</div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-lg flex-grow-1 shadow-sm">
                                <i class="bi bi-check-circle me-2"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection