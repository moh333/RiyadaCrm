<?php

namespace App\Modules\Tenant\Contacts\Application\UseCases;

use App\Modules\Tenant\Contacts\Domain\Repositories\ContactRepositoryInterface;
use App\Modules\Tenant\Contacts\Domain\Events\ContactAccountUnlinked;

/**
 * UnlinkContactFromAccountUseCase
 * 
 * Unlinks a contact from its account.
 * 
 * Database Side-Effects:
 * - Update vtiger_contactdetails (set accountid = NULL)
 * 
 * Emits: ContactAccountUnlinked event
 */
class UnlinkContactFromAccountUseCase
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
     * @return void
     * @throws \DomainException if contact not found or not linked to account
     */
    public function execute(int $contactId): void
    {
        // Find contact
        $contact = $this->contactRepository->findById($contactId);

        if (!$contact) {
            throw new \DomainException("Contact with ID {$contactId} not found");
        }

        $previousAccountId = $contact->getAccountId();

        if (!$previousAccountId) {
            throw new \DomainException("Contact is not linked to any account");
        }

        // Unlink from account
        $contact->unlinkFromAccount();

        // Save to repository
        $this->contactRepository->unlinkFromAccount($contactId);

        // Emit domain event
        event(new ContactAccountUnlinked($contactId, $previousAccountId));
    }
}
