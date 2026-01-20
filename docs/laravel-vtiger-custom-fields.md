# Laravel-vTiger Custom Fields Implementation

## Overview

This document describes how the Laravel application implements vTiger's custom fields and UI types system while maintaining full compatibility with the vTiger database schema.

## Architecture Alignment

### Database Schema Compatibility

Our Laravel implementation uses the **exact same database tables** as vTiger:

- `vtiger_field` - Field metadata (unchanged)
- `vtiger_contactscf` - Contact custom fields (unchanged)
- `vtiger_blocks` - Field grouping (unchanged)
- `vtiger_picklist` - Picklist definitions (unchanged)
- `vtiger_{picklistname}` - Picklist values tables (unchanged)

### Key Differences from vTiger PHP

| Aspect | vTiger PHP | Laravel Implementation |
|--------|-----------|----------------------|
| **Field Models** | `Vtiger_Field_Model` | `CustomField` domain entity |
| **UI Type Classes** | `modules/Vtiger/uitypes/*.php` | `CustomFieldType` enum |
| **Field Creation** | vtlib API | Use Cases (Clean Architecture) |
| **Validation** | `typeofdata` string parsing | Enum methods + Laravel validation |
| **Display Logic** | Smarty templates | Blade components |

## CustomFieldType Enum

### Supported UI Types

Our `CustomFieldType` enum maps to vTiger's uitype system:

```php
enum CustomFieldType: int
{
    // Text Types
    case TEXT = 1;              // uitype 1
    case TEXT_SPECIAL = 2;      // uitype 2
    case TEXT_LARGE = 19;       // uitype 19
    case TEXTAREA = 21;         // uitype 21
    
    // Numeric Types
    case INTEGER = 7;           // uitype 7
    case DECIMAL = 71;          // uitype 71
    case CURRENCY = 72;         // uitype 72
    case PERCENT = 9;           // uitype 9
    
    // Date/Time Types
    case DATE = 5;              // uitype 5
    case DATETIME = 6;          // uitype 6
    case DATETIME_MODIFIED = 50;// uitype 50
    case TIME = 14;             // uitype 14
    
    // Contact Types
    case EMAIL = 13;            // uitype 13
    case PHONE = 11;            // uitype 11
    case URL = 17;              // uitype 17
    case SKYPE = 85;            // uitype 85
    
    // Selection Types
    case PICKLIST = 15;         // uitype 15 (role-based)
    case PICKLIST_READONLY = 16;// uitype 16 (system)
    case MULTIPICKLIST = 33;    // uitype 33
    
    // Special Types
    case CHECKBOX = 56;         // uitype 56
    case SALUTATION = 55;       // uitype 55
    
    // Reference Types
    case REFERENCE = 10;        // uitype 10
    case OWNER = 52;            // uitype 52
    case OWNER_USER = 53;       // uitype 53
    
    // Media Types
    case IMAGE = 69;            // uitype 69
    case FILE = 28;             // uitype 28
    
    // Currency System
    case CURRENCY_LIST = 117;   // uitype 117
}
```

### Enum Methods

#### `label(): string`
Returns human-readable label for the field type.

```php
CustomFieldType::TEXT->label(); // "Text"
CustomFieldType::PICKLIST->label(); // "Picklist"
```

#### `columnType(): string`
Returns Laravel migration column type.

```php
CustomFieldType::TEXT->columnType(); // "string"
CustomFieldType::INTEGER->columnType(); // "integer"
CustomFieldType::CURRENCY->columnType(); // "decimal"
```

#### `columnLength(): ?int`
Returns column length for string types.

```php
CustomFieldType::TEXT->columnLength(); // 255
CustomFieldType::PICKLIST->columnLength(); // 200
```

#### `decimalPrecision(): array`
Returns `[precision, scale]` for decimal types.

```php
CustomFieldType::DECIMAL->decimalPrecision(); // [10, 2]
CustomFieldType::CURRENCY->decimalPrecision(); // [25, 8]
CustomFieldType::PERCENT->decimalPrecision(); // [5, 2]
```

#### `hasPicklistValues(): bool`
Checks if field type supports picklist values.

```php
CustomFieldType::PICKLIST->hasPicklistValues(); // true
CustomFieldType::TEXT->hasPicklistValues(); // false
```

#### `isReferenceType(): bool`
Checks if field is a reference/relationship type.

```php
CustomFieldType::REFERENCE->isReferenceType(); // true
CustomFieldType::OWNER->isReferenceType(); // true
```

#### `isFileType(): bool`
Checks if field is a file/media type.

