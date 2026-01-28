# VTiger CRM - Complete Module and Database Table Analysis

**Generated:** 2026-01-28 11:43:20

## Table of Contents
1. [Overview](#overview)
2. [Core Business Modules](#core-business-modules)
3. [System & Configuration Tables](#system--configuration-tables)
4. [Workflow Tables](#workflow-tables)
5. [Inventory Management Tables](#inventory-management-tables)
6. [Picklist Tables](#picklist-tables)
7. [Relationship Tables](#relationship-tables)
8. [All Database Tables](#all-database-tables)

---

## Overview

This document provides a comprehensive analysis of all modules in the VTiger CRM system and their associated database tables. The system contains **298** database tables supporting various business modules.

### Database Architecture Patterns

VTiger CRM follows these naming and architectural patterns:

1. **Main Entity Tables**: tiger_[modulename] - Core module data
2. **Custom Fields Tables**: tiger_[modulename]cf - Custom field values (EAV pattern)
3. **Address Tables**: tiger_[modulename]billads, tiger_[modulename]shipads - Billing/shipping addresses
4. **Sub-detail Tables**: tiger_[modulename]subdetails - Additional module information
5. **Relationship Tables**: Tables ending with 
el - Many-to-many relationships
6. **Picklist Tables**: Lookup tables for dropdown values
7. **History Tables**: Tables ending with history - Audit trail for status changes

---

## Core Business Modules

### Accounts

**Primary Tables:**
- **vtiger_account** (20 fields)
  - Primary Key: `accountid`
  - Key Fields: `accountid``, ``account_no``, ``accountname``, ``parentid``, ``account_type``, ``industry``, ``annualrevenue``, ``rating``, ``ownership``, ``siccode`
- **vtiger_accountbillads** (7 fields)
  - Primary Key: `accountaddressid`
  - Key Fields: `accountaddressid``, ``bill_city``, ``bill_code``, ``bill_country``, ``bill_state``, ``bill_street``, ``bill_pobox`
- **vtiger_accountshipads** (7 fields)
  - Primary Key: `accountaddressid`
  - Key Fields: `accountaddressid``, ``ship_city``, ``ship_code``, ``ship_country``, ``ship_state``, ``ship_pobox``, ``ship_street`
- **vtiger_accountscf** (1 fields)
  - Primary Key: `accountid`
  - Key Fields: `accountid`


### Assets

**Primary Tables:**


### Calendar

**Primary Tables:**
- **vtiger_activity** (18 fields)
  - Primary Key: `activityid`
  - Key Fields: `activityid``, ``subject``, ``semodule``, ``activitytype``, ``date_start``, ``due_date``, ``time_start``, ``time_end``, ``sendnotification``, ``duration_hours`
- **vtiger_activitycf** (1 fields)
  - Primary Key: `activityid`
  - Key Fields: `activityid`
- **vtiger_activity_reminder** (4 fields)
  - Primary Key: `activity_id, recurringid`
  - Key Fields: `activity_id``, ``reminder_time``, ``reminder_sent``, ``recurringid`
- **vtiger_activity_reminder_popup** (6 fields)
  - Primary Key: `reminderid`
  - Key Fields: `reminderid``, ``semodule``, ``recordid``, ``date_start``, ``time_start``, ``status`
- **vtiger_recurringevents** (6 fields)
  - Primary Key: `recurringid`
  - Key Fields: `recurringid``, ``activityid``, ``recurringdate``, ``recurringtype``, ``recurringfreq``, ``recurringinfo`
- **vtiger_invitees** (3 fields)
  - Primary Key: `activityid, inviteeid`
  - Key Fields: `activityid``, ``inviteeid``, ``status`


### Campaigns

**Primary Tables:**
- **vtiger_campaign** (21 fields)
  - Primary Key: `campaignid`
  - Key Fields: `campaign_no``, ``campaignname``, ``campaigntype``, ``campaignstatus``, ``expectedrevenue``, ``budgetcost``, ``actualcost``, ``expectedresponse``, ``numsent``, ``product_id`
- **vtiger_campaignscf** (1 fields)
  - Primary Key: `campaignid`
  - Key Fields: `campaignid`
- **vtiger_campaignaccountrel** (3 fields)
  - Key Fields: `campaignid``, ``accountid``, ``campaignrelstatusid`
- **vtiger_campaigncontrel** (3 fields)
  - Primary Key: `campaignid, contactid, campaignrelstatusid`
  - Key Fields: `campaignid``, ``contactid``, ``campaignrelstatusid`
- **vtiger_campaignleadrel** (3 fields)
  - Primary Key: `campaignid, leadid, campaignrelstatusid`
  - Key Fields: `campaignid``, ``leadid``, ``campaignrelstatusid`


### Contacts

**Primary Tables:**
- **vtiger_contactdetails** (23 fields)
  - Primary Key: `contactid`
  - Key Fields: `contactid``, ``contact_no``, ``accountid``, ``salutation``, ``firstname``, ``lastname``, ``email``, ``phone``, ``mobile``, ``title`
- **vtiger_contactsubdetails** (9 fields)
  - Primary Key: `contactsubscriptionid`
  - Key Fields: `contactsubscriptionid``, ``homephone``, ``otherphone``, ``assistant``, ``assistantphone``, ``birthday``, ``laststayintouchrequest``, ``laststayintouchsavedate``, ``leadsource`
- **vtiger_contactaddress** (13 fields)
  - Primary Key: `contactaddressid`
  - Key Fields: `contactaddressid``, ``mailingcity``, ``mailingstreet``, ``mailingcountry``, ``othercountry``, ``mailingstate``, ``mailingpobox``, ``othercity``, ``otherstate``, ``mailingzip`
- **vtiger_contactscf** (1 fields)
  - Primary Key: `contactid`
  - Key Fields: `contactid`
- **vtiger_portalinfo** (9 fields)
  - Primary Key: `id`
  - Key Fields: `id``, ``user_name``, ``user_password``, ``type``, ``cryptmode``, ``last_login_time``, ``login_time``, ``logout_time``, ``isactive`
- **vtiger_customerdetails** (4 fields)
  - Primary Key: `customerid`
  - Key Fields: `customerid``, ``portal``, ``support_start_date``, ``support_end_date`


### Documents

**Primary Tables:**
- **vtiger_notes** (12 fields)
  - Primary Key: `notesid`
  - Key Fields: `notesid``, ``note_no``, ``title``, ``filename``, ``notecontent``, ``folderid``, ``filetype``, ``filelocationtype``, ``filedownloadcount``, ``filestatus`
- **vtiger_notescf** (1 fields)
  - Primary Key: `notesid`
  - Key Fields: `notesid`


### Emails

**Primary Tables:**
- **vtiger_emaildetails** (8 fields)
  - Primary Key: `emailid`
  - Key Fields: `emailid``, ``from_email``, ``to_email``, ``cc_email``, ``bcc_email``, ``assigned_user_email``, ``idlists``, ``email_flag`
- **vtiger_email_track** (3 fields)
  - Key Fields: `crmid``, ``mailid``, ``access_count`


### EmailTemplates

**Primary Tables:**
- **vtiger_emailtemplates** (10 fields)
  - Primary Key: `templateid`
  - Key Fields: `foldername``, ``templatename``, ``templatepath``, ``subject``, ``description``, ``body``, ``deleted``, ``templateid``, ``systemtemplate``, ``module`


### Events

**Primary Tables:**
- **vtiger_activity** (18 fields)
  - Primary Key: `activityid`
  - Key Fields: `activityid``, ``subject``, ``semodule``, ``activitytype``, ``date_start``, ``due_date``, ``time_start``, ``time_end``, ``sendnotification``, ``duration_hours`
- **vtiger_activitycf** (1 fields)
  - Primary Key: `activityid`
  - Key Fields: `activityid`
- **vtiger_recurringevents** (6 fields)
  - Primary Key: `recurringid`
  - Key Fields: `recurringid``, ``activityid``, ``recurringdate``, ``recurringtype``, ``recurringfreq``, ``recurringinfo`
- **vtiger_invitees** (3 fields)
  - Primary Key: `activityid, inviteeid`
  - Key Fields: `activityid``, ``inviteeid``, ``status`


### Faq

**Primary Tables:**
- **vtiger_faq** (7 fields)
  - Primary Key: `id`
  - Key Fields: `id``, ``faq_no``, ``product_id``, ``question``, ``answer``, ``category``, ``status`
- **vtiger_faqcf** (1 fields)
  - Primary Key: `faqid`
  - Key Fields: `faqid`
- **vtiger_faqcomments** (4 fields)
  - Primary Key: `commentid`
  - Key Fields: `commentid``, ``faqid``, ``comments``, ``createdtime`


### HelpDesk

**Primary Tables:**
- **vtiger_troubletickets** (15 fields)
  - Primary Key: `ticketid`
  - Key Fields: `ticketid``, ``ticket_no``, ``groupname``, ``parent_id``, ``product_id``, ``priority``, ``severity``, ``status``, ``category``, ``title`
- **vtiger_ticketcf** (2 fields)
  - Primary Key: `ticketid`
  - Key Fields: `ticketid``, ``from_portal`
- **vtiger_ticketcomments** (6 fields)
  - Primary Key: `commentid`
  - Key Fields: `commentid``, ``ticketid``, ``comments``, ``ownerid``, ``ownertype``, ``createdtime`


### Invoice

**Primary Tables:**
- **vtiger_invoice** (28 fields)
  - Primary Key: `invoiceid`
  - Key Fields: `invoiceid``, ``subject``, ``salesorderid``, ``customerno``, ``contactid``, ``notes``, ``invoicedate``, ``duedate``, ``invoiceterms``, ``type`
- **vtiger_invoicecf** (1 fields)
  - Primary Key: `invoiceid`
  - Key Fields: `invoiceid`
- **vtiger_invoicebillads** (7 fields)
  - Primary Key: `invoicebilladdressid`
  - Key Fields: `invoicebilladdressid``, ``bill_city``, ``bill_code``, ``bill_country``, ``bill_state``, ``bill_street``, ``bill_pobox`
- **vtiger_invoiceshipads** (7 fields)
  - Primary Key: `invoiceshipaddressid`
  - Key Fields: `invoiceshipaddressid``, ``ship_city``, ``ship_code``, ``ship_country``, ``ship_state``, ``ship_street``, ``ship_pobox`
- **vtiger_invoicestatushistory** (6 fields)
  - Primary Key: `historyid`
  - Key Fields: `historyid``, ``invoiceid``, ``accountname``, ``total``, ``invoicestatus``, ``lastmodified`
- **vtiger_invoice_recurring_info** (7 fields)
  - Key Fields: `salesorderid``, ``recurring_frequency``, ``start_period``, ``end_period``, ``last_recurring_date``, ``payment_duration``, ``invoice_status`


### Leads

**Primary Tables:**
- **vtiger_leaddetails** (34 fields)
  - Primary Key: `leadid`
  - Key Fields: `leadid``, ``lead_no``, ``email``, ``interest``, ``firstname``, ``salutation``, ``lastname``, ``company``, ``annualrevenue``, ``industry`
- **vtiger_leadsubdetails** (5 fields)
  - Primary Key: `leadsubscriptionid`
  - Key Fields: `leadsubscriptionid``, ``website``, ``callornot``, ``readornot``, ``empct`
- **vtiger_leadaddress** (11 fields)
  - Primary Key: `leadaddressid`
  - Key Fields: `leadaddressid``, ``city``, ``code``, ``state``, ``pobox``, ``country``, ``phone``, ``mobile``, ``fax``, ``lane`
- **vtiger_leadscf** (1 fields)
  - Primary Key: `leadid`
  - Key Fields: `leadid`


### ModComments

**Primary Tables:**


### Potentials

**Primary Tables:**
- **vtiger_potential** (26 fields)
  - Primary Key: `potentialid`
  - Key Fields: `potentialid``, ``potential_no``, ``related_to``, ``potentialname``, ``amount``, ``currency``, ``closingdate``, ``typeofrevenue``, ``nextstep``, ``private`
- **vtiger_potentialscf** (1 fields)
  - Primary Key: `potentialid`
  - Key Fields: `potentialid`
- **vtiger_potstagehistory** (8 fields)
  - Primary Key: `historyid`
  - Key Fields: `historyid``, ``potentialid``, ``amount``, ``stage``, ``probability``, ``expectedrevenue``, ``closedate``, ``lastmodified`


### PriceBooks

**Primary Tables:**
- **vtiger_pricebook** (5 fields)
  - Primary Key: `pricebookid`
  - Key Fields: `pricebookid``, ``pricebook_no``, ``bookname``, ``active``, ``currency_id`
- **vtiger_pricebookcf** (1 fields)
  - Primary Key: `pricebookid`
  - Key Fields: `pricebookid`
- **vtiger_pricebookproductrel** (4 fields)
  - Primary Key: `pricebookid, productid`
  - Key Fields: `pricebookid``, ``productid``, ``listprice``, ``usedcurrency`


### Products

**Primary Tables:**
- **vtiger_products** (33 fields)
  - Primary Key: `productid`
  - Key Fields: `productid``, ``product_no``, ``productname``, ``productcode``, ``productcategory``, ``manufacturer``, ``qty_per_unit``, ``unit_price``, ``weight``, ``pack_size`
- **vtiger_productcf** (1 fields)
  - Primary Key: `productid`
  - Key Fields: `productid`
- **vtiger_productcurrencyrel** (4 fields)
  - Key Fields: `productid``, ``currencyid``, ``converted_price``, ``actual_price`
- **vtiger_producttaxrel** (4 fields)
  - Key Fields: `productid``, ``taxid``, ``taxpercentage``, ``regions`


### Project

**Primary Tables:**


### ProjectMilestone

**Primary Tables:**


### ProjectTask

**Primary Tables:**


### PurchaseOrder

**Primary Tables:**
- **vtiger_purchaseorder** (25 fields)
  - Primary Key: `purchaseorderid`
  - Key Fields: `purchaseorderid``, ``subject``, ``quoteid``, ``vendorid``, ``requisition_no``, ``purchaseorder_no``, ``tracking_no``, ``contactid``, ``duedate``, ``carrier`
- **vtiger_purchaseordercf** (1 fields)
  - Primary Key: `purchaseorderid`
  - Key Fields: `purchaseorderid`
- **vtiger_pobillads** (7 fields)
  - Primary Key: `pobilladdressid`
  - Key Fields: `pobilladdressid``, ``bill_city``, ``bill_code``, ``bill_country``, ``bill_state``, ``bill_street``, ``bill_pobox`
- **vtiger_poshipads** (7 fields)
  - Primary Key: `poshipaddressid`
  - Key Fields: `poshipaddressid``, ``ship_city``, ``ship_code``, ``ship_country``, ``ship_state``, ``ship_street``, ``ship_pobox`
- **vtiger_postatushistory** (6 fields)
  - Primary Key: `historyid`
  - Key Fields: `historyid``, ``purchaseorderid``, ``vendorname``, ``total``, ``postatus``, ``lastmodified`


### Quotes

**Primary Tables:**
- **vtiger_quotes** (23 fields)
  - Primary Key: `quoteid`
  - Key Fields: `quoteid``, ``subject``, ``potentialid``, ``quotestage``, ``validtill``, ``contactid``, ``quote_no``, ``subtotal``, ``carrier``, ``shipping`
- **vtiger_quotescf** (1 fields)
  - Primary Key: `quoteid`
  - Key Fields: `quoteid`
- **vtiger_quotesbillads** (7 fields)
  - Primary Key: `quotebilladdressid`
  - Key Fields: `quotebilladdressid``, ``bill_city``, ``bill_code``, ``bill_country``, ``bill_state``, ``bill_street``, ``bill_pobox`
- **vtiger_quotesshipads** (7 fields)
  - Primary Key: `quoteshipaddressid`
  - Key Fields: `quoteshipaddressid``, ``ship_city``, ``ship_code``, ``ship_country``, ``ship_state``, ``ship_street``, ``ship_pobox`
- **vtiger_quotestagehistory** (6 fields)
  - Primary Key: `historyid`
  - Key Fields: `historyid``, ``quoteid``, ``accountname``, ``total``, ``quotestage``, ``lastmodified`


### SalesOrder

**Primary Tables:**
- **vtiger_salesorder** (30 fields)
  - Primary Key: `salesorderid`
  - Key Fields: `salesorderid``, ``subject``, ``potentialid``, ``customerno``, ``salesorder_no``, ``quoteid``, ``vendorterms``, ``contactid``, ``vendorid``, ``duedate`
- **vtiger_salesordercf** (1 fields)
  - Primary Key: `salesorderid`
  - Key Fields: `salesorderid`
- **vtiger_sobillads** (7 fields)
  - Primary Key: `sobilladdressid`
  - Key Fields: `sobilladdressid``, ``bill_city``, ``bill_code``, ``bill_country``, ``bill_state``, ``bill_street``, ``bill_pobox`
- **vtiger_soshipads** (7 fields)
  - Primary Key: `soshipaddressid`
  - Key Fields: `soshipaddressid``, ``ship_city``, ``ship_code``, ``ship_country``, ``ship_state``, ``ship_street``, ``ship_pobox`
- **vtiger_sostatushistory** (6 fields)
  - Primary Key: `historyid`
  - Key Fields: `historyid``, ``salesorderid``, ``accountname``, ``total``, ``sostatus``, ``lastmodified`


### ServiceContracts

**Primary Tables:**


### Services

**Primary Tables:**


### SMSNotifier

**Primary Tables:**


### Users

**Primary Tables:**
- **vtiger_users** (59 fields)
  - Primary Key: `id`
  - Key Fields: `id``, ``user_name``, ``user_password``, ``user_hash``, ``cal_color``, ``first_name``, ``last_name``, ``reports_to_id``, ``is_admin``, ``currency_id`
- **vtiger_user2role** (2 fields)
  - Primary Key: `userid`
  - Key Fields: `userid``, ``roleid`
- **vtiger_users2group** (2 fields)
  - Primary Key: `groupid, userid`
  - Key Fields: `groupid``, ``userid`
- **vtiger_user_module_preferences** (3 fields)
  - Primary Key: `userid, tabid`
  - Key Fields: `userid``, ``tabid``, ``default_cvid`
- **vtiger_loginhistory** (6 fields)
  - Primary Key: `login_id`
  - Key Fields: `login_id``, ``user_name``, ``user_ip``, ``logout_time``, ``login_time``, ``status`


### Vendors

**Primary Tables:**
- **vtiger_vendor** (15 fields)
  - Primary Key: `vendorid`
  - Key Fields: `vendorid``, ``vendor_no``, ``vendorname``, ``phone``, ``email``, ``website``, ``glacct``, ``category``, ``street``, ``city`
- **vtiger_vendorcf** (1 fields)
  - Primary Key: `vendorid`
  - Key Fields: `vendorid`
- **vtiger_vendorcontactrel** (2 fields)
  - Primary Key: `vendorid, contactid`
  - Key Fields: `vendorid``, ``contactid`


---

## System & Configuration Tables

These tables manage system configuration, user permissions, roles, profiles, and metadata.

**Total Tables:** 52
- **vtiger_blocks** (11 fields)
- **vtiger_crmentity** (14 fields)
- **vtiger_currencies** (4 fields)
- **vtiger_currency_info** (8 fields)
- **vtiger_customview** (7 fields)
- **vtiger_cvadvfilter** (7 fields)
- **vtiger_cvadvfilter_grouping** (4 fields)
- **vtiger_cvcolumnlist** (3 fields)
- **vtiger_cvstdfilter** (5 fields)
- **vtiger_datashare_grp2grp** (4 fields)
- **vtiger_datashare_grp2role** (4 fields)
- **vtiger_datashare_grp2rs** (4 fields)
- **vtiger_datashare_module_rel** (3 fields)
- **vtiger_datashare_relatedmodule_permission** (3 fields)
- **vtiger_datashare_relatedmodules** (3 fields)
- **vtiger_datashare_role2group** (4 fields)
- **vtiger_datashare_role2role** (4 fields)
- **vtiger_datashare_role2rs** (4 fields)
- **vtiger_datashare_rs2grp** (4 fields)
- **vtiger_datashare_rs2role** (4 fields)
- **vtiger_datashare_rs2rs** (4 fields)
- **vtiger_def_org_field** (4 fields)
- **vtiger_def_org_share** (4 fields)
- **vtiger_entityname** (6 fields)
- **vtiger_field** (20 fields)
- **vtiger_fieldmodulerel** (5 fields)
- **vtiger_group2grouprel** (2 fields)
- **vtiger_group2role** (2 fields)
- **vtiger_group2rs** (2 fields)
- **vtiger_groups** (3 fields)
- **vtiger_links** (11 fields)
- **vtiger_organizationdetails** (13 fields)
- **vtiger_picklist** (2 fields)
- **vtiger_picklist_dependency** (7 fields)
- **vtiger_profile** (3 fields)
- **vtiger_profile2field** (5 fields)
- **vtiger_profile2globalpermissions** (3 fields)
- **vtiger_profile2standardpermissions** (4 fields)
- **vtiger_profile2tab** (3 fields)
- **vtiger_profile2utility** (4 fields)
- **vtiger_relatedlists** (11 fields)
- **vtiger_relatedlists_rb** (6 fields)
- **vtiger_role** (4 fields)
- **vtiger_role2picklist** (4 fields)
- **vtiger_role2profile** (2 fields)
- **vtiger_settings_blocks** (3 fields)
- **vtiger_settings_field** (8 fields)
- **vtiger_tab** (14 fields)
- **vtiger_version** (3 fields)
- **vtiger_ws_entity** (5 fields)
- **vtiger_ws_entity_tables** (2 fields)
- **vtiger_ws_fieldinfo** (3 fields)

---

## Workflow Tables

These tables manage automated workflows, tasks, and business process automation.

**Total Tables:** 7
- **com_vtiger_workflow_activatedonce** (2 fields)
- **com_vtiger_workflow_tasktypes** (8 fields)
- **com_vtiger_workflows** (16 fields)
- **com_vtiger_workflowtask_queue** (4 fields)
- **com_vtiger_workflowtasks** (4 fields)
- **com_vtiger_workflowtasks_entitymethod** (5 fields)
- **com_vtiger_workflowtemplates** (4 fields)

---

## Inventory Management Tables

These tables are shared across inventory modules (Quotes, Sales Orders, Purchase Orders, Invoices).

**Total Tables:** 6
- **vtiger_inventory_tandc** (3 fields)
- **vtiger_inventoryproductrel** (11 fields)
- **vtiger_inventoryshippingrel** (1 fields)
- **vtiger_inventorysubproductrel** (4 fields)
- **vtiger_inventorytaxinfo** (5 fields)
- **vtiger_shippingtaxinfo** (5 fields)

---

## Picklist Tables

These tables store dropdown/picklist values for various modules.

**Total Tables:** 40
- **vtiger_accountrating**
- **vtiger_accounttype**
- **vtiger_activitytype**
- **vtiger_campaignrelstatus**
- **vtiger_campaignstatus**
- **vtiger_campaigntype**
- **vtiger_carrier**
- **vtiger_eventstatus**
- **vtiger_faqcategories**
- **vtiger_faqstatus**
- **vtiger_glacct**
- **vtiger_industry**
- **vtiger_invoicestatus**
- **vtiger_leadsource**
- **vtiger_leadstage**
- **vtiger_leadstatus**
- **vtiger_manufacturer**
- **vtiger_opportunity_type**
- **vtiger_opportunitystage**
- **vtiger_payment_duration**
- **vtiger_postatus**
- **vtiger_priority**
- **vtiger_productcategory**
- **vtiger_quotestage**
- **vtiger_rating**
- **vtiger_recurring_frequency**
- **vtiger_recurringtype**
- **vtiger_sales_stage**
- **vtiger_salutationtype**
- **vtiger_sostatus**
- **vtiger_status**
- **vtiger_taskpriority**
- **vtiger_taskstatus**
- **vtiger_taxclass**
- **vtiger_ticketcategories**
- **vtiger_ticketpriorities**
- **vtiger_ticketseverities**
- **vtiger_ticketstatus**
- **vtiger_usageunit**
- **vtiger_visibility**

---

## Relationship Tables

These tables manage many-to-many relationships between different modules.

**Total Tables:** 12
- **vtiger_activityproductrel** (2 fields)
- **vtiger_cntactivityrel** (2 fields)
- **vtiger_contpotentialrel** (2 fields)
- **vtiger_crmentityrel** (4 fields)
- **vtiger_salesmanactivityrel** (2 fields)
- **vtiger_salesmanattachmentsrel** (2 fields)
- **vtiger_salesmanticketrel** (2 fields)
- **vtiger_seactivityrel** (2 fields)
- **vtiger_seattachmentsrel** (2 fields)
- **vtiger_senotesrel** (2 fields)
- **vtiger_seproductsrel** (4 fields)
- **vtiger_seticketsrel** (2 fields)

---

## All Database Tables

Complete list of all 298 database tables in alphabetical order:
- com_vtiger_workflow_activatedonce
- com_vtiger_workflow_tasktypes
- com_vtiger_workflows
- com_vtiger_workflowtask_queue
- com_vtiger_workflowtasks
- com_vtiger_workflowtasks_entitymethod
- com_vtiger_workflowtemplates
- vtiger_account
- vtiger_accountbillads
- vtiger_accountrating
- vtiger_accountscf
- vtiger_accountshipads
- vtiger_accounttype
- vtiger_actionmapping
- vtiger_activity
- vtiger_activity_reminder
- vtiger_activity_reminder_popup
- vtiger_activity_view
- vtiger_activitycf
- vtiger_activityproductrel
- vtiger_activitytype
- vtiger_announcement
- vtiger_app2tab
- vtiger_asterisk
- vtiger_asteriskextensions
- vtiger_asteriskincomingcalls
- vtiger_asteriskincomingevents
- vtiger_attachments
- vtiger_attachmentsfolder
- vtiger_audit_trial
- vtiger_blocks
- vtiger_calendar_default_activitytypes
- vtiger_calendar_user_activitytypes
- vtiger_campaign
- vtiger_campaignaccountrel
- vtiger_campaigncontrel
- vtiger_campaignleadrel
- vtiger_campaignrelstatus
- vtiger_campaignscf
- vtiger_campaignstatus
- vtiger_campaigntype
- vtiger_carrier
- vtiger_cntactivityrel
- vtiger_contactaddress
- vtiger_contactdetails
- vtiger_contactscf
- vtiger_contactsubdetails
- vtiger_contpotentialrel
- vtiger_convertleadmapping
- vtiger_crmentity
- vtiger_crmentityrel
- vtiger_crmsetup
- vtiger_currencies
- vtiger_currency
- vtiger_currency_decimal_separator
- vtiger_currency_grouping_pattern
- vtiger_currency_grouping_separator
- vtiger_currency_info
- vtiger_currency_symbol_placement
- vtiger_customaction
- vtiger_customerdetails
- vtiger_customerportal_prefs
- vtiger_customerportal_tabs
- vtiger_customview
- vtiger_cvadvfilter
- vtiger_cvadvfilter_grouping
- vtiger_cvcolumnlist
- vtiger_cvstdfilter
- vtiger_dashboard_tabs
- vtiger_datashare_grp2grp
- vtiger_datashare_grp2role
- vtiger_datashare_grp2rs
- vtiger_datashare_module_rel
- vtiger_datashare_relatedmodule_permission
- vtiger_datashare_relatedmodules
- vtiger_datashare_role2group
- vtiger_datashare_role2role
- vtiger_datashare_role2rs
- vtiger_datashare_rs2grp
- vtiger_datashare_rs2role
- vtiger_datashare_rs2rs
- vtiger_date_format
- vtiger_def_org_field
- vtiger_def_org_share
- vtiger_defaultcv
- vtiger_duration_minutes
- vtiger_durationhrs
- vtiger_durationmins
- vtiger_email_access
- vtiger_email_track
- vtiger_emaildetails
- vtiger_emailtemplates
- vtiger_entityname
- vtiger_eventhandler_module
- vtiger_eventhandlers
- vtiger_eventstatus
- vtiger_expectedresponse
- vtiger_faq
- vtiger_faqcategories
- vtiger_faqcf
- vtiger_faqcomments
- vtiger_faqstatus
- vtiger_feedback
- vtiger_field
- vtiger_fieldmodulerel
- vtiger_freetagged_objects
- vtiger_freetags
- vtiger_glacct
- vtiger_group2grouprel
- vtiger_group2role
- vtiger_group2rs
- vtiger_groups
- vtiger_home_layout
- vtiger_homedashbd
- vtiger_homedefault
- vtiger_homemodule
- vtiger_homemoduleflds
- vtiger_homereportchart
- vtiger_homerss
- vtiger_homestuff
- vtiger_import_maps
- vtiger_industry
- vtiger_inventory_tandc
- vtiger_inventorynotification
- vtiger_inventoryproductrel
- vtiger_inventoryshippingrel
- vtiger_inventorysubproductrel
- vtiger_inventorytaxinfo
- vtiger_invitees
- vtiger_invoice
- vtiger_invoice_recurring_info
- vtiger_invoicebillads
- vtiger_invoicecf
- vtiger_invoiceshipads
- vtiger_invoicestatus
- vtiger_invoicestatushistory
- vtiger_language
- vtiger_lead_view
- vtiger_leadaddress
- vtiger_leaddetails
- vtiger_leadscf
- vtiger_leadsource
- vtiger_leadstage
- vtiger_leadstatus
- vtiger_leadsubdetails
- vtiger_links
- vtiger_loginhistory
- vtiger_mail_accounts
- vtiger_mailscanner
- vtiger_mailscanner_actions
- vtiger_mailscanner_folders
- vtiger_mailscanner_ids
- vtiger_mailscanner_ruleactions
- vtiger_mailscanner_rules
- vtiger_manufacturer
- vtiger_modentity_num
- vtiger_notebook_contents
- vtiger_notes
- vtiger_notescf
- vtiger_notificationscheduler
- vtiger_opportunity_type
- vtiger_opportunitystage
- vtiger_org_share_action_mapping
- vtiger_org_share_action2tab
- vtiger_organizationdetails
- vtiger_parenttab
- vtiger_parenttabrel
- vtiger_payment_duration
- vtiger_picklist
- vtiger_picklist_dependency
- vtiger_pobillads
- vtiger_portal
- vtiger_portalinfo
- vtiger_poshipads
- vtiger_postatus
- vtiger_postatushistory
- vtiger_potential
- vtiger_potentialscf
- vtiger_potstagehistory
- vtiger_pricebook
- vtiger_pricebookcf
- vtiger_pricebookproductrel
- vtiger_priority
- vtiger_productcategory
- vtiger_productcf
- vtiger_productcurrencyrel
- vtiger_products
- vtiger_producttaxrel
- vtiger_profile
- vtiger_profile2field
- vtiger_profile2globalpermissions
- vtiger_profile2standardpermissions
- vtiger_profile2tab
- vtiger_profile2utility
- vtiger_purchaseorder
- vtiger_purchaseordercf
- vtiger_quotes
- vtiger_quotesbillads
- vtiger_quotescf
- vtiger_quotesshipads
- vtiger_quotestage
- vtiger_quotestagehistory
- vtiger_rating
- vtiger_recurring_frequency
- vtiger_recurringevents
- vtiger_recurringtype
- vtiger_relatedlists
- vtiger_relatedlists_rb
- vtiger_relcriteria
- vtiger_relcriteria_grouping
- vtiger_reminder_interval
- vtiger_report
- vtiger_reportdatefilter
- vtiger_reportfilters
- vtiger_reportfolder
- vtiger_reportgroupbycolumn
- vtiger_reportmodules
- vtiger_reportsharing
- vtiger_reportsortcol
- vtiger_reportsummary
- vtiger_reporttype
- vtiger_role
- vtiger_role2picklist
- vtiger_role2profile
- vtiger_rss
- vtiger_sales_stage
- vtiger_salesmanactivityrel
- vtiger_salesmanattachmentsrel
- vtiger_salesmanticketrel
- vtiger_salesorder
- vtiger_salesordercf
- vtiger_salutationtype
- vtiger_scheduled_reports
- vtiger_seactivityrel
- vtiger_seattachmentsrel
- vtiger_selectcolumn
- vtiger_selectquery
- vtiger_senotesrel
- vtiger_seproductsrel
- vtiger_seticketsrel
- vtiger_settings_blocks
- vtiger_settings_field
- vtiger_sharedcalendar
- vtiger_shareduserinfo
- vtiger_shippingtaxinfo
- vtiger_soapservice
- vtiger_sobillads
- vtiger_soshipads
- vtiger_sostatus
- vtiger_sostatushistory
- vtiger_status
- vtiger_systems
- vtiger_tab
- vtiger_tab_info
- vtiger_taskpriority
- vtiger_taskstatus
- vtiger_taxclass
- vtiger_ticketcategories
- vtiger_ticketcf
- vtiger_ticketcomments
- vtiger_ticketpriorities
- vtiger_ticketseverities
- vtiger_ticketstatus
- vtiger_time_zone
- vtiger_tmp_read_group_rel_sharing_per
- vtiger_tmp_read_group_sharing_per
- vtiger_tmp_read_user_rel_sharing_per
- vtiger_tmp_read_user_sharing_per
- vtiger_tmp_write_group_rel_sharing_per
- vtiger_tmp_write_group_sharing_per
- vtiger_tmp_write_user_rel_sharing_per
- vtiger_tmp_write_user_sharing_per
- vtiger_tracker
- vtiger_troubletickets
- vtiger_usageunit
- vtiger_user_module_preferences
- vtiger_user2mergefields
- vtiger_user2role
- vtiger_users
- vtiger_users_last_import
- vtiger_users2group
- vtiger_vendor
- vtiger_vendorcf
- vtiger_vendorcontactrel
- vtiger_version
- vtiger_visibility
- vtiger_wordtemplates
- vtiger_ws_entity
- vtiger_ws_entity_fieldtype
- vtiger_ws_entity_name
- vtiger_ws_entity_referencetype
- vtiger_ws_entity_tables
- vtiger_ws_fieldinfo
- vtiger_ws_fieldtype
- vtiger_ws_operation
- vtiger_ws_operation_parameters
- vtiger_ws_referencetype
- vtiger_ws_userauthtoken

---

## Module Directory Structure

The following modules exist in the filesystem:
- **Accounts**
- **Assets**
- **Billing**
- **Billing_old**
- **Billing170519**
- **Calendar**
- **Campaigns**
- **com_vtiger_workflow**
- **Contacts**
- **CTLabelsUpdate**
- **CTLabelsUpdate(latest)**
- **CTLabelsUpdate(latest2)**
- **CTPowerBlocksFields**
- **CTPowerBlocksFields(latest)**
- **CTPowerBlocksFields(latest2)**
- **CustomerPortal**
- **CustomView**
- **Documents**
- **Emails**
- **EmailTemplates**
- **Events**
- **ExtensionStore**
- **Faq**
- **Google**
- **HelpDesk**
- **Home**
- **Import**
- **Install**
- **Inventory**
- **Invoice**
- **Leads**
- **MailManager**
- **Migration**
- **Mobile**
- **ModComments**
- **ModTracker**
- **PBXManager**
- **PickList**
- **Portal**
- **Potentials**
- **PriceBooks**
- **Products**
- **Project**
- **ProjectMilestone**
- **ProjectTask**
- **PurchaseOrder**
- **Quotes**
- **RecycleBin**
- **Reports**
- **Rss**
- **SalesOrder**
- **SchoolBookLists**
- **ServiceContracts**
- **Services**
- **Settings**
- **SMSNotifier**
- **SMSTemplates**
- **Users**
- **Utilities**
- **Vendors**
- **Vtiger**
- **Webforms**
- **WSAPP**
- **WTSAPNotifier**

---

## Summary Statistics

- **Total Database Tables:** 298
- **Core Business Modules:** 28
- **System/Configuration Tables:** 52
- **Workflow Tables:** 7
- **Inventory Tables:** 6
- **Picklist Tables:** 40
- **Relationship Tables:** 12
- **Module Directories:** 64

---

*This document was automatically generated from the database schema and module structure.*
