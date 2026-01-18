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
        if ($dto->title !== $contact->getTitle()) {
            $contact->setTitle($dto->title);
            $changedFields[] = 'title';
        }
        if ($dto->department !== $contact->getDepartment()) {
            $contact->setDepartment($dto->department);
            $changedFields[] = 'department';
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
    public ?string $title;
    public ?string $department;


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
        $this->title = $data['title'] ?? null;
        $this->department = $data['department'] ?? null;
    }
}

