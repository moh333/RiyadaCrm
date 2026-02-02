# CRM Settings - Quick Reference Guide

## 🚀 Quick Start

### Accessing the Features

1. **Login** to your tenant account
2. Navigate to **Administration** section in sidebar
3. Click on **CRM Settings** to expand
4. Choose:
   - **Picklist** - Manage dropdown values
   - **Picklist Dependency** - Configure field dependencies

## 📋 Picklist Management

### Adding a New Value

```javascript
// User Flow
1. Select Module (e.g., "Contacts")
2. Select Field (e.g., "Lead Source")
3. Click "Add Value" button
4. Enter value name
5. Choose color (optional)
6. Click "Save"
```

### Editing a Value

```javascript
// User Flow
1. Select Module and Field
2. Click edit icon (✏️) next to value
3. Modify value name or color
4. Click "Update"
```

### Deleting a Value

```javascript
// User Flow
1. Select Module and Field
2. Click delete icon (🗑️) next to value
3. Confirm deletion
```

## 🔗 Picklist Dependency

### Creating a Dependency

```javascript
// User Flow
1. Click "Add Dependency"
2. Select Module (e.g., "Contacts")
3. Select Source Field (e.g., "Lead Source")
4. Select Target Field (e.g., "Industry")
5. Click "Configure Dependency"
6. Click cells in matrix to toggle selections
7. Click "Save Dependency"
```

### Understanding the Matrix

```
Source Field Values (Rows) × Target Field Values (Columns)

✅ = Selected (Target value available for this source value)
⭕ = Not selected (Target value NOT available)

Example:
If "Advertisement" is selected, only checked industries will be available
```

### Editing a Dependency

```javascript
// User Flow
1. Find dependency in list
2. Click "Edit" button
3. Modify matrix selections
4. Click "Save Dependency"
```

### Deleting a Dependency

```javascript
// User Flow
1. Find dependency in list
2. Click "Delete" button
3. Confirm deletion
```

## 🛠️ Developer Reference

### Controller Methods

#### PicklistController

```php
// Get picklist fields for a module
POST /settings/crm/picklist/fields
Request: { module: "Contacts" }
Response: { fields: [...] }

// Get picklist values for a field
POST /settings/crm/picklist/values
Request: { fieldname: "leadsource" }
Response: { values: [...] }

// Add new value
POST /settings/crm/picklist/add
Request: { fieldname: "leadsource", value: "Website", color: "#6366f1" }
Response: { success: true, message: "..." }

// Update value
POST /settings/crm/picklist/update
Request: { fieldname: "leadsource", old_value: "Web", new_value: "Website", color: "#6366f1" }
Response: { success: true, message: "..." }

// Delete value
POST /settings/crm/picklist/delete
Request: { fieldname: "leadsource", value: "Website" }
Response: { success: true, message: "..." }
```

#### PicklistDependencyController

```php
// Get available picklist fields
POST /settings/crm/picklist-dependency/fields
Request: { module: "Contacts" }
Response: { fields: [...] }

// Save dependency
POST /settings/crm/picklist-dependency/store
Request: {
    module: "Contacts",
    source_field: "leadsource",
    target_field: "industry",
    mappings: {
        "Advertisement": ["Banking", "Insurance"],
        "Cold Call": ["Banking", "Finance"]
    }
}
Response: { success: true, message: "..." }

// Delete dependency
POST /settings/crm/picklist-dependency/delete
Request: {
    module: "Contacts",
    source_field: "leadsource",
    target_field: "industry"
}
Response: { success: true, message: "..." }
```

### Database Queries

#### Get Picklist Fields for Module

```php
DB::table('vtiger_field')
    ->where('tabid', $moduleId)
    ->whereIn('uitype', [15, 16, 33])
    ->where('presence', '!=', 1)
    ->get();
```

#### Get Picklist Values

```php
DB::table('vtiger_' . $fieldName)
    ->where('presence', 1)
    ->orderBy('sortorderid')
    ->get();
```

#### Get Dependencies for Module

```php
DB::table('vtiger_picklist_dependency')
    ->where('tabid', $moduleId)
    ->where('sourcefield', $sourceField)
    ->where('targetfield', $targetField)
    ->get();
```

### Frontend JavaScript

#### Load Picklist Fields

```javascript
$.ajax({
    url: '/settings/crm/picklist/fields',
    method: 'POST',
    data: {
        module: 'Contacts',
        _token: csrfToken
    },
    success: function(response) {
        // Handle response
        console.log(response.fields);
    }
});
```

#### Toggle Dependency Cell

