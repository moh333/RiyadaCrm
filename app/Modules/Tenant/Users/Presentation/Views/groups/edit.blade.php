@extends('tenant::layout')

@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold mb-0">
                <i class="bi bi-pencil me-2"></i>{{ __('tenant::users.update_group') ?? 'Edit Group' }}
            </h3>
            <a href="{{ route('tenant.settings.users.groups.index') }}" class="btn btn-outline-secondary rounded-3">
                <i class="bi bi-arrow-left me-1"></i>{{ __('tenant::users.cancel') }}
            </a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger rounded-3 shadow-sm mb-4">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('tenant.settings.users.groups.update', $group->groupid) }}" method="POST" id="groupForm">
            @csrf
            @method('PUT')
            <div class="row g-4">
                <div class="col-md-5">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h5 class="mb-0 fw-bold">Basic Information</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ __('tenant::users.group_name') ?? 'Group Name' }} <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="groupname" class="form-control rounded-3"
                                    value="{{ old('groupname', $group->groupname) }}" required>
                            </div>
                            <div class="mb-4">
                                <label
                                    class="form-label fw-bold">{{ __('tenant::users.description') ?? 'Description' }}</label>
                                <textarea name="description" class="form-control rounded-3"
                                    rows="4">{{ old('description', $group->description) }}</textarea>
                            </div>

                            <hr>

                            <div class="form-check form-switch p-3 bg-light rounded-3">
                                <input class="form-check-input ms-0 me-2" type="checkbox" name="allow_ticket_assign"
                                    id="allowTicketAssign" value="1" {{ old('allow_ticket_assign', $group->allow_ticket_assign) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="allowTicketAssign">
                                    {{ __('tenant::users.allow_ticket_auto_assign') ?? 'Allow Ticket Auto Assign' }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-7">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div
                            class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold">{{ __('tenant::users.group_members') ?? 'Group Members' }} <span
                                    class="text-danger">*</span></h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-2 mb-3 align-items-end">
                                <div class="col-sm-4">
                                    <label
                                        class="form-label small fw-bold">{{ __('tenant::users.member_type') ?? 'Member Type' }}</label>
                                    <select id="memberType" class="form-select rounded-3">
                                        <option value="Users">{{ __('tenant::users.users') }}</option>
                                        <option value="Groups">{{ __('tenant::users.groups') }}</option>
                                        <option value="Roles">{{ __('tenant::users.roles') }}</option>
                                        <option value="RoleAndSubordinates">{{ __('tenant::users.roles_subordinates') }}
                                        </option>
                                    </select>
                                </div>
                                <div class="col-sm-6">
                                    <label
                                        class="form-label small fw-bold">{{ __('tenant::users.select_members') ?? 'Select Member' }}</label>
                                    <select id="memberValue" class="form-select rounded-3 select2">
                                        <!-- Options will be populated by JS -->
                                    </select>
                                </div>
                                <div class="col-sm-2">
                                    <button type="button" id="addMember" class="btn btn-primary w-100 rounded-3">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="table-responsive" style="max-height: 300px;">
                                <table class="table table-sm table-hover align-middle border" id="membersTable">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Type</th>
                                            <th>Name</th>
                                            <th width="50"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="membersList">
                                        @php 
                                                                                        $members = old('members', array_map(function ($m) use ($users, $groups, $roles) {
                                                $name = 'Unknown';
                                                if ($m['type'] === 'Users') {
                                                    $u = $users->firstWhere('id', $m['id']);
                                                    $name = $u ? (($u->first_name . ' ' . $u->last_name) ?: $u->user_name) : 'User #' . $m['id'];
                                                } elseif ($m['type'] === 'Groups') {
                                                    $g = $groups->firstWhere('id', $m['id']);
                                                    $name = $g ? $g->name : 'Group #' . $m['id'];
                                                } else {
                                                    $r = $roles->firstWhere('id', $m['id']);
                                                    $name = $r ? $r->name : 'Role #' . $m['id'];
                                                }
                                                return ['id' => $m['id'], 'type' => $m['type'], 'name' => $name];
                                            }, $selectedMembers));
                                        @endphp
                                        @foreach($members as $index => $member)
                                            <tr data-id="{{ $member['id'] }}" data-type="{{ $member['type'] }}">
                                                <td><span class="badge bg-info">{{ $member['type'] }}</span></td>
                                                <td>{{ $member['name'] }}</td>
                                                    <td>
                                                    <input type="hidden" name="members[{{ $index }}][id]" value="{{ $member['id'] }}">
                                                    <input type="hidden" name="members[{{ $index }}][type]" value="{{ $member['type'] }}">
                                                    <input type="hidden" name="members[{{ $index }}][name]" value="{{ $member['name'] }}">
                                                    <button type="button" class="btn btn-sm text-danger remove-member"><i class="bi bi-x-circle"></i></button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4 gap-3">
                    <a href="{{ route('tenant.settings.users.groups.index') }}" class="btn btn-light btn-lg rounded-3 px-4">
                        {{ __('tenant::users.cancel') }}
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg rounded-3 px-5 shadow-sm">
                        <i class="bi bi-save me-2"></i>{{ __('tenant::users.update') }}
                    </button>
                </div>
            </form>
         </div>
@endsection

@push('scripts')
    <script>
        const data = {
            Users: @json($users->map(fn($u) => ['id' => $u->id, 'name' => ($u->first_name . ' ' . $u->last_name) ?: $u->user_name])),
            Groups: @json($groups),
            Roles: @json($roles),
            RoleAndSubordinates: @json($roles)
        };

        let memberCount = {{ count($members) }};

        $(document).ready(function() {
            const typeSelect = $('#memberType');
            const valueSelect = $('#memberValue' );

            function updateOptions() {
                const type = typeSelect.val();
                const options = data[type];
                valueSelect.empty();
                options.forEach(opt => {
                    valueSelect.append(new Option(opt.name, opt.id));
                });
                valueSelect.trigger('change');
            }

            typeSelect.on('change', updateOptions);
            updateOptions();

            $('#addMember').on('click', function() {
                const type = typeSelect.val();
                const id = valueSelect.val();
                const name = valueSelect.find('option:selected').text();

                if (!id) return;

                // Check if already added
                if ($(`#membersList tr[data-id="${id}"][data-type="${type}"]`).length > 0) {
                    alert('Member already added');
                    return;
                }

                const html = `
                    <tr data-id="${id}" data-type="${type}">
                         <td><span class="badge bg-info">${type}</span></td>
                        <td>${name}</td>
                        <td>
                            <input type="hidden"  name="members[${memberCount}][id]" value="${id}">
                            <input type="hidden" name="members[${memberCount}][type]" value="${type}">
                            <input type="hidden" name="members[${memberCount}][name]" value="${name}">
                            <button type="button" class="btn btn-sm text-danger remove-member"><i class="bi bi-x-circle"></i></button>
                        </td>
                    </tr>
                `;
                $('#membersList').append(html);
                memberCount++;
            });

            $(document).on('click', '.remove-member', function() {
                $(this).closest('tr').remove();
            });

            $('#groupForm').on('submit', function(e) {
                if ($('#membersList tr').length === 0) {
                    alert('Please add at least one member.');
                    e.preventDefault();
                }
            });
        });
    </script>
@endpush