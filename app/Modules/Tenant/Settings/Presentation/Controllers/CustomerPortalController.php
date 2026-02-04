<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Settings\Presentation\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class CustomerPortalController extends Controller
{
    /**
     * Display customer portal configuration
     */
    public function index(): View
    {
        return view('tenant::settings.portal.index');
    }

    /**
     * Save customer portal configuration
     */
    public function save(Request $request): JsonResponse
    {
        // TODO: Implement save logic
        return response()->json([
            'success' => true,
            'message' => __('tenant::settings.portal_settings_saved')
        ]);
    }

    /**
     * Get available modules for portal configuration (AJAX)
     */
    public function getModules(): JsonResponse
    {
        // TODO: Implement get modules logic
        return response()->json([
            'modules' => []
        ]);
    }
}
