# Reports Module Deep Analysis & Laravel Re-coding Plan

## 1. Overview
The Reports module in VTiger CRM is a powerful tool for generating custom data reports. It allows users to select primary and secondary modules, choose specific fields to display, apply complex filters, and perform aggregate calculations (Sum, Average, etc.). The module supports both tabular and summary reports, as well as charts.

---

## 2. Database Schema Analysis

The Reports module relies on several interconnected tables to store report configurations, logic, and sharing permissions.

| Table Name | Description | Key Fields |
| :--- | :--- | :--- |
| `vtiger_report` | Core report metadata. | `reportid`, `folderid`, `reportname`, `description`, `reporttype`, `queryid` |
| `vtiger_selectquery` | Represents the query object associated with a report. | `queryid` |
| `vtiger_reportmodules` | Defines module relationships for the report. | `reportmodulesid` (FK to reportid), `primarymodule`, `secondarymodules` |
| `vtiger_reportfolder` | Logical grouping of reports in folders. | `folderid`, `foldername`, `description` |
| `vtiger_selectcolumn` | Fields selected to be displayed in the report. | `queryid`, `columnindex`, `columnname` |
| `vtiger_reportsortcol` | Fields used for sorting. | `reportid`, `columnname`, `sortorder` |
| `vtiger_reportgroupbycolumn` | Fields used for grouping (Summary reports). | `reportid`, `sortcolname`, `dategroupbycriteria` |
| `vtiger_reportsummary` | Aggregate calculations (SUM, AVG, MIN, MAX). | `reportsummaryid`, `summarytype` (0=SUM, 1=AVG, 2=MIN, 3=MAX), `columnname` |
| `vtiger_reportdatefilter` | Standard predefined date range filters. | `datefilterid`, `datecolumnname`, `datefilter`, `startdate`, `enddate` |
| `vtiger_relcriteria` | Advanced filter conditions (WHERE clause filters). | `queryid`, `columnname`, `comparator`, `value`, `groupid` |
| `vtiger_relcriteria_grouping` | Grouping logic for advanced filters (AND/OR groups). | `queryid`, `groupid`, `group_condition`, `condition_expression` |
| `vtiger_reportsharing` | Role-based sharing for private reports. | `reportid`, `shareid`, `setype` (users, groups, roles, rs) |

---

## 3. Functionality Deep-Dive

### A. Listing Reports
- **Logic Location:** `Reports_ListView_Model::getListViewEntries()` and `Reports_Folder_Model::getReports()`.
- **Process:** 
    1. Fetch folders from `vtiger_reportfolder`.
    2. Join `vtiger_report` with `vtiger_reportmodules` and `vtiger_reportfolder`.
    3. Filter by permissions (checking `vtiger_report.owner` and `vtiger_reportsharing`).
    4. Group by folder for the UI tree view.

### B. Adding/Editing Reports (3-Step Process)
- **Controller:** `Reports_Edit_View` (steps 1, 2, 3) and `Reports_Save_Action`.

#### Step 1: Basic Information
- **Input:** Report Name, Description, Target Folder, Primary Module.
- **Secondary Modules:** Users can select related modules. The system dynamically fetches valid related modules based on the primary module's schema.

#### Step 2: Columns and Calculations
- **Display Fields:** Fetches fields from `vtiger_field` for primary and selected secondary modules.
- **Sorting:** User selects column and sort order (ASC/DESC).
- **Calculations:** Only available for numeric/currency fields. Options: Sum, Average, Lowest Value, Highest Value.

#### Step 3: Filters
- **Standard Filter:** Quick date filters (e.g., "This Month", "Last 30 Days") on a selected date field.
- **Advanced Filters:** Custom conditions using comparators (`equals`, `contains`, `greater than`, etc.). Supports grouping conditions with AND/OR logic.

### C. Execution & Results
- **Engine:** `ReportRun.php`
- **Workflow:** 
    1. Reads all config from the tables mentioned above.
    2. Dynamically constructs a heavy SQL query with multiple `LEFT JOIN`s based on module relationships.
    3. Handles field translations and formatting (currency symbols, date formats).
    4. Generates data for HTML view, CSV, or Excel export.

---

## 4. Laravel Re-coding Strategy

To modernize this module in Laravel, we should move away from the procedural query building and use a more structured approach.

### 4.1. Proposed Database Migrations & Models

We can use polymorphic relationships or dedicated models for report configurations.

- **Report Model:** `Report` (Main entry)
- **ReportFilter Model:** `ReportFilter` (Advanced criteria - polymorphic)
- **ReportColumn Model:** `ReportColumn` (Selected display fields)
- **ReportCalculation Model:** `ReportCalculation` (Aggregates)

### 4.2. API Design (Controllers)

| Endpoint | Method | Description |
| :--- | :--- | :--- |
| `/api/reports` | `GET` | List all reports (paginated, with search). |
| `/api/reports/folders` | `GET` | Get report folders hierarchy. |
| `/api/reports` | `POST` | Create a new report. |
| `/api/reports/{id}` | `GET` | Get report configuration (for editing). |
| `/api/reports/{id}` | `PUT/PATCH`| Update report configuration. |
| `/api/reports/{id}` | `DELETE` | Delete a report. |
| `/api/reports/{id}/run` | `GET` | Execute report and return JSON data. |
| `/api/reports/{id}/export` | `GET` | Download CSV/Excel. |

### 4.3. Dynamic Query Builder (The Core)

Instead of the complex `ReportRun.php`, use a Service class: `ReportExecutionService`.

1. **Relation Mapping:** Use Laravel's Eloquent relationships predefined in models.
2. **Dynamic Joins:** Use a Query Builder that maps report field names (e.g., `Accounts:accountname`) to database columns (`vtiger_account.accountname`).
3. **Filter Mapping:** Translate Report Comparators to Laravel Query Builder clauses:
   - `e` (equals) -> `->where('col', '=', $val)`
   - `c` (contains) -> `->where('col', 'LIKE', "%$val%")`
   - `bw` (between) -> `->whereBetween('col', [$v1, $v2])`

### 4.4. Frontend Architecture (React/Vue)
- **Wizard Component:** Use a multi-step form (XState or a simple stepper).
- **Drag & Drop:** Use `dnd-kit` or `react-beautiful-dnd` for reordering columns and sort fields.
- **Query Builder UI:** Use a component like `react-querybuilder` for the advanced filtering step.

---

## 5. Potential Challenges in Migration
1. **Cross-module Relationships:** VTiger handles many modules via `vtiger_crmentityrel`. Laravel needs a clean way to handle these many-to-many relationships without excessive manual joins.
2. **Performance:** Large reports can be slow. Implementation should support background processing (Laravel Jobs) for heavy reports with notification upon completion.
3. **Variable Schema:** Since fields are dynamic (custom fields), the query builder must be aware of the `vtiger_field` metadata at runtime.

---
**Prepared by:** Antigravity (Advanced Agentic AI)
**Date:** 2026-02-08
