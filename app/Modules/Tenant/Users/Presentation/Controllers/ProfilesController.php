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
        return view('tenant::profiles.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'profilename' => 'required|string|max:200|unique:tenant.vtiger_profile,profilename',
            'description' => 'nullable|string',
        ]);

        $profileId = DB::connection('tenant')->table('vtiger_profile')->max('profileid') + 1;

        DB::connection('tenant')->table('vtiger_profile')->insert([
            'profileid' => $profileId,
            'profilename' => $validated['profilename'],
            'description' => $validated['description'],
        ]);

        // Init default global permissions if needed
        // For now, empty profile.

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

        // Fetch permissions (Example: Standard permissions for modules)
        // This part would be expanded to show a matrix

        return view('tenant::profiles.edit', compact('profile'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'profilename' => 'required|string|max:200',
            'description' => 'nullable|string',
        ]);

        DB::connection('tenant')->table('vtiger_profile')
            ->where('profileid', $id)
            ->update([
                'profilename' => $validated['profilename'],
                'description' => $validated['description'],
            ]);

        return redirect()->route('tenant.settings.users.profiles.index')
            ->with('success', __('tenant::users.updated_successfully'));
    }

    public function destroy($id)
    {
        // Check if assigned to roles
        $inUse = DB::connection('tenant')->table('vtiger_role2profile')->where('profileid', $id)->exists();
        if ($inUse) {
            return back()->withErrors(['error' => 'Cannot delete profile in use by roles.']);
        }

        DB::connection('tenant')->table('vtiger_profile')->where('profileid', $id)->delete();

        return redirect()->route('tenant.settings.users.profiles.index')
            ->with('success', __('tenant::users.deleted_successfully'));
    }
}
