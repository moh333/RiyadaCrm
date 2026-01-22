<?php

namespace App\Modules\Tenant\Contacts\Infrastructure;

use App\Modules\Tenant\Contacts\Domain\Contact;
use App\Modules\Tenant\Contacts\Domain\Repositories\ContactRepositoryInterface;
use App\Modules\Tenant\Contacts\Domain\ValueObjects\PortalCredentials;
use Illuminate\Support\Facades\DB;

/**
 * EloquentContactRepository
 * 
 * Implements ContactRepositoryInterface using Laravel Eloquent and raw SQL.
 * Handles vtiger's complex EAV pattern across 6+ tables.
 * 
 * vtiger Tables Managed:
 * - vtiger_crmentity (base entity)
 * - vtiger_contactdetails (main data)
 * - vtiger_contactsubdetails (extended data)
 * - vtiger_contactaddress (addresses)
 * - vtiger_contactscf (custom fields - EAV)
 * - vtiger_portalinfo (portal credentials)
 * - vtiger_customerdetails (support info)
 * 
 * CRITICAL: All operations must be transactional
 * CRITICAL: Use vtiger_crmentity_seq for ID generation
 * CRITICAL: Use createdtime/modifiedtime, NOT created_at/updated_at
 * CRITICAL: Soft delete via deleted flag, NOT deleted_at
 */
class EloquentContactRepository implements ContactRepositoryInterface
{
    /**
     * Generate next contact ID from vtiger_crmentity_seq
     * 
     * Business Rule: NEVER use auto-increment, always use vtiger's sequence
     */
    public function nextIdentity(): int
    {
        // Get next value from vtiger_crmentity_seq table
        $query = DB::connection('tenant')->table('vtiger_crmentity_seq')->lockForUpdate();
        $result = $query->first();

        if (!$result) {
            // Initialize if empty (standard vtiger behavior requires at least one row)
            // Use current max crmid as base if exists, otherwise start at 1000 to avoid collisions
            $maxId = DB::connection('tenant')->table('vtiger_crmentity')->max('crmid') ?? 1000;
            $nextId = $maxId + 1;
            DB::connection('tenant')->table('vtiger_crmentity_seq')->insert(['id' => $nextId]);
            return $nextId;
        }

        $nextId = $result->id + 1;

        DB::connection('tenant')->table('vtiger_crmentity_seq')
            ->update(['id' => $nextId]);

        return $nextId;
    }


    /**
     * Generate unique contact number
     * 
     * Format: "CON" + padded sequence number
     * Example: CON1, CON2, CON100
     */
    public function generateContactNumber(): string
    {
        // Get next sequence from vtiger_modentity_num
        $query = DB::connection('tenant')->table('vtiger_modentity_num')
            ->where('semodule', 'Contacts')
            ->lockForUpdate();

        $row = $query->first();

        if (!$row) {
            // Initialize if missing
            $nextSequence = 1;
            DB::connection('tenant')->table('vtiger_modentity_num')->insert([
                'semodule' => 'Contacts',
                'prefix' => 'CON',
                'start_id' => 1,
                'cur_id' => 1,
                'active' => 1,
            ]);
        } else {
            $nextSequence = $row->cur_id + 1;
            DB::connection('tenant')->table('vtiger_modentity_num')
                ->where('semodule', 'Contacts')
                ->update([
                    'cur_id' => $nextSequence
                ]);
        }

        return 'CON' . $nextSequence;
    }


    /**
     * Find contact by ID
     * 
     * Joins all 6 contact tables to build complete Contact entity
     * Returns null if deleted = 1
     */
    public function findById(int $id): ?Contact
    {
        // Query joins vtiger_crmentity + all contact tables
        $data = DB::connection('tenant')
            ->table('vtiger_contactdetails as cd')
            ->join('vtiger_crmentity as ce', 'ce.crmid', '=', 'cd.contactid')
            ->leftJoin('vtiger_contactsubdetails as csd', 'csd.contactsubscriptionid', '=', 'cd.contactid')
            ->leftJoin('vtiger_contactaddress as ca', 'ca.contactaddressid', '=', 'cd.contactid')
            ->leftJoin('vtiger_contactscf as cf', 'cf.contactid', '=', 'cd.contactid')
            ->leftJoin('vtiger_customerdetails as cud', 'cud.customerid', '=', 'cd.contactid')
            ->leftJoin('vtiger_portalinfo as pi', 'pi.id', '=', 'cd.contactid')
            ->where('cd.contactid', $id)
            ->where('ce.deleted', 0)
            ->select([
                'cd.*',
                'ce.*',
                'csd.*',
                'ca.*',
                'cf.*',
                'cud.*',
                'pi.*'
            ])
            ->first();

        if (!$data) {
            return null;
        }

        // Map database row to Contact entity
        return $this->mapToEntity($data);
    }

