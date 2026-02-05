<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Settings\Presentation\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ConfigEditorController extends Controller
{
    /**
     * Display configuration values
     */
    public function index(): View
    {
        $config = \DB::connection('tenant')
            ->table('vtiger_configuration_editor')
            ->get()
            ->pluck('value', 'key');

        return view('tenant::settings.config.index', compact('config'));
    }

    /**
     * Show edit form for configuration
     */
    public function edit(): View
    {
        $config = \DB::connection('tenant')
            ->table('vtiger_configuration_editor')
            ->get()
            ->pluck('value', 'key');

        return view('tenant::settings.config.edit', compact('config'));
    }

    /**
     * Save configuration changes
     */
    public function save(Request $request): RedirectResponse
    {
        $settings = $request->except(['_token']);

        \DB::connection('tenant')->transaction(function () use ($settings) {
            foreach ($settings as $key => $value) {
                \DB::connection('tenant')
                    ->table('vtiger_configuration_editor')
                    ->where('key', $key)
                    ->update([
                        'value' => $value,
                        'updated_at' => now()
                    ]);
            }
        });

        return redirect()->route('tenant.settings.crm.config.index')
            ->with('success', __('tenant::settings.config_saved_successfully'));
    }
}
