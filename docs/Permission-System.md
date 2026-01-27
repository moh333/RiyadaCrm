# Permission System Implementation

## Overview
This document describes the role-based permission system implemented for the RiyadaCRM application. The system controls access to modules, actions, tools, and fields based on user roles and profiles.

## Architecture

### 1. PermissionService
**Location**: `app/Modules/Tenant/Users/Domain/Services/PermissionService.php`

The `PermissionService` is the core component that checks permissions by:
- Retrieving user's role from `vtiger_user2role`
- Getting associated profiles from `vtiger_role2profile`
- Checking permissions in:
  - `vtiger_profile2tab` - Module access
  - `vtiger_profile2standardpermissions` - CRUD operations (create, edit, delete)
  - `vtiger_profile2utility` - Tools (Import, Export, Merge, Duplicates)
  - `vtiger_profile2field` - Field-level permissions

**Key Methods**:
- `hasPermission($userId, $moduleName, $action)` - Check module action permission
- `hasToolPermission($userId, $moduleName, $tool)` - Check tool permission
- `hasFieldPermission($userId, $fieldId, $access)` - Check field permission
- `getModulePermissions($userId, $moduleName)` - Get all permissions for a module

**Caching**: All permission checks are cached for 5 minutes (300 seconds) to improve performance.

### 2. CheckModulePermission Middleware
**Location**: `app/Http/Middleware/CheckModulePermission.php`

Backend middleware that protects routes by checking if the authenticated user has the required permission.

**Usage**:
```php
Route::get('/contacts', [ContactsController::class, 'index'])
    ->middleware('permission.module:Contacts,view');
```

**Registered as**: `permission.module` in `bootstrap/app.php`

### 3. Blade Directives
**Location**: `app/Providers/AppServiceProvider.php`

Custom Blade directives for frontend permission checks:

**@canModule / @endcanModule**:
```blade
@canModule('Contacts', 'create')
    <a href="{{ route('tenant.contacts.create') }}" class="btn btn-primary">
        Add Contact
    </a>
@endcanModule
```

**@cannotModule / @endcannotModule**:
```blade
@cannotModule('Contacts', 'delete')
    <p>You don't have permission to delete contacts.</p>
@endcannotModule
```

**@canTool / @endcanTool**:
```blade
@canTool('Contacts', 'Export')
    <a href="{{ route('tenant.contacts.export') }}" class="btn btn-secondary">
        Export
    </a>
@endcanTool
```

## Implementation Example: Contacts Module

### Backend Protection (routes/tenant.php)
```php
// View permission required
Route::get('/contacts', [ContactsController::class, 'index'])
    ->middleware('permission.module:Contacts,view');

// Create permission required
Route::get('/contacts/create', [ContactsController::class, 'create'])
    ->middleware('permission.module:Contacts,create');

// Edit permission required
Route::put('/contacts/{id}', [ContactsController::class, 'update'])
    ->middleware('permission.module:Contacts,edit');

// Delete permission required
Route::delete('/contacts/{id}', [ContactsController::class, 'destroy'])
    ->middleware('permission.module:Contacts,delete');
```

### Frontend Protection (views)

**Conditional Button Display**:
```blade
@canModule('Contacts', 'create')
<div>
    <a href="{{ route('tenant.contacts.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i>
        <span>{{ __('contacts::contacts.add_contact') }}</span>
    </a>
</div>
@endcanModule
```

**Tool Buttons**:
```blade
@canTool('Contacts', 'Export')
<a href="{{ route('tenant.contacts.export') }}" class="btn btn-outline-secondary">
    <i class="bi bi-download me-1"></i> Export
</a>
@endcanTool

@canTool('Contacts', 'Import')
<a href="{{ route('tenant.contacts.import.step1') }}" class="btn btn-outline-secondary">
    <i class="bi bi-upload me-1"></i> Import
</a>
@endcanTool

@canTool('Contacts', 'DuplicatesHandling')
<a href="{{ route('tenant.contacts.duplicates.index') }}" class="btn btn-outline-secondary">
    <i class="bi bi-intersect me-1"></i> Duplicates
</a>
@endcanTool
```

