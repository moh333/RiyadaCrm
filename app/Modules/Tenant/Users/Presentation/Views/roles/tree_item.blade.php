@php
    $hasChildren = count($role->children) > 0;
    // Fallback depth calculation based on parentrole string
    $currentDepth = isset($role->depth) ? $role->depth : (count(explode('::', $role->parentrole)) - 1);
@endphp

<div class="role-wrapper" data-id="{{ $role->roleid }}">
    <!-- Main Role Row -->
    <div class="role-row level-row-{{ $currentDepth }} d-flex align-items-center py-2 px-4 border-bottom">
        <!-- Toggle / Spacing -->
        <div style="width: 40px;" class="d-flex align-items-center justify-content-center">
            @if($hasChildren)
                <button class="collapse-toggle collapsed d-flex align-items-center justify-content-center" type="button"
                    data-bs-toggle="collapse" data-bs-target="#children-{{ $role->roleid }}" aria-expanded="false"
                    aria-controls="children-{{ $role->roleid }}">
                    <i class="bi bi-chevron-right"></i>
                </button>
            @else
                <div style="width: 32px; height: 32px; visibility: hidden;"></div>
            @endif
        </div>

        <!-- Role Core Info -->
        <div class="flex-grow-1 ps-4 d-flex align-items-center">
            <div class="role-icon-box me-3">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
            <div>
                <span class="role-name fw-bold text-dark d-block mb-0">{{ $role->rolename }}</span>
                <span class="depth-indicator">{{ __('tenant::users.level') }} {{ $currentDepth }}</span>
            </div>

        </div>

        <!-- ID & Depth Meta -->
        <div style="width: 150px;" class="text-center d-flex flex-column align-items-center">
            <span class="role-id-badge mb-1">{{ __('tenant::users.id') ?? 'ID' }}: {{ $role->roleid }}</span>
        </div>

        <!-- Actions -->
        <div style="width: 200px;" class="text-end pe-4">
            <div class="btn-group shadow-sm rounded-3 overflow-hidden">
                <a href="{{ route('tenant.settings.users.roles.create', ['parent_role_id' => $role->roleid]) }}"
                    class="btn btn-sm btn-white py-2 px-3 transition-all"
                    title="{{ __('tenant::users.add_sub_role') }}">
                    <i class="bi bi-plus-circle-fill text-success"></i>
                </a>
                <a href="{{ route('tenant.settings.users.roles.edit', $role->roleid) }}"
                    class="btn btn-sm btn-white py-2 px-3 transition-all" title="{{ __('tenant::users.edit') }}">
                    <i class="bi bi-pencil-fill text-primary"></i>
                </a>
                @if($role->roleid !== 'H1')
                    <button type="button" class="btn btn-sm btn-white py-2 px-3 transition-all delete-role"
                        data-id="{{ $role->roleid }}" data-name="{{ $role->rolename }}"
                        title="{{ __('tenant::users.delete') }}">
                        <i class="bi bi-trash-fill text-danger"></i>
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Children Recurse -->
    @if($hasChildren)
        <div class="collapse role-children-container" id="children-{{ $role->roleid }}">
            @foreach($role->children as $child)
                @include('tenant::roles.tree_item', ['role' => $child])
            @endforeach
        </div>
    @endif
</div>