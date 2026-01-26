# Module Relations Management - Implementation Plan

## Overview
Implement a comprehensive module relations management system that allows administrators to:
- View all relations for a module
- Create new relations between modules
- Modify existing relations (label, sequence, actions)
- Delete relations
- Reorder relations via drag-and-drop

## Database Schema

### vtiger_relatedlists
Main table storing module relationships:

| Column | Type | Description |
|--------|------|-------------|
| relation_id | int (PK) | Unique relation identifier |
| tabid | int | Source module ID |
| related_tabid | int | Target module ID |
| name | varchar(100) | Relation name/identifier |
| sequence | int | Display order |
| label | varchar(100) | Display label |
| presence | int | Visibility (0=visible, 1=hidden) |
| actions | varchar(50) | Available actions (ADD,SELECT) |
| relationfieldid | int | Field ID if field-based relation |
| source | varchar(25) | Source type |
| relationtype | varchar(10) | Type: 1:N, N:N, lookup |

### vtiger_relatedlists_seq
Sequence table for generating relation IDs

## Features to Implement

### 1. List Relations (Selection Page)
- Show all modules in grid layout
- Click to manage relations for specific module
- Show count of relations per module

### 2. Manage Relations (Per Module)
- List all relations for the module
- Show: Target Module, Label, Type, Actions, Sequence
- Drag-and-drop to reorder
- Edit button for each relation
- Delete button for each relation
- Add new relation button

### 3. Create Relation
- Select target module (dropdown)
- Set label
- Choose relation type (1:N, N:N)
- Select actions (ADD, SELECT checkboxes)
- Set sequence
- Optional: Select linking field

### 4. Edit Relation
- Modify label
- Change actions
- Update sequence
- Cannot change source/target modules (delete and recreate instead)

### 5. Delete Relation
- Confirmation dialog
- Soft delete (set presence = 1) or hard delete
- Update sequences of remaining relations

## Implementation Steps

### Step 1: Add Routes
```php
// routes/tenant.php
Route::prefix('settings/modules/relations')->name('settings.modules.relations.')->group(function () {
    Route::get('/', 'relationsSelection')->name('selection');
    Route::get('/{module}', 'editRelations')->name('index');
    Route::post('/{module}', 'storeRelation')->name('store');
    Route::put('/{module}/{relationId}', 'updateRelation')->name('update');
    Route::delete('/{module}/{relationId}', 'deleteRelation')->name('destroy');
    Route::post('/{module}/reorder', 'reorderRelations')->name('reorder');
});
```

### Step 2: Add Controller Methods
In `ModuleManagementController.php`:
- `relationsSelection()` - Show module selection
- `editRelations($module)` - Show relations for module
- `storeRelation(Request, $module)` - Create new relation
- `updateRelation(Request, $module, $relationId)` - Update relation
- `deleteRelation($module, $relationId)` - Delete relation
- `reorderRelations(Request, $module)` - Update sequences

### Step 3: Create Views
- `relations_selection.blade.php` - Module grid
- `relations.blade.php` - Manage relations for module
- Modals for add/edit relation forms

### Step 4: Add Repository Methods
In `ModuleMetadataRepositoryInterface`:
- `addRelation()` - Create new relation
- `updateRelation()` - Update existing relation
- `deleteRelation()` - Delete relation
- `updateRelationSequences()` - Bulk update sequences

### Step 5: Add Translations
```php
'module_relations' => 'Module Relations',
'add_relation' => 'Add Relation',
'edit_relation' => 'Edit Relation',
'target_module' => 'Target Module',
'relation_type' => 'Relation Type',
'relation_label' => 'Relation Label',
'available_actions' => 'Available Actions',
'action_add' => 'Allow Add',
'action_select' => 'Allow Select',
```

## Relation Types

### 1:N (One-to-Many)
- Example: Contact → Potentials
- One contact can have many potentials
- Uses a foreign key field in target module

### N:N (Many-to-Many)
- Example: Contact → Campaigns
- Many contacts can be in many campaigns
- Uses a junction table

### Lookup
- Field-based relationship
- Dropdown field that references another module

## Actions

### ADD
- Allows creating new related records
- Shows "Add" button in related list

### SELECT
- Allows linking existing records
- Shows "Select" button in related list

## UI/UX Considerations

### Relations List
- Table with sortable columns
- Drag handles for reordering
- Inline edit icons
- Delete with confirmation
- Color-coded by relation type

### Add/Edit Form
- Modal dialog
- Validation
- Live preview of relation
- Help text explaining options

### Module Selection
- Grid layout like numbering
- Show relation count badge
- Search/filter modules

## Validation Rules

### Create Relation
- Source module must exist
- Target module must exist
- Label is required
- Relation type is required
- Cannot create duplicate relation (same source + target)

### Update Relation
- Label is required
- Actions must be valid (ADD, SELECT, or both)
- Sequence must be positive integer

### Delete Relation
- Cannot delete if relation is used in custom code
- Warn if deleting system relations

## Security

- Only administrators can manage relations
- Validate all inputs
- Use transactions for data consistency
- Log all relation changes

## Testing Checklist

- [ ] List all modules
- [ ] View relations for a module
- [ ] Create 1:N relation
- [ ] Create N:N relation
- [ ] Edit relation label
- [ ] Edit relation actions
- [ ] Reorder relations via drag-drop
- [ ] Delete relation
- [ ] Validate duplicate prevention
- [ ] Test with no relations
- [ ] Test with many relations

## Files to Create/Modify

### New Files
1. `app/Modules/Tenant/Presentation/Views/module_mgmt/relations_selection.blade.php`
2. `app/Modules/Tenant/Presentation/Views/module_mgmt/relations.blade.php`

### Modified Files
1. `routes/tenant.php` - Add relation routes
2. `app/Modules/Tenant/Settings/Presentation/Controllers/ModuleManagementController.php` - Add methods
3. `app/Modules/Core/VtigerModules/Contracts/ModuleMetadataRepositoryInterface.php` - Add methods
4. `app/Modules/Core/VtigerModules/Infrastructure/VtigerModuleMetadataRepository.php` - Implement methods
5. `app/Modules/Tenant/Resources/Lang/en/tenant.php` - Add translations
6. `app/Modules/Tenant/Resources/Lang/ar/tenant.php` - Add translations

## Next Steps

1. Implement routes
2. Add controller methods
3. Create repository methods
4. Build views
5. Add translations
6. Test thoroughly
7. Document usage

---

**Status**: Ready to implement
**Complexity**: High
**Estimated Time**: 4-6 hours
**Priority**: High
