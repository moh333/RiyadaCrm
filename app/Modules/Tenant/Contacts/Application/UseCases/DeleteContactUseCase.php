<?php

namespace App\Modules\Tenant\Contacts\Application\UseCases;

use App\Modules\Tenant\Contacts\Domain\Repositories\ContactRepositoryInterface;
use App\Modules\Tenant\Contacts\Domain\Events\ContactDeleted;

/**
 * DeleteContactUseCase
 * 
 * Soft-deletes a contact and triggers relationship cleanup.
 * 
 * Business Rules:
 * - Sets deleted = 1 (soft delete)
 * - Soft-deletes related Potentials where Contact is related_to
 * - Unlinks from Tickets, Orders, Quotes
 * - Deletes portal access
 * - Backs up relationships for recovery
 * 
 * Database Side-Effects:
 * - Update vtiger_crmentity (set deleted = 1)
 * - Multiple relationship cleanup operations
 * - Insert backup records into vtiger_relatedlists_rb
 * 
 * Emits: ContactDeleted event
 */
class DeleteContactUseCase
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
     * @throws \DomainException if contact not found
     */
    public function execute(int $contactId): void
    {
        // Find contact
        $contact = $this->contactRepository->findById($contactId);

        if (!$contact) {
            throw new \DomainException("Contact with ID {$contactId} not found");
        }

        // Soft delete contact
        $contact->delete();

        // Save (triggers relationship cleanup in repository)
        $this->contactRepository->delete($contact);

        // Emit domain event
        event(new ContactDeleted($contactId));
    }
}
