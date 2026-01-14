<?php

declare(strict_types=1);

use App\Http\Middleware\InitializeTenancyOrRedirect;
use App\Modules\Tenant\Presentation\Controllers\DashboardController;
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
])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
});
