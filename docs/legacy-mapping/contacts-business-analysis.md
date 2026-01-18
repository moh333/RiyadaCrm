# vtiger Contacts Module: Business Behavior Analysis

**Document Purpose:** Single source of truth for rebuilding the Contacts module in Laravel  
**Analysis Date:** 2026-01-18  
**Legacy System:** vtiger CRM v7  
**Target System:** Laravel Clean Architecture

---

## 1. Core Business Responsibilities

### 1.1 Primary Purpose
A **Contact** represents an individual person within the CRM system. Contacts are the human touchpoints for business relationships and can exist independently or be associated with an Account (organization).

### 1.2 Core Responsibilities
1. **Person Management**: Store and manage individual contact information (names, titles, contact details)
2. **Relationship Hub**: Act as a central link between people and business activities (Opportunities, Tickets, Orders, etc.)
3. **Communication Center**: Manage email addresses, phone numbers, and communication preferences
4. **Portal Access**: Enable customer portal login for self-service
5. **Address Management**: Maintain both mailing and alternate addresses
6. **Account Association**: Link contacts to parent organizations (Accounts)
7. **Activity Tracking**: Connect to all customer-facing activities (calls, meetings, emails)

---

## 2. Database Schema Structure

### 2.1 Core Tables (EAV Pattern)

#### `vtiger_crmentity` (Shared Entity Table)
**Purpose:** Every Contact MUST have a record here (standard vtiger pattern)

**Critical Fields:**
- `crmid` - Primary key (contactid references this)
- `setype` - Must be 'Contacts'
- `deleted` - Soft delete flag (0 = active, 1 = deleted)
- `smownerid` - Assigned user/group ID
- `smcreatorid` - Creator user ID
- `createdtime` - Creation timestamp
- `modifiedtime` - Last modification timestamp
- `modifiedby` - Last modifier user ID
- `label` - Display name (typically "FirstName LastName")

#### `vtiger_contactdetails` (Main Contact Table)
**Purpose:** Primary contact information

**Fields:**
- `contactid` (PK, FK to vtiger_crmentity.crmid)
- `contact_no` (Unique auto-generated number, NOT NULL)
- `accountid` (FK to vtiger_account - optional parent organization)
- `salutation` (Mr., Mrs., Dr., etc.)
- `firstname` (Optional)
- `lastname` (NOT NULL - **MANDATORY FIELD**)
- `email` (Primary email address)
- `phone` (Office phone)
- `mobile` (Mobile phone)
- `title` (Job title)
- `department` (Department name)
- `fax` (Fax number)
- `reportsto` (Reports to - appears to be text field, not FK)
- `training` (Training information)
- `usertype` (User type classification)
- `contacttype` (Contact type classification)
- `otheremail` (Secondary email)
- `secondaryemail` (Third email address)
- `donotcall` (Do not call flag)
- `emailoptout` (Email opt-out flag, default '0')
- `imagename` (Contact image filename)
- `reference` (Reference flag)
- `notify_owner` (Notify owner flag, default '0')

#### `vtiger_contactsubdetails` (Extended Contact Information)
**Purpose:** Additional contact details

**Fields:**
- `contactsubscriptionid` (PK, FK to contactdetails.contactid)
- `homephone` (Home phone number)
- `otherphone` (Other phone number)
- `assistant` (Assistant name)
- `assistantphone` (Assistant phone)
- `birthday` (Date of birth)
- `laststayintouchrequest` (Last stay-in-touch request timestamp)
- `laststayintouchsavedate` (Last stay-in-touch save date)
- `leadsource` (Lead source - inherited from Lead conversion)

#### `vtiger_contactaddress` (Address Information)
**Purpose:** Mailing and alternate addresses

**Fields:**
- `contactaddressid` (PK, FK to contactdetails.contactid)

**Mailing Address:**
- `mailingcity`
- `mailingstreet`
- `mailingcountry`
- `mailingstate`
- `mailingpobox`
- `mailingzip`

**Alternate Address:**
- `othercity`
- `otherstreet`
- `othercountry`
- `otherstate`
- `otherpobox`
- `otherzip`

#### `vtiger_contactscf` (Custom Fields)
**Purpose:** EAV table for custom fields

**Fields:**
- `contactid` (PK, FK to contactdetails.contactid)
- Additional columns added dynamically per custom field configuration

#### `vtiger_portalinfo` (Customer Portal Access)
**Purpose:** Portal login credentials for customer self-service

