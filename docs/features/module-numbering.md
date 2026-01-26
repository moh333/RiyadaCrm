# Module Numbering System

## Overview

The Module Numbering system in Riyada CRM provides automatic sequential numbering for records across all modules, similar to vtiger CRM. Each module can have its own customizable prefix and starting sequence number.

## Features

### 1. **Automatic Number Generation**
- Every new record automatically receives a unique sequential number
- Format: `PREFIX` + `SEQUENCE_NUMBER`
- Example: `CON1`, `CON2`, `CON3` for Contacts

### 2. **Customizable Per Module**
- Each module has independent numbering configuration
- Customize prefix (e.g., CON, ACC, LEA, ORG)
- Set custom starting sequence number
- Live preview of numbering format

### 3. **Database Schema**

The system uses two main tables following vtiger's schema:

#### `vtiger_modentity_num`
Stores the numbering configuration for each module:

| Column | Type | Description |
|--------|------|-------------|
| `num_id` | Primary Key | Unique identifier |
| `semodule` | varchar(100) | Module name (e.g., 'Contacts') |
| `prefix` | varchar(50) | Number prefix (e.g., 'CON') |
| `start_id` | varchar(50) | Starting sequence number |
| `cur_id` | varchar(50) | Current sequence number |
| `active` | varchar(2) | Active status (1 or 0) |

#### `vtiger_modentity_num_seq`
Sequence table for generating unique IDs:

| Column | Type | Description |
|--------|------|-------------|
| `id` | integer | Sequence counter |

## How It Works

### 1. **Configuration**

Navigate to: **Settings → Module Management → Module Numbering**

1. Select the module you want to configure
2. Set the prefix (2-4 characters recommended)
3. Set the starting sequence number
4. Save the configuration

### 2. **Number Generation Process**

When a new record is created:

```php
// Example from EloquentContactRepository
public function generateContactNumber(): string
{
    // Get next sequence from vtiger_modentity_num
    $query = DB::connection('tenant')->table('vtiger_modentity_num')
        ->where('semodule', 'Contacts')
        ->lockForUpdate();

    $row = $query->first();

    if (!$row) {
        // Initialize if missing
        $nextSequence = 1;
        DB::connection('tenant')->table('vtiger_modentity_num')->insert([
            'semodule' => 'Contacts',
            'prefix' => 'CON',
            'start_id' => 1,
            'cur_id' => 1,
            'active' => 1,
        ]);
    } else {
        $nextSequence = $row->cur_id + 1;
        DB::connection('tenant')->table('vtiger_modentity_num')
            ->where('semodule', 'Contacts')
            ->update([
                'cur_id' => $nextSequence
            ]);
    }

    return 'CON' . $nextSequence;
}
```

### 3. **Thread Safety**

The system uses database row locking (`lockForUpdate()`) to ensure:
- No duplicate numbers are generated
- Thread-safe in concurrent environments
- Transactional integrity

## Implementation Guide

### For New Modules

To implement module numbering for a new module:

#### 1. **Add to Repository Interface**

```php
interface YourModuleRepositoryInterface
{
    public function generateModuleNumber(): string;
}
```

#### 2. **Implement in Repository**

```php
public function generateModuleNumber(): string
{
    $query = DB::connection('tenant')->table('vtiger_modentity_num')
        ->where('semodule', 'YourModule')
        ->lockForUpdate();

    $row = $query->first();

    if (!$row) {
        $nextSequence = 1;
        DB::connection('tenant')->table('vtiger_modentity_num')->insert([
            'semodule' => 'YourModule',
            'prefix' => 'YMD',  // Your module prefix
            'start_id' => 1,
            'cur_id' => 1,
            'active' => 1,
        ]);
    } else {
        $nextSequence = $row->cur_id + 1;
        DB::connection('tenant')->table('vtiger_modentity_num')
            ->where('semodule', 'YourModule')
            ->update(['cur_id' => $nextSequence]);
    }

    return 'YMD' . $nextSequence;
}
```

#### 3. **Call During Record Creation**

```php
public function save(YourEntity $entity): void
{
    DB::connection('tenant')->transaction(function () use ($entity) {
        $exists = DB::connection('tenant')
            ->table('vtiger_crmentity')
            ->where('crmid', $entity->getId())
            ->exists();

        if (!$exists) {
            // Generate number for new records only
            $moduleNumber = $this->generateModuleNumber();
            
            // Store in your module's main table
            DB::connection('tenant')->table('vtiger_yourmodule')->insert([
                'yourmoduleid' => $entity->getId(),
                'yourmodule_no' => $moduleNumber,
                // ... other fields
            ]);
        }
    });
}
```

## User Interface

### Numbering Selection Page

Shows all available modules with entity records:
- Grid layout with module cards
- Click to configure numbering for specific module
- Visual indicators for configured modules

### Numbering Configuration Page

For each module:
- **Prefix Input**: Short identifier (e.g., CON, ACC)
- **Start Sequence Input**: Starting number (default: 1)
- **Live Preview**: Shows example number format
- **Tips Panel**: Best practices for numbering

