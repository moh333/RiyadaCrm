<?php

namespace App\Modules\Tenant\Contacts\Domain\Repositories;

use App\Modules\Tenant\Contacts\Domain\Contact;
use App\Modules\Tenant\Contacts\Domain\ValueObjects\PortalCredentials;

/**
 * ContactRepositoryInterface
 * 
 * Defines the contract for Contact persistence.
 * Implementation must handle vtiger's EAV pattern across 6+ tables.
 * 
 * vtiger Tables:
 * - vtiger_crmentity (base entity table)
 * - vtiger_contactdetails (main contact data)
 * - vtiger_contactsubdetails (extended contact data)
 * - vtiger_contactaddress (mailing and alternate addresses)
 * - vtiger_contactscf (custom fields - EAV)
 * - vtiger_portalinfo (portal credentials)
 * - vtiger_customerdetails (support contract info)
 */
interface ContactRepositoryInterface
{
    /**
     * Generate next contact ID from vtiger_crmentity_seq
     * 
     * Business Rule: Must use vtiger's sequence, NOT auto-increment
     */
    public function nextIdentity(): int;

    /**
     * Generate unique contact number
     * 
     * Format: "CON" + sequence number
     */
    public function generateContactNumber(): string;

    /**
     * Find contact by ID
     * 
     * Database: Joins vtiger_crmentity + all 6 contact tables
     * Returns null if not found or deleted = 1
     */
    public function findById(int $id): ?Contact;

    /**
     * Find contact by email address
     * 
     * Searches across email, otheremail, secondaryemail fields
     */
    public function findByEmail(string $email): ?Contact;

    /**
     * Find contact by contact number
     */
    public function findByContactNumber(string $contactNo): ?Contact;

    /**
     * Find contacts by account ID
     * 
     * Returns all contacts linked to an account
     */
    public function findByAccountId(int $accountId): array;

    /**
     * Find contacts by owner ID
     * 
     * Database: Filters by vtiger_crmentity.smownerid and deleted = 0
     */
    public function findByOwnerId(int $ownerId): array;

    /**
     * Save contact (create or update)
     * 
     * Database Side-Effects (Create):
     * - Insert into vtiger_crmentity
     * - Insert into vtiger_contactdetails
     * - Insert into vtiger_contactsubdetails
     * - Insert into vtiger_contactaddress
     * - Insert into vtiger_contactscf
     * - Insert into vtiger_customerdetails
     * 
     * Database Side-Effects (Update):
     * - Update vtiger_crmentity (modifiedtime, modifiedby, label)
     * - Update vtiger_contactdetails
     * - Update vtiger_contactsubdetails (if changed)
     * - Update vtiger_contactaddress (if changed)
     * - Update vtiger_contactscf (if changed)
     * - Update vtiger_customerdetails (if changed)
     * 
     * Must be transactional across all tables
     */
    public function save(Contact $contact): void;

    /**
     * Delete contact (soft delete)
     * 
     * Business Rule: Sets deleted = 1, triggers relationship cleanup
     * 
     * Database Side-Effects:
     * - Update vtiger_crmentity (set deleted = 1)
     * - Soft-delete related Potentials (where Contact is related_to)
     * - Unlink from Tickets, Orders, Quotes (set contact_id/contactid = 0)
     * - Delete from vtiger_portalinfo
     * - Update vtiger_customerdetails (clear portal and support dates)
     * - Insert backup records into vtiger_relatedlists_rb
     */
    public function delete(Contact $contact): void;

    /**
     * Enable portal access for contact
     * 
     * Business Rule: Portal username = contact's email
     * 
     * Database Side-Effects:
     * - Insert into vtiger_portalinfo (if new) OR Update (if exists)
     * - Update vtiger_customerdetails (set portal = 1)
     */
    public function enablePortalAccess(int $contactId, PortalCredentials $credentials): void;

    /**
     * Disable portal access for contact
     * 
     * Business Rule: Record preserved, isactive set to 0
     * 
     * Database Side-Effects:
     * - Update vtiger_portalinfo (set isactive = 0)
     * - Update vtiger_customerdetails (set portal = 0)
     */
    public function disablePortalAccess(int $contactId): void;

    /**
     * Link contact to account
     * 
     * Business Rule: Contact can be linked to at most ONE Account
     * 
     * Database: Updates vtiger_contactdetails.accountid
     */
    public function linkToAccount(int $contactId, int $accountId): void;

    /**
     * Unlink contact from account
     * 
     * Database: Sets vtiger_contactdetails.accountid = NULL
     */
    public function unlinkFromAccount(int $contactId): void;

    /**
     * Upload contact image
     * 
     * Business Rule: Only one image per contact (old image deleted)
     * 
     * Database Side-Effects:
     * - Update vtiger_contactdetails.imagename
     * - Insert into vtiger_attachments + vtiger_seattachmentsrel
     * - Delete old image if exists (where setype = 'Contacts Image')
     */
    public function uploadImage(int $contactId, string $imageName, string $filePath): void;

    /**
     * Transfer contact ownership
     * 
     * Business Rule: All related records move to new owner
     * 
     * Database Side-Effects:
     * - Update vtiger_crmentity.smownerid
     * - Transfer relationships for: Potentials, Activities, Emails, Tickets, Quotes, 
     *   POs, SOs, Products, Documents, Campaigns, Invoices, Service Contracts, Projects, Assets, Vendors
     * - Duplicate prevention during transfer
     */
    public function transferOwnership(int $contactId, int $newOwnerId): void;

    /**
     * Create contact from lead conversion
     * 
     * Field Mapping (Lead → Contact):
     * - firstname → firstname
     * - lastname → lastname
     * - salutation → salutation
     * - email → email
     * - phone → phone (from leadaddress)
     * - mobile → mobile (from leadaddress)
     * - leadsource → leadsource (in contactsubdetails)
     * - Address fields → mailing address
     * 
     * Database: Creates contact with lead data, links to Account/Potential if created
     */
    public function createFromLead(array $leadData, ?int $accountId = null, ?int $potentialId = null): Contact;

    /**
     * Get contact count by owner
     * 
     * Used for dashboard statistics
     */
    public function countByOwnerId(int $ownerId): int;

    /**
     * Search contacts
     * 
     * Searches across: firstname, lastname, email, phone, account name
     * Respects field-level security and sharing rules
     */
    public function search(string $query, int $userId, int $limit = 50): array;

    /**
     * Get paginated contacts
     */
    public function paginate(int $perPage = 20, array $filters = []): \Illuminate\Pagination\LengthAwarePaginator;
}

