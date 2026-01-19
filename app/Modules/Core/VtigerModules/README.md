# Vtiger Module Management Engine

A generic, framework-level engine for dynamically managing vtiger CRM module metadata using Clean Architecture and DDD principles.

## Overview

This engine provides a clean, object-oriented API for accessing vtiger module structure, fields, blocks, and relationships. It powers dynamic forms, APIs, permissions, and custom field management across the entire CRM.

## Architecture

```
app/Modules/Core/VtigerModules/
├── Domain/                    # Pure PHP domain objects
│   ├── ModuleDefinition.php   # Module aggregate root
│   ├── FieldDefinition.php    # Field metadata
│   ├── BlockDefinition.php    # Block/section metadata
│   └── RelationDefinition.php # Module relationships
├── Contracts/                 # Interfaces
│   ├── ModuleRegistryInterface.php
│   └── ModuleMetadataRepositoryInterface.php
├── Infrastructure/            # Database access
│   └── VtigerModuleMetadataRepository.php
├── Services/                  # Application services
│   └── ModuleRegistry.php     # Cached registry
└── VtigerModulesServiceProvider.php
```

## Features

- **Fully Generic**: Works with ALL vtiger modules (Contacts, Accounts, Leads, custom modules)
- **Clean Architecture**: Domain layer has zero Laravel dependencies
- **DDD Principles**: Rich domain objects with behavior
- **Performance**: Aggressive in-memory caching
- **Multi-tenant Ready**: Uses configurable database connection
- **Type Safe**: Full PHP 8.2+ type hints and return types

## Usage

### Basic Usage

```php
use App\Modules\Core\VtigerModules\Contracts\ModuleRegistryInterface;

// Get the registry (injected via DI)
$registry = app(ModuleRegistryInterface::class);

// Get all modules
$modules = $registry->all();

// Get specific module
$contacts = $registry->get('Contacts');

// Access module metadata
echo $contacts->getName();        // "Contacts"
echo $contacts->getLabel();       // "LBL_CONTACTS"
echo $contacts->getBaseTable();   // "vtiger_contactdetails"
echo $contacts->isActive();       // true
```

### Working with Fields

```php
$contacts = $registry->get('Contacts');

// Get all fields
$fields = $contacts->fields();

// Get specific field
$emailField = $contacts->getField('email');

// Field metadata
echo $emailField->getColumnName();  // "email"
echo $emailField->getTableName();   // "vtiger_contactdetails"
echo $emailField->getUitype();      // 13 (email type)
echo $emailField->isMandatory();    // false
echo $emailField->isCustomField();  // false

// Filter fields
$customFields = $contacts->getCustomFields();
$visibleFields = $contacts->getVisibleFields();
$editableFields = $contacts->getEditableFields();
```

### Working with Blocks

```php
$contacts = $registry->get('Contacts');

// Get all blocks
$blocks = $contacts->blocks();

// Get fields grouped by block
$fieldsByBlock = $contacts->getFieldsByBlock();

foreach ($blocks as $block) {
    echo $block->getLabel();     // "LBL_CONTACT_INFORMATION"
    echo $block->getSequence();  // Display order
    echo $block->isVisible();    // true
}
```

### Working with Relations

```php
$contacts = $registry->get('Contacts');

// Get all relations
$relations = $contacts->relations();

// Get specific relation
$potentialsRelation = $contacts->getRelation('Potentials');

// Relation metadata
echo $potentialsRelation->getTargetModule();  // "Potentials"
echo $potentialsRelation->getRelationType();  // "1:N"
echo $potentialsRelation->isOneToMany();      // true
echo $potentialsRelation->canAdd();           // true
```

### Filtering Modules

```php
// Get only active modules
$activeModules = $registry->getActive();

// Get only custom modules
$customModules = $registry->getCustomModules();

// Get only standard modules
$standardModules = $registry->getStandardModules();

// Custom filter
$entityModules = $registry->filter(fn($m) => $m->isEntity());
```

### Cache Management

```php
// Refresh cache (reload from database)
$registry->refresh();
```

## Configuration

Configuration file: `config/vtiger-modules.php`

```php
return [
    // Database connection (usually 'tenant' for multi-tenant)
    'connection' => env('VTIGER_MODULES_CONNECTION', 'tenant'),
    
    // Cache TTL in seconds
    'cache_ttl' => env('VTIGER_MODULES_CACHE_TTL', 3600),
    
    // Modules to exclude
    'excluded_modules' => ['Migration', 'ModComments'],
    
    // Load relations automatically
    'load_relations' => true,
];
```

## Testing

Test routes are available at `/test/modules/*` (requires authentication):

```bash
# List all modules
GET /test/modules

# Get specific module details
GET /test/modules/Contacts

# Get custom fields for a module
GET /test/modules/Contacts/custom-fields

# Refresh cache
POST /test/modules/refresh
```

## Integration Examples

### Dynamic Form Generator

```php
$contacts = $registry->get('Contacts');

foreach ($contacts->getFieldsByBlock() as $blockId => $fields) {
    $block = $contacts->getBlock($blockId);
    echo "<h3>{$block->getLabel()}</h3>";
    
    foreach ($fields as $field) {
        if ($field->isVisible() && $field->isEditable()) {
            echo "<label>{$field->getLabel()}</label>";
            echo "<input name='{$field->getFieldName()}' 
                         type='{$field->getFieldType()}'
                         " . ($field->isMandatory() ? 'required' : '') . ">";
        }
    }
}
```

### Generic CRUD Controller

```php
class GenericModuleController
{
    public function __construct(
        private ModuleRegistryInterface $registry
    ) {}
    
    public function create(string $module)
    {
        $moduleDefinition = $this->registry->get($module);
        $fields = $moduleDefinition->getEditableFields();
        
        return view('generic.create', compact('moduleDefinition', 'fields'));
    }
}
```

### API Endpoint Generator

```php
Route::get('/api/modules/{module}/fields', function (
    string $module,
    ModuleRegistryInterface $registry
) {
    $moduleDefinition = $registry->get($module);
    
    return response()->json([
        'fields' => $moduleDefinition->fields()->map(fn($f) => [
            'name' => $f->getFieldName(),
            'label' => $f->getLabel(),
            'type' => $f->getFieldType(),
            'required' => $f->isMandatory(),
        ]),
    ]);
});
```

## Vtiger Tables Reference

The engine reads from these vtiger tables:

- **vtiger_tab**: Module definitions
- **vtiger_field**: Field metadata
- **vtiger_blocks**: Field groupings
- **vtiger_relatedlists**: Module relationships
- **vtiger_entityname**: Base table information
- **vtiger_fieldmodulerel**: Lookup field relations

## Performance

- **First call**: ~50-200ms (database queries)
- **Cached calls**: <1ms (in-memory)
- **Memory usage**: ~2-5MB for all modules

## Future Enhancements

- [ ] Laravel cache integration (Redis/Memcached)
- [ ] Event-driven cache invalidation
- [ ] Field validation rule extraction
- [ ] Picklist value loading
- [ ] Workflow/automation metadata
- [ ] Permission/profile integration
