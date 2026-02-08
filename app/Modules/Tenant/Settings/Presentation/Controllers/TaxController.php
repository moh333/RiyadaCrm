<?php

namespace App\Modules\Tenant\Settings\Presentation\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class TaxController
{
    /**
     * Display tax management page
     */
    public function index(): RedirectResponse
    {
        return redirect()->route('tenant.settings.crm.tax.taxes');
    }

    public function taxes(): View
    {
        return view('tenant::settings.tax.index', ['activeTab' => 'taxes']);
    }

    public function charges(): View
    {
        return view('tenant::settings.tax.index', ['activeTab' => 'charges']);
    }

    public function regions(): View
    {
        return view('tenant::settings.tax.index', ['activeTab' => 'regions']);
    }

    /**
     * Get tax data for DataTables (AJAX)
     */
    public function data(Request $request): JsonResponse
    {
        $type = $request->get('type', 'product');
        $table = ($type === 'shipping') ? 'vtiger_shippingtaxinfo' : 'vtiger_inventorytaxinfo';

        $taxes = DB::connection('tenant')
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
     * Get charges data
     */
    public function chargesData(Request $request): JsonResponse
    {
        $charges = DB::connection('tenant')
            ->table('vtiger_inventorycharges')
            ->where('deleted', 0)
            ->get();

        return response()->json([
            'draw' => $request->get('draw'),
            'recordsTotal' => $charges->count(),
            'recordsFiltered' => $charges->count(),
            'data' => $charges
        ]);
    }

    /**
     * Get regions data
     */
    public function regionsData(Request $request): JsonResponse
    {
        $regions = DB::connection('tenant')
            ->table('vtiger_taxregions')
            ->get();

        return response()->json([
            'draw' => $request->get('draw'),
            'recordsTotal' => $regions->count(),
            'recordsFiltered' => $regions->count(),
            'data' => $regions
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
            'percentage' => 'nullable|numeric|min:0',
            'method' => 'required|string',
            'tax_type' => 'required|string',
            'compoundon' => 'nullable|array',
            'regions' => 'nullable|string',
        ]);

        $exists = DB::connection('tenant')
            ->table($table)
            ->where('taxlabel', $validated['taxlabel'])
            ->where('deleted', 0)
            ->exists();

        if ($exists) {
            return back()->with('error', __('tenant::settings.duplicate_tax_label'))->withInput();
        }

        $taxName = ($type === 'shipping' ? 'shtax' : 'tax') . time();
        $maxId = DB::connection('tenant')->table($table)->max('taxid') ?? 1;

        DB::connection('tenant')->table($table)->insert([
            'taxid' => $maxId + 1,
            'taxname' => $taxName,
            'taxlabel' => $validated['taxlabel'],
            'percentage' => $validated['percentage'] ?? 0,
            'method' => $validated['method'],
            'type' => $validated['tax_type'],
            'compoundon' => isset($validated['compoundon']) ? implode(',', $validated['compoundon']) : null,
            'regions' => $validated['regions'],
            'deleted' => 0
        ]);

        $redirectRoute = ($type === 'shipping') ? 'tenant.settings.crm.tax.charges' : 'tenant.settings.crm.tax.taxes';
        return redirect()->route($redirectRoute)->with('success', __('tenant::settings.tax_created_successfully'));
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
            'percentage' => 'nullable|numeric|min:0',
            'method' => 'required|string',
            'tax_type' => 'required|string',
            'compoundon' => 'nullable|array',
            'regions' => 'nullable|string',
            'status' => 'nullable|integer'
        ]);

        DB::connection('tenant')
            ->table($table)
            ->where('taxid', $id)
            ->update([
                'taxlabel' => $validated['taxlabel'],
                'percentage' => $validated['percentage'] ?? 0,
                'method' => $validated['method'],
                'type' => $validated['tax_type'],
                'compoundon' => isset($validated['compoundon']) ? implode(',', $validated['compoundon']) : null,
                'regions' => $validated['regions'],
                'deleted' => ($request->has('status') && $request->status == 1) ? 0 : 1
            ]);

