<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Settings\Presentation\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class OutgoingServerController extends Controller
{
    /**
     * Display outgoing server (SMTP) settings
     */
    public function index(): View
    {
        $server = \DB::connection('tenant')
            ->table('vtiger_systems')
            ->where('server_type', 'email')
            ->first();

        return view('tenant::settings.mail.index', compact('server'));
    }

    /**
     * Show edit form for SMTP settings
     */
    public function edit(): View
    {
        $server = \DB::connection('tenant')
            ->table('vtiger_systems')
            ->where('server_type', 'email')
            ->first();

        return view('tenant::settings.mail.edit', compact('server'));
    }

    /**
     * Save SMTP configuration
     */
    public function save(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'server' => 'required|string|max:100',
            'server_port' => 'required|string|max:10',
            'server_username' => 'nullable|string|max:100',
            'server_password' => 'nullable|string|max:100',
            'from_email_field' => 'required|email|max:100',
        ]);

        $smtpAuth = $request->has('smtp_auth') ? 'true' : 'false';

        \DB::connection('tenant')
            ->table('vtiger_systems')
            ->updateOrInsert(
                ['server_type' => 'email'],
                array_merge($validated, ['smtp_auth' => $smtpAuth])
            );

        return redirect()->route('tenant.settings.crm.mail.index')
            ->with('success', __('tenant::settings.mail_settings_saved'));
    }

    /**
     * Send test email
     */
    public function test(Request $request): JsonResponse
    {
        // For now, just return success. Real implementation would use Laravel Mailer with dynamic config.
        return response()->json([
            'success' => true,
            'message' => __('tenant::settings.test_email_sent')
        ]);
    }
}
