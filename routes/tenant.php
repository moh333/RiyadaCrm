<?php

declare(strict_types=1);

use App\Http\Middleware\InitializeTenancyOrRedirect;
use App\Modules\Tenant\Contacts\Presentation\Controllers\ContactsController;
use App\Modules\Tenant\Contacts\Presentation\Controllers\CustomFieldsController;
use App\Modules\Tenant\ModComments\Presentation\Controllers\ModCommentsController;
use App\Modules\Tenant\Presentation\Controllers\DashboardController;
use App\Modules\Tenant\Presentation\Controllers\LoginController;
use App\Modules\Tenant\Presentation\Controllers\ProfileController;
use App\Modules\Tenant\Presentation\Controllers\SettingsController;
use App\Modules\Tenant\Settings\Presentation\Controllers\ModuleManagementController;
use App\Modules\Tenant\Users\Presentation\Controllers\GroupsController;
use App\Modules\Tenant\Users\Presentation\Controllers\LoginHistoryController;
use App\Modules\Tenant\Users\Presentation\Controllers\ProfilesController;
use App\Modules\Tenant\Users\Presentation\Controllers\RolesController;
use App\Modules\Tenant\Users\Presentation\Controllers\SharingRulesController;
use App\Modules\Tenant\Users\Presentation\Controllers\UsersController;
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

            // Menu Management
            Route::get('/menu', [ModuleManagementController::class, 'menu'])->name('menu');
            Route::post('/menu', [ModuleManagementController::class, 'updateMenu'])->name('menu.update');

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

            // Relations Management
            Route::get('/relations', [ModuleManagementController::class, 'relationsSelection'])->name('relations.selection');
            Route::get('/{module}/relations', [ModuleManagementController::class, 'editRelations'])->name('relations');
            Route::post('/{module}/relations', [ModuleManagementController::class, 'storeRelation'])->name('relations.store');
            Route::put('/{module}/relations/{relationId}', [ModuleManagementController::class, 'updateRelation'])->name('relations.update');
            Route::delete('/{module}/relations/{relationId}', [ModuleManagementController::class, 'deleteRelation'])->name('relations.destroy');
            Route::post('/{module}/relations/reorder', [ModuleManagementController::class, 'reorderRelations'])->name('relations.reorder');

            // Toggle Module Status
            Route::post('/{module}/toggle', [ModuleManagementController::class, 'toggleStatus'])->name('toggle');
        });

        // ModComments Routes
        Route::prefix('comments')->name('comments.')->group(function () {
            Route::post('/', [ModCommentsController::class, 'store'])->name('store');
            Route::put('/{id}', [ModCommentsController::class, 'update'])->name('update');
            Route::delete('/{id}', [ModCommentsController::class, 'destroy'])->name('destroy');
        });

        // User Management Routes
        Route::prefix('settings/users')->name('settings.users.')->group(function () {
            // Users
            Route::get('/', [UsersController::class, 'index'])->name('index');
            Route::get('/create', [UsersController::class, 'create'])->name('create');
            Route::post('/', [UsersController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [UsersController::class, 'edit'])->name('edit');
            Route::put('/{id}', [UsersController::class, 'update'])->name('update');
            Route::delete('/{id}', [UsersController::class, 'destroy'])->name('destroy');

            // Roles
            Route::post('roles/reorder', [RolesController::class, 'reorder'])->name('roles.reorder');
            Route::resource('roles', RolesController::class);

            // Profiles
            Route::resource('profiles', ProfilesController::class);

            // Sharing Rules
            Route::get('/sharing-rules', [SharingRulesController::class, 'index'])->name('sharing-rules.index');
            Route::post('/sharing-rules', [SharingRulesController::class, 'updateDefaults'])->name('sharing-rules.update-defaults');
            Route::post('/sharing-rules/custom', [SharingRulesController::class, 'storeCustom'])->name('sharing-rules.custom.store');
            Route::put('/sharing-rules/custom/{id}', [SharingRulesController::class, 'updateCustom'])->name('sharing-rules.custom.update');
            Route::delete('/sharing-rules/custom/{id}', [SharingRulesController::class, 'destroyCustom'])->name('sharing-rules.custom.destroy');

            // Groups
            Route::resource('groups', GroupsController::class);

            // Login History
            Route::get('/login-history', [LoginHistoryController::class, 'index'])->name('login-history.index');
        });
    });


});
