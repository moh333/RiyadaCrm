# Copy Privileges Feature - Role Management

## Overview
Added a "Copy privileges from profile" feature that allows users to quickly populate the permissions table with an existing profile's permissions as a starting point when creating a new role.

## Feature Details

### 🎯 What It Does
When creating a role with "Assign new privileges directly to this role":
1. User selects an existing profile from the dropdown
2. System fetches that profile's permissions via AJAX
3. All permission checkboxes are automatically populated
4. User can then customize the permissions as needed

### 📋 UI Components

**Dropdown Location:**
- Appears at the top of the "New Privileges Section"
- Only visible when "Assign new privileges directly to this role" is selected
- Shows all shared profiles (excludes directly related profiles)

**Dropdown Options:**
- `-- Create from scratch --` (default) - Clears all checkboxes
- List of existing profiles - Copies their permissions

**User Feedback:**
- Loading state: Dropdown disabled while fetching
- Success notification: "Privileges copied successfully! You can now customize them."
- Error notification: "Error loading profile privileges. Please try again."

### 🔄 How It Works

#### Frontend (JavaScript):
```javascript
$('#copyFromProfile').on('change', function() {
    const profileId = $(this).val();
    
    if (!profileId) {
        // Clear all checkboxes
        return;
    }
    
    // Fetch via AJAX
    $.ajax({
        url: '/roles/get-profile-privileges',
        data: { profile_id: profileId },
        success: function(response) {
            // Populate checkboxes
            // Update select-all states
            // Show notification
        }
    });
});
```

#### Backend (Controller):
```php
public function getProfilePrivileges(Request $request)
{
    $profileId = $request->input('profile_id');
    
    // Fetch from vtiger_profile2tab (View permission)
    // Fetch from vtiger_profile2standardpermissions (Create, Edit, Delete)
    
    return response()->json(['privileges' => $privileges]);
}
```

### 📊 Data Flow

```
User selects profile
    ↓
AJAX GET request to /roles/get-profile-privileges
    ↓
RolesController::getProfilePrivileges()
    ↓
Query vtiger_profile2tab (module access)
    ↓
Query vtiger_profile2standardpermissions (actions)
    ↓
Return JSON: { privileges: { tabid: { view, create, edit, delete } } }
    ↓
JavaScript populates checkboxes
    ↓
Update all select-all checkboxes
    ↓
Show success notification
```

### 🗄️ Database Queries

**Module Access (View Permission):**
```sql
SELECT * FROM vtiger_profile2tab 
WHERE profileid = ?
```

**Action Permissions (Create, Edit, Delete):**
```sql
SELECT * FROM vtiger_profile2standardpermissions 
WHERE profileid = ? AND tabid = ?
```

**Operation Codes:**
- `0` = Create
- `1` = Edit
- `2` = Delete

**Permission Values:**
- `0` = Allowed
- `1` = Denied

### 📝 Response Format

```json
{
    "privileges": {
        "2": {
            "view": true,
            "create": true,
            "edit": true,
            "delete": false
        },
        "4": {
            "view": true,
            "create": false,
            "edit": false,
            "delete": false
        }
    }
}
```

Where keys are `tabid` (module IDs).

### 🎨 UI/UX Features

1. **Info Alert** - Explains the quick start feature
2. **Dropdown** - Clean, Bootstrap-styled select
3. **Loading State** - Dropdown disabled during fetch
4. **Toast Notifications** - Bootstrap alerts (auto-dismiss after 3s)
5. **Smart Sync** - All select-all checkboxes update automatically

### ⚙️ JavaScript Functions

**Main Handler:**
- `$('#copyFromProfile').on('change')` - Triggers AJAX on selection

**Helper Functions:**
- `updateModuleRowCheckboxes()` - Updates all row checkboxes
- `updateColumnCheckboxes()` - Updates all column checkboxes
- `updateSelectAllModules()` - Updates master checkbox
- `showNotification(message, type)` - Displays toast notification

### 🔧 Files Modified

1. **create.blade.php**
   - Added dropdown HTML
   - Added info alert
   - Added AJAX handler (60+ lines)
   - Added notification function (20+ lines)

2. **RolesController.php**
   - Fixed `getProfilePrivileges()` method
   - Now correctly fetches all operation types (Create, Edit, Delete)

3. **tenant.php** (routes)
   - Route already exists: `roles.get-profile-privileges`

### ✅ Testing Checklist

- [ ] Select "Create from scratch" - All checkboxes clear
- [ ] Select a profile - Checkboxes populate correctly
- [ ] View permissions load correctly
- [ ] Create permissions load correctly
- [ ] Edit permissions load correctly
- [ ] Delete permissions load correctly
- [ ] Row checkboxes update after copy
- [ ] Column checkboxes update after copy
- [ ] Master checkbox updates after copy
- [ ] Success notification appears
- [ ] Notification auto-dismisses after 3 seconds
- [ ] Can customize permissions after copying
- [ ] Dropdown disables during loading
- [ ] Error notification on AJAX failure

### 🎓 Usage Example

**Scenario: Creating a "Junior Sales Rep" role based on "Sales Manager" profile**

1. Fill in role name: "Junior Sales Rep"
2. Select "Assign new privileges directly to this role"
3. From "Copy privileges from" dropdown, select "Sales Manager"
4. ✅ All permissions from Sales Manager are copied
5. Customize:
   - ❌ Remove Delete permission from Opportunities
   - ❌ Remove access to Settings module
   - ✅ Keep View/Create/Edit for Contacts and Leads
6. Save

**Result:**
- New role created with customized permissions
- Saved time by not checking boxes manually
- Started from a proven permission set

### 🚀 Benefits

1. **Time Saving** - No need to manually check dozens of boxes
2. **Consistency** - Start from proven permission templates
3. **Flexibility** - Easy to customize after copying
4. **User-Friendly** - Clear feedback and smooth UX
5. **Error Prevention** - Less chance of missing important permissions

### 🔮 Future Enhancements

1. **Permission Templates** - Pre-defined sets (Admin, Manager, User)
2. **Compare Profiles** - Side-by-side comparison before copying
3. **Bulk Operations** - Copy from multiple profiles
4. **Permission Diff** - Show what changed after customization
5. **Save as Template** - Save custom permission sets for reuse

### 📚 Related Documentation

- **Inline Permissions**: `docs/role-management-inline-permissions.md`
- **vtiger Model**: `docs/role-management-vtiger-implementation.md`
- **Deep Analysis**: `docs/roles-deep-analysis.md`

## Summary

The "Copy privileges from profile" feature provides a **quick start** option for creating roles with complex permission sets. Users can select an existing profile as a template, automatically populate all permissions, and then fine-tune them to meet specific requirements. This dramatically improves the user experience and reduces the time needed to configure role permissions.