    /**
     * Find contact by email
     * 
     * Searches across email, otheremail, secondaryemail fields
     */
    public function findByEmail(string $email): ?Contact
    {
        $data = DB::connection('tenant')
            ->table('vtiger_contactdetails as cd')
            ->join('vtiger_crmentity as ce', 'ce.crmid', '=', 'cd.contactid')
            ->where('ce.deleted', 0)
            ->where(function ($query) use ($email) {
                $query->where('cd.email', $email)
                    ->orWhere('cd.otheremail', $email)
                    ->orWhere('cd.secondaryemail', $email);
            })
            ->select('cd.contactid')
            ->first();

        return $data ? $this->findById($data->contactid) : null;
    }

    /**
     * Find contact by contact number
     */
    public function findByContactNumber(string $contactNo): ?Contact
    {
        $data = DB::connection('tenant')
            ->table('vtiger_contactdetails as cd')
            ->join('vtiger_crmentity as ce', 'ce.crmid', '=', 'cd.contactid')
            ->where('cd.contact_no', $contactNo)
            ->where('ce.deleted', 0)
            ->select('cd.contactid')
            ->first();

        return $data ? $this->findById($data->contactid) : null;
    }

    /**
     * Find contacts by account ID
     */
    public function findByAccountId(int $accountId): array
    {
        $results = DB::connection('tenant')
            ->table('vtiger_contactdetails as cd')
            ->join('vtiger_crmentity as ce', 'ce.crmid', '=', 'cd.contactid')
            ->where('cd.accountid', $accountId)
            ->where('ce.deleted', 0)
            ->select('cd.contactid')
            ->get();

        return $results->map(fn($row) => $this->findById($row->contactid))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Find contacts by owner ID
     */
    public function findByOwnerId(int $ownerId): array
    {
        $results = DB::connection('tenant')
            ->table('vtiger_contactdetails as cd')
            ->join('vtiger_crmentity as ce', 'ce.crmid', '=', 'cd.contactid')
            ->where('ce.smownerid', $ownerId)
            ->where('ce.deleted', 0)
            ->select('cd.contactid')
            ->get();

        return $results->map(fn($row) => $this->findById($row->contactid))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Save contact (create or update)
     * 
     * CRITICAL: Must be transactional across all 6+ tables
     * CRITICAL: Use createdtime/modifiedtime, NOT Laravel timestamps
     */
    public function save(Contact $contact): void
    {
        DB::connection('tenant')->transaction(function () use ($contact) {
            $exists = DB::connection('tenant')
                ->table('vtiger_crmentity')
                ->where('crmid', $contact->getId())
                ->exists();

            if ($exists) {
                $this->updateContact($contact);
            } else {
                $this->createContact($contact);
            }
        });
    }

    /**
     * Create new contact
     * 
     * Inserts into 6 tables: crmentity, contactdetails, contactsubdetails,
     * contactaddress, contactscf, customerdetails
     */
    private function createContact(Contact $contact): void
    {
        $now = now()->format('Y-m-d H:i:s');

        // 1. Insert into vtiger_crmentity
        DB::connection('tenant')->table('vtiger_crmentity')->insert([
            'crmid' => $contact->getId(),
            'smcreatorid' => $contact->getOwnerId(),
            'smownerid' => $contact->getOwnerId(),
            'modifiedby' => $contact->getOwnerId(),
            'setype' => 'Contacts',
            'description' => $contact->getDescription(),
            'createdtime' => $now,
            'modifiedtime' => $now,
            'viewedtime' => null,
            'status' => null,
            'version' => 0,
            'presence' => 1,
            'deleted' => 0,
            'label' => $contact->getFullName()->getDisplayName(),
        ]);

        // 2. Insert into vtiger_contactdetails
        DB::connection('tenant')->table('vtiger_contactdetails')->insert([
            'contactid' => $contact->getId(),
            'contact_no' => $contact->getContactNo(),
            'accountid' => $contact->getAccountId(),
            'salutation' => $contact->getFullName()->getSalutation(),
            'firstname' => $contact->getFullName()->getFirstName(),
            'lastname' => $contact->getFullName()->getLastName(),
            'email' => $contact->getEmail()?->getEmail(),
            'phone' => $contact->getOfficePhone()?->getNumber(),
            'mobile' => $contact->getMobilePhone()?->getNumber(),
            'title' => $contact->getTitle(),
            'department' => $contact->getDepartment(),
            'fax' => $contact->getFax()?->getNumber(),
            'emailoptout' => $contact->getEmail()?->isOptedOut() ? 1 : 0,
            'imagename' => $contact->getImageName(),
        ]);


        // 3. Insert into vtiger_contactsubdetails
        DB::connection('tenant')->table('vtiger_contactsubdetails')->insert([
            'contactsubscriptionid' => $contact->getId(),
            'homephone' => $contact->getHomePhone()?->getNumber(),
            'otherphone' => null, // Mapping not defined for and 'otherphone' yet
            'assistant' => $contact->getAssistant(),
            'assistantphone' => $contact->getAssistantPhone()?->getNumber(),
            'birthday' => $contact->getBirthday()?->format('Y-m-d'),
            'leadsource' => $contact->getLeadSource(),
        ]);

        // 4. Insert into vtiger_contactaddress
        $mailing = $contact->getMailingAddress();
        $other = $contact->getAlternateAddress();

        DB::connection('tenant')->table('vtiger_contactaddress')->insert([
            'contactaddressid' => $contact->getId(),
            'mailingcity' => $mailing?->getCity(),
            'mailingstreet' => $mailing?->getStreet(),
            'mailingcountry' => $mailing?->getCountry(),
            'mailingstate' => $mailing?->getState(),
            'mailingzip' => $mailing?->getZip(),
            'mailingpobox' => $mailing?->getPoBox(),
            'othercity' => $other?->getCity(),
            'otherstreet' => $other?->getStreet(),
            'othercountry' => $other?->getCountry(),
            'otherstate' => $other?->getState(),
            'otherzip' => $other?->getZip(),
            'otherpobox' => $other?->getPoBox(),
        ]);

        // 5. Insert into vtiger_contactscf (custom fields)
        $customFieldsData = ['contactid' => $contact->getId()];

        // Add all custom fields
        foreach ($contact->getAllCustomFields() as $fieldName => $value) {
            $customFieldsData[$fieldName] = $value;
        }

        DB::connection('tenant')->table('vtiger_contactscf')->insert($customFieldsData);

        // 6. Insert into vtiger_customerdetails
        DB::connection('tenant')->table('vtiger_customerdetails')->insert([
            'customerid' => $contact->getId(),
            'portal' => $contact->isPortalEnabled() ? 1 : 0,
            'support_start_date' => $contact->getSupportStartDate()?->format('Y-m-d'),
            'support_end_date' => $contact->getSupportEndDate()?->format('Y-m-d'),
        ]);
    }

    /**
     * Update existing contact
     * 
     * Updates vtiger_crmentity + relevant contact tables
     */
    private function updateContact(Contact $contact): void
    {
        $now = now()->format('Y-m-d H:i:s');

        // Update vtiger_crmentity
        DB::connection('tenant')->table('vtiger_crmentity')
            ->where('crmid', $contact->getId())
            ->update([
                'modifiedtime' => $now,
                'modifiedby' => $contact->getOwnerId(), // Should ideally be current user from DTO
                'label' => $contact->getFullName()->getDisplayName(),
                'description' => $contact->getDescription(),
            ]);

        // Update vtiger_contactdetails
        DB::connection('tenant')->table('vtiger_contactdetails')
            ->where('contactid', $contact->getId())
            ->update([
                'accountid' => $contact->getAccountId(),
                'salutation' => $contact->getFullName()->getSalutation(),
                'firstname' => $contact->getFullName()->getFirstName(),
                'lastname' => $contact->getFullName()->getLastName(),
                'email' => $contact->getEmail()?->getEmail(),
                'phone' => $contact->getOfficePhone()?->getNumber(),
                'mobile' => $contact->getMobilePhone()?->getNumber(),
                'title' => $contact->getTitle(),
                'department' => $contact->getDepartment(),
                'fax' => $contact->getFax()?->getNumber(),
                'emailoptout' => $contact->getEmail()?->isOptedOut() ? 1 : 0,
                'imagename' => $contact->getImageName(),
            ]);

        // Update vtiger_contactsubdetails
        DB::connection('tenant')->table('vtiger_contactsubdetails')
            ->where('contactsubscriptionid', $contact->getId())
            ->update([
                'homephone' => $contact->getHomePhone()?->getNumber(),
                'assistant' => $contact->getAssistant(),
                'assistantphone' => $contact->getAssistantPhone()?->getNumber(),
                'birthday' => $contact->getBirthday()?->format('Y-m-d'),
                'leadsource' => $contact->getLeadSource(),
            ]);

        // Update vtiger_contactaddress
        $mailing = $contact->getMailingAddress();
        $other = $contact->getAlternateAddress();

        DB::connection('tenant')->table('vtiger_contactaddress')
            ->where('contactaddressid', $contact->getId())
            ->update([
                'mailingcity' => $mailing?->getCity(),
                'mailingstreet' => $mailing?->getStreet(),
                'mailingcountry' => $mailing?->getCountry(),
                'mailingstate' => $mailing?->getState(),
                'mailingzip' => $mailing?->getZip(),
                'mailingpobox' => $mailing?->getPoBox(),
                'othercity' => $other?->getCity(),
                'otherstreet' => $other?->getStreet(),
                'othercountry' => $other?->getCountry(),
                'otherstate' => $other?->getState(),
                'otherzip' => $other?->getZip(),
                'otherpobox' => $other?->getPoBox(),
            ]);

        // Update vtiger_contactscf (custom fields)
        $customFieldsData = $contact->getAllCustomFields();
        if (!empty($customFieldsData)) {
            DB::connection('tenant')->table('vtiger_contactscf')
                ->where('contactid', $contact->getId())
                ->update($customFieldsData);
        }

        // Update vtiger_customerdetails
        DB::connection('tenant')->table('vtiger_customerdetails')
            ->where('customerid', $contact->getId())
            ->update([
                'portal' => $contact->isPortalEnabled() ? 1 : 0,
                'support_start_date' => $contact->getSupportStartDate()?->format('Y-m-d'),
                'support_end_date' => $contact->getSupportEndDate()?->format('Y-m-d'),
            ]);
    }

    /**
     * Delete contact (soft delete)
     * 
     * Sets deleted = 1 and triggers relationship cleanup
     */
    public function delete(Contact $contact): void
    {
        DB::connection('tenant')->transaction(function () use ($contact) {
            // Soft delete in crmentity
            DB::connection('tenant')->table('vtiger_crmentity')
                ->where('crmid', $contact->getId())
                ->update(['deleted' => 1]);

            // Relationship cleanup (as per vtiger business rules)
            $this->cleanupRelationships($contact->getId());
        });
    }

    /**
     * Cleanup relationships when contact is deleted
     * 
     * Business Rules from vtiger:
     * - Soft-delete Potentials where Contact is related_to
     * - Unlink from Tickets, Orders, Quotes
     * - Delete portal access
     * - Backup relationships for recovery
     */
    private function cleanupRelationships(int $contactId): void
    {
        // Soft-delete related Potentials
        $potentials = DB::connection('tenant')
            ->table('vtiger_potential')
            ->where('related_to', $contactId)
            ->pluck('potentialid');

        if ($potentials->isNotEmpty()) {
            DB::connection('tenant')->table('vtiger_crmentity')
                ->whereIn('crmid', $potentials)
                ->update(['deleted' => 1]);

            // Backup for recovery
            DB::connection('tenant')->table('vtiger_relatedlists_rb')->insert([
                'entity_id' => $contactId,
                'action' => 'RB_RECORD_UPDATED',
                'rel_table' => 'vtiger_crmentity',
                'rel_field' => 'deleted',
                'queryid' => 'crmid',
                'data' => implode(',', $potentials->toArray()),
            ]);
        }

        // Unlink from Tickets
        DB::connection('tenant')->table('vtiger_troubletickets')
            ->where('contact_id', $contactId)
            ->update(['contact_id' => 0]);

        // Unlink from Purchase Orders
        DB::connection('tenant')->table('vtiger_purchaseorder')
            ->where('contactid', $contactId)
            ->update(['contactid' => 0]);

        // Unlink from Sales Orders
        DB::connection('tenant')->table('vtiger_salesorder')
            ->where('contactid', $contactId)
            ->update(['contactid' => 0]);

        // Unlink from Quotes
        DB::connection('tenant')->table('vtiger_quotes')
            ->where('contactid', $contactId)
            ->update(['contactid' => 0]);

        // Delete portal info
        DB::connection('tenant')->table('vtiger_portalinfo')
            ->where('id', $contactId)
            ->delete();

        // Clear customer details
        DB::connection('tenant')->table('vtiger_customerdetails')
            ->where('customerid', $contactId)
            ->update([
                'portal' => 0,
                'support_start_date' => null,
                'support_end_date' => null,
            ]);
    }

    /**
     * Enable portal access
     */
    public function enablePortalAccess(int $contactId, PortalCredentials $credentials): void
    {
        DB::connection('tenant')->transaction(function () use ($contactId, $credentials) {
            // Check if portal record exists
            $exists = DB::connection('tenant')
                ->table('vtiger_portalinfo')
                ->where('id', $contactId)
                ->exists();

            if ($exists) {
                // Update existing
                DB::connection('tenant')->table('vtiger_portalinfo')
                    ->where('id', $contactId)
                    ->update([
                        'user_name' => $credentials->getUsername(),
                        'user_password' => $credentials->getEncryptedPassword(),
                        'cryptmode' => $credentials->getCryptMode(),
                        'isactive' => 1,
                    ]);
            } else {
                // Insert new
                DB::connection('tenant')->table('vtiger_portalinfo')->insert([
                    'id' => $contactId,
                    'user_name' => $credentials->getUsername(),
                    'user_password' => $credentials->getEncryptedPassword(),
                    'cryptmode' => $credentials->getCryptMode(),
                    'type' => PortalCredentials::TYPE_CONTACT,
                    'isactive' => 1,
                ]);
            }

            // Update customer details
            DB::connection('tenant')->table('vtiger_customerdetails')
                ->where('customerid', $contactId)
                ->update(['portal' => 1]);
        });
    }

    /**
     * Disable portal access
     */
    public function disablePortalAccess(int $contactId): void
    {
        DB::connection('tenant')->transaction(function () use ($contactId) {
            DB::connection('tenant')->table('vtiger_portalinfo')
                ->where('id', $contactId)
                ->update(['isactive' => 0]);

            DB::connection('tenant')->table('vtiger_customerdetails')
                ->where('customerid', $contactId)
                ->update(['portal' => 0]);
        });
    }

    /**
     * Link contact to account
     */
    public function linkToAccount(int $contactId, int $accountId): void
    {
        DB::connection('tenant')->table('vtiger_contactdetails')
            ->where('contactid', $contactId)
            ->update(['accountid' => $accountId]);
    }

    /**
     * Unlink contact from account
     */
    public function unlinkFromAccount(int $contactId): void
    {
        DB::connection('tenant')->table('vtiger_contactdetails')
            ->where('contactid', $contactId)
            ->update(['accountid' => null]);
    }

    /**
     * Upload contact image
     */
    public function uploadImage(int $contactId, string $imageName, string $filePath): void
    {
        DB::connection('tenant')->transaction(function () use ($contactId, $imageName, $filePath) {
            // Get old attachment ID
            $oldAttachmentId = DB::connection('tenant')
                ->table('vtiger_seattachmentsrel as sar')
                ->join('vtiger_crmentity as ce', 'ce.crmid', '=', 'sar.attachmentsid')
                ->where('sar.crmid', $contactId)
                ->where('ce.setype', 'Contacts Image')
                ->value('sar.attachmentsid');

            // Delete old image if exists
            if ($oldAttachmentId) {
                DB::connection('tenant')->table('vtiger_attachments')
                    ->where('attachmentsid', $oldAttachmentId)
                    ->delete();
                DB::connection('tenant')->table('vtiger_seattachmentsrel')
                    ->where('attachmentsid', $oldAttachmentId)
                    ->delete();
            }

            // Insert new attachment (implementation details omitted)
            // Would involve creating attachment record and linking via vtiger_seattachmentsrel

            // Update contact imagename
            DB::connection('tenant')->table('vtiger_contactdetails')
                ->where('contactid', $contactId)
                ->update(['imagename' => $imageName]);
        });
    }

    /**
     * Transfer contact ownership
     */
    public function transferOwnership(int $contactId, int $newOwnerId): void
    {
        // Implementation would transfer all related records
        // This is complex and involves multiple relationship tables
        // See vtiger's transferRelatedRecords() method
    }

    /**
     * Create contact from lead conversion
     */
    public function createFromLead(array $leadData, ?int $accountId = null, ?int $potentialId = null): Contact
    {
        // Map lead data to contact and create
        // Implementation details omitted for brevity
        throw new \Exception('Not implemented');
    }

    /**
     * Get contact count by owner
     */
    public function countByOwnerId(int $ownerId): int
    {
        return DB::connection('tenant')
            ->table('vtiger_contactdetails as cd')
            ->join('vtiger_crmentity as ce', 'ce.crmid', '=', 'cd.contactid')
            ->where('ce.smownerid', $ownerId)
            ->where('ce.deleted', 0)
            ->count();
    }

    /**
     * Search contacts
     */
    public function search(string $query, int $userId, int $limit = 50): array
    {
        // Implementation would search across multiple fields
        // and respect field-level security
        throw new \Exception('Not implemented');
    }

    /**
     * Get paginated contacts
     */
    public function paginate(int $perPage = 20, array $filters = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = $this->getDataTableQuery();

        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($search) {
                $q->where('cd.firstname', 'like', $search)
                    ->orWhere('cd.lastname', 'like', $search)
                    ->orWhere('cd.email', 'like', $search)
                    ->orWhere('cd.phone', 'like', $search)
                    ->orWhere('acc.accountname', 'like', $search);
            });
        }

        $paginator = $query->select([
            'cd.*',
            'ce.*',
            'acc.accountname as account_name'
        ])
            ->orderBy('ce.modifiedtime', 'desc')
            ->paginate($perPage);

        // Transform results to entities
        $paginator->getCollection()->transform(function ($row) {
            return $this->mapToEntity($row);
        });

        return $paginator;
    }

