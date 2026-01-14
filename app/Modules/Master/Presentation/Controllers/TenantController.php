<?php

namespace App\Modules\Master\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Master\Application\UseCases\GetAllTenants;
use App\Modules\Master\Application\UseCases\CreateTenant;
use App\Modules\Master\Application\UseCases\DeleteTenant;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function index(GetAllTenants $getAllTenants)
    {
        $tenants = $getAllTenants->execute();
        return view('master::tenants.index', ['tenants' => $tenants]);
    }

    public function create()
    {
        return view('master::tenants.create');
    }

    public function store(Request $request, CreateTenant $createTenant)
    {
        set_time_limit(600); // Increase execution time to preventing timeout

        $request->validate([
            'id' => 'required|alpha_dash|unique:tenants,id',
            'domain' => 'required|string|unique:domains,domain',
        ]);

        try {
            $createTenant->execute($request->id, $request->domain);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tenant created successfully!',
                    'redirect' => route('master.tenants.index')
                ]);
            }

            return redirect()->route('master.tenants.index')->with('success', 'Tenant created successfully.');

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating tenant: ' . $e->getMessage()
                ], 500);
            }
            return back()->withErrors(['domain' => $e->getMessage()])->withInput();
        }
    }

    public function destroy(string $id, DeleteTenant $deleteTenant)
    {
        try {
            $deleteTenant->execute($id);
            return redirect()->back()->with('success', 'Tenant deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error deleting tenant: ' . $e->getMessage());
        }
    }
}