```php
CustomFieldType::IMAGE->isFileType(); // true
CustomFieldType::FILE->isFileType(); // true
```

#### `isDateTimeType(): bool`
Checks if field is a date/time type.

```php
CustomFieldType::DATE->isDateTimeType(); // true
CustomFieldType::DATETIME->isDateTimeType(); // true
```

#### `getTypeOfData(bool $mandatory = false): string`
Returns vTiger's `typeofdata` validation string.

```php
CustomFieldType::TEXT->getTypeOfData(true);  // "V~M" (Varchar, Mandatory)
CustomFieldType::EMAIL->getTypeOfData(false); // "E~O" (Email, Optional)
CustomFieldType::INTEGER->getTypeOfData(true); // "I~M" (Integer, Mandatory)
```

## Field Creation Flow

### 1. User Request
```php
// POST /settings/custom-fields/Contacts
$request = [
    'label' => 'Customer Type',
    'uitype' => 15, // Picklist
    'block' => 1,
    'picklist_values' => ['Retail', 'Wholesale', 'Partner']
];
```

### 2. DTO Creation
```php
$dto = CreateCustomFieldDTO::fromRequest($request, 'Contacts');
// Automatically generates:
// - fieldname: 'customer_type'
// - columnname: 'cf_1234' (based on next fieldid)
// - tablename: 'vtiger_contactscf'
```

### 3. Use Case Execution
```php
$useCase = new CreateCustomFieldUseCase($customFieldRepository);
$customField = $useCase->execute($dto);
```

### 4. Database Operations

#### a. Insert into `vtiger_field`
```sql
INSERT INTO vtiger_field (
    tabid, fieldname, columnname, tablename, 
    generatedtype, uitype, fieldlabel, 
    typeofdata, displaytype, block, sequence
) VALUES (
    4,                      -- Contacts module
    'customer_type',        -- Field name
    'cf_1234',             -- Column name
    'vtiger_contactscf',   -- Table name
    2,                     -- Custom field
    15,                    -- Picklist uitype
    'Customer Type',       -- Label
    'V~O',                 -- Varchar, Optional
    1,                     -- Editable
    1,                     -- Block ID
    10                     -- Sequence
);
```

#### b. Add Column to `vtiger_contactscf`
```sql
ALTER TABLE vtiger_contactscf 
ADD COLUMN cf_1234 VARCHAR(200) NULL;
```

#### c. Create Picklist Structure (if applicable)
```sql
-- Create picklist table
CREATE TABLE vtiger_customer_type (
    customer_typeid INT AUTO_INCREMENT PRIMARY KEY,
    customer_type VARCHAR(200) NOT NULL,
    presence INT(1) NOT NULL DEFAULT 1,
    picklist_valueid INT(19) NOT NULL DEFAULT 0,
    sortorderid INT(11) DEFAULT 0
);

-- Insert values
INSERT INTO vtiger_customer_type (customer_type, sortorderid) VALUES
('Retail', 0),
('Wholesale', 1),
('Partner', 2);

-- Link to main picklist table
INSERT INTO vtiger_picklist (name) VALUES ('customer_type');
```

## Display Logic

### Edit View (Blade)

```blade
@foreach($customFields as $field)
    @switch($field->getUitype()->value)
        @case(1) {{-- TEXT --}}
            <input type="text" 
                   name="{{ $field->getFieldName() }}" 
                   value="{{ old($field->getFieldName(), $contact->getCustomField($field->getFieldName())) }}"
                   maxlength="{{ $field->getUitype()->columnLength() }}">
            @break
            
        @case(15) {{-- PICKLIST --}}
        @case(16)
            <select name="{{ $field->getFieldName() }}">
                @foreach($field->getPicklistValues() as $value)
                    <option value="{{ $value }}">{{ $value }}</option>
                @endforeach
            </select>
            @break
            
        @case(5) {{-- DATE --}}
            <input type="date" name="{{ $field->getFieldName() }}">
            @break
            
        @case(56) {{-- CHECKBOX --}}
            <input type="checkbox" 
                   name="{{ $field->getFieldName() }}" 
                   value="1"
                   {{ $contact->getCustomField($field->getFieldName()) ? 'checked' : '' }}>
            @break
    @endswitch
@endforeach
```

## Validation

### Laravel Validation Rules

