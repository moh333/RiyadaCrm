# Module and Database Table Analysis Script
# This script analyzes all vtiger CRM modules and their associated database tables

$outputFile = "d:\vtiger\TenantCRM\MODULE_DATABASE_ANALYSIS.md"
$schemaFile = "d:\vtiger\TenantCRM\schema\DatabaseSchema.xml"
$modulesDir = "d:\vtiger\TenantCRM\modules"

# Load XML Schema
[xml]$xml = Get-Content $schemaFile

# Get all tables (excluding alter tables)
$allTables = $xml.schema.table | Where-Object { $_.alter -ne 'true' } | Select-Object -ExpandProperty name | Sort-Object

# Module mapping based on vtiger naming conventions
$moduleTableMapping = @{}

# Core entity modules
$coreModules = @{
    "Accounts"         = @("vtiger_account", "vtiger_accountbillads", "vtiger_accountshipads", "vtiger_accountscf")
    "Contacts"         = @("vtiger_contactdetails", "vtiger_contactsubdetails", "vtiger_contactaddress", "vtiger_contactscf", "vtiger_portalinfo", "vtiger_customerdetails")
    "Leads"            = @("vtiger_leaddetails", "vtiger_leadsubdetails", "vtiger_leadaddress", "vtiger_leadscf")
    "Potentials"       = @("vtiger_potential", "vtiger_potentialscf", "vtiger_potstagehistory")
    "Products"         = @("vtiger_products", "vtiger_productcf", "vtiger_productcurrencyrel", "vtiger_producttaxrel")
    "Services"         = @("vtiger_service", "vtiger_servicecf")
    "Vendors"          = @("vtiger_vendor", "vtiger_vendorcf", "vtiger_vendorcontactrel")
    "PriceBooks"       = @("vtiger_pricebook", "vtiger_pricebookcf", "vtiger_pricebookproductrel")
    "Quotes"           = @("vtiger_quotes", "vtiger_quotescf", "vtiger_quotesbillads", "vtiger_quotesshipads", "vtiger_quotestagehistory")
    "SalesOrder"       = @("vtiger_salesorder", "vtiger_salesordercf", "vtiger_sobillads", "vtiger_soshipads", "vtiger_sostatushistory")
    "PurchaseOrder"    = @("vtiger_purchaseorder", "vtiger_purchaseordercf", "vtiger_pobillads", "vtiger_poshipads", "vtiger_postatushistory")
    "Invoice"          = @("vtiger_invoice", "vtiger_invoicecf", "vtiger_invoicebillads", "vtiger_invoiceshipads", "vtiger_invoicestatushistory", "vtiger_invoice_recurring_info")
    "Campaigns"        = @("vtiger_campaign", "vtiger_campaignscf", "vtiger_campaignaccountrel", "vtiger_campaigncontrel", "vtiger_campaignleadrel")
    "HelpDesk"         = @("vtiger_troubletickets", "vtiger_ticketcf", "vtiger_ticketcomments")
    "Faq"              = @("vtiger_faq", "vtiger_faqcf", "vtiger_faqcomments")
    "Documents"        = @("vtiger_notes", "vtiger_notescf")
    "Calendar"         = @("vtiger_activity", "vtiger_activitycf", "vtiger_activity_reminder", "vtiger_activity_reminder_popup", "vtiger_recurringevents", "vtiger_invitees")
    "Events"           = @("vtiger_activity", "vtiger_activitycf", "vtiger_recurringevents", "vtiger_invitees")
    "Users"            = @("vtiger_users", "vtiger_user2role", "vtiger_users2group", "vtiger_user_module_preferences", "vtiger_loginhistory")
    "Emails"           = @("vtiger_emaildetails", "vtiger_email_track")
    "EmailTemplates"   = @("vtiger_emailtemplates")
    "SMSNotifier"      = @("vtiger_smsnotifier", "vtiger_smsnotifier_servers", "vtiger_smsnotifier_status")
    "ModComments"      = @("vtiger_modcomments", "vtiger_modcommentscf")
    "Project"          = @("vtiger_project", "vtiger_projectcf")
    "ProjectTask"      = @("vtiger_projecttask", "vtiger_projecttaskcf")
    "ProjectMilestone" = @("vtiger_projectmilestone", "vtiger_projectmilestonecf")
    "ServiceContracts" = @("vtiger_servicecontracts", "vtiger_servicecontractscf")
    "Assets"           = @("vtiger_assets", "vtiger_assetscf")
}

