<?php

namespace App\Modules\Tenant\Users\Presentation\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GroupsController extends Controller
{
    public function index()
    {
        $groups = DB::connection('tenant')->table('vtiger_groups')->get();
        return view('tenant::groups.index', compact('groups'));
    }

    public function create()
    {
        $data = $this->getFormData();
        return view('tenant::groups.create', $data);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'groupname' => 'required|string|max:100|unique:tenant.vtiger_groups,groupname',
            'description' => 'nullable|string',
            'allow_ticket_assign' => 'nullable|boolean',
            'members' => 'nullable|array',
            'members.*.id' => 'required',
            'members.*.type' => 'required|in:Users,Groups,Roles,RoleAndSubordinates',
        ]);

        DB::connection('tenant')->transaction(function () use ($validated, $request) {
            $groupId = DB::connection('tenant')->table('vtiger_groups')->max('groupid') + 1;

            DB::connection('tenant')->table('vtiger_groups')->insert([
                'groupid' => $groupId,
                'groupname' => $validated['groupname'],
                'description' => $validated['description'],
                'allow_ticket_assign' => $request->has('allow_ticket_assign') ? 1 : 0,
            ]);

            $this->syncMembers($groupId, $validated['members'] ?? []);
        });

        return redirect()->route('tenant.settings.users.groups.index')
            ->with('success', __('tenant::users.created_successfully'));
    }

    public function edit($id)
    {
        $group = DB::connection('tenant')->table('vtiger_groups')->where('groupid', $id)->first();
        if (!$group)
            abort(404);

        $data = $this->getFormData($id);
        $data['group'] = $group;

        // Fetch existing members
        $data['selectedMembers'] = $this->getExistingMembers($id);

        return view('tenant::groups.edit', $data);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'groupname' => 'required|string|max:100',
            'description' => 'nullable|string',
            'allow_ticket_assign' => 'nullable|boolean',
            'members' => 'nullable|array',
            'members.*.id' => 'required',
            'members.*.type' => 'required|in:Users,Groups,Roles,RoleAndSubordinates',
        ]);

        DB::connection('tenant')->transaction(function () use ($validated, $id, $request) {
            DB::connection('tenant')->table('vtiger_groups')
                ->where('groupid', $id)
                ->update([
                    'groupname' => $validated['groupname'],
                    'description' => $validated['description'],
                    'allow_ticket_assign' => $request->has('allow_ticket_assign') ? 1 : 0,
                ]);

            $this->syncMembers($id, $validated['members'] ?? []);
        });

        return redirect()->route('tenant.settings.users.groups.index')
            ->with('success', __('tenant::users.updated_successfully'));
    }

    public function destroy($id)
    {
        DB::connection('tenant')->transaction(function () use ($id) {
            DB::connection('tenant')->table('vtiger_groups')->where('groupid', $id)->delete();
            DB::connection('tenant')->table('vtiger_users2group')->where('groupid', $id)->delete();
            DB::connection('tenant')->table('vtiger_group2grouprel')->where('groupid', $id)->delete();
            DB::connection('tenant')->table('vtiger_group2role')->where('groupid', $id)->delete();
            DB::connection('tenant')->table('vtiger_group2rs')->where('groupid', $id)->delete();
        });

        return redirect()->route('tenant.settings.users.groups.index')
            ->with('success', __('tenant::users.deleted_successfully'));
    }

    private function getFormData($excludeGroupId = null)
    {
        $users = DB::connection('tenant')->table('vtiger_users')
            ->select('id', 'user_name', 'first_name', 'last_name')
            ->where('status', 'Active')
            ->get();

        $groupsQuery = DB::connection('tenant')->table('vtiger_groups')
            ->select('groupid as id', 'groupname as name');
        if ($excludeGroupId) {
            $groupsQuery->where('groupid', '!=', $excludeGroupId);
        }
        $groups = $groupsQuery->get();

        $roles = DB::connection('tenant')->table('vtiger_role')
            ->select('roleid as id', 'rolename as name')
            ->get();

        return compact('users', 'groups', 'roles');
    }

    private function getExistingMembers($groupId)
    {
        $members = [];

        // Users
        $users = DB::connection('tenant')->table('vtiger_users2group')
            ->where('groupid', $groupId)
            ->pluck('userid')
            ->toArray();
        foreach ($users as $uid)
            $members[] = ['id' => $uid, 'type' => 'Users'];

        // Groups
        $groups = DB::connection('tenant')->table('vtiger_group2grouprel')
            ->where('groupid', $groupId)
            ->pluck('containsgroupid')
            ->toArray();
        foreach ($groups as $gid)
            $members[] = ['id' => $gid, 'type' => 'Groups'];

        // Roles
        $roles = DB::connection('tenant')->table('vtiger_group2role')
            ->where('groupid', $groupId)
            ->pluck('roleid')
            ->toArray();
        foreach ($roles as $rid)
            $members[] = ['id' => $rid, 'type' => 'Roles'];

        // RS
        $rs = DB::connection('tenant')->table('vtiger_group2rs')
            ->where('groupid', $groupId)
            ->pluck('roleandsubid')
            ->toArray();
        foreach ($rs as $rid)
            $members[] = ['id' => $rid, 'type' => 'RoleAndSubordinates'];

        return $members;
    }

    private function syncMembers($groupId, $members)
    {
        // Clear existing
        DB::connection('tenant')->table('vtiger_users2group')->where('groupid', $groupId)->delete();
        DB::connection('tenant')->table('vtiger_group2grouprel')->where('groupid', $groupId)->delete();
        DB::connection('tenant')->table('vtiger_group2role')->where('groupid', $groupId)->delete();
        DB::connection('tenant')->table('vtiger_group2rs')->where('groupid', $groupId)->delete();

        foreach ($members as $member) {
            switch ($member['type']) {
                case 'Users':
                    DB::connection('tenant')->table('vtiger_users2group')->insert(['groupid' => $groupId, 'userid' => $member['id']]);
                    break;
                case 'Groups':
                    DB::connection('tenant')->table('vtiger_group2grouprel')->insert(['groupid' => $groupId, 'containsgroupid' => $member['id']]);
                    break;
                case 'Roles':
                    DB::connection('tenant')->table('vtiger_group2role')->insert(['groupid' => $groupId, 'roleid' => $member['id']]);
                    break;
                case 'RoleAndSubordinates':
                    DB::connection('tenant')->table('vtiger_group2rs')->insert(['groupid' => $groupId, 'roleandsubid' => $member['id']]);
                    break;
            }
        }
    }
}
