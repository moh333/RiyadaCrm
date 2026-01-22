<?php

namespace App\Modules\Tenant\Contacts\Application\UseCases;

use App\Modules\Tenant\Contacts\Domain\Contact;
use App\Modules\Tenant\Contacts\Domain\Repositories\ContactRepositoryInterface;
use App\Modules\Tenant\Contacts\Domain\ValueObjects\EmailAddress;
use App\Modules\Tenant\Contacts\Domain\ValueObjects\FullName;
use App\Modules\Tenant\Contacts\Domain\Events\ContactCreated;
use App\Modules\Tenant\Contacts\Domain\ValueObjects\PhoneNumber;

/**
 * CreateContactUseCase
 * 
 * Creates a new contact in the system.
 * 
 * Business Rules:
 * - lastname is MANDATORY
 * - Contact number is auto-generated
 * - ID generated from vtiger_crmentity_seq
 * - Must have an owner
 * 
 * Database Side-Effects:
 * - Inserts into 6 tables (crmentity, contactdetails, contactsubdetails, contactaddress, contactscf, customerdetails)
 * 
 * Emits: ContactCreated event
 */
class CreateContactUseCase
{
    private ContactRepositoryInterface $contactRepository;

    public function __construct(ContactRepositoryInterface $contactRepository)
    {
        $this->contactRepository = $contactRepository;
    }

    /**
     * Execute the use case
     * 
     * @param CreateContactDTO $dto
     * @return Contact
     */
    public function execute(CreateContactDTO $dto): Contact
    {
        // Generate ID and contact number
        $id = $this->contactRepository->nextIdentity();
        $contactNo = $this->contactRepository->generateContactNumber();

        // Create full name value object
        $fullName = FullName::create(
            $dto->salutation,
            $dto->firstName,
            $dto->lastName
        );

        // Create contact entity
        $contact = Contact::create(
            $id,
            $contactNo,
            $fullName,
            $dto->ownerId,
            $dto->creatorId
        );

        // Set optional fields
        if ($dto->email) {
            $contact->setEmail(EmailAddress::create($dto->email));
        }
        if ($dto->accountId) {
            $contact->linkToAccount($dto->accountId);
        }
        if ($dto->phone) {
            $contact->setOfficePhone(PhoneNumber::office($dto->phone));
        }
        if ($dto->mobile) {
            $contact->setMobilePhone(PhoneNumber::mobile($dto->mobile));
        }
        if ($dto->homePhone) {
            $contact->setHomePhone(PhoneNumber::home($dto->homePhone));
        }
        if ($dto->fax) {
            $contact->setFax(PhoneNumber::other($dto->fax));
        }
        if ($dto->title) {
            $contact->setTitle($dto->title);
        }
        if ($dto->department) {
            $contact->setDepartment($dto->department);
        }
        if ($dto->description) {
            $contact->setDescription($dto->description);
        }
        if ($dto->assistant) {
            $contact->setAssistant($dto->assistant);
        }
        if ($dto->assistantPhone) {
            $contact->setAssistantPhone(PhoneNumber::other($dto->assistantPhone));
        }
        if ($dto->birthday) {
            try {
                $contact->setBirthday(new \DateTimeImmutable($dto->birthday));
            } catch (\Exception $e) {
            }
        }
        if ($dto->leadSource) {
            $contact->setLeadSource($dto->leadSource);
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
        }

        if ($dto->image) {
            $contact->uploadImage($dto->image);
        }

        // Set custom fields
        if (!empty($dto->customFields)) {
            $contact->setCustomFields($dto->customFields);
        }


        // Save contact
        $this->contactRepository->save($contact);

        // Emit domain event
        event(new ContactCreated($contact));

        return $contact;
    }
}

/**
 * CreateContactDTO
 * 
 * Data Transfer Object for creating a contact
 */
class CreateContactDTO
{
    public ?string $salutation;
    public ?string $firstName;
    public string $lastName; // MANDATORY
    public ?string $email;
    public ?int $accountId;
    public int $ownerId; // MANDATORY
    public int $creatorId; // MANDATORY

    // Additional fields...
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
        $this->ownerId = $data['ownerId'];
        $this->creatorId = $data['creatorId'];
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

