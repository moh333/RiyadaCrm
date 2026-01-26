# Module Relations - Quick Start Guide

## What are Module Relations?

Module Relations define how different modules in your CRM connect to each other. For example:
- **Contacts** can have **Opportunities** (sales deals)
- **Accounts** can have **Contacts** (people at the company)
- **Leads** can have **Activities** (calls, meetings, tasks)

## How to Access

1. Log in to your tenant dashboard
2. Go to **Settings** (gear icon)
3. Click **Module Management**
4. Select **Module Relations** tab

**Direct URL**: `/settings/modules/relations`

## Managing Relations

### Step 1: Select a Module

You'll see a grid of all your modules. Click on the module you want to configure relations for.

**Example**: Click on "Contacts" to manage what can be related to contacts.

### Step 2: View Existing Relations

You'll see a list of all current relations for that module, showing:
- **Label**: Display name (e.g., "Opportunities")
- **Target Module**: What module it connects to
- **Type**: 1:N (one-to-many) or N:N (many-to-many)
- **Actions**: ADD (create new) and/or SELECT (link existing)

### Step 3: Add a New Relation

Click the **"Add Relation"** button and fill in:

1. **Target Module**: Choose which module to relate to
2. **Label**: Give it a display name
3. **Type**: 
   - **1:N** - One record can have many (e.g., one Contact has many Opportunities)
   - **N:N** - Many-to-many (e.g., many Contacts in many Campaigns)
4. **Actions**:
   - ✓ **Allow Add** - Can create new related records
   - ✓ **Allow Select** - Can link existing records

Click **"Add Relation"** to save.

### Step 4: Edit a Relation

Click the **edit icon** (pencil) next to any relation to:
- Change the label
- Update which actions are allowed

Click **"Save Settings"** when done.

### Step 5: Delete a Relation

Click the **delete icon** (trash) next to any relation.

**Note**: This hides the relation but doesn't delete any data.

### Step 6: Reorder Relations

Drag relations up or down using the **grip handle** (≡) to change the display order.

Changes save automatically!

## Common Examples

### Example 1: Add Opportunities to Contacts

**Goal**: Let contacts have a list of sales opportunities

**Steps**:
1. Go to Contacts relations
2. Click "Add Relation"
3. Fill in:
   - Target Module: **Opportunities**
   - Label: **"Opportunities"**
   - Type: **1:N**
   - Actions: ✓ ADD, ✓ SELECT
4. Save

**Result**: Contacts now show an "Opportunities" related list where you can add or link opportunities.

### Example 2: Add Campaigns to Contacts

**Goal**: Track which marketing campaigns a contact is in

**Steps**:
1. Go to Contacts relations
2. Click "Add Relation"
3. Fill in:
   - Target Module: **Campaigns**
   - Label: **"Campaigns"**
   - Type: **N:N** (many contacts in many campaigns)
   - Actions: ✓ SELECT (usually just link, not create)
4. Save

**Result**: Contacts can be linked to multiple campaigns.

### Example 3: Add Activities to Accounts

**Goal**: Track calls, meetings, and tasks for each account

**Steps**:
1. Go to Accounts relations
2. Click "Add Relation"
3. Fill in:
   - Target Module: **Calendar** (Activities)
   - Label: **"Activities"**
   - Type: **1:N**
   - Actions: ✓ ADD, ✓ SELECT
4. Save

**Result**: Accounts show an activities list.

## Understanding Relation Types

### 1:N (One-to-Many)

**What it means**: One parent record can have many child records.

**Examples**:
- One Contact → Many Opportunities
- One Account → Many Contacts
- One Lead → Many Activities

**When to use**: Most common type. Use when one record "owns" or "has" multiple related records.

### N:N (Many-to-Many)

**What it means**: Many records can relate to many other records.

**Examples**:
- Many Contacts ↔ Many Campaigns
- Many Products ↔ Many Sales Orders
- Many Users ↔ Many Groups

**When to use**: When the relationship goes both ways and isn't exclusive.

## Understanding Actions

### ADD Action

**What it does**: Allows creating new related records directly

**Example**: 
- You're viewing a Contact
- Click "Add" in the Opportunities section
- Create a new Opportunity right there

**When to enable**: When users should be able to create new related records quickly.

### SELECT Action

**What it does**: Allows linking existing records

**Example**:
- You're viewing a Contact
- Click "Select" in the Campaigns section
- Choose from existing campaigns to link

**When to enable**: When you want to link existing records rather than create new ones.

### Both Actions

Most relations have both enabled for maximum flexibility:
- **ADD** - Quick creation
- **SELECT** - Link existing

## Tips & Best Practices

### 1. Use Clear Labels
✅ Good: "Sales Opportunities"  
❌ Bad: "Opps"

Make labels descriptive so users understand what they're looking at.

### 2. Choose the Right Type
- **1:N** for ownership (Contact has Opportunities)
- **N:N** for associations (Contact in Campaigns)

### 3. Enable Appropriate Actions
- **ADD** - When users create related records often
- **SELECT** - When linking existing records
- **Both** - For maximum flexibility

### 4. Order Matters
Put most-used relations at the top. Users see them first!

### 5. Don't Over-Relate
Only add relations that users actually need. Too many can be overwhelming.

## Troubleshooting

### Q: I added a relation but don't see it

**A**: Check that:
1. You saved the relation
2. The page refreshed
3. You're looking at the right module
4. The relation isn't hidden

### Q: Can I relate a module to itself?

**A**: Yes! For example, Contacts can relate to other Contacts (parent/child relationships).

### Q: What happens if I delete a relation?

**A**: The relation is hidden but no data is deleted. Related records remain intact.

### Q: Can I undo a deletion?

**A**: Currently no, but the data isn't deleted - contact your administrator to restore.

### Q: Why can't I change the target module?

**A**: By design. Delete the relation and create a new one instead.

## Next Steps

After setting up relations:

1. **Test them**: Create a record and verify the related list appears
2. **Train users**: Show your team how to use the new relations
3. **Monitor usage**: See which relations are most valuable
4. **Refine**: Add, remove, or reorder based on feedback

## Need Help?

- **Documentation**: See `docs/features/module-relations-implementation.md`
- **Support**: Contact your system administrator
- **Examples**: Check existing modules for relation ideas

---

**Remember**: Relations make your CRM more powerful by connecting related information. Start simple and add more as needed!
