# Inventory & User Preferences - Deep Analysis

**Generated:** 2026-02-04  
**Project:** TenantCRM (Vtiger CRM)  
**Version:** 7.x

---

## Table of Contents

1. [Executive Summary](#executive-summary)
2. [Inventory Settings](#inventory-settings)
   - [Tax Management](#tax-management)
   - [Terms and Conditions](#terms-and-conditions)
3. [My Preferences](#my-preferences)
   - [User Preferences](#user-preferences)
   - [Calendar Settings](#calendar-settings)
   - [My Tags](#my-tags)
4. [Database Schema](#database-schema)
5. [Integration & Usage](#integration--usage)
6. [Best Practices](#best-practices)

---

## 1. Executive Summary

This document analyzes three critical configuration areas in TenantCRM:

| Category | Components | Purpose | Key Features |
|----------|-----------|---------|--------------|
| **Inventory Settings** | Tax Management, Terms & Conditions | Configure taxes and legal terms for inventory modules | Product/Shipping taxes, Module-specific T&C |
| **My Preferences** | User Settings | Personalize user experience | Language, timezone, date format |
| **Calendar Settings** | Calendar Configuration | Configure calendar behavior | Hour format, day start, activity settings |
| **My Tags** | Tag Management | Personal tag organization | Create, edit, delete user tags |

---

## 2. Inventory Settings

### 2.1 Tax Management

#### Overview

**Purpose:** Manage tax configurations for Products, Services, and Shipping & Handling.

**Location:** `modules/Settings/Vtiger/models/TaxRecord.php`

#### Tax Types

```php
class Settings_Vtiger_TaxRecord_Model {
    const PRODUCT_AND_SERVICE_TAX = 0;
    const SHIPPING_AND_HANDLING_TAX = 1;
}
```

| Tax Type | ID | Applied To | Table | Column Prefix |
|----------|----|-----------| ------|---------------|
| **Product & Service** | 0 | Products, Services | `vtiger_inventorytaxinfo` | `tax{id}` |
| **Shipping & Handling** | 1 | Shipping charges | `vtiger_shippingtaxinfo` | `shtax{id}` |

#### Tax Record Structure

```php
class Settings_Vtiger_TaxRecord_Model {
    // Properties
    private $type;                  // Tax type (0 or 1)
    
    // Core Methods
    public function getId()         // Get tax ID
    public function getName()       // Get tax label
    public function getTax()        // Get tax percentage
    public function isDeleted()     // Check if deleted
    public function markDeleted()   // Soft delete
    public function unMarkDeleted() // Restore
    public function setType($type)  // Set tax type
    public function getType()       // Get tax type
    public function isProductTax()  // Check if product tax
    public function isShippingTax() // Check if shipping tax
}
```

#### Database Tables

**vtiger_inventorytaxinfo** (Product & Service Taxes)

```sql
CREATE TABLE vtiger_inventorytaxinfo (
    taxid INT PRIMARY KEY,
    taxname VARCHAR(50),            -- Column name (tax1, tax2, etc.)
    taxlabel VARCHAR(50),           -- Display label (VAT, GST, etc.)
    percentage DECIMAL(7,3),        -- Tax rate (e.g., 15.000)
    deleted INT DEFAULT 0           -- Soft delete flag
);
```

**vtiger_shippingtaxinfo** (Shipping & Handling Taxes)

```sql
CREATE TABLE vtiger_shippingtaxinfo (
    taxid INT PRIMARY KEY,
    taxname VARCHAR(50),            -- Column name (shtax1, shtax2, etc.)
    taxlabel VARCHAR(50),           -- Display label
    percentage DECIMAL(7,3),        -- Tax rate
    deleted INT DEFAULT 0           -- Soft delete flag
);
```

**vtiger_inventoryproductrel** (Product Line Items)

```sql
CREATE TABLE vtiger_inventoryproductrel (
    id INT PRIMARY KEY AUTO_INCREMENT,
    productid INT,
    quantity DECIMAL(25,3),
    listprice DECIMAL(28,8),
    -- Dynamic tax columns added per tax
    tax1 DECIMAL(7,3),              -- First product tax
    tax2 DECIMAL(7,3),              -- Second product tax
    tax3 DECIMAL(7,3),              -- Third product tax
    -- More tax columns added dynamically
    ...
);
```

**vtiger_inventoryshippingrel** (Shipping Charges)

```sql
CREATE TABLE vtiger_inventoryshippingrel (
    id INT PRIMARY KEY AUTO_INCREMENT,
    shipping_handling_charge DECIMAL(25,3),
    -- Dynamic shipping tax columns
    shtax1 DECIMAL(7,3),            -- First shipping tax
    shtax2 DECIMAL(7,3),            -- Second shipping tax
    shtax3 DECIMAL(7,3),            -- Third shipping tax
    -- More shipping tax columns added dynamically
    ...
);
```

#### Add Tax Process

```php
public function addTax() {
    $adb = PearDatabase::getInstance();

    $tableName = $this->getTableNameFromType();
    $taxid = $adb->getUniqueID($tableName);
    $taxLabel = $this->getName();
    $percentage = $this->get('percentage');
    
    // 1. Add column to inventory table
    if($this->isShippingTax()) {
        $taxname = "shtax".$taxid;
        $query = "ALTER TABLE vtiger_inventoryshippingrel 
                  ADD COLUMN $taxname decimal(7,3) DEFAULT NULL";
    } else {
        $taxname = "tax".$taxid;
        $query = "ALTER TABLE vtiger_inventoryproductrel 
                  ADD COLUMN $taxname decimal(7,3) DEFAULT NULL";
    }
    $res = $adb->pquery($query, array());
    
    // 2. Add field to inventory modules (for product taxes only)
    if ($this->isProductTax()) {
        $inventoryModules = getInventoryModules();
        
        foreach ($inventoryModules as $moduleName) {
            $moduleInstance = Vtiger_Module::getInstance($moduleName);
            $blockInstance = Vtiger_Block::getInstance('LBL_ITEM_DETAILS', $moduleInstance);
            
            $field = new Vtiger_Field();
            $field->name = $taxname;
            $field->label = $taxLabel;
            $field->column = $taxname;
            $field->table = 'vtiger_inventoryproductrel';
            $field->uitype = '83';              // Tax field type
            $field->typeofdata = 'V~O';         // Optional varchar
            $field->readonly = '0';
            $field->displaytype = '5';          // Not displayed in UI
            $field->masseditable = '0';
            
            $blockInstance->addField($field);
        }
    }

    // 3. Insert tax record
    if($res) {
        $query = 'INSERT INTO '.$tableName.' values(?,?,?,?,?)';
        $params = array($taxid, $taxname, $taxLabel, $percentage, 0);
        $adb->pquery($query, $params);
        return $taxid;
    }
    
    throw new Error('Error occurred while adding tax');
}
```

#### Save Tax

```php
public function save() {
    $db = PearDatabase::getInstance();
    
    $tablename = $this->getTableNameFromType();
    $taxId = $this->getId();
    
    if(!empty($taxId)) {
        // Update existing tax
        $deleted = 0;
        if($this->isDeleted()) {
            $deleted = 1;
        }
        
        $query = 'UPDATE '.$tablename.' 
                  SET taxlabel=?, percentage=?, deleted=? 
                  WHERE taxid=?';
        $params = array(
            $this->getName(),
            $this->get('percentage'),
            $deleted,
            $taxId
        );
        $db->pquery($query, $params);
    } else {
        // Add new tax
        $taxId = $this->addTax();   
    }
    
    return $taxId;
}
```

#### Get Taxes

```php
// Get all product taxes
public static function getProductTaxes() {
    vimport('~~/include/utils/InventoryUtils.php');
    $taxes = getAllTaxes();
    
    $recordList = array();
    foreach($taxes as $taxInfo) {
        $taxRecord = new self();
        $taxRecord->setData($taxInfo)
                  ->setType(self::PRODUCT_AND_SERVICE_TAX);
        $recordList[] = $taxRecord;
    }
    
    return $recordList;
}

// Get all shipping taxes
public static function getShippingTaxes() {
    vimport('~~/include/utils/InventoryUtils.php');
    $taxes = getAllTaxes('all', 'sh');
    
    $recordList = array();
    foreach($taxes as $taxInfo) {
        $taxRecord = new self();
        $taxRecord->setData($taxInfo)
                  ->setType(self::SHIPPING_AND_HANDLING_TAX);
        $recordList[] = $taxRecord;
    }
    
    return $recordList;
}

// Get tax by ID
public static function getInstanceById($id, $type = self::PRODUCT_AND_SERVICE_TAX) {
    $db = PearDatabase::getInstance();
    $tablename = 'vtiger_inventorytaxinfo';
    
    if($type == self::SHIPPING_AND_HANDLING_TAX) {
        $tablename = 'vtiger_shippingtaxinfo';
    }
    
    $query = 'SELECT * FROM '.$tablename.' WHERE taxid=?';
    $result = $db->pquery($query, array($id));
    
    $taxRecordModel = new self();
    if($db->num_rows($result) > 0) {
        $row = $db->query_result_rowdata($result, 0);
        $taxRecordModel->setData($row)->setType($type);
    }
    
    return $taxRecordModel;
}
```

#### Check Duplicate Tax

```php
public static function checkDuplicate($label, $excludedIds = array(), $type = self::PRODUCT_AND_SERVICE_TAX) {
    $db = PearDatabase::getInstance();
    
    if(!is_array($excludedIds)) {
        if(!empty($excludedIds)){
            $excludedIds = array($excludedIds);
        } else {
            $excludedIds = array();
        }
    }
    
    $tablename = 'vtiger_inventorytaxinfo';
    
    if($type == self::SHIPPING_AND_HANDLING_TAX) {
        $tablename = 'vtiger_shippingtaxinfo';
    }
    
    $query = 'SELECT 1 FROM '.$tablename.' WHERE taxlabel = ?';
    $params = array($label);

    if (!empty($excludedIds)) {
        $query .= " AND taxid NOT IN (". generateQuestionMarks($excludedIds). ")";
        $params = array_merge($params, $excludedIds);
    }
    
    $result = $db->pquery($query, $params);
    return ($db->num_rows($result) > 0) ? true : false;
}
```

#### Tax Calculation Example

```php
// Example: Calculate total with taxes
$productPrice = 100.00;
$quantity = 2;
$subtotal = $productPrice * $quantity;  // 200.00

// Apply product taxes
$tax1 = 10.000;  // 10% VAT
$tax2 = 5.000;   // 5% Sales Tax

$tax1Amount = ($subtotal * $tax1) / 100;  // 20.00
$tax2Amount = ($subtotal * $tax2) / 100;  // 10.00

$totalTax = $tax1Amount + $tax2Amount;    // 30.00
$grandTotal = $subtotal + $totalTax;      // 230.00

// Shipping with tax
$shippingCharge = 15.00;
$shtax1 = 8.000;  // 8% Shipping Tax

$shippingTaxAmount = ($shippingCharge * $shtax1) / 100;  // 1.20
$totalShipping = $shippingCharge + $shippingTaxAmount;   // 16.20

$finalTotal = $grandTotal + $totalShipping;  // 246.20
```

#### Inventory Modules Using Taxes

```php
function getInventoryModules() {
    return array(
        'Quotes',
        'SalesOrder',
        'PurchaseOrder',
        'Invoice'
    );
}
```

---

### 2.2 Terms and Conditions

#### Overview

**Purpose:** Manage module-specific terms and conditions for inventory documents.

**Location:** `modules/Settings/Vtiger/models/TermsAndConditions.php`

#### Model Structure

```php
class Settings_Vtiger_TermsAndConditions_Model extends Vtiger_Base_Model {
    
    const tableName = 'vtiger_inventory_tandc';
    
    // Get terms text
    public function getText() {
        return $this->get('tandc');
    }
    
    // Set terms text
    public function setText($text) {
        return $this->set('tandc', $text);
    }
    
    // Get module type
    public function getType() {
        return $this->get('type');
    }

    // Set module type
    public function setType($type) {
        return $this->set('type', $type);
    }
}
```

#### Database Table

**vtiger_inventory_tandc**

```sql
CREATE TABLE vtiger_inventory_tandc (
    id INT PRIMARY KEY,
    type VARCHAR(100),              -- Module name (Quotes, Invoice, etc.)
    tandc TEXT,                     -- Terms and conditions text
    UNIQUE KEY (type)
);
```

#### Save Terms & Conditions

```php
public function save() {
    $db = PearDatabase::getInstance();
    $type = $this->getType();

    // Check if exists
    $query = 'SELECT 1 FROM '.self::tableName.' WHERE type = ?';
    $result = $db->pquery($query, array($type));
    
    if($db->num_rows($result) > 0) {
        // Update existing
        $query = 'UPDATE '.self::tableName.' SET tandc = ? WHERE type = ?';
        $params = array($this->getText(), $type);
    } else {
        // Insert new
        $query = 'INSERT INTO '.self::tableName.' (id,type,tandc) VALUES(?,?,?)';
        $params = array(
            $db->getUniqueID(self::tableName), 
            $type, 
            $this->getText()
        );
    }
    
    $result = $db->pquery($query, $params);
}
```

#### Get Terms & Conditions

```php
public static function getInstance($moduleName) {
    $db = PearDatabase::getInstance();

    $query = 'SELECT tandc FROM '.self::tableName.' WHERE type = ?';
    $result = $db->pquery($query, array($moduleName));
    
    $instance = new self();
    if($db->num_rows($result) > 0) {
        $text = $db->query_result($result, 0, 'tandc');
        $instance->setText($text);
        $instance->setType($moduleName);
    }
    
    return $instance;
}
```

#### Usage Example

```php
// Get terms for Quotes module
$termsModel = Settings_Vtiger_TermsAndConditions_Model::getInstance('Quotes');
$termsText = $termsModel->getText();

// Update terms
$termsModel->setText('Payment due within 30 days. Late fees apply.');
$termsModel->save();
```

#### Module-Specific Terms

Each inventory module can have its own terms and conditions:

| Module | Type Value | Use Case |
|--------|-----------|----------|
| **Quotes** | `Quotes` | Quote validity, pricing terms |
| **Sales Order** | `SalesOrder` | Delivery terms, payment schedule |
| **Purchase Order** | `PurchaseOrder` | Supplier terms, delivery expectations |
| **Invoice** | `Invoice` | Payment terms, late fees |

---

## 3. My Preferences

### 3.1 User Preferences

#### Overview

**Purpose:** Allow users to customize their personal CRM experience.

**Location:** `modules/Users/views/PreferenceDetail.php`

#### Preference Fields

```php
// User preference fields stored in vtiger_users table
$userPreferences = array(
    // Display & Localization
    'language'              => 'text',      // UI language
    'currency_id'           => 'picklist',  // Default currency
    'date_format'           => 'picklist',  // Date display format
    'hour_format'           => 'picklist',  // 12/24 hour format
    'start_hour'            => 'picklist',  // Day start time
    'end_hour'              => 'picklist',   // Day end time
    'time_zone'             => 'picklist',  // User timezone
    
    // Activity Settings
    'activity_view'         => 'picklist',  // Default calendar view
    'callduration'          => 'text',      // Default call duration
    'othereventduration'    => 'text',      // Default event duration
    'reminder_interval'     => 'picklist',  // Activity reminder time
    
    // Calendar Sharing
    'defaultcalendarview'   => 'picklist',  // Calendar default view
    'defaultactivitytype'   => 'picklist',  // Default activity type
    'defaulteventstatus'    => 'picklist',  // Default event status
    
    // UI Preferences
    'rowheight'             => 'picklist',  // List view row height
    'defaultlandingpage'    => 'picklist',  // Landing page module
    'no_of_currency_decimals' => 'text',    // Currency decimal places
    
    // Tag Cloud
    'tagcloud'              => 'checkbox'   // Enable/disable tag cloud
);
```

#### View Structure

```php
class Users_PreferenceDetail_View extends Vtiger_Detail_View {
    
    // Check permission
    public function checkPermission(Vtiger_Request $request) {
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $record = $request->get('record');

        if($currentUserModel->isAdminUser() == true || 
           $currentUserModel->get('id') == $record) {
            return true;
        } else {
            throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
        }
    }
    
    // Get preference detail URL
    public function getPreferenceDetailViewUrl() {
        return 'index.php?module='.$this->getModuleName().
               '&view=PreferenceDetail&parent=Settings&record='.$this->getId();
    }
    
    // Get preference edit URL
    public function getPreferenceEditViewUrl() {
        return 'index.php?module='.$this->getModuleName().
               '&view=PreferenceEdit&parent=Settings&record='.$this->getId();
    }
}
```

#### Date Format Options

```php
$dateFormats = array(
    'yyyy-mm-dd'    => '2026-02-04',
    'mm-dd-yyyy'    => '02-04-2026',
    'dd-mm-yyyy'    => '04-02-2026',
    'yyyy.mm.dd'    => '2026.02.04',
    'mm.dd.yyyy'    => '02.04.2026',
    'dd.mm.yyyy'    => '04.02.2026',
    'yyyy/mm/dd'    => '2026/02/04',
    'mm/dd/yyyy'    => '02/04/2026',
    'dd/mm/yyyy'    => '04/02/2026'
);
```

#### Hour Format Options

```php
$hourFormats = array(
    '12' => '12 Hour (AM/PM)',
    '24' => '24 Hour'
);
```

#### Timezone Options

```php
// Common timezones
$timezones = array(
    'America/New_York'      => 'Eastern Time (US & Canada)',
    'America/Chicago'       => 'Central Time (US & Canada)',
    'America/Denver'        => 'Mountain Time (US & Canada)',
    'America/Los_Angeles'   => 'Pacific Time (US & Canada)',
    'Europe/London'         => 'London',
    'Europe/Paris'          => 'Paris',
    'Asia/Dubai'            => 'Dubai',
    'Asia/Kolkata'          => 'Mumbai, Kolkata',
    'Asia/Tokyo'            => 'Tokyo',
    'Australia/Sydney'      => 'Sydney'
);
```

---

### 3.2 Calendar Settings

#### Overview

**Purpose:** Configure personal calendar behavior and preferences.

**Location:** `modules/Users/views/Calendar.php`

#### Calendar Settings Fields

```php
class Users_Calendar_View extends Vtiger_Detail_View {
    
    // Calendar-specific fields
    $calendarSettings = array(
        // Time Settings
        'hour_format'           => 'picklist',  // 12/24 hour
        'start_hour'            => 'picklist',  // Day start (00:00-23:00)
        'end_hour'              => 'picklist',  // Day end
        
        // Default Values
        'defaultactivitytype'   => 'picklist',  // Call, Meeting, Task
        'defaulteventstatus'    => 'picklist',  // Planned, Held, etc.
        'callduration'          => 'text',      // Minutes
        'othereventduration'    => 'text',      // Minutes
        
        // View Settings
        'activity_view'         => 'picklist',  // Today, This Week, Month
        'defaultcalendarview'   => 'picklist',  // ListView, Calendar
        
        // Reminder
        'reminder_interval'     => 'picklist'   // None, 1 Minute, 5 Minutes, etc.
    );
}
```

#### Calendar View Modes

```php
// Edit mode
public function calendarSettingsEdit(Vtiger_Request $request){
    $viewer = $this->getViewer($request);
    $this->initializeView($viewer, $request);
    $viewer->view('CalendarSettingsEditView.tpl', $request->getModule());
}

// Detail mode
public function calendarSettingsDetail(Vtiger_Request $request){
    $viewer = $this->getViewer($request);
    $this->initializeView($viewer, $request);
    $viewer->view('CalendarSettingsDetailView.tpl', $request->getModule());
}
```

#### Day Start Picklist Values

```php
public static function getDayStartsPicklistValues($stucturedValues){
    $fieldModel = $stucturedValues['LBL_CALENDAR_SETTINGS'];
    $hour_format = $fieldModel['hour_format']->getPicklistValues();
    $start_hour = $fieldModel['start_hour']->getPicklistValues();

    $defaultValues = array(
        '00:00' => '12:00 AM', '01:00' => '01:00 AM', '02:00' => '02:00 AM',
        '03:00' => '03:00 AM', '04:00' => '04:00 AM', '05:00' => '05:00 AM',
        '06:00' => '06:00 AM', '07:00' => '07:00 AM', '08:00' => '08:00 AM',
        '09:00' => '09:00 AM', '10:00' => '10:00 AM', '11:00' => '11:00 AM',
        '12:00' => '12:00 PM', '13:00' => '01:00 PM', '14:00' => '02:00 PM',
        '15:00' => '03:00 PM', '16:00' => '04:00 PM', '17:00' => '05:00 PM',
        '18:00' => '06:00 PM', '19:00' => '07:00 PM', '20:00' => '08:00 PM',
        '21:00' => '09:00 PM', '22:00' => '10:00 PM', '23:00' => '11:00 PM'
    );

    $picklistDependencyData = array();
    foreach ($hour_format as $value) {
        if($value == 24){
            $picklistDependencyData['hour_format'][$value]['start_hour'] = $start_hour;
        } else {
            $picklistDependencyData['hour_format'][$value]['start_hour'] = $defaultValues;
        }
    }
    
    if(empty($picklistDependencyData['hour_format']['__DEFAULT__']['start_hour'])) {
        $picklistDependencyData['hour_format']['__DEFAULT__']['start_hour'] = $defaultValues;
    }
    
    return $picklistDependencyData;
}
```

#### Calendar Sharing

```php
// Get shared users for calendar
$sharedUsers = Calendar_Module_Model::getCaledarSharedUsers($currentUserModel->id);
$sharedType = Calendar_Module_Model::getSharedType($currentUserModel->id);

// Shared types
$sharedTypes = array(
    'public'    => 'Public',        // Visible to all
    'private'   => 'Private',       // Only owner
    'selected'  => 'Selected Users' // Specific users
);
```

#### Activity Reminder Options

```php
$reminderIntervals = array(
    'None'          => 'None',
    '1 Minute'      => '1 Minute',
    '5 Minutes'     => '5 Minutes',
    '15 Minutes'    => '15 Minutes',
    '30 Minutes'    => '30 Minutes',
    '45 Minutes'    => '45 Minutes',
    '1 Hour'        => '1 Hour',
    '1 Day'         => '1 Day'
);
```

#### Get Reminder in Seconds

```php
function getCurrentUserActivityReminderInSeconds() {
    $activityReminder = $this->reminder_interval;
    $activityReminderInSeconds = '';
    
    if($activityReminder != 'None') {
        preg_match('/([0-9]+)[\s]([a-zA-Z]+)/', $activityReminder, $matches);
        
        if($matches) {
            $number = $matches[1];
            $string = $matches[2];
            
            if($string) {
                switch($string) {
                    case 'Minute':
                    case 'Minutes': 
                        $activityReminderInSeconds = $number * 60;
                        break;
                    case 'Hour':
                        $activityReminderInSeconds = $number * 60 * 60;
                        break;
                    case 'Day':
                        $activityReminderInSeconds = $number * 60 * 60 * 24;
                        break;
                    default:
                        $activityReminderInSeconds = '';
                }
            }
        }
    }
    
    return $activityReminderInSeconds;
}
```

#### Calendar Settings URLs

```php
// Detail view URL
public function getCalendarSettingsDetailViewUrl(){
    return 'index.php?module='.$this->getModuleName().
           '&parent=Settings&view=Calendar&record='.$this->getId();
}

// Edit view URL
public function getCalendarSettingsEditViewUrl(){
    return 'index.php?module='.$this->getModuleName().
           '&parent=Settings&view=Calendar&mode=Edit&record='.$this->getId();
}
```

---

### 3.3 My Tags

#### Overview

**Purpose:** Manage personal tags for organizing records.

**Location:** `modules/Settings/Tags/`

#### Tag Model Structure

```php
class Settings_Tags_Record_Model extends Settings_Vtiger_Record_Model {
 
    public function getId() {
        return $this->get('id');
    }
    
    public function getName() {
        return $this->get('tag');
    }
}
```

#### Database Table

**vtiger_freetags**

```sql
CREATE TABLE vtiger_freetags (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tag VARCHAR(50),
    raw_tag VARCHAR(50)
);
```

**vtiger_freetagged_objects**

```sql
CREATE TABLE vtiger_freetagged_objects (
    tag_id INT,
    tagger_id INT,                  -- User ID who tagged
    object_id INT,                  -- Record ID
    tagged_on DATETIME,
    module VARCHAR(50),
    PRIMARY KEY (tag_id, tagger_id, object_id, module)
);
```

#### Tag Operations

```php
// Get record links
public function getRecordLinks() {
    $links = array();
    $recordLinks = array(
        array(
            'linktype' => 'LISTVIEWRECORD',
            'linklabel' => 'LBL_EDIT',
            'linkurl' => 'javascript:Settings_Tags_List_Js.triggerEdit(event)',
            'linkicon' => 'icon-pencil'
        ),
        array(
            'linktype' => 'LISTVIEWRECORD',
            'linklabel' => 'LBL_DELETE',
            'linkurl' => "javascript:Settings_Tags_List_Js.triggerDelete('".$this->getDeleteActionUrl()."')",
            'linkicon' => 'icon-trash'
        )
    );
    
    foreach ($recordLinks as $recordLink) {
        $links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
    }

    return $links;
}

// Get delete URL
public function getDeleteActionUrl() {
    return 'index.php?module=Vtiger&action=TagCloud&mode=remove&tag_id='.$this->getId();
}

// Get row info
public function getRowInfo() {
    return $this->getData();
}
```

#### My Tags Settings URL

```php
public function getMyTagSettingsListUrl() {
    return 'index.php?module=Tags&parent=Settings&view=List&record='.$this->getId();
}
```

#### Tag Cloud

```php
// Check tag cloud status
function getTagCloudStatus() {
    $db = PearDatabase::getInstance();
    $query = "SELECT visible FROM vtiger_homestuff 
              WHERE userid=? AND stufftype='Tag Cloud'";
    $visibility = $db->query_result(
        $db->pquery($query, array($this->getId())), 
        0, 
        'visible'
    );
    
    if($visibility == 0) {
        return true;
    } 
    return false; 
}

// Save tag cloud preference
function saveTagCloud() {
    $db = PearDatabase::getInstance();
    $db->pquery(
        "UPDATE vtiger_homestuff SET visible = ? 
         WHERE userid=? AND stufftype='Tag Cloud'",
        array($this->get('tagcloud'), $this->getId())
    );
}
```

#### Tag Usage

```php
// Tag a record
function tagRecord($recordId, $moduleName, $tagName, $userId) {
    $db = PearDatabase::getInstance();
    
    // Get or create tag
    $query = "SELECT id FROM vtiger_freetags WHERE tag = ?";
    $result = $db->pquery($query, array($tagName));
    
    if($db->num_rows($result) > 0) {
        $tagId = $db->query_result($result, 0, 'id');
    } else {
        $tagId = $db->getUniqueID('vtiger_freetags');
        $db->pquery(
            "INSERT INTO vtiger_freetags (id, tag, raw_tag) VALUES (?,?,?)",
            array($tagId, $tagName, $tagName)
        );
    }
    
    // Link tag to record
    $db->pquery(
        "INSERT INTO vtiger_freetagged_objects 
         (tag_id, tagger_id, object_id, tagged_on, module) 
         VALUES (?,?,?,?,?)",
        array($tagId, $userId, $recordId, date('Y-m-d H:i:s'), $moduleName)
    );
}

// Get tags for record
function getTagsForRecord($recordId, $moduleName) {
    $db = PearDatabase::getInstance();
    
    $query = "SELECT vtiger_freetags.tag 
              FROM vtiger_freetags
              INNER JOIN vtiger_freetagged_objects 
                ON vtiger_freetags.id = vtiger_freetagged_objects.tag_id
              WHERE vtiger_freetagged_objects.object_id = ? 
                AND vtiger_freetagged_objects.module = ?";
    
    $result = $db->pquery($query, array($recordId, $moduleName));
    $tags = array();
    
    while($row = $db->fetch_array($result)) {
        $tags[] = $row['tag'];
    }
    
    return $tags;
}
```

---

## 4. Database Schema

### 4.1 Inventory Tables

```sql
-- Tax Information (Product & Service)
CREATE TABLE vtiger_inventorytaxinfo (
    taxid INT PRIMARY KEY,
    taxname VARCHAR(50),
    taxlabel VARCHAR(50),
    percentage DECIMAL(7,3),
    deleted INT DEFAULT 0
);

-- Shipping Tax Information
CREATE TABLE vtiger_shippingtaxinfo (
    taxid INT PRIMARY KEY,
    taxname VARCHAR(50),
    taxlabel VARCHAR(50),
    percentage DECIMAL(7,3),
    deleted INT DEFAULT 0
);

-- Terms and Conditions
CREATE TABLE vtiger_inventory_tandc (
    id INT PRIMARY KEY,
    type VARCHAR(100),
    tandc TEXT,
    UNIQUE KEY (type)
);

-- Product Line Items
CREATE TABLE vtiger_inventoryproductrel (
    id INT PRIMARY KEY AUTO_INCREMENT,
    productid INT,
    sequence_no INT,
    quantity DECIMAL(25,3),
    listprice DECIMAL(28,8),
    comment TEXT,
    description TEXT,
    incrementondel INT,
    lineitem_id INT,
    tax1 DECIMAL(7,3),
    tax2 DECIMAL(7,3),
    tax3 DECIMAL(7,3),
    -- More tax columns added dynamically
    discount_percent DECIMAL(7,3),
    discount_amount DECIMAL(28,8)
);

-- Shipping Information
CREATE TABLE vtiger_inventoryshippingrel (
    id INT PRIMARY KEY AUTO_INCREMENT,
    shipping_handling_charge DECIMAL(25,3),
    shtax1 DECIMAL(7,3),
    shtax2 DECIMAL(7,3),
    shtax3 DECIMAL(7,3)
    -- More shipping tax columns added dynamically
);
```

### 4.2 User Preferences Tables

```sql
-- User Information
CREATE TABLE vtiger_users (
    id INT PRIMARY KEY,
    user_name VARCHAR(64),
    first_name VARCHAR(30),
    last_name VARCHAR(30),
    
    -- Preferences
    language VARCHAR(10),
    currency_id INT,
    date_format VARCHAR(20),
    hour_format VARCHAR(2),
    start_hour VARCHAR(5),
    end_hour VARCHAR(5),
    time_zone VARCHAR(100),
    
    -- Calendar Settings
    activity_view VARCHAR(20),
    callduration INT,
    othereventduration INT,
    reminder_interval VARCHAR(100),
    defaultcalendarview VARCHAR(20),
    defaultactivitytype VARCHAR(20),
    defaulteventstatus VARCHAR(20),
    
    -- UI Preferences
    rowheight VARCHAR(10),
    defaultlandingpage VARCHAR(100),
    no_of_currency_decimals INT,
    
    -- Status
    status VARCHAR(10),
    is_admin VARCHAR(3),
    
    -- Other fields...
    FOREIGN KEY (currency_id) REFERENCES vtiger_currency_info(id)
);

-- Calendar Sharing
CREATE TABLE vtiger_calendar_shared_users (
    userid INT,
    sharedid INT,
    PRIMARY KEY (userid, sharedid)
);

-- Tag Cloud Settings
CREATE TABLE vtiger_homestuff (
    stuffid INT PRIMARY KEY,
    userid INT,
    stufftype VARCHAR(100),
    visible INT,
    stuffsequence INT
);
```

### 4.3 Tag Tables

```sql
-- Free Tags
CREATE TABLE vtiger_freetags (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tag VARCHAR(50),
    raw_tag VARCHAR(50)
);

-- Tagged Objects
CREATE TABLE vtiger_freetagged_objects (
    tag_id INT,
    tagger_id INT,
    object_id INT,
    tagged_on DATETIME,
    module VARCHAR(50),
    PRIMARY KEY (tag_id, tagger_id, object_id, module),
    FOREIGN KEY (tag_id) REFERENCES vtiger_freetags(id)
);
```

---

## 5. Integration & Usage

### 5.1 Tax Integration

**Used In:**
- Quote generation
- Sales Order processing
- Purchase Order creation
- Invoice generation
- Product pricing
- Service pricing

**Calculation Flow:**

```
1. User adds product to Quote
2. System retrieves product price
3. System gets active product taxes
4. For each tax:
   - Calculate tax amount = (price * quantity * tax_percentage) / 100
   - Store in tax{id} column
5. Calculate subtotal with all taxes
6. Add shipping charges
7. Apply shipping taxes
8. Calculate grand total
```

### 5.2 Terms & Conditions Integration

**Used In:**
- PDF generation (Quotes, SO, PO, Invoices)
- Email templates
- Print views
- Customer portal

**Display Flow:**

```
1. User generates Quote PDF
2. System retrieves terms for 'Quotes' module
3. Terms text appended to PDF footer
4. PDF generated with terms included
```

### 5.3 User Preferences Integration

**Applied To:**
- Date/time display throughout CRM
- Currency formatting
- Language translation
- Calendar views
- List view pagination
- Default module on login

**Usage Example:**

```php
$currentUser = Users_Record_Model::getCurrentUserModel();

// Format date according to user preference
$dateFormat = $currentUser->get('date_format');
$formattedDate = formatDate($date, $dateFormat);

// Get user timezone
$timezone = $currentUser->get('time_zone');
$userTime = convertToUserTimezone($utcTime, $timezone);

// Get user currency
$currencyId = $currentUser->get('currency_id');
$currencySymbol = getCurrencySymbol($currencyId);
```

### 5.4 Calendar Settings Integration

**Applied To:**
- Calendar module views
- Activity creation defaults
- Reminder notifications
- Shared calendar access
- Mobile calendar sync

### 5.5 Tag Integration

**Used In:**
- Record filtering
- Search functionality
- Record organization
- Dashboard widgets
- Reports

---

## 6. Best Practices

### 6.1 Tax Management

✅ **DO:**
- Create clear, descriptive tax labels (e.g., "VAT 15%", "Sales Tax 8%")
- Keep tax percentages up to date with regulations
- Use product taxes for item-level taxes
- Use shipping taxes for delivery-related taxes
- Test tax calculations before going live
- Document tax configuration changes

❌ **DON'T:**
- Delete taxes that are in use on existing records
- Use ambiguous tax names
- Forget to update tax rates when regulations change
- Mix product and shipping tax types
- Create duplicate tax entries

### 6.2 Terms & Conditions

✅ **DO:**
- Keep terms concise and clear
- Review terms with legal counsel
- Update terms regularly
- Use module-specific terms when needed
- Include payment terms, delivery terms, and warranties
- Version control terms changes

❌ **DON'T:**
- Use overly complex legal language
- Forget to update terms when policies change
- Use same terms for all modules if requirements differ
- Include outdated information

### 6.3 User Preferences

✅ **DO:**
- Set appropriate defaults for new users
- Allow users to customize their experience
- Respect user timezone settings
- Use consistent date/time formats
- Test preference changes before deployment
- Document preference options for users

❌ **DON'T:**
- Force all users to use same preferences
- Change system-wide defaults without notice
- Ignore timezone differences
- Use ambiguous date formats

### 6.4 Calendar Settings

✅ **DO:**
- Set realistic default durations
- Configure appropriate reminder intervals
- Share calendars selectively
- Use consistent activity types
- Test calendar sharing permissions

❌ **DON'T:**
- Set overly long default durations
- Share calendar publicly without consideration
- Use too many activity types
- Forget to configure reminders

### 6.5 Tag Management

✅ **DO:**
- Use consistent tag naming conventions
- Create meaningful tag categories
- Clean up unused tags periodically
- Document tag usage guidelines
- Limit number of tags per record

❌ **DON'T:**
- Create duplicate tags with different cases
- Use overly generic tag names
- Tag everything indiscriminately
- Delete tags without checking usage

---

## Appendix A: Quick Reference

### Tax Management

```php
// Add product tax
$tax = new Settings_Vtiger_TaxRecord_Model();
$tax->set('taxlabel', 'VAT 15%');
$tax->set('percentage', 15.000);
$tax->setType(Settings_Vtiger_TaxRecord_Model::PRODUCT_AND_SERVICE_TAX);
$taxId = $tax->save();

// Get all product taxes
$productTaxes = Settings_Vtiger_TaxRecord_Model::getProductTaxes();

// Get all shipping taxes
$shippingTaxes = Settings_Vtiger_TaxRecord_Model::getShippingTaxes();
```

### Terms & Conditions

```php
// Get terms for module
$terms = Settings_Vtiger_TermsAndConditions_Model::getInstance('Quotes');
$termsText = $terms->getText();

// Update terms
$terms->setText('New terms and conditions text');
$terms->save();
```

### User Preferences

```php
// Get current user
$user = Users_Record_Model::getCurrentUserModel();

// Get preferences
$language = $user->get('language');
$dateFormat = $user->get('date_format');
$timezone = $user->get('time_zone');
$currencyId = $user->get('currency_id');

// Update preferences
$user->set('date_format', 'yyyy-mm-dd');
$user->save();
```

### Calendar Settings

```php
// Get calendar settings URL
$calendarUrl = $user->getCalendarSettingsDetailViewUrl();

// Get reminder in seconds
$reminderSeconds = $user->getCurrentUserActivityReminderInSeconds();
```

### Tags

```php
// Get my tags URL
$tagsUrl = $user->getMyTagSettingsListUrl();

// Tag cloud status
$tagCloudEnabled = $user->getTagCloudStatus();
```

---

**Document Version:** 1.0  
**Last Updated:** 2026-02-04  
**Author:** System Analysis
