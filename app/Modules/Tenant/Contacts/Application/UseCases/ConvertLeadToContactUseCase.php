<?php

namespace App\Modules\Tenant\Contacts\Application\UseCases;

use App\Modules\Tenant\Contacts\Domain\Contact;
use App\Modules\Tenant\Contacts\Domain\Repositories\ContactRepositoryInterface;

/**
 * ConvertLeadToContactUseCase
 * 
 * Converts a Lead to a Contact during lead conversion process.
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
 * Business Rules:
 * - Contact is automatically linked to created Account (if Account created)
 * - Contact is automatically linked to created Potential (if Potential created)
 * - Lead's leadsource is preserved in contact
 * 
 * Database Side-Effects:
 * - Creates contact with lead data
 * - Links to Account/Potential if created
 * - Marks lead as converted
 * 
 * Emits: ContactCreated event
 */
class ConvertLeadToContactUseCase
{
    private ContactRepositoryInterface $contactRepository;

    public function __construct(ContactRepositoryInterface $contactRepository)
    {
        $this->contactRepository = $contactRepository;
    }

    /**
     * Execute the use case
     * 
     * @param ConvertLeadDTO $dto
     * @return Contact
     */
    public function execute(ConvertLeadDTO $dto): Contact
    {
        // Create contact from lead data
        $contact = $this->contactRepository->createFromLead(
            $dto->leadData,
            $dto->accountId,
            $dto->potentialId
        );

        // Repository handles:
        // - Field mapping from lead to contact
        // - Linking to account (if accountId provided)
        // - Linking to potential (if potentialId provided)
        // - Preserving leadsource

        return $contact;
    }
}

/**
 * ConvertLeadDTO
 * 
 * Data Transfer Object for lead conversion
 */
class ConvertLeadDTO
{
    public array $leadData;
    public ?int $accountId;
    public ?int $potentialId;

    public function __construct(array $leadData, ?int $accountId = null, ?int $potentialId = null)
    {
        $this->leadData = $leadData;
        $this->accountId = $accountId;
        $this->potentialId = $potentialId;
    }
}