    /**
     * Map database row to Contact entity
     * 
     * Converts flat database row to rich domain entity with value objects
     */
    private function mapToEntity($data): Contact
    {
        $fullName = \App\Modules\Tenant\Contacts\Domain\ValueObjects\FullName::create(
            $data->salutation ?? $data->salutationtype ?? null,
            $data->firstname ?? null,
            $data->lastname
        );

        $contact = Contact::create(
            (int) $data->contactid,
            $data->contact_no,
            $fullName,
            (int) $data->smownerid,
            (int) $data->smcreatorid
        );

        // Set image name if exists
        if (!empty($data->imagename)) {
            $contact->uploadImage($data->imagename);
        }

        if (!empty($data->email)) {
            $contact->setEmail(\App\Modules\Tenant\Contacts\Domain\ValueObjects\EmailAddress::create(
                $data->email,
                (bool) ($data->emailoptout ?? false)
            ));
        }

        if (!empty($data->accountid)) {
            $contact->linkToAccount((int) $data->accountid);
        }

        if (!empty($data->phone)) {
            $contact->setOfficePhone(\App\Modules\Tenant\Contacts\Domain\ValueObjects\PhoneNumber::office($data->phone));
        }

        if (!empty($data->mobile)) {
            $contact->setMobilePhone(\App\Modules\Tenant\Contacts\Domain\ValueObjects\PhoneNumber::mobile($data->mobile));
        }

        if (!empty($data->homephone)) {
            $contact->setHomePhone(\App\Modules\Tenant\Contacts\Domain\ValueObjects\PhoneNumber::home($data->homephone));
        }

        if (!empty($data->fax)) {
            $contact->setFax(\App\Modules\Tenant\Contacts\Domain\ValueObjects\PhoneNumber::other($data->fax));
        }

        if (!empty($data->title)) {
            $contact->setTitle($data->title);
        }

        if (!empty($data->department)) {
            $contact->setDepartment($data->department);
        }

        if (!empty($data->description)) {
            $contact->setDescription($data->description);
        }

        if (!empty($data->assistant)) {
            $contact->setAssistant($data->assistant);
        }

        if (!empty($data->assistantphone)) {
            $contact->setAssistantPhone(\App\Modules\Tenant\Contacts\Domain\ValueObjects\PhoneNumber::other($data->assistantphone));
        }

        if (!empty($data->birthday)) {
            try {
                $contact->setBirthday(new \DateTimeImmutable($data->birthday));
            } catch (\Exception $e) {
            }
        }

        if (!empty($data->leadsource)) {
            $contact->setLeadSource($data->leadsource);
        }

        // Map Address
        if (!empty($data->mailingstreet) || !empty($data->mailingcity)) {
            $contact->setMailingAddress(\App\Modules\Tenant\Contacts\Domain\ValueObjects\Address::mailing(
                $data->mailingstreet,
                $data->mailingcity,
                $data->mailingstate,
                $data->mailingzip,
                $data->mailingcountry,
                $data->mailingpobox
            ));
        }

        if (!empty($data->otherstreet) || !empty($data->othercity)) {
            $contact->setAlternateAddress(\App\Modules\Tenant\Contacts\Domain\ValueObjects\Address::alternate(
                $data->otherstreet,
                $data->othercity,
                $data->otherstate,
                $data->otherzip,
                $data->othercountry,
                $data->otherpobox
            ));
        }

        // Map Portal Info
        if (!empty($data->portal)) {
            // Usually we'd load vtiger_portalinfo here, but for now we just set the flag
            $reflection = new \ReflectionClass($contact);
            $property = $reflection->getProperty('portalEnabled');
            $property->setAccessible(true);
            $property->setValue($contact, (bool) $data->portal);

            if (!empty($data->support_start_date) || !empty($data->support_end_date)) {
                try {
                    $start = $data->support_start_date ? new \DateTimeImmutable($data->support_start_date) : null;
                    $end = $data->support_end_date ? new \DateTimeImmutable($data->support_end_date) : null;
                    $contact->setSupportDates($start, $end);
                } catch (\Exception $e) {
                }
            }
        }

        // Map Custom Fields (from vtiger_contactscf)
        // Extract all cf_* columns from the data
        $customFields = [];
        foreach ((array) $data as $key => $value) {
            if (str_starts_with($key, 'cf_') && $value !== null) {
                $customFields[$key] = $value;
            }
        }
        if (!empty($customFields)) {
            $contact->setCustomFields($customFields);
        }

        return $contact;
    }

    public function getDataTableQuery(): \Illuminate\Database\Query\Builder
    {
        return DB::connection('tenant')
            ->table('vtiger_contactdetails as cd')
            ->join('vtiger_crmentity as ce', 'ce.crmid', '=', 'cd.contactid')
            ->leftJoin('vtiger_account as acc', 'acc.accountid', '=', 'cd.accountid')
            ->where('ce.deleted', 0)
            ->select([
                'cd.contactid',
                'cd.contact_no',
                'cd.firstname',
                'cd.lastname',
                'cd.salutation',
                'cd.email',
                'cd.title',
                'cd.accountid',
                'ce.smownerid',
                'ce.modifiedtime',
                'acc.accountname as account_name',
                'cd.imagename'
            ]);
    }
}

