# Settings Configuration Modules - Deep Analysis

**Generated:** 2026-02-04  
**Project:** TenantCRM (Vtiger CRM)  
**Version:** 7.x

---

## Table of Contents

1. [Executive Summary](#executive-summary)
2. [Company Details](#company-details)
3. [Customer Portal](#customer-portal)
4. [Currency Management](#currency-management)
5. [Outgoing Server](#outgoing-server)
6. [Config Editor](#config-editor)
7. [Database Schema](#database-schema)
8. [Integration & Dependencies](#integration--dependencies)
9. [Security Considerations](#security-considerations)
10. [Best Practices](#best-practices)

---

## 1. Executive Summary

This document provides a comprehensive analysis of five critical configuration modules in TenantCRM:

| Module | Purpose | Location | Key Features |
|--------|---------|----------|--------------|
| **Company Details** | Organization information & branding | `Settings/Vtiger` | Logo, address, contact info, VAT |
| **Customer Portal** | Self-service portal configuration | `Settings/CustomerPortal` | Module access, fields, permissions |
| **Currencies** | Multi-currency support | `Settings/Currency` | Exchange rates, base currency |
| **Outgoing Server** | Email server configuration | `Settings/Vtiger` | SMTP settings, authentication |
| **Config Editor** | System configuration | `Settings/Vtiger` | Upload limits, default module, helpdesk |

---

## 2. Company Details

### 2.1 Overview

**Purpose:** Manage organization information, branding, and contact details displayed throughout the CRM.

**Location:** `modules/Settings/Vtiger/models/CompanyDetails.php`

### 2.2 Architecture

```
modules/Settings/Vtiger/
├── models/
│   └── CompanyDetails.php          # Business logic
├── views/
│   ├── CompanyDetails.php          # Display view
│   └── CompanyDetailsEdit.php      # Edit form
└── actions/
    ├── CompanyDetailsSave.php      # Save handler
    └── UpdateCompanyLogo.php       # Logo upload handler
```

### 2.3 Data Model

#### Fields

```php
var $fields = array(
    'organizationname' => 'text',      // Company name
    'logoname'         => 'text',      // Logo filename
    'logo'             => 'file',      // Logo upload
    'address'          => 'textarea',  // Street address
    'city'             => 'text',      // City
    'state'            => 'text',      // State/Province
    'code'             => 'text',      // Postal/ZIP code
    'country'          => 'text',      // Country
    'phone'            => 'text',      // Phone number
    'fax'              => 'text',      // Fax number
    'website'          => 'text',      // Website URL
    'vatid'            => 'text'       // VAT/Tax ID
);
```

#### Database Table

**Table:** `vtiger_organizationdetails`

```sql
CREATE TABLE vtiger_organizationdetails (
    organization_id INT PRIMARY KEY,
    organizationname VARCHAR(100),
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    code VARCHAR(30),
    country VARCHAR(100),
    phone VARCHAR(30),
    fax VARCHAR(30),
    website VARCHAR(100),
    logoname VARCHAR(50),
    logo BLOB,
    vatid VARCHAR(100)
);
```

### 2.4 Logo Management

#### Supported Formats

```php
static $logoSupportedFormats = array(
    'jpeg', 'jpg', 'png', 'gif', 'pjpeg', 'x-png'
);
```

#### Logo Path Structure

```php
// Multi-tenant structure
$dbName = vglobal('dbname');
$uniqueId = vglobal('unique_com_id');

if($dbName == 'tenantcrm'){
    $logoPath = 'test/'.$uniqueId.'/';
} else {
    $logoPath = 'test/'.$uniqueId.'/logo/';
}
```

#### Logo Upload Process

```php
public function saveLogo($companyname) {
    // 1. Sanitize company name
    $companyname = str_replace(' ', '', $companyname);
    $companyname = preg_replace('/( *)/', '', $companyname);
    
    // 2. Determine upload directory
    $uploadDir = vglobal('root_directory'). '/' .$logoPath;
    
    // 3. Generate filename
    if($dbName == 'tenantcrm'){
        $logoName = $uploadDir.$_FILES["logo"]["name"];
    } else {
        $logoName = $uploadDir.strtolower($companyname).'_'.$_FILES["logo"]["name"];
    }
    
    // 4. Move uploaded file
    move_uploaded_file($_FILES["logo"]["tmp_name"], $logoName);
    
    // 5. Create favicon
    copy($logoName, $uploadDir.'application.ico');
}
```

#### Security Validations

```php
// 1. File type validation
$fileType = explode('/', $logoDetails['type']);
if (!in_array($fileType, $logoSupportedFormats)) {
    throw new Exception('LBL_INVALID_IMAGE');
}

// 2. MIME type check
$mimeType = mime_content_type($logoDetails['tmp_name']);
$mimeTypeContents = explode('/', $mimeType);
if ($mimeTypeContents[0] != 'image') {
    throw new Exception('LBL_INVALID_IMAGE');
}

// 3. PHP code injection check
$imageContents = file_get_contents($_FILES["logo"]["tmp_name"]);
if (preg_match('/(<\?php?(.*?))/i', $imageContents) == 1) {
    throw new Exception('LBL_INVALID_IMAGE');
}

// 4. File size check
if (!$logoDetails['size']) {
    throw new Exception('LBL_INVALID_IMAGE');
}
```

### 2.5 Usage Across System

Company details are used in:

- **Email Templates**: Merge tags for company info
- **PDF Documents**: Invoices, quotes, reports
- **Customer Portal**: Branding and contact information
- **Login Page**: Company logo display
- **Reports**: Header/footer information
- **Webservices**: Company information API

### 2.6 API Methods

```php
class Settings_Vtiger_CompanyDetails_Model {
    // Get singleton instance
    public static function getInstance()
    
    // Get logo path for display
    public function getLogoPath()
    
    // Save company details
    public function save()
    
    // Save logo file
    public function saveLogo($companyname)
    
    // Get edit view URL
    public function getEditViewUrl()
    
    // Get index view URL
    public function getIndexViewUrl()
}
```

---

## 3. Customer Portal

### 3.1 Overview

**Purpose:** Configure self-service customer portal for Contacts to access their data.

**Location:** `modules/Settings/CustomerPortal/`

### 3.2 Architecture

```
modules/Settings/CustomerPortal/
├── models/
│   └── Module.php                  # Portal configuration logic
├── views/
│   ├── Index.php                   # Portal settings view
│   └── Edit.php                    # Edit configuration
└── actions/
    └── SaveAjax.php                # Save portal settings
```

### 3.3 Portal Configuration

#### Database Tables

**vtiger_customerportal_tabs**
```sql
CREATE TABLE vtiger_customerportal_tabs (
    tabid INT,
    visible INT,                    -- 0=hidden, 1=visible
    sequence INT,                   -- Display order
    createrecord INT,               -- Can create records
    editrecord INT,                 -- Can edit records
    PRIMARY KEY (tabid)
);
```

**vtiger_customerportal_fields**
```sql
CREATE TABLE vtiger_customerportal_fields (
    tabid INT PRIMARY KEY,
    fieldinfo TEXT,                 -- JSON field configuration
    records_visible INT             -- 0=onlymine, 1=all, 2=published
);
```

**vtiger_customerportal_settings**
```sql
CREATE TABLE vtiger_customerportal_settings (
    url VARCHAR(255),
    default_assignee INT,           -- Default user for portal records
    support_notification INT,       -- Days before renewal notification
    announcement TEXT,              -- Portal announcement
    shortcuts TEXT,                 -- JSON shortcuts configuration
    widgets TEXT                    -- JSON dashboard widgets
);
```

**vtiger_customerportal_relatedmoduleinfo**
```sql
CREATE TABLE vtiger_customerportal_relatedmoduleinfo (
    tabid INT PRIMARY KEY,
    relatedmodules TEXT             -- JSON related modules list
);
```

**vtiger_customerportal_prefs**
```sql
CREATE TABLE vtiger_customerportal_prefs (
    tabid INT,
    prefkey VARCHAR(100),
    prefvalue VARCHAR(100)
);
```

### 3.4 Module Configuration

#### Available Modules

```php
public function getModulesList() {
    $query = "SELECT vtiger_customerportal_tabs.*, vtiger_tab.name 
              FROM vtiger_customerportal_tabs
              INNER JOIN vtiger_tab ON vtiger_customerportal_tabs.tabid = vtiger_tab.tabid 
              AND vtiger_tab.presence = 0 
              ORDER BY vtiger_customerportal_tabs.sequence";
}
```

#### Restricted Modules

```php
// Cannot be disabled in portal
$restrictedModules = array('Accounts', 'Contacts');

// Cannot be related to Contacts
$restrictedRelatedModules = array(
    'ModComments', 'Calendar', 'Potentials', 'Emails', 
    'PurchaseOrder', 'SalesOrder', 'Campaigns', 'Vendors'
);
```

### 3.5 Field Configuration

#### Field Visibility

```php
public function updateFields($tabId, $fieldJson) {
    // Store JSON configuration of visible fields
    $db->pquery(
        'INSERT INTO vtiger_customerportal_fields(tabid, fieldinfo) 
         VALUES(?,?) 
         ON DUPLICATE KEY UPDATE fieldinfo = ?', 
        array($tabId, $fieldJson, $fieldJson)
    );
}
```

#### Field Editability Rules

```php
public function isFieldCustomerPortalEditable($crmStatus, $value, $module) {
    $isFieldEditable = 0;
    
    // Never editable
    if ($value->name === 'assigned_user_id' || $value->name === 'contact_id') {
        return 0;
    }
    
    // Module-specific restrictions
    switch ($module) {
        case 'HelpDesk':
            if (in_array($value->name, array('contact_id', 'parent_id'))) {
                $isFieldEditable = 0;
            }
            break;
            
        case 'Assets':
            if (in_array($value->name, array('account', 'contact', 'datesold', 'serialnumber'))) {
                $isFieldEditable = 0;
            }
            break;
    }
    
    return $isFieldEditable;
}
```

### 3.6 Record Visibility

#### Visibility Options

```php
// 0 = Only Mine (contact's own records)
// 1 = All (all records)
// 2 = Only Published (marked as published)

public function getRecordVisiblity($tabId) {
    $result = $db->pquery(
        'SELECT records_visible FROM vtiger_customerportal_fields WHERE tabid= ?', 
        array($tabId)
    );
    
    $visibilityResult = $db->query_result($result, 0, 'records_visible');
    
    if ($visibilityResult == 0) {
        return array('onlymine' => 1, 'all' => 0, 'onlypublished' => 0);
    } else if ($visibilityResult == 1) {
        return array('all' => 1, 'onlymine' => 0, 'onlypublished' => 0);
    } else if ($visibilityResult == 2) {
        return array('onlypublished' => 1, 'all' => 0, 'onlymine' => 0);
    }
}
```

### 3.7 Record Permissions

```php
public function getRecordPermissions($tabid) {
    $result = $db->pquery(
        'SELECT createrecord, editrecord 
         FROM vtiger_customerportal_tabs 
         WHERE tabid=?', 
        array($tabid)
    );
    
    return array(
        'create' => $db->query_result($result, 0, 'createrecord'),
        'edit'   => $db->query_result($result, 0, 'editrecord')
    );
}
```

### 3.8 Dashboard Configuration

```php
public function getDashboardInfo() {
    $result = $db->pquery('SELECT * FROM vtiger_customerportal_settings', array());
    
    return array(
        'url'                   => $row['url'],
        'default_assignee'      => $row['default_assignee'],
        'support_notification'  => $row['support_notification'],  // Days
        'announcement'          => $row['announcement'],
        'shortcuts'             => decode_html($row['shortcuts']),
        'widgets'               => decode_html($row['widgets'])
    );
}
```

### 3.9 Portal User Management

```php
public function getCurrentPortalUser() {
    $result = $db->pquery(
        "SELECT prefvalue FROM vtiger_customerportal_prefs 
         WHERE prefkey = 'userid' AND tabid = 0", 
        array()
    );
    
    return $db->query_result($result, 0, 'prefvalue');
}

public function getCurrentDefaultAssignee() {
    $result = $db->pquery(
        "SELECT default_assignee FROM vtiger_customerportal_settings", 
        array()
    );
    
    return $db->query_result($result, 0, 'default_assignee');
}
```

### 3.10 Save Process

```php
public function save() {
    // 1. Update module visibility
    foreach ($enableModules as $moduleId => $visibility) {
        $db->pquery(
            'INSERT INTO vtiger_customerportal_tabs(tabid,visible) 
             VALUES(?,?) 
             ON DUPLICATE KEY UPDATE visible = ?', 
            array($tabid, $visibility, $visibility)
        );
    }
    
    // 2. Update module sequence
    foreach ($portalModulesInfo as $tabId => $moduleDetails) {
        $db->pquery(
            "UPDATE vtiger_customerportal_tabs SET sequence = ? WHERE tabid = ?", 
            array($moduleDetails['sequence'], $tabId)
        );
    }
    
    // 3. Update dashboard settings
    $db->pquery(
        'UPDATE vtiger_customerportal_settings 
         SET default_assignee=?, support_notification=?, announcement=?, widgets=?', 
        array($defaultAssignee, $renewalPeriod, $announcement, $dashboardWidgets)
    );
    
    // 4. Update field configurations
    foreach ($moduleFieldsInfo as $module => $fields) {
        self::updateFields($tabid, json_encode($currentActiveFields));
    }
    
    // 5. Update related modules
    foreach ($relatedModuleList as $module => $info) {
        $db->pquery(
            'INSERT INTO vtiger_customerportal_relatedmoduleinfo(tabid, relatedmodules) 
             VALUES(?,?) 
             ON DUPLICATE KEY UPDATE relatedmodules = ?', 
            array($tabid, $info, $info)
        );
    }
    
    // 6. Update record visibility
    foreach ($recordsVisible as $module => $info) {
        if ($info == 'all') {
            $db->pquery(
                'UPDATE vtiger_customerportal_fields SET records_visible = ? WHERE tabid = ?', 
                array(1, $tabid)
            );
        } else if ($info == 'onlymine') {
            $db->pquery(
                'UPDATE vtiger_customerportal_fields SET records_visible = ? WHERE tabid = ?', 
                array(0, $tabid)
            );
        }
    }
    
    // 7. Update record permissions
    $db->pquery(
        'UPDATE vtiger_customerportal_tabs SET createrecord=?,editrecord=? WHERE tabid=?', 
        array($updatedPermissions['create'], $updatedPermissions['edit'], $tabid)
    );
    
    // 8. Clear cache
    Vtiger_Cache::delete('CustomerPortal', 'activeModules');
    Vtiger_Cache::delete('CustomerPortal', 'activeFields');
}
```

---

## 4. Currency Management

### 4.1 Overview

**Purpose:** Manage multiple currencies with exchange rates for international business.

**Location:** `modules/Settings/Currency/`

### 4.2 Architecture

```
modules/Settings/Currency/
├── models/
│   ├── Module.php                  # Currency module logic
│   ├── Record.php                  # Currency record CRUD
│   └── ListView.php                # List view logic
├── views/
│   ├── List.php                    # Currency list
│   ├── Edit.php                    # Add/Edit currency
│   └── Delete.php                  # Delete currency
└── actions/
    ├── Save.php                    # Save currency
    └── Delete.php                  # Delete handler
```

### 4.3 Database Schema

#### vtiger_currency_info

**Primary currency table:**

```sql
CREATE TABLE vtiger_currency_info (
    id INT PRIMARY KEY AUTO_INCREMENT,
    currency_name VARCHAR(100),
    currency_code VARCHAR(10),      -- ISO code (USD, EUR, GBP)
    currency_symbol VARCHAR(15),    -- Symbol ($, €, £)
    conversion_rate DECIMAL(10,3),  -- Rate to base currency
    currency_status VARCHAR(20),    -- Active/Inactive
    defaultid INT,                  -- -11 for base currency
    deleted INT DEFAULT 0           -- Soft delete flag
);
```

#### vtiger_currencies

**Master currency list (ISO standard):**

```sql
CREATE TABLE vtiger_currencies (
    currencyid INT PRIMARY KEY AUTO_INCREMENT,
    currency_name VARCHAR(200),
    currency_code VARCHAR(50),
    currency_symbol VARCHAR(20)
);
```

### 4.4 Currency Record Model

```php
class Settings_Currency_Record_Model {
    const tableName = 'vtiger_currency_info';
    
    // Get currency ID
    public function getId()
    
    // Get currency name
    public function getName()
    
    // Check if base currency
    public function isBaseCurrency() {
        return ($this->get('defaultid') != '-11') ? false : true;
    }
    
    // Check if currency is in use
    public function isDisabledRestricted() {
        $query = 'SELECT 1 FROM vtiger_users WHERE currency_id = ?';
        $result = $db->pquery($query, array($this->getId()));
        return ($db->num_rows($result) > 0) ? true : false;
    }
    
    // Get delete status
    public function getDeleteStatus() {
        return $this->get('deleted') ?? 0;
    }
}
```

### 4.5 Currency Operations

#### Save Currency

```php
public function save() {
    $id = $this->getId();
    $tableName = Settings_Currency_Module_Model::tableName;
    
    if(!empty($id)) {
        // Update existing
        $query = 'UPDATE '.$tableName.' 
                  SET currency_name=?, currency_code=?, currency_status=?,
                      currency_symbol=?, conversion_rate=?, deleted=? 
                  WHERE id=?';
        $params = array(
            $this->get('currency_name'), 
            $this->get('currency_code'),
            $this->get('currency_status'), 
            $this->get('currency_symbol'),
            $this->get('conversion_rate'),
            $this->getDeleteStatus(), 
            $id
        );
    } else {
        // Insert new
        $id = $db->getUniqueID($tableName);
        $query = 'INSERT INTO '. $tableName .' VALUES(?,?,?,?,?,?,?,?)';
        $params = array(
            $id, 
            $this->get('currency_name'), 
            $this->get('currency_code'),
            $this->get('currency_symbol'), 
            $this->get('conversion_rate'), 
            $this->get('currency_status'),
            0,  // defaultid
            0   // deleted
        );
    }
    
    $db->pquery($query, $params);
    return $id;
}
```

#### Get All Active Currencies

```php
public static function getAll($excludedIds = array()) {
    $query = 'SELECT * FROM '.Settings_Currency_Module_Model::tableName.' 
              WHERE deleted=0 AND currency_status="Active"';
    $params = array();
    
    if(!empty($excludedIds)) {
        $params = $excludedIds;
        $query .= ' AND id NOT IN ('.generateQuestionMarks($excludedIds).')';
    }
    
    $result = $db->pquery($query, $params);
    $instanceList = array();
    
    for($i=0; $i<$num_rows; $i++) {
        $row = $db->query_result_rowdata($result, $i);
        $instanceList[$row['id']] = new Settings_Currency_Record_Model($row); 
    }
    
    return $instanceList;
}
```

#### Get Non-Mapped Currencies

```php
public static function getAllNonMapped($includedIds = array()) {
    $query = 'SELECT vtiger_currencies.* 
              FROM vtiger_currencies 
              LEFT JOIN vtiger_currency_info 
                ON vtiger_currency_info.currency_name = vtiger_currencies.currency_name
              WHERE vtiger_currency_info.currency_name IS NULL 
                 OR vtiger_currency_info.deleted=1';
    
    if(!empty($includedIds)) {
        $query .= ' OR vtiger_currency_info.id IN('.generateQuestionMarks($includedIds).')';
    }
    
    $query .= ' ORDER BY vtiger_currencies.currency_name';
    
    return $currencyModelList;
}
```

### 4.6 Currency Conversion

#### Conversion Rate Logic

```php
// Base currency has conversion_rate = 1.000
// Other currencies store rate relative to base

// Example:
// Base: USD (rate = 1.000)
// EUR (rate = 0.850)  // 1 USD = 0.85 EUR
// GBP (rate = 0.730)  // 1 USD = 0.73 GBP

// Convert amount from one currency to another:
$amountInBase = $amount / $sourceCurrency->conversion_rate;
$convertedAmount = $amountInBase * $targetCurrency->conversion_rate;
```

#### Usage in Modules

```sql
-- Products with currency conversion
SELECT 
    CASE WHEN vtiger_products.currency_id = vtiger_users.currency_id 
         THEN vtiger_products.unit_price 
         ELSE (vtiger_products.unit_price / vtiger_currency_info.conversion_rate) 
    END AS unit_price
FROM vtiger_products
LEFT JOIN vtiger_currency_info 
    ON vtiger_products.currency_id = vtiger_currency_info.id
```

### 4.7 Record Links

```php
public function getRecordLinks() {
    if($this->isBaseCurrency()){
        // No Edit and delete link for base currency 
        return array(); 
    } 
    
    $editLink = array(
        'linkurl' => "javascript:Settings_Currency_Js.triggerEdit(event, '".$this->getId()."')",
        'linklabel' => 'LBL_EDIT',
        'linkicon' => 'icon-pencil'
    );
    
    $deleteLink = array(
        'linkurl' => "javascript:Settings_Currency_Js.triggerDelete(event,'".$this->getId()."')",
        'linklabel' => 'LBL_DELETE',
        'linkicon' => 'icon-trash'
    );
    
    return array($editLinkInstance, $deleteLinkInstance);
}
```

---

## 5. Outgoing Server

### 5.1 Overview

**Purpose:** Configure SMTP server settings for sending emails from the CRM.

**Location:** `modules/Settings/Vtiger/models/OutgoingServer.php`

### 5.2 Architecture

```
modules/Settings/Vtiger/
├── models/
│   ├── OutgoingServer.php          # SMTP configuration
│   └── Systems.php                 # Base systems model
└── views/
    ├── OutgoingServerDetail.php    # View SMTP settings
    └── OutgoingServerEdit.php      # Edit SMTP settings
```

### 5.3 Database Schema

#### vtiger_systems

```sql
CREATE TABLE vtiger_systems (
    id INT PRIMARY KEY AUTO_INCREMENT,
    server VARCHAR(100),
    server_port INT,
    server_username VARCHAR(100),
    server_password VARCHAR(100),
    server_type VARCHAR(20),        -- 'email'
    smtp_auth VARCHAR(5),            -- 'true'/'false'
    server_path VARCHAR(256),
    from_email_field VARCHAR(50)
);
```

### 5.4 Configuration Fields

```php
class Settings_Vtiger_OutgoingServer_Model extends Settings_Vtiger_Systems_Model {
    
    // SMTP Server Settings
    protected $fields = array(
        'server'            => 'text',      // SMTP host
        'server_port'       => 'text',      // Port (25, 465, 587)
        'server_username'   => 'text',      // SMTP username
        'server_password'   => 'password',  // SMTP password
        'from_email_field'  => 'email',     // From email address
        'smtp_auth'         => 'checkbox'   // Require authentication
    );
}
```

### 5.5 Test Email Functionality

```php
public function getSubject() {
    return 'Test mail about the mail server configuration.';
}

public function getBody() {
    $currentUser = Users_Record_Model::getCurrentUserModel();
    return 'Dear '.$currentUser->get('user_name').', <br><br>
            <b>This is a test mail sent to confirm if a mail is 
            actually being sent through the smtp server that you have configured.</b>
            <br>Feel free to delete this mail.
            <br><br>Thanks and Regards,<br>Team vTiger <br><br>';
}
```

### 5.6 Save with Validation

```php
public function save($request){
    vimport('~~/modules/Emails/mail.php');
    $currentUser = Users_Record_Model::getCurrentUserModel();

    $from_email = $request->get('from_email_field');
    $to_email = getUserEmailId('id', $currentUser->getId());
    
    $subject = $this->getSubject();
    $description = $this->getBody();
    
    // This is added so that send_mail API will treat it as user initiated action
    $olderAction = $_REQUEST['action'];
    $_REQUEST['action'] = 'Save';
    
    if($to_email != ''){
        $mail_status = send_mail(
            'Users',
            $to_email,
            $currentUser->get('user_name'),
            $from_email,
            $subject,
            $description,
            '','','','','',
            true  // Test mode
        );
    }
    
    $_REQUEST['action'] = $olderAction;
    
    if($mail_status != 1 && !$this->isDefaultSettingLoaded()) {
        throw new Exception('Error occurred while sending mail');
    } 
    
    return parent::save();
}
```

### 5.7 Default Configuration

```php
public function loadDefaultValues() {
    $defaultOutgoingServerDetails = VtigerConfig::getOD('DEFAULT_OUTGOING_SERVER_DETAILS');
    
    if (empty($defaultOutgoingServerDetails)) {
        $db = PearDatabase::getInstance();
        $db->pquery('DELETE FROM vtiger_systems WHERE server_type = ?', array('email'));
        return;
    }
    
    foreach ($defaultOutgoingServerDetails as $key=>$value){
        $this->set($key, $value);
    }

    $this->defaultLoaded = true;
}

public function isDefaultSettingLoaded() {
    return $this->defaultLoaded;
}
```

### 5.8 Common SMTP Configurations

#### Gmail

```
Server: smtp.gmail.com
Port: 587 (TLS) or 465 (SSL)
Authentication: Required
Username: your-email@gmail.com
Password: App-specific password
```

#### Office 365

```
Server: smtp.office365.com
Port: 587
Authentication: Required
Username: your-email@company.com
Password: Your password
```

#### SendGrid

```
Server: smtp.sendgrid.net
Port: 587
Authentication: Required
Username: apikey
Password: Your API key
```

#### Amazon SES

```
Server: email-smtp.region.amazonaws.com
Port: 587
Authentication: Required
Username: SMTP username
Password: SMTP password
```

---

## 6. Config Editor

### 6.1 Overview

**Purpose:** Edit system configuration parameters stored in `config.inc.php`.

**Location:** `modules/Settings/Vtiger/models/ConfigModule.php`

### 6.2 Architecture

```
modules/Settings/Vtiger/
├── models/
│   └── ConfigModule.php            # Config file editor
└── views/
    ├── ConfigEditorDetail.php      # View config
    └── ConfigEditorEdit.php        # Edit config
```

### 6.3 Editable Fields

```php
public function getEditableFields() {
    return array(
        'HELPDESK_SUPPORT_EMAIL_ID' => array(
            'label' => 'LBL_HELPDESK_SUPPORT_EMAILID',
            'fieldType' => 'input'
        ),
        'HELPDESK_SUPPORT_NAME' => array(
            'label' => 'LBL_HELPDESK_SUPPORT_NAME',
            'fieldType' => 'input'
        ),
        'upload_maxsize' => array(
            'label' => 'LBL_MAX_UPLOAD_SIZE',
            'fieldType' => 'input'
        ),
        'default_module' => array(
            'label' => 'LBL_DEFAULT_MODULE',
            'fieldType' => 'picklist'
        ),
        'listview_max_textlength' => array(
            'label' => 'LBL_MAX_TEXT_LENGTH_IN_LISTVIEW',
            'fieldType' => 'input'
        ),
        'list_max_entries_per_page' => array(
            'label' => 'LBL_MAX_ENTRIES_PER_PAGE_IN_LISTVIEW',
            'fieldType' => 'input'
        )
    );
}
```

### 6.4 Configuration File Structure

**File:** `config.inc.php`

```php
// Helpdesk Support Settings
$HELPDESK_SUPPORT_EMAIL_ID = 'support@company.com';
$HELPDESK_SUPPORT_NAME = 'Support Team';

// Upload Settings
$upload_maxsize = 5242880;  // 5MB in bytes

// Default Module
$default_module = 'Home';

// List View Settings
$listview_max_textlength = 40;
$list_max_entries_per_page = 20;
```

### 6.5 Read Configuration

```php
public function readFile() {
    if (!$this->completeData) {
        $this->completeData = file_get_contents($this->fileName);
    }
    return $this->completeData;
}

public function getViewableData() {
    $fileContent = $this->readFile();
    $pattern = '/\$([^=]+)=([^;]+);/';
    $matches = null;
    $matchesFound = preg_match_all($pattern, $fileContent, $matches);
    
    $configContents = array();
    if ($matchesFound) {
        $configContents = $matches[0];
    }

    $data = array();
    $editableFileds = $this->getEditableFields();
    
    foreach ($editableFileds as $fieldName => $fieldDetails) {
        foreach ($configContents as $configContent) {
            if (strpos($configContent, $fieldName)) {
                $fieldValue = explode(' = ', $configContent);
                $fieldValue = $fieldValue[1];
                
                if ($fieldName === 'upload_maxsize') {
                    $fieldValue = round(number_format($fieldValue / 1048576, 2));
                }

                $data[$fieldName] = str_replace(";", '', str_replace("'", '', $fieldValue));
                break;
            }
        }
    }
    
    $this->setData($data);
    return $this->getData();
}
```

### 6.6 Save Configuration

```php
public function save() {
    $fileContent = $this->completeData;
    $updatedFields = $this->get('updatedFields');
    $validationInfo = $this->validateFieldValues($updatedFields);
    
    if ($validationInfo === true) {
        foreach ($updatedFields as $fieldName => $fieldValue) {
            $patternString = "\$%s = '%s';";
            
            if ($fieldName === 'upload_maxsize') {
                $fieldValue = $fieldValue * 1048576; //(1024 * 1024)
                $patternString = "\$%s = %s;";
            }
            
            if($fieldName==='list_max_entries_per_page' || $fieldName ==='listview_max_textlength'){
                $fieldValue= intval($fieldValue);
            }
            
            $pattern = '/\$' . $fieldName . '[\s]+=([^;]+);/';
            $replacement = sprintf($patternString, $fieldName, ltrim($fieldValue, '0'));
            $fileContent = preg_replace($pattern, $replacement, $fileContent);
        }
        
        $filePointer = fopen($this->fileName, 'w');
        fwrite($filePointer, $fileContent);
        fclose($filePointer);
    }
    
    return $validationInfo;
}
```

### 6.7 Field Validation

```php
public function validateFieldValues($updatedFields){
    // Email validation
    if (array_key_exists('HELPDESK_SUPPORT_EMAIL_ID',$updatedFields) 
        && !filter_var($updatedFields['HELPDESK_SUPPORT_EMAIL_ID'], FILTER_VALIDATE_EMAIL)) {
        return "LBL_INVALID_EMAILID";
    } 
    
    // Support name validation
    else if(array_key_exists('HELPDESK_SUPPORT_NAME',$updatedFields) 
        && preg_match ('/[\'\";?><]/', $updatedFields['HELPDESK_SUPPORT_NAME'])) {
        return "LBL_INVALID_SUPPORT_NAME";
    } 
    
    // Module validation
    else if(array_key_exists('default_module',$updatedFields) 
        && !preg_match ('/[a-zA-z0-9]/', $updatedFields['default_module'])) {
        return "LBL_INVALID_MODULE";
    } 
    
    // Numeric validation
    else if((array_key_exists('upload_maxsize',$updatedFields) 
            && !filter_var(ltrim($updatedFields['upload_maxsize'],'0'), FILTER_VALIDATE_INT))
        || (array_key_exists('list_max_entries_per_page',$updatedFields) 
            &&  !filter_var(ltrim($updatedFields['list_max_entries_per_page'], '0'), FILTER_VALIDATE_INT))
        || (array_key_exists('listview_max_textlength',$updatedFields) 
            && !filter_var(ltrim($updatedFields['listview_max_textlength'], '0'), FILTER_VALIDATE_INT))) {
        return "LBL_INVALID_NUMBER";
    }
    
    return true;
}
```

### 6.8 Default Module Picklist

```php
public function getPicklistValues($fieldName) {
    if ($fieldName === 'default_module') {
        $db = PearDatabase::getInstance();

        $presence = array(0);
        $restrictedModules = array('Webmails', 'Emails', 'Integration', 'Dashboard','ModComments');
        
        $query = 'SELECT name, tablabel FROM vtiger_tab 
                  WHERE presence IN (' . generateQuestionMarks($presence) . ') 
                    AND isentitytype = ? 
                    AND name NOT IN (' . generateQuestionMarks($restrictedModules) . ')';

        $result = $db->pquery($query, array($presence, '1', $restrictedModules));
        $numOfRows = $db->num_rows($result);

        $moduleData = array('Home' => 'Home');
        for ($i = 0; $i < $numOfRows; $i++) {
            $row = $db->query_result_rowdata($result, $i);
            $moduleData[$db->query_result($result, $i, 'name')] = $db->query_result($result, $i, 'tablabel');
        }
        
        return $moduleData;
    }
    
    return array('true', 'false');
}
```

---

## 7. Database Schema

### 7.1 Complete Table Overview

```sql
-- Company Details
CREATE TABLE vtiger_organizationdetails (
    organization_id INT PRIMARY KEY,
    organizationname VARCHAR(100),
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    code VARCHAR(30),
    country VARCHAR(100),
    phone VARCHAR(30),
    fax VARCHAR(30),
    website VARCHAR(100),
    logoname VARCHAR(50),
    logo BLOB,
    vatid VARCHAR(100)
);

-- Currency Management
CREATE TABLE vtiger_currency_info (
    id INT PRIMARY KEY AUTO_INCREMENT,
    currency_name VARCHAR(100),
    currency_code VARCHAR(10),
    currency_symbol VARCHAR(15),
    conversion_rate DECIMAL(10,3),
    currency_status VARCHAR(20),
    defaultid INT,
    deleted INT DEFAULT 0
);

CREATE TABLE vtiger_currencies (
    currencyid INT PRIMARY KEY AUTO_INCREMENT,
    currency_name VARCHAR(200),
    currency_code VARCHAR(50),
    currency_symbol VARCHAR(20)
);

-- Customer Portal
CREATE TABLE vtiger_customerportal_tabs (
    tabid INT PRIMARY KEY,
    visible INT,
    sequence INT,
    createrecord INT,
    editrecord INT
);

CREATE TABLE vtiger_customerportal_fields (
    tabid INT PRIMARY KEY,
    fieldinfo TEXT,
    records_visible INT
);

CREATE TABLE vtiger_customerportal_settings (
    url VARCHAR(255),
    default_assignee INT,
    support_notification INT,
    announcement TEXT,
    shortcuts TEXT,
    widgets TEXT
);

CREATE TABLE vtiger_customerportal_relatedmoduleinfo (
    tabid INT PRIMARY KEY,
    relatedmodules TEXT
);

CREATE TABLE vtiger_customerportal_prefs (
    tabid INT,
    prefkey VARCHAR(100),
    prefvalue VARCHAR(100)
);

-- Outgoing Server
CREATE TABLE vtiger_systems (
    id INT PRIMARY KEY AUTO_INCREMENT,
    server VARCHAR(100),
    server_port INT,
    server_username VARCHAR(100),
    server_password VARCHAR(100),
    server_type VARCHAR(20),
    smtp_auth VARCHAR(5),
    server_path VARCHAR(256),
    from_email_field VARCHAR(50)
);
```

### 7.2 Relationships

```
vtiger_organizationdetails (1) ← Used by → All Modules (N)
vtiger_currency_info (1) ← Used by → vtiger_users (N)
vtiger_currency_info (1) ← Used by → Inventory Modules (N)
vtiger_customerportal_tabs (1) ← Configured for → vtiger_tab (1)
vtiger_customerportal_fields (1) ← Configured for → vtiger_tab (1)
vtiger_systems (1) ← Used by → Email System (N)
```

---

## 8. Integration & Dependencies

### 8.1 Company Details Integration

**Used In:**
- Email templates (merge tags)
- PDF generation (invoices, quotes)
- Customer portal (branding)
- Login page (logo)
- Reports (headers/footers)
- Webservices API

**Dependencies:**
- File system (logo storage)
- Multi-tenant configuration
- Image processing

### 8.2 Currency Integration

**Used In:**
- Products/Services pricing
- Inventory modules (Quotes, SO, PO, Invoices)
- User preferences
- Reports with currency fields
- Dashboard widgets

**Dependencies:**
- User currency preferences
- Product currency settings
- Conversion rate calculations

### 8.3 Customer Portal Integration

**Used In:**
- Portal login system
- Portal module access
- Portal field visibility
- Portal record permissions
- Portal dashboard

**Dependencies:**
- Contact authentication
- Module permissions
- Field configurations
- Related module settings

### 8.4 Outgoing Server Integration

**Used In:**
- Email sending (all modules)
- Workflow email tasks
- Scheduled reports
- Notifications
- Password reset emails

**Dependencies:**
- SMTP server connectivity
- Email templates
- User email addresses

### 8.5 Config Editor Integration

**Used In:**
- File upload validation
- Default module routing
- List view pagination
- Helpdesk email settings
- System-wide configurations

**Dependencies:**
- config.inc.php file
- File system write permissions
- Module availability

---

## 9. Security Considerations

### 9.1 Company Details Security

**Logo Upload:**
- File type validation (MIME type check)
- PHP code injection prevention
- File size limits
- Supported format whitelist
- Secure file path handling

**Data Validation:**
- HTML encoding for display
- SQL injection prevention (parameterized queries)
- XSS prevention

### 9.2 Currency Security

**Data Integrity:**
- Base currency protection (cannot delete/edit)
- In-use currency protection
- Soft delete mechanism
- Conversion rate validation

**Access Control:**
- Admin-only access
- Record link restrictions
- Audit trail

### 9.3 Customer Portal Security

**Access Control:**
- Contact-based authentication
- Module-level permissions
- Field-level visibility
- Record-level permissions (own/all/published)

**Data Protection:**
- Restricted field editing
- Module restrictions
- Related module filtering

### 9.4 Outgoing Server Security

**Credential Protection:**
- Password encryption
- Secure storage in database
- Test email validation
- SMTP authentication

**Configuration Validation:**
- Email format validation
- Port number validation
- Server connectivity test

### 9.5 Config Editor Security

**File Protection:**
- Write permission validation
- Backup before save
- Syntax validation
- Field value validation

**Input Validation:**
- Email format validation
- Numeric value validation
- Special character filtering
- Module existence check

---

## 10. Best Practices

### 10.1 Company Details

✅ **DO:**
- Use high-quality logo images (PNG recommended)
- Keep logo file size under 500KB
- Use standard image formats
- Update VAT ID for tax compliance
- Keep contact information current

❌ **DON'T:**
- Upload executable files as logos
- Use copyrighted images without permission
- Leave required fields empty
- Use special characters in company name

### 10.2 Currency Management

✅ **DO:**
- Update conversion rates regularly
- Set one base currency (cannot change later)
- Use ISO standard currency codes
- Test conversions after rate updates
- Document rate update schedule

❌ **DON'T:**
- Delete currencies in use
- Set conversion rate to zero
- Change base currency after setup
- Use non-standard currency codes

### 10.3 Customer Portal

✅ **DO:**
- Enable only necessary modules
- Configure field visibility carefully
- Set appropriate record permissions
- Test portal access regularly
- Document portal configuration

❌ **DON'T:**
- Expose sensitive fields
- Allow unrestricted record creation
- Disable Accounts/Contacts modules
- Forget to set default assignee

### 10.4 Outgoing Server

✅ **DO:**
- Use app-specific passwords (Gmail)
- Test email sending after configuration
- Use TLS/SSL encryption
- Monitor email delivery
- Keep credentials secure

❌ **DON'T:**
- Use plain SMTP (port 25) in production
- Share SMTP credentials
- Skip test email verification
- Use personal email for system emails

### 10.5 Config Editor

✅ **DO:**
- Backup config.inc.php before changes
- Test changes in development first
- Document configuration changes
- Use reasonable upload limits
- Set appropriate list view limits

❌ **DON'T:**
- Set upload_maxsize too high (server limits)
- Use special characters in support name
- Set list entries too high (performance)
- Change default module frequently

---

## Appendix A: Quick Reference

### Configuration File Locations

```
Company Details:
- Model: modules/Settings/Vtiger/models/CompanyDetails.php
- View: modules/Settings/Vtiger/views/CompanyDetails.php
- Logo Path: test/{unique_id}/logo/

Customer Portal:
- Model: modules/Settings/CustomerPortal/models/Module.php
- Views: modules/Settings/CustomerPortal/views/

Currency:
- Model: modules/Settings/Currency/models/Record.php
- Table: vtiger_currency_info

Outgoing Server:
- Model: modules/Settings/Vtiger/models/OutgoingServer.php
- Table: vtiger_systems

Config Editor:
- Model: modules/Settings/Vtiger/models/ConfigModule.php
- File: config.inc.php
```

### Common Database Queries

```sql
-- Get company details
SELECT * FROM vtiger_organizationdetails;

-- Get all active currencies
SELECT * FROM vtiger_currency_info WHERE deleted=0 AND currency_status='Active';

-- Get base currency
SELECT * FROM vtiger_currency_info WHERE defaultid = -11;

-- Get portal enabled modules
SELECT * FROM vtiger_customerportal_tabs WHERE visible=1 ORDER BY sequence;

-- Get SMTP configuration
SELECT * FROM vtiger_systems WHERE server_type='email';

-- Get portal user
SELECT prefvalue FROM vtiger_customerportal_prefs WHERE prefkey='userid' AND tabid=0;
```

### API Endpoints

```php
// Company Details
Settings_Vtiger_CompanyDetails_Model::getInstance()

// Currency
Settings_Currency_Record_Model::getInstance($id)
Settings_Currency_Record_Model::getAll()

// Customer Portal
Settings_CustomerPortal_Module_Model::getModulesList()
Settings_CustomerPortal_Module_Model::getDashboardInfo()

// Outgoing Server
Settings_Vtiger_OutgoingServer_Model::getInstance()

// Config Editor
Settings_Vtiger_ConfigModule_Model::getInstance()
```

---

**Document Version:** 1.0  
**Last Updated:** 2026-02-04  
**Author:** System Analysis
