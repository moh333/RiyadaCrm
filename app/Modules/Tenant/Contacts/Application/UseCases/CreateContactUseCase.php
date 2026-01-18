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
        if ($dto->title) {
            $contact->setTitle($dto->title);
        }
        if ($dto->department) {
            $contact->setDepartment($dto->department);
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
    public ?string $title;
    public ?string $department;

    // Address fields
    public ?string $mailingStreet;
    public ?string $mailingCity;
    public ?string $mailingState;
    public ?string $mailingZip;
    public ?string $mailingCountry;

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
        $this->title = $data['title'] ?? null;
        $this->department = $data['department'] ?? null;
        $this->mailingStreet = $data['mailingStreet'] ?? null;
        $this->mailingCity = $data['mailingCity'] ?? null;
        $this->mailingState = $data['mailingState'] ?? null;
        $this->mailingZip = $data['mailingZip'] ?? null;
        $this->mailingCountry = $data['mailingCountry'] ?? null;
    }
}

