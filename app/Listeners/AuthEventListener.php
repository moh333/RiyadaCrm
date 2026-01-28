<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Log;

class AuthEventListener
{
    /**
     * Handle user login events.
     */
    public function handleLogin(Login $event): void
    {
        try {
            $user = $event->user;
            $guard = $event->guard;

            // Skip if it's not one of our recognized guards
            if (!in_array($guard, ['master', 'tenant'])) {
                return;
            }

            $ip = Request::ip();
            $userName = ($guard === 'master') ? ($user->name ?? 'Admin') : ($user->user_name ?? 'User');

            // Insert login record
            $loginId = DB::table('vtiger_loginhistory')->insertGetId([
                'user_name' => $userName,
                'user_ip' => $ip,
                'login_time' => Carbon::now(),
                'status' => 'Signed in'
            ]);

            // Store the login ID in session to update on logout
            // We use global session helper which works with the current request's session
            session(['last_login_id' => $loginId, 'last_login_guard' => $guard]);

        } catch (\Exception $e) {
            Log::error('Failed to record login history: ' . $e->getMessage());
        }
    }

    /**
     * Handle user logout events.
     */
    public function handleLogout(Logout $event): void
    {
        try {
            $guard = $event->guard;

            if (!in_array($guard, ['master', 'tenant'])) {
                return;
            }

            // Note: In Laravel 11+, the session might already be cleared or hard to access directly from the event
            // depending on how logout is called. However, we'll try to get it before it's invalidated.
            $loginId = session('last_login_id');

            if ($loginId) {
                DB::table('vtiger_loginhistory')
                    ->where('login_id', $loginId)
                    ->update([
                        'logout_time' => Carbon::now(),
                        'status' => 'Signed off'
                    ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to record logout history: ' . $e->getMessage());
        }
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     * @return void
     */
    public function subscribe($events): void
    {
        $events->listen(
            Login::class,
            [AuthEventListener::class, 'handleLogin']
        );

        $events->listen(
            Logout::class,
            [AuthEventListener::class, 'handleLogout']
        );
    }
}
