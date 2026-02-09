<?php

declare(strict_types=1);

use App\Http\Middleware\InitializeTenancyOrRedirect;
use App\Modules\Tenant\Contacts\Presentation\Controllers\ContactsController;
use App\Modules\Tenant\Contacts\Presentation\Controllers\CustomFieldsController;
use App\Modules\Tenant\Core\Presentation\Controllers\GenericModuleController;
use App\Modules\Tenant\ModComments\Presentation\Controllers\ModCommentsController;
use App\Modules\Tenant\Presentation\Controllers\DashboardController;
use App\Modules\Tenant\Presentation\Controllers\LoginController;
use App\Modules\Tenant\Presentation\Controllers\ProfileController;
use App\Modules\Tenant\Presentation\Controllers\SettingsController;
use App\Modules\Tenant\Reports\Presentation\Controllers\ReportsController;
use App\Modules\Tenant\Settings\Presentation\Controllers\ModuleManagementController;
use App\Modules\Tenant\Settings\Presentation\Controllers\PicklistController;
use App\Modules\Tenant\Settings\Presentation\Controllers\PicklistDependencyController;
use App\Modules\Tenant\Settings\Presentation\Controllers\SchedulerController;
use App\Modules\Tenant\Settings\Presentation\Controllers\WorkflowController;
use App\Modules\Tenant\Settings\Presentation\Controllers\CompanyDetailsController;
use App\Modules\Tenant\Settings\Presentation\Controllers\CustomerPortalController;
use App\Modules\Tenant\Settings\Presentation\Controllers\CurrencyController;
use App\Modules\Tenant\Settings\Presentation\Controllers\OutgoingServerController;
use App\Modules\Tenant\Settings\Presentation\Controllers\ConfigEditorController;
use App\Modules\Tenant\Settings\Presentation\Controllers\CTPowerBlocksFieldsController;
use App\Modules\Tenant\Settings\Presentation\Controllers\TaxController;
use App\Modules\Tenant\Settings\Presentation\Controllers\TermsConditionsController;
use App\Modules\Tenant\Settings\Presentation\Controllers\UserPreferencesController;
use App\Modules\Tenant\Settings\Presentation\Controllers\CalendarSettingsController;
use App\Modules\Tenant\Settings\Presentation\Controllers\MyTagsController;
use App\Modules\Tenant\Users\Presentation\Controllers\GroupsController;
use App\Modules\Tenant\Users\Presentation\Controllers\LoginHistoryController;
use App\Modules\Tenant\Users\Presentation\Controllers\ProfilesController;
use App\Modules\Tenant\Users\Presentation\Controllers\RolesController;
use App\Modules\Tenant\Users\Presentation\Controllers\SharingRulesController;
use App\Modules\Tenant\Users\Presentation\Controllers\UsersController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Features\UserImpersonation;
use App\Modules\Core\VtigerModules\Contracts\ModuleRegistryInterface;
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
            Route::get('/', [ContactsController::class, 'index'])->name('index')->middleware('permission.module:Contacts,view');
            Route::get('/data', [ContactsController::class, 'data'])->name('data')->middleware('permission.module:Contacts,view');

            // Import/Export
            Route::get('/export', [ContactsController::class, 'export'])->name('export')->middleware('permission.module:Contacts,view');
            Route::get('/import', [ContactsController::class, 'importStep1'])->name('import.step1')->middleware('permission.module:Contacts,create');
            Route::post('/import/upload', [ContactsController::class, 'importStep2'])->name('import.step2')->middleware('permission.module:Contacts,create');
            Route::post('/import/process', [ContactsController::class, 'importProcess'])->name('import.process')->middleware('permission.module:Contacts,create');

            // Duplicates & Merge
            Route::get('/duplicates', [ContactsController::class, 'findDuplicates'])->name('duplicates.index')->middleware('permission.module:Contacts,view');
            Route::match(['get', 'post'], '/duplicates/search', [ContactsController::class, 'searchDuplicates'])->name('duplicates.search')->middleware('permission.module:Contacts,view');
            Route::match(['get', 'post'], '/duplicates/merge', [ContactsController::class, 'showMergeView'])->name('duplicates.merge')->middleware('permission.module:Contacts,edit');
            Route::post('/duplicates/process-merge', [ContactsController::class, 'processMerge'])->name('duplicates.process-merge')->middleware('permission.module:Contacts,edit');

            Route::get('/create', [ContactsController::class, 'create'])->name('create')->middleware('permission.module:Contacts,create');
            Route::post('/', [ContactsController::class, 'store'])->name('store')->middleware('permission.module:Contacts,create');

            // Contact CRUD routes with {id} parameter
            Route::get('/{id}', [ContactsController::class, 'show'])->name('show')->middleware('permission.module:Contacts,view');
            Route::get('/{id}/edit', [ContactsController::class, 'edit'])->name('edit')->middleware('permission.module:Contacts,edit');
            Route::put('/{id}', [ContactsController::class, 'update'])->name('update')->middleware('permission.module:Contacts,edit');
            Route::delete('/{id}', [ContactsController::class, 'destroy'])->name('destroy')->middleware('permission.module:Contacts,delete');
        });

        // Dynamic Modules (Metadata Engine)
        Route::prefix('modules')->name('modules.')->group(function () {
            Route::get('/{moduleName}', [GenericModuleController::class, 'index'])->name('index');
            Route::get('/{moduleName}/create', [GenericModuleController::class, 'create'])->name('create');
            Route::post('/{moduleName}', [GenericModuleController::class, 'store'])->name('store');
            Route::get('/{moduleName}/reference-search/{field}', [GenericModuleController::class, 'referenceSearch'])->name('reference-search');
            Route::get('/{moduleName}/{id}/edit', [GenericModuleController::class, 'edit'])->name('edit');
            Route::get('/{moduleName}/{id}/related/{relationId}', [GenericModuleController::class, 'relatedData'])->name('related-data');
            Route::get('/{moduleName}/{id}/{tab?}', [GenericModuleController::class, 'show'])->name('show');
            Route::put('/{moduleName}/{id}', [GenericModuleController::class, 'update'])->name('update');
            Route::delete('/{moduleName}/{id}', [GenericModuleController::class, 'destroy'])->name('destroy');
        });

        // Reports Module
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportsController::class, 'index'])->name('index');
            Route::get('/datatable', [ReportsController::class, 'datatable'])->name('datatable');
            Route::get('/condition-operators', [ReportsController::class, 'getConditionOperators'])->name('condition-operators');
            Route::get('/create', [ReportsController::class, 'create'])->name('create');
            Route::post('/', [ReportsController::class, 'store'])->name('store');
            Route::get('/{id}', [ReportsController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [ReportsController::class, 'edit'])->name('edit');
            Route::put('/{id}', [ReportsController::class, 'update'])->name('update');
            Route::delete('/{id}', [ReportsController::class, 'destroy'])->name('destroy');
            Route::get('/{id}/run', [ReportsController::class, 'run'])->name('run');
            Route::get('/{id}/export', [ReportsController::class, 'export'])->name('export');
        });

        // Test Routes for Vtiger Module Management Engine
        Route::prefix('test/modules')->group(function () {
            Route::get('/', function (ModuleRegistryInterface $registry) {
                $modules = $registry->all();
                return response()->json([
                    'total' => $modules->count(),
                    'modules' => $modules->map(fn($m) => [
                        'id' => $m->getId(),
                        'name' => $m->getName(),
                        'label' => $m->getLabel(),
                    ])->values(),
                ]);
            });

            Route::get('/{module}', function (string $module, ModuleRegistryInterface $registry) {
                try {
                    $moduleDefinition = $registry->get($module);
                    return response()->json([
                        'module' => [
                            'id' => $moduleDefinition->getId(),
                            'name' => $moduleDefinition->getName(),
                            'label' => $moduleDefinition->getLabel(),
                            'base_table' => $moduleDefinition->getBaseTable(),
                            'base_index' => $moduleDefinition->getBaseIndex(),
                            'is_entity' => $moduleDefinition->isEntity(),
                            'is_custom' => $moduleDefinition->isCustom(),
                            'is_active' => $moduleDefinition->isActive(),
                        ],
                        'fields' => $moduleDefinition->fields()->map(fn($f) => [
                            'name' => $f->getFieldName(),
                            'label' => $f->getLabel(),
                            'column' => $f->getColumnName(),
                            'table' => $f->getTableName(),
                            'uitype' => $f->getUitype(),
                            'type' => $f->getFieldType(),
                            'is_mandatory' => $f->isMandatory(),
                            'is_custom' => $f->isCustomField(),
                            'is_editable' => $f->isEditable(),
                        ])->values(),
                        'relations' => $moduleDefinition->relations()->map(fn($r) => [
                            'target' => $r->getTargetModule(),
                            'type' => $r->getRelationType(),
                            'field' => $r->getRelatedField(),
                            'label' => $r->getLabel(),
                        ])->values(),
                    ]);
                } catch (\Exception $e) {
                    return response()->json(['error' => $e->getMessage()], 404);
                }
            });

            Route::post('/refresh', function (ModuleRegistryInterface $registry) {
                $registry->refresh();
                return response()->json(['message' => 'Cache refreshed']);
            });
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
            Route::get('user/{id}', [UsersController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [UsersController::class, 'edit'])->name('edit');
            Route::put('/{id}', [UsersController::class, 'update'])->name('update');
            Route::delete('/{id}', [UsersController::class, 'destroy'])->name('destroy');

            // Roles
            Route::get('roles/get-profile-privileges', [RolesController::class, 'getProfilePrivileges'])->name('roles.get-profile-privileges');
            Route::get('roles/get-module-fields', [RolesController::class, 'getModuleFields'])->name('roles.get-module-fields');
            Route::get('roles/get-module-tools', [RolesController::class, 'getModuleTools'])->name('roles.get-module-tools');
            Route::post('roles/reorder', [RolesController::class, 'reorder'])->name('roles.reorder');
            Route::resource('roles', RolesController::class);

            // Profiles
            Route::post('profiles/{profile}/duplicate', [ProfilesController::class, 'duplicate'])->name('profiles.duplicate');
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

        // CRM Settings Routes
        Route::prefix('settings/crm')->name('settings.crm.')->group(function () {
            // Picklist Management
            Route::get('/picklist', [PicklistController::class, 'index'])->name('picklist.index');
            Route::post('/picklist/fields', [PicklistController::class, 'getPicklistFields'])->name('picklist.fields');
            Route::post('/picklist/values', [PicklistController::class, 'getPicklistValues'])->name('picklist.values');
            Route::post('/picklist/add', [PicklistController::class, 'addValue'])->name('picklist.add');
            Route::post('/picklist/update', [PicklistController::class, 'updateValue'])->name('picklist.update');
            Route::post('/picklist/delete', [PicklistController::class, 'deleteValue'])->name('picklist.delete');
            Route::post('/picklist/order', [PicklistController::class, 'updateOrder'])->name('picklist.order');

            // Picklist Dependency Management
            Route::get('/picklist-dependency', [PicklistDependencyController::class, 'index'])->name('picklist-dependency.index');
            Route::get('/picklist-dependency/create', [PicklistDependencyController::class, 'create'])->name('picklist-dependency.create');
            Route::post('/picklist-dependency/fields', [PicklistDependencyController::class, 'getAvailablePicklists'])->name('picklist-dependency.fields');
            Route::get('/picklist-dependency/edit', [PicklistDependencyController::class, 'edit'])->name('picklist-dependency.edit');
            Route::post('/picklist-dependency/store', [PicklistDependencyController::class, 'store'])->name('picklist-dependency.store');
            Route::post('/picklist-dependency/delete', [PicklistDependencyController::class, 'destroy'])->name('picklist-dependency.delete');

            // Automation - Workflows
            Route::prefix('automation')->name('automation.')->group(function () {
                Route::get('/workflows', [WorkflowController::class, 'index'])->name('workflows.index');
                Route::get('/workflows/data', [WorkflowController::class, 'data'])->name('workflows.data');
                Route::get('/workflows/create', [WorkflowController::class, 'create'])->name('workflows.create');
                Route::post('/workflows', [WorkflowController::class, 'store'])->name('workflows.store');
                Route::get('/workflows/{id}/edit', [WorkflowController::class, 'edit'])->name('workflows.edit');
                Route::put('/workflows/{id}', [WorkflowController::class, 'update'])->name('workflows.update');
                Route::delete('/workflows/{id}', [WorkflowController::class, 'destroy'])->name('workflows.destroy');
                Route::post('/workflows/{id}/toggle-status', [WorkflowController::class, 'toggleStatus'])->name('workflows.toggle-status');

                // AJAX endpoints for workflow management
                Route::get('/workflows/module-fields', [WorkflowController::class, 'getModuleFields'])->name('workflows.module-fields');
                Route::get('/workflows/condition-operators', [WorkflowController::class, 'getConditionOperators'])->name('workflows.condition-operators');
                Route::post('/workflows/{id}/conditions', [WorkflowController::class, 'updateConditions'])->name('workflows.update-conditions');
                Route::post('/workflows/{id}/schedule', [WorkflowController::class, 'updateSchedule'])->name('workflows.update-schedule');

                // Task management routes
                Route::post('/workflows/{workflowId}/tasks', [WorkflowController::class, 'createTask'])->name('workflows.tasks.create');
                Route::put('/workflows/{workflowId}/tasks/{taskId}', [WorkflowController::class, 'updateTask'])->name('workflows.tasks.update');
                Route::delete('/workflows/{workflowId}/tasks/{taskId}', [WorkflowController::class, 'deleteTask'])->name('workflows.tasks.delete');

                // Scheduler routes
                Route::get('/scheduler', [SchedulerController::class, 'index'])->name('scheduler.index');
                Route::get('/scheduler/data', [SchedulerController::class, 'data'])->name('scheduler.data');
                Route::get('/scheduler/create', [SchedulerController::class, 'create'])->name('scheduler.create');
                Route::post('/scheduler', [SchedulerController::class, 'store'])->name('scheduler.store');
                Route::get('/scheduler/{id}/edit', [SchedulerController::class, 'edit'])->name('scheduler.edit');
                Route::put('/scheduler/{id}', [SchedulerController::class, 'update'])->name('scheduler.update');
                Route::delete('/scheduler/{id}', [SchedulerController::class, 'destroy'])->name('scheduler.destroy');
                Route::post('/scheduler/{id}/toggle-status', [SchedulerController::class, 'toggleStatus'])->name('scheduler.toggle-status');
                Route::post('/scheduler/{id}/run-now', [SchedulerController::class, 'runNow'])->name('scheduler.run-now');
                Route::get('/scheduler/{id}/details', [SchedulerController::class, 'getDetails'])->name('scheduler.details');
            });

            // Company Details
            Route::prefix('company')->name('company.')->group(function () {
                Route::get('/', [CompanyDetailsController::class, 'index'])->name('index');
                Route::get('/edit', [CompanyDetailsController::class, 'edit'])->name('edit');
                Route::post('/update', [CompanyDetailsController::class, 'update'])->name('update');
                Route::post('/logo', [CompanyDetailsController::class, 'uploadLogo'])->name('logo');
            });

            // Customer Portal
            Route::prefix('portal')->name('portal.')->group(function () {
                Route::get('/', [CustomerPortalController::class, 'index'])->name('index');
                Route::post('/save', [CustomerPortalController::class, 'save'])->name('save');
                Route::get('/modules', [CustomerPortalController::class, 'getModules'])->name('modules');
            });

            // Currency Management
            Route::prefix('currency')->name('currency.')->group(function () {
                Route::get('/', [CurrencyController::class, 'index'])->name('index');
                Route::get('/data', [CurrencyController::class, 'data'])->name('data');
                Route::get('/create', [CurrencyController::class, 'create'])->name('create');
                Route::post('/', [CurrencyController::class, 'store'])->name('store');
                Route::get('/{id}/edit', [CurrencyController::class, 'edit'])->name('edit');
                Route::put('/{id}', [CurrencyController::class, 'update'])->name('update');
                Route::delete('/{id}', [CurrencyController::class, 'destroy'])->name('destroy');
            });

            // Outgoing Server (Mail)
            Route::prefix('mail')->name('mail.')->group(function () {
                Route::get('/', [OutgoingServerController::class, 'index'])->name('index');
                Route::get('/edit', [OutgoingServerController::class, 'edit'])->name('edit');
                Route::post('/save', [OutgoingServerController::class, 'save'])->name('save');
                Route::post('/test', [OutgoingServerController::class, 'test'])->name('test');
            });

            // Config Editor
            Route::prefix('config')->name('config.')->group(function () {
                Route::get('/', [ConfigEditorController::class, 'index'])->name('index');
                Route::get('/edit', [ConfigEditorController::class, 'edit'])->name('edit');
                Route::post('/save', [ConfigEditorController::class, 'save'])->name('save');
            });

            // CTPowerBlocksFields Management
            Route::prefix('power-blocks')->name('ctpower-blocks-fields.')->group(function () {
                Route::get('/', [CTPowerBlocksFieldsController::class, 'index'])->name('index');
                Route::get('/create', [CTPowerBlocksFieldsController::class, 'create'])->name('create');
                Route::post('/', [CTPowerBlocksFieldsController::class, 'store'])->name('store');
                Route::get('/{id}/edit', [CTPowerBlocksFieldsController::class, 'edit'])->name('edit');
                Route::put('/{id}', [CTPowerBlocksFieldsController::class, 'update'])->name('update');
                Route::delete('/{id}', [CTPowerBlocksFieldsController::class, 'destroy'])->name('destroy');
            });

            // Tax Management
            Route::prefix('tax')->name('tax.')->group(function () {
                Route::get('/', [TaxController::class, 'index'])->name('index');
                Route::get('/taxes', [TaxController::class, 'taxes'])->name('taxes');
                Route::get('/charges', [TaxController::class, 'charges'])->name('charges');
                Route::get('/regions', [TaxController::class, 'regions'])->name('regions');

                // Data routes
                Route::get('/data', [TaxController::class, 'data'])->name('data');
                Route::get('/charges/data', [TaxController::class, 'chargesData'])->name('charges.data');
                Route::get('/regions/data', [TaxController::class, 'regionsData'])->name('regions.data');

                // CRUD actions
                Route::post('/', [TaxController::class, 'store'])->name('store');
                Route::put('/{id}', [TaxController::class, 'update'])->name('update');
                Route::delete('/{id}', [TaxController::class, 'destroy'])->name('destroy');

                Route::post('/charges', [TaxController::class, 'storeCharge'])->name('charges.store');
                Route::put('/charges/{id}', [TaxController::class, 'updateCharge'])->name('charges.update');
                Route::delete('/charges/{id}', [TaxController::class, 'destroyCharge'])->name('charges.destroy');

                Route::post('/regions', [TaxController::class, 'storeRegion'])->name('regions.store');
                Route::put('/regions/{id}', [TaxController::class, 'updateRegion'])->name('regions.update');
                Route::delete('/regions/{id}', [TaxController::class, 'destroyRegion'])->name('regions.destroy');

                Route::post('/check-duplicate', [TaxController::class, 'checkDuplicate'])->name('check-duplicate');
            });

            // Terms and Conditions
            Route::prefix('terms')->name('terms.')->group(function () {
                Route::get('/', [TermsConditionsController::class, 'index'])->name('index');
                Route::get('/{module}/edit', [TermsConditionsController::class, 'edit'])->name('edit');
                Route::post('/save', [TermsConditionsController::class, 'save'])->name('save');
            });
        });

        // My Preferences (under settings)
        Route::prefix('settings/preferences')->name('settings.preferences.')->group(function () {
            Route::get('/', [UserPreferencesController::class, 'index'])->name('index');
            Route::get('/edit', [UserPreferencesController::class, 'edit'])->name('edit');
            Route::post('/update', [UserPreferencesController::class, 'update'])->name('update');
        });

        // Calendar Settings (under settings)
        Route::prefix('settings/calendar')->name('settings.calendar.')->group(function () {
            Route::get('/', [CalendarSettingsController::class, 'index'])->name('index');
            Route::get('/edit', [CalendarSettingsController::class, 'edit'])->name('edit');
            Route::post('/update', [CalendarSettingsController::class, 'update'])->name('update');
        });

        // My Tags (under settings)
        Route::prefix('settings/tags')->name('settings.tags.')->group(function () {
            Route::get('/', [MyTagsController::class, 'index'])->name('index');
            Route::get('/data', [MyTagsController::class, 'data'])->name('data');
            Route::post('/', [MyTagsController::class, 'store'])->name('store');
            Route::put('/{id}', [MyTagsController::class, 'update'])->name('update');
            Route::delete('/{id}', [MyTagsController::class, 'destroy'])->name('destroy');
            Route::post('/tag-cloud', [MyTagsController::class, 'updateTagCloud'])->name('tag-cloud');
        });
    });


});

