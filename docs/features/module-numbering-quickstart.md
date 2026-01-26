# Module Numbering - Quick Start Guide

## What is Module Numbering?

Module Numbering automatically assigns unique sequential numbers to records in your CRM, just like vtiger CRM. Each record gets a number like `CON1`, `CON2`, `ACC1`, etc.

## How to Configure Module Numbering

### Step 1: Access Module Numbering Settings

1. Log in to your tenant dashboard
2. Navigate to **Settings** (gear icon in top navigation)
3. Click on **Module Management**
4. Select **Module Numbering** tab

**Route**: `/settings/modules/numbering`

### Step 2: Select a Module

You'll see a grid of all available modules that support numbering:
- Contacts
- Accounts  
- Leads
- Opportunities
- And more...

Click on any module card to configure its numbering.

### Step 3: Configure Numbering

On the configuration page, you'll see two main fields:

#### **Prefix** (Required)
- Short identifier for the module
- Recommended: 2-4 uppercase characters
- Examples:
  - Contacts: `CON`
  - Accounts: `ACC`
  - Leads: `LEA`
  - Opportunities: `OPP`

#### **Start Sequence** (Required)
- The first number in your sequence
- Default: `1`
- For new systems: Start at `1`
- For migrated data: Start after your highest existing number

#### **Live Preview**
As you type, you'll see a preview of how your numbers will look:
- Example: `CON1`, `CON2`, `CON3`...

### Step 4: Save Configuration

Click **Save Settings** to apply your configuration.

## Examples

### Example 1: New Contacts Module

**Configuration:**
- Prefix: `CON`
- Start Sequence: `1`

**Generated Numbers:**
- First contact: `CON1`
- Second contact: `CON2`
- Third contact: `CON3`

### Example 2: Migrated Accounts

If you're migrating from another system and already have 500 accounts:

**Configuration:**
- Prefix: `ACC`
- Start Sequence: `501`

**Generated Numbers:**
- First new account: `ACC501`
- Second new account: `ACC502`

### Example 3: Custom Prefix

**Configuration:**
- Prefix: `CUST`
- Start Sequence: `1000`

**Generated Numbers:**
- First record: `CUST1000`
- Second record: `CUST1001`

## Important Notes

### ✅ What Happens When You Save

- New records will use the configured prefix and sequence
- The system automatically increments the sequence for each new record
- Numbers are guaranteed to be unique (thread-safe)

### ⚠️ What DOESN'T Happen

- **Existing records are NOT renumbered**
- Changing the configuration only affects NEW records
- Old records keep their original numbers

### 🔒 Thread Safety

The system uses database locking to ensure:
- No duplicate numbers are ever generated
- Safe for concurrent users creating records simultaneously
- Transactional integrity

## Best Practices

### 1. Choose Meaningful Prefixes
- Use abbreviations that make sense
- Keep them short (2-4 characters)
- Use uppercase for consistency

### 2. Plan Your Sequences
- Start from 1 for new systems
- For migrations, start after existing data
- Leave room for growth

### 3. Be Consistent
- Use similar patterns across modules
- Document your numbering scheme
- Train users on the format

## Troubleshooting

### Q: I changed the prefix, but old records still have the old prefix
**A:** This is expected. Only NEW records use the updated configuration.

### Q: My numbers have gaps (CON1, CON3, CON5)
**A:** This is normal. If a record creation fails or is rolled back, the number is still consumed to ensure uniqueness.

### Q: Can I reset the sequence back to 1?
**A:** Yes, just update the "Start Sequence" field. But be careful not to create duplicates with existing records.

### Q: Can I use special characters in the prefix?
**A:** Yes, but we recommend sticking to letters and numbers for compatibility.

## Technical Details

### Database Tables

The system uses two tables:

1. **vtiger_modentity_num** - Stores configuration
2. **vtiger_modentity_num_seq** - Sequence generator

### How It Works

When you create a new record:

1. System checks `vtiger_modentity_num` for module configuration
2. Gets current sequence number
3. Increments sequence by 1
4. Generates number: `PREFIX` + `SEQUENCE`
5. Saves number with the record
6. Updates sequence for next record

### Compatibility

✅ **100% Compatible with vtiger CRM**
- Same database schema
- Same numbering logic
- Can migrate data between systems

## Next Steps

After configuring module numbering:

1. **Test It**: Create a new record and verify the number
2. **Configure Other Modules**: Set up numbering for all your modules
3. **Train Users**: Show your team the new numbering format
4. **Document**: Keep a record of your numbering scheme

## Need Help?

- **Documentation**: See `docs/features/module-numbering.md` for detailed technical information
- **Example Code**: Check `app/Modules/Tenant/Contacts/Infrastructure/EloquentContactRepository.php`
- **Support**: Contact your system administrator

---

**Remember**: Module numbering is automatic once configured. You don't need to manually enter numbers when creating records!
