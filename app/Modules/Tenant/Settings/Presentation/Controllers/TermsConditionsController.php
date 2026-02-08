<?php

namespace App\Modules\Tenant\Settings\Presentation\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TermsConditionsController
{
    public function __construct(
        private \App\Modules\Core\VtigerModules\Contracts\ModuleRegistryInterface $moduleRegistry
    ) {
    }

    /**
     * Display terms and conditions management
     */
    public function index(): View
    {
        // Get all active modules with their localized labels
        $modules = $this->moduleRegistry->getActive()
            ->map(fn($m) => [
                'name' => $m->getName(),
                'label' => $m->getLabel()
            ])->values();

        $moduleNames = $modules->pluck('name')->toArray();

        // Load terms for each module
        $termsMap = \DB::connection('tenant')
            ->table('vtiger_inventory_termsandconditions')
            ->whereIn('module_name', $moduleNames)
            ->get()
            ->keyBy('module_name');

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
            ->where('module_name', $module)
            ->first();

        return view('tenant::settings.terms.edit', [
            'module_name' => $module,
            'terms' => $terms
        ]);
    }

    /**
     * Save terms and conditions
     */
    public function save(Request $request): RedirectResponse
    {
        $module = $request->get('module_name');
        $terms_en = $request->get('terms_en');
        $terms_ar = $request->get('terms_ar');

        \DB::connection('tenant')
            ->table('vtiger_inventory_termsandconditions')
            ->updateOrInsert(
                ['module_name' => $module],
                [
                    'terms_en' => $terms_en,
                    'terms_ar' => $terms_ar,
                    'is_default' => 1,
                    'status' => 1,
                    'updated_at' => now()
                ]
            );

        return redirect()->route('tenant.settings.crm.terms.index')
            ->with('success', __('tenant::settings.terms_saved_successfully'));
    }
}
