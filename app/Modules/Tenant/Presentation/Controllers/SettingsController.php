<?php

namespace App\Modules\Tenant\Presentation\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        return view('tenant::settings.index');
    }

    public function update(Request $request)
    {
        // For now, this is a placeholder for tenant-wide settings
        // which might be stored in a 'vtiger_settings' table or similar.

        return back()->with('success', __('tenant::tenant.settings_saved'));
    }
}
