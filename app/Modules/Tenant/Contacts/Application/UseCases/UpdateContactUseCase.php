<?php

namespace App\Modules\Tenant\Contacts\Application\UseCases;

use App\Modules\Tenant\Contacts\Domain\Contact;
use App\Modules\Tenant\Contacts\Domain\Repositories\ContactRepositoryInterface;
use App\Modules\Tenant\Contacts\Domain\ValueObjects\FullName;
use App\Modules\Tenant\Contacts\Domain\Events\ContactUpdated;

/**
 * UpdateContactUseCase
 * 
 * Updates an existing contact.
 * 
 * Business Rules:
 * - Contact must exist and not be deleted
 * - lastname is MANDATORY
 * - Updates modifiedtime and modifiedby
 * 
 * Database Side-Effects:
 * - Updates vtiger_crmentity (modifiedtime, modifiedby, label)
 * - Updates vtiger_contactdetails and related tables
 * 
 * Emits: ContactUpdated event
 */
class UpdateContactUseCase
{
    private ContactRepositoryInterface $contactRepository;

    public function __construct(ContactRepositoryInterface $contactRepository)
    {
        $this->contactRepository = $contactRepository;
    }

    /**
     * Execute the use case
     * 
     * @param int $contactId
     * @param UpdateContactDTO $dto
     * @return Contact
     * @throws \DomainException if contact not found
     */
    public function execute(int $contactId, UpdateContactDTO $dto): Contact
    {
        // Find existing contact
        $contact = $this->contactRepository->findById($contactId);

        if (!$contact) {
            throw new \DomainException("Contact with ID {$contactId} not found");
        }

        // Track changed fields
        $changedFields = [];

        // Update full name if changed
        $fullName = FullName::create(
            $dto->salutation,
            $dto->firstName,
            $dto->lastName
        );

        if (!$contact->getFullName()->equals($fullName)) {
            $contact->update($fullName, $dto->modifiedBy);
            $changedFields[] = 'fullName';
        }

        // Update other fields
        if ($dto->email) {
            $contact->setEmail(\App\Modules\Tenant\Contacts\Domain\ValueObjects\EmailAddress::create($dto->email));
            $changedFields[] = 'email';
        }
        if ($dto->accountId !== $contact->getAccountId()) {
            if ($dto->accountId) {
                $contact->linkToAccount($dto->accountId);
            } else {
                $contact->unlinkFromAccount();
            }
            $changedFields[] = 'accountId';
        }
        if ($dto->phone) {
            $contact->setOfficePhone(\App\Modules\Tenant\Contacts\Domain\ValueObjects\PhoneNumber::office($dto->phone));
            $changedFields[] = 'phone';
        }
        if ($dto->mobile) {
            $contact->setMobilePhone(\App\Modules\Tenant\Contacts\Domain\ValueObjects\PhoneNumber::mobile($dto->mobile));
            $changedFields[] = 'mobile';
        }
        if ($dto->homePhone) {
            $contact->setHomePhone(\App\Modules\Tenant\Contacts\Domain\ValueObjects\PhoneNumber::home($dto->homePhone));
            $changedFields[] = 'homephone';
        }
        if ($dto->fax) {
            $contact->setFax(\App\Modules\Tenant\Contacts\Domain\ValueObjects\PhoneNumber::other($dto->fax));
            $changedFields[] = 'fax';
        }
        if ($dto->title !== $contact->getTitle()) {
            $contact->setTitle($dto->title);
            $changedFields[] = 'title';
        }
        if ($dto->department !== $contact->getDepartment()) {
            $contact->setDepartment($dto->department);
            $changedFields[] = 'department';
        }
        if ($dto->description !== $contact->getDescription()) {
            $contact->setDescription($dto->description);
            $changedFields[] = 'description';
        }
        if ($dto->assistant) {
            $contact->setAssistant($dto->assistant);
            $changedFields[] = 'assistant';
        }
        if ($dto->assistantPhone) {
            $contact->setAssistantPhone(\App\Modules\Tenant\Contacts\Domain\ValueObjects\PhoneNumber::other($dto->assistantPhone));
            $changedFields[] = 'assistantphone';
        }
        if ($dto->birthday) {
            try {
                $contact->setBirthday(new \DateTimeImmutable($dto->birthday));
                $changedFields[] = 'birthday';
            } catch (\Exception $e) {
            }
        }
        if ($dto->leadSource) {
            $contact->setLeadSource($dto->leadSource);
            $changedFields[] = 'leadsource';
        }

        // Address mapping
        if ($dto->mailingStreet || $dto->mailingCity) {
            $contact->setMailingAddress(\App\Modules\Tenant\Contacts\Domain\ValueObjects\Address::mailing(
                $dto->mailingStreet,
                $dto->mailingCity,
                $dto->mailingState,
                $dto->mailingZip,
                $dto->mailingCountry,
                $dto->mailingPoBox
            ));
            $changedFields[] = 'mailing_address';
        }

        if ($dto->otherStreet || $dto->otherCity) {
            $contact->setAlternateAddress(\App\Modules\Tenant\Contacts\Domain\ValueObjects\Address::alternate(
                $dto->otherStreet,
                $dto->otherCity,
                $dto->otherState,
                $dto->otherZip,
                $dto->otherCountry,
                $dto->otherPoBox
            ));
            $changedFields[] = 'other_address';
        }

        // Update custom fields (even if empty, to allow clearing)
        if (isset($dto->customFields)) {
            foreach ($dto->customFields as $name => $value) {
                $contact->setCustomField($name, $value);
            }
            $changedFields[] = 'customFields';
        }

        // Update standard image if provided
        if ($dto->image) {
            $contact->uploadImage($dto->image);
            $changedFields[] = 'image';
        }


        // Save contact
        $this->contactRepository->save($contact);

        // Emit domain event
        event(new ContactUpdated($contact, $changedFields));

        return $contact;
    }
}

