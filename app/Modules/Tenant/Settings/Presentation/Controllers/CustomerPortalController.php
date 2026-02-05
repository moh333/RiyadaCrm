<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Settings\Presentation\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CustomerPortalController extends Controller
{
    /**
     * Display customer portal configuration
     */
    public function index(): View
    {
        $settings = \DB::connection('tenant')
            ->table('vtiger_customerportal_prefs')
            ->get()
            ->pluck('prefvalue', 'prefkey');

        $tabs = \DB::connection('tenant')
            ->table('vtiger_customerportal_tabs')
            ->join('vtiger_tab', 'vtiger_customerportal_tabs.tabid', '=', 'vtiger_tab.tabid')
            ->select(
                'vtiger_tab.tabid',
                'vtiger_tab.name',
                'vtiger_tab.tablabel',
                'vtiger_customerportal_tabs.visible',
                'vtiger_customerportal_tabs.sequence'
            )
            ->orderBy('vtiger_customerportal_tabs.sequence')
            ->get();

        $users = \DB::connection('tenant')
            ->table('vtiger_users')
            ->select('id', 'first_name', 'last_name')
            ->where('status', 'Active')
            ->get();

        return view('tenant::settings.portal.index', compact('settings', 'tabs', 'users'));
    }

    /**
     * Save customer portal configuration
     */
    public function save(Request $request): RedirectResponse
    {
        $input = $request->all();

        \DB::connection('tenant')->transaction(function () use ($input) {
            // Save General Settings
            $generalSettings = [
                'default_assignee' => $input['default_assignee'] ?? '',
                'support_notification' => $input['support_notification'] ?? '7',
                'announcement' => $input['announcement'] ?? '',
            ];

            // foreach ($generalSettings as $key => $value) {
            //     \DB::connection('tenant')
            //         ->table('vtiger_customerportal_prefs')
            //         ->updateOrInsert(['prefkey' => $key], ['prefvalue' => $value]);
            // }

            // Save Tab Settings
            if (isset($input['tabs'])) {
                foreach ($input['tabs'] as $tabId => $data) {
                    // Update visibility
                    \DB::connection('tenant')
                        ->table('vtiger_customerportal_tabs')
                        ->where('tabid', $tabId)
                        ->update(['visible' => isset($data['visible']) ? 1 : 0]);

                    // Update permissions in settings table (can_create_module, can_edit_module)
                    $tab = \DB::connection('tenant')->table('vtiger_tab')->where('tabid', $tabId)->first();
                    if ($tab) {
                        $moduleName = strtolower($tab->name);

                        // \DB::connection('tenant')
                        //     ->table('vtiger_customerportal_prefs')
                        //     ->updateOrInsert(
                        //         ['prefkey' => 'can_create_' . $moduleName],
                        //         ['prefvalue' => isset($data['create']) ? '1' : '0']
                        //     );

                        // \DB::connection('tenant')
                        //     ->table('vtiger_customerportal_prefs')
                        //     ->updateOrInsert(
                        //         ['prefkey' => 'can_edit_' . $moduleName],
                        //         ['prefvalue' => isset($data['edit']) ? '1' : '0']
                        //     );
                    }
                }
            }
        });

        return back()->with('success', __('tenant::settings.portal_settings_saved'));
    }

    /**
     * Get available modules for portal configuration (AJAX)
     */
    public function getModules(): JsonResponse
    {
        $modules = \DB::connection('tenant')
            ->table('vtiger_customerportal_tabs')
            ->join('vtiger_tab', 'vtiger_customerportal_tabs.tabid', '=', 'vtiger_tab.tabid')
            ->select('vtiger_tab.tabid', 'vtiger_tab.name', 'vtiger_tab.tablabel', 'vtiger_customerportal_tabs.visible', 'vtiger_customerportal_tabs.sequence')
            ->orderBy('vtiger_customerportal_tabs.sequence')
            ->get();

        return response()->json([
            'modules' => $modules
        ]);
    }
}