```javascript
$(document).on('click', '.dependency-cell', function() {
    $(this).toggleClass('selected');
    
    if ($(this).hasClass('selected')) {
        $(this).html('<i class="bi bi-check-circle-fill text-success"></i>');
    } else {
        $(this).html('<i class="bi bi-circle text-muted"></i>');
    }
});
```

## 🎨 UI Components

### Color Picker

```html
<input type="color" class="form-control" value="#6366f1">
```

### Dependency Matrix Cell

```html
<td class="dependency-cell selected" 
    data-source="Advertisement" 
    data-target="Banking">
    <i class="bi bi-check-circle-fill text-success"></i>
</td>
```

### Value Badge

```html
<span class="badge" style="background-color: #6366f1; color: white;">
    Advertisement
</span>
```

## 🌐 Localization

### Using Translations

```php
// In Blade templates
{{ __('tenant::settings.picklist') }}
{{ __('tenant::settings.add_value') }}

// In JavaScript (via Blade)
'{{ __("tenant::settings.confirm_delete_value") }}'
```

### Adding New Translations

**English:** `app/Modules/Tenant/Resources/Lang/en/settings.php`
```php
'new_key' => 'English Translation',
```

**Arabic:** `app/Modules/Tenant/Resources/Lang/ar/settings.php`
```php
'new_key' => 'الترجمة العربية',
```

## 🔒 Security

### CSRF Protection

```javascript
// All POST requests include CSRF token
data: {
    _token: '{{ csrf_token() }}',
    // ... other data
}
```

### Input Validation

```php
// Controller validation
$request->validate([
    'fieldname' => 'required|string',
    'value' => 'required|string',
    'color' => 'nullable|string',
]);
```

### SQL Injection Prevention

```php
// Always use parameterized queries
DB::table($tableName)
    ->where($fieldName, $value)
    ->update(['presence' => 0]);
```

## 🐛 Troubleshooting

### Issue: Fields not loading

**Solution:**
1. Check browser console for errors
2. Verify module has picklist fields
3. Check database connection
4. Clear browser cache

### Issue: Values not saving

**Solution:**
1. Check CSRF token is valid
2. Verify database permissions
3. Check error logs
4. Ensure table exists

### Issue: Dependency matrix not displaying

**Solution:**
1. Verify both fields have values
2. Check JavaScript console
3. Ensure jQuery is loaded
4. Clear browser cache

### Issue: Translation not showing

**Solution:**
1. Check language file exists
2. Verify key is correct
3. Clear Laravel cache: `php artisan cache:clear`
4. Check locale setting

## 📊 Performance Tips

### Optimize Database Queries

```php
// Use select() to limit columns
DB::table('vtiger_field')
    ->select('fieldid', 'fieldname', 'fieldlabel')
    ->where('tabid', $moduleId)
    ->get();

// Use pagination for large datasets
DB::table('vtiger_picklist_dependency')
    ->paginate(50);
```

### Cache Frequently Used Data

```php
// Cache module list
$modules = Cache::remember('picklist_modules', 3600, function () {
    return $this->getPicklistSupportedModules();
});
```

### Minimize AJAX Requests

```javascript
// Load all data at once when possible
// Store in JavaScript variables
let fieldsData = {};
let valuesData = {};
```

## 🧪 Testing Checklist

### Picklist Management
- [ ] Select module loads fields
- [ ] Select field loads values
- [ ] Add value works
- [ ] Edit value works
- [ ] Delete value works
- [ ] Color picker works
- [ ] Validation works
- [ ] Error messages display

### Picklist Dependency
- [ ] List shows all dependencies
- [ ] Create dependency works
- [ ] Matrix displays correctly
- [ ] Cell toggle works
- [ ] Select All works
- [ ] Clear All works
- [ ] Save dependency works
- [ ] Delete dependency works
- [ ] Cyclic dependency prevented

### Localization
- [ ] English translations work
- [ ] Arabic translations work
- [ ] RTL layout works in Arabic

### Responsive Design
- [ ] Works on desktop
- [ ] Works on tablet
- [ ] Works on mobile

## 📚 Additional Resources

- **Analysis Document:** `docs/settings-picklist-analysis.md`
- **Implementation Summary:** `docs/crm-settings-implementation.md`
- **Menu Structure:** `docs/crm-settings-menu-structure.md`
- **Laravel Docs:** https://laravel.com/docs
- **Bootstrap Icons:** https://icons.getbootstrap.com/

## 🆘 Support

For issues or questions:
1. Check this guide
2. Review error logs
3. Check browser console
4. Review database structure
5. Contact development team

---

**Last Updated:** 2026-02-02  
**Version:** 1.0.0