/**
 * UpdateContactDTO
 * 
 * Data Transfer Object for updating a contact
 */
class UpdateContactDTO
{
    public ?string $salutation;
    public ?string $firstName;
    public string $lastName; // MANDATORY
    public ?string $email;
    public ?int $accountId;
    public int $modifiedBy; // MANDATORY

    public ?string $phone;
    public ?string $mobile;
    public ?string $homePhone;
    public ?string $fax;
    public ?string $title;
    public ?string $department;
    public ?string $description;
    public ?string $assistant;
    public ?string $assistantPhone;
    public ?string $birthday;
    public ?string $leadSource;
    public ?string $image;
    public array $customFields = [];

    // Address fields
    public ?string $mailingStreet;
    public ?string $mailingCity;
    public ?string $mailingState;
    public ?string $mailingZip;
    public ?string $mailingCountry;
    public ?string $mailingPoBox;

    public ?string $otherStreet;
    public ?string $otherCity;
    public ?string $otherState;
    public ?string $otherZip;
    public ?string $otherCountry;
    public ?string $otherPoBox;


    public function __construct(array $data)
    {
        $this->salutation = $data['salutation'] ?? null;
        $this->firstName = $data['firstName'] ?? null;
        $this->lastName = $data['lastName'];
        $this->email = $data['email'] ?? null;
        $this->accountId = $data['accountId'] ?? null;
        $this->modifiedBy = $data['modifiedBy'];
        $this->phone = $data['phone'] ?? null;
        $this->mobile = $data['mobile'] ?? null;
        $this->homePhone = $data['homephone'] ?? $data['homePhone'] ?? null;
        $this->fax = $data['fax'] ?? null;
        $this->title = $data['title'] ?? null;
        $this->department = $data['department'] ?? null;
        $this->description = $data['description'] ?? null;
        $this->assistant = $data['assistant'] ?? null;
        $this->assistantPhone = $data['assistantphone'] ?? null;
        $this->birthday = $data['birthday'] ?? null;
        $this->leadSource = $data['leadsource'] ?? null;

        $this->mailingStreet = $data['mailingstreet'] ?? null;
        $this->mailingCity = $data['mailingcity'] ?? null;
        $this->mailingState = $data['mailingstate'] ?? null;
        $this->mailingZip = $data['mailingzip'] ?? null;
        $this->mailingCountry = $data['mailingcountry'] ?? null;
        $this->mailingPoBox = $data['mailingpobox'] ?? null;

        $this->otherStreet = $data['otherstreet'] ?? null;
        $this->otherCity = $data['othercity'] ?? null;
        $this->otherState = $data['otherstate'] ?? null;
        $this->otherZip = $data['otherzip'] ?? null;
        $this->otherCountry = $data['othercountry'] ?? null;
        $this->otherPoBox = $data['otherpobox'] ?? null;

        $this->image = $data['image'] ?? null;
        $this->customFields = $data['customFields'] ?? [];
    }
}

