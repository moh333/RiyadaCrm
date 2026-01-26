<li class="dd-item" data-id="{{ $role->roleid }}">
    <div class="dd-handle">
        <div class="d-flex align-items-center">
            <div class="drag-icon me-3 text-muted">
                <i class="bi bi-grid-3x3-gap-fill opacity-25"></i>
            </div>
            <div class="role-icon me-3">
                <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-2 d-flex align-items-center justify-content-center"
                    style="width: 40px; height: 40px;">
                    <i class="bi bi-person-badge"></i>
                </div>
            </div>
            <div>
                <span class="role-name h6 mb-0">{{ $role->rolename }}</span>
                <span class="role-badge ms-2">#{{ $role->roleid }}</span>
            </div>
        </div>
        <div class="role-actions d-flex gap-2" onclick="event.stopPropagation();">
            <a href="{{ route('tenant.settings.users.roles.edit', $role->roleid) }}"
                class="btn btn-sm btn-light border-0 rounded-circle text-primary shadow-sm"
                title="{{ __('tenant::users.update') }}">
                <i class="bi bi-pencil-fill"></i>
            </a>
            @if($role->roleid !== 'H1')
                <button type="button" class="btn btn-sm btn-light border-0 rounded-circle text-danger shadow-sm delete-role"
                    data-id="{{ $role->roleid }}" data-name="{{ $role->rolename }}"
                    title="{{ __('tenant::users.delete') }}">
                    <i class="bi bi-trash-fill"></i>
                </button>
            @endif
        </div>
    </div>
    @if(count($role->children) > 0)
        <ol class="dd-list">
            @foreach($role->children as $child)
                @include('tenant::roles._tree_item', ['role' => $child])
            @endforeach
        </ol>
    @endif
</li>