# System/Configuration tables
$systemTables = @(
    "vtiger_crmentity",
    "vtiger_tab",
    "vtiger_blocks",
    "vtiger_field",
    "vtiger_profile",
    "vtiger_profile2field",
    "vtiger_profile2tab",
    "vtiger_profile2standardpermissions",
    "vtiger_profile2globalpermissions",
    "vtiger_profile2utility",
    "vtiger_role",
    "vtiger_role2profile",
    "vtiger_role2picklist",
    "vtiger_groups",
    "vtiger_group2role",
    "vtiger_group2rs",
    "vtiger_group2grouprel",
    "vtiger_picklist",
    "vtiger_picklist_dependency",
    "vtiger_customview",
    "vtiger_cvcolumnlist",
    "vtiger_cvstdfilter",
    "vtiger_cvadvfilter",
    "vtiger_cvadvfilter_grouping",
    "vtiger_def_org_share",
    "vtiger_def_org_field",
    "vtiger_datashare_grp2grp",
    "vtiger_datashare_grp2role",
    "vtiger_datashare_grp2rs",
    "vtiger_datashare_role2group",
    "vtiger_datashare_role2role",
    "vtiger_datashare_role2rs",
    "vtiger_datashare_rs2grp",
    "vtiger_datashare_rs2role",
    "vtiger_datashare_rs2rs",
    "vtiger_datashare_module_rel",
    "vtiger_datashare_relatedmodules",
    "vtiger_datashare_relatedmodule_permission",
    "vtiger_organizationdetails",
    "vtiger_currencies",
    "vtiger_currency_info",
    "vtiger_settings_field",
    "vtiger_settings_blocks",
    "vtiger_entityname",
    "vtiger_fieldmodulerel",
    "vtiger_relatedlists",
    "vtiger_relatedlists_rb",
    "vtiger_links",
    "vtiger_ws_entity",
    "vtiger_ws_entity_tables",
    "vtiger_ws_fieldinfo",
    "vtiger_version"
)

# Workflow tables
$workflowTables = @(
    "com_vtiger_workflows",
    "com_vtiger_workflowtasks",
    "com_vtiger_workflowtask_queue",
    "com_vtiger_workflow_activatedonce",
    "com_vtiger_workflow_tasktypes",
    "com_vtiger_workflowtasks_entitymethod",
    "com_vtiger_workflowtemplates"
)

# Inventory/Common tables
$inventoryTables = @(
    "vtiger_inventoryproductrel",
    "vtiger_inventorysubproductrel",
    "vtiger_inventoryshippingrel",
    "vtiger_inventorytaxinfo",
    "vtiger_inventory_tandc",
    "vtiger_shippingtaxinfo"
)

# Picklist tables
$picklistTables = @(
    "vtiger_accounttype",
    "vtiger_accountrating",
    "vtiger_industry",
    "vtiger_leadstatus",
    "vtiger_leadstage",
    "vtiger_leadsource",
    "vtiger_opportunitystage",
    "vtiger_opportunity_type",
    "vtiger_sales_stage",
    "vtiger_quotestage",
    "vtiger_invoicestatus",
    "vtiger_sostatus",
    "vtiger_postatus",
    "vtiger_campaignstatus",
    "vtiger_campaigntype",
    "vtiger_campaignrelstatus",
    "vtiger_eventstatus",
    "vtiger_activitytype",
    "vtiger_taskstatus",
    "vtiger_taskpriority",
    "vtiger_ticketstatus",
    "vtiger_ticketpriorities",
    "vtiger_ticketseverities",
    "vtiger_ticketcategories",
    "vtiger_faqstatus",
    "vtiger_faqcategories",
    "vtiger_productcategory",
    "vtiger_manufacturer",
    "vtiger_usageunit",
    "vtiger_glacct",
    "vtiger_taxclass",
    "vtiger_salutationtype",
    "vtiger_carrier",
    "vtiger_rating",
    "vtiger_priority",
    "vtiger_visibility",
    "vtiger_status",
    "vtiger_payment_duration",
    "vtiger_recurring_frequency",
    "vtiger_recurringtype"
)

# Relationship tables
$relationshipTables = @(
    "vtiger_crmentityrel",
    "vtiger_cntactivityrel",
    "vtiger_contpotentialrel",
    "vtiger_seactivityrel",
    "vtiger_seattachmentsrel",
    "vtiger_senotesrel",
    "vtiger_seproductsrel",
    "vtiger_seticketsrel",
    "vtiger_salesmanactivityrel",
    "vtiger_salesmanattachmentsrel",
    "vtiger_salesmanticketrel",
    "vtiger_activityproductrel"
)

# Generate Markdown Output
$output = @"
# VTiger CRM - Complete Module and Database Table Analysis

**Generated:** $(Get-Date -Format "yyyy-MM-dd HH:mm:ss")

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

This document provides a comprehensive analysis of all modules in the VTiger CRM system and their associated database tables. The system contains **$($allTables.Count)** database tables supporting various business modules.

### Database Architecture Patterns

VTiger CRM follows these naming and architectural patterns:

1. **Main Entity Tables**: `vtiger_[modulename]` - Core module data
2. **Custom Fields Tables**: `vtiger_[modulename]cf` - Custom field values (EAV pattern)
3. **Address Tables**: `vtiger_[modulename]billads`, `vtiger_[modulename]shipads` - Billing/shipping addresses
4. **Sub-detail Tables**: `vtiger_[modulename]subdetails` - Additional module information
5. **Relationship Tables**: Tables ending with `rel` - Many-to-many relationships
6. **Picklist Tables**: Lookup tables for dropdown values
7. **History Tables**: Tables ending with `history` - Audit trail for status changes

