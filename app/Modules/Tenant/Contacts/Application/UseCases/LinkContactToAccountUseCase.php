<?php

namespace App\Modules\Tenant\Contacts\Application\UseCases;

use App\Modules\Tenant\Contacts\Domain\Repositories\ContactRepositoryInterface;
use App\Modules\Tenant\Contacts\Domain\Events\ContactAccountLinked;

/**
 * LinkContactToAccountUseCase
 * 
 * Links a contact to an account (organization).
 * 
 * Business Rule: Contact can be linked to at most ONE Account
 * 
 * Database Side-Effects:
 * - Update vtiger_contactdetails (set accountid)
 * 
 * Emits: ContactAccountLinked event
 */
class LinkContactToAccountUseCase
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
     * @param int $accountId
     * @return void
     * @throws \DomainException if contact not found
     */
    public function execute(int $contactId, int $accountId): void
    {
        // Find contact
        $contact = $this->contactRepository->findById($contactId);

        if (!$contact) {
            throw new \DomainException("Contact with ID {$contactId} not found");
        }

        // Link to account
        $contact->linkToAccount($accountId);

        // Save to repository
        $this->contactRepository->linkToAccount($contactId, $accountId);

        // Emit domain event
        event(new ContactAccountLinked($contactId, $accountId));
    }
}
