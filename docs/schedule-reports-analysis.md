# Schedule Reports - Deep Analysis

## Table of Contents
1. [Overview](#overview)
2. [Architecture](#architecture)
3. [Database Schema](#database-schema)
4. [Backend Components](#backend-components)
5. [Frontend Components](#frontend-components)
6. [Data Flow](#data-flow)
7. [Schedule Types](#schedule-types)
8. [Recipients System](#recipients-system)
9. [Email Delivery](#email-delivery)
10. [Cron Job Integration](#cron-job-integration)
11. [User Interface Elements](#user-interface-elements)
12. [JavaScript Interactions](#javascript-interactions)
13. [Laravel Migration Guide](#laravel-migration-guide)

---

## Overview

The **Schedule Reports** functionality in Vtiger CRM allows users to automatically generate and email reports at predefined intervals. This feature is integrated into the report creation/editing form (Step 1) and provides flexible scheduling options including daily, weekly, monthly, yearly, and specific date triggers.

### Key Capabilities
- Schedule reports to run automatically
- Support for multiple schedule types (Daily, Weekly, Monthly, Yearly, Specific Date)
- Multiple recipient selection (Users, Groups, Roles)
- Support for specific email addresses
- File format selection (CSV, XLS)
- Next trigger time preview
- Cron-based execution

---

## Architecture

### Component Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                    SCHEDULE REPORTS ARCHITECTURE                │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌─────────────────┐    ┌─────────────────┐                    │
│  │   Frontend UI   │───▶│  Reports_Save   │                    │
│  │ ScheduleReport  │    │    _Action      │                    │
│  │     .tpl        │    └────────┬────────┘                    │
│  └─────────────────┘             │                              │
│                                  ▼                              │
│                    ┌─────────────────────────┐                  │
│                    │   Reports_Schedule      │                  │
│                    │   Reports_Model         │                  │
│                    └────────────┬────────────┘                  │
│                                 │                               │
│           ┌─────────────────────┼─────────────────────┐        │
│           ▼                     ▼                     ▼        │
│  ┌────────────────┐  ┌────────────────┐  ┌────────────────┐   │
│  │ vtiger_schedule│  │  Cron Service  │  │ Vtiger_Mailer  │   │
│  │    reports     │  │ ScheduleReports│  │                │   │
│  │   (Database)   │  │   .service     │  │                │   │
│  └────────────────┘  └────────────────┘  └────────────────┘   │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### File Structure

```
modules/Reports/
├── models/
│   ├── ScheduleReports.php      # Main model for schedule reports
│   └── Record.php               # Report record model (includes getScheduledReport)
├── actions/
│   ├── Save.php                 # Handles saving report with scheduling
│   └── ChartSave.php            # Handles saving chart reports with scheduling
├── ScheduledReports.php         # Legacy scheduled reports class
└── ...

layouts/v7/modules/Reports/
├── Step1.tpl                    # Report creation Step 1 (includes ScheduleReport.tpl)
├── ScheduleReport.tpl           # Schedule reports UI component
└── resources/
    └── Edit1.js                 # JavaScript for Step 1 interactions

cron/modules/Reports/
└── ScheduleReports.service      # Cron service for executing scheduled reports
```

---

## Database Schema

### Table: `vtiger_schedulereports`

| Column            | Type         | Description                                    |
|-------------------|--------------|------------------------------------------------|
| `reportid`        | INT(10)      | Foreign key to vtiger_report                   |
| `scheduleid`      | INT(3)       | Schedule type (1-5)                            |
| `recipients`      | TEXT         | JSON-encoded list of recipients                |
| `schdate`         | VARCHAR(20)  | Specific date (JSON array for specific date)   |
| `schtime`         | TIME         | Scheduled time (HH:MM:SS)                      |
| `schdayoftheweek` | VARCHAR(100) | JSON array of weekdays (for weekly)            |
| `schdayofthemonth`| VARCHAR(100) | JSON array of month days (for monthly)         |
| `schannualdates`  | VARCHAR(500) | JSON array of annual dates (YYYY-MM-DD format) |
| `specificemails`  | VARCHAR(500) | JSON-encoded specific email addresses          |
| `next_trigger_time`| TIMESTAMP   | Next scheduled execution time                  |
| `fileformat`      | VARCHAR(10)  | Export format (CSV, XLS)                       |

### Table Creation SQL

```sql
CREATE TABLE IF NOT EXISTS vtiger_schedulereports(
    reportid INT(10),
    scheduleid INT(3),
    recipients TEXT,
    schdate VARCHAR(20),
    schtime TIME,
    schdayoftheweek VARCHAR(100),
    schdayofthemonth VARCHAR(100),
    schannualdates VARCHAR(500),
    specificemails VARCHAR(500),
    next_trigger_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fileformat VARCHAR(10) DEFAULT "CSV"
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

---

## Backend Components

### 1. Reports_ScheduleReports_Model (`modules/Reports/models/ScheduleReports.php`)

This is the primary model class for managing scheduled reports.

#### Static Properties - Schedule Type Constants

```php
static $SCHEDULED_DAILY = 1;
static $SCHEDULED_WEEKLY = 2;
static $SCHEDULED_MONTHLY_BY_DATE = 3;
static $SCHEDULED_ANNUALLY = 4;
static $SCHEDULED_ON_SPECIFIC_DATE = 5;
```

#### Key Methods

| Method                      | Description                                         |
|-----------------------------|-----------------------------------------------------|
| `getInstance()`             | Returns a new instance of the model                 |
| `getInstanceById($recordId)`| Retrieves schedule info for a specific report       |
| `saveScheduleReport()`      | Saves/updates schedule configuration                |
| `getRecipientEmails()`      | Retrieves email addresses of all recipients         |
| `sendEmail()`               | Generates and sends the scheduled report email      |
| `getNextTriggerTime()`      | Calculates the next execution time                  |
| `updateNextTriggerTime()`   | Updates the next trigger time in database           |
| `getScheduledReports()`     | Gets all reports due for execution                  |
| `runScheduledReports()`     | Main entry point for cron job execution             |
| `getEmailContent($reportRecordModel)` | Generates email body HTML                 |
| `getNextTriggerTimeInUserFormat()`    | Formats next trigger time for display     |

#### saveScheduleReport() Logic

```php
public function saveScheduleReport() {
    $adb = PearDatabase::getInstance();

    $reportid = $this->get('reportid');
    $scheduleid = $this->get('scheduleid');
    $schtime = $this->get('schtime');
    
    // Time validation
    if(!preg_match('/^[0-2]\d(:[0-5]\d){1,2}$/', $schtime) or substr($schtime,0,2)>23) {
        $schtime='00:00';
    }
    $schtime .=':00';

    // Schedule type specific data processing
    if ($scheduleid == self::$SCHEDULED_ON_SPECIFIC_DATE) {
        // Handle specific date
        $schdate = Zend_Json::encode(array($dateDBFormat));
    } else if ($scheduleid == self::$SCHEDULED_WEEKLY) {
        $schdayoftheweek = Zend_Json::encode($this->get('schdayoftheweek'));
    } else if ($scheduleid == self::$SCHEDULED_MONTHLY_BY_DATE) {
        $schdayofthemonth = Zend_Json::encode($this->get('schdayofthemonth'));
    } else if ($scheduleid == self::$SCHEDULED_ANNUALLY) {
        $schannualdates = Zend_Json::encode($this->get('schannualdates'));
    }

    $recipients = Zend_Json::encode($this->get('recipients'));
    $specificemails = Zend_Json::encode($this->get('specificemails'));
    $isReportScheduled = $this->get('isReportScheduled');
    $fileFormat = $this->get('fileformat');

    // Calculate next trigger time
    if($scheduleid != self::$SCHEDULED_ON_SPECIFIC_DATE) {
        $nextTriggerTime = $this->getNextTriggerTime();
    }

    // Delete if scheduling is disabled
    if ($isReportScheduled == '0' || $isReportScheduled == '' || $isReportScheduled == false) {
        $deleteScheduledReportSql = "DELETE FROM vtiger_schedulereports WHERE reportid=?";
        $adb->pquery($deleteScheduledReportSql, array($reportid));
    } else {
        // INSERT or UPDATE based on existence
        $checkScheduledResult = $adb->pquery('SELECT next_trigger_time FROM vtiger_schedulereports WHERE reportid=?', 
            array($reportid));
        if ($adb->num_rows($checkScheduledResult) > 0) {
            // UPDATE existing
            $scheduledReportSql = 'UPDATE vtiger_schedulereports SET scheduleid=?, recipients=?, schdate=?, schtime=?, 
                schdayoftheweek=?, schdayofthemonth=?, schannualdates=?, specificemails=?, next_trigger_time=?, 
                fileformat = ? WHERE reportid=?';
            // ...
        } else {
            // INSERT new
            $scheduleReportSql = 'INSERT INTO vtiger_schedulereports (reportid,scheduleid,recipients,schdate,schtime,
                schdayoftheweek,schdayofthemonth,schannualdates,next_trigger_time,specificemails, fileformat) 
                VALUES (?,?,?,?,?,?,?,?,?,?,?)';
            // ...
        }
    }
}
```

### 2. Reports_Save_Action (`modules/Reports/actions/Save.php`)

Handles saving the report including schedule information.

```php
public function process(Vtiger_Request $request) {
    // ... save report model ...

    //Scheduled Reports
    $scheduleReportModel = new Reports_ScheduleReports_Model();
    $scheduleReportModel->set('scheduleid', $request->get('schtypeid'));
    $scheduleReportModel->set('schtime', date('H:i', strtotime($request->get('schtime'))));
    $scheduleReportModel->set('schdate', $request->get('schdate'));
    $scheduleReportModel->set('schdayoftheweek', $request->get('schdayoftheweek'));
    $scheduleReportModel->set('schdayofthemonth', $request->get('schdayofthemonth'));
    $scheduleReportModel->set('schannualdates', $request->get('schannualdates'));
    $scheduleReportModel->set('reportid', $reportModel->getId());
    $scheduleReportModel->set('recipients', $request->get('recipients'));
    $scheduleReportModel->set('isReportScheduled', $request->get('enable_schedule'));
    $scheduleReportModel->set('specificemails', $request->get('specificemails'));
    $scheduleReportModel->set('fileformat', $request->get('fileformat'));
    $scheduleReportModel->saveScheduleReport();
    //END
}
```

---

## Frontend Components

### 1. ScheduleReport.tpl (`layouts/v7/modules/Reports/ScheduleReport.tpl`)

The main UI component for schedule configuration.

#### Structure

```smarty
{strip}
    {* Enable Schedule Checkbox *}
    <div class="row">
        <div>
            <label>
                <input type="checkbox" 
                       {if $SCHEDULEDREPORTS->get('scheduleid') neq ''} checked="checked" {/if} 
                       name='enable_schedule'>
                <strong>{vtranslate('LBL_SCHEDULE_REPORTS',$MODULE)}</strong>
            </label>
        </div>
    </div>
    
    {* Schedule Configuration Box *}
    <div id="scheduleBox" class='row well contentsBackground {if $SCHEDULEDREPORTS->get('scheduleid') eq ''} hide {/if}'>
        
        {* Schedule Type Dropdown *}
        <select id='schtypeid' name='schtypeid'>
            <option value="1">Daily</option>
            <option value="2">Weekly</option>
            <option value="5">On Specific Date</option>
            <option value="3">Monthly by Date</option>
            <option value="4">Yearly</option>
        </select>
        
        {* Weekly - Day Selection *}
        <div id='scheduledWeekDay' class='{if $scheduleid neq 2} hide {/if}'>
            <select multiple name='schdayoftheweek' id='schdayoftheweek'>
                <option value="7">Sunday</option>
                <option value="1">Monday</option>
                <!-- ... -->
            </select>
        </div>
        
        {* Monthly - Day of Month Selection *}
        <div id='scheduleMonthByDates' class='{if $scheduleid neq 3} hide {/if}'>
            <select multiple name='schdayofthemonth' id='schdayofthemonth'>
                {section name=foo loop=31}
                    <option value={$smarty.section.foo.iteration}>{$smarty.section.foo.iteration}</option>
                {/section}
            </select>
        </div>
        
        {* Specific Date - Date Picker *}
        <div id='scheduleByDate' class='{if $scheduleid neq 5} hide {/if}'>
            <input type="text" class="dateField" id="schdate" name="schdate" />
        </div>
        
        {* Annually - Multi-Date Picker *}
        <div id='scheduleAnually' class='{if $scheduleid neq 4} hide {/if}'>
            <div id='annualDatePicker'></div>
            <input type=hidden id=hiddenAnnualDates value='{$SCHEDULEDREPORTS->get('schannualdates')}' />
            <select multiple id='annualDates' name='schannualdates'>
                <!-- Populated via JavaScript -->
            </select>
        </div>
        
        {* Time Picker *}
        <div id='scheduledTime'>
            <input type='text' class='timepicker-default' name='schtime' 
                   value="{$SCHEDULEDREPORTS->get('schtime')}" />
        </div>
        
        {* Recipients Selection *}
        <div id='recipientsList'>
            <select multiple id='recipients' name='recipients'>
                <optgroup label="Users">
                    {foreach key=USER_ID item=USER_NAME from=$ALL_ACTIVEUSER_LIST}
                        <option value="USER::{$USER_ID}">{$USER_NAME}</option>
                    {/foreach}
                </optgroup>
                <optgroup label="Groups">
                    {foreach key=GROUP_ID item=GROUP_NAME from=$ALL_ACTIVEGROUP_LIST}
                        <option value="GROUP::{$GROUP_ID}">{$GROUP_NAME}</option>
                    {/foreach}
                </optgroup>
                <optgroup label="Roles">
                    {foreach key=ROLE_ID item=ROLE_OBJ from=$ROLES}
                        <option value="ROLE::{$ROLE_ID}">{$ROLE_OBJ->get('rolename')}</option>
                    {/foreach}
                </optgroup>
            </select>
        </div>
        
        {* Specific Emails *}
        <div id='specificemailsids'>
            <input id="specificemails" type="text" name="specificemails" 
                   data-validation-engine="validate[funcCall[Vtiger_MultiEmails_Validator_Js.invokeValidation]]" />
        </div>
        
        {* File Format (CSV/Excel) *}
        {if $TYPE neq 'Chart'}
            <div id='fileformat'>
                <select id='fileformat' name='fileformat'>
                    <option value="CSV">CSV</option>
                    <option value="XLS">Excel</option>
                </select>
            </div>
        {/if}
        
        {* Next Trigger Time Display *}
        {if $SCHEDULEDREPORTS->get('next_trigger_time')}
            <div>
                {$SCHEDULEDREPORTS->getNextTriggerTimeInUserFormat()}
                <span>({$CURRENT_USER->time_zone})</span>
            </div>
        {/if}
    </div>
{/strip}
```

### 2. Step1.tpl (`layouts/v7/modules/Reports/Step1.tpl`)

The main report creation form that includes the schedule component.

```smarty
<form class="form-horizontal recordEditView" id="report_step1" method="post" action="index.php">
    <input type="hidden" name="mode" value="step2" />
    <input type="hidden" name="module" value="{$MODULE}" />
    <!-- Report basic fields: name, folder, primary module, etc. -->
    
    {* Include Schedule Report Component *}
    {include file="ScheduleReport.tpl"|@vtemplate_path:$MODULE}
    
    <!-- Navigation buttons -->
</form>
```

---

## Data Flow

### 1. Saving Schedule Configuration

```
User Input (Form)
       │
       ▼
┌─────────────────────┐
│   JavaScript        │
│   Form Validation   │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  Reports_Save_Action│
│  process()          │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ Reports_ScheduleReports_Model │
│ saveScheduleReport()          │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ vtiger_schedulereports│
│      (Database)        │
└────────────────────────┘
```

### 2. Executing Scheduled Reports

```
┌────────────────────────────┐
│   Cron Job (Every 15 min)  │
│   ScheduleReports.service  │
└─────────────┬──────────────┘
              │
              ▼
┌─────────────────────────────────┐
│ Reports_ScheduleReports_Model:: │
│ runScheduledReports()           │
└─────────────┬───────────────────┘
              │
              ▼
┌─────────────────────────────────┐
│ getScheduledReports()           │
│ Query: SELECT WHERE             │
│ next_trigger_time <= NOW()      │
└─────────────┬───────────────────┘
              │
              ▼
    ┌─────────────────┐
    │  For each report │
    └────────┬────────┘
             │
    ┌────────┴────────┐
    │                 │
    ▼                 ▼
┌─────────────┐  ┌──────────────┐
│ sendEmail() │  │ updateNext   │
│             │  │ TriggerTime()│
└─────────────┘  └──────────────┘
```

---

## Schedule Types

### 1. Daily (scheduleid = 1)
- Runs every day at the specified time
- Uses workflow's `getNextTriggerTimeForDaily()` method

### 2. Weekly (scheduleid = 2)
- Runs on selected days of the week
- Days stored as JSON array: `["1","3","5"]` (Mon, Wed, Fri)
- Day values: 1=Monday, 2=Tuesday, ..., 7=Sunday
- Uses workflow's `getNextTriggerTimeForWeekly()` method

### 3. Monthly by Date (scheduleid = 3)
- Runs on selected days of the month (1-31)
- Days stored as JSON array: `["1","15","28"]`
- Uses workflow's `getNextTriggerTimeForMonthlyByDate()` method

### 4. Yearly/Annually (scheduleid = 4)
- Runs on specific dates each year
- Dates stored as JSON array: `["2024-01-15","2024-06-30"]`
- Uses workflow's `getNextTriggerTimeForAnnualDates()` method

### 5. Specific Date (scheduleid = 5)
- One-time execution on a specific date
- After execution, sets next trigger far in future (+10 years)
- Date stored in schdate field

---

## Recipients System

### Recipient Types

| Type   | Format               | Example               |
|--------|----------------------|-----------------------|
| Users  | `USER::<user_id>`    | `USER::1`             |
| Groups | `GROUP::<group_id>`  | `GROUP::3`            |
| Roles  | `ROLE::<role_id>`    | `ROLE::H2`            |

### Recipient Resolution Flow

```php
public function getRecipientEmails() {
    $recipientsInfo = $this->get('recipients');
    $recipientsInfo = Zend_Json::decode($recipientsInfo);
    
    // Parse recipient identifiers
    foreach ($recipientsInfo as $recipient) {
        if (strpos($recipient,'USER') !== false) {
            $recipients['Users'][] = $id[1];
        } else if (strpos($recipient,'GROUP') !== false) {
            $recipients['Groups'][] = $id[1];
        } else if (strpos($recipient,'ROLE') !== false) {
            $recipients['Roles'][] = $id[1];
        }
    }
    
    // Resolve to actual user IDs
    // - Users: Direct use
    // - Roles: getRoleUsers($roleId)
    // - Groups: GetGroupUsers->getAllUsersInGroup($groupId)
    
    // Get email addresses
    foreach ($recipientsList as $userId) {
        $userEmail = getUserEmail($userId);
        $recipientsEmails[$userName] = $userEmail;
    }
    
    // Add specific email addresses
    $specificemails = Zend_Json::decode($this->get('specificemails'));
    $recipientsEmails = array_merge($recipientsEmails, $specificemails);
    
    return $recipientsEmails;
}
```

---

## Email Delivery

### Email Generation Process

```php
public function sendEmail() {
    $vtigerMailer = new Vtiger_Mailer();
    
    // Add recipients
    foreach ($recipientEmails as $name => $email) {
        $vtigerMailer->AddAddress($email, decode_html($name));
    }
    
    // Set email content
    $vtigerMailer->Subject = $reportname;
    $vtigerMailer->Body = $this->getEmailContent($reportRecordModel);
    
    // Generate report attachment based on format
    if ($reportFormat == 'CSV') {
        $fileName = $baseFileName . '.csv';
        $oReportRun->writeReportToCSVFile($filePath);
    } else if ($reportFormat == 'XLS') {
        $fileName = $baseFileName . '.xls';
        $oReportRun->writeReportToExcelFile($filePath);
    }
    
    $vtigerMailer->AddAttachment($path, $attachmentName);
    $status = $vtigerMailer->Send(true);
    
    // Cleanup - delete temporary files
    foreach ($attachments as $path) {
        unlink($path);
    }
    
    return $status;
}
```

### Email Template Structure

The email body includes:
- Company logo
- Auto-generated email notice
- Report name with link to CRM
- Report description

---

## Cron Job Integration

### Service File (`cron/modules/Reports/ScheduleReports.service`)

```php
<?php
vimport ('includes.runtime.Globals');
require_once 'modules/Reports/models/ScheduleReports.php';
Reports_ScheduleReports_Model::runScheduledReports();
```

### Cron Registration

```php
Vtiger_Cron::register(
    'ScheduleReports',                              // Name
    'cron/modules/Reports/ScheduleReports.service', // Handler path
    900,                                            // Frequency (15 minutes)
    'Reports',                                      // Module
    1,                                              // Status (active)
    4,                                              // Sequence
    'Recommended frequency for ScheduleReports is 15 mins' // Description
);
```

### Cron Execution Logic

```php
public static function runScheduledReports() {
    $util = new VTWorkflowUtils();
    $util->adminUser();  // Switch to admin context

    $scheduledReports = self::getScheduledReports();
    
    foreach ($scheduledReports as $reportId => $scheduledReport) {
        $reportRecordModel = Reports_Record_Model::getInstanceById($reportId);
        $reportType = $reportRecordModel->get('reporttype');
        
        if ($reportType == 'chart') {
            $status = $scheduledReport->sendEmail();
        } else {
            // Check if report has data before sending
            $query = $reportRecordModel->getReportSQL();
            $countQuery = $reportRecordModel->generateCountQuery($query);
            if ($reportRecordModel->getReportsCount($countQuery) > 0) {
                $status = $scheduledReport->sendEmail();
            }
        }
        
        $scheduledReport->updateNextTriggerTime();
    }
    
    $util->revertUser();
}
```

---

## User Interface Elements

### Form Fields Summary

| Field Name         | Type          | Required | HTML Element                       |
|--------------------|---------------|----------|-------------------------------------|
| `enable_schedule`  | Checkbox      | No       | `<input type="checkbox">`          |
| `schtypeid`        | Select        | Yes*     | `<select id="schtypeid">`          |
| `schdayoftheweek`  | Multi-select  | Yes*     | `<select multiple id="schdayoftheweek">` |
| `schdayofthemonth` | Multi-select  | Yes*     | `<select multiple id="schdayofthemonth">` |
| `schdate`          | Date          | Yes*     | `<input class="dateField" id="schdate">` |
| `schannualdates`   | Multi-select  | Yes*     | `<select multiple id="annualDates">` |
| `schtime`          | Time          | Yes*     | `<input class="timepicker-default">` |
| `recipients`       | Multi-select  | Yes*     | `<select multiple id="recipients">` |
| `specificemails`   | Text          | No       | `<input id="specificemails">`      |
| `fileformat`       | Select        | Yes*     | `<select id="fileformat">`         |

*Required when scheduling is enabled

---

## JavaScript Interactions

### Edit1.js Key Functions

```javascript
Reports_Edit1_Js({}, {
    
    // Register schedule events
    registerEventForScheduledReprots: function() {
        // Toggle schedule box visibility
        jQuery('input[name="enable_schedule"]').on('click', function(e) {
            var element = jQuery(e.currentTarget);
            var scheduleBoxContainer = jQuery('#scheduleBox');
            if(element.is(':checked')) {
                scheduleBoxContainer.removeClass('hide');
            } else {
                scheduleBoxContainer.addClass('hide');
            }
        });
        
        // Initialize UI components
        app.registerEventForTimeFields('#schtime', true);
        app.registerEventForDatePickerFields('#scheduleByDate', true);
        
        jQuery('#annualDates').chosen();
        jQuery('#schdayoftheweek').chosen();
        jQuery('#schdayofthemonth').chosen();
        jQuery('#recipients').chosen();
        
        // Annual date picker
        jQuery('#annualDatePicker').datepick({
            multiSelect: 100,
            monthsToShow: [1,2],
            onSelect: function(dates) {
                // Update annualDates select
            }
        });
    },
    
    // Handle schedule type change
    registerEventForChangeInScheduledType: function() {
        jQuery('#schtypeid').on('change', function(e) {
            var value = element.val();
            
            thisInstance.showScheduledTime();
            thisInstance.hideScheduledWeekList();
            thisInstance.hideScheduledMonthByDateList();
            thisInstance.hideScheduledAnually();
            thisInstance.hideScheduledSpecificDate();
            
            if(value == '2') { // Weekly
                thisInstance.showScheduledWeekList();
            } else if(value == '3') { // Monthly by day
                thisInstance.showScheduledMonthByDateList();
            } else if(value == '4') { // Annually
                thisInstance.showScheduledAnually();
            } else if(value == '5') { // Specific date
                thisInstance.showScheduledSpecificDate();
            }
        });
    },
    
    // Helper functions for visibility control
    showScheduledTime: function() { jQuery('#scheduledTime').removeClass('hide'); },
    hideScheduledTime: function() { jQuery('#scheduledTime').addClass('hide'); },
    showScheduledWeekList: function() { jQuery('#scheduledWeekDay').removeClass('hide'); },
    hideScheduledWeekList: function() { jQuery('#scheduledWeekDay').addClass('hide'); },
    // ... similar for other UI sections
});
```

---

## Laravel Migration Guide

### 1. Database Migration

```php
// database/migrations/xxxx_create_schedule_reports_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScheduleReportsTable extends Migration
{
    public function up()
    {
        Schema::create('schedule_reports', function (Blueprint $table) {
            $table->unsignedBigInteger('report_id')->primary();
            $table->tinyInteger('schedule_type'); // 1-5
            $table->json('recipients')->nullable();
            $table->json('schedule_date')->nullable();
            $table->time('schedule_time')->nullable();
            $table->json('day_of_week')->nullable();
            $table->json('day_of_month')->nullable();
            $table->json('annual_dates')->nullable();
            $table->json('specific_emails')->nullable();
            $table->timestamp('next_trigger_time')->nullable();
            $table->string('file_format', 10)->default('CSV');
            $table->timestamps();
            
            $table->foreign('report_id')->references('id')->on('reports')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('schedule_reports');
    }
}
```

### 2. Eloquent Model

```php
// app/Models/ScheduleReport.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ScheduleReport extends Model
{
    const SCHEDULED_DAILY = 1;
    const SCHEDULED_WEEKLY = 2;
    const SCHEDULED_MONTHLY_BY_DATE = 3;
    const SCHEDULED_ANNUALLY = 4;
    const SCHEDULED_ON_SPECIFIC_DATE = 5;
    
    protected $table = 'schedule_reports';
    protected $primaryKey = 'report_id';
    public $incrementing = false;
    
    protected $fillable = [
        'report_id',
        'schedule_type',
        'recipients',
        'schedule_date',
        'schedule_time',
        'day_of_week',
        'day_of_month',
        'annual_dates',
        'specific_emails',
        'next_trigger_time',
        'file_format'
    ];
    
    protected $casts = [
        'recipients' => 'array',
        'schedule_date' => 'array',
        'day_of_week' => 'array',
        'day_of_month' => 'array',
        'annual_dates' => 'array',
        'specific_emails' => 'array',
        'next_trigger_time' => 'datetime'
    ];
    
    public function report()
    {
        return $this->belongsTo(Report::class);
    }
    
    public function calculateNextTriggerTime(): Carbon
    {
        $time = Carbon::parse($this->schedule_time);
        
        switch ($this->schedule_type) {
            case self::SCHEDULED_DAILY:
                return Carbon::tomorrow()->setTimeFrom($time);
                
            case self::SCHEDULED_WEEKLY:
                return $this->getNextWeeklyTrigger($time);
                
            case self::SCHEDULED_MONTHLY_BY_DATE:
                return $this->getNextMonthlyTrigger($time);
                
            case self::SCHEDULED_ANNUALLY:
                return $this->getNextAnnualTrigger($time);
                
            case self::SCHEDULED_ON_SPECIFIC_DATE:
                return Carbon::parse($this->schedule_date[0])->setTimeFrom($time);
        }
    }
}
```

### 3. Form Request Validation

```php
// app/Http/Requests/ScheduleReportRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScheduleReportRequest extends FormRequest
{
    public function rules()
    {
        return [
            'enable_schedule' => 'nullable|boolean',
            'schedule_type' => 'required_if:enable_schedule,true|integer|in:1,2,3,4,5',
            'schedule_time' => 'required_if:enable_schedule,true|date_format:H:i',
            'day_of_week' => 'required_if:schedule_type,2|array',
            'day_of_week.*' => 'integer|between:1,7',
            'day_of_month' => 'required_if:schedule_type,3|array',
            'day_of_month.*' => 'integer|between:1,31',
            'annual_dates' => 'required_if:schedule_type,4|array',
            'annual_dates.*' => 'date_format:Y-m-d',
            'schedule_date' => 'required_if:schedule_type,5|date',
            'recipients' => 'required_if:enable_schedule,true|array',
            'recipients.*' => 'string|regex:/^(USER|GROUP|ROLE)::\d+$/',
            'specific_emails' => 'nullable|string',
            'file_format' => 'required_if:enable_schedule,true|in:CSV,XLS'
        ];
    }
}
```

### 4. Controller Method

```php
// app/Http/Controllers/ReportsController.php

public function store(ReportRequest $request, ScheduleReportRequest $scheduleRequest)
{
    $report = Report::create($request->validated());
    
    if ($request->boolean('enable_schedule')) {
        $scheduleData = $scheduleRequest->validated();
        $scheduleData['report_id'] = $report->id;
        
        $scheduleReport = ScheduleReport::updateOrCreate(
            ['report_id' => $report->id],
            $scheduleData
        );
        
        $scheduleReport->next_trigger_time = $scheduleReport->calculateNextTriggerTime();
        $scheduleReport->save();
    } else {
        ScheduleReport::where('report_id', $report->id)->delete();
    }
    
    return redirect()->route('reports.show', $report);
}
```

### 5. Blade View Component

```blade
{{-- resources/views/reports/partials/schedule-report.blade.php --}}

<div class="card mt-4">
    <div class="card-header">
        <div class="form-check">
            <input type="checkbox" 
                   class="form-check-input" 
                   name="enable_schedule" 
                   id="enable_schedule"
                   value="1"
                   {{ old('enable_schedule', $scheduleReport?->exists) ? 'checked' : '' }}>
            <label class="form-check-label fw-bold" for="enable_schedule">
                {{ __('reports.schedule_reports') }}
            </label>
        </div>
    </div>
    
    <div class="card-body" id="scheduleBox" style="{{ old('enable_schedule', $scheduleReport?->exists) ? '' : 'display: none;' }}">
        {{-- Schedule Type --}}
        <div class="mb-3">
            <label class="form-label">{{ __('reports.run_report') }}</label>
            <select name="schedule_type" id="schtypeid" class="form-select">
                <option value="1" {{ old('schedule_type', $scheduleReport?->schedule_type) == 1 ? 'selected' : '' }}>
                    {{ __('reports.daily') }}
                </option>
                <option value="2" {{ old('schedule_type', $scheduleReport?->schedule_type) == 2 ? 'selected' : '' }}>
                    {{ __('reports.weekly') }}
                </option>
                <option value="5" {{ old('schedule_type', $scheduleReport?->schedule_type) == 5 ? 'selected' : '' }}>
                    {{ __('reports.specific_date') }}
                </option>
                <option value="3" {{ old('schedule_type', $scheduleReport?->schedule_type) == 3 ? 'selected' : '' }}>
                    {{ __('reports.monthly_by_date') }}
                </option>
                <option value="4" {{ old('schedule_type', $scheduleReport?->schedule_type) == 4 ? 'selected' : '' }}>
                    {{ __('reports.yearly') }}
                </option>
            </select>
        </div>
        
        {{-- Weekly Days --}}
        <div class="mb-3" id="scheduledWeekDay" style="{{ old('schedule_type', $scheduleReport?->schedule_type) == 2 ? '' : 'display: none;' }}">
            <label class="form-label">{{ __('reports.on_these_days') }}</label>
            <select name="day_of_week[]" multiple class="form-select select2">
                @foreach(['Sunday' => 7, 'Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3, 'Thursday' => 4, 'Friday' => 5, 'Saturday' => 6] as $day => $value)
                    <option value="{{ $value }}" {{ in_array($value, old('day_of_week', $scheduleReport?->day_of_week ?? [])) ? 'selected' : '' }}>
                        {{ __('calendar.day_' . strtolower($day)) }}
                    </option>
                @endforeach
            </select>
        </div>
        
        {{-- Monthly Days --}}
        <div class="mb-3" id="scheduleMonthByDates" style="{{ old('schedule_type', $scheduleReport?->schedule_type) == 3 ? '' : 'display: none;' }}">
            <label class="form-label">{{ __('reports.on_these_days') }}</label>
            <select name="day_of_month[]" multiple class="form-select select2">
                @for($i = 1; $i <= 31; $i++)
                    <option value="{{ $i }}" {{ in_array($i, old('day_of_month', $scheduleReport?->day_of_month ?? [])) ? 'selected' : '' }}>
                        {{ $i }}
                    </option>
                @endfor
            </select>
        </div>
        
        {{-- Specific Date --}}
        <div class="mb-3" id="scheduleByDate" style="{{ old('schedule_type', $scheduleReport?->schedule_type) == 5 ? '' : 'display: none;' }}">
            <label class="form-label">{{ __('reports.choose_date') }}</label>
            <input type="date" name="schedule_date" class="form-control" 
                   value="{{ old('schedule_date', $scheduleReport?->schedule_date[0] ?? '') }}">
        </div>
        
        {{-- Annual Dates --}}
        <div class="mb-3" id="scheduleAnually" style="{{ old('schedule_type', $scheduleReport?->schedule_type) == 4 ? '' : 'display: none;' }}">
            <label class="form-label">{{ __('reports.select_month_and_day') }}</label>
            <select name="annual_dates[]" multiple class="form-select select2">
                @foreach(old('annual_dates', $scheduleReport?->annual_dates ?? []) as $date)
                    <option value="{{ $date }}" selected>{{ $date }}</option>
                @endforeach
            </select>
        </div>
        
        {{-- Time Picker --}}
        <div class="mb-3">
            <label class="form-label">{{ __('reports.at_time') }} <span class="text-danger">*</span></label>
            <input type="time" name="schedule_time" class="form-control" required
                   value="{{ old('schedule_time', $scheduleReport?->schedule_time?->format('H:i')) }}">
        </div>
        
        {{-- Recipients --}}
        <div class="mb-3">
            <label class="form-label">{{ __('reports.select_recipients') }} <span class="text-danger">*</span></label>
            <select name="recipients[]" multiple class="form-select select2" required>
                <optgroup label="{{ __('common.users') }}">
                    @foreach($users as $user)
                        <option value="USER::{{ $user->id }}" 
                            {{ in_array("USER::{$user->id}", old('recipients', $scheduleReport?->recipients ?? [])) ? 'selected' : '' }}>
                            {{ $user->full_name }}
                        </option>
                    @endforeach
                </optgroup>
                <optgroup label="{{ __('common.groups') }}">
                    @foreach($groups as $group)
                        <option value="GROUP::{{ $group->id }}" 
                            {{ in_array("GROUP::{$group->id}", old('recipients', $scheduleReport?->recipients ?? [])) ? 'selected' : '' }}>
                            {{ $group->name }}
                        </option>
                    @endforeach
                </optgroup>
                <optgroup label="{{ __('common.roles') }}">
                    @foreach($roles as $role)
                        <option value="ROLE::{{ $role->id }}" 
                            {{ in_array("ROLE::{$role->id}", old('recipients', $scheduleReport?->recipients ?? [])) ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </optgroup>
            </select>
        </div>
        
        {{-- Specific Emails --}}
        <div class="mb-3">
            <label class="form-label">{{ __('reports.specific_email_address') }}</label>
            <input type="text" name="specific_emails" class="form-control"
                   value="{{ old('specific_emails', is_array($scheduleReport?->specific_emails) ? implode(',', $scheduleReport->specific_emails) : '') }}"
                   placeholder="email1@example.com, email2@example.com">
        </div>
        
        {{-- File Format --}}
        <div class="mb-3">
            <label class="form-label">{{ __('reports.file_format') }}</label>
            <select name="file_format" class="form-select">
                <option value="CSV" {{ old('file_format', $scheduleReport?->file_format) == 'CSV' ? 'selected' : '' }}>CSV</option>
                <option value="XLS" {{ old('file_format', $scheduleReport?->file_format) == 'XLS' ? 'selected' : '' }}>Excel</option>
            </select>
        </div>
        
        {{-- Next Trigger Time --}}
        @if($scheduleReport?->next_trigger_time)
            <div class="alert alert-info">
                <strong>{{ __('reports.next_trigger_time') }}:</strong>
                {{ $scheduleReport->next_trigger_time->format('Y-m-d H:i') }} ({{ config('app.timezone') }})
            </div>
        @endif
    </div>
</div>
```

### 6. Laravel Scheduled Command

```php
// app/Console/Commands/ProcessScheduledReports.php

namespace App\Console\Commands;

use App\Models\ScheduleReport;
use Illuminate\Console\Command;

class ProcessScheduledReports extends Command
{
    protected $signature = 'reports:process-scheduled';
    protected $description = 'Process and send scheduled reports';

    public function handle()
    {
        $dueReports = ScheduleReport::where('next_trigger_time', '<=', now())
            ->whereNotNull('next_trigger_time')
            ->with('report')
            ->get();
        
        foreach ($dueReports as $scheduleReport) {
            try {
                $this->info("Processing report: {$scheduleReport->report->name}");
                
                // Generate and send report
                dispatch(new \App\Jobs\SendScheduledReport($scheduleReport));
                
                // Update next trigger time
                $scheduleReport->next_trigger_time = $scheduleReport->calculateNextTriggerTime();
                $scheduleReport->save();
                
            } catch (\Exception $e) {
                $this->error("Failed to process report {$scheduleReport->report_id}: {$e->getMessage()}");
            }
        }
        
        return Command::SUCCESS;
    }
}
```

### 7. Schedule Registration

```php
// app/Console/Kernel.php

protected function schedule(Schedule $schedule)
{
    $schedule->command('reports:process-scheduled')
             ->everyFifteenMinutes()
             ->withoutOverlapping();
}
```

---

## Summary

The Schedule Reports functionality is a comprehensive feature that allows automated report generation and distribution. Key aspects include:

1. **Flexibility**: Supports 5 different scheduling patterns
2. **Recipient Management**: Supports users, groups, roles, and specific email addresses
3. **File Formats**: CSV and Excel export options
4. **Integration**: Cron-based execution with the Vtiger workflow system
5. **UI**: Dynamic form with conditional field visibility

For Laravel migration, the main considerations are:
- Database schema using native JSON columns
- Eloquent model with proper casts
- Laravel's scheduler for cron execution
- Blade components with Alpine.js or vanilla JS for dynamic behavior
- Queued jobs for email sending