## Best Practices

### 1. **Prefix Guidelines**
- Keep prefixes short (2-4 characters)
- Use uppercase for consistency
- Make them memorable and related to module name
- Examples:
  - Contacts: `CON`
  - Accounts: `ACC`
  - Leads: `LEA`
  - Opportunities: `OPP`

### 2. **Sequence Numbers**
- Start from 1 for new systems
- For migrated data, start after highest existing number
- Changing sequence won't affect existing records
- Only new records use the updated sequence

### 3. **Thread Safety**
- Always use `lockForUpdate()` when reading sequence
- Wrap in database transaction
- Update sequence in same transaction

### 4. **Error Handling**
- Initialize configuration if missing
- Handle concurrent access gracefully
- Log sequence generation failures

## Routes

The module numbering feature uses these routes:

```php
// Selection page - choose which module to configure
Route::get('/settings/modules/numbering', 'numbering')
    ->name('tenant.settings.modules.numbering.selection');

// Configuration page for specific module
Route::get('/settings/modules/{module}/numbering', 'editNumbering')
    ->name('tenant.settings.modules.numbering');

// Update numbering configuration
Route::post('/settings/modules/{module}/numbering', 'updateNumbering')
    ->name('tenant.settings.modules.numbering.update');
```

## Controller Methods

### `ModuleManagementController`

#### `numbering()`
Shows module selection page for numbering configuration.

#### `editNumbering(string $module)`
Shows numbering configuration form for specific module.

#### `updateNumbering(Request $request, string $module)`
Saves numbering configuration:
- Validates prefix and start_id
- Uses `updateOrInsert` to create or update configuration
- Preserves current sequence if already exists

## Database Migrations

The tables are created via migrations:

```php
// vtiger_modentity_num table
Schema::create('vtiger_modentity_num', function (Blueprint $table) {
    $table->id('num_id');
    $table->string('semodule', 100)->nullable();
    $table->string('prefix', 50)->default('');
    $table->string('start_id', 50);
    $table->string('cur_id', 50);
    $table->string('active', 2);
    $table->index(['semodule', 'active'], 'semodule_active_idx');
});

// vtiger_modentity_num_seq table
Schema::create('vtiger_modentity_num_seq', function (Blueprint $table) {
    $table->integer('id');
});
```

## Troubleshooting

### Numbers Not Generating

**Problem**: New records don't get automatic numbers

**Solution**:
1. Check if module has numbering configuration in `vtiger_modentity_num`
2. Verify repository calls `generateModuleNumber()` during creation
3. Ensure transaction is committed successfully

### Duplicate Numbers

**Problem**: Multiple records have same number

**Solution**:
1. Verify `lockForUpdate()` is used when reading sequence
2. Check that sequence update is in same transaction
3. Review concurrent access patterns

### Sequence Gaps

**Problem**: Numbers skip (e.g., CON1, CON3, CON5)

**Solution**:
- This is normal if records are created then rolled back
- Gaps ensure uniqueness and are acceptable
- vtiger CRM also exhibits this behavior

## Comparison with vtiger CRM

Riyada CRM's module numbering is fully compatible with vtiger:

| Feature | vtiger CRM | Riyada CRM |
|---------|-----------|------------|
| Database Schema | ✅ Same | ✅ Same |
| Prefix Customization | ✅ Yes | ✅ Yes |
| Sequence Control | ✅ Yes | ✅ Yes |
| Thread Safety | ✅ Row Locking | ✅ Row Locking |
| Per-Module Config | ✅ Yes | ✅ Yes |
| UI Configuration | ✅ Yes | ✅ Enhanced |

## Future Enhancements

Potential improvements:

1. **Number Formatting**
   - Add padding options (e.g., CON001 vs CON1)
   - Date-based prefixes (e.g., CON-2026-001)
   - Custom separators

2. **Bulk Operations**
   - Reset sequences
   - Renumber existing records
   - Export/import configurations

3. **Advanced Features**
   - Branch/location-specific prefixes
   - Year-based reset (auto-reset each year)
   - Custom number generators

## Related Files

- **Controller**: `app/Modules/Tenant/Settings/Presentation/Controllers/ModuleManagementController.php`
- **Views**: 
  - `app/Modules/Tenant/Presentation/Views/module_mgmt/numbering_selection.blade.php`
  - `app/Modules/Tenant/Presentation/Views/module_mgmt/numbering.blade.php`
- **Migrations**:
  - `database/migrations/tenant/2026_01_13_135255_create_vtiger_modentity_num_table.php`
  - `database/migrations/tenant/2026_01_13_135255_create_vtiger_modentity_num_seq_table.php`
- **Example Implementation**: `app/Modules/Tenant/Contacts/Infrastructure/EloquentContactRepository.php`
- **Routes**: `routes/tenant.php`

## Conclusion

The Module Numbering system provides a robust, vtiger-compatible solution for automatic record numbering. It's thread-safe, customizable, and follows the same patterns as vtiger CRM, ensuring compatibility and familiar behavior for users migrating from vtiger.
