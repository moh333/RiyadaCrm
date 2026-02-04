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

        // TODO: Load terms for each module

        return view('tenant::settings.terms.index', [
            'modules' => $modules
        ]);
    }

    /**
     * Show edit form for specific module
     */
    public function edit(string $module): View
    {
        // TODO: Load terms for module

        return view('tenant::settings.terms.edit', [
            'module' => $module,
            'terms' => null // TODO: Load from database
        ]);
    }

    /**
     * Save terms and conditions
     */
    public function save(Request $request): RedirectResponse
    {
        $module = $request->get('module');
        $terms = $request->get('terms');

        // TODO: Save or update terms
        // - Check if exists
        // - Insert or update

        return redirect()->route('tenant.settings.crm.terms.index')
            ->with('success', 'Terms and conditions saved successfully');
    }
}
