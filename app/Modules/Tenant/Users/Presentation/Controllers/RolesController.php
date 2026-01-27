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

        // Only show modules that are entity types (isentitytype = 1) and active (presence = 0)
        // This matches vtiger's behavior - only entity modules need permission management
        $modules = DB::connection('tenant')->table('vtiger_tab')
            ->where('presence', 0)
            ->where('isentitytype', 1)
            ->orderBy('name')
            ->get();

        return view('tenant::roles.create', compact('parentRoles', 'profiles', 'modules'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'rolename' => 'required|string|max:200|unique:tenant.vtiger_role,rolename',
            'parent_role_id' => 'required|string',
            'allowassignedrecordsto' => 'required|in:1,2,3',
            'profile_directly_related_to_role' => 'required|in:0,1',
            'profilename' => 'nullable|string|max:200',
            'profiles' => 'nullable|array',
            'profiles.*' => 'integer',
            'copy_from_profile' => 'nullable|integer',
        ]);

        $parentRole = DB::connection('tenant')->table('vtiger_role')
            ->where('roleid', $validated['parent_role_id'])
            ->first();

        if (!$parentRole) {
            return back()->withErrors(['parent_role_id' => 'Parent role not found.']);
        }

        // Generate new Role ID (vtiger format: H1, H2, etc.)
        $roleId = 'H' . (DB::connection('tenant')->table('vtiger_role')->count() + 1 + rand(100, 999));

        $depth = $parentRole->depth + 1;
        $parentRolePath = $parentRole->parentrole . '::' . $roleId;

        DB::connection('tenant')->transaction(function () use ($validated, $request, $roleId, $parentRolePath, $depth, $parentRole) {
            // Insert the role
            DB::connection('tenant')->table('vtiger_role')->insert([
                'roleid' => $roleId,
                'rolename' => $validated['rolename'],
                'parentrole' => $parentRolePath,
                'depth' => $depth,
                'allowassignedrecordsto' => $validated['allowassignedrecordsto'],
                'copy_from_profile' => $validated['copy_from_profile'] ?? null,
            ]);

            // Handle profile assignment
            if ($validated['profile_directly_related_to_role'] == '1') {
                // Create a directly related profile
                $profileName = $validated['profilename'] ?? ($validated['rolename'] . ' Profile');

                $profileId = DB::connection('tenant')->table('vtiger_profile')->insertGetId([
                    'profilename' => $profileName,
                    'directly_related_to_role' => 1,
                    'description' => 'Profile for ' . $validated['rolename'],
                ]);

                // Link role to profile
                DB::connection('tenant')->table('vtiger_role2profile')->insert([
                    'roleid' => $roleId,
                    'profileid' => $profileId,
                ]);

                // Save module-level permissions if provided
                if ($request->has('permissions')) {
                    $profile2tab = [];
                    $profile2standard = [];
                    foreach ($request->input('permissions', []) as $tabid => $perms) {
                        // Insert into vtiger_profile2tab (module access)
                        if (isset($perms['view']) && $perms['view']) {
                            $profile2tab[] = [
                                'profileid' => $profileId,
                                'tabid' => $tabid,
                                'permissions' => 0, // 0 = enabled in vtiger
                            ];

                            // Insert into vtiger_profile2standardpermissions (create, edit, delete)
                            $profile2standard[] = [
                                'profileid' => $profileId,
                                'tabid' => $tabid,
                                'operation' => 0, // Create
                                'permissions' => isset($perms['create']) && $perms['create'] ? 0 : 1,
                            ];
                            $profile2standard[] = [
                                'profileid' => $profileId,
                                'tabid' => $tabid,
                                'operation' => 1, // Edit
                                'permissions' => isset($perms['edit']) && $perms['edit'] ? 0 : 1,
                            ];
                            $profile2standard[] = [
                                'profileid' => $profileId,
                                'tabid' => $tabid,
                                'operation' => 2, // Delete
                                'permissions' => isset($perms['delete']) && $perms['delete'] ? 0 : 1,
                            ];
                        }
                    }
                    if (!empty($profile2tab)) {
                        DB::connection('tenant')->table('vtiger_profile2tab')->insert($profile2tab);
                    }
                    if (!empty($profile2standard)) {
                        DB::connection('tenant')->table('vtiger_profile2standardpermissions')->insert($profile2standard);
                    }
                }

                // Save field-level permissions if provided
                if ($request->has('field_privileges')) {
                    $fieldPrivs = json_decode($request->input('field_privileges'), true);
                    if (!empty($fieldPrivs)) {
                        $profile2field = [];
                        foreach ($fieldPrivs as $tabid => $fields) {
                            foreach ($fields as $fieldid => $permission) {
                                // permission: 0=Invisible, 1=Read-only, 2=Write
                                $visible = ($permission == '0') ? 1 : 0;
                                $readonly = ($permission == '1') ? 1 : 0;

                                $profile2field[] = [
                                    'profileid' => $profileId,
                                    'tabid' => $tabid,
                                    'fieldid' => $fieldid,
                                    'visible' => $visible,
                                    'readonly' => $readonly,
                                ];
                            }
                        }
                        if (!empty($profile2field)) {
                            DB::connection('tenant')->table('vtiger_profile2field')->insert($profile2field);
                        }
                    }
                }

                // Save tool-level permissions if provided
                if ($request->has('tool_privileges')) {
                    $toolPrivs = json_decode($request->input('tool_privileges'), true);
                    if (!empty($toolPrivs)) {
                        $profile2utility = [];
                        foreach ($toolPrivs as $tabid => $tools) {
                            foreach ($tools as $toolid => $isEnabled) {
                                $actionId = $toolid;
                                if ($toolid == 'Import')
                                    $actionId = 4;
                                if ($toolid == 'Export')
                                    $actionId = 3;
                                if ($toolid == 'Merge')
                                    $actionId = 8;
                                if ($toolid == 'DuplicatesHandling')
                                    $actionId = 10;

                                $profile2utility[] = [
                                    'profileid' => $profileId,
                                    'tabid' => $tabid,
                                    'activityid' => $actionId,
                                    'permission' => $isEnabled ? 0 : 1, // 0=allowed, 1=denied
                                ];
                            }
                        }
                        if (!empty($profile2utility)) {
                            DB::connection('tenant')->table('vtiger_profile2utility')->insert($profile2utility);
                        }
                    }
                }
            } else {
                // Assign existing profiles
                if (!empty($validated['profiles'])) {
                    foreach ($validated['profiles'] as $profileId) {
                        DB::connection('tenant')->table('vtiger_role2profile')->insert([
                            'roleid' => $roleId,
                            'profileid' => $profileId,
                        ]);
                    }
                }
            }

            // Copy picklist values from parent role (vtiger behavior)
            DB::connection('tenant')->statement(
                "INSERT INTO vtiger_role2picklist (roleid, picklistvalueid, picklistid, sortid)
                 SELECT ?, picklistvalueid, picklistid, sortid
                 FROM vtiger_role2picklist WHERE roleid = ?",
                [$roleId, $parentRole->roleid]
            );
        });

        return redirect()->route('tenant.settings.users.roles.index')
            ->with('success', __('tenant::users.created_successfully'));
    }

    public function edit($id)
    {
        $role = DB::connection('tenant')->table('vtiger_role')->where('roleid', $id)->first();
        if (!$role)
            abort(404);

        $profiles = DB::connection('tenant')->table('vtiger_profile')->get();

        // Get parent role name
        $parentRoleName = null;
        if ($role->parentrole) {
            $parentRoleIds = explode('::', $role->parentrole);
            if (count($parentRoleIds) > 1) {
                $parentRoleId = $parentRoleIds[count($parentRoleIds) - 2];
                $parentRoleData = DB::connection('tenant')->table('vtiger_role')
                    ->where('roleid', $parentRoleId)
                    ->first();
                $parentRoleName = $parentRoleData->rolename ?? null;
            }
        }

        // Get assigned profiles for this role
        $roleProfiles = DB::connection('tenant')->table('vtiger_role2profile')
            ->where('roleid', $id)
            ->pluck('profileid')
            ->toArray();

        // Check if role has a directly related profile
        $directlyRelatedProfileId = null;
        $directlyRelatedProfileName = null;

        if (!empty($roleProfiles)) {
            $directProfile = DB::connection('tenant')->table('vtiger_profile')
                ->whereIn('profileid', $roleProfiles)
                ->where('directly_related_to_role', 1)
                ->first();

            if ($directProfile) {
                $directlyRelatedProfileId = $directProfile->profileid;
                $directlyRelatedProfileName = $directProfile->profilename;
            }
        }

        // Load modules for the permissions table (entity types only)
        $modules = DB::connection('tenant')->table('vtiger_tab')
            ->where('presence', 0)
            ->where('isentitytype', 1)
            ->orderBy('name')
            ->get();

        $existingPrivileges = [];
        $existingFieldPrivs = [];
        $existingToolPrivs = [];

        if ($directlyRelatedProfileId) {
            // Load module privileges
            $tabPermissions = DB::connection('tenant')
                ->table('vtiger_profile2tab')
                ->where('profileid', $directlyRelatedProfileId)
                ->get();

            foreach ($tabPermissions as $tabPerm) {
                $tabid = $tabPerm->tabid;
                $standardPerms = DB::connection('tenant')
                    ->table('vtiger_profile2standardpermissions')
                    ->where('profileid', $directlyRelatedProfileId)
                    ->where('tabid', $tabid)
                    ->get()
                    ->keyBy('operation');

                $existingPrivileges[$tabid] = [
                    'view' => $tabPerm->permissions == 0,
                    'create' => isset($standardPerms[0]) ? $standardPerms[0]->permissions == 0 : false,
                    'edit' => isset($standardPerms[1]) ? $standardPerms[1]->permissions == 0 : false,
                    'delete' => isset($standardPerms[2]) ? $standardPerms[2]->permissions == 0 : false,
                ];
            }

            // Load field privileges
            $fieldPermissions = DB::connection('tenant')
                ->table('vtiger_profile2field')
                ->where('profileid', $directlyRelatedProfileId)
                ->get();

            foreach ($fieldPermissions as $fieldPerm) {
                $val = 2; // Write
                if ($fieldPerm->visible == 1)
                    $val = 0; // Invisible
                else if ($fieldPerm->readonly == 1)
                    $val = 1; // Read-only
                $existingFieldPrivs[$fieldPerm->tabid][$fieldPerm->fieldid] = $val;
            }

            // Load tool privileges
            $toolPermissions = DB::connection('tenant')
                ->table('vtiger_profile2utility')
                ->where('profileid', $directlyRelatedProfileId)
                ->get();

            foreach ($toolPermissions as $toolPerm) {
                $activityId = $toolPerm->activityid;
                $toolId = $activityId;
                if ($activityId == 4)
                    $toolId = 'Import';
                if ($activityId == 3)
                    $toolId = 'Export';
                if ($activityId == 8)
                    $toolId = 'Merge';
                if ($activityId == 10)
                    $toolId = 'DuplicatesHandling';
                $existingToolPrivs[$toolPerm->tabid][$toolId] = $toolPerm->permission == 0;
            }
        }

        return view('tenant::roles.edit', compact(
            'role',
            'profiles',
            'modules',
            'parentRoleName',
            'roleProfiles',
            'directlyRelatedProfileId',
            'directlyRelatedProfileName',
            'existingPrivileges',
            'existingFieldPrivs',
            'existingToolPrivs'
        ));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'rolename' => 'required|string|max:200',
            'allowassignedrecordsto' => 'required|in:1,2,3',
            'profile_directly_related_to_role' => 'required|in:0,1',
            'profilename' => 'nullable|string|max:200',
            'profiles' => 'nullable|array',
            'profiles.*' => 'integer',
            'profile_directly_related_to_role_id' => 'nullable|integer',
            'copy_from_profile' => 'nullable|integer',
        ]);

        DB::connection('tenant')->transaction(function () use ($validated, $request, $id) {
            // Update role basic info
            DB::connection('tenant')->table('vtiger_role')
                ->where('roleid', $id)
                ->update([
                    'rolename' => $validated['rolename'],
                    'allowassignedrecordsto' => $validated['allowassignedrecordsto'],
                    'copy_from_profile' => $validated['copy_from_profile'] ?? null,
                ]);

            // Clear existing profile assignments
            DB::connection('tenant')->table('vtiger_role2profile')->where('roleid', $id)->delete();

            // Handle profile assignment
            if ($validated['profile_directly_related_to_role'] == '1') {
                // Create or update directly related profile
                $profileId = $validated['profile_directly_related_to_role_id'] ?? null;
                $profileName = $validated['profilename'] ?? ($validated['rolename'] . ' Profile');

                if ($profileId) {
                    // Update existing directly related profile
                    DB::connection('tenant')->table('vtiger_profile')
                        ->where('profileid', $profileId)
                        ->update([
                            'profilename' => $profileName,
                        ]);
                } else {
                    // Create new directly related profile
                    $profileId = DB::connection('tenant')->table('vtiger_profile')->insertGetId([
                        'profilename' => $profileName,
                        'directly_related_to_role' => 1,
                        'description' => 'Profile for ' . $validated['rolename'],
                    ]);
                }

                // Link role to profile
                DB::connection('tenant')->table('vtiger_role2profile')->insert([
                    'roleid' => $id,
                    'profileid' => $profileId,
                ]);

                // Clear existing permissions for this profile before re-saving
                DB::connection('tenant')->table('vtiger_profile2tab')->where('profileid', $profileId)->delete();
                DB::connection('tenant')->table('vtiger_profile2standardpermissions')->where('profileid', $profileId)->delete();
                DB::connection('tenant')->table('vtiger_profile2field')->where('profileid', $profileId)->delete();
                DB::connection('tenant')->table('vtiger_profile2utility')->where('profileid', $profileId)->delete();

                // Save module-level permissions if provided
                if ($request->has('permissions')) {
                    $profile2tab = [];
                    $profile2standard = [];
                    foreach ($request->input('permissions', []) as $tabid => $perms) {
                        // Insert into vtiger_profile2tab (module access)
                        if (isset($perms['view']) && $perms['view']) {
                            $profile2tab[] = [
                                'profileid' => $profileId,
                                'tabid' => $tabid,
                                'permissions' => 0, // 0 = enabled in vtiger
                            ];

                            // Insert into vtiger_profile2standardpermissions (create, edit, delete)
                            $profile2standard[] = [
                                'profileid' => $profileId,
                                'tabid' => $tabid,
                                'operation' => 0, // Create
                                'permissions' => isset($perms['create']) && $perms['create'] ? 0 : 1,
                            ];

                            $profile2standard[] = [
                                'profileid' => $profileId,
                                'tabid' => $tabid,
                                'operation' => 1, // Edit
                                'permissions' => isset($perms['edit']) && $perms['edit'] ? 0 : 1,
                            ];

                            $profile2standard[] = [
                                'profileid' => $profileId,
                                'tabid' => $tabid,
                                'operation' => 2, // Delete
                                'permissions' => isset($perms['delete']) && $perms['delete'] ? 0 : 1,
                            ];
                        }
                    }
                    if (!empty($profile2tab)) {
                        DB::connection('tenant')->table('vtiger_profile2tab')->insert($profile2tab);
                    }
                    if (!empty($profile2standard)) {
                        DB::connection('tenant')->table('vtiger_profile2standardpermissions')->insert($profile2standard);
                    }
                }

                // Save field-level permissions if provided
                if ($request->has('field_privileges')) {
                    $fieldPrivs = json_decode($request->input('field_privileges'), true);
                    if (!empty($fieldPrivs)) {
                        $profile2field = [];
                        foreach ($fieldPrivs as $tabid => $fields) {
                            foreach ($fields as $fieldid => $permission) {
                                $visible = ($permission == '0') ? 1 : 0;
                                $readonly = ($permission == '1') ? 1 : 0;

                                $profile2field[] = [
                                    'profileid' => $profileId,
                                    'tabid' => $tabid,
                                    'fieldid' => $fieldid,
                                    'visible' => $visible,
                                    'readonly' => $readonly,
                                ];
                            }
                        }
                        if (!empty($profile2field)) {
                            DB::connection('tenant')->table('vtiger_profile2field')->insert($profile2field);
                        }
                    }
                }

                // Save tool-level permissions if provided
                if ($request->has('tool_privileges')) {
                    $toolPrivs = json_decode($request->input('tool_privileges'), true);
                    if (!empty($toolPrivs)) {
                        $profile2utility = [];
                        foreach ($toolPrivs as $tabid => $tools) {
                            foreach ($tools as $toolid => $isEnabled) {
                                $actionId = $toolid;
                                if ($toolid == 'Import')
                                    $actionId = 4;
                                if ($toolid == 'Export')
                                    $actionId = 3;
                                if ($toolid == 'Merge')
                                    $actionId = 8;
                                if ($toolid == 'DuplicatesHandling')
                                    $actionId = 10;

                                $profile2utility[] = [
                                    'profileid' => $profileId,
                                    'tabid' => $tabid,
                                    'activityid' => $actionId,
                                    'permission' => $isEnabled ? 0 : 1,
                                ];
                            }
                        }
                        if (!empty($profile2utility)) {
                            DB::connection('tenant')->table('vtiger_profile2utility')->insert($profile2utility);
                        }
                    }
                }
            } else {
                // Assign existing profiles
                if (!empty($validated['profiles'])) {
                    foreach ($validated['profiles'] as $profileId) {
                        DB::connection('tenant')->table('vtiger_role2profile')->insert([
                            'roleid' => $id,
                            'profileid' => $profileId,
                        ]);
                    }
                }
            }
        });

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

            // Get standard permissions for this module (Create, Edit, Delete)
            $standardPerms = DB::connection('tenant')
                ->table('vtiger_profile2standardpermissions')
                ->where('profileid', $profileId)
                ->where('tabid', $tabid)
                ->get()
                ->keyBy('operation'); // Key by operation: 0=Create, 1=Edit, 2=Delete

            $privileges[$tabid] = [
                'view' => $tabPerm->permissions == 0,
                'create' => isset($standardPerms[0]) ? $standardPerms[0]->permissions == 0 : false,
                'edit' => isset($standardPerms[1]) ? $standardPerms[1]->permissions == 0 : false,
                'delete' => isset($standardPerms[2]) ? $standardPerms[2]->permissions == 0 : false,
            ];
        }

        // Get field permissions
        $fieldPermissions = DB::connection('tenant')
            ->table('vtiger_profile2field')
            ->where('profileid', $profileId)
            ->get();

        $fieldPrivs = [];
        foreach ($fieldPermissions as $fieldPerm) {
            $tabid = $fieldPerm->tabid;
            $fieldid = $fieldPerm->fieldid;

            // Logic: 0=Invisible, 1=Read-only, 2=Write
            $val = 2; // Write
            if ($fieldPerm->visible == 1)
                $val = 0; // Invisible
            else if ($fieldPerm->readonly == 1)
                $val = 1; // Read-only

            $fieldPrivs[$tabid][$fieldid] = $val;
        }

        // Get tool permissions
        $toolPermissions = DB::connection('tenant')
            ->table('vtiger_profile2utility')
            ->where('profileid', $profileId)
            ->get();

        $toolPrivs = [];
        foreach ($toolPermissions as $toolPerm) {
            $tabid = $toolPerm->tabid;
            $activityId = $toolPerm->activityid;

            // Map activityId back to toolid string
            $toolId = $activityId;
            if ($activityId == 4)
                $toolId = 'Import';
            if ($activityId == 3)
                $toolId = 'Export';
            if ($activityId == 8)
                $toolId = 'Merge';
            if ($activityId == 10)
                $toolId = 'DuplicatesHandling';

            $toolPrivs[$tabid][$toolId] = $toolPerm->permission == 0;
        }

        return response()->json([
            'privileges' => $privileges,
            'field_privileges' => $fieldPrivs,
            'tool_privileges' => $toolPrivs
        ]);
    }

    public function getModuleFields(Request $request)
    {
        $moduleId = $request->input('module_id');
        if (!$moduleId) {
            return response()->json(['error' => 'Module ID is required'], 400);
        }

        $locale = app()->getLocale();
        $labelColumn = ($locale === 'ar') ? 'fieldlabel_ar' : 'fieldlabel_en';

        $fields = DB::connection('tenant')
            ->table('vtiger_field')
            ->join('vtiger_blocks', 'vtiger_field.block', '=', 'vtiger_blocks.blockid')
            ->where('vtiger_field.tabid', $moduleId)
            ->whereIn('vtiger_field.presence', [0, 2])
            ->whereIn('vtiger_field.displaytype', [1, 2, 4])
            ->where('vtiger_blocks.visible', 0) // 0 = visible in vtiger
            ->orderBy('vtiger_blocks.sequence')
            ->orderBy('vtiger_field.sequence')
            ->get([
                'vtiger_field.fieldid',
                'vtiger_field.fieldname',
                DB::raw("CASE 
                    WHEN vtiger_field.fieldname = 'cf_912' THEN '" . __('tenant::users.value') . "'
                    WHEN (vtiger_field.{$labelColumn} IS NOT NULL AND vtiger_field.{$labelColumn} != '') THEN vtiger_field.{$labelColumn}
                    ELSE vtiger_field.fieldname 
                END as fieldlabel"),
                'vtiger_field.uitype',
                'vtiger_field.typeofdata'
            ]);

        return response()->json(['fields' => $fields]);
    }

    public function getModuleTools(Request $request)
    {
        $moduleId = $request->input('module_id');
        if (!$moduleId) {
            return response()->json(['error' => 'Module ID is required'], 400);
        }

        // In vtiger, tools are standard actions like Import, Export, etc.
        $tools = [
            ['toolid' => 'Import', 'toolname' => __('tenant::users.import'), 'description' => __('tenant::users.import_description')],
            ['toolid' => 'Export', 'toolname' => __('tenant::users.export'), 'description' => __('tenant::users.export_description')],
            ['toolid' => 'Merge', 'toolname' => __('tenant::users.merge'), 'description' => __('tenant::users.merge_description')],
            ['toolid' => 'DuplicatesHandling', 'toolname' => __('tenant::users.duplicates_handling'), 'description' => __('tenant::users.duplicates_handling_description')],
        ];

        return response()->json(['tools' => $tools]);
    }
}
