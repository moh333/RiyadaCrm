<?php

namespace App\Modules\Tenant\Presentation\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::guard('tenant')->user();
        return view('tenant::profile.index', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::guard('tenant')->user();

        $request->validate([
            'first_name' => 'required|string|max:30',
            'last_name' => 'required|string|max:30',
            'email1' => 'required|email|max:100',
            'phone_mobile' => 'nullable|string|max:50',
            'title' => 'nullable|string|max:50',
            'department' => 'nullable|string|max:50',
        ]);

        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email1' => $request->email1,
            'phone_mobile' => $request->phone_mobile,
            'title' => $request->title,
            'department' => $request->department,
        ]);

        return back()->with('success', __('tenant::tenant.profile_updated'));
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::guard('tenant')->user();

        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->update([
            'user_password' => Hash::make($request->password),
        ]);

        return back()->with('success', __('tenant::tenant.password_updated'));
    }
}