---

## Core Business Modules

"@

# Add each core module
foreach ($module in $coreModules.Keys | Sort-Object) {
    $tables = $coreModules[$module]
    $output += @"

### $module

**Primary Tables:**
"@
    
    foreach ($table in $tables) {
        # Get table structure from XML
        $tableNode = $xml.schema.table | Where-Object { $_.name -eq $table -and $_.alter -ne 'true' } | Select-Object -First 1
        
        if ($tableNode) {
            $fields = $tableNode.field
            $fieldCount = ($fields | Measure-Object).Count
            
            $output += @"

- **$table** ($fieldCount fields)
"@
            
            # Add key fields
            $keyFields = $fields | Where-Object { $_.key -ne $null } | Select-Object -ExpandProperty name
            if ($keyFields) {
                $output += "
  - Primary Key: ``$($keyFields -join ', ')``"
            }
            
            # Add important fields (first 10)
            $importantFields = $fields | Select-Object -First 10 -ExpandProperty name
            if ($importantFields) {
                $output += "
  - Key Fields: ``$($importantFields -join '``, ``')``"
            }
        }
    }
    
    $output += "

"
}

$output += @"

---

## System & Configuration Tables

These tables manage system configuration, user permissions, roles, profiles, and metadata.

**Total Tables:** $($systemTables.Count)

"@

foreach ($table in $systemTables | Sort-Object) {
    $tableNode = $xml.schema.table | Where-Object { $_.name -eq $table -and $_.alter -ne 'true' } | Select-Object -First 1
    
    if ($tableNode) {
        $fields = $tableNode.field
        $fieldCount = ($fields | Measure-Object).Count
        $output += "- **$table** ($fieldCount fields)
"
    }
}

$output += @"

---

## Workflow Tables

These tables manage automated workflows, tasks, and business process automation.

**Total Tables:** $($workflowTables.Count)

"@

foreach ($table in $workflowTables | Sort-Object) {
    $tableNode = $xml.schema.table | Where-Object { $_.name -eq $table -and $_.alter -ne 'true' } | Select-Object -First 1
    
    if ($tableNode) {
        $fields = $tableNode.field
        $fieldCount = ($fields | Measure-Object).Count
        $output += "- **$table** ($fieldCount fields)
"
    }
}

$output += @"

---

## Inventory Management Tables

These tables are shared across inventory modules (Quotes, Sales Orders, Purchase Orders, Invoices).

**Total Tables:** $($inventoryTables.Count)

"@

foreach ($table in $inventoryTables | Sort-Object) {
    $tableNode = $xml.schema.table | Where-Object { $_.name -eq $table -and $_.alter -ne 'true' } | Select-Object -First 1
    
    if ($tableNode) {
        $fields = $tableNode.field
        $fieldCount = ($fields | Measure-Object).Count
        $output += "- **$table** ($fieldCount fields)
"
    }
}

$output += @"

---

## Picklist Tables

These tables store dropdown/picklist values for various modules.

**Total Tables:** $($picklistTables.Count)

"@

foreach ($table in $picklistTables | Sort-Object) {
    $output += "- **$table**
"
}

$output += @"

---

## Relationship Tables

These tables manage many-to-many relationships between different modules.

**Total Tables:** $($relationshipTables.Count)

"@

foreach ($table in $relationshipTables | Sort-Object) {
    $tableNode = $xml.schema.table | Where-Object { $_.name -eq $table -and $_.alter -ne 'true' } | Select-Object -First 1
    
    if ($tableNode) {
        $fields = $tableNode.field
        $fieldCount = ($fields | Measure-Object).Count
        $output += "- **$table** ($fieldCount fields)
"
    }
}

$output += @"

---

## All Database Tables

Complete list of all $($allTables.Count) database tables in alphabetical order:

"@

foreach ($table in $allTables) {
    $output += "- $table
"
}

$output += @"

---

## Module Directory Structure

The following modules exist in the filesystem:

"@

# Get module directories
$moduleDirs = Get-ChildItem -Path $modulesDir -Directory | Sort-Object Name

foreach ($dir in $moduleDirs) {
    $output += "- **$($dir.Name)**
"
}

$output += @"

---

## Summary Statistics

- **Total Database Tables:** $($allTables.Count)
- **Core Business Modules:** $($coreModules.Keys.Count)
- **System/Configuration Tables:** $($systemTables.Count)
- **Workflow Tables:** $($workflowTables.Count)
- **Inventory Tables:** $($inventoryTables.Count)
- **Picklist Tables:** $($picklistTables.Count)
- **Relationship Tables:** $($relationshipTables.Count)
- **Module Directories:** $($moduleDirs.Count)

---

*This document was automatically generated from the database schema and module structure.*
"@

# Write output to file
$output | Out-File -FilePath $outputFile -Encoding UTF8

Write-Host "Analysis complete! Output written to: $outputFile"
Write-Host "Total tables analyzed: $($allTables.Count)"
