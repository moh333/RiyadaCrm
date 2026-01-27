# Module Permissions Filtering - vtiger CRM

## Issue
When displaying modules in the role permissions table, ALL modules were being shown, but vtiger CRM only shows specific modules that require permission management.

## Root Cause
The query was only filtering by `presence = 0` (active modules), but was missing the critical filter for `isentitytype = 1`.

## vtiger_tab Table Structure

### Key Columns:

| Column | Type | Description |
|--------|------|-------------|
| `tabid` | INT | Unique module ID |
| `name` | VARCHAR | Module name |
| `presence` | INT | 0 = Active, 1 = Hidden, 2 = Disabled |
| `isentitytype` | INT | 0 = Utility module, 1 = Entity module |

### Module Types:

**Entity Modules (`isentitytype = 1`):**
- Represent business entities with records
- Require permission management (View, Create, Edit, Delete)
- Examples: Contacts, Leads, Accounts, Opportunities, Tickets, etc.
- **These should appear in permissions table**

**Utility Modules (`isentitytype = 0`):**
- Provide functionality/tools
- Don't have traditional CRUD permissions
- Examples: Home, Settings, Utilities, Import, etc.
- **These should NOT appear in permissions table**

## Solution

### Before (Incorrect):
```php
$modules = DB::connection('tenant')->table('vtiger_tab')
    ->where('presence', 0)
    ->orderBy('name')
    ->get();
```

**Result:** Shows ALL active modules (50+ modules)

### After (Correct):
```php
$modules = DB::connection('tenant')->table('vtiger_tab')
    ->where('presence', 0)
    ->where('isentitytype', 1)  // ← Added this filter
    ->orderBy('name')
    ->get();
```

**Result:** Shows only entity modules (~10-15 modules)

## Expected Modules in Permissions Table

Based on standard vtiger installation with `isentitytype = 1`:

1. **Dashboards** - Dashboard management
2. **Contacts** - Contact records
3. **Tickets** - Support tickets
4. **Reports** - Report records
5. **Comments** - Comment records
6. **SMS Notifier** - SMS records
7. **Email Templates** - Email template records
8. **CTLabels Update** - Custom module
9. **CTPowerBlocksFields** - Custom module
10. **CTWhatsAppExt** - Custom module

Plus any other custom entity modules.

## Why This Matters

### Permission Management Logic:
- **Entity modules** have records that users create, view, edit, and delete
- **Utility modules** are just interfaces/tools with no records

### Example:

**Contacts (Entity Module):**
- ✅ View permission - Can see contact records
- ✅ Create permission - Can create new contacts
- ✅ Edit permission - Can modify contacts
- ✅ Delete permission - Can delete contacts

**Home (Utility Module):**
- ❌ No View permission - It's just a dashboard
- ❌ No Create permission - Can't "create" a home page
- ❌ No Edit permission - No records to edit
- ❌ No Delete permission - No records to delete

## Database Query Explanation

```sql
SELECT * FROM vtiger_tab 
WHERE presence = 0      -- Active modules only
  AND isentitytype = 1  -- Entity modules only
ORDER BY name;
```

**Filters:**
1. `presence = 0` - Module is active (not hidden/disabled)
2. `isentitytype = 1` - Module is an entity type (has records)

## vtiger Standard Values

### presence Column:
- `0` = Active (shown in menu, available)
- `1` = Hidden (exists but not shown)
- `2` = Disabled (completely inactive)

### isentitytype Column:
- `0` = Utility/Tool module (no permission management)
- `1` = Entity module (requires permission management)

## Impact on Other Features

This filter should be applied everywhere modules are listed for permissions:

1. ✅ **Role Create Form** - Module permissions table
2. ✅ **Role Edit Form** - Module permissions table
3. ✅ **Profile Management** - Module permissions configuration
4. ✅ **Sharing Rules** - Module selection
5. ✅ **Field Permissions** - Module selection

## Testing

### Verify Correct Modules:
```sql
-- Should return only entity modules
SELECT tabid, name, isentitytype, presence 
FROM vtiger_tab 
WHERE presence = 0 AND isentitytype = 1 
ORDER BY name;
```

### Check a Specific Module:
```sql
-- Example: Check if "Contacts" is an entity module
SELECT name, isentitytype 
FROM vtiger_tab 
WHERE name = 'Contacts';
-- Should return: isentitytype = 1
```

### Verify Utility Modules are Excluded:
```sql
-- Should return utility modules (should NOT be in permissions)
SELECT tabid, name, isentitytype 
FROM vtiger_tab 
WHERE presence = 0 AND isentitytype = 0 
ORDER BY name;
```

## Files Modified

1. **RolesController.php** - `create()` method
   - Added `->where('isentitytype', 1)` filter

## Related vtiger Tables

- `vtiger_tab` - Module definitions
- `vtiger_profile2tab` - Profile-to-module permissions
- `vtiger_profile2standardpermissions` - Standard permissions (Create, Edit, Delete)
- `vtiger_def_org_share` - Default sharing rules per module

## Summary

The key to matching vtiger's permission management behavior is filtering modules by **`isentitytype = 1`**. This ensures only entity modules (those with actual records requiring CRUD permissions) appear in the permissions table, while utility modules (tools and interfaces) are correctly excluded.

This is a fundamental vtiger CRM pattern that must be followed for proper permission management.
