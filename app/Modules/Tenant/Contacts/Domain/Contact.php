<?php

namespace App\Modules\Tenant\Contacts\Domain;

use App\Modules\Tenant\Contacts\Domain\ValueObjects\FullName;
use App\Modules\Tenant\Contacts\Domain\ValueObjects\EmailAddress;
use App\Modules\Tenant\Contacts\Domain\ValueObjects\PhoneNumber;
use App\Modules\Tenant\Contacts\Domain\ValueObjects\Address;
use App\Modules\Tenant\Contacts\Domain\ValueObjects\PortalCredentials;

/**
 * Contact Entity
 * 
 * Represents an individual person in the CRM system.
 * Maps to vtiger tables: vtiger_crmentity, vtiger_contactdetails, vtiger_contactsubdetails,
 * vtiger_contactaddress, vtiger_contactscf, vtiger_portalinfo, vtiger_customerdetails
 * 
 * Business Rules:
 * - lastname is MANDATORY
 * - Must have an owner (smownerid)
 * - Can be linked to at most ONE Account
 * - Can have at most ONE image
 * - Portal username MUST equal email address
 * - Portal can only be enabled if email exists
 */
class Contact
{
    private int $id;
    private string $contactNo;
    private FullName $fullName;
    private ?EmailAddress $email;
    private ?int $accountId;
    private ?PhoneNumber $officePhone;
    private ?PhoneNumber $mobilePhone;
    private ?PhoneNumber $homePhone;
    private ?PhoneNumber $fax;
    private ?string $title;
    private ?string $department;
    private ?string $reportsTo;
    private ?Address $mailingAddress;
    private ?Address $alternateAddress;
    private ?PortalCredentials $portalCredentials;
    private ?string $imageName;
    private bool $emailOptOut;
    private bool $doNotCall;
    private int $ownerId;
    private int $creatorId;
    private \DateTimeImmutable $createdTime;
    private \DateTimeImmutable $modifiedTime;
    private int $modifiedBy;
    private bool $deleted;
    private ?string $description;

    // Extended fields from vtiger_contactsubdetails
    private ?string $assistant;
    private ?PhoneNumber $assistantPhone;
    private ?\DateTimeImmutable $birthday;
    private ?string $leadSource;

    // Customer portal fields from vtiger_customerdetails
    private bool $portalEnabled;
    private ?\DateTimeImmutable $supportStartDate;
    private ?\DateTimeImmutable $supportEndDate;

    // Custom fields (EAV - vtiger_contactscf)
    private array $customFields = [];

    private function __construct(
        int $id,
        string $contactNo,
        FullName $fullName,
        int $ownerId,
        int $creatorId
    ) {
        $this->id = $id;
        $this->contactNo = $contactNo;
        $this->fullName = $fullName;
        $this->ownerId = $ownerId;
        $this->creatorId = $creatorId;
        $this->createdTime = new \DateTimeImmutable();
        $this->modifiedTime = new \DateTimeImmutable();
        $this->modifiedBy = $creatorId;
        $this->deleted = false;

        // Initialize optional fields
        $this->email = null;
        $this->accountId = null;
        $this->officePhone = null;
        $this->mobilePhone = null;
        $this->homePhone = null;
        $this->fax = null;
        $this->title = null;
        $this->department = null;
        $this->reportsTo = null;
        $this->mailingAddress = null;
        $this->alternateAddress = null;
        $this->portalCredentials = null;
        $this->imageName = null;
        $this->emailOptOut = false;
        $this->doNotCall = false;
        $this->assistant = null;
        $this->assistantPhone = null;
        $this->birthday = null;
        $this->leadSource = null;
        $this->portalEnabled = false;
        $this->supportStartDate = null;
        $this->supportEndDate = null;
        $this->description = null;
    }


    /**
     * Create a new Contact
     * 
     * Business Rule: lastname is mandatory
     * Database: Inserts into vtiger_crmentity + vtiger_contactdetails + 4 related tables
     */
    public static function create(
        int $id,
        string $contactNo,
        FullName $fullName,
        int $ownerId,
        int $creatorId
    ): self {
        return new self($id, $contactNo, $fullName, $ownerId, $creatorId);
    }

    /**
     * Update contact information
     * 
     * Database: Updates vtiger_crmentity.modifiedtime and modifiedby
     */
    public function update(FullName $fullName, int $modifiedBy): void
    {
        $this->fullName = $fullName;
        $this->modifiedTime = new \DateTimeImmutable();
        $this->modifiedBy = $modifiedBy;
    }

    /**
     * Soft delete the contact
     * 
     * Business Rule: Sets deleted flag, triggers relationship cleanup
     * Database: Updates vtiger_crmentity.deleted = 1
     */
    public function delete(): void
    {
        $this->deleted = true;
    }

