<?php

declare(strict_types=1);

use App\Http\Middleware\InitializeTenancyOrRedirect;
use App\Modules\Tenant\Presentation\Controllers\DashboardController;
use App\Modules\Tenant\Presentation\Controllers\LoginController;
use App\Modules\Tenant\Presentation\Controllers\ProfileController;
use App\Modules\Tenant\Presentation\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    InitializeTenancyOrRedirect::class,
    PreventAccessFromCentralDomains::class,
])->name('tenant.')->group(function () {

    // Auth Routes
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/impersonate/{token}', function ($token) {
        return \Stancl\Tenancy\Features\UserImpersonation::makeResponse($token);
    })->name('impersonate');

    // Protected Routes
    Route::middleware('auth:tenant')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Profile Routes
        Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
        Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

        // Settings Routes
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
        Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
    });

});
