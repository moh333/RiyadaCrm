<script>
    $(document).ready(function () {
        // Initialize Select2 if needed (ProfilesController doesn't use multiple profiles select like Roles)

        // Copy privileges from existing profile
        $('#copyFromProfile').on('change', function () {
            const profileId = $(this).val();

            if (!profileId) {
                // Clear all checkboxes if "Start fresh" is selected
                $('.permission-checkbox').prop('checked', false);
                $('.module-select-row').prop('checked', false);
                fieldPrivilegesData = {};
                toolPrivilegesData = {};
                $('#fieldPrivilegesInput').val('{}');
                $('#toolPrivilegesInput').val('{}');
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
                success: function (response) {
                    // Clear all checkboxes first
                    $('.permission-checkbox').prop('checked', false);

                    // Populate checkboxes based on response
                    if (response.privileges) {
                        $.each(response.privileges, function (tabid, permissions) {
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

                    showNotification("{{ __('tenant::users.privileges_copied_successfully') }}", 'success');
                },
                error: function () {
                    showNotification("{{ __('tenant::users.error_loading_profile_privileges') }}", 'error');
                },
                complete: function () {
                    $select.prop('disabled', false);
                }
            });
        });

        // Select All Modules (master checkbox)
        $('#selectAllModules').on('change', function () {
            const checked = $(this).prop('checked');
            $('.module-select-row').each(function () {
                $(this).prop('checked', checked);
                const moduleId = $(this).data('module-id');
                $(`tr[data-module-id="${moduleId}"] .permission-checkbox`).prop('checked', checked);
            });
            updateColumnCheckboxes();
        });

        // Select All for each permission column
        $('.select-all-permission').on('change', function () {
            const permission = $(this).data('permission');
            const checked = $(this).prop('checked');
            $(`.permission-checkbox[data-permission="${permission}"]`).prop('checked', checked);

            // Update row checkboxes
            $('.module-select-row').each(function () {
                updateModuleRowCheckbox($(this).data('module-id'));
            });
            updateSelectAllModules();
        });

        // Individual permission checkbox click
        $(document).on('change', '.permission-checkbox', function () {
            const moduleId = $(this).data('module-id');
            const permission = $(this).data('permission');

            updateModuleRowCheckbox(moduleId);
            updateColumnCheckbox(permission);
            updateSelectAllModules();
        });

        // Module row checkbox click
        $(document).on('change', '.module-select-row', function () {
            const moduleId = $(this).data('module-id');
            const checked = $(this).prop('checked');
            $(`tr[data-module-id="${moduleId}"] .permission-checkbox`).prop('checked', checked);

            updateColumnCheckboxes();
            updateSelectAllModules();
        });

        // Helper functions
        function updateModuleRowCheckbox(moduleId) {
            const allChecked = $(`tr[data-module-id="${moduleId}"] .permission-checkbox:checked`).length ===
                $(`tr[data-module-id="${moduleId}"] .permission-checkbox`).length;
            const anyChecked = $(`tr[data-module-id="${moduleId}"] .permission-checkbox:checked`).length > 0;
            // For Profiles, we mark the row as checked if ANY permission is checked (meaning it has SOME access)
            $(`.module-select-row[data-module-id="${moduleId}"]`).prop('checked', anyChecked);
        }

        function updateModuleRowCheckboxes() {
            $('.module-select-row').each(function () {
                updateModuleRowCheckbox($(this).data('module-id'));
            });
        }

        function updateColumnCheckbox(permission) {
            const columnCheckboxes = $(`.permission-checkbox[data-permission="${permission}"]`);
            const allColumnChecked = columnCheckboxes.length > 0 &&
                columnCheckboxes.filter(':checked').length === columnCheckboxes.length;
            $(`.select-all-permission[data-permission="${permission}"]`).prop('checked', allColumnChecked);
        }

        function updateColumnCheckboxes() {
            ['view', 'create', 'edit', 'delete'].forEach(permission => {
                updateColumnCheckbox(permission);
            });
        }

        function updateSelectAllModules() {
            const rowCheckboxes = $('.module-select-row');
            const allModulesChecked = rowCheckboxes.length > 0 &&
                rowCheckboxes.filter(':checked').length === rowCheckboxes.length;
            $('#selectAllModules').prop('checked', allModulesChecked);
        }

        // Initialize states
        updateModuleRowCheckboxes();
        updateColumnCheckboxes();
        updateSelectAllModules();
    });

    // Global storage for field and tool privileges (Pre-initialized if edit mode)
    if (typeof fieldPrivilegesData === 'undefined') {
        fieldPrivilegesData = {};
    }
    if (typeof toolPrivilegesData === 'undefined') {
        toolPrivilegesData = {};
    }

    function openFieldPrivileges(moduleId, moduleName) {
        $('#modalModuleName').text(moduleName);
        $('#currentModuleId').val(moduleId);
        $('#fields-tab').tab('show');
        loadModuleFields(moduleId);
        loadModuleTools(moduleId);
        const modal = new bootstrap.Modal(document.getElementById('fieldPrivilegesModal'));
        modal.show();
    }

    function loadModuleFields(moduleId) {
        $('#fieldsTableBody').html('<tr><td colspan="4" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>');
        $.ajax({
            url: '{{ route("tenant.settings.users.roles.get-module-fields") }}',
            method: 'GET',
            data: { module_id: moduleId },
            success: function (response) {
                if (response.fields && response.fields.length > 0) {
                    let html = '';
                    response.fields.forEach(function (field) {
                        const fieldId = field.fieldid;
                        const currentPermission = fieldPrivilegesData[moduleId]?.[fieldId] ?? 2; // Default: Write
                        html += `
                            <tr>
                                <td><strong>${field.fieldlabel}</strong><br><small class="text-muted">${field.fieldname}</small></td>
                                <td class="text-center"><input class="form-check-input field-permission-radio" type="radio" name="field_${fieldId}" value="0" data-field-id="${fieldId}" data-module-id="${moduleId}" ${currentPermission == 0 ? 'checked' : ''}></td>
                                <td class="text-center"><input class="form-check-input field-permission-radio" type="radio" name="field_${fieldId}" value="1" data-field-id="${fieldId}" data-module-id="${moduleId}" ${currentPermission == 1 ? 'checked' : ''}></td>
                                <td class="text-center"><input class="form-check-input field-permission-radio" type="radio" name="field_${fieldId}" value="2" data-field-id="${fieldId}" data-module-id="${moduleId}" ${currentPermission == 2 ? 'checked' : ''}></td>
                            </tr>
                        `;
                    });
                    $('#fieldsTableBody').html(html);
                } else {
                    $('#fieldsTableBody').html('<tr><td colspan="4" class="text-center py-4">{{ __("tenant::users.no_fields_available") }}</td></tr>');
                }
            }
        });
    }

    function loadModuleTools(moduleId) {
        $('#toolsTableBody').html('<tr><td colspan="3" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>');
        $.ajax({
            url: '{{ route("tenant.settings.users.roles.get-module-tools") }}',
            method: 'GET',
            data: { module_id: moduleId },
            success: function (response) {
                if (response.tools && response.tools.length > 0) {
                    let html = '';
                    response.tools.forEach(function (tool) {
                        const toolId = tool.toolid;
                        const isEnabled = toolPrivilegesData[moduleId]?.[toolId] ?? true;
                        html += `
                            <tr>
                                <td><strong>${tool.toolname}</strong></td>
                                <td class="text-center"><input class="form-check-input tool-permission-checkbox" type="checkbox" data-tool-id="${toolId}" data-module-id="${moduleId}" ${isEnabled ? 'checked' : ''}></td>
                                <td><small class="text-muted">${tool.description || ''}</small></td>
                            </tr>
                        `;
                    });
                    $('#toolsTableBody').html(html);
                } else {
                    $('#toolsTableBody').html('<tr><td colspan="3" class="text-center py-4">{{ __("tenant::users.no_tools_available") }}</td></tr>');
                }
            }
        });
    }

    function saveFieldPrivileges() {
        const moduleId = $('#currentModuleId').val();
        if (!fieldPrivilegesData[moduleId]) fieldPrivilegesData[moduleId] = {};
        $('.field-permission-radio:checked').each(function () {
            fieldPrivilegesData[moduleId][$(this).data('field-id')] = $(this).val();
        });
        if (!toolPrivilegesData[moduleId]) toolPrivilegesData[moduleId] = {};
        $('.tool-permission-checkbox').each(function () {
            toolPrivilegesData[moduleId][$(this).data('tool-id')] = $(this).is(':checked');
        });
        $('#fieldPrivilegesInput').val(JSON.stringify(fieldPrivilegesData));
        $('#toolPrivilegesInput').val(JSON.stringify(toolPrivilegesData));
        bootstrap.Modal.getInstance(document.getElementById('fieldPrivilegesModal')).hide();
        showNotification("{{ __('tenant::users.privileges_saved_successfully') }}", 'success');
    }

    // Modal select all fields
    $(document).on('change', '.select-all-field-permission', function () {
        const val = $(this).val();
        $(`.field-permission-radio[value="${val}"]`).prop('checked', true);
    });

    $(document).on('change', '#selectAllTools', function () {
        $('.tool-permission-checkbox').prop('checked', $(this).prop('checked'));
    });

    function showNotification(message, type) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const icon = type === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle';
        const notification = $(`
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed top-0 end-0 m-3" role="alert" style="z-index: 9999; min-width: 300px;">
                <i class="bi ${icon} me-2"></i>${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
        $('body').append(notification);
        setTimeout(() => notification.alert('close'), 3000);
    }
</script>