@extends('master::layout')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1">Create Tenant</h2>
        <p class="text-muted">Onboard a new customer</p>
    </div>
    <a href="{{ route('master.tenants.index') }}" class="btn btn-light border">
        <i class="bi bi-arrow-left me-2"></i>Back
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form id="createTenantForm" action="{{ route('master.tenants.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-muted text-uppercase">Tenant Identifier</label>
                        <input type="text" name="id" id="tenantId" class="form-control form-control-lg @error('id') is-invalid @enderror" placeholder="e.g. apple" value="{{ old('id') }}">
                        <div class="form-text">This will be used for internal identification.</div>
                        <div class="invalid-feedback" id="idError"></div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small text-muted text-uppercase">Domain</label>
                        <div class="input-group">
                            <input type="text" name="domain" id="tenantDomain" class="form-control form-control-lg @error('domain') is-invalid @enderror" placeholder="apple.riyadacrm.test" value="{{ old('domain') }}">
                            <span class="input-group-text bg-light text-muted"><i class="bi bi-globe"></i></span>
                        </div>
                        <div class="invalid-feedback" id="domainError"></div>
                    </div>

                    <!-- Progress Bar Container -->
                    <div id="progressContainer" class="d-none mb-4">
                        <label class="form-label small text-muted fw-bold d-flex justify-content-between">
                            <span>Creating Tenant...</span>
                            <span id="timeEstimate">Est. 45s</span>
                        </label>
                        <div class="progress" style="height: 10px;">
                            <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%"></div>
                        </div>
                        <div class="text-center mt-2 small text-muted" id="progressStatus">Initializing...</div>
                    </div>

                    <div id="alertContainer"></div>

                    <hr class="my-4">

                    <button type="submit" id="submitBtn" class="btn btn-primary btn-lg w-100 shadow-sm">
                        <i class="bi bi-cloud-plus me-2"></i>Create Tenant
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('createTenantForm');
    const submitBtn = document.getElementById('submitBtn');
    const progressContainer = document.getElementById('progressContainer');
    const progressBar = document.getElementById('progressBar');
    const progressStatus = document.getElementById('progressStatus');
    const alertContainer = document.getElementById('alertContainer');
    
    // Helper to clear validation errors
    const clearErrors = () => {
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        document.querySelectorAll('.invalid-feedback').forEach(el => {
             el.innerText = '';
             el.style.display = 'none';
        });
    };

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        clearErrors();
        alertContainer.innerHTML = '';
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processing...';
        progressContainer.classList.remove('d-none');
        
        // --- Progress Simulation ---
        let progress = 0;
        const totalDuration = 45000; 
        const intervalTime = 500;
        const increment = (100 / (totalDuration / intervalTime)); 
        
        const progressInterval = setInterval(() => {
            progress += increment;
            if (progress > 95) progress = 95; // Cap to 95% until response
            
            progressBar.style.width = progress + '%';
            
            // UX Status Messages
            if(progress < 15) progressStatus.innerText = "Initializing Database...";
            else if(progress < 40) progressStatus.innerText = "Provisioning Resources...";
            else if(progress < 80) progressStatus.innerText = "Running Migrations (this may take a while)...";
            else progressStatus.innerText = "Finalizing Setup...";

        }, intervalTime);

        // --- AJAX Request ---
        const formData = new FormData(form);

        fetch("{{ route('master.tenants.store') }}", {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(async response => {
            const isJson = response.headers.get('content-type')?.includes('application/json');
            const data = isJson ? await response.json() : null;

            if (!response.ok) {
                const error = new Error(data?.message || response.statusText);
                error.data = data;
                error.status = response.status;
                throw error;
            }
            return data;
        })
        .then(body => {
            clearInterval(progressInterval);
            
            if (body.success) {
                progressBar.style.width = '100%';
                progressBar.classList.remove('bg-primary');
                progressBar.classList.add('bg-success');
                progressStatus.innerText = "Done!";
                
                alertContainer.innerHTML = `<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>${body.message}</div>`;
                
                setTimeout(() => {
                    window.location.href = body.redirect;
                }, 1000);
            }
        })
        .catch(error => {
            clearInterval(progressInterval);
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-cloud-plus me-2"></i>Create Tenant';
            progressContainer.classList.add('d-none');
            
            if (error.status === 422 && error.data && error.data.errors) {
                const errors = error.data.errors;
                Object.keys(errors).forEach(key => {
                    let input = document.getElementById('tenant' + key.charAt(0).toUpperCase() + key.slice(1)); 
                    if (!input) input = document.querySelector(`[name="${key}"]`);
                    
                    if(input) {
                        input.classList.add('is-invalid');
                        const errorDiv = document.getElementById(key + 'Error');
                        if (errorDiv) {
                            errorDiv.innerText = errors[key][0];
                            errorDiv.style.display = 'block';
                        }
                    }
                });
                alertContainer.innerHTML = `<div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-2"></i>Please correct the errors below.</div>`;
            } else {
                alertContainer.innerHTML = `<div class="alert alert-danger"><i class="bi bi-exclamation-circle me-2"></i>${error.message || 'An unexpected error occurred.'}</div>`;
            }
        });
    });
});
</script>
@endsection