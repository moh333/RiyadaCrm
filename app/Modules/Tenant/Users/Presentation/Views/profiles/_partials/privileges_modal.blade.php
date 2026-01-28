<div class="modal fade" id="fieldPrivilegesModal" tabindex="-1" aria-labelledby="fieldPrivilegesModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="fieldPrivilegesModalLabel">
                    <i class="bi bi-sliders me-2"></i>{{ __('tenant::users.field_tool_privileges') }}: <span
                        id="modalModuleName"></span>
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
                            <strong>{{ __('tenant::users.field_permissions') }}:</strong>
                            {{ __('tenant::users.field_permissions_control') }}
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 40%;">{{ __('tenant::users.field_name') }}</th>
                                        <th class="text-center" style="width: 20%;">
                                            <div class="form-check d-inline-block">
                                                <input class="form-check-input select-all-field-permission" type="radio"
                                                    name="select_all_fields" value="0" id="selectAllInvisible">
                                                <label class="form-check-label" for="selectAllInvisible">
                                                    <i class="bi bi-eye-slash text-danger"></i>
                                                    {{ __('tenant::users.invisible') }}
                                                </label>
                                            </div>
                                        </th>
                                        <th class="text-center" style="width: 20%;">
                                            <div class="form-check d-inline-block">
                                                <input class="form-check-input select-all-field-permission" type="radio"
                                                    name="select_all_fields" value="1" id="selectAllReadonly">
                                                <label class="form-check-label" for="selectAllReadonly">
                                                    <i class="bi bi-eye text-warning"></i>
                                                    {{ __('tenant::users.read_only') }}
                                                </label>
                                            </div>
                                        </th>
                                        <th class="text-center" style="width: 20%;">
                                            <div class="form-check d-inline-block">
                                                <input class="form-check-input select-all-field-permission" type="radio"
                                                    name="select_all_fields" value="2" id="selectAllWrite" checked>
                                                <label class="form-check-label" for="selectAllWrite">
                                                    <i class="bi bi-pencil text-success"></i>
                                                    {{ __('tenant::users.write') }}
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
                            <strong>{{ __('tenant::users.tool_permissions') }}:</strong>
                            {{ __('tenant::users.tool_permissions_control') }}
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