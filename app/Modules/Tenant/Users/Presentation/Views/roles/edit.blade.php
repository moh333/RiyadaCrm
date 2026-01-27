@extends('tenant::layout')

@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">
                    <i class="bi bi-pencil me-2"></i>{{ __('tenant::users.update_role') ?? 'Edit Role' }}
                </h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('tenant.settings.users.roles.index') }}">Roles</a></li>
                        <li class="breadcrumb-item active">Edit: {{ $role->rolename }}</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('tenant.settings.users.roles.index') }}" class="btn btn-outline-secondary rounded-3">
                <i class="bi bi-arrow-left me-1"></i>{{ __('tenant::users.cancel') }}
            </a>
        </div>

        <form action="{{ route('tenant.settings.users.roles.update', $role->roleid) }}" method="POST" id="roleForm">
            @csrf
            @method('PUT')
            
            <!-- Basic Information -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-info-circle me-2"></i>Basic Information</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('tenant::users.role_name') ?? 'Role Name' }} <span class="text-danger">*</span></label>
                            <input type="text" name="rolename" class="form-control rounded-3" value="{{ $role->rolename }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('tenant::users.reports_to') ?? 'Reports To' }}</label>
                            @php
                                $pathParts = explode('::', $role->parentrole);
                                $parentId = count($pathParts) > 1 ? $pathParts[count($pathParts) - 2] : null;
                            @endphp
                            <select disabled name="parent_role_id" class="form-select rounded-3 bg-light">
                                @foreach($parentRoles as $pRole)
                                    <option value="{{ $pRole->roleid }}" 
                                        {{ $parentId == $pRole->roleid ? 'selected' : '' }}>
                                        {{ $pRole->rolename }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted mt-2 d-block">Parent role cannot be changed directly during editing.</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Record Assignment -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-people me-2"></i>Record Assignment Rules</h5>
                </div>
                <div class="card-body p-4">
                    <label class="form-label fw-bold mb-3">Can Assign Records To:</label>
                    @php
                        $currentAssignType = $role->assign_type ?? 'all';
                    @endphp
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="radio" name="assign_type" id="assign_all" value="all" 
                            {{ $currentAssignType == 'all' ? 'checked' : '' }}>
                        <label class="form-check-label" for="assign_all">
                            <strong>All Users</strong>
                            <small class="d-block text-muted">Users with this role can assign records to any user in the system</small>
                        </label>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="radio" name="assign_type" id="assign_same_subordinate" value="same_or_subordinate"
                            {{ $currentAssignType == 'same_or_subordinate' ? 'checked' : '' }}>
                        <label class="form-check-label" for="assign_same_subordinate">
                            <strong>Users having Same Role or Subordinate Role</strong>
                            <small class="d-block text-muted">Can assign to users in the same role or any subordinate roles</small>
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="assign_type" id="assign_subordinate" value="subordinate"
                            {{ $currentAssignType == 'subordinate' ? 'checked' : '' }}>
                        <label class="form-check-label" for="assign_subordinate">
                            <strong>Users having Subordinate Role</strong>
                            <small class="d-block text-muted">Can only assign to users in subordinate roles</small>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Privileges -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-shield-lock me-2"></i>Privileges Configuration</h5>
                </div>
                <div class="card-body p-4">
                    <label class="form-label fw-bold mb-3">Privilege Assignment Method:</label>
                    
                    @php
                        $currentPrivilegeType = $role->privilege_type ?? 'direct';
                    @endphp
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="radio" name="privilege_type" id="privilege_direct" value="direct"
                            {{ $currentPrivilegeType == 'direct' ? 'checked' : '' }}>
                        <label class="form-check-label" for="privilege_direct">
                            <strong>Assign privileges directly to Role</strong>
                            <small class="d-block text-muted">Define custom privileges for this role</small>
                        </label>
                    </div>
                    
                    <div class="form-check mb-4">
                        <input class="form-check-input" type="radio" name="privilege_type" id="privilege_profile" value="profile"
                            {{ $currentPrivilegeType == 'profile' ? 'checked' : '' }}>
                        <label class="form-check-label" for="privilege_profile">
                            <strong>Assign privileges from existing profiles</strong>
                            <small class="d-block text-muted">Use an existing profile's privileges</small>
                        </label>
                    </div>

                    <!-- Direct Privileges Section -->
                    <div id="directPrivilegesSection" class="privilege-section" style="display: {{ $currentPrivilegeType == 'direct' ? 'block' : 'none' }};">
                        <div class="alert alert-info border-0 rounded-3 mb-4">
                            <i class="bi bi-info-circle me-2"></i>
                            You can copy privileges from an existing profile to override current settings.
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">Copy privileges from:</label>
                            <select name="copy_from_profile" class="form-select rounded-3" id="copyFromProfile">
                                <option value="">-- Keep current settings --</option>
                                @if(isset($profiles))
                                    @foreach($profiles as $profile)
                                        <option value="{{ $profile->profileid }}">{{ $profile->profilename }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <div class="border-top pt-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h6 class="fw-bold mb-1">Edit privileges of this role:</h6>
                                    <p class="text-muted small mb-0">Configure module-level permissions for this role</p>
                                </div>
                                
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-hover align-middle" id="privilegesTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 5%;" class="text-center">
                                                <input type="checkbox" class="form-check-input" id="selectAllModules" title="Select all modules">
                                            </th>
                                            <th style="width: 25%;">Module</th>
                                            <th class="text-center" style="width: 14%;">
                                                <div class="d-flex flex-column align-items-center">
                                                    <input type="checkbox" class="form-check-input mb-1 select-all-permission" data-permission="view" title="Select all View">
                                                    <span><i class="bi bi-eye me-1"></i>View</span>
                                                </div>
                                            </th>
                                            <th class="text-center" style="width: 14%;">
                                                <div class="d-flex flex-column align-items-center">
                                                    <input type="checkbox" class="form-check-input mb-1 select-all-permission" data-permission="create" title="Select all Create">
                                                    <span><i class="bi bi-plus-circle me-1"></i>Create</span>
                                                </div>
                                            </th>
                                            <th class="text-center" style="width: 14%;">
                                                <div class="d-flex flex-column align-items-center">
                                                    <input type="checkbox" class="form-check-input mb-1 select-all-permission" data-permission="edit" title="Select all Edit">
                                                    <span><i class="bi bi-pencil me-1"></i>Edit</span>
                                                </div>
                                            </th>
                                            <th class="text-center" style="width: 14%;">
                                                <div class="d-flex flex-column align-items-center">
                                                    <input type="checkbox" class="form-check-input mb-1 select-all-permission" data-permission="delete" title="Select all Delete">
                                                    <span><i class="bi bi-trash me-1"></i>Delete</span>
                                                </div>
                                            </th>
                                            <th class="text-center" style="width: 14%;"><i class="bi bi-gear me-1"></i>Tools</th>
                                        </tr>
                                    </thead>
                                    <tbody id="modulePrivilegesTable">
                                        @if(isset($modules))
                                            @foreach($modules as $module)
                                                @php
                                                    $modulePrivileges = $rolePrivileges[$module->tabid] ?? [];
                                                @endphp
                                                <tr data-module-id="{{ $module->tabid }}">
                                                    <td class="text-center">
                                                        <input type="checkbox" class="form-check-input module-select-row" data-module-id="{{ $module->tabid }}">
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <i class="bi bi-folder me-2 text-primary"></i>
                                                            <strong>{{ $module->name }}</strong>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="checkbox" class="form-check-input permission-checkbox" 
                                                            name="privileges[{{ $module->tabid }}][view]" 
                                                            data-permission="view"
                                                            data-module-id="{{ $module->tabid }}"
                                                            value="1"
                                                            {{ isset($modulePrivileges['view']) && $modulePrivileges['view'] ? 'checked' : '' }}>
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="checkbox" class="form-check-input permission-checkbox" 
                                                            name="privileges[{{ $module->tabid }}][create]" 
                                                            data-permission="create"
                                                            data-module-id="{{ $module->tabid }}"
                                                            value="1"
                                                            {{ isset($modulePrivileges['create']) && $modulePrivileges['create'] ? 'checked' : '' }}>
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="checkbox" class="form-check-input permission-checkbox" 
                                                            name="privileges[{{ $module->tabid }}][edit]" 
                                                            data-permission="edit"
                                                            data-module-id="{{ $module->tabid }}"
                                                            value="1"
                                                            {{ isset($modulePrivileges['edit']) && $modulePrivileges['edit'] ? 'checked' : '' }}>
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="checkbox" class="form-check-input permission-checkbox" 
                                                            name="privileges[{{ $module->tabid }}][delete]" 
                                                            data-permission="delete"
                                                            data-module-id="{{ $module->tabid }}"
                                                            value="1"
                                                            {{ isset($modulePrivileges['delete']) && $modulePrivileges['delete'] ? 'checked' : '' }}>
                                                    </td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" 
                                                                onclick="openFieldPrivileges('{{ $module->tabid }}', '{{ $module->name }}')">
                                                            <i class="bi bi-sliders"></i> Configure
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Selection Section -->
                    <div id="profileSelectionSection" class="privilege-section" style="display: {{ $currentPrivilegeType == 'profile' ? 'block' : 'none' }};">
                        <div class="alert alert-warning border-0 rounded-3 mb-4">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            This role will inherit all privileges from the selected profile.
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Select Profile:</label>
                            <select name="assigned_profile_id" class="form-select rounded-3" id="assignedProfile">
                                <option value="">-- Select a profile --</option>
                                @if(isset($profiles))
                                    @foreach($profiles as $profile)
                                        <option value="{{ $profile->profileid }}"
                                            {{ isset($role->assigned_profile_id) && $role->assigned_profile_id == $profile->profileid ? 'selected' : '' }}>
                                            {{ $profile->profilename }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="d-flex justify-content-end gap-2 mb-4">
                <a href="{{ route('tenant.settings.users.roles.index') }}" class="btn btn-outline-secondary rounded-3 px-4">
                    <i class="bi bi-x-lg me-2"></i>Cancel
                </a>
                <button type="submit" class="btn btn-primary rounded-3 px-4 shadow-sm">
                    <i class="bi bi-save me-2"></i>{{ __('tenant::users.update') }}
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Toggle privilege sections based on radio selection
    $('input[name="privilege_type"]').on('change', function() {
        if ($(this).val() === 'direct') {
            $('#directPrivilegesSection').slideDown();
            $('#profileSelectionSection').slideUp();
        } else {
            $('#directPrivilegesSection').slideUp();
            $('#profileSelectionSection').slideDown();
        }
    });

    // Select All Modules & Permissions (master checkbox)
    $('#selectAllModulesPermissions').on('change', function () {
        const checked = $(this).prop('checked');
        $('.permission-checkbox').prop('checked', checked);
        $('.module-select-row').prop('checked', checked);
        $('#selectAllModules').prop('checked', checked);
        $('.select-all-permission').prop('checked', checked);
    });

    // Select All Modules (first column)
    $('#selectAllModules').on('change', function () {
        const checked = $(this).prop('checked');
        $('.module-select-row').each(function() {
            $(this).prop('checked', checked);
            const moduleId = $(this).data('module-id');
            $(`tr[data-module-id="${moduleId}"] .permission-checkbox`).prop('checked', checked);
        });
        updateMasterCheckbox();
    });

    // Select All for each permission column (View, Create, Edit, Delete)
    $('.select-all-permission').on('change', function () {
        const permission = $(this).data('permission');
        const checked = $(this).prop('checked');
        $(`.permission-checkbox[data-permission="${permission}"]`).prop('checked', checked);
        updateMasterCheckbox();
    });

    // Module row checkbox - select all permissions for that module
    $('.module-select-row').on('change', function () {
        const moduleId = $(this).data('module-id');
        const checked = $(this).prop('checked');
        $(`tr[data-module-id="${moduleId}"] .permission-checkbox`).prop('checked', checked);
        updateSelectAllModules();
        updateMasterCheckbox();
    });

    // Individual permission checkbox
    $('.permission-checkbox').on('change', function () {
        const moduleId = $(this).data('module-id');
        const permission = $(this).data('permission');
        
        // Update module row checkbox
        const allChecked = $(`tr[data-module-id="${moduleId}"] .permission-checkbox:checked`).length === 
                           $(`tr[data-module-id="${moduleId}"] .permission-checkbox`).length;
        $(`.module-select-row[data-module-id="${moduleId}"]`).prop('checked', allChecked);
        
        // Update column select-all
        const allColumnChecked = $(`.permission-checkbox[data-permission="${permission}"]:checked`).length === 
                                 $(`.permission-checkbox[data-permission="${permission}"]`).length;
        $(`.select-all-permission[data-permission="${permission}"]`).prop('checked', allColumnChecked);
        
        updateSelectAllModules();
        updateMasterCheckbox();
    });

    // Update "Select All Modules" checkbox state
    function updateSelectAllModules() {
        const allModulesChecked = $('.module-select-row:checked').length === $('.module-select-row').length;
        $('#selectAllModules').prop('checked', allModulesChecked);
    }

    // Update master "Select All Modules & Permissions" checkbox state
    function updateMasterCheckbox() {
        const allChecked = $('.permission-checkbox:checked').length === $('.permission-checkbox').length;
        $('#selectAllModulesPermissions').prop('checked', allChecked);
    }

    // Load privileges from profile when copying
    $('#copyFromProfile').on('change', function () {
        const profileId = $(this).val();
        if (profileId) {
            // Show loading state
            $('#privilegesTable').css('opacity', '0.5');
            
            // AJAX call to get profile privileges
            $.ajax({
                url: '{{ route("tenant.settings.users.roles.get-profile-privileges") }}',
                method: 'GET',
                data: { profile_id: profileId },
                success: function (response) {
                    // Clear all checkboxes first
                    $('.permission-checkbox').prop('checked', false);
                    
                    // Set privileges from response
                    if (response.privileges) {
                        $.each(response.privileges, function (tabid, permissions) {
                            if (permissions.view) {
                                $(`input[name="privileges[${tabid}][view]"]`).prop('checked', true);
                            }
                            if (permissions.create) {
                                $(`input[name="privileges[${tabid}][create]"]`).prop('checked', true);
                            }
                            if (permissions.edit) {
                                $(`input[name="privileges[${tabid}][edit]"]`).prop('checked', true);
                            }
                            if (permissions.delete) {
                                $(`input[name="privileges[${tabid}][delete]"]`).prop('checked', true);
                            }
                        });
                    }
                    
                    // Update all select-all checkboxes
                    $('.permission-checkbox').trigger('change');
                    $('#privilegesTable').css('opacity', '1');
                },
                error: function () {
                    alert('Error loading profile privileges. Please try again.');
                    $('#privilegesTable').css('opacity', '1');
                }
            });
        }
    });
    
    // Initialize select-all checkboxes based on current state
    $('.permission-checkbox').trigger('change');
});

function openFieldPrivileges(moduleId, moduleName) {
    // This would open a modal for field-level privileges
    alert('Field and Tool privileges configuration for ' + moduleName + ' (Module ID: ' + moduleId + ') would open here.');
}
</script>
@endpush

@push('styles')
<style>
    .privilege-section {
        transition: all 0.3s ease;
    }
    
    .table tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .form-check-input:checked {
        background-color: #6366f1;
        border-color: #6366f1;
    }
</style>
@endpush