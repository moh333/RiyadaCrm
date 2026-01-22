<?php

declare(strict_types=1);

use App\Http\Middleware\InitializeTenancyOrRedirect;
use App\Modules\Tenant\Contacts\Presentation\Controllers\ContactsController;
use App\Modules\Tenant\Contacts\Presentation\Controllers\CustomFieldsController;
use App\Modules\Tenant\Presentation\Controllers\DashboardController;
use App\Modules\Tenant\Presentation\Controllers\LoginController;
use App\Modules\Tenant\Presentation\Controllers\ProfileController;
use App\Modules\Tenant\Presentation\Controllers\SettingsController;
use App\Modules\Tenant\Settings\Presentation\Controllers\ModuleManagementController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Features\UserImpersonation;
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
        return UserImpersonation::makeResponse($token);
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

        // Contacts Routes
        Route::prefix('contacts')->name('contacts.')->group(function () {
            Route::get('/', [ContactsController::class, 'index'])->name('index');
            Route::get('/data', [ContactsController::class, 'data'])->name('data');
            Route::get('/create', [ContactsController::class, 'create'])->name('create');
            Route::post('/', [ContactsController::class, 'store'])->name('store');

            // Contact CRUD routes with {id} parameter
            Route::get('/{id}', [ContactsController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [ContactsController::class, 'edit'])->name('edit');
            Route::put('/{id}', [ContactsController::class, 'update'])->name('update');
            Route::delete('/{id}', [ContactsController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/delete-file', [ContactsController::class, 'deleteFile'])->name('delete-file');
        });

        // Custom Fields Management (Generic for all modules)
        Route::prefix('settings/custom-fields')->name('custom-fields.')->group(function () {
            Route::get('/{module}', [CustomFieldsController::class, 'index'])->name('index');
            Route::get('/{module}/create', [CustomFieldsController::class, 'create'])->name('create');
            Route::post('/{module}', [CustomFieldsController::class, 'store'])->name('store');
            Route::get('/{module}/{id}/edit', [CustomFieldsController::class, 'edit'])->name('edit');
            Route::put('/{module}/{id}', [CustomFieldsController::class, 'update'])->name('update');
            Route::delete('/{module}/{id}', [CustomFieldsController::class, 'destroy'])->name('destroy');
            Route::post('/{module}/bulk-destroy', [CustomFieldsController::class, 'bulkDestroy'])->name('bulk-destroy');
        });

        // Module Management
        Route::prefix('settings/modules')->name('settings.modules.')->group(function () {
            Route::get('/', [ModuleManagementController::class, 'index'])->name('index');
            Route::get('/list', [ModuleManagementController::class, 'listModules'])->name('list');

            // Layout Management
            Route::get('/layouts', [ModuleManagementController::class, 'layouts'])->name('layouts');
            Route::get('/{module}/layout', [ModuleManagementController::class, 'editLayout'])->name('layout');
            Route::post('/{module}/layout', [ModuleManagementController::class, 'updateLayout'])->name('layout.update');
            Route::post('/{module}/layout/reorder', [ModuleManagementController::class, 'updateFieldOrder'])->name('layout.reorder');
            Route::post('/{module}/block', [ModuleManagementController::class, 'addBlock'])->name('block.add');
            Route::put('/{module}/block/{blockId}', [ModuleManagementController::class, 'updateBlock'])->name('block.update');
            Route::delete('/{module}/block/{blockId}', [ModuleManagementController::class, 'deleteBlock'])->name('block.delete');

            // Numbering Configuration
            Route::get('/numbering', [ModuleManagementController::class, 'numbering'])->name('numbering.selection');
            Route::get('/{module}/numbering', [ModuleManagementController::class, 'editNumbering'])->name('numbering');
            Route::post('/{module}/numbering', [ModuleManagementController::class, 'updateNumbering'])->name('numbering.update');

            // Toggle Module Status
            Route::post('/{module}/toggle', [ModuleManagementController::class, 'toggleStatus'])->name('toggle');
        });
    });


});
