# Picklist Field Type Update

## Changes Made

### 1. CustomFieldType Enum
- Kept both `PICKLIST` (uitype 15) and `PICKLIST_READONLY` (uitype 16) enum cases
- Updated `getCustomFieldTypes()` to only return `PICKLIST` (uitype 15)
- Added comment explaining that uitype 16 is selected via checkbox

### 2. Custom Field Creation Form (create.blade.php)

**Added Role-Based Checkbox:**
```blade
<div id="role_based_container" class="col-md-12 d-none">
    <div class="form-check">
        <input class="form-check-input" type="checkbox" 
               name="role_based_picklist" value="1" id="role_based_picklist">
        <label class="form-check-label" for="role_based_picklist">
            <strong>Role-Based Picklist</strong>
        </label>
        <br>
        <small class="text-muted">
            Enable this to allow different user roles to see different picklist options. 
            Leave unchecked for a standard picklist where all users see the same options.
        </small>
    </div>
</div>
```

**Updated JavaScript:**
- Shows picklist values container when uitype 15 or 33 is selected
- Shows role-based checkbox ONLY when uitype 15 (Picklist) is selected
- Hides role-based checkbox for uitype 33 (Multi-select)
- Removed uitype 16 from the check since it's no longer in the dropdown

### 3. CustomFieldsController

**Updated `create()` method:**
```php
// Get only field types suitable for custom field creation
$fieldTypes = array_map(
    fn($type) => $type,
    CustomFieldType::getCustomFieldTypes()
);
```

**Updated `store()` method:**
```php
// Add validation for role_based_picklist
'role_based_picklist' => 'nullable|boolean',

// Convert uitype based on checkbox
if ($validated['uitype'] == 15 && !($request->input('role_based_picklist', false))) {
    $validated['uitype'] = 16; // Change to non-role-based
}
```

## User Experience

### Before:
- User sees two separate options in dropdown:
  - "Picklist" (uitype 15)
  - "Picklist" (uitype 16) - confusing duplicate

### After:
1. User selects "Picklist" from dropdown (only one option)
2. Picklist values textarea appears
3. **NEW:** "Role-Based Picklist" checkbox appears
4. User can check/uncheck to choose:
   - ✅ **Checked** = uitype 15 (role-based picklist)
   - ⬜ **Unchecked** = uitype 16 (standard picklist - default)

## Technical Flow

```
User selects "Picklist" (uitype 15)
         ↓
Form shows:
  - Picklist values textarea
  - Role-based checkbox
         ↓
User submits form
         ↓
Controller checks checkbox:
  - If checked → Keep uitype 15
  - If unchecked → Change to uitype 16
         ↓
Field created with correct uitype
```

## Benefits

✅ **Clearer UI** - No duplicate "Picklist" options
✅ **Better UX** - Checkbox with explanation is more intuitive
✅ **Flexible** - Easy to understand the difference between role-based and standard
✅ **Backward Compatible** - Both uitype 15 and 16 still work in the system
✅ **Default Behavior** - Unchecked = standard picklist (most common use case)

## Example Usage

**Creating a Standard Picklist (uitype 16):**
1. Select "Picklist" from Field Type dropdown
2. Enter picklist values
3. Leave "Role-Based Picklist" **unchecked**
4. Submit → Creates uitype 16

**Creating a Role-Based Picklist (uitype 15):**
1. Select "Picklist" from Field Type dropdown
2. Enter picklist values
3. **Check** "Role-Based Picklist"
4. Submit → Creates uitype 15
