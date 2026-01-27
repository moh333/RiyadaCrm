# Contacts Module Analysis: Import, Export, Find Duplicates, Merge

This document provides a detailed analysis of the **Import**, **Export**, **Find Duplicates**, and **Merge** functionalities within the Contacts module of the vtiger CRM `TenantCRM` system. It covers both the business workflow (what the user experiences) and the technical implementation (how the code handles it).

---

## 1. Import

The Import functionality allows users to bulk-load Contact records from CSV files. It is handled by the generic `Import` module which is capable of importing data into most CRM modules.

### Business Flow
1.  **Initiation**: The user navigates to the Contacts List View and clicks the **Import** action.
2.  **File Selection**: The user selects a CSV file from their local machine.
3.  **Duplicate Handling Strategy**: The user selects how duplicates should be handled:
    *   **Skip**: Do not import if a duplicate is found.
    *   **Overwrite**: Update the existing record.
    *   **Merge**: Update specific fields on the existing record.
    *   The user defines which fields constitute a "match" (e.g., Email, Last Name).
4.  **Field Mapping**: The user maps columns from the CSV file to vtiger Contact fields (e.g., "First Name" -> `firstname`).
5.  **Execution**: The import runs in the background.
6.  **Summary**: A report is generated showing success count, failure count, and skipped records.

### Technical Implementation
*   **Module**: `modules/Import`
*   **Controller**: `Import_Data_Action` (in `modules/Import/actions/Data.php`).
*   **Core Logic**:
    *   **Reading**: Uses `Import_CSVReader_Reader` to parse the uploaded file.
    *   **Mapping**: Uses `Import_Map_Model` to store and retrieve the user's field mapping.
    *   **Saving**: Iterates through rows and creates `Vtiger_Record_Model` instances. It calls `CRMEntity::save('Contacts')` to persist data to `vtiger_contactdetails` and `vtiger_crmentity`.
    *   **Duplicate Check**: If duplicate handling is enabled, it queries `vtiger_contactdetails` or `vtiger_crmentity` using the specified match fields before insertion.
*   **Key Files**:
    *   `modules/Import/actions/Data.php`: Main entry point for processing the import.
    *   `modules/Import/models/Map.php`: Handles field mapping persistence.

---

## 2. Export

The Export functionality allows users to extract Contact records into a CSV file.

### Business Flow
1.  **Selection**: The user can select specific records in the List View or choose to export "All Records" or the "Current Page".
2.  **Initiation**: The user clicks **Export** from the More Actions menu.
3.  **Configuration**: A modal appears allowing the user to:
    *   Export selected records / all records / current page.
    *   Export data with unrelated fields (optional).
4.  **Download**: The system generates a CSV file and triggers a browser download.

### Technical Implementation
*   **Action**: `Vtiger_ExportData_Action` (in `modules/Vtiger/actions/ExportData.php`).
*   **View**: `export.php` (View for the settings modal).
*   **Core Logic**:
    *   **Query Generation**: The action calls `Vtiger_Module_Model::getExportQuery()`. Since `Contacts_Module_Model` does not override this, it uses the standard Vtiger query generator.
    *   **Query Execution**: It executes a query joining `vtiger_contactdetails`, `vtiger_contactaddress`, `vtiger_contactsubdetails`, `vtiger_contactscf` (custom fields), and `vtiger_crmentity`.
    *   **Sanitization**: Data fields are sanitized (tags removed, special chars handled) before writing to the stream.
*   **Key Files**:
    *   `modules/Vtiger/actions/ExportData.php`: Handles the query execution and CSV streaming.
    *   `modules/Vtiger/models/Module.php`: underlying logic for `getExportQuery`.

---

## 3. Find Duplicates

This feature helps identifying duplicate records based on specific field criteria preventing data redundancy.

### Business Flow
1.  **Initiation**: The user clicks **Find Duplicates** from the List View actions.
2.  **Criteria Selection**: The user selects the fields to match against (e.g., `Last Name`, `Email`, `Mobile`).
3.  **Result**: The system displays a list of groups. Each group contains records that share the same values for the selected fields.
4.  **Action**: From this view, the user can select records to **Delete** (if they are junk) or **Merge**.

### Technical Implementation
*   **View**: `Vtiger_FindDuplicates_View` (in `modules/Vtiger/views/FindDuplicates.php`).
*   **Core Logic**:
    *   It constructs a SQL query using `GROUP BY` on the selected fields and `HAVING COUNT(*) > 1`.
    *   It ignores deleted records (`vtiger_crmentity.deleted = 0`).
    *   The result is a list of records grouped by the duplicate values.
*   **UI Template**: `FindDuplicate.tpl`.

---

## 4. Merge

Merge is the resolution step after finding duplicates. It collapses multiple records into a single "Primary" record.

### Business Flow
1.  **Selection**: In the "Find Duplicates" screen, the user selects 2 or 3 records to merge and clicks **Merge**.
2.  **Resolution**: A side-by-side comparison view appears.
    *   **Primary Record**: The user selects which record ID will be preserved (the "Master").
    *   **Field Selection**: For every field (Name, Phone, etc.), the user picks which record's value to keep (e.g., keep Name from Record A, but Phone from Record B).
3.  **Execution**: The user clicks **Merge**.
4.  **Result**:
    *   The **Primary Record** is updated with the chosen values.
    *   **Related Records** (like Activities, Tickets, Potentials) from the non-primary records are moved to the Primary Record.
    *   The **Non-Primary Records** are deleted (moved to Trash).

### Technical Implementation
*   **Action**: `Vtiger_ProcessDuplicates_Action` (in `modules/Vtiger/actions/ProcessDuplicates.php`).
*   **Core Logic**:
    1.  **Update Primary**: It iterates through the incoming request fields. Values chosen by the user are set on the Primary Record's `Vtiger_Record_Model` and saved.
    2.  **Transfer Relations**: For each deleted record, it calls `$primaryRecordModel->transferRelationInfoOfRecords(array($deleteRecordId))`.
        *   This delegates to `CRMEntity::transferRelatedRecords`.
        *   This function executes SQL `UPDATE` statements on related tables (e.g., changing `contactid` in `vtiger_troubletickets` from the old ID to the new Primary ID).
    3.  **Delete Iteration**: The non-primary records are deleted via `$record->delete()`, setting `deleted=1` in `vtiger_crmentity`.
*   **Key Files**:
    *   `modules/Vtiger/actions/ProcessDuplicates.php`: High-level controller.
    *   `modules/Vtiger/models/Record.php` & `data/CRMEntity.php`: Low-level database updates and relationship transfers.
