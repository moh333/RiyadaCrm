# Role Management - Inline Permissions Configuration

## Overview
The role management forms now support **inline module-level permissions configuration** when creating or editing roles with directly related profiles. This eliminates the need to navigate to the Profiles section separately.

## What's New

### ✅ Create Role Form Enhanced

**New Features:**
1. **Module Permissions Table** - Visible when "Assign new privileges directly to this role" is selected
2. **Select-All Functionality**:
   - Master checkbox to select/deselect all modules
   - Column checkboxes for each permission type (View, Create, Edit, Delete)
   - Row checkboxes to select all permissions for a specific module
3. **Individual Permission Checkboxes** - Fine-grained control over each module's permissions
4. **Field/Tool Configuration Buttons** - Placeholder for future field-level permissions

### 📋 Permissions Table Structure

| Column | Description |
|--------|-------------|
| ☑️ | Module row selector (selects all permissions for that module) |
| Module | Module name with icon |
| 👁️ View | Permission to view records |
| ➕ Create | Permission to create new records |
| ✏️ Edit | Permission to edit existing records |
| 🗑️ Delete | Permission to delete records |
| ⚙️ Tools | Configure field-level permissions (placeholder) |

### 🔄 How It Works

#### When Creating a Role:

1. **Select "Assign new privileges directly to this role"**
2. **Configure Permissions**:
   - Use checkboxes to grant module access
   - Select specific actions (View, Create, Edit, Delete)
   - Use select-all features for bulk configuration
3. **Save** - Permissions are automatically saved to:
   - `vtiger_profile` - Profile created
   - `vtiger_profile2tab` - Module access
   - `vtiger_profile2standardpermissions` - Action permissions

#### Data Flow:

```
Form Submission
    ↓
RolesController::store()
    ↓
Create Profile (vtiger_profile)
    ↓
Link Role to Profile (vtiger_role2profile)
    ↓
Save Module Access (vtiger_profile2tab)
    ↓
Save Action Permissions (vtiger_profile2standardpermissions)
```

### 🗄️ Database Tables Used

#### vtiger_profile2tab
Stores which modules a profile can access:
```sql
profileid INT(11)
tabid INT(11)
permissions TINYINT(1)  -- 0 = enabled, 1 = disabled
```

#### vtiger_profile2standardpermissions
Stores action permissions (Create, Edit, Delete):
```sql
profileid INT(11)
tabid INT(11)
operation INT(11)       -- 0=Create, 1=Edit, 2=Delete
permissions TINYINT(1)  -- 0 = allowed, 1 = denied
```

**Note:** View permission is controlled by `vtiger_profile2tab`. If a module is in this table with `permissions=0`, the user can view it.

### 📝 Form Data Structure

**Permissions Array:**
```php
permissions[{tabid}][view] = 1
permissions[{tabid}][create] = 1
permissions[{tabid}][edit] = 1
permissions[{tabid}][delete] = 1
```

**Example:**
```
permissions[2][view] = 1      // Can view Contacts
permissions[2][create] = 1    // Can create Contacts
permissions[2][edit] = 1      // Can edit Contacts
permissions[2][delete] = 1    // Can delete Contacts
```

### 🎯 JavaScript Features

**Select-All Functionality:**
- **Master Checkbox** (`#selectAllModules`) - Selects all modules and all permissions
- **Column Checkboxes** (`.select-all-permission`) - Selects all modules for a specific permission
- **Row Checkboxes** (`.module-select-row`) - Selects all permissions for a specific module

**Smart Synchronization:**
- Individual checkbox changes update row and column checkboxes
- Row checkbox changes update column and master checkboxes
- Column checkbox changes update row and master checkboxes
- All checkboxes stay in sync automatically

### 🔧 Controller Changes

#### RolesController::create()
```php
// Now loads modules for permissions table
$modules = DB::connection('tenant')->table('vtiger_tab')
    ->where('presence', 0)
    ->orderBy('name')
    ->get();
```

#### RolesController::store()
```php
// Saves permissions to vtiger tables
if ($request->has('permissions')) {
    foreach ($request->input('permissions', []) as $tabid => $perms) {
        // Save to vtiger_profile2tab (View permission)
        // Save to vtiger_profile2standardpermissions (Create, Edit, Delete)
    }
}
```

### 📊 vtiger Permissions Model

**Important:** In vtiger, `permissions = 0` means **ALLOWED**, `permissions = 1` means **DENIED**.

This is counter-intuitive but matches vtiger's original design:
- `vtiger_profile2tab.permissions = 0` → Module is **accessible**
- `vtiger_profile2standardpermissions.permissions = 0` → Action is **allowed**

### 🎨 UI/UX Features

1. **Responsive Table** - Scrollable on small screens
2. **Visual Feedback** - Hover effects on rows
3. **Icons** - Clear visual indicators for each permission type
4. **Tooltips** - Helpful hints on select-all checkboxes
5. **Smooth Animations** - Section transitions when switching privilege types

### ⚠️ Important Notes

1. **Directly Related Profiles Only** - Inline permissions only work when creating a new profile for the role
2. **Existing Profiles** - When selecting existing profiles, permissions are inherited (not editable inline)
3. **Field-Level Permissions** - "Configure" buttons are placeholders for future implementation
4. **vtiger Compatibility** - All data structures match vtiger CRM's schema exactly

### 🔮 Future Enhancements

1. **Edit Form Integration** - Add same permissions table to edit form with pre-populated data
2. **Field-Level Permissions** - Modal for configuring field visibility and editability
3. **Tool Permissions** - Configure access to import, export, merge, etc.
4. **Copy from Profile** - Quick-copy permissions from an existing profile as a starting point
5. **Permission Templates** - Pre-defined permission sets (Admin, Manager, User, etc.)

### 📁 Files Modified

1. **create.blade.php** - Added permissions table and JavaScript
2. **RolesController.php** - Added modules loading and permissions saving
3. **role-management-inline-permissions.md** - This documentation

### ✅ Testing Checklist

- [ ] Create role with directly related profile
- [ ] Select individual module permissions
- [ ] Use master select-all checkbox
- [ ] Use column select-all checkboxes
- [ ] Use row select-all checkboxes
- [ ] Verify permissions saved to `vtiger_profile2tab`
- [ ] Verify permissions saved to `vtiger_profile2standardpermissions`
- [ ] Switch between directly related and existing profiles
- [ ] Verify permissions table shows/hides correctly
- [ ] Test with no permissions selected
- [ ] Test with all permissions selected

### 🎓 Usage Example

**Creating a "Sales Manager" Role:**

1. Fill in role name: "Sales Manager"
2. Select parent role: "Sales Team"
3. Choose record assignment: "Same or Subordinate"
4. Select "Assign new privileges directly to this role"
5. Configure permissions:
   - ✅ Contacts: View, Create, Edit
   - ✅ Leads: View, Create, Edit, Delete
   - ✅ Opportunities: View, Create, Edit
   - ✅ Quotes: View, Create
   - ❌ Settings: (no access)
6. Click Save

**Result:**
- Role created with ID (e.g., H5)
- Profile created: "Sales Manager Profile"
- Permissions saved to database
- Ready to assign to users

## Summary

The inline permissions configuration provides a **seamless, all-in-one experience** for creating roles with custom permissions, eliminating the need for multi-step workflows across different sections of the application. This matches modern CRM UX expectations while maintaining full compatibility with vtiger's proven database architecture.
