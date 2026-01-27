@extends('tenant::layout')

@section('title', __('tenant::users.create_role'))

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">{{ __('tenant::users.create_role') }}</h2>
                <p class="text-muted mb-0">{{ __('tenant::users.define_new_role') }}</p>
            </div>
        </div>

        <form action="{{ route('tenant.settings.users.roles.store') }}" method="POST" id="roleForm">
            @csrf

            <!-- Basic Information -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-info-circle me-2"></i>{{ __('tenant::users.basic_info') }}</h5>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label for="rolename" class="form-label fw-bold">
                            {{ __('tenant::users.role_name') }} <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control rounded-3 @error('rolename') is-invalid @enderror"
                            id="rolename" name="rolename" value="{{ old('rolename') }}" required>
                        @error('rolename')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="parent_role_id" class="form-label fw-bold">{{ __('tenant::users.reports_to') }}</label>
                        @php
                            $preselectedParentId = request('parent_role_id') ?? old('parent_role_id');
                            $isParentPreselected = !empty($preselectedParentId);
                        @endphp
                        
                        <select name="parent_role_id" id="parent_role_id"
                            class="form-select rounded-3 @error('parent_role_id') is-invalid @enderror" 
                            {{ $isParentPreselected ? 'disabled' : '' }} required>
                            <option value="">{{ __('tenant::users.select_parent_role') }}</option>
                            @if (isset($parentRoles))
                                @foreach ($parentRoles as $role)
                                    <option value="{{ $role->roleid }}"
                                        {{ $preselectedParentId == $role->roleid ? 'selected' : '' }}>
                                        {{ $role->rolename }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        
                        @if($isParentPreselected)
                            <!-- Hidden input to submit the value when select is disabled -->
                            <input type="hidden" name="parent_role_id" value="{{ $preselectedParentId }}">
                            <small class="text-muted">{{ __('tenant::users.parent_role_preselected') }}</small>
                        @endif
                        
                        @error('parent_role_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Record Assignment -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-people me-2"></i>{{ __('tenant::users.record_assignment_rules') }}</h5>
                </div>
                <div class="card-body p-4">
                    <label class="form-label fw-bold mb-3">{{ __('tenant::users.can_assign_records_to') }}</label>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="radio" name="allowassignedrecordsto" id="assign_all"
                            value="1" {{ old('allowassignedrecordsto', '1') == '1' ? 'checked' : '' }}>
                        <label class="form-check-label" for="assign_all">
                            <strong>{{ __('tenant::users.all_users') }}</strong>
                            <small class="d-block text-muted">{{ __('tenant::users.all_users_description') }}</small>
                        </label>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="radio" name="allowassignedrecordsto"
                            id="assign_same_subordinate" value="2"
                            {{ old('allowassignedrecordsto') == '2' ? 'checked' : '' }}>
                        <label class="form-check-label" for="assign_same_subordinate">
                            <strong>{{ __('tenant::users.same_or_subordinate_role') }}</strong>
                            <small class="d-block text-muted">{{ __('tenant::users.same_or_subordinate_role_description') }}</small>
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="allowassignedrecordsto" id="assign_subordinate"
                            value="3" {{ old('allowassignedrecordsto') == '3' ? 'checked' : '' }}>
                        <label class="form-check-label" for="assign_subordinate">
                            <strong>{{ __('tenant::users.subordinate_role_only') }}</strong>
                            <small class="d-block text-muted">{{ __('tenant::users.subordinate_role_only_description') }}</small>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Privileges -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-shield-lock me-2"></i>{{ __('tenant::users.privileges_configuration') }}</h5>
                </div>
                <div class="card-body p-4">
                    <label class="form-label fw-bold mb-3">{{ __('tenant::users.privilege_assignment_method') }}</label>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="radio" name="profile_directly_related_to_role"
                            id="privilege_new" value="1" checked data-handler="new">
                        <label class="form-check-label" for="privilege_new">
                            <strong>{{ __('tenant::users.assign_directly') }}</strong>
                            <small class="d-block text-muted">{{ __('tenant::users.assign_directly_description') }}</small>
                        </label>
                    </div>

                    <div class="form-check mb-4">
                        <input class="form-check-input" type="radio" name="profile_directly_related_to_role"
                            id="privilege_existing" value="0" data-handler="existing">
                        <label class="form-check-label" for="privilege_existing">
                            <strong>{{ __('tenant::users.assign_existing') }}</strong>
                            <small class="d-block text-muted">{{ __('tenant::users.assign_existing_description') }}</small>
                        </label>
                    </div>

                    <!-- New Privileges Container -->
                    <div class="padding20px boxSizingBorderBox contentsBackground" id="newPrivilegesSection"
                        data-content="new">
                        
                        <div class="alert alert-info border-0 rounded-3 mb-4">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>{{ __('tenant::users.quick_start') }}</strong> {{ __('tenant::users.quick_start_description') }}
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">{{ __('tenant::users.copy_privileges_from') }}</label>
                            <select name="copy_from_profile" class="form-select rounded-3" id="copyFromProfile">
                                <option value="">-- {{ __('tenant::users.create_from_scratch') }} --</option>
                                @if(isset($profiles))
                                    @foreach($profiles as $profile)
                                        @if(!isset($profile->directly_related_to_role) || $profile->directly_related_to_role != 1)
                                            <option value="{{ $profile->profileid }}" {{ old('copy_from_profile') == $profile->profileid ? 'selected' : '' }}>
                                                {{ $profile->profilename }}
                                            </option>
                                        @endif
                                    @endforeach
                                @endif
                            </select>
                            <small class="text-muted">{{ __('tenant::users.copy_privileges_description') }}</small>
                        </div>

                        <hr class="my-4">
                        
                        <h6 class="fw-bold mb-3">{{ __('tenant::users.module_level_permissions') }}</h6>
                        <p class="text-muted small mb-3">{{ __('tenant::users.module_level_permissions_description') }}</p>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="privilegesTable">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 5%;" class="text-center">
                                            <input type="checkbox" class="form-check-input" id="selectAllModules"
                                                title="Select all modules">
                                        </th>
                                        <th style="width: 30%;">{{ __('tenant::users.module') }}</th>
                                        <th class="text-center" style="width: 13%;">
                                            <div class="d-flex flex-column align-items-center">
                                                <input type="checkbox" class="form-check-input mb-1 select-all-permission"
                                                    data-permission="view" title="Select all View">
                                                <span><i class="bi bi-eye me-1"></i>{{ __('tenant::users.view') }}</span>
                                            </div>
                                        </th>
                                        <th class="text-center" style="width: 13%;">
                                            <div class="d-flex flex-column align-items-center">
                                                <input type="checkbox" class="form-check-input mb-1 select-all-permission"
                                                    data-permission="create" title="Select all Create">
                                                <span><i class="bi bi-plus-circle me-1"></i>{{ __('tenant::users.create') }}</span>
                                            </div>
                                        </th>
                                        <th class="text-center" style="width: 13%;">
                                            <div class="d-flex flex-column align-items-center">
                                                <input type="checkbox" class="form-check-input mb-1 select-all-permission"
                                                    data-permission="edit" title="Select all Edit">
                                                <span><i class="bi bi-pencil me-1"></i>{{ __('tenant::users.edit') }}</span>
                                            </div>
                                        </th>
                                        <th class="text-center" style="width: 13%;">
                                            <div class="d-flex flex-column align-items-center">
                                                <input type="checkbox" class="form-check-input mb-1 select-all-permission"
                                                    data-permission="delete" title="Select all Delete">
                                                <span><i class="bi bi-trash me-1"></i>{{ __('tenant::users.delete') }}</span>
                                            </div>
                                        </th>
                                        <th class="text-center" style="width: 13%;"><i class="bi bi-gear me-1"></i>{{ __('tenant::users.tools') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="modulePrivilegesTable">
                                    @if (isset($modules))
                                        @foreach ($modules as $module)
                                            <tr data-module-id="{{ $module->tabid }}">
                                                <td class="text-center">
                                                    <input type="checkbox" class="form-check-input module-select-row"
                                                        data-module-id="{{ $module->tabid }}">
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-folder me-2 text-primary"></i>
                                                        <strong>{{ $module->name }}</strong>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <input type="checkbox" class="form-check-input permission-checkbox"
                                                        name="permissions[{{ $module->tabid }}][view]"
                                                        data-permission="view" data-module-id="{{ $module->tabid }}"
                                                        value="1">
                                                </td>
                                                <td class="text-center">
                                                    <input type="checkbox" class="form-check-input permission-checkbox"
                                                        name="permissions[{{ $module->tabid }}][create]"
                                                        data-permission="create" data-module-id="{{ $module->tabid }}"
                                                        value="1">
                                                </td>
                                                <td class="text-center">
                                                    <input type="checkbox" class="form-check-input permission-checkbox"
                                                        name="permissions[{{ $module->tabid }}][edit]"
                                                        data-permission="edit" data-module-id="{{ $module->tabid }}"
                                                        value="1">
                                                </td>
                                                <td class="text-center">
                                                    <input type="checkbox" class="form-check-input permission-checkbox"
                                                        name="permissions[{{ $module->tabid }}][delete]"
                                                        data-permission="delete" data-module-id="{{ $module->tabid }}"
                                                        value="1">
                                                </td>
                                                <td class="text-center">
                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-secondary rounded-pill"
                                                        onclick="openFieldPrivileges('{{ $module->tabid }}', '{{ $module->name }}')">
                                                        <i class="bi bi-sliders"></i> {{ __('tenant::users.configure') }}
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>

                        <input type="hidden" name="profile_directly_related_to_role_id" value="">
                        
                        <!-- JSON storage for field and tool privileges -->
                        <input type="hidden" name="field_privileges" id="fieldPrivilegesInput" value="{}">
                        <input type="hidden" name="tool_privileges" id="toolPrivilegesInput" value="{}">
                    </div>

                    <!-- Existing Privileges Container -->
                    <div style="display: none;" class="hide hidden" id="existingPrivilegesSection"
                        data-content="existing">
                        <div class="mb-3">
                            <label class="form-label  fw-bold">{{ __('tenant::users.select_profiles') }}</label>
                            <select class="form-select" multiple id="profilesList" name="profiles[]"
                                data-placeholder="{{ __('tenant::users.select_profiles_placeholder') }}" style="width: 100%">
                                @if (isset($profiles))
                                    @foreach ($profiles as $profile)
                                        @if (!isset($profile->directly_related_to_role) || $profile->directly_related_to_role != 1)
                                            <option value="{{ $profile->profileid }}">
                                                {{ $profile->profilename }}
                                            </option>
                                        @endif
                                    @endforeach
                                @endif
                            </select>
                            <small class="text-muted">{{ __('tenant::users.inherit_permissions_description') }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="d-flex justify-content-end gap-2 mb-4">
                <a href="{{ route('tenant.settings.users.roles.index') }}"
                    class="btn btn-outline-secondary rounded-3 px-4">
                    <i class="bi bi-x-lg me-2"></i>{{ __('tenant::users.cancel') }}
                </a>
                <button type="submit" class="btn btn-primary rounded-3 px-4 shadow-sm">
                    <i class="bi bi-save me-2"></i>{{ __('tenant::users.save') }}
                </button>
            </div>
        </form>
    </div>

    <!-- Field & Tool Privileges Modal -->
    <div class="modal fade" id="fieldPrivilegesModal" tabindex="-1" aria-labelledby="fieldPrivilegesModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold" id="fieldPrivilegesModalLabel">
                        <i class="bi bi-sliders me-2"></i>{{ __('tenant::users.field_tool_privileges') }}: <span id="modalModuleName"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="currentModuleId" value="">

                    <!-- Tabs for Fields and Tools -->
                    <ul class="nav nav-tabs mb-4" id="privilegesTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="fields-tab" data-bs-toggle="tab"
                                data-bs-target="#fields-panel" type="button" role="tab">
                                <i class="bi bi-input-cursor-text me-2"></i>{{ __('tenant::users.field_privileges') }}
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tools-tab" data-bs-toggle="tab" data-bs-target="#tools-panel"
                                type="button" role="tab">
                                <i class="bi bi-tools me-2"></i>{{ __('tenant::users.tool_privileges') }}
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="privilegesTabContent">
                        <!-- Field Privileges Tab -->
                        <div class="tab-pane fade show active" id="fields-panel" role="tabpanel">
                            <div class="alert alert-info border-0 rounded-3 mb-3">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>{{ __('tenant::users.field_permissions') }}:</strong> {{ __('tenant::users.field_permissions_control') }}
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 40%;">{{ __('tenant::users.field_name') }}</th>
                                            <th class="text-center" style="width: 20%;">
                                                <div class="form-check d-inline-block">
                                                    <input class="form-check-input select-all-field-permission"
                                                        type="radio" name="select_all_fields" value="0"
                                                        id="selectAllInvisible">
                                                    <label class="form-check-label" for="selectAllInvisible">
                                                        <i class="bi bi-eye-slash text-danger"></i> {{ __('tenant::users.invisible') }}
                                                    </label>
                                                </div>
                                            </th>
                                            <th class="text-center" style="width: 20%;">
                                                <div class="form-check d-inline-block">
                                                    <input class="form-check-input select-all-field-permission"
                                                        type="radio" name="select_all_fields" value="1"
                                                        id="selectAllReadonly">
                                                    <label class="form-check-label" for="selectAllReadonly">
                                                        <i class="bi bi-eye text-warning"></i> {{ __('tenant::users.read_only') }}
                                                    </label>
                                                </div>
                                            </th>
                                            <th class="text-center" style="width: 20%;">
                                                <div class="form-check d-inline-block">
                                                    <input class="form-check-input select-all-field-permission"
                                                        type="radio" name="select_all_fields" value="2"
                                                        id="selectAllWrite" checked>
                                                    <label class="form-check-label" for="selectAllWrite">
                                                        <i class="bi bi-pencil text-success"></i> {{ __('tenant::users.write') }}
                                                    </label>
                                                </div>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody id="fieldsTableBody">
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">
                                                <div class="spinner-border spinner-border-sm me-2" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                                {{ __('tenant::users.loading_fields') }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Tool Privileges Tab -->
                        <div class="tab-pane fade" id="tools-panel" role="tabpanel">
                            <div class="alert alert-info border-0 rounded-3 mb-3">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>{{ __('tenant::users.tool_permissions') }}:</strong> {{ __('tenant::users.tool_permissions_control') }}
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 40%;">{{ __('tenant::users.tool_name') }}</th>
                                            <th class="text-center" style="width: 30%;">
                                                <input type="checkbox" class="form-check-input" id="selectAllTools">
                                                {{ __('tenant::users.enabled') }}
                                            </th>
                                            <th style="width: 30%;">{{ __('tenant::users.description') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="toolsTableBody">
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">
                                                <div class="spinner-border spinner-border-sm me-2" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                                {{ __('tenant::users.loading_tools') }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top">
                    <button type="button" class="btn btn-secondary px-4 hstack gap-2" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg"></i>{{ __('tenant::users.cancel') }}
                    </button>
                    <button type="button" class="btn btn-primary px-4 hstack gap-2" onclick="saveFieldPrivileges()">
                        <i class="bi bi-check2-circle"></i>{{ __('tenant::users.save_privileges') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Initialize Select2 for profiles dropdown
            $('#profilesList').select2({
                placeholder: 'Choose one or more profiles',
                allowClear: true,
                width: '100%'
            });


            // Toggle privilege sections based on radio selection
            $('input[name="profile_directly_related_to_role"]').on('change', function() {
                if ($(this).val() === '1') {
                    // Show new privileges section (with copy dropdown and permissions table)
                    $('#newPrivilegesSection').slideDown();
                    $('#existingPrivilegesSection').slideUp();
                } else {
                    // Show existing profiles section (multi-select dropdown)
                    $('#newPrivilegesSection').slideUp();
                    $('#existingPrivilegesSection').slideDown();
                    
                    // Reset the copy privileges dropdown when switching away
                    $('#copyFromProfile').val('').trigger('change');
                }
            });

            // Copy privileges from existing profile
            $('#copyFromProfile').on('change', function() {
                const profileId = $(this).val();
                
                if (!profileId) {
                    // Clear all checkboxes if "Create from scratch" is selected
                    $('.permission-checkbox').prop('checked', false);
                    $('.module-select-row').prop('checked', false);
                    updateColumnCheckboxes();
                    updateSelectAllModules();
                    return;
                }

                // Show loading indicator
                const $select = $(this);
                $select.prop('disabled', true);
                
                // Fetch profile privileges via AJAX
                $.ajax({
                    url: '{{ route("tenant.settings.users.roles.get-profile-privileges") }}',
                    method: 'GET',
                    data: { profile_id: profileId },
                    success: function(response) {
                        // Clear all checkboxes first
                        $('.permission-checkbox').prop('checked', false);
                        
                        // Populate checkboxes based on response
                        if (response.privileges) {
                            $.each(response.privileges, function(tabid, permissions) {
                                if (permissions.view) {
                                    $(`input[name="permissions[${tabid}][view]"]`).prop('checked', true);
                                }
                                if (permissions.create) {
                                    $(`input[name="permissions[${tabid}][create]"]`).prop('checked', true);
                                }
                                if (permissions.edit) {
                                    $(`input[name="permissions[${tabid}][edit]"]`).prop('checked', true);
                                }
                                if (permissions.delete) {
                                    $(`input[name="permissions[${tabid}][delete]"]`).prop('checked', true);
                                }
                            });
                        }

                        // Copy field and tool privileges data
                        if (response.field_privileges) {
                            fieldPrivilegesData = response.field_privileges;
                            $('#fieldPrivilegesInput').val(JSON.stringify(fieldPrivilegesData));
                        }
                        if (response.tool_privileges) {
                            toolPrivilegesData = response.tool_privileges;
                            $('#toolPrivilegesInput').val(JSON.stringify(toolPrivilegesData));
                        }
                        
                        // Update all select-all checkboxes
                        updateModuleRowCheckboxes();
                        updateColumnCheckboxes();
                        updateSelectAllModules();
                        
                        // Show success message
                        showNotification('Privileges copied successfully! You can now customize them.', 'success');
                    },
                    error: function() {
                        showNotification('Error loading profile privileges. Please try again.', 'error');
                    },
                    complete: function() {
                        $select.prop('disabled', false);
                    }
                });
            });

            // Select All Modules (first column)
            $('#selectAllModules').on('change', function() {
                const checked = $(this).prop('checked');
                $('.module-select-row').each(function() {
                    $(this).prop('checked', checked);
                    const moduleId = $(this).data('module-id');
                    $(`tr[data-module-id="${moduleId}"] .permission-checkbox`).prop('checked', checked);
                });
                updateColumnCheckboxes();
            });

            // Select All for each permission column (View, Create, Edit, Delete)
            $('.select-all-permission').on('change', function() {
                const permission = $(this).data('permission');
                const checked = $(this).prop('checked');
                $(`.permission-checkbox[data-permission="${permission}"]`).prop('checked', checked);
                updateModuleRowCheckboxes();
                updateSelectAllModules();
            });

            // Module row checkbox - select all permissions for that module
            $('.module-select-row').on('change', function() {
                const moduleId = $(this).data('module-id');
                const checked = $(this).prop('checked');
                $(`tr[data-module-id="${moduleId}"] .permission-checkbox`).prop('checked', checked);
                updateSelectAllModules();
                updateColumnCheckboxes();
            });

            // Individual permission checkbox
            $('.permission-checkbox').on('change', function() {
                const moduleId = $(this).data('module-id');
                const permission = $(this).data('permission');

                // Update module row checkbox
                updateModuleRowCheckbox(moduleId);

                // Update column select-all
                updateColumnCheckbox(permission);

                // Update master select-all
                updateSelectAllModules();
            });

            // Update module row checkbox state
            function updateModuleRowCheckbox(moduleId) {
                const allChecked = $(`tr[data-module-id="${moduleId}"] .permission-checkbox:checked`).length ===
                    $(`tr[data-module-id="${moduleId}"] .permission-checkbox`).length;
                $(`.module-select-row[data-module-id="${moduleId}"]`).prop('checked', allChecked);
            }

            // Update all module row checkboxes
            function updateModuleRowCheckboxes() {
                $('.module-select-row').each(function() {
                    const moduleId = $(this).data('module-id');
                    updateModuleRowCheckbox(moduleId);
                });
            }

            // Update column checkbox state
            function updateColumnCheckbox(permission) {
                const allColumnChecked = $(`.permission-checkbox[data-permission="${permission}"]:checked`)
                    .length ===
                    $(`.permission-checkbox[data-permission="${permission}"]`).length;
                $(`.select-all-permission[data-permission="${permission}"]`).prop('checked', allColumnChecked);
            }

            // Update all column checkboxes
            function updateColumnCheckboxes() {
                ['view', 'create', 'edit', 'delete'].forEach(permission => {
                    updateColumnCheckbox(permission);
                });
            }

            // Update "Select All Modules" checkbox state
            function updateSelectAllModules() {
                const allModulesChecked = $('.module-select-row:checked').length === $('.module-select-row')
                    .length;
                $('#selectAllModules').prop('checked', allModulesChecked);
            }

            // Form validation
            $('#roleForm').on('submit', function(e) {
                const privilegeType = $('input[name="profile_directly_related_to_role"]:checked').val();

                if (privilegeType === '0') {
                    // Validate that at least one profile is selected
                    const selectedProfiles = $('#profilesList').val();
                    if (!selectedProfiles || selectedProfiles.length === 0) {
                        e.preventDefault();
                        alert("{{ __('tenant::users.select_at_least_one_profile') }}");
                        return false;
                    }
                }

                return true;
            });
        });

        // Global storage for field and tool privileges
        let fieldPrivilegesData = {};
        let toolPrivilegesData = {};

        function openFieldPrivileges(moduleId, moduleName) {
            // Set modal title and current module
            $('#modalModuleName').text(moduleName);
            $('#currentModuleId').val(moduleId);

            // Reset tabs to fields tab
            $('#fields-tab').tab('show');

            // Load fields and tools
            loadModuleFields(moduleId);
            loadModuleTools(moduleId);

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('fieldPrivilegesModal'));
            modal.show();
        }

        function loadModuleFields(moduleId) {
            $('#fieldsTableBody').html(`
                <tr>
                    <td colspan="4" class="text-center text-muted py-4">
                        <div class="spinner-border spinner-border-sm me-2" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        {{ __('tenant::users.loading_fields') }}
                    </td>
                </tr>
            `);

            $.ajax({
                url: '{{ route("tenant.settings.users.roles.get-module-fields") }}',
                method: 'GET',
                data: {
                    module_id: moduleId
                },
                success: function(response) {
                    if (response.fields && response.fields.length > 0) {
                        let html = '';
                        response.fields.forEach(function(field) {
                            const fieldId = field.fieldid;
                            const currentPermission = fieldPrivilegesData[moduleId]?.[fieldId] ?? 2; // Default: Write

                            html += `
                                <tr>
                                    <td>
                                        <strong>${field.fieldlabel}</strong>
                                        <br><small class="text-muted">${field.fieldname}</small>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check d-inline-block">
                                            <input class="form-check-input field-permission-radio" type="radio" 
                                                name="field_${fieldId}" value="0" 
                                                data-field-id="${fieldId}" data-module-id="${moduleId}"
                                                ${currentPermission == 0 ? 'checked' : ''}>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check d-inline-block">
                                            <input class="form-check-input field-permission-radio" type="radio" 
                                                name="field_${fieldId}" value="1" 
                                                data-field-id="${fieldId}" data-module-id="${moduleId}"
                                                ${currentPermission == 1 ? 'checked' : ''}>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check d-inline-block">
                                            <input class="form-check-input field-permission-radio" type="radio" 
                                                name="field_${fieldId}" value="2" 
                                                data-field-id="${fieldId}" data-module-id="${moduleId}"
                                                ${currentPermission == 2 ? 'checked' : ''}>
                                        </div>
                                    </td>
                                </tr>
                            `;
                        });
                        $('#fieldsTableBody').html(html);
                    } else {
                        $('#fieldsTableBody').html(`
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    {{ __('tenant::users.no_fields_available') }}
                                </td>
                            </tr>
                        `);
                    }
                },
                error: function() {
                    $('#fieldsTableBody').html(`
                        <tr>
                            <td colspan="4" class="text-center text-danger py-4">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                {{ __('tenant::users.error_loading_fields') }}
                            </td>
                        </tr>
                    `);
                }
            });
        }

        function loadModuleTools(moduleId) {
            $('#toolsTableBody').html(`
                <tr>
                    <td colspan="3" class="text-center text-muted py-4">
                        <div class="spinner-border spinner-border-sm me-2" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        {{ __('tenant::users.loading_tools') }}
                    </td>
                </tr>
            `);

            $.ajax({
                url: '{{ route("tenant.settings.users.roles.get-module-tools") }}',
                method: 'GET',
                data: {
                    module_id: moduleId
                },
                success: function(response) {
                    if (response.tools && response.tools.length > 0) {
                        let html = '';
                        response.tools.forEach(function(tool) {
                            const toolId = tool.toolid;
                            const isEnabled = toolPrivilegesData[moduleId]?.[toolId] ?? true; // Default: Enabled

                            html += `
                                <tr>
                                    <td><strong>${tool.toolname}</strong></td>
                                    <td class="text-center">
                                        <div class="form-check d-inline-block">
                                            <input class="form-check-input tool-permission-checkbox" type="checkbox" 
                                                data-tool-id="${toolId}" data-module-id="${moduleId}"
                                                ${isEnabled ? 'checked' : ''}>
                                        </div>
                                    </td>
                                    <td><small class="text-muted">${tool.description || ''}</small></td>
                                </tr>
                            `;
                        });
                        $('#toolsTableBody').html(html);
                    } else {
                        $('#toolsTableBody').html(`
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">
                                    {{ __('tenant::users.no_tools_available') }}
                                </td>
                            </tr>
                        `);
                    }
                },
                error: function() {
                    $('#toolsTableBody').html(`
                        <tr>
                            <td colspan="3" class="text-center text-danger py-4">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                {{ __('tenant::users.error_loading_tools') }}
                            </td>
                        </tr>
                    `);
                }
            });
        }

        function saveFieldPrivileges() {
            const moduleId = $('#currentModuleId').val();

            // Save field permissions
            if (!fieldPrivilegesData[moduleId]) {
                fieldPrivilegesData[moduleId] = {};
            }

            $('.field-permission-radio:checked').each(function() {
                const fieldId = $(this).data('field-id');
                const permission = $(this).val();
                fieldPrivilegesData[moduleId][fieldId] = permission;
            });

            // Save tool permissions
            if (!toolPrivilegesData[moduleId]) {
                toolPrivilegesData[moduleId] = {};
            }

            $('.tool-permission-checkbox').each(function() {
                const toolId = $(this).data('tool-id');
                const isEnabled = $(this).is(':checked');
                toolPrivilegesData[moduleId][toolId] = isEnabled;
            });

            // Sync with hidden inputs for form submission
            $('#fieldPrivilegesInput').val(JSON.stringify(fieldPrivilegesData));
            $('#toolPrivilegesInput').val(JSON.stringify(toolPrivilegesData));

            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('fieldPrivilegesModal')).hide();

            // Show success notification
            showNotification("{{ __('tenant::users.privileges_saved_successfully') }}", 'success');
        }

        // Select all field permissions
        $('.select-all-field-permission').on('change', function() {
            const permission = $(this).val();
            $(`.field-permission-radio[value="${permission}"]`).prop('checked', true);
        });

        // Select all tools
        $(document).on('change', '#selectAllTools', function() {
            const checked = $(this).prop('checked');
            $('.tool-permission-checkbox').prop('checked', checked);
        });

        function showNotification(message, type) {
            // Simple notification using Bootstrap alert
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            const icon = type === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle';
            
            const notification = $(`
                <div class="alert ${alertClass} alert-dismissible fade show position-fixed top-0 end-0 m-3" role="alert" style="z-index: 9999; min-width: 300px;">
                    <i class="bi ${icon} me-2"></i>${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `);
            
            $('body').append(notification);
            
            // Auto-dismiss after 3 seconds
            setTimeout(function() {
                notification.alert('close');
            }, 3000);
        }
    </script>
@endpush

@push('styles')
    <style>
        .contentsBackground {
            background-color: #f8f9fa;
            border-radius: 8px;
        }

        .padding20px {
            padding: 20px;
        }

        .boxSizingBorderBox {
            box-sizing: border-box;
        }

        .hide {
            display: none;
        }

        .form-check-input:checked {
            background-color: #6366f1;
            border-color: #6366f1;
        }
    </style>
@endpush