```php
public function rules(): array
{
    $rules = [];
    
    foreach ($this->customFields as $field) {
        $fieldRules = [];
        
        // Mandatory check
        if ($field->isMandatory()) {
            $fieldRules[] = 'required';
        } else {
            $fieldRules[] = 'nullable';
        }
        
        // Type-specific validation
        switch ($field->getUitype()) {
            case CustomFieldType::EMAIL:
                $fieldRules[] = 'email';
                break;
                
            case CustomFieldType::INTEGER:
                $fieldRules[] = 'integer';
                break;
                
            case CustomFieldType::DECIMAL:
            case CustomFieldType::CURRENCY:
            case CustomFieldType::PERCENT:
                $fieldRules[] = 'numeric';
                break;
                
            case CustomFieldType::DATE:
                $fieldRules[] = 'date';
                break;
                
            case CustomFieldType::URL:
                $fieldRules[] = 'url';
                break;
                
            case CustomFieldType::PICKLIST:
            case CustomFieldType::PICKLIST_READONLY:
                $fieldRules[] = 'in:' . implode(',', $field->getPicklistValues());
                break;
        }
        
        $rules[$field->getFieldName()] = $fieldRules;
    }
    
    return $rules;
}
```

## Data Access Patterns

### Reading Custom Fields

```php
// Using Repository
$contact = $contactRepository->findById($contactId);
$customerType = $contact->getCustomField('customer_type');

// Direct Query
$result = DB::connection('tenant')
    ->table('vtiger_contactdetails as cd')
    ->join('vtiger_crmentity as ce', 'ce.crmid', '=', 'cd.contactid')
    ->leftJoin('vtiger_contactscf as cf', 'cf.contactid', '=', 'cd.contactid')
    ->where('cd.contactid', $contactId)
    ->select('cd.*', 'cf.cf_1234 as customer_type')
    ->first();
```

### Saving Custom Fields

```php
// Using Repository
$contact->setCustomField('customer_type', 'Wholesale');
$contactRepository->save($contact);

// Direct Query
DB::connection('tenant')
    ->table('vtiger_contactscf')
    ->updateOrInsert(
        ['contactid' => $contactId],
        ['cf_1234' => 'Wholesale']
    );
```

## Migration from vTiger PHP

### Field Creation Comparison

**vTiger PHP (vtlib):**
```php
$field = new Vtiger_Field();
$field->name = 'customer_type';
$field->label = 'Customer Type';
$field->table = 'vtiger_contactscf';
$field->column = 'cf_1234';
$field->uitype = 15;
$field->typeofdata = 'V~O';
$field->generatedtype = 2;
$block->addField($field);
$field->setPicklistValues(['Retail', 'Wholesale']);
```

**Laravel (Our Implementation):**
```php
$dto = new CreateCustomFieldDTO(
    label: 'Customer Type',
    uitype: CustomFieldType::PICKLIST,
    blockId: 1,
    picklistValues: ['Retail', 'Wholesale']
);

$useCase->execute($dto);
```

## Best Practices

### 1. Always Use Enums
```php
// ✅ Good
$uitype = CustomFieldType::PICKLIST;

// ❌ Bad
$uitype = 15;
```

### 2. Use Helper Methods
```php
// ✅ Good
if ($uitype->hasPicklistValues()) {
    // Handle picklist
}

// ❌ Bad
if (in_array($uitype->value, [15, 16, 33])) {
    // Handle picklist
}
```

### 3. Respect generatedtype
```php
// Only delete custom fields (generatedtype = 2)
if ($field->isCustomField()) {
    $customFieldRepository->delete($field->getFieldId());
}
```

### 4. Use Repository Pattern
```php
// ✅ Good
$contact = $contactRepository->findById($id);

// ❌ Bad
$contact = DB::table('vtiger_contactdetails')->find($id);
```

## Future Enhancements

### Planned Features

1. **UI Type Classes**: Implement Laravel equivalents of vTiger's UI Type classes for advanced display logic
2. **Field Dependencies**: Support for picklist dependencies
3. **Calculated Fields**: Support for uitype 10 (calculated/formula fields)
4. **Advanced Validation**: Implement all vTiger validation rules
5. **Field Permissions**: Role-based field visibility and editability

### Compatibility Notes

- All database operations maintain 100% compatibility with vTiger
- Custom fields created in Laravel are fully accessible in vTiger PHP
- Custom fields created in vTiger PHP are fully accessible in Laravel
- No data migration required when switching between systems

## Summary

This implementation provides:
- ✅ Full vTiger database schema compatibility
- ✅ Type-safe field type handling via enums
- ✅ Clean Architecture principles
- ✅ Laravel best practices
- ✅ Comprehensive validation
- ✅ Easy extensibility

The system bridges vTiger's legacy architecture with modern Laravel patterns while maintaining complete interoperability.
