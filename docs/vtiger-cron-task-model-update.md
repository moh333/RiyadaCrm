# VtigerCronTask Model - Updated! ✅

**Date:** 2026-02-04  
**Issue:** Missing helper methods in VtigerCronTask model  
**Status:** ✅ FIXED

---

## Problem

The `VtigerCronTask` model existed but was missing all the helper methods needed by the SchedulerController and views, causing the error:

```
Call to undefined method App\Models\Tenant\VtigerCronTask::isRunning()
```

---

## Solution

Updated the `VtigerCronTask` model with:

### 1. **Configuration**
```php
protected $table = 'vtiger_cron_task';
protected $primaryKey = 'id';
public $timestamps = false;
```

### 2. **Fillable Attributes**
```php
protected $fillable = [
    'name',
    'handler_file',
    'frequency',
    'laststart',
    'lastend',
    'status',
    'module',
    'sequence',
    'description',
];
```

### 3. **Casts**
```php
protected function casts(): array
{
    return [
        'id' => 'integer',
        'frequency' => 'integer',
        'laststart' => 'integer',
        'lastend' => 'integer',
        'status' => 'integer',
        'sequence' => 'integer',
    ];
}
```

### 4. **Accessor Methods (Attributes)**

#### `getStatusLabelAttribute()`
Returns human-readable status: "Active" or "Disabled"

#### `getLastRunAttribute()`
Converts `laststart` timestamp to readable date format

#### `getLastEndTimeAttribute()`
Converts `lastend` timestamp to readable date format

#### `getFrequencyLabelAttribute()`
Converts frequency in seconds to human-readable format:
- Less than 60s → "X seconds"
- Less than 1 hour → "X minutes"
- Less than 1 day → "X hours"
- 1 day or more → "X days"

### 5. **Helper Methods**

#### `isRunning(): bool`
Checks if task is currently running (has laststart but no lastend)

#### `isEnabled(): bool`
Checks if task is enabled (status == 1)

### 6. **Query Scopes**

#### `scopeActive($query)`
Filter only active tasks (status = 1)

#### `scopeByModule($query, string $module)`
Filter tasks by module name

---

## Usage Examples

### **In Controller:**
```php
// Get all active tasks
$activeTasks = VtigerCronTask::active()->get();

// Get tasks by module
$contactTasks = VtigerCronTask::byModule('Contacts')->get();

// Check if running
if ($task->isRunning()) {
    // Task is currently executing
}

// Check if enabled
if ($task->isEnabled()) {
    // Task is active
}
```

### **In Views:**
```blade
{{ $task->status_label }}        <!-- "Active" or "Disabled" -->
{{ $task->last_run }}             <!-- "2026-02-04 12:00:00" -->
{{ $task->last_end_time }}        <!-- "2026-02-04 12:05:00" -->
{{ $task->frequency_label }}      <!-- "5 minutes" -->

@if($task->isRunning())
    <span class="badge bg-info">Running</span>
@elseif($task->isEnabled())
    <span class="badge bg-success">Active</span>
@else
    <span class="badge bg-secondary">Disabled</span>
@endif
```

---

## Complete Method List

| Method | Type | Returns | Description |
|--------|------|---------|-------------|
| `getStatusLabelAttribute()` | Accessor | string | Human-readable status |
| `getLastRunAttribute()` | Accessor | ?string | Last run timestamp |
| `getLastEndTimeAttribute()` | Accessor | ?string | Last end timestamp |
| `getFrequencyLabelAttribute()` | Accessor | string | Human-readable frequency |
| `isRunning()` | Helper | bool | Check if task is running |
| `isEnabled()` | Helper | bool | Check if task is enabled |
| `scopeActive()` | Scope | Builder | Filter active tasks |
| `scopeByModule()` | Scope | Builder | Filter by module |

---

## Database Structure

**Table:** `vtiger_cron_task`

| Column | Type | Description |
|--------|------|-------------|
| `id` | int | Primary key |
| `name` | string | Task name |
| `handler_file` | string | PHP handler file path |
| `frequency` | int | Run frequency in seconds |
| `laststart` | int | Unix timestamp of last start |
| `lastend` | int | Unix timestamp of last end |
| `status` | int | 1 = Active, 0 = Disabled |
| `module` | string | Associated module (optional) |
| `sequence` | int | Execution order |
| `description` | string | Task description |

---

## Testing

### ✅ **Model Methods**
- [x] `isRunning()` works correctly
- [x] `isEnabled()` works correctly
- [x] `status_label` attribute works
- [x] `last_run` attribute works
- [x] `last_end_time` attribute works
- [x] `frequency_label` attribute works
- [x] `active()` scope works
- [x] `byModule()` scope works

### ✅ **Controller Integration**
- [x] Index page displays tasks
- [x] Statistics calculated correctly
- [x] Edit page shows task details
- [x] Update method works
- [x] Toggle status works
- [x] Run now works

### ✅ **View Integration**
- [x] All attributes display correctly
- [x] Status badges show correctly
- [x] Frequency displays in human format
- [x] Last run times display correctly

---

## File Modified

**File:** `app/Models/Tenant/VtigerCronTask.php`

**Lines Added:** ~90 lines
- Configuration: 3 lines
- Fillable: 11 lines
- Casts: 6 lines
- Accessors: 40 lines
- Helper methods: 15 lines
- Scopes: 15 lines

---

**Status:** ✅ PRODUCTION READY  
**Error Fixed:** Yes  
**All Methods Working:** Yes  
**Tested:** Yes

The VtigerCronTask model is now fully functional with all necessary helper methods! 🎉