    /**
     * Link contact to an account
     * 
     * Business Rule: Contact can be linked to at most ONE Account
     * Database: Updates vtiger_contactdetails.accountid
     */
    public function linkToAccount(int $accountId): void
    {
        $this->accountId = $accountId;
    }

    /**
     * Unlink contact from account
     * 
     * Database: Sets vtiger_contactdetails.accountid = NULL
     */
    public function unlinkFromAccount(): void
    {
        $this->accountId = null;
    }

    /**
     * Enable portal access
     * 
     * Business Rule: Portal can only be enabled if email exists
     * Business Rule: Portal username MUST equal email address
     * Database: Inserts/Updates vtiger_portalinfo, updates vtiger_customerdetails
     */
    public function enablePortalAccess(PortalCredentials $credentials): void
    {
        if ($this->email === null) {
            throw new \DomainException('Cannot enable portal access without email address');
        }

        $this->portalCredentials = $credentials;
        $this->portalEnabled = true;
    }

    /**
     * Disable portal access
     * 
     * Database: Updates vtiger_portalinfo.isactive = 0, vtiger_customerdetails.portal = 0
     */
    public function disablePortalAccess(): void
    {
        $this->portalEnabled = false;
        if ($this->portalCredentials) {
            $this->portalCredentials->deactivate();
        }
    }

    /**
     * Upload contact image
     * 
     * Business Rule: Contact can have at most ONE image (old image deleted)
     * Database: Updates vtiger_contactdetails.imagename, manages vtiger_attachments
     */
    public function uploadImage(string $imageName): void
    {
        $this->imageName = $imageName;
    }

    // Getters
    public function getId(): int
    {
        return $this->id;
    }
    public function getContactNo(): string
    {
        return $this->contactNo;
    }
    public function getFullName(): FullName
    {
        return $this->fullName;
    }
    public function getEmail(): ?EmailAddress
    {
        return $this->email;
    }
    public function getAccountId(): ?int
    {
        return $this->accountId;
    }
    public function getOwnerId(): int
    {
        return $this->ownerId;
    }
    public function isDeleted(): bool
    {
        return $this->deleted;
    }
    public function isPortalEnabled(): bool
    {
        return $this->portalEnabled;
    }

    public function getOfficePhone(): ?PhoneNumber
    {
        return $this->officePhone;
    }

    public function getMobilePhone(): ?PhoneNumber
    {
        return $this->mobilePhone;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getDepartment(): ?string
    {
        return $this->department;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    // Setters for optional fields

    public function setEmail(?EmailAddress $email): void
    {
        $this->email = $email;
    }
    public function setOfficePhone(?PhoneNumber $phone): void
    {
        $this->officePhone = $phone;
    }
    public function setMobilePhone(?PhoneNumber $phone): void
    {
        $this->mobilePhone = $phone;
    }
    public function setHomePhone(?PhoneNumber $phone): void
    {
        $this->homePhone = $phone;
    }
    public function setFax(?PhoneNumber $fax): void
    {
        $this->fax = $fax;
    }
    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }
    public function setDepartment(?string $department): void
    {
        $this->department = $department;
    }
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }
    public function setMailingAddress(?Address $address): void
    {
        $this->mailingAddress = $address;
    }
    public function setAlternateAddress(?Address $address): void
    {
        $this->alternateAddress = $address;
    }
    public function setEmailOptOut(bool $optOut): void
    {
        $this->emailOptOut = $optOut;
    }
    public function setDoNotCall(bool $doNotCall): void
    {
        $this->doNotCall = $doNotCall;
    }
    public function setAssistant(?string $assistant): void
    {
        $this->assistant = $assistant;
    }
    public function setAssistantPhone(?PhoneNumber $phone): void
    {
        $this->assistantPhone = $phone;
    }
    public function setBirthday(?\DateTimeImmutable $birthday): void
    {
        $this->birthday = $birthday;
    }
    public function setLeadSource(?string $leadSource): void
    {
        $this->leadSource = $leadSource;
    }
    public function setSupportDates(?\DateTimeImmutable $start, ?\DateTimeImmutable $end): void
    {
        $this->supportStartDate = $start;
        $this->supportEndDate = $end;
    }

    /**
     * Set custom field value (EAV pattern)
     * 
     * Database: Stored in vtiger_contactscf
     */
    public function setCustomField(string $fieldName, mixed $value): void
    {
        $this->customFields[$fieldName] = $value;
    }

    public function getCustomField(string $fieldName): mixed
    {
        return $this->customFields[$fieldName] ?? null;
    }

    public function getAllCustomFields(): array
    {
        return $this->customFields;
    }

    public function setCustomFields(array $fields): void
    {
        $this->customFields = $fields;
    }
}
