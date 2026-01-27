# Role Management - vtiger CRM Model Implementation

## Overview
The role management forms have been updated to match vtiger CRM's authentic role-profile relationship model as documented in `docs/roles-deep-analysis.md`.

## Key Changes

### 1. **Removed Direct Module Permissions from Roles**
**Before:** Roles had direct module-level permissions stored in custom tables.
**After:** Roles use vtiger's profile-based permission model.

**Rationale:** In vtiger CRM, roles define organizational hierarchy and record assignment rules, while profiles handle module-level permissions.

### 2. **Implemented vtiger's Profile Assignment Model**

#### Two Methods of Profile Assignment:

##### A. Directly Related Profile (1:1 Relationship)
- A profile is created specifically for the role
- Stored in `vtiger_profile` with `directly_related_to_role = 1`
- Profile name auto-generated as "[Role Name] + Profile" if not specified
- Cannot be assigned to other roles
- Ideal for roles with unique permission requirements

##### B. Existing Profiles (Many-to-Many Relationship)
- Role inherits permissions from one or more existing profiles
- Profiles stored in `vtiger_profile` with `directly_related_to_role = 0`
- Can be shared across multiple roles
- Managed separately in Profiles module
- Ideal for standardized permission sets

### 3. **Database Structure**

#### vtiger_role Table
```sql
roleid VARCHAR(255) PRIMARY KEY
rolename VARCHAR(200)
parentrole VARCHAR(255)  -- Hierarchical path: H1::H2::H3
depth INT(11)            -- Hierarchy level
allowassignedrecordsto INT(2)  -- 1=All, 2=Same/Sub, 3=Sub only
```

#### vtiger_role2profile Table (Junction Table)
```sql
roleid VARCHAR(255)
profileid INT(11)
PRIMARY KEY (roleid, profileid)
```

#### vtiger_profile Table
```sql
profileid INT(11) PRIMARY KEY
profilename VARCHAR(200)
directly_related_to_role TINYINT(1)  -- 1=directly related, 0=shared
description TEXT
```

### 4. **Form Changes**

#### Create Role Form (`create.blade.php`)
**Removed:**
- Module permissions table with View/Create/Edit/Delete checkboxes
- "Copy privileges from profile" dropdown in direct privileges section
- Select-all checkboxes for modules and permissions

**Added:**
- Profile assignment method selection:
  - "Assign new privileges directly to this role"
  - "Assign privileges from existing profiles"
- Profile name input (optional) for directly related profiles
- Multi-select dropdown for existing profiles
- Informational alerts explaining the vtiger model

#### Edit Role Form (`edit.blade.php`)
**Removed:**
- Module permissions table
- Direct privilege editing

**Added:**
- Pre-populated profile assignment method
- Display of currently assigned profiles
- Ability to switch between directly related and existing profiles
- Profile name editing for directly related profiles

### 5. **Controller Changes**

#### RolesController::create()
**Removed:**
- Loading modules from `vtiger_tab`

**Kept:**
- Loading parent roles
- Loading profiles

#### RolesController::store()
**Changed:**
- Validation rules updated to match vtiger model
- Profile assignment logic:
  - Creates directly related profile if selected
  - Links existing profiles via `vtiger_role2profile`
- Copies picklist values from parent role (vtiger behavior)
- Uses `allowassignedrecordsto` instead of `assign_type`

#### RolesController::edit()
**Changed:**
- Loads role with profile relationships
- Extracts parent role name from `parentrole` path
- Identifies directly related profile if exists
- Loads all assigned profile IDs

**Removed:**
- Module permissions loading
- Complex privilege aggregation logic

#### RolesController::update()
**Changed:**
- Updates role basic info only (name, allowassignedrecordsto)
- Clears and recreates profile assignments
- Handles directly related profile creation/update
- Links to existing profiles

**Removed:**
- Direct privilege saving to custom tables
- Module permission handling

### 6. **Record Assignment Rules**

Controlled by `allowassignedrecordsto` field:
- **1 = All Users**: Can assign records to anyone
- **2 = Same or Subordinate**: Can assign to users in same role or below
- **3 = Subordinate Only**: Can only assign to users in subordinate roles

This affects the user dropdown when assigning records in modules.

### 7. **Hierarchical Role Structure**

Roles maintain hierarchy through:
- **parentrole**: Path string (e.g., "H1::H2::H3")
- **depth**: Numeric level (0 = root, 1 = first level, etc.)
- **roleid**: Format "H{number}" (e.g., H1, H2, H3)

When creating a role:
1. Generate new roleid (H + count + random)
2. Calculate depth = parent.depth + 1
3. Build parentrole = parent.parentrole + "::" + new roleid
4. Copy picklist values from parent

### 8. **Permission Management Workflow**

**New Workflow:**
1. Create/Edit Role → Choose profile assignment method
2. If "Directly Related":
   - Role created with auto-generated profile
   - Go to Settings → Profiles to configure module permissions
3. If "Existing Profiles":
   - Select one or more profiles
   - Role inherits all permissions from selected profiles

**Benefits:**
- Separation of concerns (roles = hierarchy, profiles = permissions)
- Reusable permission sets
- Easier to manage permissions across multiple roles
- Matches vtiger CRM's proven architecture

### 9. **Migration Path**

If you have existing roles with direct permissions:
1. Create profiles for each unique permission set
2. Update roles to reference these profiles
3. Migrate data from old permission tables to `vtiger_role2profile`

### 10. **Future Enhancements**

Based on vtiger's full implementation, future additions could include:
- Drag-and-drop role hierarchy reorganization
- Role deletion with user/child role transfer
- Field-level permissions (managed in Profiles)
- Tool permissions (managed in Profiles)
- Role-based picklist value restrictions
- Sharing rules based on role hierarchy

## Testing Checklist

- [ ] Create role with directly related profile
- [ ] Create role with existing profiles
- [ ] Edit role and switch from directly related to existing profiles
- [ ] Edit role and switch from existing profiles to directly related
- [ ] Update directly related profile name
- [ ] Assign multiple existing profiles to a role
- [ ] Verify parent role displays correctly in edit form
- [ ] Verify record assignment rules save correctly
- [ ] Check that picklist values are copied from parent role
- [ ] Verify role hierarchy (parentrole and depth) is correct

## Documentation References

- **vtiger Role Model**: `docs/roles-deep-analysis.md`
- **Database Schema**: See vtiger_role, vtiger_role2profile, vtiger_profile tables
- **Profile Management**: Settings → Users → Profiles (separate module)

## Notes

- Module-level permissions are now managed exclusively through Profiles
- Roles focus on organizational hierarchy and record assignment rules
- This implementation follows vtiger CRM's proven architecture
- Profile management UI should be implemented separately
