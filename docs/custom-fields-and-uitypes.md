# Custom Fields and UI Types in vTiger CRM

## Table of Contents
1. [Overview](#overview)
2. [Database Schema](#database-schema)
3. [Custom Fields Architecture](#custom-fields-architecture)
4. [UI Types System](#ui-types-system)
5. [Field Generation Types](#field-generation-types)
6. [Creating Custom Fields](#creating-custom-fields)
7. [UI Type Classes](#ui-type-classes)
8. [Code Examples](#code-examples)

---

## Overview

vTiger CRM uses a flexible **Entity-Attribute-Value (EAV)** pattern combined with a **UI Type system** to manage both standard and custom fields. This architecture allows dynamic field creation without modifying the core database schema.

### Key Concepts:
- **Custom Fields**: User-defined fields added to modules at runtime
- **UI Types**: Define how fields are rendered and processed (text, picklist, reference, etc.)
- **Generated Types**: Distinguish between system fields (1) and custom fields (2)
- **Field Metadata**: Stored in `vtiger_field` table
- **Field Values**: Stored in module-specific custom field tables (e.g., `vtiger_accountscf`, `vtiger_contactscf`)

---

## Database Schema

### Core Tables

#### 1. `vtiger_field` - Field Metadata
Stores all field definitions for all modules.

```sql
CREATE TABLE vtiger_field (
    tabid INT(19) NOT NULL,                    -- Module ID (FK to vtiger_tab)
    fieldid INT(19) PRIMARY KEY AUTO_INCREMENT, -- Unique field identifier
    columnname VARCHAR(30) NOT NULL,            -- Database column name
    tablename VARCHAR(50) NOT NULL,             -- Table where value is stored
    generatedtype INT(19) NOT NULL DEFAULT 0,   -- 1=System field, 2=Custom field
    uitype VARCHAR(30) NOT NULL,                -- UI Type (1-117+)
    fieldname VARCHAR(50) NOT NULL,             -- Internal field name
    fieldlabel VARCHAR(50) NOT NULL,            -- Display label
    readonly INT(1) NOT NULL,                   -- Is field read-only
    presence INT(19) NOT NULL DEFAULT 1,        -- 0=visible, 1=hidden, 2=visible
    defaultvalue TEXT,                          -- Default value
    maximumlength INT(19),                      -- Max length for input
    sequence INT(19),                           -- Display order
    block INT(19),                              -- Block ID (FK to vtiger_blocks)
    displaytype INT(19),                        -- 1=editable, 2=readonly, 3=hidden, 4=password, 5=locked, 6=calculated
    typeofdata VARCHAR(100),                    -- Validation rules (e.g., 'V~M' = varchar mandatory)
    quickcreate INT(10) NOT NULL DEFAULT 1,     -- 0=mandatory, 1=not enabled, 2=enabled
    quickcreatesequence INT(19),                -- Order in quick create
    info_type VARCHAR(20),                      -- BAS, ADV, etc.
    masseditable INT(10) NOT NULL DEFAULT 1,    -- Can be mass edited
    
    INDEX field_tabid_idx (tabid),
    INDEX field_fieldname_idx (fieldname),
    INDEX field_block_idx (block),
    INDEX field_displaytype_idx (displaytype),
    
    CONSTRAINT fk_1_vtiger_field FOREIGN KEY (tabid) 
        REFERENCES vtiger_tab(tabid) ON DELETE CASCADE
) ENGINE=InnoDB;
```

#### 2. Custom Field Tables (e.g., `vtiger_accountscf`, `vtiger_contactscf`)
Each module has a corresponding custom fields table.

```sql
-- Example: Accounts Custom Fields
CREATE TABLE vtiger_accountscf (
    accountid INT(19) PRIMARY KEY DEFAULT 0,
    -- Custom columns added dynamically when fields are created
    -- e.g., cf_1234 VARCHAR(255),
    -- e.g., cf_5678 TEXT,
    
    CONSTRAINT fk_1_vtiger_accountscf FOREIGN KEY (accountid) 
        REFERENCES vtiger_account(accountid) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Example: Contacts Custom Fields
CREATE TABLE vtiger_contactscf (
    contactid INT(19) PRIMARY KEY DEFAULT 0,
    -- Custom columns added dynamically
    
    CONSTRAINT fk_1_vtiger_contactscf FOREIGN KEY (contactid) 
        REFERENCES vtiger_contactdetails(contactid) ON DELETE CASCADE
) ENGINE=InnoDB;
```

#### 3. `vtiger_blocks` - Field Grouping
Fields are organized into blocks for UI layout.

```sql
CREATE TABLE vtiger_blocks (
    blockid INT(19) PRIMARY KEY NOT NULL,
    tabid INT(19) NOT NULL,                    -- Module ID
    blocklabel VARCHAR(100) NOT NULL,          -- Block label
    sequence INT(10),                          -- Display order
    show_title INT(2),                         -- Show block title
    visible INT(2) NOT NULL DEFAULT 0,         -- Is visible
    create_view INT(2) NOT NULL DEFAULT 0,     -- Show in create view
    edit_view INT(2) NOT NULL DEFAULT 0,       -- Show in edit view
    detail_view INT(2) NOT NULL DEFAULT 0,     -- Show in detail view
    display_status INT(1) NOT NULL DEFAULT 1,  -- Display status
    iscustom INT(1) NOT NULL DEFAULT 0,        -- Is custom block
    
    INDEX block_tabid_idx (tabid),
    
    CONSTRAINT fk_1_vtiger_blocks FOREIGN KEY (tabid) 
        REFERENCES vtiger_tab(tabid) ON DELETE CASCADE
) ENGINE=InnoDB;
```

---

## Custom Fields Architecture

### How Custom Fields Work

1. **Field Definition**: Metadata stored in `vtiger_field` with `generatedtype = 2`
2. **Column Creation**: Physical column added to module's custom field table (e.g., `vtiger_accountscf`)
3. **Value Storage**: Field values stored in the custom field table, linked by entity ID
4. **UI Rendering**: UI Type classes handle display and input rendering
5. **Validation**: `typeofdata` column defines validation rules

### Custom Field Table Naming Convention

```
vtiger_{modulename}cf
```

Examples:
- Accounts: `vtiger_accountscf`
- Contacts: `vtiger_contactscf`
- Leads: `vtiger_leadscf`
- Potentials: `vtiger_potentialscf`

### Column Naming Convention

Custom field columns typically use the format:
```
cf_{fieldid}
```

Example: `cf_1234` for field with `fieldid = 1234`

---

## UI Types System

UI Types define how fields behave, render, and validate. Each UI Type has a corresponding class in `modules/Vtiger/uitypes/`.

### Common UI Types

| UIType | Name | Description | Example |
|--------|------|-------------|---------|
| 1 | Text | Single-line text input | Account Name |
| 2 | Text | Single-line text (with special handling) | Last Name |
| 5 | Date | Date picker | Birthday |
| 6 | Datetime | Date and time picker | Created Time |
| 7 | Integer | Whole numbers | Employee Count |
| 9 | Percentage | Percentage value | Discount % |
| 10 | Reference | Relation to other modules | Related Account |
| 13 | Email | Email address | Email |
| 14 | Time | Time picker | Start Time |
| 15 | Picklist | Dropdown (role-based) | Industry |
| 16 | Picklist | Dropdown (non-role-based) | Lead Source |
| 17 | URL | Website URL | Website |
| 19 | Text | Text field | Description |
| 21 | Textarea | Multi-line text | Address |
| 33 | Multipicklist | Multiple selection dropdown | Product Categories |
| 50 | Datetime | Date and time | Modified Time |
| 52 | Owner | User/Group assignment | Assigned To |
| 53 | Owner | User assignment only | Created By |
| 55 | Salutation | Name prefix | Mr., Mrs., Dr. |
| 56 | Boolean | Checkbox | Email Opt Out |
| 69 | Image | Image upload | Contact Photo |
| 71 | Currency | Currency value | Annual Revenue |
| 72 | Currency | Currency (with conversion) | Amount |
| 117 | Currency List | Currency selector | Currency |

### UI Type Class Hierarchy

```
Vtiger_Base_UIType (modules/Vtiger/uitypes/Base.php)
    ├── Vtiger_Text_UIType
    ├── Vtiger_Integer_UIType
    ├── Vtiger_Double_UIType
    ├── Vtiger_Currency_UIType
    ├── Vtiger_Date_UIType
    │   ├── Vtiger_Datetime_UIType
    │   ├── Vtiger_Reminder_UIType
    │   └── Vtiger_Recurrence_UIType
    ├── Vtiger_Time_UIType
    ├── Vtiger_Picklist_UIType
    ├── Vtiger_Multipicklist_UIType
    ├── Vtiger_Boolean_UIType
    ├── Vtiger_Reference_UIType
    ├── Vtiger_Owner_UIType
    │   └── Vtiger_Ownergroup_UIType
    ├── Vtiger_Email_UIType
    ├── Vtiger_Phone_UIType
    ├── Vtiger_Url_UIType
    ├── Vtiger_File_UIType
    ├── Vtiger_Image_UIType
    ├── Vtiger_Password_UIType
    └── ... (34 total UI Type classes)
```

---

## Field Generation Types

The `generatedtype` column in `vtiger_field` distinguishes field origins:

### Generation Type Values

| Value | Type | Description | Can Delete? |
|-------|------|-------------|-------------|
| 1 | System Field | Core fields created during module installation | No |
| 2 | Custom Field | User-created fields added via Layout Editor | Yes |

### Code Reference

From `vtlib/Vtiger/FieldBasic.php`:
```php
var $generatedtype = 1; // Default is system field
```

From `modules/Vtiger/models/Field.php`:
```php
public function isCustomField() {
    return ($this->get('generatedtype') == 2) ? true : false;
}
```

### Querying Custom Fields Only

```php
// Get only custom fields for a module
$sql = "SELECT * FROM vtiger_field 
        WHERE tabid = ? 
        AND generatedtype = 2 
        AND vtiger_field.presence IN (0,2)";
```

---

## Creating Custom Fields

### Using vtlib API

```php
<?php
require_once('vtlib/Vtiger/Module.php');

// Get module instance
$moduleInstance = Vtiger_Module::getInstance('Contacts');

// Get or create a block
$block = Vtiger_Block::getInstance('LBL_CONTACT_INFORMATION', $moduleInstance);
if (!$block) {
    $block = new Vtiger_Block();
    $block->label = 'LBL_CONTACT_INFORMATION';
    $moduleInstance->addBlock($block);
}

// Create a text field
$field = new Vtiger_Field();
$field->name = 'custom_field_name';
$field->label = 'Custom Field Label';
$field->table = 'vtiger_contactscf';  // Custom field table
$field->column = 'cf_1234';            // Column name
$field->columntype = 'VARCHAR(255)';   // SQL column type
$field->uitype = 1;                    // Text field
$field->typeofdata = 'V~O';            // Varchar, Optional
$field->generatedtype = 2;             // Custom field
$field->displaytype = 1;               // Editable
$block->addField($field);

// Create a picklist field
$picklistField = new Vtiger_Field();
$picklistField->name = 'custom_picklist';
$picklistField->label = 'Custom Picklist';
$picklistField->table = 'vtiger_contactscf';
$picklistField->column = 'cf_5678';
$picklistField->columntype = 'VARCHAR(200)';
$picklistField->uitype = 15;           // Role-based picklist
$picklistField->typeofdata = 'V~O';
$picklistField->generatedtype = 2;
$block->addField($picklistField);

// Set picklist values
$picklistField->setPicklistValues(['Option 1', 'Option 2', 'Option 3']);

// Create a reference field (relation)
$referenceField = new Vtiger_Field();
$referenceField->name = 'custom_account';
$referenceField->label = 'Related Account';
$referenceField->table = 'vtiger_contactscf';
$referenceField->column = 'cf_9999';
$referenceField->columntype = 'INT(19)';
$referenceField->uitype = 10;          // Reference field
$referenceField->typeofdata = 'V~O';
$referenceField->generatedtype = 2;
$block->addField($referenceField);

// Set related modules
$referenceField->setRelatedModules(['Accounts']);
```

### Field Validation Types (`typeofdata`)

Format: `{DataType}~{Mandatory}~{AdditionalRules}`

| Code | Description |
|------|-------------|
| V~M | Varchar, Mandatory |
| V~O | Varchar, Optional |
| I~M | Integer, Mandatory |
| I~O | Integer, Optional |
| N~M | Number (decimal), Mandatory |
| N~O | Number (decimal), Optional |
| E~M | Email, Mandatory |
| E~O | Email, Optional |
| D~M | Date, Mandatory |
| D~O | Date, Optional |
| DT~M | Datetime, Mandatory |
| DT~O | Datetime, Optional |
| T~M | Time, Mandatory |
| T~O | Time, Optional |
| C~M | Checkbox (boolean), Mandatory |
| C~O | Checkbox (boolean), Optional |

---

## UI Type Classes

### Base UI Type Class

Location: `modules/Vtiger/uitypes/Base.php`

```php
class Vtiger_Base_UIType extends Vtiger_Base_Model {
    
    /**
     * Get template name for rendering
     */
    public function getTemplateName() {
        return 'uitypes/String.tpl';
    }
    
    /**
     * Convert user input to DB format
     */
    public function getDBInsertValue($value) {
        return $value;
    }
    
    /**
     * Get user request value
     */
    public function getUserRequestValue($value) {
        return $value;
    }
    
    /**
     * Get display value for detail view
     */
    public function getDisplayValue($value, $record=false, $recordInstance=false) {
        return $value;
    }
    
    /**
     * Get display value for edit view
     */
    public function getEditViewDisplayValue($value) {
        return $value;
    }
    
    /**
     * Get detail view template name
     */
    public function getDetailViewTemplateName() {
        return 'uitypes/StringDetailView.tpl';
    }
    
    /**
     * Get display value for related list
     */
    public function getRelatedListDisplayValue($value) {
        return $this->getDisplayValue($value);
    }
    
    /**
     * Get list search template name
     */
    public function getListSearchTemplateName() {
        return 'uitypes/FieldSearchView.tpl';
    }
    
    /**
     * Factory method to get UI Type instance from field
     */
    public static function getInstanceFromField($fieldModel) {
        $fieldDataType = $fieldModel->getFieldDataType();
        $uiTypeClassSuffix = ucfirst($fieldDataType);
        $moduleName = $fieldModel->getModuleName();
        
        // Try module-specific UI Type class
        $moduleSpecificUiTypeClassName = $moduleName.'_'.$uiTypeClassSuffix.'_UIType';
        $moduleSpecificFileName = 'modules.'. $moduleName .'.uitypes.'.$uiTypeClassSuffix;
        $moduleSpecificFilePath = Vtiger_Loader::resolveNameToPath($moduleSpecificFileName);
        
        if(file_exists($moduleSpecificFilePath)) {
            $instance = new $moduleSpecificUiTypeClassName();
        }
        // Try generic UI Type class
        else {
            $uiTypeClassName = 'Vtiger_'.$uiTypeClassSuffix.'_UIType';
            $uiTypeClassFileName = 'modules.Vtiger.uitypes.'.$uiTypeClassSuffix;
            $completeFilePath = Vtiger_Loader::resolveNameToPath($uiTypeClassFileName);
            
            if(file_exists($completeFilePath)) {
                $instance = new $uiTypeClassName();
            } else {
                $instance = new Vtiger_Base_UIType();
            }
        }
        
        $instance->set('field', $fieldModel);
        return $instance;
    }
}
```

### Example: Picklist UI Type

Location: `modules/Vtiger/uitypes/Picklist.php`

```php
class Vtiger_Picklist_UIType extends Vtiger_Base_UIType {
    
    public function getTemplateName() {
        return 'uitypes/Picklist.tpl';
    }
    
    public function getDisplayValue($value) {
        // Translate picklist value
        return Vtiger_Language_Handler::getTranslatedString(
            $value, 
            $this->get('field')->getModuleName()
        );
    }
    
    public function getListSearchTemplateName() {
        return 'uitypes/PickListFieldSearchView.tpl';
    }
}
```

### Example: Reference UI Type

Location: `modules/Vtiger/uitypes/Reference.php`

```php
class Vtiger_Reference_UIType extends Vtiger_Base_UIType {
    
    public function getTemplateName() {
        return 'uitypes/Reference.tpl';
    }
    
    public function getReferenceModule($value) {
        $fieldModel = $this->get('field');
        $referenceModuleList = $fieldModel->getReferenceList();
        $referenceEntityType = getSalesEntityType($value);
        
        if(in_array($referenceEntityType, $referenceModuleList)) {
            return Vtiger_Module_Model::getInstance($referenceEntityType);
        } elseif (in_array('Users', $referenceModuleList)) {
            return Vtiger_Module_Model::getInstance('Users');
        }
        return null;
    }
    
    public function getDisplayValue($value) {
        $referenceModule = $this->getReferenceModule($value);
        
        if($referenceModule && !empty($value)) {
            $referenceModuleName = $referenceModule->get('name');
            
            if($referenceModuleName == 'Users') {
                $db = PearDatabase::getInstance();
                $nameResult = $db->pquery(
                    'SELECT first_name, last_name FROM vtiger_users WHERE id = ?', 
                    array($value)
                );
                if($db->num_rows($nameResult)) {
                    return $db->query_result($nameResult, 0, 'first_name').' '.
                           $db->query_result($nameResult, 0, 'last_name');
                }
            } else {
                $entityNames = getEntityName($referenceModuleName, array($value));
                $linkValue = "<a href='index.php?module=$referenceModuleName&view=".
                            $referenceModule->getDetailViewName()."&record=$value' ".
                            "title='".vtranslate($fieldModel->get('label'), $referenceModuleName).
                            ": ". $entityNames[$value] ."'>$entityNames[$value]</a>";
                return $linkValue;
            }
        }
        return '';
    }
    
    public function getEditViewDisplayValue($value) {
        $referenceModule = $this->getReferenceModule($value);
        if($referenceModule) {
            $referenceModuleName = $referenceModule->get('name');
            $entityNames = getEntityName($referenceModuleName, array($value));
            return $entityNames[$value];
        }
        return '';
    }
}
```

### Creating Module-Specific UI Types

You can override UI Type behavior for specific modules:

Location: `modules/Contacts/uitypes/Picklist.php`

```php
class Contacts_Picklist_UIType extends Vtiger_Picklist_UIType {
    
    public function getDisplayValue($value) {
        // Custom display logic for Contacts module
        $displayValue = parent::getDisplayValue($value);
        
        // Add custom formatting
        return '<strong>' . $displayValue . '</strong>';
    }
}
```

---

## Code Examples

### 1. Getting Field Information

```php
<?php
// Get field by name
$fieldModel = Vtiger_Field_Model::getInstance('firstname', 
    Vtiger_Module_Model::getInstance('Contacts'));

// Get field properties
$uitype = $fieldModel->get('uitype');
$label = $fieldModel->get('label');
$isCustom = $fieldModel->isCustomField();
$isMandatory = $fieldModel->isMandatory();
$isEditable = $fieldModel->isEditable();

// Get UI Type instance
$uiTypeInstance = $fieldModel->getUITypeModel();

// Get display value
$displayValue = $fieldModel->getDisplayValue($rawValue, $recordId);
```

### 2. Working with Custom Field Values

```php
<?php
// Save custom field value
$recordModel = Vtiger_Record_Model::getInstanceById($recordId, 'Contacts');
$recordModel->set('cf_1234', 'Custom Value');
$recordModel->save();

// Get custom field value
$value = $recordModel->get('cf_1234');

// Get display value
$fieldModel = $recordModel->getField('cf_1234');
$displayValue = $fieldModel->getDisplayValue($value, $recordId, $recordModel);
```

### 3. Querying Custom Fields

```php
<?php
global $adb;

// Get all custom fields for a module
$tabId = getTabid('Contacts');
$result = $adb->pquery(
    "SELECT * FROM vtiger_field 
     WHERE tabid = ? 
     AND generatedtype = 2 
     AND vtiger_field.presence IN (0,2)
     ORDER BY sequence",
    array($tabId)
);

while($row = $adb->fetchByAssoc($result)) {
    $fieldName = $row['fieldname'];
    $columnName = $row['columnname'];
    $uitype = $row['uitype'];
    $label = $row['fieldlabel'];
    
    echo "Field: $fieldName (UIType: $uitype) - $label\n";
}
```

### 4. Creating Picklist with Colors

```php
<?php
require_once('vtlib/Vtiger/Module.php');

$moduleInstance = Vtiger_Module::getInstance('Contacts');
$block = Vtiger_Block::getInstance('LBL_CONTACT_INFORMATION', $moduleInstance);

$field = new Vtiger_Field();
$field->name = 'priority_level';
$field->label = 'Priority Level';
$field->table = 'vtiger_contactscf';
$field->column = 'cf_priority';
$field->columntype = 'VARCHAR(200)';
$field->uitype = 15;
$field->typeofdata = 'V~O';
$field->generatedtype = 2;
$block->addField($field);

// Set picklist values with colors
$field->setPicklistValues([
    ['High', '#FF0000'],      // Red
    ['Medium', '#FFA500'],    // Orange
    ['Low', '#00FF00']        // Green
]);
```

### 5. Field Model Usage in Controllers

```php
<?php
class Contacts_Edit_View extends Vtiger_Edit_View {
    
    public function process(Vtiger_Request $request) {
        $recordId = $request->get('record');
        $moduleName = $request->getModule();
        
        if($recordId) {
            $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
        } else {
            $recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
        }
        
        // Get all fields
        $moduleModel = $recordModel->getModule();
        $fieldList = $moduleModel->getFields();
        
        foreach($fieldList as $fieldName => $fieldModel) {
            $uitype = $fieldModel->get('uitype');
            $isCustom = $fieldModel->isCustomField();
            
            // Get UI Type instance for rendering
            $uiTypeInstance = $fieldModel->getUITypeModel();
            
            // Get edit view display value
            $value = $recordModel->get($fieldName);
            $displayValue = $uiTypeInstance->getEditViewDisplayValue($value);
            
            // Assign to template
            $viewer->assign('FIELD_MODEL', $fieldModel);
            $viewer->assign('FIELD_VALUE', $displayValue);
        }
        
        parent::process($request);
    }
}
```

### 6. Accessing Custom Field Tables

```php
<?php
global $adb;

// Direct query to custom field table
$contactId = 123;
$result = $adb->pquery(
    "SELECT cf_1234, cf_5678 
     FROM vtiger_contactscf 
     WHERE contactid = ?",
    array($contactId)
);

if($adb->num_rows($result) > 0) {
    $customField1 = $adb->query_result($result, 0, 'cf_1234');
    $customField2 = $adb->query_result($result, 0, 'cf_5678');
}

// Join with main table
$result = $adb->pquery(
    "SELECT 
        c.firstname, 
        c.lastname, 
        cf.cf_1234 as custom_field
     FROM vtiger_contactdetails c
     INNER JOIN vtiger_crmentity ce ON c.contactid = ce.crmid
     LEFT JOIN vtiger_contactscf cf ON c.contactid = cf.contactid
     WHERE ce.deleted = 0 
     AND c.contactid = ?",
    array($contactId)
);
```

### 7. Validating Custom Fields

```php
<?php
// In save action
class Contacts_Save_Action extends Vtiger_Save_Action {
    
    public function process(Vtiger_Request $request) {
        $moduleName = $request->getModule();
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        
        // Get all fields
        $fieldList = $moduleModel->getFields();
        
        foreach($fieldList as $fieldName => $fieldModel) {
            $value = $request->get($fieldName);
            
            // Validate based on typeofdata
            $typeOfData = $fieldModel->get('typeofdata');
            list($dataType, $mandatory) = explode('~', $typeOfData);
            
            if($mandatory == 'M' && empty($value)) {
                throw new Exception(
                    vtranslate($fieldModel->get('label'), $moduleName) . 
                    ' is mandatory'
                );
            }
            
            // Get UI Type instance for validation
            $uiTypeInstance = $fieldModel->getUITypeModel();
            
            // Convert to DB format
            $dbValue = $uiTypeInstance->getDBInsertValue($value);
            
            // Set in request
            $request->set($fieldName, $dbValue);
        }
        
        parent::process($request);
    }
}
```

---

## Best Practices

### 1. Always Use Field Models
```php
// ❌ Bad - Direct database access
$value = $adb->query_result($result, 0, 'cf_1234');

// ✅ Good - Use field model
$fieldModel = $recordModel->getField('custom_field_name');
$value = $fieldModel->getDisplayValue($rawValue, $recordId);
```

### 2. Check Field Type Before Processing
```php
$uitype = $fieldModel->get('uitype');

if($uitype == 10) {
    // Handle reference field
    $referenceModules = $fieldModel->getReferenceList();
} elseif($uitype == 15 || $uitype == 16) {
    // Handle picklist
    $picklistValues = $fieldModel->getPicklistValues();
}
```

### 3. Use UI Type Instances for Display
```php
$uiTypeInstance = $fieldModel->getUITypeModel();
$displayValue = $uiTypeInstance->getDisplayValue($value, $recordId, $recordModel);
```

### 4. Respect generatedtype
```php
// Only allow deletion of custom fields
if($fieldModel->isCustomField()) {
    // generatedtype == 2
    // Safe to delete
} else {
    // generatedtype == 1
    // System field - do not delete
}
```

### 5. Use vtlib for Field Creation
```php
// ✅ Good - Use vtlib API
$field = new Vtiger_Field();
$field->name = 'custom_field';
// ... set properties
$block->addField($field);

// ❌ Bad - Direct SQL insert
$adb->pquery("INSERT INTO vtiger_field ...");
```

---

## Summary

- **Custom Fields** use EAV pattern with metadata in `vtiger_field` and values in `vtiger_{module}cf` tables
- **UI Types** define field behavior through specialized classes in `modules/Vtiger/uitypes/`
- **generatedtype** distinguishes system fields (1) from custom fields (2)
- Always use **Field Models** and **UI Type instances** for proper abstraction
- Use **vtlib API** for creating and managing custom fields
- Custom fields provide flexibility without modifying core schema

This architecture allows vTiger to be highly customizable while maintaining data integrity and code organization.
