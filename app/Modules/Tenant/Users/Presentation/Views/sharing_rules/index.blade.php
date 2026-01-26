@extends('tenant::layout')

@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold mb-0">
                <i class="bi bi-share me-2"></i>{{ __('tenant::users.sharing_rules') }}
            </h3>
            <button type="button" class="btn btn-primary rounded-3 shadow-sm open-create-modal">
                <i class="bi bi-plus-lg me-1"></i>{{ __('tenant::users.add_custom_rule') }}
            </button>
        </div>

        @if(session('success'))
            <div class="alert alert-success rounded-3 shadow-sm mb-4 border-0 border-start border-success border-4">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            </div>
        @endif

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">{{ __('tenant::users.organization_wide_defaults') }}</h5>
            </div>
            <div class="card-body p-0">
                <form action="{{ route('tenant.settings.users.sharing-rules.update-defaults') }}" method="POST">
                    @csrf
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th width="60" class="text-center">#</th>
                                    <th class="ps-4 py-3">{{ __('tenant::users.module') }}</th>
                                    <th class="py-3">{{ __('tenant::users.default_permission') }}</th>
                                    <th class="text-end pe-4 py-3">{{ __('tenant::users.advanced_rules') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($defaults as $def)
                                    <tr class="module-row">
                                        <td class="text-center">
                                            @if(isset($customRules[$def->tabid]))
                                                <button type="button"
                                                    class="btn btn-sm btn-light rounded-circle shadow-sm toggle-advanced collapsed"
                                                    data-bs-toggle="collapse" data-bs-target="#advanced-{{ $def->tabid }}">
                                                    <i class="bi bi-chevron-down"></i>
                                                </button>
                                            @endif
                                        </td>
                                        <td class="ps-4 fw-bold">
                                            {{ $def->tablabel }}
                                            <small class="text-muted d-block font-monospace"
                                                style="font-size: 0.7rem;">{{ $def->module_name }}</small>
                                        </td>
                                        <td>
                                            <select name="rules[{{ $def->ruleid }}]"
                                                class="form-select form-select-sm rounded-3 w-auto border-0 bg-light fw-medium">
                                                <option value="0" {{ $def->permission == 0 ? 'selected' : '' }}>
                                                    {{ __('tenant::users.public_read_only') }}</option>
                                                <option value="1" {{ $def->permission == 1 ? 'selected' : '' }}>
                                                    {{ __('tenant::users.public_read_edit') }}</option>
                                                <option value="2" {{ $def->permission == 2 ? 'selected' : '' }}>
                                                    {{ __('tenant::users.public_read_edit_delete') }}</option>
                                                <option value="3" {{ $def->permission == 3 ? 'selected' : '' }}>
                                                    {{ __('tenant::users.private') }}</option>
                                            </select>
                                        </td>
                                        <td class="text-end pe-4">
                                            @if(isset($customRules[$def->tabid]))
                                                <span
                                                    class="badge bg-indigo-subtle text-indigo rounded-pill px-3">{{ count($customRules[$def->tabid]) }}
                                                    {{ __('tenant::users.rules_count') }}</span>
                                            @else
                                                <span class="text-muted small">{{ __('tenant::users.none') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @if(isset($customRules[$def->tabid]))
                                        <tr>
                                            <td colspan="4" class="p-0 border-0">
                                                <div class="collapse bg-light-subtle shadow-inner" id="advanced-{{ $def->tabid }}">
                                                    <div class="p-4">
                                                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                                                            <table class="table table-sm table-hover mb-0 align-middle">
                                                                <thead class="table-dark">
                                                                    <tr class="small text-uppercase">
                                                                        <th class="ps-4 py-2">{{ __('tenant::users.shared_from') }}
                                                                        </th>
                                                                        <th class="py-2">{{ __('tenant::users.shared_to') }}</th>
                                                                        <th class="py-2">{{ __('tenant::users.permission') }}</th>
                                                                        <th width="100" class="text-end pe-4 py-2">
                                                                            {{ __('tenant::users.actions') }}</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach($customRules[$def->tabid] as $rule)
                                                                                                                        <tr class="custom-rule-row">
                                                                                                                            <td class="ps-4">
                                                                                                                                <span class="badge bg-indigo rounded-pill me-1 small"
                                                                                                                                    style="font-size: 0.65rem;">{{ __('tenant::users.' . strtolower($rule->from_type)) }}</span>
                                                                                                                                <span class="fw-semibold">{{ $rule->from }}</span>
                                                                                                                            </td>
                                                                                                                            <td>
                                                                                                                                <span class="badge bg-teal rounded-pill me-1 small"
                                                                                                                                    style="font-size: 0.65rem;">{{ __('tenant::users.' . strtolower($rule->to_type)) }}</span>
                                                                                                                                <span class="fw-semibold">{{ $rule->to }}</span>
                                                                                                                            </td>
                                                                                                                            <td>
                                                                                                                                @php
                                                                                                                                    $permsMap = [
                                                                                                                                        1 => __('tenant::users.read_only'),
                                                                                                                                        2 => __('tenant::users.read_write')
                                                                                                                                    ];
                                                                                                                                @endphp
                                                                         <span
                                                                                                                                    class="text-muted fw-medium">{{ $permsMap[$rule->permission] ?? 'Custom (' . $rule->permission . ')' }}</span>
                                                                                                                            </td>
                                                                                                                            <td class="text-end pe-4">
                                                                                                                                <div
                                                                                                                                    class="btn-group shadow-sm rounded-3 overflow-hidden">
                                                                                                                                    <button type="button"
                                                                                                                                        class="btn btn-sm btn-white edit-custom-rule"
                                                                                                                                        data-rule='@json($rule)'>
                                                                                                                                        <i class="bi bi-pencil text-primary"></i>
                                                                                                                                    </button>
                                                                                                                                    <button type="button"
                                                                                                                                        class="btn btn-sm btn-white delete-custom-rule"
                                                                                                                                        data-id="{{ $rule->shareid }}"
                                                                                                                                        data-module="{{ $def->tablabel }}">
                                                                                                                                        <i class="bi bi-trash text-danger"></i>
                                                                                                                                    </button>
                                                                                                                                </div>
                                                                                                                            </td>
                                                                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="p-3 bg-white border-top d-flex justify-content-end">
                        <button type="submit" class="btn btn-success btn-lg rounded-pill px-5 shadow-sm fw-bold">
                            <i class="bi bi-save2 me-2"></i>{{ __('tenant::users.save_settings') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Custom Rule Modal -->
    <div class="modal fade" id="customRuleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-bottom-0 p-4 pb-0">
                    <h5 class="modal-title fw-bold" id="modalTitle">
                        <i class="bi bi-share-fill me-2 text-primary"></i>{{ __('tenant::users.add_custom_rule') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('tenant.settings.users.sharing-rules.custom.store') }}" method="POST"
                    id="customRuleForm">
                    @csrf
                    <div id="methodContainer"></div>
                    <div class="modal-body p-4">
                        <div class="row g-4">
                            <div class="col-12">
                                <label
                                    class="form-label fw-bold small text-uppercase text-muted">{{ __('tenant::users.target_module') }}</label>
                                <select name="tabid" id="modalTabId"
                                    class="form-select rounded-3 py-2 border-0 bg-light fw-bold">
                                    @foreach($defaults as $def)
                                        <option value="{{ $def->tabid }}">{{ $def->tablabel }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 border-end">
                                <label
                                    class="form-label fw-bold small text-uppercase text-muted">{{ __('tenant::users.shared_from') }}</label>
                                <div class="bg-light p-3 rounded-4">
                                    <select name="from_type" id="from_type" class="form-select mb-3 border-0 bg-white">
                                        <option value="Groups">{{ __('tenant::users.groups') }}</option>
                                        <option value="Roles">{{ __('tenant::users.roles') }}</option>
                                        <option value="RoleAndSubordinates">{{ __('tenant::users.roles_subordinates') }}
                                        </option>
                                    </select>
                                    <select name="from_id" id="from_id" class="form-select border-0 bg-white select2">
                                        <!-- Populated by JS -->
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label
                                    class="form-label fw-bold small text-uppercase text-muted">{{ __('tenant::users.shared_to') }}</label>
                                <div class="bg-light p-3 rounded-4">
                                    <select name="to_type" id="to_type" class="form-select mb-3 border-0 bg-white">
                                        <option value="Groups">{{ __('tenant::users.groups') }}</option>
                                        <option value="Roles">{{ __('tenant::users.roles') }}</option>
                                        <option value="RoleAndSubordinates">{{ __('tenant::users.roles_subordinates') }}
                                        </option>
                                    </select>
                                    <select name="to_id" id="to_id" class="form-select border-0 bg-white select2">
                                        <!-- Populated by JS -->
                                    </select>
                                </div>
                            </div>

                            <div class="col-12 mt-4">
                                <label
                                    class="form-label fw-bold small text-uppercase text-muted">{{ __('tenant::users.permission') }}</label>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <input type="radio" class="btn-check" name="permission" id="perm_read" value="0"
                                            checked>
                                        <label
                                            class="btn btn-outline-primary w-100 py-3 rounded-4 d-flex flex-column align-items-center"
                                            for="perm_read">
                                            <i class="bi bi-eye mb-1 h4"></i>
                                            <span class="fw-bold">{{ __('tenant::users.read_only') }}</span>
                                        </label>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="radio" class="btn-check" name="permission" id="perm_rw" value="1">
                                        <label
                                            class="btn btn-outline-primary w-100 py-3 rounded-4 d-flex flex-column align-items-center"
                                            for="perm_rw">
                                            <i class="bi bi-pencil-square mb-1 h4"></i>
                                            <span class="fw-bold">{{ __('tenant::users.read_write') }}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-info border-0 rounded-4 mt-4 mb-0 small py-2 d-flex align-items-center">
                            <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                            <span>{{ __('tenant::users.custom_rule_info') }}</span>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 p-4 pt-0">
                        <button type="button" class="btn btn-link link-secondary fw-bold text-decoration-none"
                            data-bs-dismiss="modal">{{ __('tenant::users.cancel') }}</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm fw-bold" id="submitBtn">
                            <i class="bi bi-plus-lg me-2"></i>{{ __('tenant::users.create') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <form id="deleteForm" method="POST" style="display:none;">
        @csrf
        @method('DELETE')
    </form>

@endsection

@push('scripts')
    <script>
        const entityData = {
            Groups: @json($groups->map(fn($g) => ['id' => $g->groupid, 'name' => $g->groupname])),
            Roles: @json($roles->map(fn($r) => ['id' => $r->roleid, 'name' => $r->rolename])),
            RoleAndSubordinates: @json($roles->map(fn($r) => ['id' => $r->roleid, 'name' => $r->rolename]))
        };

        const strings = {
            addRule: "{{ __('tenant::users.add_custom_rule') }}",
            editRule: "{{ __('tenant::users.edit_custom_rule') }}",
            create: "{{ __('tenant::users.create') }}",
            update: "{{ __('tenant::users.update') }}",
            deleteConfirm: "{{ __('tenant::users.delete_rule_confirm', ['module' => ':module']) }}",
            areYouSure: "{{ __('tenant::users.are_you_sure') }}"
        };

        $(document).ready(function () {
            const modal = new bootstrap.Modal(document.getElementById('customRuleModal'));
            const form = $('#customRuleForm');
            const modalTitle = $('#modalTitle');
            const submitBtn = $('#submitBtn');
            const methodContainer = $('#methodContainer');

            function updateOptions(typeSelectId, valSelectId, selectedId = null) {
                const type = $('#' + typeSelectId).val();
                const select = $('#' + valSelectId);
                const options = entityData[type];

                select.empty();
                options.forEach(opt => {
                    const option = new Option(opt.name, opt.id);
                    if (selectedId && opt.id == selectedId) option.selected = true;
                    select.append(option);
                });
                select.trigger('change');
            }

            $('#from_type').on('change', () => updateOptions('from_type', 'from_id'));
            $('#to_type').on('change', () => updateOptions('to_type', 'to_id'));

            // Handle Create
            $('.open-create-modal').on('click', function () {
                modalTitle.html(`<i class="bi bi-plus-circle-fill me-2 text-primary"></i>${strings.addRule}`);
                submitBtn.html(`<i class="bi bi-plus-lg me-2"></i>${strings.create}`);
                form.attr('action', "{{ route('tenant.settings.users.sharing-rules.custom.store') }}");
                methodContainer.empty();

                // Reset fields
                $('#modalTabId').val($('#modalTabId option:first').val());
                $('#from_type').val('Groups').trigger('change');
                $('#to_type').val('Groups').trigger('change');
                $('#perm_read').prop('checked', true);

                modal.show();
            });

            // Handle Edit
            $('.edit-custom-rule').on('click', function () {
                const rule = $(this).data('rule');

                modalTitle.html(`<i class="bi bi-pencil-fill me-2 text-primary"></i>${strings.editRule}`);
                submitBtn.html(`<i class="bi bi-save2 me-2"></i>${strings.update}`);

                let updateUrl = "{{ route('tenant.settings.users.sharing-rules.custom.update', ':id') }}";
                form.attr('action', updateUrl.replace(':id', rule.shareid));
                methodContainer.html('<input type="hidden" name="_method" value="PUT">');

                // Fill fields
                $('#modalTabId').val(rule.tabid);
                $('#from_type').val(rule.from_type);
                updateOptions('from_type', 'from_id', rule.from_id);
                $('#to_type').val(rule.to_type);
                updateOptions('to_type', 'to_id', rule.to_id);

                // Vtiger permission 1=Read, 2=RW -> map to our 0/1
                if (rule.permission == 2) $('#perm_rw').prop('checked', true);
                else $('#perm_read').prop('checked', true);

                modal.show();
            });

            // Handle Delete
            $('.delete-custom-rule').on('click', function () {
                const id = $(this).data('id');
                const module = $(this).data('module');

                if (confirm(strings.deleteConfirm.replace(':module', module))) {
                    let deleteUrl = "{{ route('tenant.settings.users.sharing-rules.custom.destroy', ':id') }}";
                    const deleteForm = $('#deleteForm');
                    deleteForm.attr('action', deleteUrl.replace(':id', id));
                    deleteForm.submit();
                }
            });

            // Init options on first load
            updateOptions('from_type', 'from_id');
            updateOptions('to_type', 'to_id');

            // Animation for chevron
            $('.toggle-advanced').on('click', function () {
                const icon = $(this).find('i');
                if ($(this).hasClass('collapsed')) {
                    icon.css('transform', 'rotate(180deg)');
                } else {
                    icon.css('transform', 'rotate(0deg)');
                }
            });
        });
    </script>

    <style>
        .bg-indigo {
            background-color: #6366f1;
            color: white;
        }

        .bg-indigo-subtle {
            background-color: #eef2ff;
            color: #4338ca;
        }

        .text-indigo {
            color: #6366f1;
        }

        .bg-teal {
            background-color: #14b8a6;
            color: white;
        }

        .bg-light-subtle {
            background-color: #f8fafc;
        }

        .shadow-inner {
            box-shadow: inset 0 3px 15px rgba(0, 0, 0, 0.03);
        }

        .module-row {
            transition: all 0.2s;
        }

        .module-row:hover {
            background-color: rgba(99, 102, 241, 0.03) !important;
        }

        .custom-rule-row {
            border-left: 4px solid transparent;
            transition: all 0.2s;
        }

        .custom-rule-row:hover {
            border-left-color: #6366f1;
            background-color: white !important;
        }

        .btn-white {
            background-color: white;
            border: 1px solid #e2e8f0;
        }

        .btn-white:hover {
            background-color: #f8fafc;
        }

        .toggle-advanced i {
            transition: transform 0.3s;
        }

        /* Custom radio styling */
        .btn-check:checked+.btn-outline-primary {
            background-color: #eef2ff !important;
            border-color: #6366f1 !important;
            color: #4338ca !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .btn-outline-primary {
            border: 2px solid #e2e8f0;
            color: #64748b;
        }

        .btn-outline-primary:hover {
            background-color: #f8fafc;
            border-color: #cbd5e1;
            color: #475569;
        }
    </style>
@endpush