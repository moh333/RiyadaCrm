<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Settings\Presentation\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class CurrencyController extends Controller
{
    /**
     * Display list of currencies
     */
    public function index(): View
    {
        $totalCurrencies = \DB::connection('tenant')
            ->table('vtiger_currency_info')
            ->where('deleted', 0)
            ->count();

        $activeCurrencies = \DB::connection('tenant')
            ->table('vtiger_currency_info')
            ->where('deleted', 0)
            ->where('currency_status', 'Active')
            ->count();

        $baseCurrency = \DB::connection('tenant')
            ->table('vtiger_currency_info')
            ->where('deleted', 0)
            ->where('conversion_rate', 1.0)
            ->first();

        return view('tenant::settings.currency.index', compact('totalCurrencies', 'activeCurrencies', 'baseCurrency'));
    }

    /**
     * Get currencies data for DataTables (AJAX)
     */
    public function data(Request $request)
    {
        $currencies = \DB::connection('tenant')
            ->table('vtiger_currency_info')
            ->where('deleted', 0);

        return \Yajra\DataTables\DataTables::of($currencies)
            ->addColumn('action', function ($row) {
                return '';
            })
            ->make(true);
    }

    /**
     * Show form for creating new currency
     */
    public function create(): View
    {
        $allCurrencies = \DB::connection('tenant')
            ->table('vtiger_currencies')
            ->get();

        return view('tenant::settings.currency.edit', [
            'currency' => null,
            'allCurrencies' => $allCurrencies
        ]);
    }

    /**
     * Store a newly created currency
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'currency_name' => 'required|string|max:200',
            'currency_code' => 'required|string|max:100',
            'currency_symbol' => 'required|string|max:30',
            'conversion_rate' => 'required|numeric',
            'currency_status' => 'required|string|max:25',
        ]);

        \DB::connection('tenant')
            ->table('vtiger_currency_info')
            ->insert(array_merge($validated, ['deleted' => 0]));

        return redirect()->route('tenant.settings.crm.currency.index')
            ->with('success', __('tenant::settings.currency_created_successfully'));
    }

    /**
     * Show form for editing currency
     */
    public function edit(int $id): View
    {
        $currency = \DB::connection('tenant')
            ->table('vtiger_currency_info')
            ->where('id', $id)
            ->first();

        $allCurrencies = \DB::connection('tenant')
            ->table('vtiger_currencies')
            ->get();

        return view('tenant::settings.currency.edit', [
            'currency' => $currency,
            'allCurrencies' => $allCurrencies
        ]);
    }

    /**
     * Update the specified currency
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'currency_name' => 'required|string|max:200',
            'currency_code' => 'required|string|max:100',
            'currency_symbol' => 'required|string|max:30',
            'conversion_rate' => 'required|numeric',
            'currency_status' => 'required|string|max:25',
        ]);

        \DB::connection('tenant')
            ->table('vtiger_currency_info')
            ->where('id', $id)
            ->update($validated);

        return redirect()->route('tenant.settings.crm.currency.index')
            ->with('success', __('tenant::settings.currency_updated_successfully'));
    }

    /**
     * Remove the specified currency
     */
    public function destroy(int $id): JsonResponse
    {
        \DB::connection('tenant')
            ->table('vtiger_currency_info')
            ->where('id', $id)
            ->update(['deleted' => 1]);

        return response()->json([
            'success' => true,
            'message' => __('tenant::settings.currency_deleted_successfully')
        ]);
    }
}
