# CRM Settings Menu Structure

## Sidebar Navigation Hierarchy

```
┌─────────────────────────────────────────┐
│         TenantHub CRM                   │
├─────────────────────────────────────────┤
│                                         │
│  📊 Dashboard                           │
│                                         │
│  ─── MODULES ───                        │
│  📁 Sales                               │
│     ├─ Leads                            │
│     ├─ Accounts                         │
│     ├─ Contacts                         │
│     └─ Opportunities                    │
│                                         │
│  📁 Marketing                           │
│     ├─ Campaigns                        │
│     └─ Email Templates                  │
│                                         │
│  ─── ADMINISTRATION ───                 │
│  🎛️ Module Management                   │
│     ├─ Menu Management                  │
│     ├─ Layouts & Fields                 │
│     ├─ Numbering                        │
│     └─ Relations                        │
│                                         │
│  👥 User Management                     │
│     ├─ Users                            │
│     ├─ Roles                            │
│     ├─ Profiles                         │
│     ├─ Sharing Rules                    │
│     ├─ Groups                           │
│     └─ Login History                    │
│                                         │
│  🎚️ CRM Settings          ⭐ NEW!       │
│     ├─ 📋 Picklist                      │
│     └─ 🔗 Picklist Dependency           │
│                                         │
│  ⚙️ Settings                            │
│                                         │
└─────────────────────────────────────────┘
```

## CRM Settings Submenu Details

### 📋 Picklist
**Route:** `/settings/crm/picklist`  
**Purpose:** Manage dropdown field values across all CRM modules

**Features:**
- ✅ Module selection
- ✅ Field selection
- ✅ Add/Edit/Delete values
- ✅ Color coding
- ✅ Value ordering

**User Flow:**
```
1. Select Module (e.g., Contacts)
   ↓
2. Select Field (e.g., Lead Source)
   ↓
3. View/Manage Values
   ├─ Add new value
   ├─ Edit existing value
   ├─ Delete value
   └─ Assign colors
```

### 🔗 Picklist Dependency
**Route:** `/settings/crm/picklist-dependency`  
**Purpose:** Create conditional relationships between picklist fields

**Features:**
- ✅ List all dependencies
- ✅ Create new dependency
- ✅ Interactive matrix editor
- ✅ Cyclic dependency prevention
- ✅ Delete dependencies

**User Flow:**
```
1. View Dependencies List
   ↓
2. Click "Add Dependency"
   ↓
3. Select Module, Source Field, Target Field
   ↓
4. Configure Dependency Matrix
   ├─ Click cells to toggle
   ├─ Select All / Clear All
   └─ Save mappings
```

## Page Layouts

### Picklist Management Page

```
┌────────────────────────────────────────────────────────────┐
│  Picklist Management                                       │
│  Manage dropdown field values across all CRM modules       │
├────────────────────────────────────────────────────────────┤
│                                                            │
│  Select Module: [Dropdown ▼]    Select Field: [Dropdown ▼]│
│                                                            │
│  ┌──────────────────────────────────────────────────────┐ │
│  │  Picklist Values                    [+ Add Value]    │ │
│  ├──────────────────────────────────────────────────────┤ │
│  │  Value          │  Color    │  Actions              │ │
│  ├──────────────────────────────────────────────────────┤ │
│  │  🟦 Advertisement│  #6366f1  │  [✏️ Edit] [🗑️ Delete]│ │
│  │  🟩 Cold Call    │  #22c55e  │  [✏️ Edit] [🗑️ Delete]│ │
│  │  🟨 Partner      │  #f59e0b  │  [✏️ Edit] [🗑️ Delete]│ │
│  └──────────────────────────────────────────────────────┘ │
└────────────────────────────────────────────────────────────┘
```

### Picklist Dependency List Page

