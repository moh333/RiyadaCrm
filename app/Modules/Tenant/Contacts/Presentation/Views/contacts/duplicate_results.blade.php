@extends('tenant::layout')

@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-0">Duplicate Contacts Found</h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('tenant.contacts.index') }}">Contacts</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('tenant.contacts.duplicates.index') }}">Find
                                Duplicates</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Results</li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="{{ route('tenant.contacts.duplicates.index') }}" class="btn btn-outline-secondary rounded-3">
                    <i class="bi bi-arrow-left me-1"></i> Back to Criteria
                </a>
            </div>
        </div>

        @if(count($groups) == 0)
            <div class="card border-0 shadow-sm rounded-4 p-5 text-center">
                <div class="mb-4">
                    <i class="bi bi-check-circle text-success" style="font-size: 4rem;"></i>
                </div>
                <h4>No duplicates found!</h4>
                <p class="text-muted">No contacts match the selected criteria.</p>
                <div class="mt-4">
                    <a href="{{ route('tenant.contacts.index') }}" class="btn btn-primary px-4 rounded-pill">Back to
                        Contacts</a>
                </div>
            </div>
        @else
            <form action="{{ route('tenant.contacts.duplicates.merge') }}" method="POST">
                @csrf
                <div class="alert alert-info rounded-4 border-0 shadow-sm mb-4">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    Select up to 3 records in a group and click <strong>Merge</strong> to combine them.
                </div>

                @foreach($groups as $groupKey => $group)
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                        <div class="card-header bg-light py-3 px-4 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-primary rounded-pill me-2">{{ count($group['records']) }} Records</span>
                                <span class="text-main fw-bold">{!! $groupKey !!}</span>
                            </div>
                            <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 merge-btn"
                                data-group="{{ hash('md5', $groupKey) }}" disabled>
                                <i class="bi bi-intersect me-1"></i> Merge Selected
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="text-muted small">
                                    <tr>
                                        <th class="ps-4" style="width: 50px;">Select</th>
                                        <th>Contact No</th>
                                        <th>Full Name</th>
                                        <th>Email</th>
                                        <th>Account</th>
                                        <th class="pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($group['records'] as $record)
                                        <tr>
                                            <td class="ps-4">
                                                <input type="checkbox" name="ids[]" value="{{ $record->contactid }}"
                                                    class="form-check-input group-check-{{ hash('md5', $groupKey) }}"
                                                    data-group="{{ hash('md5', $groupKey) }}">
                                            </td>
                                            <td>{{ $record->contact_no }}</td>
                                            <td class="fw-bold">{{ $record->firstname }} {{ $record->lastname }}</td>
                                            <td>{{ $record->email ?: 'N/A' }}</td>
                                            <td>{{ $record->account_name ?: 'N/A' }}</td>
                                            <td class="pe-4">
                                                <a href="{{ route('tenant.contacts.show', $record->contactid) }}" target="_blank"
                                                    class="btn btn-sm btn-link text-decoration-none">View</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach

                <div class="mt-4 d-flex justify-content-center">
                    {!! $paginator->links() !!}
                </div>
            </form>
        @endif
    </div>

    @section('scripts')
        <script>
            document.querySelectorAll('.form-check-input').forEach(checkbox => {
                checkbox.addEventListener('change', function () {
                    const group = this.dataset.group;
                    const checkboxes = document.querySelectorAll('.group-check-' + group);
                    const checkedCount = Array.from(checkboxes).filter(c => c.checked).length;
                    const mergeBtn = this.closest('.card').querySelector('.merge-btn');

                    // Disable other groups if any checkbox in THIS group is checked
                    // Or keep it simple: allow selection only within ONE group at a time
                    if (checkedCount > 0) {
                        document.querySelectorAll('.form-check-input:not(.group-check-' + group + ')').forEach(c => c.disabled = true);
                    } else {
                        document.querySelectorAll('.form-check-input').forEach(c => c.disabled = false);
                    }

                    // Limit to 3 records
                    if (checkedCount >= 3) {
                        checkboxes.forEach(c => { if (!c.checked) c.disabled = true; });
                    }

                    // Enable/Disable merge button
                    mergeBtn.disabled = checkedCount < 2;
                });
            });
        </script>
    @endsection
@endsection