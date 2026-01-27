<?php

namespace App\Modules\Tenant\Users\Presentation\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RolesController extends Controller
{
    public function index()
    {
        $roles = DB::connection('tenant')->table('vtiger_role')->get();
        $tree = $this->buildTree($roles);

        return view('tenant::roles.index', compact('tree'));
    }

    private function buildTree($roles, $parentPath = '')
    {
        $branch = [];

        foreach ($roles as $role) {
            $pathParts = explode('::', $role->parentrole);
            $myParentPath = count($pathParts) > 1 ? implode('::', array_slice($pathParts, 0, -1)) : '';

            if ($myParentPath === $parentPath) {
                $children = $this->buildTree($roles, $role->parentrole);
                $role->children = $children;
                $branch[] = $role;
            }
        }

        return $branch;
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'roleid' => 'required|string',
            'new_parent_id' => 'nullable|string',
        ]);

        DB::connection('tenant')->transaction(function () use ($validated) {
            $role = DB::connection('tenant')->table('vtiger_role')
                ->where('roleid', $validated['roleid'])
                ->first();

            if (!$role)
                return;

            $newParent = null;
            if ($validated['new_parent_id']) {
                $newParent = DB::connection('tenant')->table('vtiger_role')
                    ->where('roleid', $validated['new_parent_id'])
                    ->first();
            }

            $oldPath = $role->parentrole;
            $newPath = $newParent ? ($newParent->parentrole . '::' . $role->roleid) : $role->roleid;
            $newDepth = $newParent ? ($newParent->depth + 1) : 0;

            // Update the moved role
            DB::connection('tenant')->table('vtiger_role')
                ->where('roleid', $role->roleid)
                ->update([
                    'parentrole' => $newPath,
                    'depth' => $newDepth
                ]);

            // Update all children's paths recursively
            $allRoles = DB::connection('tenant')->table('vtiger_role')->get();
            $this->updateChildrenPaths($role->roleid, $oldPath, $newPath, $newDepth, $allRoles);
        });

        return response()->json(['success' => true]);
    }

    private function updateChildrenPaths($parentRoleId, $oldParentPath, $newParentPath, $newParentDepth, $allRoles)
    {
        foreach ($allRoles as $role) {
            // Check if this role is a child by checking its path starts with oldParentPath::
            if (strpos($role->parentrole, $oldParentPath . '::') === 0) {
                $relativeSuffix = substr($role->parentrole, strlen($oldParentPath));
                $updatedPath = $newParentPath . $relativeSuffix;
                $updatedDepth = $newParentDepth + (count(explode('::', $updatedPath)) - count(explode('::', $newParentPath)));

                DB::connection('tenant')->table('vtiger_role')
                    ->where('roleid', $role->roleid)
                    ->update([
                        'parentrole' => $updatedPath,
                        'depth' => $updatedDepth
                    ]);
            }
        }
    }

    public function create()
    {
        $parentRoles = DB::connection('tenant')->table('vtiger_role')->get();
        $profiles = DB::connection('tenant')->table('vtiger_profile')->get();
        $modules = DB::connection('tenant')->table('vtiger_tab')
            ->where('presence', 0)
            ->orderBy('tablabel')
            ->get();

        return view('tenant::roles.create', compact('parentRoles', 'profiles', 'modules'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'rolename' => 'required|string|max:200|unique:tenant.vtiger_role,rolename',
            'parent_role_id' => 'required|string',
            'assign_type' => 'required|in:all,same_or_subordinate,subordinate',
            'privilege_type' => 'required|in:direct,profile',
            'assigned_profile_id' => 'nullable|integer',
            'copy_from_profile' => 'nullable|integer',
        ]);

        $parentRole = DB::connection('tenant')->table('vtiger_role')
            ->where('roleid', $validated['parent_role_id'])
            ->first();

        // Generate new Role ID (usually Hx format or similar in vtiger, but typically standard ID)
        // Check `vtiger_role` ID format. It's usually like 'H1', 'H2'. 
        // IF it is standard vtiger, we need to generate proper ID.
        // For simplicity in this "modern" version, if the table allows arbitrary string/int IDs great.
        // Assuming standard vtiger behavior: depth and parentrole string must be calculated.

        $roleId = 'H' . (DB::connection('tenant')->table('vtiger_role')->count() + 1 + rand(100, 999)); // Simplified generation

        $depth = $parentRole->depth + 1;
        $parentRolePath = $parentRole->parentrole . '::' . $roleId;

        DB::connection('tenant')->table('vtiger_role')->insert([
            'roleid' => $roleId,
            'rolename' => $validated['rolename'],
            'parentrole' => $parentRolePath,
            'depth' => $depth,
            'assign_type' => $validated['assign_type'],
            'privilege_type' => $validated['privilege_type'],
            'assigned_profile_id' => $validated['assigned_profile_id'] ?? null,
        ]);


        // Handle direct privileges if selected
        if ($validated['privilege_type'] === 'direct' && $request->has('privileges')) {
            // Insert privileges for the new role
            foreach ($request->input('privileges', []) as $tabid => $permissions) {
                // Only insert if at least one permission is granted
                if (!empty(array_filter($permissions))) {
                    DB::connection('tenant')->table('vtiger_role2profile')->insert([
                        'roleid' => $roleId,
                        'tabid' => $tabid,
                        'view' => isset($permissions['view']) ? 1 : 0,
                        'create' => isset($permissions['create']) ? 1 : 0,
                        'edit' => isset($permissions['edit']) ? 1 : 0,
                        'delete' => isset($permissions['delete']) ? 1 : 0,
                    ]);
                }
            }
        }

        return redirect()->route('tenant.settings.users.roles.index')
            ->with('success', __('tenant::users.created_successfully'));
    }

    public function edit($id)
    {
        $role = DB::connection('tenant')->table('vtiger_role')->where('roleid', $id)->first();
        if (!$role)
            abort(404);

        $parentRoles = DB::connection('tenant')->table('vtiger_role')->where('roleid', '!=', $id)->get();
        $profiles = DB::connection('tenant')->table('vtiger_profile')->get();
        $modules = DB::connection('tenant')->table('vtiger_tab')
            ->where('presence', 0)
            ->orderBy('tablabel')
            ->get();

        // Fetch existing privileges for this role
        // In vtiger, role privileges can be stored in multiple ways:
        // 1. If role uses a profile (assigned_profile_id), privileges come from that profile
        // 2. If role has direct privileges, they're in vtiger_role2profile or similar tables

        $rolePrivileges = [];

        // Check if role has an assigned profile
        if (isset($role->assigned_profile_id) && $role->assigned_profile_id) {
            // Load privileges from the assigned profile
            $profileId = $role->assigned_profile_id;

            $tabPermissions = DB::connection('tenant')
                ->table('vtiger_profile2tab')
                ->where('profileid', $profileId)
                ->get();

            foreach ($tabPermissions as $tabPerm) {
                $tabid = $tabPerm->tabid;

                $standardPerms = DB::connection('tenant')
                    ->table('vtiger_profile2standardpermissions')
                    ->where('profileid', $profileId)
                    ->where('tabid', $tabid)
                    ->first();

                $rolePrivileges[$tabid] = [
                    'view' => $tabPerm->permissions == 0,
                    'create' => $standardPerms ? $standardPerms->permissions == 0 : false,
                    'edit' => $standardPerms ? $standardPerms->permissions == 0 : false,
                    'delete' => $standardPerms ? $standardPerms->permissions == 0 : false,
                ];
            }
        } else {
            // Load direct role privileges
            // Note: vtiger typically uses profiles, but we'll check for direct role permissions
            // This might be stored in custom tables or vtiger_role2profile
            $rolePerms = DB::connection('tenant')
                ->table('vtiger_role2profile')
                ->where('roleid', $id)
                ->get();

            foreach ($rolePerms as $perm) {
                if (isset($perm->tabid)) {
                    $rolePrivileges[$perm->tabid] = [
                        'view' => isset($perm->view) ? $perm->view : false,
                        'create' => isset($perm->create) ? $perm->create : false,
                        'edit' => isset($perm->edit) ? $perm->edit : false,
                        'delete' => isset($perm->delete) ? $perm->delete : false,
                    ];
                }
            }
        }

        return view('tenant::roles.edit', compact('role', 'parentRoles', 'profiles', 'modules', 'rolePrivileges'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'rolename' => 'required|string|max:200',
            'assign_type' => 'required|in:all,same_or_subordinate,subordinate',
            'privilege_type' => 'required|in:direct,profile',
            'assigned_profile_id' => 'nullable|integer',
            'copy_from_profile' => 'nullable|integer',
        ]);

        DB::connection('tenant')->table('vtiger_role')
            ->where('roleid', $id)
            ->update([
                'rolename' => $validated['rolename'],
                'assign_type' => $validated['assign_type'],
                'privilege_type' => $validated['privilege_type'],
                'assigned_profile_id' => $validated['assigned_profile_id'] ?? null,
            ]);

        // Handle direct privileges if selected
        if ($validated['privilege_type'] === 'direct' && $request->has('privileges')) {
            // First, delete existing role privileges
            DB::connection('tenant')->table('vtiger_role2profile')->where('roleid', $id)->delete();

            // Insert new privileges
            foreach ($request->input('privileges', []) as $tabid => $permissions) {
                // Only insert if at least one permission is granted
                if (!empty(array_filter($permissions))) {
                    DB::connection('tenant')->table('vtiger_role2profile')->insert([
                        'roleid' => $id,
                        'tabid' => $tabid,
                        'view' => isset($permissions['view']) ? 1 : 0,
                        'create' => isset($permissions['create']) ? 1 : 0,
                        'edit' => isset($permissions['edit']) ? 1 : 0,
                        'delete' => isset($permissions['delete']) ? 1 : 0,
                    ]);
                }
            }
        } elseif ($validated['privilege_type'] === 'profile') {
            // If switching to profile-based, clear any direct privileges
            DB::connection('tenant')->table('vtiger_role2profile')->where('roleid', $id)->delete();
        }

        // Note: Moving roles in hierarchy (changing parent) is complex due to 'parentrole' path updates.
        // Skipping for MVP unless requested.

        return redirect()->route('tenant.settings.users.roles.index')
            ->with('success', __('tenant::users.updated_successfully'));
    }

    public function destroy($id)
    {
        // Check if role has users
        $hasUsers = DB::connection('tenant')->table('vtiger_user2role')->where('roleid', $id)->exists();
        if ($hasUsers) {
            return back()->withErrors(['error' => 'Cannot delete role assigned to users.']);
        }

        // Check for sub-roles
        $hasChildren = DB::connection('tenant')->table('vtiger_role')->where('parentrole', 'like', "%::{$id}%")->exists();
        if ($hasChildren) {
            // This check is loose, 'parentrole' contains path like H1::H2::H3.
            // If we delete H2, H3 becomes orphaned or needs reparenting.
            // Block for safety.
            return back()->withErrors(['error' => 'Cannot delete role with child roles.']);
        }

        DB::connection('tenant')->table('vtiger_role')->where('roleid', $id)->delete();

        return redirect()->route('tenant.settings.users.roles.index')
            ->with('success', __('tenant::users.deleted_successfully'));
    }

    public function getProfilePrivileges(Request $request)
    {
        $profileId = $request->input('profile_id');

        if (!$profileId) {
            return response()->json(['error' => 'Profile ID is required'], 400);
        }

        // Fetch profile privileges from vtiger tables
        // In vtiger, privileges are stored in vtiger_profile2tab, vtiger_profile2standardpermissions, etc.
        $privileges = [];

        // Get tab (module) permissions
        $tabPermissions = DB::connection('tenant')
            ->table('vtiger_profile2tab')
            ->where('profileid', $profileId)
            ->get();

        foreach ($tabPermissions as $tabPerm) {
            $tabid = $tabPerm->tabid;

            // Get standard permissions for this module
            $standardPerms = DB::connection('tenant')
                ->table('vtiger_profile2standardpermissions')
                ->where('profileid', $profileId)
                ->where('tabid', $tabid)
                ->first();

            $privileges[$tabid] = [
                'view' => $tabPerm->permissions == 0, // 0 = enabled, 1 = disabled in vtiger
                'create' => $standardPerms ? $standardPerms->permissions == 0 : false,
                'edit' => $standardPerms ? $standardPerms->permissions == 0 : false,
                'delete' => $standardPerms ? $standardPerms->permissions == 0 : false,
            ];
        }

        return response()->json(['privileges' => $privileges]);
    }
}
