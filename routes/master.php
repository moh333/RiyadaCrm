<?php

use App\Modules\Master\Presentation\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Master Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the RouteServiceProvider within a group which
| contains the "web" and "auth:master" middleware groups.
|
*/

use App\Modules\Master\Presentation\Controllers\LoginController;
use App\Modules\Master\Presentation\Controllers\TenantController;
use App\Modules\Master\Presentation\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Master Routes
|--------------------------------------------------------------------------
|
*/

Route::prefix('admin')->name('master.')->group(function () {

    // Auth Routes
    Route::middleware('guest:master')->group(function () {
        Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('login', [LoginController::class, 'login'])->name('login.submit');
    });
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');

    // Protected Routes
    Route::middleware('auth:master')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Profile Management
        Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');
        Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

        // Tenant Management
        Route::get('tenants', [TenantController::class, 'index'])->name('tenants.index');
        Route::get('tenants/create', [TenantController::class, 'create'])->name('tenants.create');
        Route::post('tenants', [TenantController::class, 'store'])->name('tenants.store');
        Route::get('tenants/{id}', [TenantController::class, 'show'])->name('tenants.show');
        Route::get('tenants/{id}/edit', [TenantController::class, 'edit'])->name('tenants.edit');
        Route::put('tenants/{id}', [TenantController::class, 'update'])->name('tenants.update');
        Route::get('tenants/{id}/impersonate', [TenantController::class, 'impersonate'])->name('tenants.impersonate');
        Route::delete('tenants/{id}', [TenantController::class, 'destroy'])->name('tenants.destroy');
    });
});

