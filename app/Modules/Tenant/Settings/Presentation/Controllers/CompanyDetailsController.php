<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Settings\Presentation\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class CompanyDetailsController extends Controller
{
    /**
     * Display company details
     */
    public function index(): View
    {
        $organization = \DB::connection('tenant')
            ->table('vtiger_organizationdetails')
            ->first();

        return view('tenant::settings.company.index', compact('organization'));
    }

    /**
     * Show edit form for company details
     */
    public function edit(): View
    {
        $organization = \DB::connection('tenant')
            ->table('vtiger_organizationdetails')
            ->first();

        return view('tenant::settings.company.edit', compact('organization'));
    }

    /**
     * Update company details
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'organizationname' => 'required|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:200',
            'state' => 'nullable|string|max:200',
            'code' => 'nullable|string|max:200',
            'country' => 'nullable|string|max:200',
            'phone' => 'nullable|string|max:100',
            'fax' => 'nullable|string|max:100',
            'website' => 'nullable|string|max:255',
            'vatid' => 'nullable|string|max:100',
            'logo' => 'nullable|image|max:2048',
        ]);

        $data = \Arr::except($validated, ['logo']);

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('logos', $filename, 'public');

            $data['logoname'] = $filename;
            $data['logo'] = $path;
        }

        \DB::connection('tenant')
            ->table('vtiger_organizationdetails')
            ->update($data);

        return redirect()->route('tenant.settings.crm.company.index')
            ->with('success', __('tenant::settings.company_updated_successfully'));
    }

    /**
     * Upload company logo
     */
    public function uploadLogo(Request $request): JsonResponse
    {
        if (!$request->hasFile('logo')) {
            return response()->json(['success' => false, 'message' => 'No logo file provided'], 400);
        }

        $file = $request->file('logo');
        $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();

        // Store logo. Assuming a storage setup for tenants.
        // For now, let's just simulate the DB update with a path.
        $path = $file->storeAs('logos', $filename, 'public');

        \DB::connection('tenant')
            ->table('vtiger_organizationdetails')
            ->update(['logoname' => $filename, 'logo' => $path]);

        return response()->json([
            'success' => true,
            'message' => __('tenant::settings.logo_uploaded_successfully'),
            'logo_url' => tenant_asset($path)
        ]);
    }
}
