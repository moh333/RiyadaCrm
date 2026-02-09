<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);

        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('admin') || $request->is('admin/*')) {
                return route('master.login');
            }

            // If it's a tenant context (not central domain)
            if (app()->bound(\Stancl\Tenancy\Contracts\Tenant::class)) {
                return route('tenant.login');
            }

            return route('login');
        });

        $middleware->redirectUsersTo(function (Request $request) {
            if (Auth::guard('master')->check()) {
                return route('master.dashboard');
            }
            return '/';
        });

        // Register custom middleware aliases
        $middleware->alias([
            'permission.module' => \App\Http\Middleware\CheckModulePermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