### Dynamic Action Buttons (ContactsController.php)
```php
public function data()
{
    return DataTables::query($query)
        ->addColumn('actions', function ($row) {
            $userId = auth('tenant')->id();
            $canEdit = $this->permissionService->hasPermission($userId, 'Contacts', 'edit');
            $canDelete = $this->permissionService->hasPermission($userId, 'Contacts', 'delete');
            
            $actions = '<div class="d-flex justify-content-end gap-2">';
            
            // View button - always show
            $actions .= '<a href="' . route('tenant.contacts.show', $row->contactid) . '" 
                            class="btn btn-sm btn-soft-info">
                            <i class="bi bi-eye"></i>
                        </a>';
            
            // Edit button - only if user has edit permission
            if ($canEdit) {
                $actions .= '<a href="' . route('tenant.contacts.edit', $row->contactid) . '" 
                                class="btn btn-sm btn-soft-primary">
                                <i class="bi bi-pencil"></i>
                            </a>';
            }
            
            // Delete button - only if user has delete permission
            if ($canDelete) {
                $actions .= '<form action="' . route('tenant.contacts.destroy', $row->contactid) . '" 
                                method="POST">
                                <button type="submit" class="btn btn-sm btn-soft-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>';
            }
            
            $actions .= '</div>';
            return $actions;
        })
        ->make(true);
}
```

## Permission Types

### 1. Module-Level Permissions
- **view**: Can view the module and its records
- **create**: Can create new records
- **edit**: Can edit existing records
- **delete**: Can delete records

### 2. Tool Permissions
- **Import** (activityid: 4): Can import data
- **Export** (activityid: 3): Can export data
- **Merge** (activityid: 8): Can merge duplicate records
- **DuplicatesHandling** (activityid: 10): Can find and handle duplicates

### 3. Field Permissions
- **view**: Field is visible (visible = 0)
- **edit**: Field is editable (visible = 0 AND readonly = 0)
- **hidden**: Field is not visible (visible = 1)
- **readonly**: Field is visible but not editable (readonly = 1)

## Database Tables

### vtiger_profile2tab
Controls module access for profiles.
- `permissions = 0`: Module enabled
- `permissions = 1`: Module disabled

### vtiger_profile2standardpermissions
Controls CRUD operations.
- `operation = 0`: Create
- `operation = 1`: Edit
- `operation = 2`: Delete
- `permissions = 0`: Allowed
- `permissions = 1`: Denied

### vtiger_profile2utility
Controls tool access.
- `activityid = 3`: Export
- `activityid = 4`: Import
- `activityid = 8`: Merge
- `activityid = 10`: Duplicates Handling
- `permission = 0`: Allowed
- `permission = 1`: Denied

### vtiger_profile2field
Controls field-level permissions.
- `visible = 0`: Field is visible
- `visible = 1`: Field is hidden
- `readonly = 0`: Field is editable
- `readonly = 1`: Field is read-only

## Extending to Other Modules

To apply permissions to a new module:

1. **Add PermissionService to Controller**:
```php
public function __construct(
    private PermissionService $permissionService
) {}
```

2. **Protect Routes**:
```php
Route::get('/module', [ModuleController::class, 'index'])
    ->middleware('permission.module:ModuleName,view');
```

3. **Update Views**:
```blade
@canModule('ModuleName', 'create')
    <!-- Create button -->
@endcanModule
```

4. **Dynamic Actions**:
```php
$canEdit = $this->permissionService->hasPermission($userId, 'ModuleName', 'edit');
```

## Testing Permissions

To test permissions:

1. Create a role with specific permissions in the Roles management
2. Assign a profile to that role with limited permissions
3. Assign a user to that role
4. Login as that user and verify:
   - Buttons are hidden/shown correctly
   - Routes are protected (403 error if no permission)
   - DataTable actions reflect permissions

## Performance Considerations

- All permission checks are cached for 5 minutes
- Cache is automatically cleared when roles/profiles are updated
- Use `clearUserPermissionCache($userId)` to manually clear cache

## Security Notes

- **Always check permissions on both frontend AND backend**
- Frontend checks improve UX by hiding unavailable actions
- Backend checks enforce security and prevent unauthorized access
- Never rely solely on frontend checks for security
