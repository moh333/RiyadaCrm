# User Management: Add/Edit Forms Specification

This document specifies the structure, fields, and logic for the **Add** and **Edit** forms of the **Users** module. These forms are used by administrators to provision and manage user accounts within the CRM.

## 1. Overview
The User form allows for the creation and modification of system users. It handles authentication credentials, user profile information, role-based access control assignment, and personal preferences (currency, timezone, etc.).

**Associated Files:**
*   **Controller/View**: `modules/Users/views/Edit.php`
*   **Model**: `modules/Users/Users.php`
*   **Template**: `layouts/vlayout/modules/Users/EditView.tpl`

## 2. Form Structure
The form is organized into logical blocks (sections) to group related information. The layout is dynamically rendered based on the `RecordStructureModel`, but the standard blocks are defined as follows:

### Block 1: Login Details
This block captures the credentials and system status of the user.

| Field Label | Field Name | Type | Mandatory? | Notes |
| :--- | :--- | :--- | :--- | :--- |
| **User Name** | `user_name` | Text | **Yes** | Unique system identifier. Cannot be changed after creation in some configurations. |
| **Password** | `user_password` | Password | **Yes** (Add) | Encrypted (MD5/Blowfish). On Edit, leave blank to keep current password. |
| **Confirm Password** | `confirm_password` | Password | **Yes** (Add) | Must match Password. |
| **Primary Email** | `email1` | Email | **Yes** | Used for notifications and password recovery. |
| **Role** | `roleid` | Reference | **Yes** | Selects the user's Role in the hierarchy (e.g., CEO, Sales Manager). |
| **Status** | `status` | Picklist | No | Options: `Active`, `Inactive`. Inactive users cannot log in. |
| **Admin** | `is_admin` | Checkbox | No | If checked, grants global super-user privileges. |

### Block 2: User Information
General profile information for the user.

| Field Label | Field Name | Type | Mandatory? | Notes |
| :--- | :--- | :--- | :--- | :--- |
| **First Name** | `first_name` | Text | No | |
| **Last Name** | `last_name` | Text | **Yes** | |
| **Office Phone** | `phone_work` | Phone | No | |
| **Mobile** | `phone_mobile` | Phone | No | |
| **Title** | `title` | Text | No | Job Title. |
| **Department** | `department` | Text | No | |
| **Signature** | `signature` | Textarea | No | Email signature. |
| **Reports To** | `reports_to_id` | Reference | No | The user to whom this user reports (Hierarchy). |

### Block 3: Address Information
Physical address details.

| Field Label | Field Name | Type | Mandatory? | Notes |
| :--- | :--- | :--- | :--- | :--- |
| **Street** | `address_street` | Textarea | No | |
| **City** | `address_city` | Text | No | |
| **State** | `address_state` | Text | No | |
| **Postal Code** | `address_postalcode` | Text | No | |
| **Country** | `address_country` | Text | No | |

### Block 4: User Configuration / Preferences
Settings that affect how the user views data.

| Field Label | Field Name | Type | Mandatory? | Notes |
| :--- | :--- | :--- | :--- | :--- |
| **Currency** | `currency_id` | Picklist | No | Default currency for the user. |
| **Date Format** | `date_format` | Picklist | No | e.g., `dd-mm-yyyy`, `yyyy-mm-dd`. |
| **Time Zone** | `time_zone` | Picklist | No | e.g., `UTC`, `Asia/Calcutta`. |
| **Number Format** | `currency_grouping_separator` | Picklist | No | Decimal and grouping separators. |

### Block 5: Tag Cloud Display
*   **Tag Cloud**: Checkbox (`tagcloudview`) to enable/disable the tag cloud widget on the home page.

## 3. Business Logic & Validation

### Validation Rules
1.  **Mandatory Fields**: The form must validate that `User Name`, `Last Name`, `Role`, and `Primary Email` are provided.
2.  **Unique User Name**: The `user_name` must be unique across the `vtiger_users` table.
3.  **Password Complexity**: (Configurable) Should generally enforce minimum length.
4.  **Confirm Password**: `user_password` and `confirm_password` must match exactly on creation or password change.
5.  **Duplicate Check**: The system checks for duplicate `user_name` upon submission (`Users::verify_data()`).

### Processing Logic (`Users.php`)
*   **Password Encryption**: Passwords are hashed using MD5 or Blowfish (depending on PHP version/config) before storage. Salt is often based on the username.
*   **Access Key**: A random `accesskey` is generated for API access upon creation (`Users::createAccessKey()`).
*   **User Hash**: A unique hash is generated for the user session management.
*   **Privilege Files**: Upon save, the system regenerates the user privilege file (`user_privileges/user_privileges_{id}.php`) to reflect the new Role and Profile permissions.

## 4. UI/UX Implementation
*   **Layout**: The form uses a 2-column layout for fields, with blocks separating major sections.
*   **Template**: `EditView.tpl` iterates over standard Vtiger field models (`FieldModel`).
*   **References**: The `Role` and `Reports To` fields use a popup or select interface to choose from existing Roles/Users.
