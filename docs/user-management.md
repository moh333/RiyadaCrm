# User Management Architecture

This document outlines the user management system within the CRM, covering Users, Roles, Profiles, Sharing Rules, Groups, and Login History.

## 1. Users

Users are the individuals who can log in to the CRM. Each user is associated with a specific Role and can be assigned to one or more Groups.

### Key Database Tables
*   `vtiger_users`: Main table storing user details (user_name, user_password, email1, status, etc.).
*   `vtiger_user2role`: Maps users to their assigned Role (one-to-one).
*   `vtiger_users2group`: Maps users to Groups (many-to-many).

### Key Concepts
*   **Authentication**: Users authenticate using a username and password (hashed).
*   **Status**: A user can be `Active` or `Inactive`. Inactive users cannot log in.
*   **Admin Users**: Users with `is_admin` set to 'on' have global access, bypassing most permission checks.
*   **Privilege Calculation**: Upon login or modification, user privileges are calculated and cached in `user_privileges/user_privileges_{userid}.php` or similar cache files for performance.

## 2. Roles

Roles define the hierarchical position of a user within the organization. This hierarchy plays a central role in the **Sharing Rules** logic.

### Key Database Tables
*   `vtiger_role`: Stores role definitions and their depth in the hierarchy.
*   `vtiger_role2profile`: Maps roles to Profiles. Note that permissions (Profiles) are attached to Roles, not directly to Users.

### Key Concepts
*   **Hierarchy**: Roles exist in a tree structure (`H1` -> `H2`, etc.).
    *   Users in higher roles can implicitly view/edit data owned by users in subordinate roles (if Sharing Rules allow "Role" based hierarchy).
*   **Reports To**: Every role (except the root) reports to a parent role.
*   **Profile Association**: A Role is associated with one or more Profiles. The privileges for a user are the *union* of all Profiles associated with their Role.

## 3. Profiles

Profiles determine **what** actions a user can perform (Create, View, Edit, Delete) on modules and specific fields. They act as the "Global Permissions" and "Field Level Security".

### Key Database Tables
*   `vtiger_profile`: Defines the profile itself.
*   `vtiger_profile2globalpermissions`: Global view/edit definitions.
*   `vtiger_profile2tab`: Module-level permissions (Access, Create, Edit, Delete).
*   `vtiger_profile2standardpermissions`: Granular standard actions (Edit, Delete, Create, View).
*   `vtiger_profile2field`: Field-level access (Visible, Read-Only).

### Key Concepts
*   **Module Access**: Defines if a user can see a module (e.g., Leads, Contacts) and what actions they can take (Create, Edit, View, Delete).
*   **Field Level Security**: Defines which fields within a module are visible or read-only.
*   **Global Permissions**: Can override module settings to allow "View All" or "Edit All" across the system.

## 4. Sharing Rules

Sharing Rules determine **which records** a user can see. While Profiles define *capabilities* (e.g., "Can edit Contacts"), Sharing Rules define *scope* (e.g., "Can edit *my* Contacts and my team's Contacts").

### Key Database Tables
*   `vtiger_def_org_share`: Organization Wide Defaults (OWD).
*   `vtiger_datashare_module_rel`: Exception rules for modules.

### Key Concepts
*   **Organization Wide Defaults (OWD)**: The baseline access for each module.
    *   *Private*: Users can only see their own records and those of their subordinates.
    *   *Public Read Only*: Users can see all records but only edit their own.
    *   *Public Read/Write/Delete*: Users can see and edit all records.
*   **Exceptions**: Rules that grant additional access beyond OWD.
    *   *Role to Role*: Share data from Role A to Role B.
    *   *Group to Group*: Share data from Group A to Group B.
    *   *Role/Group to Role/Group*: Cross-sharing rules.

## 5. Groups

Groups are collections of Users, Roles, and potentially other Groups. They are used for shared ownership and collaborative access control.

### Key Database Tables
*   `vtiger_groups`: Group definitions.
*   `vtiger_users2group`: Users in the group.
*   `vtiger_group2role`: Roles in the group.
*   `vtiger_group2rs`: Roles and Subordinates in the group.

### Key Concepts
*   **Shared Ownership**: Records can be assigned to a Group (via `smownerid`) rather than a specific user. All members of the group inherit access to the record.
*   **Visibility**: If a record is shared with a Group via Sharing Rules, all members see it.
*   **Composition**: A group can consist of individual Users, entire Roles, or "Roles and Subordinates".

## 6. Login History

Tracks user access to the system for auditing and security purposes.

### Key Database Tables
*   `vtiger_loginhistory`: Stores login events.

### Key Concepts
*   **Attributes Logged**:
    *   `user_name`: Who logged in.
    *   `user_ip`: IP address of the request.
    *   `login_time`: Timestamp of login.
    *   `logout_time`: Timestamp of logout.
    *   `status`: Status of the attempt (e.g., 'Signed in', 'Failed login').
*   **Usage**: Administrators use this to monitor system usage and detect unauthorized access attempts.

## 7. Global Code Locations

For developers looking to modify these features, the relevant code is primarily located in:

*   **Users Module**: `modules/Users/` (Core user logic, authentication)
*   **Settings Modules**:
    *   **Roles**: `modules/Settings/Roles/`
    *   **Profiles**: `modules/Settings/Profiles/`
    *   **Groups**: `modules/Settings/Groups/`
    *   **Sharing Rules**: `modules/Settings/SharingAccess/`
    *   **Login History**: `modules/Settings/LoginHistory/`

