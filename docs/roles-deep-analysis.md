# Roles Management System - Deep Analysis

## Table of Contents
1. [Overview](#overview)
2. [Database Architecture](#database-architecture)
3. [Core Models & Business Logic](#core-models--business-logic)
4. [Controllers & Actions](#controllers--actions)
5. [Views & Templates](#views--templates)
6. [JavaScript Functionality](#javascript-functionality)
7. [Role-Profile Relationship](#role-profile-relationship)
8. [Permissions & Security](#permissions--security)
9. [Data Flow](#data-flow)
10. [Key Features](#key-features)

---

## Overview

The Roles Management System in vtiger CRM is a hierarchical role-based access control (RBAC) system that manages organizational structure and permissions. Roles define the reporting hierarchy and control data visibility through role-based sharing rules.

**Location**: `modules/Settings/Roles/`

**Purpose**: 
- Define organizational hierarchy
- Control record assignment permissions
- Link users to profiles for permission management
- Enable role-based data sharing

---

## Database Architecture

### Primary Tables

#### 1. `vtiger_role`
The core table storing role information.

**Schema**:
```sql
CREATE TABLE vtiger_role (
    roleid VARCHAR(255) PRIMARY KEY,
    rolename VARCHAR(200),
    parentrole VARCHAR(255),
    depth INT(11),
    allowassignedrecordsto INT(2) DEFAULT 1
)
```

**Fields**:
- **`roleid`**: Unique identifier (format: `H{number}`, e.g., `H1`, `H2`)
- **`rolename`**: Display name of the role (e.g., "CEO", "Sales Manager")
- **`parentrole`**: Hierarchical path using `::` separator (e.g., `H1::H2::H3`)
- **`depth`**: Hierarchy level (0 = root, 1 = first level, etc.)
- **`allowassignedrecordsto`**: Controls who can be assigned records:
  - `1` = All users
  - `2` = Users with same or lower role level
  - `3` = Users with lower role level only

**Example Data**:
```
roleid  | rolename        | parentrole           | depth | allowassignedrecordsto
--------|-----------------|----------------------|-------|----------------------
H1      | Organisation    | H1                   | 0     | 1
H2      | CEO             | H1::H2               | 1     | 1
H3      | Vice President  | H1::H2::H3           | 2     | 2
H4      | Sales Manager   | H1::H2::H3::H4       | 3     | 2
H5      | Sales Person    | H1::H2::H3::H4::H5   | 4     | 3
```

#### 2. `vtiger_role2profile`
Links roles to profiles (many-to-many relationship).

**Schema**:
```sql
CREATE TABLE vtiger_role2profile (
    roleid VARCHAR(255),
    profileid INT(11),
    PRIMARY KEY (roleid, profileid)
)
```

**Purpose**: A role can have multiple profiles, and a profile can be assigned to multiple roles.

#### 3. `vtiger_user2role`
Maps users to their roles (one-to-one relationship).

**Schema**:
```sql
CREATE TABLE vtiger_user2role (
    userid INT(11),
    roleid VARCHAR(255),
    PRIMARY KEY (userid)
)
```

#### 4. `vtiger_role2picklist`
Manages role-based picklist values (for uitype 15 fields).

**Schema**:
```sql
CREATE TABLE vtiger_role2picklist (
    roleid VARCHAR(255),
    picklistvalueid INT(11),
    picklistid INT(11),
    sortid INT(11)
)
```

**Purpose**: Controls which picklist values are visible to specific roles.

---

## Core Models & Business Logic

### Settings_Roles_Record_Model
**Location**: `modules/Settings/Roles/models/Record.php`

This is the primary domain model for role management.

#### Key Properties
```php
class Settings_Roles_Record_Model extends Settings_Vtiger_Record_Model {
    protected $parent;          // Parent role instance
    protected $children;        // Array of child role instances
    protected $profiles;        // Associated profiles
}
```

#### Core Methods

##### 1. **Getters**
```php
public function getId()                    // Returns roleid
public function getName()                  // Returns rolename
public function getDepth()                 // Returns hierarchy depth
public function getParentRoleString()      // Returns parentrole path (e.g., "H1::H2::H3")
```

##### 2. **Hierarchy Management**
```php
public function getParent()                // Get immediate parent role
public function setParent($parentRole)     // Set parent role
public function getChildren()              // Get immediate child roles
public function getAllChildren()           // Get all descendant roles recursively
public function getSameLevelRoles()        // Get sibling roles at same depth
```

**Implementation Example - getChildren()**:
```php
public function getChildren() {
    $db = PearDatabase::getInstance();
    if(!$this->children) {
        $parentRoleString = $this->getParentRoleString();
        $currentRoleDepth = $this->getDepth();
        
        // Find roles where parentrole LIKE 'H1::H2::%' AND depth = currentDepth+1
        $sql = 'SELECT * FROM vtiger_role WHERE parentrole LIKE ? AND depth = ?';
        $params = array($parentRoleString.'::%', $currentRoleDepth+1);
        $result = $db->pquery($sql, $params);
        
        $roles = array();
        for ($i=0; $i<$db->num_rows($result); ++$i) {
            $role = self::getInstanceFromQResult($result, $i);
            $roles[$role->getId()] = $role;
        }
        $this->children = $roles;
    }
    return $this->children;
}
```

##### 3. **Profile Management**
```php
public function getProfiles()              // Get associated profiles
public function getProfileIdList()         // Get array of profile IDs
public function getDirectlyRelatedProfileId() // Get directly related profile (if exists)
```

**Directly Related Profile**: A special 1:1 relationship where a profile is created specifically for a role.

##### 4. **CRUD Operations**

**save()** - Creates or updates a role:
```php
public function save() {
    $db = PearDatabase::getInstance();
    $roleId = $this->getId();
    $mode = 'edit';
    
    if(empty($roleId)) {
        $mode = '';
        $roleIdNumber = $db->getUniqueId('vtiger_role');
        $roleId = 'H'.$roleIdNumber;  // Generate H{number} format
    }
    
    $parentRole = $this->getParent();
    if($parentRole != null) {
        $this->set('depth', $parentRole->getDepth()+1);
        $this->set('parentrole', $parentRole->getParentRoleString() .'::'. $roleId);
    }
    
    if($mode == 'edit') {
        $sql = 'UPDATE vtiger_role SET rolename=?, parentrole=?, depth=?, 
                allowassignedrecordsto=? WHERE roleid=?';
        $params = array($this->getName(), $this->getParentRoleString(), 
                       $this->getDepth(), $this->get('allowassignedrecordsto'), $roleId);
        $db->pquery($sql, $params);
    } else {
        $sql = 'INSERT INTO vtiger_role(roleid, rolename, parentrole, depth, 
                allowassignedrecordsto) VALUES (?,?,?,?,?)';
        $params = array($roleId, $this->getName(), $this->getParentRoleString(), 
                       $this->getDepth(), $this->get('allowassignedrecordsto'));
        $db->pquery($sql, $params);
        
        // Copy picklist values from parent role
        $picklist2RoleSQL = "INSERT INTO vtiger_role2picklist 
                            SELECT '".$roleId."',picklistvalueid,picklistid,sortid
                            FROM vtiger_role2picklist WHERE roleid = ?";
        $db->pquery($picklist2RoleSQL, array($parentRole->getId()));
    }
    
    // Save role-to-profile mappings
    $profileIds = $this->get('profileIds');
    if(!empty($profileIds)) {
        $db->pquery('DELETE FROM vtiger_role2profile WHERE roleid=?', array($roleId));
        $sql = 'INSERT INTO vtiger_role2profile(roleid, profileid) VALUES (?,?)';
        foreach($profileIds as $profileId) {
            $db->pquery($sql, array($roleId, $profileId));
        }
    }
}
```

**delete($transferToRole)** - Deletes role and transfers data:
```php
public function delete($transferToRole) {
    require_once('modules/Users/CreateUserPrivilegeFile.php');
    $db = PearDatabase::getInstance();
    $roleId = $this->getId();
    $transferRoleId = $transferToRole->getId();
    
    // Get users to recreate privilege files
    $usersResult = $db->pquery('SELECT userid FROM vtiger_user2role WHERE roleid = ?', 
                                array($roleId));
    $users = array();
    for($i=0; $i<$db->num_rows($usersResult); $i++) {
        $users[] = $db->query_result($usersResult, $i, 'userid');
    }
    
    // Transfer users to new role
    $db->pquery('UPDATE vtiger_user2role SET roleid=? WHERE roleid=?', 
                array($transferRoleId, $roleId));
    
    // Delete role associations
    $db->pquery('DELETE FROM vtiger_role2profile WHERE roleid=?', array($roleId));
    $db->pquery('DELETE FROM vtiger_group2role WHERE roleid=?', array($roleId));
    $db->pquery('DELETE FROM vtiger_group2rs WHERE roleandsubid=?', array($roleId));
    
    // Delete sharing rules
    deleteRoleRelatedSharingRules($roleId);
    
    // Delete the role
    $db->pquery('DELETE FROM vtiger_role WHERE roleid=?', array($roleId));
    
    // Move child roles to transfer role
    $allChildren = $this->getAllChildren();
    foreach($allChildren as $roleId => $roleModel) {
        // Update parent role string and depth
        $oldChildParentRoleString = $roleModel->getParentRoleString();
        $newChildParentRoleString = str_replace($currentParentRoleSequence, 
                                                $transferParentRoleSequence, 
                                                $oldChildParentRoleString);
        $newChildDepth = count(explode('::', $newChildParentRoleString))-1;
        $roleModel->set('depth', $newChildDepth);
        $roleModel->set('parentrole', $newChildParentRoleString);
        $roleModel->save();
    }
    
    // Recreate user privilege files
    foreach($users as $userId) {
        createUserPrivilegesfile($userId);
        createUserSharingPrivilegesfile($userId);
    }
}
```

**moveTo($newParentRole)** - Moves role to new parent:
```php
public function moveTo($newParentRole) {
    $currentDepth = $this->getDepth();
    $currentParentRoleString = $this->getParentRoleString();
    
    $newDepth = $newParentRole->getDepth() + 1;
    $newParentRoleString = $newParentRole->getParentRoleString() .'::'. $this->getId();
    
    $depthDifference = $newDepth - $currentDepth;
    $allChildren = $this->getAllChildren();
    
    // Update this role
    $this->set('depth', $newDepth);
    $this->set('parentrole', $newParentRoleString);
    $this->save();
    
    // Update all children recursively
    foreach($allChildren as $roleId => $roleModel) {
        $oldChildDepth = $roleModel->getDepth();
        $newChildDepth = $oldChildDepth + $depthDifference;
        
        $oldChildParentRoleString = $roleModel->getParentRoleString();
        $newChildParentRoleString = str_replace($currentParentRoleString, 
                                                $newParentRoleString, 
                                                $oldChildParentRoleString);
        
        $roleModel->set('depth', $newChildDepth);
        $roleModel->set('parentrole', $newChildParentRoleString);
        $roleModel->save();
    }
}
```

##### 5. **Static Factory Methods**
```php
public static function getAll($baseRole = false)      // Get all roles
public static function getInstanceById($roleId)       // Get role by ID (with caching)
public static function getBaseRole()                  // Get root role (depth=0)
public static function getInstanceByName($name)       // Get role by name
```

##### 6. **User Management**
```php
public function getUsers() {
    $db = PearDatabase::getInstance();
    $result = $db->pquery('SELECT userid FROM vtiger_user2role WHERE roleid = ?', 
                          array($this->getId()));
    $numOfRows = $db->num_rows($result);
    
    $usersList = array();
    for($i=0; $i<$numOfRows; $i++) {
        $userId = $db->query_result($result, $i, 'userid');
        $usersList[$userId] = Users_Record_Model::getInstanceById($userId, 'Users');
    }
    return $usersList;
}
```

---

## Controllers & Actions

### Views

#### 1. **Settings_Roles_Index_View**
**Location**: `modules/Settings/Roles/views/Index.php`

Displays the hierarchical role tree.

```php
public function process(Vtiger_Request $request) {
    $viewer = $this->getViewer($request);
    $moduleName = $request->getModule();
    $qualifiedModuleName = $request->getModule(false);
    
    $rootRole = Settings_Roles_Record_Model::getBaseRole();
    
    $viewer->assign('ROOT_ROLE', $rootRole);
    $viewer->assign('MODULE', $moduleName);
    $viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
    
    $viewer->view('Index.tpl', $qualifiedModuleName);
}
```

#### 2. **Settings_Roles_Edit_View**
**Location**: `modules/Settings/Roles/views/Edit.php`

Handles role creation and editing.

```php
public function process(Vtiger_Request $request) {
    $viewer = $this->getViewer($request);
    $record = $request->get('record');
    $parentRoleId = $request->get('parent_roleid');
    $roleDirectlyRelated = false;
    
    if(!empty($record)) {
        // Edit mode
        $recordModel = Settings_Roles_Record_Model::getInstanceById($record);
        $viewer->assign('MODE', 'edit');
    } else {
        // Create mode
        $recordModel = new Settings_Roles_Record_Model();
        $recordModel->setParent(Settings_Roles_Record_Model::getInstanceById($parentRoleId));
        $viewer->assign('MODE', '');
        $roleDirectlyRelated = true;
    }
    
    // Check if profile is directly related to role
    $profileId = $recordModel->getDirectlyRelatedProfileId();
    if($profileId){
        $viewer->assign('PROFILE_ID', $profileId);
        $roleDirectlyRelated = true;
    }
    
    $viewer->assign('PROFILE_DIRECTLY_RELATED_TO_ROLE', $roleDirectlyRelated);
    $viewer->assign('ALL_PROFILES', Settings_Profiles_Record_Model::getAll());
    $viewer->assign('RECORD_MODEL', $recordModel);
    $viewer->view('EditView.tpl', $qualifiedModuleName);
}
```

### Actions

#### 1. **Settings_Roles_Save_Action**
**Location**: `modules/Settings/Roles/actions/Save.php`

Handles role creation and updates.

```php
public function process(Vtiger_Request $request) {
    $recordId = $request->get('record');
    $roleName = $request->get('rolename');
    $allowassignedrecordsto = $request->get('allowassignedrecordsto');
    
    if(!empty($recordId)) {
        $recordModel = Settings_Roles_Record_Model::getInstanceById($recordId);
    } else {
        $recordModel = new Settings_Roles_Record_Model();
    }
    
    // Handle directly related profile
    if($request->get('profile_directly_related_to_role') == '1') {
        $profileId = $request->get('profile_directly_related_to_role_id');
        $profileName = $request->get('profilename');
        
        if(empty($profileName)){
            $profileName = $roleName.'+'.vtranslate('LBL_PROFILE', $qualifiedModuleName);
        }
        
        if($profileId){
            $profileRecordModel = Settings_Profiles_Record_Model::getInstanceById($profileId);
        } else {
            $profileRecordModel = Settings_Profiles_Record_Model::getInstanceByName($profileName, true);
            if(empty($profileRecordModel)) {
                $profileRecordModel = new Settings_Profiles_Record_Model();
            }
        }
        
        $profileRecordModel->set('directly_related_to_role','1');
        $profileRecordModel->set('profilename', $profileName)
                           ->set('profile_permissions', $request->get('permissions'));
        $profileRecordModel->set('viewall', $request->get('viewall'));
        $profileRecordModel->set('editall', $request->get('editall'));
        $savedProfileId = $profileRecordModel->save();
        $roleProfiles = array($savedProfileId);
    } else {
        $roleProfiles = $request->get('profiles');
    }
    
    $parentRoleId = $request->get('parent_roleid');
    if($recordModel && !empty($parentRoleId)) {
        $parentRole = Settings_Roles_Record_Model::getInstanceById($parentRoleId);
        if(!empty($allowassignedrecordsto)) {
            $recordModel->set('allowassignedrecordsto', $allowassignedrecordsto);
        }
        if($parentRole && !empty($roleName) && !empty($roleProfiles)) {
            $recordModel->set('rolename', $roleName);
            $recordModel->set('profileIds', $roleProfiles);
            $parentRole->addChildRole($recordModel);
        }
        
        // Recreate user privilege files
        if ($roleProfiles) {
            foreach ($roleProfiles as $profileId) {
                $profileRecordModel = Settings_Profiles_Record_Model::getInstanceById($profileId);
                $profileRecordModel->recalculate(array($recordId));
            }
        }
    }
    
    $redirectUrl = $moduleModel->getDefaultUrl();
    header("Location: $redirectUrl");
}
```

#### 2. **Settings_Roles_MoveAjax_Action**
**Location**: `modules/Settings/Roles/actions/MoveAjax.php`

Handles drag-and-drop role movement.

```php
public function process(Vtiger_Request $request) {
    $recordId = $request->get('record');
    $parentRoleId = $request->get('parent_roleid');
    
    $recordModel = Settings_Roles_Record_Model::getInstanceById($recordId);
    $newParentRole = Settings_Roles_Record_Model::getInstanceById($parentRoleId);
    
    $recordModel->moveTo($newParentRole);
    
    $response = new Vtiger_Response();
    $response->setResult(array('success' => true));
    $response->emit();
}
```

#### 3. **Settings_Roles_Delete_Action**
**Location**: `modules/Settings/Roles/actions/Delete.php`

Handles role deletion with transfer.

```php
public function process(Vtiger_Request $request) {
    $recordId = $request->get('record');
    $transferRecordId = $request->get('transfer_record');
    
    $recordModel = Settings_Roles_Record_Model::getInstanceById($recordId);
    $transferToRole = Settings_Roles_Record_Model::getInstanceById($transferRecordId);
    
    $recordModel->delete($transferToRole);
    
    $moduleModel = Settings_Vtiger_Module_Model::getInstance($qualifiedModuleName);
    $redirectUrl = $moduleModel->getDefaultUrl();
    header("Location: $redirectUrl");
}
```

---

## Views & Templates

### 1. **Index.tpl** - Role Tree View
**Location**: `layouts/vlayout/modules/Settings/Roles/Index.tpl`

Displays the hierarchical role structure.

```smarty
<div class="container-fluid">
    <div class="widget_header row-fluid">
        <div class="span8">
            <h3>{vtranslate($MODULE, $QUALIFIED_MODULE)}</h3>
        </div>
    </div>
    <hr>
    <div class="clearfix treeView">
        <ul>
            <li data-role="{$ROOT_ROLE->getParentRoleString()}" 
                data-roleid="{$ROOT_ROLE->getId()}">
                <div class="toolbar-handle">
                    <a href="javascript:;" class="btn btn-inverse draggable droppable">
                        {$ROOT_ROLE->getName()}
                    </a>
                    <div class="toolbar" title="{vtranslate('LBL_ADD_RECORD', $QUALIFIED_MODULE)}">
                        &nbsp;<a href="{$ROOT_ROLE->getCreateChildUrl()}" 
                                 data-url="{$ROOT_ROLE->getCreateChildUrl()}" 
                                 data-action="modal">
                            <span class="icon-plus-sign"></span>
                        </a>
                    </div>
                </div>
                {assign var="ROLE" value=$ROOT_ROLE}
                {include file=vtemplate_path("RoleTree.tpl", "Settings:Roles")}
            </li>
        </ul>
    </div>
</div>
```

### 2. **RoleTree.tpl** - Recursive Role Tree
**Location**: `layouts/vlayout/modules/Settings/Roles/RoleTree.tpl`

Recursively renders child roles.

```smarty
<ul>
{foreach from=$ROLE->getChildren() item=CHILD_ROLE}
    <li data-role="{$CHILD_ROLE->getParentRoleString()}" 
        data-roleid="{$CHILD_ROLE->getId()}">
        <div class="toolbar-handle">
            <a href="{$CHILD_ROLE->getEditViewUrl()}" 
               class="btn draggable droppable" 
               rel="tooltip" 
               title="{vtranslate('LBL_CLICK_TO_EDIT_OR_DRAG_TO_MOVE',$QUALIFIED_MODULE)}">
                {$CHILD_ROLE->getName()}
            </a>
            <div class="toolbar">
                &nbsp;<a href="{$CHILD_ROLE->getCreateChildUrl()}" 
                         title="{vtranslate('LBL_ADD_RECORD', $QUALIFIED_MODULE)}">
                    <span class="icon-plus-sign"></span>
                </a>
                &nbsp;<a data-id="{$CHILD_ROLE->getId()}" 
                         href="javascript:;" 
                         data-url="{$CHILD_ROLE->getDeleteActionUrl()}" 
                         data-action="modal" 
                         title="{vtranslate('LBL_DELETE', $QUALIFIED_MODULE)}">
                    <span class="icon-trash"></span>
                </a>
            </div>
        </div>
        
        {assign var="ROLE" value=$CHILD_ROLE}
        {include file=vtemplate_path("RoleTree.tpl", "Settings:Roles")}
    </li>
{/foreach}
</ul>
```

### 3. **EditView.tpl** - Role Edit Form
**Location**: `layouts/vlayout/modules/Settings/Roles/EditView.tpl`

Form for creating/editing roles.

**Key Sections**:

#### Role Name
```smarty
<div class="row-fluid">
    <label class="span3">
        <strong>{vtranslate('LBL_NAME', $QUALIFIED_MODULE)}
        <span class="redColor">*</span>: </strong>
    </label>
    <input type="text" class="fieldValue span7" name="rolename" 
           value="{$RECORD_MODEL->getName()}" 
           data-validation-engine='validate[required]' />
</div>
```

#### Reports To (Parent Role)
```smarty
<div class="row-fluid">
    <label class="span3">
        <strong>{vtranslate('LBL_REPORTS_TO', $QUALIFIED_MODULE)}: </strong>
    </label>
    <div class="span8 fieldValue">
        <input type="hidden" name="parent_roleid" 
               {if $HAS_PARENT}value="{$RECORD_MODEL->getParent()->getId()}"{/if}>
        <input type="text" class="input-large" name="parent_roleid_display" 
               {if $HAS_PARENT}value="{$RECORD_MODEL->getParent()->getName()}"{/if} 
               readonly>
    </div>
</div>
```

#### Can Assign Records To
```smarty
<div class="row-fluid">
    <label class="fieldLabel span3">
        <strong>{vtranslate('LBL_CAN_ASSIGN_RECORDS_TO', $QUALIFIED_MODULE)}: </strong>
    </label>
    <div class="row-fluid span9 fieldValue">
        <div class="span">
            <input type="radio" value="1" 
                   {if !$RECORD_MODEL->get('allowassignedrecordsto')} checked=""{/if} 
                   {if $RECORD_MODEL->get('allowassignedrecordsto') eq '1'} checked="" {/if} 
                   name="allowassignedrecordsto" class="alignTop"/>
            &nbsp;<span>{vtranslate('LBL_ALL_USERS',$QUALIFIED_MODULE)}</span>
        </div>
        <div class="span">
            <input type="radio" value="2" 
                   {if $RECORD_MODEL->get('allowassignedrecordsto') eq '2'} checked="" {/if} 
                   name="allowassignedrecordsto" class="alignTop"/>
            &nbsp;<span>{vtranslate('LBL_USERS_WITH_SAME_OR_LOWER_LEVEL',$QUALIFIED_MODULE)}</span>
        </div>
        <div class="span">
            <input type="radio" value="3" 
                   {if $RECORD_MODEL->get('allowassignedrecordsto') eq '3'} checked="" {/if} 
                   name="allowassignedrecordsto" class="alignTop"/>
            &nbsp;<span>{vtranslate('LBL_USERS_WITH_LOWER_LEVEL',$QUALIFIED_MODULE)}</span>
        </div>
    </div>
</div>
```

#### Privileges Assignment
```smarty
<div class="row-fluid">
    <label class="span3">
        <strong>{vtranslate('LBL_PRIVILEGES',$QUALIFIED_MODULE)}:</strong>
    </label>
    <div class="row-fluid span8 fieldValue">
        <div class="span">
            <input type="radio" value="1" 
                   {if $PROFILE_DIRECTLY_RELATED_TO_ROLE} checked="" {/if} 
                   name="profile_directly_related_to_role" 
                   data-handler="new" class="alignTop"/>
            &nbsp;<span>{vtranslate('LBL_ASSIGN_NEW_PRIVILEGES',$QUALIFIED_MODULE)}</span>
        </div>
        <div class="span">
            <input type="radio" value="0" 
                   {if $PROFILE_DIRECTLY_RELATED_TO_ROLE eq false} checked="" {/if} 
                   name="profile_directly_related_to_role" 
                   data-handler="existing" class="alignTop"/>
            &nbsp;<span>{vtranslate('LBL_ASSIGN_EXISTING_PRIVILEGES',$QUALIFIED_MODULE)}</span>
        </div>
    </div>
</div>

<!-- New Privileges Container (loaded via AJAX) -->
<div class="row-fluid hide padding20px boxSizingBorderBox contentsBackground" 
     data-content="new">
    <div class="fieldValue span12"></div>
</div>

<!-- Existing Privileges Container -->
<div class="row-fluid hide" data-content="existing">
    <div class="fieldValue row-fluid">
        {assign var="ROLE_PROFILES" value=$RECORD_MODEL->getProfiles()}
        <select class="select2" multiple="true" id="profilesList" 
                name="profiles[]" 
                data-placeholder="{vtranslate('LBL_CHOOSE_PROFILES',$QUALIFIED_MODULE)}" 
                style="width: 800px">
            {foreach from=$ALL_PROFILES item=PROFILE}
                {if $PROFILE->isDirectlyRelated() eq false}
                    <option value="{$PROFILE->getId()}" 
                            {if isset($ROLE_PROFILES[$PROFILE->getId()])}selected="true"{/if}>
                        {$PROFILE->getName()}
                    </option>
                {/if}
            {/foreach}
        </select>
    </div>
</div>
```

---

## JavaScript Functionality

**Location**: `layouts/vlayout/modules/Settings/Roles/resources/Roles.js`

### Key Features

#### 1. **Drag-and-Drop Role Movement**

```javascript
jQuery('.draggable').draggable({
    containment: '.treeView',
    start : function(event, ui) {
        var container = jQuery(ui.helper);
        var referenceid = container.data('refid');
        var sourceGroup = jQuery('[data-grouprefid="'+referenceid+'"]');
        var sourceRoleId = sourceGroup.data('roleid');
        
        // Prevent moving CEO and Sales Person roles
        if(sourceRoleId == 'H5' || sourceRoleId == 'H2') {
            var params = {};
            params.title = app.vtranslate('JS_PERMISSION_DENIED');
            params.text = app.vtranslate('JS_NO_PERMISSIONS_TO_MOVE');
            params.type = 'error';
            Settings_Vtiger_Index_Js.showMessage(params);
        }
    },
    helper: function(event) {
        var target = $(event.currentTarget);
        var targetGroup = target.closest('li');
        var timestamp = +(new Date());
        
        var container = $('<div/>');
        container.data('refid', timestamp);
        container.html(targetGroup.clone());
        
        targetGroup.attr('data-grouprefid', timestamp);
        return container;
    }
});

jQuery('.droppable').droppable({
    hoverClass: 'btn-primary',
    tolerance: 'pointer',
    drop: function(event, ui) {
        var container = $(ui.helper);
        var referenceid = container.data('refid');
        var sourceGroup = $('[data-grouprefid="'+referenceid+'"]');
        
        var thisWrapper = $(this).closest('div');
        var targetRole  = thisWrapper.closest('li').data('role');
        var targetRoleId= thisWrapper.closest('li').data('roleid');
        var sourceRole   = sourceGroup.data('role');
        var sourceRoleId = sourceGroup.data('roleid');
        
        // Prevent parent-into-child movement
        if (targetRole.indexOf(sourceRole) == 0) {
            return;
        }
        
        // Prevent moving CEO and Sales Person
        if (sourceRoleId == 'H5' || sourceRoleId == 'H2') {
            return;
        }
        
        sourceGroup.appendTo(thisWrapper.next('ul'));
        applyMoveChanges(sourceRoleId, targetRoleId);
    }
});

function applyMoveChanges(roleid, parent_roleid) {
    var params = {
        module: 'Roles',
        action: 'MoveAjax',
        parent: 'Settings',
        record: roleid,
        parent_roleid: parent_roleid
    }
    
    AppConnector.request(params).then(function(res) {
        if (!res.success) {
            alert(app.vtranslate('JS_FAILED_TO_SAVE'));
            window.location.reload();
        }
    });
}
```

#### 2. **Profile Privileges Loading**

```javascript
getProfilePriviliges : function() {
    var content = jQuery('[data-content="new"]');
    var profileId = jQuery('[name="profile_directly_related_to_role_id"]').val();
    
    var params = {
        module : 'Profiles',
        parent: 'Settings',
        view : 'EditAjax',
        record : profileId
    }
    
    if(Settings_Roles_Js.newPriviliges == true) {
        jQuery('[data-content="existing"]').fadeOut('slow',function(){
            content.fadeIn('slow');
        });
        return false;
    }
    
    var progressIndicatorElement = jQuery.progressIndicator({
        'position' : 'html',
        'blockInfo' : { 'enabled' : true }
    });
    
    AppConnector.request(params).then(function(data) {
        content.find('.fieldValue').html(data);
        app.changeSelectElementView(jQuery('#directProfilePriviligesSelect'), 'select2');
        Settings_Roles_Js.registerExistingProfilesChangeEvent();
        progressIndicatorElement.progressIndicator({ 'mode' : 'hide' });
        Settings_Roles_Js.newPriviliges = true;
        jQuery('[data-content="existing"]').fadeOut('slow',function(){
            content.fadeIn('slow');
        });
    })
}
```

#### 3. **Duplicate Name Validation**

```javascript
checkDuplicateName : function(details) {
    var aDeferred = jQuery.Deferred();
    
    var params = {
        'module' : app.getModuleName(),
        'parent' : app.getParentModuleName(),
        'action' : 'EditAjax',
        'mode'   : 'checkDuplicate',
        'rolename' : details.rolename,
        'record' : details.record
    }
    
    AppConnector.request(params).then(
        function(data) {
            var response = data['result'];
            var result = response['success'];
            if(result == true) {
                aDeferred.reject(response);
            } else {
                aDeferred.resolve(response);
            }
        },
        function(error,err){
            aDeferred.reject();
        }
    );
    return aDeferred.promise();
}
```

#### 4. **Form Submission with Validation**

```javascript
registerSubmitEvent : function() {
    var thisInstance = this;
    var form = jQuery('#EditView');
    
    form.on('submit',function(e) {
        if(form.data('submit') == 'true' && form.data('performCheck') == 'true') {
            return true;
        } else {
            // Validate existing profiles selection
            if(jQuery('[data-handler="existing"]').is(':checked')){
                var selectElement = jQuery('#profilesList');
                var select2Element = app.getSelect2ElementFromSelect(selectElement);
                var result = Vtiger_MultiSelect_Validator_Js.invokeValidation(selectElement);
                if(result != true){
                    select2Element.validationEngine('showPrompt', result , 'error','bottomLeft',true);
                    e.preventDefault();
                    return;
                } else {
                    select2Element.validationEngine('hide');
                }
            } 
            
            if(form.data('jqv').InvalidFields.length <= 0) {
                var formData = form.serializeFormData();
                thisInstance.checkDuplicateName({
                    'rolename' : formData.rolename,
                    'record' : formData.record
                }).then(
                    function(data){
                        form.data('submit', 'true');
                        form.data('performCheck', 'true');
                        form.submit();
                        jQuery.progressIndicator({
                            'blockInfo' : { 'enabled' : true }
                        });
                    },
                    function(data, err){
                        var params = {};
                        params['text'] = data['message'];
                        params['type'] = 'error';
                        Settings_Vtiger_Index_Js.showMessage(params);
                        return false;
                    }
                );
            } else {
                form.removeData('submit');
                app.formAlignmentAfterValidation(form);
            }
            e.preventDefault();
        }
    });
}
```

---

## Role-Profile Relationship

### Two Types of Relationships

#### 1. **Directly Related Profile** (1:1)
- Profile created specifically for a role
- `vtiger_profile.directly_related_to_role = 1`
- Permissions managed inline when creating/editing role
- Cannot be assigned to other roles

#### 2. **Shared Profiles** (Many-to-Many)
- Profile can be assigned to multiple roles
- `vtiger_profile.directly_related_to_role = 0`
- Managed separately in Profiles module
- Selected from dropdown when creating role

### Database Relationship

```
vtiger_role (1) ----< (M) vtiger_role2profile (M) >---- (1) vtiger_profile
    roleid                    roleid, profileid                 profileid
```

### Profile Assignment Logic

```php
// In Settings_Roles_Save_Action
if($request->get('profile_directly_related_to_role') == '1') {
    // Create/update directly related profile
    $profileRecordModel->set('directly_related_to_role','1');
    $profileRecordModel->set('profilename', $profileName);
    $profileRecordModel->set('profile_permissions', $request->get('permissions'));
    $savedProfileId = $profileRecordModel->save();
    $roleProfiles = array($savedProfileId);
} else {
    // Use existing profiles
    $roleProfiles = $request->get('profiles');
}

$recordModel->set('profileIds', $roleProfiles);
```

---

## Permissions & Security

### 1. **Record Assignment Control**

The `allowassignedrecordsto` field controls who can be assigned records:

```php
// In Users_Record_Model::getAccessibleUsers()
$currentUserRoleModel = Settings_Roles_Record_Model::getInstanceById($currentUserRole);

if($currentUserRoleModel->get('allowassignedrecordsto') === '1' || $private == 'Public') {
    // All users accessible
    $accessibleUser = self::getAll();
} else if($currentUserRoleModel->get('allowassignedrecordsto') === '2'){
    // Same or lower level users
    $accessibleUser = self::getUsersInSameOrSubordinateRoles($currentUserRole);
} else if($currentUserRoleModel->get('allowassignedrecordsto') === '3') {
    // Only lower level users
    $accessibleUser = self::getUsersInSubordinateRoles($currentUserRole);
}
```

### 2. **Data Visibility (Sharing Rules)**

Roles are used in sharing rules to control data visibility:

```php
// Role hierarchy determines default sharing
// Users can see records owned by:
// 1. Themselves
// 2. Users in subordinate roles (based on parentrole hierarchy)
// 3. Users in same role (if sharing rules allow)
```

### 3. **Picklist Value Restrictions**

Role-based picklists (uitype 15) restrict values by role:

```php
// When creating new role, copy parent's picklist values
$picklist2RoleSQL = "INSERT INTO vtiger_role2picklist 
                    SELECT '".$roleId."',picklistvalueid,picklistid,sortid
                    FROM vtiger_role2picklist WHERE roleid = ?";
$db->pquery($picklist2RoleSQL, array($parentRole->getId()));
```

### 4. **User Privilege Files**

When roles change, user privilege files must be regenerated:

```php
require_once('modules/Users/CreateUserPrivilegeFile.php');

foreach($users as $userId) {
    createUserPrivilegesfile($userId);
    createUserSharingPrivilegesfile($userId);
}
```

---

## Data Flow

### Creating a New Role

```
User Action: Click "Add Role" under parent role
    ↓
Settings_Roles_Edit_View::process()
    ↓
Display EditView.tpl with:
    - Parent role pre-filled
    - Empty role name
    - Default allowassignedrecordsto = 1
    - Profile selection options
    ↓
User fills form and submits
    ↓
Settings_Roles_Save_Action::process()
    ↓
1. Create/update profile (if directly related)
2. Create role record with generated roleid (H{number})
3. Calculate depth and parentrole string
4. Insert into vtiger_role
5. Copy picklist values from parent
6. Create role2profile mappings
7. Regenerate user privilege files
    ↓
Redirect to Index view
```

### Moving a Role (Drag & Drop)

```
User Action: Drag role to new parent
    ↓
JavaScript: Validate move (prevent circular, CEO/Sales Person)
    ↓
AJAX: Settings_Roles_MoveAjax_Action
    ↓
Settings_Roles_Record_Model::moveTo($newParentRole)
    ↓
1. Calculate new depth and parentrole string
2. Update current role
3. Recursively update all children:
   - Adjust depth by difference
   - Replace parentrole string prefix
4. Save all changes
    ↓
Return success response
    ↓
UI updates to show new hierarchy
```

### Deleting a Role

```
User Action: Click delete icon
    ↓
Display DeleteAjax view (modal)
    ↓
User selects transfer role
    ↓
Settings_Roles_Delete_Action::process()
    ↓
Settings_Roles_Record_Model::delete($transferToRole)
    ↓
1. Get all users in role
2. Transfer users to new role
3. Delete role2profile mappings
4. Delete group associations
5. Delete sharing rules
6. Delete role record
7. Move child roles to transfer role parent
8. Regenerate user privilege files
    ↓
Redirect to Index view
```

---

## Key Features

### 1. **Hierarchical Structure**
- Tree-based organization
- Unlimited depth
- Parent-child relationships
- Sibling roles at same level

### 2. **Visual Role Tree**
- Drag-and-drop reorganization
- Inline add/edit/delete
- Hover toolbars
- Recursive rendering

### 3. **Flexible Profile Assignment**
- Directly related profiles (1:1)
- Shared profiles (M:M)
- Multiple profiles per role
- AJAX profile loading

### 4. **Record Assignment Control**
- All users
- Same or lower level
- Lower level only
- Affects assignment dropdowns

### 5. **Role-Based Picklists**
- Inherit from parent
- Customize per role
- Control visible values
- Support uitype 15 fields

### 6. **Data Sharing Integration**
- Default sharing based on hierarchy
- Custom sharing rules
- Role-based visibility
- Subordinate access

### 7. **User Management**
- One role per user
- Bulk privilege regeneration
- Transfer on role deletion
- Automatic file updates

### 8. **Validation & Security**
- Duplicate name check
- Circular hierarchy prevention
- Protected system roles (CEO, Sales Person)
- Admin-only access

---

## Summary

The Roles Management System is a sophisticated hierarchical RBAC implementation that:

1. **Organizes** users into a tree-based reporting structure
2. **Controls** record assignment permissions at role level
3. **Integrates** with profiles for granular permission management
4. **Enables** role-based data sharing and visibility
5. **Supports** role-based picklist value restrictions
6. **Provides** intuitive drag-and-drop interface
7. **Maintains** data integrity through validation and constraints
8. **Regenerates** user privileges automatically on changes

The system uses a clever `parentrole` string format (`H1::H2::H3`) to efficiently query hierarchies and a `depth` field for level-based operations. The integration with profiles creates a powerful two-tier permission system where roles define organizational structure and data access, while profiles define functional permissions.
