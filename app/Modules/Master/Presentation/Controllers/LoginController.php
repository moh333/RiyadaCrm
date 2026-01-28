<?php

namespace App\Modules\Master\Presentation\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('master::auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('master')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended(route('master.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        // Record Logout History before session is invalidated
        $loginId = $request->session()->get('last_login_id');
        if ($loginId) {
            DB::table('vtiger_loginhistory')
                ->where('login_id', $loginId)
                ->update([
                    'logout_time' => Carbon::now(),
                    'status' => 'Signed off'
                ]);
        }

        Auth::guard('master')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('master.login');
    }
}
