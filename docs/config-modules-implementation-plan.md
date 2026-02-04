# Configuration Modules Implementation Plan

## Overview
Implement 5 configuration modules under CRM Settings menu:
1. Company Details
2. Customer Portal  
3. Currencies
4. Outgoing Server
5. Config Editor

## Implementation Structure

### 1. Directory Structure
```
app/Modules/Tenant/Settings/
├── Presentation/
│   ├── Controllers/
│   │   ├── CompanyDetailsController.php
│   │   ├── CustomerPortalController.php
│   │   ├── CurrencyController.php
│   │   ├── OutgoingServerController.php
│   │   └── ConfigEditorController.php
│   └── Views/
│       └── settings/
│           ├── company/
│           │   ├── index.blade.php
│           │   └── edit.blade.php
│           ├── portal/
│           │   └── index.blade.php
│           ├── currency/
│           │   ├── index.blade.php
│           │   └── edit.blade.php
│           ├── mail/
│           │   ├── index.blade.php
│           │   └── edit.blade.php
│           └── config/
│               ├── index.blade.php
│               └── edit.blade.php
```

### 2. Routes
```php
// routes/tenant.php
Route::prefix('settings/crm')->name('settings.crm.')->group(function () {
    // Company Details
    Route::get('/company', [CompanyDetailsController::class, 'index'])->name('company.index');
    Route::get('/company/edit', [CompanyDetailsController::class, 'edit'])->name('company.edit');
    Route::post('/company', [CompanyDetailsController::class, 'update'])->name('company.update');
    Route::post('/company/logo', [CompanyDetailsController::class, 'uploadLogo'])->name('company.logo');
    
    // Customer Portal
    Route::get('/portal', [CustomerPortalController::class, 'index'])->name('portal.index');
    Route::post('/portal', [CustomerPortalController::class, 'save'])->name('portal.save');
    
    // Currency
    Route::resource('currency', CurrencyController::class);
    Route::get('/currency/data', [CurrencyController::class, 'data'])->name('currency.data');
    
    // Outgoing Server
    Route::get('/mail', [OutgoingServerController::class, 'index'])->name('mail.index');
    Route::get('/mail/edit', [OutgoingServerController::class, 'edit'])->name('mail.edit');
    Route::post('/mail', [OutgoingServerController::class, 'save'])->name('mail.save');
    Route::post('/mail/test', [OutgoingServerController::class, 'test'])->name('mail.test');
    
    // Config Editor
    Route::get('/config', [ConfigEditorController::class, 'index'])->name('config.index');
    Route::get('/config/edit', [ConfigEditorController::class, 'edit'])->name('config.edit');
    Route::post('/config', [ConfigEditorController::class, 'save'])->name('config.save');
});
```

### 3. Menu Integration
Add to layout.blade.php under CRM Settings submenu (after Automation):
```blade
<li><a href="{{ route('tenant.settings.crm.company.index') }}" class="nav-link ps-4">
    <i class="bi bi-building me-2"></i> {{ __('tenant::settings.company_details') }}
</a></li>
<li><a href="{{ route('tenant.settings.crm.portal.index') }}" class="nav-link ps-4">
    <i class="bi bi-person-circle me-2"></i> {{ __('tenant::settings.customer_portal') }}
</a></li>
<li><a href="{{ route('tenant.settings.crm.currency.index') }}" class="nav-link ps-4">
    <i class="bi bi-currency-exchange me-2"></i> {{ __('tenant::settings.currencies') }}
</a></li>
<li><a href="{{ route('tenant.settings.crm.mail.index') }}" class="nav-link ps-4">
    <i class="bi bi-envelope-at me-2"></i> {{ __('tenant::settings.outgoing_server') }}
</a></li>
<li><a href="{{ route('tenant.settings.crm.config.index') }}" class="nav-link ps-4">
    <i class="bi bi-code-square me-2"></i> {{ __('tenant::settings.config_editor') }}
</a></li>
```

### 4. Database Migrations
Create migrations for:
- vtiger_organizationdetails (if not exists)
- vtiger_currency_info (if not exists)
- vtiger_currencies (if not exists)
- vtiger_customerportal_* tables (if not exists)
- vtiger_systems (if not exists)

### 5. Models
Create Eloquent models:
- OrganizationDetails
- CurrencyInfo
- Currency
- CustomerPortalTab
- CustomerPortalField
- CustomerPortalSettings
- OutgoingServer

### 6. Localization Keys
Add to lang/en/settings.php and lang/ar/settings.php

## Implementation Order
1. ✅ Create implementation plan
2. Add menu items to sidebar
3. Create routes
4. Create controllers (basic structure)
5. Create views (basic structure)
6. Implement Company Details
7. Implement Currency Management
8. Implement Outgoing Server
9. Implement Config Editor
10. Implement Customer Portal
11. Testing and refinement
