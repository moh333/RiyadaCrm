# Settings Module Analysis: Automation & Workflows

**Generated:** 2026-02-02  
**Project:** TenantCRM (Vtiger CRM)  
**Version:** 7.x

---

## Table of Contents

1. [Executive Summary](#executive-summary)
2. [Module Overview](#module-overview)
3. [Workflow Engine Architecture](#workflow-engine-architecture)
4. [Database Schema](#database-schema)
5. [Workflow Types & Triggers](#workflow-types--triggers)
6. [Task Types](#task-types)
7. [Condition Engine](#condition-engine)
8. [Scheduled Workflows](#scheduled-workflows)
9. [Frontend Implementation](#frontend-implementation)
10. [API Reference](#api-reference)
11. [Best Practices](#best-practices)

---

## 1. Executive Summary

The Workflows module (also known as Automation) is the core automation engine in Vtiger CRM. It enables users to automate business processes by defining rules that trigger actions based on specific conditions.

**Key Capabilities:**
- **Event-Driven Automation**: Execute tasks when records are created, modified, or on schedule
- **10 Task Types**: Email, SMS, Field Updates, Create Entity, Notifications, and more
- **Advanced Conditions**: Complex filtering with AND/OR logic and expression support
- **Scheduled Execution**: Time-based workflows with flexible scheduling options
- **Multi-Module Support**: Works across all standard and custom modules

---

## 2. Module Overview

### 2.1 Directory Structure

```
modules/Settings/Workflows/
├── actions/
│   ├── DeleteAjax.php
│   ├── Save.php
│   ├── SaveAjax.php
│   ├── SaveWorkflow.php
│   ├── TaskAjax.php
│   └── ValidateExpression.php
├── models/
│   ├── EditTaskRecordStructure.php
│   ├── Field.php
│   ├── FilterRecordStructure.php
│   ├── ListView.php
│   ├── Module.php (149 lines)
│   ├── Record.php (533 lines)
│   ├── RecordStructure.php
│   ├── TaskRecord.php (141 lines)
│   └── TaskType.php
└── views/
    ├── CreateEntity.php
    ├── Edit.php (11,334 bytes)
    ├── EditAjax.php
    ├── EditTask.php
    ├── EditV7Task.php
    ├── List.php
    └── TasksList.php

modules/com_vtiger_workflow/
├── VTWorkflowManager.inc (729 lines)
├── VTTaskManager.inc (327 lines)
├── VTJsonCondition.inc
├── VTEntityCache.inc
├── VTEventHandler.inc
├── VTTaskQueue.inc
├── WorkFlowScheduler.php
├── expression_engine/
│   └── VTExpressionsManager.inc
└── tasks/
    ├── VTEmailTask.inc (438 lines)
    ├── VTUpdateFieldsTask.inc (422 lines)
    ├── VTCreateEntityTask.inc (242 lines)
    ├── VTCreateEventTask.inc
    ├── VTCreateTodoTask.inc
    ├── VTEntityMethodTask.inc
    ├── VTSMSTask.inc
    ├── VTSendNotificationTask.inc
    ├── VTWTSAPTask.inc
    └── VTDummyTask.inc
```

### 2.2 Core Components

| Component | Purpose |
|-----------|---------|
| **VTWorkflowManager** | Main workflow CRUD operations and execution control |
| **VTTaskManager** | Task creation, storage, and retrieval |
| **VTJsonCondition** | Condition evaluation engine |
| **VTTaskQueue** | Delayed task execution queue |
| **VTEntityCache** | Entity data caching for performance |
| **WorkFlowScheduler** | Cron-based scheduled workflow execution |

---

## 3. Workflow Engine Architecture

### 3.1 Execution Flow

```
Record Event (Save/Update)
    ↓
VTEventHandler Triggered
    ↓
VTWorkflowManager.getWorkflowsForModule()
    ↓
For Each Workflow:
    ↓
    Check Execution Condition (ON_FIRST_SAVE, ON_EVERY_SAVE, etc.)
    ↓
    Evaluate Conditions (VTJsonCondition)
    ↓
    If Conditions Met:
        ↓
        Workflow.performTasks()
        ↓
        For Each Task:
            ↓
            If executeImmediately = true:
                Task.doTask()
            Else:
                VTTaskQueue.queueTask()
```

### 3.2 Core Classes

#### VTWorkflowManager

**File:** `modules/com_vtiger_workflow/VTWorkflowManager.inc`

**Key Methods:**

```php
class VTWorkflowManager {
    // Trigger type constants
    static $ON_FIRST_SAVE = 1;
    static $ONCE = 2;
    static $ON_EVERY_SAVE = 3;
    static $ON_MODIFY = 4;
    static $ON_SCHEDULE = 6;
    static $MANUAL = 7;
    
    // Save workflow to database
    function save($workflow)
    
    // Retrieve all active workflows
    function getWorkflows()
    
    // Get workflows for specific module
    function getWorkflowsForModule($moduleName)
    
    // Get scheduled workflows ready to execute
    function getScheduledWorkflows($referenceTime)
    
    // Create new workflow instance
    function newWorkflow($moduleName)
    
    // Delete workflow
    function delete($id)
    
    // Import/Export
    function serializeWorkflow($workflow)
    function deserializeWorkflow($str)
}
```

#### Workflow Class

```php
class Workflow {
    // Schedule type constants
    static $SCHEDULED_HOURLY = 1;
    static $SCHEDULED_DAILY = 2;
    static $SCHEDULED_WEEKLY = 3;
    static $SCHEDULED_ON_SPECIFIC_DATE = 4;
    static $SCHEDULED_MONTHLY_BY_DATE = 5;
    static $SCHEDULED_MONTHLY_BY_WEEKDAY = 6;
    static $SCHEDULED_ANNUALLY = 7;
    
    // Properties
    public $id;
    public $moduleName;
    public $description;
    public $test; // JSON conditions
    public $executionCondition;
    public $status;
    
    // Evaluate conditions
    function evaluate($entityCache, $id)
    
    // Execute all tasks
    function performTasks($entityData, $relatedInfo = false)
    
    // Check if already executed for record
    function isCompletedForRecord($recordId)
    
    // Mark as completed
    function markAsCompletedForRecord($recordId)
    
    // Calculate next trigger time
    function getNextTriggerTime()
}
```

#### VTTaskManager

**File:** `modules/com_vtiger_workflow/VTTaskManager.inc`

```php
class VTTaskManager {
    // Save task (insert or update)
    public function saveTask($task)
    
    // Delete task
    public function deleteTask($taskId)
    
    // Create new task instance
    public function createTask($taskType, $workflowId)
    
    // Retrieve task from database
    public function retrieveTask($taskId)
    
    // Get all tasks for workflow
    public function getTasksForWorkflow($workflowId)
    
    // Deserialize task from database
    public function unserializeTask($str)
}
```

---

## 4. Database Schema

### 4.1 Core Tables

#### com_vtiger_workflows

**Purpose:** Stores workflow definitions

```sql
CREATE TABLE com_vtiger_workflows (
    workflow_id INT PRIMARY KEY,
    module_name VARCHAR(100),
    summary TEXT,
    test TEXT,                    -- JSON conditions
    execution_condition INT,      -- Trigger type
    defaultworkflow INT,
    type VARCHAR(255),
    filtersavedinnew INT,
    schtypeid INT,               -- Schedule type
    schtime TIME,                -- Schedule time
    schdayofmonth VARCHAR(100),  -- Days of month (JSON)
    schdayofweek VARCHAR(100),   -- Days of week (JSON)
    schdayofweekexclude VARCHAR(100),
    schannualdates TEXT,         -- Annual dates (JSON)
    nexttrigger_time DATETIME,   -- Next execution time
    status INT,                  -- 1=active, 0=inactive
    workflowname VARCHAR(255),
    timefrom TIME,
    timeto TIME
);
```

**Example Data:**
```
workflow_id: 1
module_name: Contacts
workflowname: Welcome Email
summary: Send welcome email to new contacts
execution_condition: 1 (ON_FIRST_SAVE)
test: [{"fieldname":"email","operation":"is not empty","value":"","valuetype":"rawtext"}]
status: 1
```

#### com_vtiger_workflowtasks

**Purpose:** Stores task definitions

```sql
CREATE TABLE com_vtiger_workflowtasks (
    task_id INT PRIMARY KEY,
    workflow_id INT,
    summary VARCHAR(400),
    task BLOB                    -- Serialized task object
);
```

#### com_vtiger_workflowtask_queue

**Purpose:** Queue for delayed task execution

```sql
CREATE TABLE com_vtiger_workflowtask_queue (
    task_id INT,
    entity_id VARCHAR(100),
    do_after DATETIME,           -- Execute after this time
    task_contents TEXT           -- Serialized task data
);
```

#### com_vtiger_workflow_activatedonce

**Purpose:** Track workflows executed once per record

```sql
CREATE TABLE com_vtiger_workflow_activatedonce (
    workflow_id INT,
    entity_id INT
);
```

#### com_vtiger_workflow_tasktypes

**Purpose:** Registry of available task types

```sql
CREATE TABLE com_vtiger_workflow_tasktypes (
    id INT PRIMARY KEY,
    tasktypename VARCHAR(255),
    label VARCHAR(255),
    classname VARCHAR(255),
    classpath VARCHAR(255),
    templatepath VARCHAR(255),
    modules TEXT,                -- JSON array of modules
    sourcemodule VARCHAR(255)
);
```

### 4.2 Relationships

```
com_vtiger_workflows (1) ←→ (N) com_vtiger_workflowtasks
com_vtiger_workflowtasks (1) ←→ (N) com_vtiger_workflowtask_queue
com_vtiger_workflows (1) ←→ (N) com_vtiger_workflow_activatedonce
```

---

## 5. Workflow Types & Triggers

### 5.1 Execution Conditions

| ID | Constant | Label | Description |
|----|----------|-------|-------------|
| 1 | ON_FIRST_SAVE | On First Save | Execute only when record is created |
| 2 | ONCE | Once | Execute once per record (first time conditions met) |
| 3 | ON_EVERY_SAVE | On Every Save | Execute on create and every update |
| 4 | ON_MODIFY | On Modify | Execute only when record is updated |
| 6 | ON_SCHEDULE | Scheduled | Execute at scheduled times |
| 7 | MANUAL | Manual | Execute manually by user |

### 5.2 Trigger Implementation

**ON_FIRST_SAVE Example:**
```php
if ($executionCondition == VTWorkflowManager::$ON_FIRST_SAVE) {
    if (!$entity->isNew()) {
        continue; // Skip if not new record
    }
    if ($workflow->evaluate($entityCache, $entity->getId())) {
        $workflow->performTasks($entity);
    }
}
```

**ON_MODIFY Example:**
```php
if ($executionCondition == VTWorkflowManager::$ON_MODIFY) {
    if ($entity->isNew()) {
        continue; // Skip if new record
    }
    if ($workflow->evaluate($entityCache, $entity->getId())) {
        $workflow->performTasks($entity);
    }
}
```

**ONCE Example:**
```php
if ($executionCondition == VTWorkflowManager::$ONCE) {
    if ($workflow->isCompletedForRecord($recordId)) {
        continue; // Already executed
    }
    if ($workflow->evaluate($entityCache, $entity->getId())) {
        $workflow->performTasks($entity);
        $workflow->markAsCompletedForRecord($recordId);
    }
}
```

---

## 6. Task Types

### 6.1 Available Tasks

| Task Type | Class | Description |
|-----------|-------|-------------|
| **Send Email** | VTEmailTask | Send email to recipients with template support |
| **Update Fields** | VTUpdateFieldsTask | Update field values using expressions |
| **Create Entity** | VTCreateEntityTask | Create related record |
| **Create Todo** | VTCreateTodoTask | Create task/todo |
| **Create Event** | VTCreateEventTask | Create calendar event |
| **SMS Notification** | VTSMSTask | Send SMS via SMS provider |
| **WhatsApp** | VTWTSAPTask | Send WhatsApp message |
| **Push Notification** | VTSendNotificationTask | Send in-app notification |
| **Invoke Method** | VTEntityMethodTask | Call custom entity method |
| **Dummy** | VTDummyTask | Placeholder for testing |

### 6.2 Task Structure

**Base Task Class:**
```php
abstract class VTTask {
    public $id;
    public $workflowId;
    public $summary;
    public $active;
    public $trigger;              // Delayed execution config
    public $executeImmediately;   // Execute now or queue
    
    // Execute the task
    public abstract function doTask($data);
    
    // Get field names for UI
    public abstract function getFieldNames();
    
    // Get task contents for queue
    public function getContents($entity)
    
    // Check if task has contents
    public function hasContents($entity)
}
```

### 6.3 VTEmailTask

**File:** `modules/com_vtiger_workflow/tasks/VTEmailTask.inc` (438 lines)

**Properties:**
```php
class VTEmailTask extends VTTask {
    public $fromEmail;
    public $fromName;
    public $toEmail;              // Comma-separated
    public $ccEmail;
    public $bccEmail;
    public $subject;
    public $content;
    public $emailtemplateid;      // Template ID
    public $recepient;            // Field name for recipient
}
```

**Key Features:**
- Email template support with merge tags
- Multiple recipients (To, CC, BCC)
- Attachment support
- HTML and plain text
- Reply-to configuration
- Email opt-out checking

**doTask() Flow:**
1. Get recipient email addresses
2. Parse email template (if selected)
3. Replace merge tags with entity data
4. Create mailer object
5. Add recipients, attachments
6. Send email
7. Log activity

### 6.4 VTUpdateFieldsTask

**File:** `modules/com_vtiger_workflow/tasks/VTUpdateFieldsTask.inc` (422 lines)

**Properties:**
```php
class VTUpdateFieldsTask extends VTTask {
    public $field_value_mapping;  // Array of field => expression
}
```

**Supported Operations:**
- Set field to static value
- Set field to another field's value
- Use expressions: `concat(firstname, ' ', lastname)`
- Date/time calculations: `add_days(date_field, 7)`
- Mathematical operations: `amount * 1.1`
- Reference field values: `$(account_id : accountname)`

**Example Mapping:**
```php
$field_value_mapping = [
    'followupdate' => 'add_days(createdtime, 7)',
    'status' => 'Qualified',
    'assigned_user_id' => '$(created_user_id : )'
];
```

### 6.5 VTCreateEntityTask

**File:** `modules/com_vtiger_workflow/tasks/VTCreateEntityTask.inc` (242 lines)

**Properties:**
```php
class VTCreateEntityTask extends VTTask {
    public $entity_type;          // Module to create
    public $reference_field;      // Field to link back
    public $field_value_mapping;  // Field values
}
```

**Use Cases:**
- Create Contact when Lead is converted
- Create Todo when Deal is won
- Create Invoice from Quote
- Create Support Ticket from Email

**Example:**
```php
// When Opportunity is won, create Invoice
$task = new VTCreateEntityTask();
$task->entity_type = 'Invoice';
$task->reference_field = 'potential_id';
$task->field_value_mapping = [
    'subject' => '$(subject : )',
    'account_id' => '$(account_id : )',
    'amount' => '$(amount : )'
];
```

---

## 7. Condition Engine

### 7.1 VTJsonCondition

**File:** `modules/com_vtiger_workflow/VTJsonCondition.inc`

**Condition Structure:**
```json
{
    "fieldname": "annual_revenue",
    "operation": "greater than",
    "value": "100000",
    "valuetype": "rawtext",
    "joincondition": "and",
    "groupid": "1"
}
```

### 7.2 Supported Operations

| Operation | Description | Example |
|-----------|-------------|---------|
| **is** | Equals | status is "Qualified" |
| **contains** | Contains substring | email contains "@gmail.com" |
| **does not contain** | Does not contain | phone does not contain "+1" |
| **starts with** | Starts with | company starts with "ABC" |
| **ends with** | Ends with | email ends with ".com" |
| **is empty** | Field is empty | description is empty |
| **is not empty** | Field has value | email is not empty |
| **less than** | Numeric/date comparison | amount less than 1000 |
| **greater than** | Numeric/date comparison | created_time greater than "2024-01-01" |
| **less than or equal to** | ≤ comparison | quantity ≤ 10 |
| **greater than or equal to** | ≥ comparison | rating ≥ 3 |
| **before** | Date before | followup before "2024-12-31" |
| **after** | Date after | created_time after "2024-01-01" |
| **between** | Range | amount between 1000 and 5000 |
| **has changed** | Field modified | status has changed |
| **has changed to** | Changed to specific value | status has changed to "Closed Won" |

### 7.3 Advanced Conditions

**AND/OR Logic:**
```json
[
    {
        "fieldname": "leadsource",
        "operation": "is",
        "value": "Advertisement",
        "joincondition": "and",
        "groupid": "1"
    },
    {
        "fieldname": "annual_revenue",
        "operation": "greater than",
        "value": "100000",
        "joincondition": "or",
        "groupid": "2"
    }
]
```

**Grouping:**
```
(Group 1: leadsource = "Advertisement" AND rating = "Hot")
OR
(Group 2: annual_revenue > 100000)
```

### 7.4 Expression Functions

**Available in VTExpressionsManager:**

| Function | Description | Example |
|----------|-------------|---------|
| `concat()` | Concatenate strings | `concat(firstname, ' ', lastname)` |
| `add_days()` | Add days to date | `add_days(createdtime, 7)` |
| `sub_days()` | Subtract days | `sub_days(closingdate, 30)` |
| `add_time()` | Add time | `add_time(time_start, '02:00')` |
| `sub_time()` | Subtract time | `sub_time(time_end, '01:00')` |
| `format_date()` | Format date | `format_date(createdtime, 'Y-m-d')` |
| `format_number()` | Format number | `format_number(amount, 2)` |
| `get_date()` | Current date | `get_date('today')` |
| `uppercase()` | Convert to uppercase | `uppercase(lastname)` |
| `lowercase()` | Convert to lowercase | `lowercase(email)` |

---

## 8. Scheduled Workflows

### 8.1 Schedule Types

| Type | ID | Description | Configuration |
|------|----|-----------|--------------| 
| **Hourly** | 1 | Every hour | Time: HH:MM |
| **Daily** | 2 | Every day | Time: HH:MM |
| **Weekly** | 3 | Specific days of week | Days: [1,2,3], Time: HH:MM |
| **Specific Date** | 4 | One-time execution | Date & Time |
| **Monthly by Date** | 5 | Specific dates each month | Dates: [1,15,30], Time: HH:MM |
| **Monthly by Weekday** | 6 | Specific weekday | Day: "First Monday", Time: HH:MM |
| **Annually** | 7 | Specific dates each year | Dates: ["01-01", "12-25"], Time: HH:MM |

### 8.2 Scheduler Implementation

**File:** `modules/com_vtiger_workflow/WorkFlowScheduler.php`

**Cron Job:**
```php
// In cron configuration
require_once('modules/com_vtiger_workflow/WorkFlowScheduler.php');
$workflowScheduler = new WorkFlowScheduler();
$workflowScheduler->performTasks();
```

**Execution Flow:**
1. Get current time
2. Query workflows where `nexttrigger_time <= NOW()`
3. For each workflow:
   - Get all records matching conditions
   - Execute tasks for each record
   - Calculate next trigger time
   - Update `nexttrigger_time`

**Next Trigger Calculation:**
```php
function getNextTriggerTime() {
    $scheduleType = $this->getWFScheduleType();
    
    switch($scheduleType) {
        case Workflow::$SCHEDULED_HOURLY:
            return date("Y-m-d H:i:s", strtotime("+1 hour"));
            
        case Workflow::$SCHEDULED_DAILY:
            return $this->getNextTriggerTimeForDaily($this->schtime);
            
        case Workflow::$SCHEDULED_WEEKLY:
            return $this->getNextTriggerTimeForWeekly(
                $this->schdayofweek, 
                $this->schtime
            );
    }
}
```

### 8.3 Scheduled Workflow Limits

```php
function getMaxAllowedScheduledWorkflows() {
    return 10; // Maximum 10 scheduled workflows
}
```

---

## 9. Frontend Implementation

### 9.1 JavaScript Files

**List View:** `layouts/v7/modules/Settings/Workflows/resources/List.js` (206 lines)

**Key Features:**
- Module filter with workflow count
- Enable/disable workflows via toggle switch
- Search workflows
- Delete workflows
- Navigate to edit view

**Edit View:** `layouts/v7/modules/Settings/Workflows/resources/Edit.js`

**Key Features:**
- Workflow configuration form
- Condition builder (Advanced Filter)
- Task management
- Expression validator
- Schedule configuration

### 9.2 Workflow List View

**Template:** `layouts/v7/modules/Settings/Workflows/ListViewContents.tpl`

**Features:**
- Filter by module
- Search by name/description
- Enable/disable toggle
- Workflow count per module
- Edit/Delete actions

**JavaScript:**
```javascript
// Module filter with count display
registerSelect2ForModuleFilter: function() {
    vtUtils.showSelect2ElementView(jQuery('#moduleFilter'), {
        formatResult: function(result) {
            var count = jQuery(result.element).data('count');
            return result.text + " - " + count;
        }
    });
}

// Toggle workflow status
registerEventForChangeWorkflowState: function() {
    jQuery(listViewContainer).on('switchChange.bootstrapSwitch', 
        "input[name='workflowstatus']", function(e) {
        var params = {
            'action': 'SaveAjax',
            'record': currentElement.data('id'),
            'status': currentElement.val()
        };
        app.request.post({data: params});
    });
}
```

### 9.3 Workflow Edit View

**Sections:**
1. **Basic Information**
   - Workflow name
   - Description
   - Module selection
   - Execution condition

2. **Conditions**
   - Advanced filter builder
   - AND/OR grouping
   - Expression support

3. **Schedule Configuration** (if ON_SCHEDULE)
   - Schedule type
   - Time selection
   - Day/date selection

4. **Tasks**
   - Add/Edit/Delete tasks
   - Task ordering
   - Task enable/disable

### 9.4 Condition Builder

**Template:** `layouts/v7/modules/Settings/Workflows/AdvanceFilter.tpl`

**Features:**
- Drag-and-drop field selection
- Operation dropdown
- Value input (text, picklist, date, etc.)
- Add/Remove conditions
- Group conditions
- AND/OR toggle

**JavaScript:**
```javascript
// Add new condition
addNewCondition: function() {
    var conditionGroup = this.getConditionGroup();
    var newCondition = this.getConditionTemplate();
    conditionGroup.append(newCondition);
    this.registerFieldChangeEvent(newCondition);
}

// Update operation based on field type
registerFieldChangeEvent: function(element) {
    element.on('change', '.fieldname', function() {
        var fieldType = getFieldType($(this).val());
        updateOperationDropdown(fieldType);
    });
}
```

---

## 10. API Reference

### 10.1 Settings_Workflows_Module_Model

**File:** `modules/Settings/Workflows/models/Module.php`

```php
class Settings_Workflows_Module_Model {
    // Get default URL
    public static function getDefaultUrl()
    
    // Get create record URL
    public static function getCreateRecordUrl()
    
    // Get supported modules
    public static function getSupportedModules()
    
    // Get trigger types
    public static function getTriggerTypes()
    
    // Get expression functions
    public static function getExpressions()
    
    // Get meta variables
    public static function getMetaVariables()
    
    // Get active workflow count
    public function getActiveWorkflowCount($moduleCount = false)
}
```

### 10.2 Settings_Workflows_Record_Model

**File:** `modules/Settings/Workflows/models/Record.php` (533 lines)

```php
class Settings_Workflows_Record_Model {
    // Get workflow ID
    public function getId()
    
    // Get workflow name
    public function getName()
    
    // Get tasks for workflow
    public function getTasks($active = false)
    
    // Save workflow
    public function save()
    
    // Delete workflow
    public function delete()
    
    // Get instance by ID
    public static function getInstance($workflowId)
    
    // Get clean instance
    public static function getCleanInstance($moduleName)
    
    // Transform to advanced filter condition
    public function transformToAdvancedFilterCondition()
    
    // Transform advanced filter to workflow filter
    public function transformAdvanceFilterToWorkFlowFilter()
    
    // Get dependent modules for create entity task
    public function getDependentModules()
    
    // Update next trigger time
    public function updateNextTriggerTime()
}
```

### 10.3 Settings_Workflows_TaskRecord_Model

**File:** `modules/Settings/Workflows/models/TaskRecord.php` (141 lines)

```php
class Settings_Workflows_TaskRecord_Model {
    // Get task ID
    public function getId()
    
    // Get task name
    public function getName()
    
    // Check if active
    public function isActive()
    
    // Get task object
    public function getTaskObject()
    
    // Get task type
    public function getTaskType()
    
    // Save task
    public function save()
    
    // Delete task
    public function delete()
    
    // Get all tasks for workflow
    public static function getAllForWorkflow($workflowModel, $active = false)
    
    // Get instance by ID
    public static function getInstance($taskId, $workflowModel = null)
    
    // Get clean instance
    public static function getCleanInstance($workflowModel, $taskName)
}
```

### 10.4 API Endpoints

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `Workflows&view=List` | GET | List workflows |
| `Workflows&view=Edit` | GET | Edit workflow form |
| `Workflows&action=Save` | POST | Save workflow |
| `Workflows&action=SaveAjax` | POST | Toggle workflow status |
| `Workflows&action=DeleteAjax` | POST | Delete workflow |
| `Workflows&action=SaveWorkflow` | POST | Save workflow with tasks |
| `Workflows&action=TaskAjax&mode=Delete` | POST | Delete task |
| `Workflows&action=TaskAjax&mode=ChangeStatus` | POST | Toggle task status |
| `Workflows&action=ValidateExpression` | POST | Validate expression syntax |
| `Workflows&view=EditTask` | GET | Edit task form |
| `Workflows&view=TasksList` | GET | List tasks for workflow |

---

## 11. Best Practices

### 11.1 Workflow Design

**Do's:**
✅ Use descriptive workflow names  
✅ Add detailed descriptions  
✅ Test conditions thoroughly  
✅ Use ON_FIRST_SAVE for welcome emails  
✅ Use ON_MODIFY for status change notifications  
✅ Limit scheduled workflows (max 10)  
✅ Use expressions for dynamic values  
✅ Enable only when fully tested  

**Don'ts:**
❌ Don't create circular workflows  
❌ Don't use ON_EVERY_SAVE for heavy tasks  
❌ Don't forget to test with real data  
❌ Don't create too many workflows per module  
❌ Don't use complex expressions without validation  
❌ Don't schedule workflows too frequently  

### 11.2 Performance Optimization

**Condition Optimization:**
```php
// Good: Specific conditions
if (status == "Qualified" AND annual_revenue > 100000)

// Bad: Too broad
if (status is not empty)
```

**Task Optimization:**
```php
// Good: Execute immediately for simple tasks
$task->executeImmediately = true;

// Good: Queue for heavy tasks (email, API calls)
$task->executeImmediately = false;
```

**Scheduled Workflow Optimization:**
- Run during off-peak hours
- Limit record count with conditions
- Use hourly instead of every minute
- Monitor execution time

### 11.3 Common Use Cases

**1. Lead Nurturing:**
```
Trigger: ON_FIRST_SAVE
Module: Leads
Condition: email is not empty
Task: Send Email (Welcome Template)
```

**2. Follow-up Reminder:**
```
Trigger: ON_SCHEDULE (Daily at 9:00 AM)
Module: Potentials
Condition: closingdate = today AND salesstage != "Closed Won"
Task: Create Todo (Follow-up with customer)
```

**3. Status Change Notification:**
```
Trigger: ON_MODIFY
Module: HelpDesk
Condition: ticketstatus has changed to "Closed"
Task: Send Email (Ticket Closed Notification)
```

**4. Auto-assign:**
```
Trigger: ON_FIRST_SAVE
Module: Contacts
Condition: leadsource = "Website"
Task: Update Fields (assigned_user_id = specific_user_id)
```

**5. Escalation:**
```
Trigger: ON_SCHEDULE (Hourly)
Module: HelpDesk
Condition: ticketstatus = "Open" AND createdtime < 24 hours ago
Task: Update Fields (ticketpriorities = "High")
      Send Email (Escalation Notification)
```

### 11.4 Debugging

**Enable Workflow Logging:**
```php
// In config.inc.php
$LOG_WORKFLOW = true;
```

**Check Workflow Execution:**
```sql
-- Check if workflow executed for record
SELECT * FROM com_vtiger_workflow_activatedonce 
WHERE workflow_id = ? AND entity_id = ?;

-- Check queued tasks
SELECT * FROM com_vtiger_workflowtask_queue 
WHERE entity_id = ?;

-- Check workflow status
SELECT * FROM com_vtiger_workflows 
WHERE workflow_id = ?;
```

**Common Issues:**

| Issue | Cause | Solution |
|-------|-------|----------|
| Workflow not executing | Status = 0 | Enable workflow |
| Task not running | Task active = 0 | Enable task |
| Email not sending | Email opt-out = 1 | Check recipient opt-out status |
| Scheduled workflow not running | Cron not configured | Setup cron job |
| Expression error | Invalid syntax | Use ValidateExpression endpoint |
| Circular dependency | Workflow triggers itself | Review conditions |

### 11.5 Security Considerations

**Permissions:**
- Only admin users can create/edit workflows
- Workflow executes with system privileges
- Be careful with field updates
- Validate email recipients

**Data Protection:**
- Don't expose sensitive data in emails
- Use role-based email templates
- Sanitize user inputs in expressions
- Audit workflow changes

---

## Appendix A: Meta Variables

Available in email templates and expressions:

| Variable | Description | Example |
|----------|-------------|---------|
| `$_DATE_FORMAT_` | Current date | 2024-02-02 |
| `(__VtigerMeta__) time` | Current time | 14:30:00 |
| `(__VtigerMeta__) dbtimezone` | System timezone | UTC |
| `(__VtigerMeta__) usertimezone` | User timezone | America/New_York |
| `(__VtigerMeta__) crmdetailviewurl` | CRM detail URL | http://crm.com/index.php?module=Contacts&view=Detail&record=123 |
| `(__VtigerMeta__) portaldetailviewurl` | Portal URL | http://portal.com/record/123 |
| `(__VtigerMeta__) siteurl` | Site URL | http://crm.com |
| `(__VtigerMeta__) portalurl` | Portal URL | http://portal.com |
| `(__VtigerMeta__) recordId` | Record ID | 123 |

---

## Appendix B: Task Type Registration

**Register Custom Task:**
```php
$taskType = [
    'name' => 'VTCustomTask',
    'label' => 'Custom Task',
    'classname' => 'VTCustomTask',
    'classpath' => 'modules/CustomModule/tasks/VTCustomTask.inc',
    'templatepath' => 'CustomModule/taskforms/VTCustomTask.tpl',
    'modules' => [
        'include' => ['Contacts', 'Leads'],
        'exclude' => []
    ],
    'sourcemodule' => ''
];

VTTaskType::registerTaskType($taskType);
```

---

## Document Information

**Author:** AI Analysis System  
**Date:** 2026-02-02  
**Version:** 1.0  
**Status:** Complete  

**Related Documents:**
- Settings Picklist Analysis
- User Management Analysis
- Roles Deep Analysis

---

*End of Document*