```
┌────────────────────────────────────────────────────────────┐
│  Picklist Dependency                    [+ Add Dependency] │
│  Create conditional relationships between picklist fields  │
├────────────────────────────────────────────────────────────┤
│                                                            │
│  ┌──────────────────────────────────────────────────────┐ │
│  │  Module   │ Source Field │ Target Field │ Actions   │ │
│  ├──────────────────────────────────────────────────────┤ │
│  │  Contacts │ Lead Source  │ Industry     │ [Edit][Del]│ │
│  │  Leads    │ Status       │ Rating       │ [Edit][Del]│ │
│  └──────────────────────────────────────────────────────┘ │
└────────────────────────────────────────────────────────────┘
```

### Dependency Matrix Editor

```
┌────────────────────────────────────────────────────────────┐
│  Configure Dependency: Lead Source → Industry              │
│  ◄ Back                                                    │
├────────────────────────────────────────────────────────────┤
│  ℹ️ Click on cells to toggle selection                     │
│                                                            │
│  Lead Source \ Industry │ Banking │ Insurance │ Finance   │
│  ────────────────────────┼─────────┼───────────┼──────────│
│  Advertisement           │    ✅    │     ✅     │    ✅    │
│  Cold Call               │    ✅    │     ⭕     │    ✅    │
│  Partner                 │    ⭕    │     ✅     │    ⭕    │
│                                                            │
│  [Select All] [Clear All]                    [💾 Save]    │
└────────────────────────────────────────────────────────────┘
```

## Database Schema

### vtiger_picklist_dependency

```sql
CREATE TABLE vtiger_picklist_dependency (
    id INT PRIMARY KEY,
    tabid INT,                    -- Module ID
    sourcefield VARCHAR(255),     -- Source field name
    targetfield VARCHAR(255),     -- Target field name
    sourcevalue VARCHAR(100),     -- Source value
    targetvalues TEXT,            -- JSON array of allowed target values
    criteria TEXT                 -- Optional additional criteria
);
```

**Example Data:**
```json
{
    "id": 1,
    "tabid": 4,
    "sourcefield": "leadsource",
    "targetfield": "industry",
    "sourcevalue": "Advertisement",
    "targetvalues": "[\"Banking\",\"Insurance\",\"Finance\"]",
    "criteria": null
}
```

## API Endpoints

### Picklist Management

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/settings/crm/picklist` | Main page |
| POST | `/settings/crm/picklist/fields` | Get fields for module |
| POST | `/settings/crm/picklist/values` | Get values for field |
| POST | `/settings/crm/picklist/add` | Add new value |
| POST | `/settings/crm/picklist/update` | Update value |
| POST | `/settings/crm/picklist/delete` | Delete value |
| POST | `/settings/crm/picklist/order` | Update order |

### Picklist Dependency

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/settings/crm/picklist-dependency` | List dependencies |
| GET | `/settings/crm/picklist-dependency/create` | Create form |
| POST | `/settings/crm/picklist-dependency/fields` | Get fields |
| GET | `/settings/crm/picklist-dependency/edit` | Edit matrix |
| POST | `/settings/crm/picklist-dependency/store` | Save dependency |
| POST | `/settings/crm/picklist-dependency/delete` | Delete dependency |

## Language Support

### English (en)
- ✅ All labels translated
- ✅ All messages translated
- ✅ All buttons translated

### Arabic (ar)
- ✅ All labels translated (RTL support)
- ✅ All messages translated
- ✅ All buttons translated

## Access Control

**Current Implementation:**
- All authenticated tenant users can access

**Future Enhancement:**
- Add permission checks
- Role-based access control
- Audit logging

## Browser Compatibility

- ✅ Chrome/Edge (Latest)
- ✅ Firefox (Latest)
- ✅ Safari (Latest)
- ✅ Mobile browsers

## Responsive Design

- ✅ Desktop (1920px+)
- ✅ Laptop (1366px)
- ✅ Tablet (768px)
- ✅ Mobile (375px)
