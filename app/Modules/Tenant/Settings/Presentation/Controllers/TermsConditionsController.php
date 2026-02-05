<?php

namespace App\Modules\Tenant\Settings\Presentation\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TermsConditionsController
{
    /**
     * Display terms and conditions management
     */
    public function index(): View
    {
        // Get all inventory modules
        $modules = ['Quotes', 'SalesOrder', 'PurchaseOrder', 'Invoice'];

        // Load terms for each module
        $termsMap = \DB::connection('tenant')
            ->table('vtiger_inventory_termsandconditions')
            ->whereIn('module', $modules)
            ->get()
            ->pluck('terms', 'module');

        return view('tenant::settings.terms.index', [
            'modules' => $modules,
            'termsMap' => $termsMap
        ]);
    }

    /**
     * Show edit form for specific module
     */
    public function edit(string $module): View
    {
        // Load terms for module
        $terms = \DB::connection('tenant')
            ->table('vtiger_inventory_termsandconditions')
            ->where('module', $module)
            ->first();

        return view('tenant::settings.terms.edit', [
            'module' => $module,
            'terms' => $terms ? $terms->terms : ''
        ]);
    }

    /**
     * Save terms and conditions
     */
    public function save(Request $request): RedirectResponse
    {
        $module = $request->get('module');
        $terms = $request->get('terms');

        \DB::connection('tenant')
            ->table('vtiger_inventory_termsandconditions')
            ->updateOrInsert(
                ['module' => $module],
                ['terms' => $terms, 'updated_at' => now()]
            );

        return redirect()->route('tenant.settings.crm.terms.index')
            ->with('success', __('tenant::settings.terms_saved_successfully'));
    }
}
