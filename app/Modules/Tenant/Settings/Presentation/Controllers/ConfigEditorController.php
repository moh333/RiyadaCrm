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
        return view('tenant::settings.config.index');
    }

    /**
     * Show edit form for configuration
     */
    public function edit(): View
    {
        return view('tenant::settings.config.edit');
    }

    /**
     * Save configuration changes
     */
    public function save(Request $request): RedirectResponse
    {
        // TODO: Implement save logic
        return redirect()->route('tenant.settings.crm.config.index')
            ->with('success', __('tenant::settings.config_saved_successfully'));
    }
}
