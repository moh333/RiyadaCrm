# CTPowerBlocksFields Analysis

## Overview
**CTPowerBlocksFields** is a dynamic layout and field dependency manager for Vtiger CRM. It allows administrators to define rules that control the visibility, editability, and mandatory status of blocks and fields based on specific conditions. It also supports auto-population of field values.

This module is situated within **Settings > Other Settings** and provides a configuration interface to manage these rules per module.

## Architecture

### 1. Database Schema
The core configuration is stored in the `vtiger_ctpowerblocks` table. Based on the code analysis, the table structure likely includes:

| Column Name | Description |
| :--- | :--- |
| `ctpowerblockfieldsid` | Primary Key |
| `moduleid` | ID of the module (tabid) the rule applies to |
| `conditions` | JSON encoded conditions (Vtiger Expression format) |
| `defaultblockid` | Comma-separated IDs of blocks to act upon by default |
| `defaultfieldid` | Comma-separated IDs of fields to act upon by default |
| `hideblockid` | Blocks to hide when condition is met |
| `hidefieldid` | Fields to hide when condition is met |
| `showblockid` | Blocks to show when condition is met |
| `showfieldid` | Fields to show when condition is met |
| `readonlyfieldid` | Fields to make read-only |
| `mandatoryfieldid` | Fields to make mandatory |
| `mappingfields` | JSON for auto-population (Field Mapping) |
| `fieldname`, `fieldvalue` | (Legacy/Specific use) |

### 2. Backend Logic (PHP)

#### Module & Record Models
- **Path**: `modules/Settings/CTPowerBlocksFields/models/`
- **`Module.php`**: Handles listing of rules and fetching supported modules. It excludes system modules (like Home, Events, etc.) from the list.
- **`Record.php`**: Contains the core business logic.
    - **`getAddCaseCondition($moduletabid)`**: Fetches rules applicable for creating new records.
    - **`getEditCaseCondition($request)`**: Evaluates rules for existing records. It uses `VTExpressionsManager` and `CTVTJsonCondition` to evaluate the saved JSON conditions against the record's data.
    - **`getDetailRecordData(...)`**: Returns the specific visibility/property changes for a record.
    - **`getPowerLayoutHiddenRecordData(...)`**: Resolves block/field IDs to their labels and UI types for the frontend.

#### Actions & Views
- **Path**: `modules/Settings/CTPowerBlocksFields/views/`
- **`EditAjax.php`**: Renders the condition builder UI (`WorkFlowConditions.tpl`), reusing Vtiger's workflow condition components.
- **Path**: `modules/Settings/CTPowerBlocksFields/actions/`
- **`CTPowerBlocksFieldsAjax.php`** (presumed): Handles the Ajax requests triggered by the frontend to fetch rules.

### 3. Frontend Logic (JavaScript)

#### Script Injection
- The module registers a `HEADERSCRIPT` link via `vtlib_handler` in `CTPowerBlocksFields.php`:
  `layouts/v7/modules/CTPowerBlocksFields/resources/CTPowerBlocksFields.js`

#### Dynamic Behavior (`CTPowerBlocksFields.js`)
This script is loaded on module pages (Edit, Detail) and orchestrates the dynamic UI changes:
1.  **Initialization**:
    -   `bindEvents`: Calls `CTPowerBlocksFieldsAjax` (action: `getInitialFields`) to apply default visibility rules on page load.
2.  **Edit View**:
    -   `registerForNewRecordCondition`: Attaches `change` event listeners to specific fields.
    -   When a dependent field changes, it sends the current form data to the server (`CheckConditionAjax` / `AddRecordConditionAjax`).
    -   The server evaluates the rules and returns actions (hide/show blocks, set mandatory, etc.).
    -   The script applies these actions using jQuery (e.g., adding/removing `.hide` class, setting `required`, disabling inputs).
3.  **Detail View**:
    -   `registerForDetailViewCondition`: Similar logic for Summary/Detail views to hide/show blocks/fields based on the record's static data.

## Features Logic Breakdown

### Conditional Visibility
- **Logic**: If `Condition X` is met, then `Hide Block A` / `Show Field B`.
- **Implementation**: The backend returns lists of `hideblockid`, `showfieldid`, etc. The JS iterates through these lists and toggles the `.hide` CSS class on the corresponding DOM elements (usually `td` cells or `fieldset` blocks).

### Mandatory Fields
- **Logic**: If `Condition Y` is met, `Field C` becomes required.
- **Implementation**: JS adds the `required` attribute to the input element and appends a red asterisk (`<span class='redColor'>*</span>`) to the field label.

### Read-Only Fields
- **Logic**: If `Condition Z` is met, `Field D` cannot be edited.
- **Implementation**: JS sets the `disabled` property on the input element (`prop('disabled', true)`) and may disable pointer events for reference fields.

### Auto-Population
- **Logic**: If `Field E` changes to 'Value', set `Field F` to 'Result'.
- **Implementation**: Configured via `mappingfields`. The JS receives a mapping array and uses `val()` or `trigger('change')` to set values on target fields when conditions are met.

## Integration Point
The module uses **Vtiger's Event System** (`vtiger_link` table) to inject its JavaScript resources into the header of every page. It relies on standard Vtiger DOM structure (classes like `.fieldBlockContainer`, `.fieldLabel`, `.editViewContents`) to locate and manipulate elements.