        $redirectRoute = ($type === 'shipping') ? 'tenant.settings.crm.tax.charges' : 'tenant.settings.crm.tax.taxes';
        return redirect()->route($redirectRoute)->with('success', __('tenant::settings.tax_updated_successfully'));
    }

    /**
     * Delete tax (soft delete)
     */
    public function destroy(int $id, Request $request): JsonResponse
    {
        $type = $request->get('type', 'product');
        $table = ($type === 'shipping') ? 'vtiger_shippingtaxinfo' : 'vtiger_inventorytaxinfo';

        DB::connection('tenant')
            ->table($table)
            ->where('taxid', $id)
            ->update(['deleted' => 1]);

        return response()->json(['success' => true, 'message' => __('tenant::settings.tax_deleted_successfully')]);
    }

    /**
     * Charge actions
     */
    public function storeCharge(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'format' => 'required|string|max:10',
            'type' => 'required|string|max:10',
            'value' => 'nullable|numeric',
            'regions' => 'nullable|string',
            'taxes' => 'nullable|array',
            'istaxable' => 'nullable|boolean'
        ]);

        DB::connection('tenant')->table('vtiger_inventorycharges')->insert([
            'name' => $validated['name'],
            'format' => $validated['format'],
            'type' => $validated['type'],
            'value' => $validated['value'] ?? 0,
            'regions' => $validated['regions'],
            'taxes' => isset($validated['taxes']) ? implode(',', $validated['taxes']) : null,
            'istaxable' => $request->has('istaxable') ? 1 : 0,
            'deleted' => 0
        ]);

        return redirect()->route('tenant.settings.crm.tax.charges')->with('success', __('tenant::settings.charge_created_successfully'));
    }

    public function updateCharge(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'format' => 'required|string|max:10',
            'type' => 'required|string|max:10',
            'value' => 'nullable|numeric',
            'regions' => 'nullable|string',
            'taxes' => 'nullable|array',
            'istaxable' => 'nullable|boolean'
        ]);

        DB::connection('tenant')->table('vtiger_inventorycharges')->where('chargeid', $id)->update([
            'name' => $validated['name'],
            'format' => $validated['format'],
            'type' => $validated['type'],
            'value' => $validated['value'] ?? 0,
            'regions' => $validated['regions'],
            'taxes' => isset($validated['taxes']) ? implode(',', $validated['taxes']) : null,
            'istaxable' => $request->has('istaxable') ? 1 : 0
        ]);

        return redirect()->route('tenant.settings.crm.tax.charges')->with('success', __('tenant::settings.charge_updated_successfully'));
    }

    public function destroyCharge(int $id): JsonResponse
    {
        DB::connection('tenant')->table('vtiger_inventorycharges')->where('chargeid', $id)->update(['deleted' => 1]);
        return response()->json(['success' => true, 'message' => __('tenant::settings.charge_deleted_successfully')]);
    }

    /**
     * Region actions
     */
    public function storeRegion(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100'
        ]);

        DB::connection('tenant')->table('vtiger_taxregions')->insert($validated);

        return redirect()->route('tenant.settings.crm.tax.regions')->with('success', __('tenant::settings.region_created_successfully'));
    }

    public function updateRegion(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100'
        ]);

        DB::connection('tenant')->table('vtiger_taxregions')->where('regionid', $id)->update($validated);

        return redirect()->route('tenant.settings.crm.tax.regions')->with('success', __('tenant::settings.region_updated_successfully'));
    }

    public function destroyRegion(int $id): JsonResponse
    {
        DB::connection('tenant')->table('vtiger_taxregions')->where('regionid', $id)->delete();
        return response()->json(['success' => true, 'message' => __('tenant::settings.region_deleted_successfully')]);
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

        $query = DB::connection('tenant')
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
