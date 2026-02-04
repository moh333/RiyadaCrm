# Complete Automation & Scheduling Analysis - TenantCRM

**Generated:** 2026-02-04  
**Project:** TenantCRM (Vtiger CRM)  
**Version:** 7.x

---

## Table of Contents

1. [Executive Summary](#executive-summary)
2. [Automation Architecture Overview](#automation-architecture-overview)
3. [Workflow Automation System](#workflow-automation-system)
4. [Cron Task Scheduler](#cron-task-scheduler)
5. [Scheduled Reports](#scheduled-reports)
6. [Scheduled Imports](#scheduled-imports)
7. [Database Schema](#database-schema)
8. [Implementation Details](#implementation-details)
9. [Use Cases & Examples](#use-cases--examples)
10. [Best Practices](#best-practices)

---

## 1. Executive Summary

TenantCRM implements a comprehensive automation framework with four primary components:

### Automation Components

| Component | Purpose | Trigger Type | Execution |
|-----------|---------|--------------|-----------|
| **Workflows** | Business process automation | Event-based & Scheduled | Real-time & Queued |
| **Cron Tasks** | System maintenance tasks | Time-based | Background |
| **Scheduled Reports** | Automated report delivery | Time-based | Email delivery |
| **Scheduled Imports** | Recurring data imports | Time-based | Background |

### Key Capabilities

- **10 Workflow Task Types**: Email, SMS, Field Updates, Create Entity, Notifications, etc.
- **6 Execution Triggers**: On First Save, On Every Save, On Modify, Once, Scheduled, Manual
- **7 Schedule Types**: Hourly, Daily, Weekly, Bi-weekly, Monthly, Annually, Specific Date
- **Advanced Conditions**: Complex AND/OR logic with 20+ operators
- **Expression Engine**: Dynamic field calculations and transformations

---

## 2. Automation Architecture Overview

### System Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    AUTOMATION FRAMEWORK                      │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐     │
│  │  WORKFLOWS   │  │  CRON TASKS  │  │   REPORTS    │     │
│  │              │  │              │  │              │     │
│  │ • Event-based│  │ • System     │  │ • Scheduled  │     │
│  │ • Scheduled  │  │   Tasks      │  │   Delivery   │     │
│  │ • Manual     │  │ • Background │  │ • Email      │     │
│  └──────┬───────┘  └──────┬───────┘  └──────┬───────┘     │
│         │                  │                  │              │
│         └──────────────────┼──────────────────┘              │
│                            │                                 │
│                    ┌───────▼────────┐                       │
│                    │  TASK QUEUE    │                       │
│                    │  & SCHEDULER   │                       │
│                    └────────────────┘                       │
└─────────────────────────────────────────────────────────────┘
```

### Core Files & Directories

```
modules/com_vtiger_workflow/          # Workflow engine
├── VTWorkflowManager.inc             # Workflow CRUD & execution
├── VTTaskManager.inc                 # Task management
├── VTEventHandler.inc                # Event triggers
├── WorkFlowScheduler.php             # Scheduled workflow execution
├── VTJsonCondition.inc               # Condition evaluation
├── VTTaskQueue.inc                   # Delayed task queue
└── tasks/                            # Task implementations
    ├── VTEmailTask.inc
    ├── VTUpdateFieldsTask.inc
    ├── VTCreateEntityTask.inc
    └── [8 more task types]

modules/Settings/Workflows/           # Workflow UI
├── actions/                          # CRUD operations
├── models/                           # Business logic
└── views/                            # UI components

vtlib/Vtiger/Cron.php                # Cron task framework
modules/Settings/CronTasks/          # Cron task management UI
cron/                                # Cron execution scripts
modules/Reports/ScheduledReports.php # Report scheduling
```

---

## 3. Workflow Automation System

### 3.1 Workflow Types & Triggers

#### Execution Conditions

| ID | Type | Constant | When Executed | Use Case |
|----|------|----------|---------------|----------|
| 1 | **On First Save** | `ON_FIRST_SAVE` | Record creation only | Welcome emails, initial assignments |
| 2 | **Once** | `ONCE` | First time conditions met | One-time notifications |
| 3 | **On Every Save** | `ON_EVERY_SAVE` | Create & every update | Audit trails, logging |
| 4 | **On Modify** | `ON_MODIFY` | Updates only (not create) | Change notifications |
| 6 | **Scheduled** | `ON_SCHEDULE` | Time-based intervals | Recurring reminders, reports |
| 7 | **Manual** | `MANUAL` | User-triggered | Bulk operations |

#### Schedule Types (for ON_SCHEDULE workflows)

| Type | ID | Configuration | Example |
|------|----|--------------| --------|
| **Hourly** | 1 | Time: HH:MM | Every hour at :00 |
| **Daily** | 2 | Time: HH:MM | Every day at 9:00 AM |
| **Weekly** | 3 | Days + Time | Every Monday at 10:00 AM |
| **Specific Date** | 4 | Date & Time | 2026-12-31 23:59 |
| **Monthly by Date** | 5 | Dates + Time | 1st and 15th at 9:00 AM |
| **Monthly by Weekday** | 6 | Weekday + Time | First Monday at 10:00 AM |
| **Annually** | 7 | Dates + Time | Jan 1, Dec 25 at 9:00 AM |

### 3.2 Workflow Tasks

#### Available Task Types

| Task | Class | Execute | Description | Use Cases |
|------|-------|---------|-------------|-----------|
| **Send Email** | `VTEmailTask` | Queued | Send templated emails | Notifications, confirmations |
| **Update Fields** | `VTUpdateFieldsTask` | Immediate | Modify field values | Auto-calculations, status updates |
| **Create Entity** | `VTCreateEntityTask` | Immediate | Create related records | Lead conversion, invoice generation |
| **Create Todo** | `VTCreateTodoTask` | Immediate | Create task | Follow-up reminders |
| **Create Event** | `VTCreateEventTask` | Immediate | Create calendar event | Meeting scheduling |
| **SMS Notification** | `VTSMSTask` | Queued | Send SMS | Urgent alerts |
| **WhatsApp** | `VTWTSAPTask` | Queued | Send WhatsApp message | Customer engagement |
| **Push Notification** | `VTSendNotificationTask` | Queued | In-app notification | User alerts |
| **Invoke Method** | `VTEntityMethodTask` | Immediate | Call custom method | Custom business logic |
| **Dummy** | `VTDummyTask` | Immediate | Testing placeholder | Development/testing |

### 3.3 Condition Engine

#### Supported Operations

| Category | Operations | Example |
|----------|-----------|---------|
| **Equality** | is, is not, equal to, does not equal | `status is "Qualified"` |
| **Text** | contains, does not contain, starts with, ends with | `email contains "@gmail.com"` |
| **Comparison** | less than, greater than, ≤, ≥ | `amount > 10000` |
| **Empty Check** | is empty, is not empty | `description is not empty` |
| **Date** | before, after, between | `created_time after "2024-01-01"` |
| **Special Date** | is today, is tomorrow, is yesterday | `followup_date is today` |
| **Relative Date** | days ago, days later, in less than | `created_time less than 7 days ago` |
| **Change Detection** | has changed, has changed to, has changed from | `status has changed to "Closed Won"` |

#### Condition Structure (JSON)

```json
[
  {
    "fieldname": "annual_revenue",
    "operation": "greater than",
    "value": "100000",
    "valuetype": "rawtext",
    "joincondition": "and",
    "groupid": "1",
    "groupjoin": ""
  },
  {
    "fieldname": "leadsource",
    "operation": "is",
    "value": "Advertisement",
    "valuetype": "rawtext",
    "joincondition": "or",
    "groupid": "2",
    "groupjoin": "or"
  }
]
```

**Logic:** `(annual_revenue > 100000) OR (leadsource = "Advertisement")`

### 3.4 Expression Engine

#### Available Functions

| Function | Description | Example | Result |
|----------|-------------|---------|--------|
| `concat()` | Join strings | `concat(firstname, ' ', lastname)` | "John Doe" |
| `add_days()` | Add days to date | `add_days(createdtime, 7)` | +7 days |
| `sub_days()` | Subtract days | `sub_days(closingdate, 30)` | -30 days |
| `add_time()` | Add time | `add_time(time_start, '02:00')` | +2 hours |
| `format_date()` | Format date | `format_date(createdtime, 'Y-m-d')` | "2026-02-04" |
| `format_number()` | Format number | `format_number(amount, 2)` | "1,234.56" |
| `get_date()` | Current date | `get_date('today')` | Today's date |
| `uppercase()` | To uppercase | `uppercase(lastname)` | "DOE" |
| `lowercase()` | To lowercase | `lowercase(email)` | "john@example.com" |

#### Field References

```
$(fieldname : )                           # Current record field
$(related_field : (Module) fieldname)     # Related record field
$(account_id : (Accounts) accountname)    # Account name from contact
```

### 3.5 Workflow Execution Flow

```
┌─────────────────────────────────────────────────────────┐
│ 1. RECORD EVENT (Save/Update)                           │
└────────────────┬────────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────┐
│ 2. VTEventHandler Triggered                             │
│    - Get workflows for module                           │
│    - Filter by execution condition                      │
└────────────────┬────────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────┐
│ 3. EVALUATE CONDITIONS                                   │
│    - Parse JSON conditions                              │
│    - Apply AND/OR logic                                 │
│    - Check field values                                 │
└────────────────┬────────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────┐
│ 4. EXECUTE TASKS (if conditions met)                    │
│    ┌──────────────────┬──────────────────┐             │
│    │ Immediate Tasks  │  Queued Tasks    │             │
│    │ - Update Fields  │  - Send Email    │             │
│    │ - Create Entity  │  - SMS           │             │
│    │ Execute Now      │  Add to Queue    │             │
│    └──────────────────┴──────────────────┘             │
└─────────────────────────────────────────────────────────┘
```

---

## 4. Cron Task Scheduler

### 4.1 Cron Framework (`Vtiger_Cron`)

**File:** `vtlib/Vtiger/Cron.php`

#### Cron Task Status

| Status | ID | Description |
|--------|----|----|
| **Disabled** | 0 | Task won't run |
| **Enabled** | 1 | Ready to run |
| **Running** | 2 | Currently executing |
| **Completed** | 3 | Finished successfully |

#### Core Methods

```php
class Vtiger_Cron {
    // Status constants
    static $STATUS_DISABLED = 0;
    static $STATUS_ENABLED = 1;
    static $STATUS_RUNNING = 2;
    
    // Check if task should run
    function isRunnable()
    
    // Mark task as running
    function markRunning()
    
    // Mark task as finished
    function markFinished()
    
    // Register new cron task
    static function register($name, $handler_file, $frequency, $module)
    
    // Get all active tasks
    static function listAllActiveInstances()
}
```

### 4.2 System Cron Tasks

#### Default Cron Tasks

| Task Name | Module | Frequency | Handler | Purpose |
|-----------|--------|-----------|---------|---------|
| **Workflow** | com_vtiger_workflow | 15 min | `WorkFlowScheduler.php` | Execute scheduled workflows |
| **RecurringInvoice** | Invoice | 12 hours | `RecurringInvoice.service` | Generate recurring invoices |
| **SendReminder** | Calendar | 15 min | `SendReminder.service` | Send event reminders |
| **ScheduleReports** | Reports | 15 min | `ScheduleReports.service` | Send scheduled reports |
| **MailScanner** | MailManager | 15 min | `MailScanner.service` | Scan incoming emails |
| **Scheduled Import** | Import | 15 min | `ScheduledImport.service` | Run scheduled imports |

### 4.3 Cron Execution

**Main Cron File:** `vtigercron.php`

```php
// Executed by system cron every 15 minutes
require_once('vtlib/Vtiger/Cron.php');

$cronTasks = Vtiger_Cron::listAllActiveInstances();

foreach ($cronTasks as $cronTask) {
    if ($cronTask->isRunnable()) {
        $cronTask->markRunning();
        
        // Execute handler
        require_once($cronTask->getHandlerFile());
        
        $cronTask->markFinished();
    }
}
```

**System Cron Setup (Linux):**
```bash
*/15 * * * * /usr/bin/php /path/to/vtigercron.php
```

**Windows Task Scheduler:**
```batch
vtigercron.bat
```

---

## 5. Scheduled Reports

**File:** `modules/Reports/ScheduledReports.php`

### 5.1 Schedule Configuration

```php
class VTScheduledReport {
    static $SCHEDULED_HOURLY = 1;
    static $SCHEDULED_DAILY = 2;
    static $SCHEDULED_WEEKLY = 3;
    static $SCHEDULED_BIWEEKLY = 4;
    static $SCHEDULED_MONTHLY = 5;
    static $SCHEDULED_ANNUALLY = 6;
}
```

### 5.2 Report Delivery

#### Supported Formats
- **PDF**: Formatted report
- **Excel**: XLS format
- **Both**: PDF + Excel

#### Recipient Types
- **Users**: Individual users
- **Groups**: User groups
- **Roles**: All users in role
- **Role & Subordinates**: Role hierarchy

### 5.3 Execution Process

```
┌─────────────────────────────────────────────────────────┐
│ 1. Cron triggers ScheduleReports.service                │
└────────────────┬────────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────┐
│ 2. Get reports where next_trigger_time <= NOW()         │
└────────────────┬────────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────┐
│ 3. For each report:                                      │
│    - Generate PDF/Excel                                 │
│    - Get recipient emails                               │
│    - Send email with attachments                        │
│    - Calculate next trigger time                        │
│    - Update database                                    │
└─────────────────────────────────────────────────────────┘
```

---

## 6. Scheduled Imports

**File:** `cron/modules/Import/ScheduledImport.service`

### 6.1 Import Scheduling

Allows recurring data imports from:
- CSV files
- External sources
- FTP locations

### 6.2 Configuration

Stored in `vtiger_scheduled_imports` table:
- Import source
- Field mapping
- Schedule frequency
- Next execution time

---

## 7. Database Schema

### 7.1 Workflow Tables

#### com_vtiger_workflows
```sql
CREATE TABLE com_vtiger_workflows (
    workflow_id INT PRIMARY KEY AUTO_INCREMENT,
    module_name VARCHAR(100),
    summary TEXT,
    test TEXT,                    -- JSON conditions
    execution_condition INT,      -- 1-7 (trigger type)
    defaultworkflow INT,
    type VARCHAR(255),
    filtersavedinnew INT,
    schtypeid INT,               -- Schedule type (1-7)
    schtime TIME,                -- Schedule time
    schdayofmonth VARCHAR(100),  -- JSON: [1,15,30]
    schdayofweek VARCHAR(100),   -- JSON: [1,3,5]
    schannualdates TEXT,         -- JSON: ["01-01","12-25"]
    nexttrigger_time DATETIME,   -- Next execution
    status INT,                  -- 1=active, 0=inactive
    workflowname VARCHAR(255)
);
```

#### com_vtiger_workflowtasks
```sql
CREATE TABLE com_vtiger_workflowtasks (
    task_id INT PRIMARY KEY AUTO_INCREMENT,
    workflow_id INT,
    summary VARCHAR(400),
    task BLOB                    -- Serialized task object
);
```

#### com_vtiger_workflowtask_queue
```sql
CREATE TABLE com_vtiger_workflowtask_queue (
    task_id INT,
    entity_id VARCHAR(100),
    do_after DATETIME,           -- Execute after this time
    task_contents TEXT           -- Serialized task data
);
```

#### com_vtiger_workflow_activatedonce
```sql
CREATE TABLE com_vtiger_workflow_activatedonce (
    workflow_id INT,
    entity_id INT,
    PRIMARY KEY (workflow_id, entity_id)
);
```

### 7.2 Cron Tables

#### vtiger_cron_task
```sql
CREATE TABLE vtiger_cron_task (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) UNIQUE,
    handler_file VARCHAR(100) UNIQUE,
    frequency INT,               -- Seconds between runs
    laststart INT(11) UNSIGNED,  -- Unix timestamp
    lastend INT(11) UNSIGNED,    -- Unix timestamp
    status INT,                  -- 0-3
    module VARCHAR(100),
    sequence INT,
    description TEXT
);
```

#### vtiger_cron_log
```sql
CREATE TABLE vtiger_cron_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100),
    start INT(11),
    end INT(11),
    iteration INT,
    status INT
);
```

### 7.3 Scheduled Reports Tables

#### vtiger_scheduled_reports
```sql
CREATE TABLE vtiger_scheduled_reports (
    reportid INT PRIMARY KEY,
    schedule TEXT,               -- JSON schedule config
    format VARCHAR(10),          -- pdf, excel, both
    recipients TEXT,             -- JSON recipients
    next_trigger_time DATETIME
);
```

---

## 8. Implementation Details

### 8.1 Email Task Implementation

**File:** `modules/com_vtiger_workflow/tasks/VTEmailTask.inc`

```php
class VTEmailTask extends VTTask {
    public $executeImmediately = false;  // Queued execution
    
    public $fromEmail;
    public $fromName;
    public $toEmail;
    public $ccEmail;
    public $bccEmail;
    public $subject;
    public $content;
    public $emailtemplateid;
    public $recepient;           // Field name or email
    public $replyTo;
    public $signature;
    
    public function doTask($entity) {
        // 1. Parse recipient template
        // 2. Merge email template
        // 3. Replace merge tags
        // 4. Create mailer instance
        // 5. Add recipients & attachments
        // 6. Send email
        // 7. Log to Emails module
    }
}
```

**Merge Tags:**
- `$fieldname` - Current record field
- `$(related_field : (Module) fieldname)` - Related field
- `$logo` - Company logo
- `$signature` - User signature

### 8.2 Update Fields Task

**File:** `modules/com_vtiger_workflow/tasks/VTUpdateFieldsTask.inc`

```php
class VTUpdateFieldsTask extends VTTask {
    public $executeImmediately = true;  // Immediate execution
    
    public $field_value_mapping;  // JSON array
    
    public function doTask($entity) {
        // 1. Parse field mappings
        // 2. Evaluate expressions
        // 3. Handle reference fields
        // 4. Update entity
        // 5. Save changes
    }
}
```

**Field Mapping Example:**
```json
[
  {
    "fieldname": "followupdate",
    "value": "add_days(createdtime, 7)",
    "valuetype": "expression"
  },
  {
    "fieldname": "status",
    "value": "Qualified",
    "valuetype": "rawtext"
  }
]
```

### 8.3 Create Entity Task

**File:** `modules/com_vtiger_workflow/tasks/VTCreateEntityTask.inc`

```php
class VTCreateEntityTask extends VTTask {
    public $entity_type;          // Module to create
    public $reference_field;      // Link back field
    public $field_value_mapping;  // Field values
    
    public function doTask($entity) {
        // 1. Create new entity instance
        // 2. Map field values
        // 3. Set reference field
        // 4. Save entity
        // 5. Create relationship
    }
}
```

---

## 9. Use Cases & Examples

### 9.1 Welcome Email on Contact Creation

**Trigger:** On First Save  
**Module:** Contacts  
**Condition:** `email is not empty`

**Tasks:**
1. **Send Email**
   - To: `$(email : )`
   - Subject: "Welcome to Our Company"
   - Template: Welcome Email Template

### 9.2 Lead Qualification Workflow

**Trigger:** On Every Save  
**Module:** Leads  
**Conditions:**
- `annual_revenue > 100000` AND
- `rating = "Hot"`

**Tasks:**
1. **Update Fields**
   - `status` = "Qualified"
   - `assigned_user_id` = Sales Manager
2. **Create Todo**
   - Subject: "Follow up with qualified lead"
   - Due Date: `add_days(createdtime, 1)`

### 9.3 Opportunity Won - Create Invoice

**Trigger:** On Modify  
**Module:** Potentials  
**Condition:** `sales_stage has changed to "Closed Won"`

**Tasks:**
1. **Create Entity** (Invoice)
   - Reference: `potential_id`
   - Fields:
     - `subject` = `$(subject : )`
     - `account_id` = `$(account_id : )`
     - `amount` = `$(amount : )`
2. **Send Email**
   - To: Account email
   - Subject: "Invoice Generated"

### 9.4 Daily Overdue Tasks Reminder

**Trigger:** Scheduled (Daily at 9:00 AM)  
**Module:** Calendar  
**Conditions:**
- `status != "Completed"`
- `due_date < get_date('today')`

**Tasks:**
1. **Send Email**
   - To: `$(assigned_user_id : (Users) email1)`
   - Subject: "Overdue Task Reminder"
   - Content: Task details

### 9.5 Monthly Sales Report

**Type:** Scheduled Report  
**Schedule:** Monthly (1st day at 9:00 AM)  
**Format:** PDF + Excel  
**Recipients:** Sales Team Role

**Configuration:**
- Report: Monthly Sales Summary
- Delivery: Email attachment
- Next Trigger: Calculated automatically

---

## 10. Best Practices

### 10.1 Workflow Design

✅ **DO:**
- Use descriptive workflow names
- Test conditions thoroughly
- Use "Once" trigger for one-time actions
- Queue heavy tasks (emails, SMS)
- Use expressions for dynamic values
- Document complex workflows

❌ **DON'T:**
- Create circular workflows
- Use "On Every Save" unnecessarily
- Hardcode values that may change
- Create too many workflows per module
- Ignore error handling

### 10.2 Performance Optimization

**Immediate vs Queued Execution:**
- **Immediate**: Field updates, entity creation (< 1 second)
- **Queued**: Emails, SMS, external API calls

**Scheduled Workflows:**
- Limit to 10 active scheduled workflows
- Use appropriate frequency
- Monitor execution times
- Batch process when possible

### 10.3 Cron Task Management

**Frequency Guidelines:**
- Critical tasks: 5-15 minutes
- Regular tasks: 1-6 hours
- Heavy tasks: Daily/Weekly

**Monitoring:**
- Check `vtiger_cron_log` for stuck tasks
- Monitor execution times
- Set up alerts for failures

### 10.4 Email Task Best Practices

**Template Design:**
- Use merge tags for personalization
- Test with different data
- Include unsubscribe options
- Optimize for mobile

**Deliverability:**
- Configure SPF/DKIM
- Use valid from addresses
- Monitor bounce rates
- Respect opt-out preferences

### 10.5 Security Considerations

**Access Control:**
- Workflows run as admin user
- Validate field permissions
- Sanitize user inputs
- Audit workflow changes

**Data Protection:**
- Encrypt sensitive data
- Log workflow executions
- Review scheduled tasks regularly
- Backup workflow configurations

---

## Appendix A: Quick Reference

### Workflow Trigger IDs
```
1 = ON_FIRST_SAVE
2 = ONCE
3 = ON_EVERY_SAVE
4 = ON_MODIFY
6 = ON_SCHEDULE
7 = MANUAL
```

### Schedule Type IDs
```
1 = HOURLY
2 = DAILY
3 = WEEKLY
4 = SPECIFIC_DATE
5 = MONTHLY_BY_DATE
6 = MONTHLY_BY_WEEKDAY
7 = ANNUALLY
```

### Cron Status IDs
```
0 = DISABLED
1 = ENABLED
2 = RUNNING
3 = COMPLETED
```

### Common Expressions
```php
// Date calculations
add_days(createdtime, 7)
sub_days(closingdate, 30)
get_date('today')

// String operations
concat(firstname, ' ', lastname)
uppercase(lastname)
lowercase(email)

// Field references
$(fieldname : )
$(account_id : (Accounts) accountname)
```

---

**Document Version:** 1.0  
**Last Updated:** 2026-02-04  
**Author:** System Analysis
