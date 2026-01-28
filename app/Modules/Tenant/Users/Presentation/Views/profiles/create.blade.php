@extends('tenant::layout')

@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold mb-0">
                <i class="bi bi-person-badge me-2"></i>{{ __('tenant::users.create_profile') ?? 'Create Profile' }}
            </h3>
            <a href="{{ route('tenant.settings.users.profiles.index') }}" class="btn btn-outline-secondary rounded-3">
                <i class="bi bi-arrow-left me-1"></i>{{ __('tenant::users.cancel') }}
            </a>
        </div>

        @if($errors->any())
            <div class="alert alert-danger rounded-3 shadow-sm mb-4">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('tenant.settings.users.profiles.store') }}" method="POST" id="profileForm">
            @csrf
            
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-info-circle me-2"></i>{{ __('tenant::users.basic_info') }}</h5>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">{{ __('tenant::users.profile_name') ?? 'Profile Name' }} <span class="text-danger">*</span></label>
                        <input type="text" name="profilename" class="form-control rounded-3" value="{{ old('profilename') }}" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold">{{ __('tenant::users.description') ?? 'Description' }}</label>
                        <textarea name="description" class="form-control rounded-3" rows="2">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Privileges -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-shield-lock me-2"></i>{{ __('tenant::users.privileges_configuration') }}</h5>
                </div>
                <div class="card-body p-4">
                    <div class="alert alert-info border-0 rounded-3 mb-4">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>{{ __('tenant::users.quick_start') }}</strong> {{ __('tenant::users.quick_start_description') }}
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">{{ __('tenant::users.copy_privileges_from') }}</label>
                        <select name="copy_from_profile" class="form-select rounded-3" id="copyFromProfile">
                            <option value="">-- {{ __('tenant::users.create_from_scratch') }} --</option>
                            @if(isset($profiles))
                                @foreach($profiles as $p)
                                    @if(!isset($p->directly_related_to_role) || $p->directly_related_to_role != 1)
                                        <option value="{{ $p->profileid }}" {{ old('copy_from_profile') == $p->profileid ? 'selected' : '' }}>
                                            {{ $p->profilename }}
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
                                        <input type="checkbox" class="form-check-input" id="selectAllModules" title="Select all modules">
                                    </th>
                                    <th style="width: 30%;">{{ __('tenant::users.module') }}</th>
                                    <th class="text-center" style="width: 13%;">
                                        <div class="d-flex flex-column align-items-center">
                                            <input type="checkbox" class="form-check-input mb-1 select-all-permission" data-permission="view" title="Select all View">
                                            <span><i class="bi bi-eye me-1"></i>{{ __('tenant::users.view') }}</span>
                                        </div>
                                    </th>
                                    <th class="text-center" style="width: 13%;">
                                        <div class="d-flex flex-column align-items-center">
                                            <input type="checkbox" class="form-check-input mb-1 select-all-permission" data-permission="create" title="Select all Create">
                                            <span><i class="bi bi-plus-circle me-1"></i>{{ __('tenant::users.create') }}</span>
                                        </div>
                                    </th>
                                    <th class="text-center" style="width: 13%;">
                                        <div class="d-flex flex-column align-items-center">
                                            <input type="checkbox" class="form-check-input mb-1 select-all-permission" data-permission="edit" title="Select all Edit">
                                            <span><i class="bi bi-pencil me-1"></i>{{ __('tenant::users.edit') }}</span>
                                        </div>
                                    </th>
                                    <th class="text-center" style="width: 13%;">
                                        <div class="d-flex flex-column align-items-center">
                                            <input type="checkbox" class="form-check-input mb-1 select-all-permission" data-permission="delete" title="Select all Delete">
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
                                                <input type="checkbox" class="form-check-input module-select-row" data-module-id="{{ $module->tabid }}">
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-folder me-2 text-primary"></i>
                                                    <strong>{{ $module->name }}</strong>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" class="form-check-input permission-checkbox" name="permissions[{{ $module->tabid }}][view]" data-permission="view" data-module-id="{{ $module->tabid }}" value="1">
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" class="form-check-input permission-checkbox" name="permissions[{{ $module->tabid }}][create]" data-permission="create" data-module-id="{{ $module->tabid }}" value="1">
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" class="form-check-input permission-checkbox" name="permissions[{{ $module->tabid }}][edit]" data-permission="edit" data-module-id="{{ $module->tabid }}" value="1">
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" class="form-check-input permission-checkbox" name="permissions[{{ $module->tabid }}][delete]" data-permission="delete" data-module-id="{{ $module->tabid }}" value="1">
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" onclick="openFieldPrivileges('{{ $module->tabid }}', '{{ $module->name }}')">
                                                    <i class="bi bi-sliders"></i> {{ __('tenant::users.configure') }}
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <!-- JSON storage for field and tool privileges -->
                    <input type="hidden" name="field_privileges" id="fieldPrivilegesInput" value="{}">
                    <input type="hidden" name="tool_privileges" id="toolPrivilegesInput" value="{}">
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mb-4">
                <a href="{{ route('tenant.settings.users.profiles.index') }}" class="btn btn-outline-secondary rounded-3 px-4">
                    <i class="bi bi-x-lg me-2"></i>{{ __('tenant::users.cancel') }}
                </a>
                <button type="submit" class="btn btn-primary rounded-3 px-4 shadow-sm">
                    <i class="bi bi-save me-2"></i>{{ __('tenant::users.create') }}
                </button>
            </div>
        </form>
    </div>

    <!-- Modal and Footer UI components here -->
    @include('tenant::profiles._partials.privileges_modal')

@endsection

@push('scripts')
    @include('tenant::profiles._partials.privileges_scripts')
@endpush

@push('styles')
    <style>
        .contentsBackground { background-color: #f8f9fa; border-radius: 8px; }
        .padding20px { padding: 20px; }
        .boxSizingBorderBox { box-sizing: border-box; }
        .form-check-input:checked { background-color: #6366f1; border-color: #6366f1; }
    </style>
@endpush