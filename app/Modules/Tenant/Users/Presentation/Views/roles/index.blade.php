@extends('tenant::layout')

@section('content')
<div class="container-fluid p-0">
    <div class="row mb-5 align-items-center">
        <div class="col-auto">
            <div class="bg-primary bg-opacity-10 p-3 rounded-4">
                <i class="bi bi-diagram-3-fill fs-2 text-primary"></i>
            </div>
        </div>
        <div class="col">
            <h3 class="fw-bold mb-0">{{ __('tenant::users.roles') }}</h3>
            <p class="text-muted mb-0">{{ __('tenant::users.drag_and_drop_info') }}</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('tenant.settings.users.roles.create') }}" class="btn btn-primary btn-lg rounded-pill px-4 shadow-sm">
                <i class="bi bi-plus-lg me-2"></i>{{ __('tenant::users.create_role') }}
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success rounded-4 shadow-sm mb-4 border-0 border-start border-success border-4 animate__animated animate__fadeIn">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill me-3 fs-4 text-success"></i>
                <div class="fw-medium">{{ session('success') }}</div>
            </div>
        </div>
    @endif

    <div class="role-tree-container animate__animated animate__fadeInUp">
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="card-header bg-white py-4 border-bottom d-flex justify-content-between align-items-center px-4">
                <h5 class="mb-0 fw-bold">{{ __('tenant::users.role_hierarchy') }}</h5>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" id="expand-all">
                        <i class="bi bi-arrows-expand me-1"></i>Expand
                    </button>
                    <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" id="collapse-all">
                        <i class="bi bi-arrows-collapse me-1"></i>Collapse
                    </button>
                </div>
            </div>
            <div class="card-body p-4 p-md-5">
                <div class="dd-tree" id="roles-tree">
                    <ol class="dd-list">
                        @foreach($tree as $role)
                            @include('tenant::roles._tree_item', ['role' => $role])
                        @endforeach
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="saving-indicator" class="saving-toast position-fixed bottom-0 end-0 m-4 shadow-lg rounded-4 bg-dark text-white p-3 px-4 d-none">
    <div class="d-flex align-items-center">
        <div class="spinner-border spinner-border-sm me-3 text-primary" role="status"></div>
        <span class="fw-medium">Saving hierarchy changes...</span>
    </div>
</div>

{{-- Deletion Form --}}
<form id="delete-role-form" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>

@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/nestable2/1.6.0/jquery.nestable.min.css">
<style>
    :root {
        --role-item-height: 60px;
        --tree-indent: 40px;
        --primary-indigo: #6366f1;
        --bg-light: #f8fafc;
    }

    .role-tree-container {
        max-width: 1000px;
        margin: 0;
    }

    .dd-tree { max-width: 100%; }
    .dd-list { list-style: none; margin: 0; padding: 0; }
    .dd-list .dd-list { padding-left: var(--tree-indent); position: relative; }
    
    /* Visual Tree Lines */
    .dd-list .dd-list::before {
        content: '';
        position: absolute;
        left: 20px;
        top: 0;
        bottom: 30px;
        width: 2px;
        background: #e2e8f0;
    }

    .dd-item { display: block; margin: 0; padding: 0; min-height: 20px; position: relative; }
    
    .dd-handle {
        display: flex;
        align-items: center;
        justify-content: space-between;
        height: var(--role-item-height);
        margin: 10px 0;
        padding: 0 20px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        color: #1e293b;
        font-weight: 600;
        cursor: grab;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }

    .dd-handle:hover {
        border-color: var(--primary-indigo);
        background: #fff;
        box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05);
        transform: translateY(-2px);
    }

    /* Active dragging state */
    .dd-dragel > .dd-item > .dd-handle {
        background: var(--primary-indigo);
        color: white !important;
        border-color: var(--primary-indigo);
        box-shadow: 0 20px 25px -5px rgba(99, 102, 241, 0.2);
    }
    
    .dd-dragel > .dd-item > .dd-handle * { color: white !important; }

    .dd-item > button {
        width: 30px;
        height: 30px;
        margin: 15px 5px 0 0;
        background: #f1f5f9;
        border-radius: 8px;
        color: #64748b;
        transition: all 0.2s;
    }
    
    .dd-item > button:hover { background: #e2e8f0; color: #0f172a; }

    .dd-placeholder {
        margin: 10px 0;
        min-height: var(--role-item-height);
        background: rgba(99, 102, 241, 0.05);
        border: 2px dashed var(--primary-indigo);
        border-radius: 16px;
    }

    .role-badge {
        font-size: 0.75rem;
        padding: 4px 10px;
        border-radius: 20px;
        background: #f1f5f9;
        color: #64748b;
        font-weight: 500;
    }

    .saving-toast {
        z-index: 1060;
        min-width: 250px;
        border: 1px solid rgba(255,255,255,0.1);
    }

    /* Custom line for child nodes */
    .child-node-line {
        position: absolute;
        left: -20px;
        top: 30px;
        width: 20px;
        height: 2px;
        background: #e2e8f0;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/nestable2/1.6.0/jquery.nestable.min.js"></script>
<script>
    $(document).ready(function() {
        const rolesTree = $('#roles-tree');
        const indicator = $('#saving-indicator');

        rolesTree.nestable({
            maxDepth: 10,
            callback: function(l, e) {
                const roleId = $(e).data('id');
                const parentId = $(e).parent().closest('.dd-item').data('id') || null;

                indicator.removeClass('d-none').addClass('animate__animated animate__fadeInUp');

                $.ajax({
                    url: "{{ route('tenant.settings.users.roles.reorder') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        roleid: roleId,
                        new_parent_id: parentId
                    },
                    success: function(response) {
                        setTimeout(() => {
                            indicator.addClass('animate__fadeOutDown').one('animationend', function() {
                                $(this).removeClass('animate__fadeOutDown animate__animated').addClass('d-none');
                            });
                        }, 500);
                    },
                    error: function() {
                        alert('Error updating role hierarchy. The page will refresh.');
                        window.location.reload();
                    }
                });
            }
        });

        $('#expand-all').on('click', () => rolesTree.nestable('expandAll'));
        $('#collapse-all').on('click', () => rolesTree.nestable('collapseAll'));

        $(document).on('click', '.delete-role', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            if(confirm(`{{ __('tenant::users.are_you_sure') }} - ${name}?`)) {
                const form = $('#delete-role-form');
                let url = "{{ route('tenant.settings.users.roles.destroy', ':id') }}";
                form.attr('action', url.replace(':id', id));
                form.submit();
            }
        });
    });
</script>
@endpush