**Fields:**
- `id` (PK, FK to contactdetails.contactid)
- `user_name` (Portal username - typically email)
- `user_password` (Encrypted password)
- `type` (Type - 'C' for Contact)
- `cryptmode` (Encryption mode - 'CRYPT')
- `last_login_time` (Last login timestamp)
- `login_time` (Current login timestamp)
- `logout_time` (Logout timestamp)
- `isactive` (Active status - 1 = active, 0 = inactive)

#### `vtiger_customerdetails` (Customer Support Information)
**Purpose:** Support contract details

**Fields:**
- `customerid` (PK, FK to contactdetails.contactid)
- `portal` (Portal enabled flag - 'on'/'1' = enabled)
- `support_start_date` (Support start date)
- `support_end_date` (Support end date)

---

## 3. Business Rules & Validations

### 3.1 Mandatory Fields
**CRITICAL:** These fields MUST be present:
1. `lastname` - Cannot be empty
2. `assigned_user_id` (smownerid) - Must have an owner
3. `createdtime` - Auto-set on creation
4. `modifiedtime` - Auto-updated on modification

### 3.2 Unique Constraints
1. `contact_no` - Auto-generated unique identifier (format appears to be "CON" + sequence)
2. Must not have duplicate combinations that violate business logic (system doesn't enforce email uniqueness)

### 3.3 Field-Level Business Rules

#### Salutation Handling
- **Rule:** If salutation is set to '--None--', it must be converted to empty string
- **Location:** `Contacts_Save_Action::process()`
- **Reason:** Prevents storing placeholder values in database

#### Email Validation
- **Rule:** Email addresses are used for portal login
- **Rule:** `emailoptout` flag controls whether automated emails are sent
- **Default:** `emailoptout = 0` (emails allowed)

#### Account Relationship
- **Rule:** Contact can exist WITHOUT an Account (accountid can be NULL)
- **Rule:** Contact can be linked to exactly ONE Account at a time
- **Business Logic:** Contacts are individuals; Accounts are organizations

#### Portal Access Rules
- **Rule:** Portal can only be enabled if contact has a valid email address
- **Rule:** Portal username = contact's email address
- **Rule:** When portal is enabled, system auto-generates password and sends email
- **Rule:** When email changes and portal is active, new credentials are generated
- **Rule:** When portal is disabled, `isactive` set to 0 (record preserved)
- **Rule:** Portal emails are NOT sent if `emailoptout = 1`

### 3.4 Image Attachment Rules
- **Rule:** Contact can have ONE image attachment
- **Rule:** When new image is uploaded, old image is deleted (if setype = 'Contacts Image')
- **Rule:** Image name is stored in `imagename` field
- **Rule:** Actual image stored in `vtiger_attachments` + `vtiger_seattachmentsrel`

---

## 4. Lifecycle Workflows

### 4.1 Contact Creation

**Steps:**
1. Generate unique `contact_no` from sequence
2. Create record in `vtiger_crmentity` with:
   - `setype = 'Contacts'`
   - `deleted = 0`
   - `smownerid` = assigned user
   - `smcreatorid` = current user
   - `createdtime` = current timestamp
   - `modifiedtime` = current timestamp
   - `label` = "FirstName LastName" (or just LastName if no FirstName)

3. Insert into `vtiger_contactdetails` with mandatory `lastname`
4. Insert into `vtiger_contactsubdetails` (contactsubscriptionid = contactid)
5. Insert into `vtiger_contactaddress` (contactaddressid = contactid)
6. Insert into `vtiger_contactscf` (contactid = contactid)
7. Insert into `vtiger_customerdetails` (customerid = contactid)
8. If image uploaded, process attachment
9. If portal enabled, create portal user and send credentials

**Database Side-Effects:**
- Minimum 6 table inserts per contact creation
- Potential email send if portal enabled
- Attachment processing if image provided

### 4.2 Contact Update

**Steps:**
1. Update `vtiger_crmentity.modifiedtime` and `modifiedby`
2. Update `vtiger_crmentity.label` if name changed
3. Update relevant fields in `vtiger_contactdetails`
4. Update `vtiger_contactsubdetails` if extended fields changed
5. Update `vtiger_contactaddress` if address fields changed
6. Update `vtiger_contactscf` if custom fields changed
7. Update `vtiger_customerdetails` if support fields changed
8. Handle image replacement if new image uploaded
9. Handle portal credential changes if email changed or portal toggled

**Portal Update Logic:**
- If `portal` changed from OFF to ON: Create portal user, send credentials
- If `portal` is ON and `email` changed: Update username, regenerate password, send new credentials
- If `portal` changed from ON to OFF: Set `isactive = 0` in vtiger_portalinfo

**Database Side-Effects:**
- Updates across 2-7 tables depending on fields changed
- Potential email send for portal changes
- Potential attachment deletion/creation

### 4.3 Contact Deletion (Soft Delete)

**Steps:**
1. Set `vtiger_crmentity.deleted = 1`
2. Handle relationship cleanup via `unlinkDependencies()`

**Relationship Cleanup Logic:**
- **Potentials (Opportunities):** If Contact is `related_to` for Potential, soft-delete the Potential
- **Tickets:** Set `contact_id = 0` (unlink, don't delete)
- **Purchase Orders:** Set `contactid = 0` (unlink, don't delete)
- **Sales Orders:** Set `contactid = 0` (unlink, don't delete)
- **Quotes:** Set `contactid = 0` (unlink, don't delete)
- **Portal Info:** Delete from `vtiger_portalinfo`
- **Customer Details:** Set `portal = 0`, clear support dates

**Backup for Recovery:**
- All relationship changes are logged to `vtiger_relatedlists_rb` for potential restore

**Database Side-Effects:**
- 1 update to crmentity
- Potential soft-delete of related Potentials
- Unlinking from Tickets, Orders, Quotes
- Deletion of portal access
- Multiple inserts to relatedlists_rb for recovery

### 4.4 Lead Conversion to Contact

**Business Context:**
When a Lead is converted, it can create:
1. A new Contact (the person)
2. A new Account (the organization) - optional
3. A new Potential (the sales opportunity) - optional

**Contact Creation from Lead:**
- Lead's personal information maps to Contact
- `leadsource` field is preserved in `vtiger_contactsubdetails`
- Contact is automatically linked to created Account (if Account created)
- Contact is automatically linked to created Potential (if Potential created)

**Field Mapping (Lead → Contact):**
- `firstname` → `firstname`
- `lastname` → `lastname`
- `salutation` → `salutation`
- `email` → `email`
- `phone` → `phone` (from leadaddress)
- `mobile` → `mobile` (from leadaddress)
- `leadsource` → `leadsource` (in contactsubdetails)
- Address fields map to mailing address

---

## 5. Relationship Side-Effects

### 5.1 Direct Relationships (Foreign Keys)

#### Account Relationship
- **Table:** `vtiger_contactdetails.accountid`
- **Type:** Many-to-One (many Contacts to one Account)
- **Cascade:** When Contact deleted, accountid remains (no cascade)
- **Unlink:** When unlinking from Account, set `accountid = NULL`

### 5.2 Many-to-Many Relationships (Junction Tables)

#### Potentials (Opportunities)
- **Junction Table:** `vtiger_contpotentialrel` (contactid, potentialid)
- **Direct Link:** `vtiger_potential.contact_id` (primary contact for opportunity)
- **Business Rule:** Contact can be related to Potential in TWO ways:
  1. Via junction table (secondary contacts)
  2. Via direct FK (primary contact)
- **Unlink:** Delete from junction table AND set contact_id = 0 in potential
- **Delete:** If Contact is `related_to` for Potential, Potential is soft-deleted

#### Activities (Calendar/Tasks/Events)
- **Junction Table:** `vtiger_cntactivityrel` (contactid, activityid)
- **Type:** Many-to-Many
- **Business Logic:** Contact can be invited to multiple activities
- **Query Filter:** Activities shown as "Open" if:
  - Task: status NOT IN ('Completed', 'Deferred')
  - Event: eventstatus NOT IN ('', 'Held')

#### Emails
- **Junction Table:** `vtiger_seactivityrel` (crmid, activityid)
- **Type:** Many-to-Many
- **Business Logic:** Emails related to Contact, related Potentials, and related Tickets are all shown
- **Special:** Email list includes emails to Contact's Opportunities and Tickets

#### Tickets (HelpDesk)
- **Table:** `vtiger_troubletickets.contact_id`
- **Type:** Many-to-One (many Tickets to one Contact)
- **Unlink:** Set `contact_id = 0` (ticket remains, contact unlinked)

#### Quotes
- **Table:** `vtiger_quotes.contactid`
- **Type:** Many-to-One
- **Unlink:** Set `contactid = 0`

#### Sales Orders
- **Table:** `vtiger_salesorder.contactid`
- **Type:** Many-to-One
- **Unlink:** Set `contactid = 0`
- **Business Logic:** Contact address can auto-populate SO billing/shipping address

#### Purchase Orders
- **Table:** `vtiger_purchaseorder.contactid`
- **Type:** Many-to-One
- **Unlink:** Set `contactid = 0`

#### Invoices
- **Table:** `vtiger_invoice.contactid`
- **Type:** Many-to-One
- **Business Logic:** Contact address can auto-populate invoice billing/shipping address

#### Products
- **Junction Table:** `vtiger_seproductsrel` (crmid, productid, setype)
- **Type:** Many-to-Many
- **Special:** `setype = 'Contacts'` to distinguish from other modules

#### Campaigns
- **Junction Table:** `vtiger_campaigncontrel` (campaignid, contactid, campaignrelstatusid)
- **Type:** Many-to-Many
- **Business Logic:** Tracks campaign participation status

#### Documents
- **Junction Table:** `vtiger_senotesrel` (crmid, notesid)
- **Type:** Many-to-Many

#### Service Contracts
- **Table:** `vtiger_servicecontracts.sc_related_to`
- **Type:** Many-to-One
- **Business Logic:** Service contract can be related to Contact

#### Vendors
- **Junction Table:** `vtiger_vendorcontactrel` (vendorid, contactid)
- **Type:** Many-to-Many
- **Business Logic:** Contacts can be associated with Vendors

#### Assets
- **Table:** `vtiger_assets.contact`
- **Type:** Many-to-One

#### Projects
- **Table:** `vtiger_project.linktoaccountscontacts`
- **Type:** Polymorphic (can link to Account OR Contact)

### 5.3 Address Mapping to Inventory Modules

**Business Rule:** When creating Quotes, Sales Orders, Purchase Orders, or Invoices from a Contact, the Contact's address auto-populates the document.

**Mapping (Contact → Inventory):**

**Account Relationship:**
- `accountid` → `account_id`

**Billing Address:**
- `mailingcity` → `bill_city`
- `mailingstreet` → `bill_street`
- `mailingstate` → `bill_state`
- `mailingzip` → `bill_code`
- `mailingcountry` → `bill_country`
- `mailingpobox` → `bill_pobox`

**Shipping Address:**
- `otherstreet` → `ship_street`
- `othercity` → `ship_city`
- `otherstate` → `ship_state`
- `otherzip` → `ship_code`
- `othercountry` → `ship_country`
- `otherpobox` → `ship_pobox`

**Implementation:** `Contacts_Record_Model::getInventoryMappingFields()`

---

## 6. Hidden Business Assumptions

### 6.1 Naming Conventions
- **Display Name:** Contacts are displayed as "FirstName LastName" or just "LastName" if FirstName is empty
- **Contact Number:** Auto-generated with prefix (likely "CON" + sequence number)

### 6.2 Security & Permissions
- **Field-Level Security:** System respects profile-based field visibility
- **Record-Level Security:** Contacts respect user/group ownership and sharing rules
- **Portal Access:** Portal users can only see their own data (enforced elsewhere)

### 6.3 Email Behavior
- **Portal Credentials:** Sent via `send_mail()` function
- **Email Template:** Uses `vtiger_emailtemplates` with `schedulednotificationid = 5` for portal login details
- **Opt-Out Respected:** No automated emails sent if `emailoptout = 1`
- **Support Email:** Uses global `$HELPDESK_SUPPORT_EMAIL_ID` as sender

### 6.4 Image Handling
- **One Image Rule:** Only one image per contact (enforced by deletion of old image)
- **Image Type:** Stored as `setype = 'Contacts Image'` in crmentity
- **Storage:** Physical file stored in filesystem, metadata in database

### 6.5 Reporting Hierarchy
- **Reports To:** Field exists (`reportsto`) but appears to be text, not a FK to another Contact
- **Implication:** No enforced hierarchical relationship between Contacts

### 6.6 Activity History
- **Completed Activities:** Activities with status 'Completed', 'Deferred', or eventstatus 'Held' are considered "history"
- **Open Activities:** All others are considered "open"

### 6.7 Opportunity Ownership
- **Special Rule:** When Contact is deleted and Contact is `related_to` for an Opportunity, the Opportunity is also soft-deleted
- **Rationale:** If the Contact IS the Opportunity (not just linked), deleting the Contact invalidates the Opportunity

### 6.8 Transfer Ownership
- **Relationship Transfer:** When merging Contacts (transfer ownership), all related records move to the target Contact
- **Duplicate Prevention:** System checks to avoid duplicate relationships during transfer
- **Affected Entities:** Potentials, Activities, Emails, Tickets, Quotes, POs, SOs, Products, Documents, Campaigns, Invoices, Service Contracts, Projects, Assets, Vendors

---

## 7. Critical Database Side-Effects Summary

### On Contact Create:
1. Insert into `vtiger_crmentity`
2. Insert into `vtiger_contactdetails`
3. Insert into `vtiger_contactsubdetails`
4. Insert into `vtiger_contactaddress`
5. Insert into `vtiger_contactscf`
6. Insert into `vtiger_customerdetails`
7. **Conditional:** Insert into `vtiger_portalinfo` (if portal enabled)
8. **Conditional:** Insert into `vtiger_attachments` + `vtiger_seattachmentsrel` (if image uploaded)
9. **Conditional:** Send portal credentials email (if portal enabled AND emailoptout = 0)

### On Contact Update:
1. Update `vtiger_crmentity` (modifiedtime, modifiedby, label)
2. Update `vtiger_contactdetails`
3. **Conditional:** Update `vtiger_contactsubdetails`
4. **Conditional:** Update `vtiger_contactaddress`
5. **Conditional:** Update `vtiger_contactscf`
6. **Conditional:** Update `vtiger_customerdetails`
7. **Conditional:** Update/Insert `vtiger_portalinfo` (if portal toggled)
8. **Conditional:** Delete old + Insert new attachment (if image changed)
9. **Conditional:** Send portal credentials email (if portal enabled/email changed AND emailoptout = 0)

### On Contact Delete:
1. Update `vtiger_crmentity` (set deleted = 1)
2. Soft-delete related Potentials (where Contact is related_to)
3. Unlink from Tickets (set contact_id = 0)
4. Unlink from Purchase Orders (set contactid = 0)
5. Unlink from Sales Orders (set contactid = 0)
6. Unlink from Quotes (set contactid = 0)
7. Delete from `vtiger_portalinfo`
8. Update `vtiger_customerdetails` (set portal = 0, clear support dates)
9. Insert backup records into `vtiger_relatedlists_rb` for each relationship change

### On Relationship Link:
- **Products:** Insert into `vtiger_seproductsrel`
- **Campaigns:** Insert into `vtiger_campaigncontrel`
- **Potentials:** Insert into `vtiger_contpotentialrel`
- **Vendors:** Insert into `vtiger_vendorcontactrel`

### On Relationship Unlink:
- **Accounts:** Update `vtiger_contactdetails` (set accountid = NULL)
- **Potentials:** Delete from `vtiger_contpotentialrel` AND update `vtiger_potential` (set contact_id = 0)
- **Campaigns:** Delete from `vtiger_campaigncontrel`
- **Products:** Delete from `vtiger_seproductsrel`
- **Vendors:** Delete from `vtiger_vendorcontactrel`
- **Documents:** Delete from `vtiger_senotesrel`

---

## 8. Laravel Rebuild Implications

### 8.1 Entity Structure
**Domain Entity:** `Contact`

**Value Objects to Consider:**
- `EmailAddress` (with opt-out flag)
- `FullName` (salutation + firstname + lastname)
- `PhoneNumber` (multiple types: office, mobile, home, fax, assistant)
- `Address` (mailing and alternate)
- `PortalCredentials` (username, password, active status)

### 8.2 Repository Requirements
**ContactRepositoryInterface must handle:**
1. EAV pattern across 6+ tables
2. Transactional writes across all tables
3. Soft delete with relationship cleanup
4. Relationship management for 15+ related modules
5. Portal credential management
6. Image attachment handling

### 8.3 Domain Events to Emit
1. `ContactCreated`
2. `ContactUpdated`
3. `ContactDeleted`
4. `ContactPortalEnabled`
5. `ContactPortalDisabled`
6. `ContactEmailChanged`
7. `ContactAccountLinked`
8. `ContactAccountUnlinked`
9. `ContactImageUploaded`

### 8.4 Business Invariants to Enforce
1. Contact MUST have lastname
2. Contact MUST have owner (smownerid)
3. Contact can have at most ONE Account
4. Contact can have at most ONE image
5. Portal username MUST equal email address
6. Portal can only be enabled if email exists
7. Contact number MUST be unique

### 8.5 Use Cases to Implement
1. `CreateContactUseCase`
2. `UpdateContactUseCase`
3. `DeleteContactUseCase`
4. `EnablePortalAccessUseCase`
5. `DisablePortalAccessUseCase`
6. `LinkContactToAccountUseCase`
7. `UnlinkContactFromAccountUseCase`
8. `TransferContactOwnershipUseCase`
9. `UploadContactImageUseCase`
10. `ConvertLeadToContactUseCase`

### 8.6 Critical Implementation Notes
1. **ID Generation:** Use `vtiger_crmentity_seq` for new Contact IDs (never auto-increment)
2. **Timestamps:** Use `createdtime` and `modifiedtime`, NOT Laravel's `created_at`/`updated_at`
3. **Soft Deletes:** Use `deleted` flag in `vtiger_crmentity`, NOT Laravel's `deleted_at`
4. **Transactions:** All writes must be transactional across multiple tables
5. **Email Service:** Portal credential emails must be sent asynchronously (queue)
6. **File Storage:** Image handling must integrate with vtiger's attachment system

---

## 9. Data Integrity Rules

### 9.1 Referential Integrity
- `contactid` in all related tables CASCADE DELETE via FK constraints
- `accountid` is optional (can be NULL)
- `smownerid` must reference valid user or group

### 9.2 Data Consistency
- `contact_no` must be unique across all contacts
- `label` in crmentity should match "FirstName LastName" from contactdetails
- Portal `user_name` must match `email` from contactdetails
- Image `name` in attachments must match `imagename` in contactdetails

### 9.3 Business Logic Consistency
- If `portal = 1` in customerdetails, record must exist in portalinfo
- If `emailoptout = 1`, no automated emails should be sent
- If Contact has Potentials where Contact is `related_to`, deleting Contact must delete Potentials

---

## 10. Search & Query Patterns

### 10.1 Common Query Patterns
1. **List Contacts by Owner:** Filter by `smownerid` and `deleted = 0`
2. **Search by Email:** Search across `email`, `otheremail`, `secondaryemail`
3. **Search by Name:** Search across `firstname` and `lastname` (supports REGEXP for multiple terms)
4. **Find by Account:** Filter by `accountid`
5. **Portal Users:** Join with `vtiger_portalinfo` where `isactive = 1`

### 10.2 Default Sorting
- **Default Order By:** `lastname`
- **Default Sort Order:** ASC

### 10.3 List View Fields
- First Name
- Last Name
- Title
- Account Name
- Email
- Office Phone
- Assigned To

---

## 11. Integration Points

### 11.1 Email System
- Portal credential emails
- Mass email campaigns (respects emailoptout)
- Email tracking (vtiger_email_track)

### 11.2 Workflow System
- Event: `vtiger.lead.convertlead` (when Lead converts to Contact)
- Custom workflows can trigger on Contact create/update/delete

### 11.3 External Systems
- Customer Portal (separate application)
- Outlook Plugin (syncs contacts)
- WebServices API (CRUD operations)

---

## 12. Performance Considerations

### 12.1 Query Optimization
- Index on `accountid` for Account-Contact queries
- Index on `email` for portal login lookups
- Index on `smownerid` + `deleted` for user-specific lists

### 12.2 EAV Performance
- Joining 6 tables for full Contact data can be slow
- Consider caching frequently accessed Contact data
- Use selective field loading (don't always join all tables)

---

## 13. Conclusion

The vtiger Contacts module is a **relationship hub** that connects people to all business activities. Its complexity lies not in the Contact entity itself, but in the **web of relationships** it maintains with 15+ other modules.

**Key Takeaways for Laravel Rebuild:**
1. **EAV Pattern:** Must handle 6-table joins for complete Contact data
2. **Relationship Management:** Must handle 15+ different relationship types with varying cascade behaviors
3. **Portal Integration:** Must manage customer portal access with credential generation and email notifications
4. **Soft Delete Complexity:** Deletion triggers cascading side-effects across multiple modules
5. **Address Mapping:** Contact addresses auto-populate inventory documents (Quotes, Orders, Invoices)
6. **Lead Conversion:** Contacts can be created from Lead conversion with field mapping

**Critical Business Rules:**
- `lastname` is mandatory
- Portal username = email address
- Only one image per contact
- Deleting Contact can delete related Potentials
- All relationship changes must be logged for recovery

This document serves as the **single source of truth** for rebuilding the Contacts module in Laravel using Clean Architecture principles.
