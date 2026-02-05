<?php

namespace App\Modules\Tenant\Settings\Presentation\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class TaxController
{
    /**
     * Display tax management page
     */
    public function index(): View
    {
        return view('tenant::settings.tax.index');
    }

    /**
     * Get tax data for DataTables (AJAX)
     */
    public function data(Request $request): JsonResponse
    {
        $type = $request->get('type', 'product');
        $table = ($type === 'shipping') ? 'vtiger_shippingtaxinfo' : 'vtiger_inventorytaxinfo';

        $taxes = \DB::connection('tenant')
            ->table($table)
            ->where('deleted', 0)
            ->get();

        return response()->json([
            'draw' => $request->get('draw'),
            'recordsTotal' => $taxes->count(),
            'recordsFiltered' => $taxes->count(),
            'data' => $taxes
        ]);
    }

    /**
     * Show create tax form
     */
    public function create(Request $request): View
    {
        $type = $request->get('type', 'product'); // product or shipping

        return view('tenant::settings.tax.edit', [
            'tax' => null,
            'type' => $type
        ]);
    }

    /**
     * Store new tax
     */
    public function store(Request $request): RedirectResponse
    {
        $type = $request->get('type', 'product');
        $table = ($type === 'shipping') ? 'vtiger_shippingtaxinfo' : 'vtiger_inventorytaxinfo';

        $validated = $request->validate([
            'taxlabel' => 'required|string|max:255',
            'percentage' => 'required|numeric|min:0',
        ]);

        // Check duplicate label in active taxes
        $exists = \DB::connection('tenant')
            ->table($table)
            ->where('taxlabel', $validated['taxlabel'])
            ->where('deleted', 0)
            ->exists();

        if ($exists) {
            return back()->withErrors(['taxlabel' => __('tenant::settings.duplicate_tax_label')]);
        }

        // Generate a tax name (internal)
        $taxName = 'tax' . time();

        \DB::connection('tenant')->transaction(function () use ($table, $validated, $taxName) {
            \DB::connection('tenant')
                ->table($table)
                ->insert(array_merge($validated, [
                    'taxname' => $taxName,
                    'deleted' => 0
                ]));

            // Note: In real Vtiger, adding a tax also adds columns to vtiger_inventoryproductrel
            // and fields to vtiger_field. We skip that for now unless requested.
        });

        return redirect()->route('tenant.settings.crm.tax.index')
            ->with('success', __('tenant::settings.tax_created_successfully'));
    }

    /**
     * Show edit tax form
     */
    public function edit(int $id, Request $request): View
    {
        $type = $request->get('type', 'product');
        $table = ($type === 'shipping') ? 'vtiger_shippingtaxinfo' : 'vtiger_inventorytaxinfo';

        $tax = \DB::connection('tenant')
            ->table($table)
            ->where('taxid', $id)
            ->first();

        return view('tenant::settings.tax.edit', [
            'tax' => $tax,
            'type' => $type
        ]);
    }

    /**
     * Update tax
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $type = $request->get('type', 'product');
        $table = ($type === 'shipping') ? 'vtiger_shippingtaxinfo' : 'vtiger_inventorytaxinfo';

        $validated = $request->validate([
            'taxlabel' => 'required|string|max:255',
            'percentage' => 'required|numeric|min:0',
        ]);

        \DB::connection('tenant')
            ->table($table)
            ->where('taxid', $id)
            ->update($validated);

        return redirect()->route('tenant.settings.crm.tax.index')
            ->with('success', __('tenant::settings.tax_updated_successfully'));
    }

    /**
     * Delete tax (soft delete)
     */
    public function destroy(int $id, Request $request): JsonResponse
    {
        $type = $request->get('type', 'product');
        $table = ($type === 'shipping') ? 'vtiger_shippingtaxinfo' : 'vtiger_inventorytaxinfo';

        \DB::connection('tenant')
            ->table($table)
            ->where('taxid', $id)
            ->update(['deleted' => 1]);

        return response()->json([
            'success' => true,
            'message' => __('tenant::settings.tax_deleted_successfully')
        ]);
    }

    /**
     * Check duplicate tax label
     */
    public function checkDuplicate(Request $request): JsonResponse
    {
        $label = $request->get('label');
        $excludeId = $request->get('exclude_id');
        $type = $request->get('type', 'product');
        $table = ($type === 'shipping') ? 'vtiger_shippingtaxinfo' : 'vtiger_inventorytaxinfo';

        $query = \DB::connection('tenant')
            ->table($table)
            ->where('taxlabel', $label)
            ->where('deleted', 0);

        if ($excludeId) {
            $query->where('taxid', '!=', $excludeId);
        }

        return response()->json([
            'exists' => $query->exists()
        ]);
    }
}
