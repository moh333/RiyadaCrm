<?php

namespace App\Modules\Tenant\Presentation\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('tenant::auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'user_name' => ['required', 'string'],
            'user_password' => ['required', 'string'],
        ]);

        // Map user_password to password for Laravel's Auth
        if (
            Auth::guard('tenant')->attempt([
                'user_name' => $credentials['user_name'],
                'password' => $credentials['user_password']
            ], $request->filled('remember'))
        ) {
            $request->session()->regenerate();

            return redirect()->intended(route('tenant.dashboard'));
        }

        return back()->withErrors([
            'user_name' => __('tenant::tenant.auth_failed'),
        ])->onlyInput('user_name');
    }

    public function logout(Request $request)
    {
        Auth::guard('tenant')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('tenant.login');
    }
}
