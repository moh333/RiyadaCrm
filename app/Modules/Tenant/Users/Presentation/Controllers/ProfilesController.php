<?php

namespace App\Modules\Tenant\Users\Presentation\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfilesController extends Controller
{
    public function index()
    {
        $profiles = DB::connection('tenant')->table('vtiger_profile')
            ->leftJoin('vtiger_role2profile', 'vtiger_profile.profileid', '=', 'vtiger_role2profile.profileid')
            ->leftJoin('vtiger_role', 'vtiger_role2profile.roleid', '=', 'vtiger_role.roleid')
            ->select(
                'vtiger_profile.profileid',
                'vtiger_profile.profilename',
                'vtiger_profile.description',
                'vtiger_profile.directly_related_to_role',
                'vtiger_role.rolename as role_name'
            )
            ->get();

        return view('tenant::profiles.index', compact('profiles'));
    }

    public function create()
    {
        $profiles = DB::connection('tenant')->table('vtiger_profile')->get();

        // Only show modules that are entity types (isentitytype = 1) and active (presence = 0)
        $modules = DB::connection('tenant')->table('vtiger_tab')
            ->where('presence', 0)
            ->where('isentitytype', 1)
            ->orderBy('name')
            ->get();

        return view('tenant::profiles.create', compact('profiles', 'modules'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'profilename' => 'required|string|max:200|unique:tenant.vtiger_profile,profilename',
            'description' => 'nullable|string',
            'copy_from_profile' => 'nullable|integer',
        ]);

        $profileId = DB::connection('tenant')->table('vtiger_profile')->max('profileid') + 1;

        DB::connection('tenant')->transaction(function () use ($validated, $request, $profileId) {
            DB::connection('tenant')->table('vtiger_profile')->insert([
                'profileid' => $profileId,
                'profilename' => $validated['profilename'],
                'description' => $validated['description'],
                'directly_related_to_role' => 0,
            ]);

            // Save module-level permissions
            if ($request->has('permissions')) {
                $profile2tab = [];
                $profile2standard = [];
                foreach ($request->input('permissions', []) as $tabid => $perms) {
                    if (isset($perms['view']) && $perms['view']) {
                        $profile2tab[] = [
                            'profileid' => $profileId,
                            'tabid' => $tabid,
                            'permissions' => 0,
                        ];

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

            // Save field-level permissions
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

            // Save tool-level permissions
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
        });

        return redirect()->route('tenant.settings.users.profiles.index')
            ->with('success', __('tenant::users.created_successfully'));
    }

    public function edit($id)
    {
        $profile = DB::connection('tenant')->table('vtiger_profile')
            ->leftJoin('vtiger_role2profile', 'vtiger_profile.profileid', '=', 'vtiger_role2profile.profileid')
            ->leftJoin('vtiger_role', 'vtiger_role2profile.roleid', '=', 'vtiger_role.roleid')
            ->where('vtiger_profile.profileid', $id)
            ->select('vtiger_profile.*', 'vtiger_role.rolename as role_name')
            ->first();

        if (!$profile)
            abort(404);

        $profiles = DB::connection('tenant')->table('vtiger_profile')->get();

        // Load modules for the permissions table (entity types only)
        $modules = DB::connection('tenant')->table('vtiger_tab')
            ->where('presence', 0)
            ->where('isentitytype', 1)
            ->orderBy('name')
            ->get();

        $existingPrivileges = [];
        $existingFieldPrivs = [];
        $existingToolPrivs = [];

        // Load module privileges
        $tabPermissions = DB::connection('tenant')
            ->table('vtiger_profile2tab')
            ->where('profileid', $id)
            ->get();

        foreach ($tabPermissions as $tabPerm) {
            $tabid = $tabPerm->tabid;
            $standardPerms = DB::connection('tenant')
                ->table('vtiger_profile2standardpermissions')
                ->where('profileid', $id)
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
            ->where('profileid', $id)
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
            ->where('profileid', $id)
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

        return view('tenant::profiles.edit', compact(
            'profile',
            'profiles',
            'modules',
            'existingPrivileges',
            'existingFieldPrivs',
            'existingToolPrivs'
        ));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'profilename' => 'required|string|max:200',
            'description' => 'nullable|string',
            'copy_from_profile' => 'nullable|integer',
        ]);

        DB::connection('tenant')->transaction(function () use ($validated, $request, $id) {
            DB::connection('tenant')->table('vtiger_profile')
                ->where('profileid', $id)
                ->update([
                    'profilename' => $validated['profilename'],
                    'description' => $validated['description'],
                ]);

            // Clear existing permissions
            DB::connection('tenant')->table('vtiger_profile2tab')->where('profileid', $id)->delete();
            DB::connection('tenant')->table('vtiger_profile2standardpermissions')->where('profileid', $id)->delete();
            DB::connection('tenant')->table('vtiger_profile2field')->where('profileid', $id)->delete();
            DB::connection('tenant')->table('vtiger_profile2utility')->where('profileid', $id)->delete();

            // Save module-level permissions
            if ($request->has('permissions')) {
                $profile2tab = [];
                $profile2standard = [];
                foreach ($request->input('permissions', []) as $tabid => $perms) {
                    if (isset($perms['view']) && $perms['view']) {
                        $profile2tab[] = [
                            'profileid' => $id,
                            'tabid' => $tabid,
                            'permissions' => 0,
                        ];

                        $profile2standard[] = [
                            'profileid' => $id,
                            'tabid' => $tabid,
                            'operation' => 0,
                            'permissions' => isset($perms['create']) && $perms['create'] ? 0 : 1,
                        ];

                        $profile2standard[] = [
                            'profileid' => $id,
                            'tabid' => $tabid,
                            'operation' => 1,
                            'permissions' => isset($perms['edit']) && $perms['edit'] ? 0 : 1,
                        ];

                        $profile2standard[] = [
                            'profileid' => $id,
                            'tabid' => $tabid,
                            'operation' => 2,
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

            // Save field-level permissions
            if ($request->has('field_privileges')) {
                $fieldPrivs = json_decode($request->input('field_privileges'), true);
                if (!empty($fieldPrivs)) {
                    $profile2field = [];
                    foreach ($fieldPrivs as $tabid => $fields) {
                        foreach ($fields as $fieldid => $permission) {
                            $visible = ($permission == '0') ? 1 : 0;
                            $readonly = ($permission == '1') ? 1 : 0;

                            $profile2field[] = [
                                'profileid' => $id,
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

            // Save tool-level permissions
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
                                'profileid' => $id,
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
        });

        return redirect()->route('tenant.settings.users.profiles.index')
            ->with('success', __('tenant::users.updated_successfully'));
    }

    public function destroy($id)
    {
        // Check if assigned to roles
        $inUse = DB::connection('tenant')->table('vtiger_role2profile')->where('profileid', $id)->exists();
        if ($inUse) {
            return back()->with('error', 'Cannot delete profile in use by roles.');
        }

        try {
            DB::connection('tenant')->transaction(function () use ($id) {
                // Clean up related tables
                DB::connection('tenant')->table('vtiger_profile2tab')->where('profileid', $id)->delete();
                DB::connection('tenant')->table('vtiger_profile2standardpermissions')->where('profileid', $id)->delete();
                DB::connection('tenant')->table('vtiger_profile2utility')->where('profileid', $id)->delete();
                DB::connection('tenant')->table('vtiger_profile2field')->where('profileid', $id)->delete();

                // Finally delete the profile
                DB::connection('tenant')->table('vtiger_profile')->where('profileid', $id)->delete();
            });
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting profile: ' . $e->getMessage());
        }

        return redirect()->route('tenant.settings.users.profiles.index')
            ->with('success', __('tenant::users.deleted_successfully'));
    }

    public function duplicate($id)
    {
        $originalProfile = DB::connection('tenant')->table('vtiger_profile')->where('profileid', $id)->first();
        if (!$originalProfile) {
            abort(404);
        }

        try {
            DB::connection('tenant')->transaction(function () use ($originalProfile, $id) {
                $newProfileId = DB::connection('tenant')->table('vtiger_profile')->max('profileid') + 1;
                $newName = $originalProfile->profilename . ' - Copy';

                // Ensure unique name
                $i = 1;
                while (DB::connection('tenant')->table('vtiger_profile')->where('profilename', $newName)->exists()) {
                    $newName = $originalProfile->profilename . " - Copy ($i)";
                    $i++;
                }

                // Insert new profile
                DB::connection('tenant')->table('vtiger_profile')->insert([
                    'profileid' => $newProfileId,
                    'profilename' => $newName,
                    'description' => $originalProfile->description,
                    'directly_related_to_role' => 0, // Reset for duplicated profiles
                ]);

                // Copy vtiger_profile2tab
                DB::connection('tenant')->statement(
                    "INSERT INTO vtiger_profile2tab (profileid, tabid, permissions) 
                     SELECT ?, tabid, permissions FROM vtiger_profile2tab WHERE profileid = ?",
                    [$newProfileId, $id]
                );

                // Copy vtiger_profile2standardpermissions
                DB::connection('tenant')->statement(
                    "INSERT INTO vtiger_profile2standardpermissions (profileid, tabid, operation, permissions) 
                     SELECT ?, tabid, operation, permissions FROM vtiger_profile2standardpermissions WHERE profileid = ?",
                    [$newProfileId, $id]
                );

                // Copy vtiger_profile2utility
                DB::connection('tenant')->statement(
                    "INSERT INTO vtiger_profile2utility (profileid, tabid, activityid, permission) 
                     SELECT ?, tabid, activityid, permission FROM vtiger_profile2utility WHERE profileid = ?",
                    [$newProfileId, $id]
                );

                // Copy vtiger_profile2field
                DB::connection('tenant')->statement(
                    "INSERT INTO vtiger_profile2field (profileid, tabid, fieldid, visible, readonly) 
                     SELECT ?, tabid, fieldid, visible, readonly FROM vtiger_profile2field WHERE profileid = ?",
                    [$newProfileId, $id]
                );
            });
        } catch (\Exception $e) {
            return back()->with('error', 'Error duplicating profile: ' . $e->getMessage());
        }

        return redirect()->route('tenant.settings.users.profiles.index')
            ->with('success', 'Profile duplicated successfully.');
    }
}
