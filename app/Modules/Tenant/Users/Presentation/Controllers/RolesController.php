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
        return view('tenant::roles.create', compact('parentRoles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'rolename' => 'required|string|max:200|unique:tenant.vtiger_role,rolename',
            'parent_role_id' => 'required|string', // References roleid
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
        ]);

        return redirect()->route('tenant.settings.users.roles.index')
            ->with('success', __('tenant::users.created_successfully'));
    }

    public function edit($id)
    {
        $role = DB::connection('tenant')->table('vtiger_role')->where('roleid', $id)->first();
        if (!$role)
            abort(404);

        $parentRoles = DB::connection('tenant')->table('vtiger_role')->where('roleid', '!=', $id)->get();

        return view('tenant::roles.edit', compact('role', 'parentRoles'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'rolename' => 'required|string|max:200',
        ]);

        DB::connection('tenant')->table('vtiger_role')
            ->where('roleid', $id)
            ->update([
                'rolename' => $validated['rolename']
            ]);

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
}
