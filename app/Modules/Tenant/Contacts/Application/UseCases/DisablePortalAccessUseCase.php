<?php

namespace App\Modules\Tenant\Contacts\Application\UseCases;

use App\Modules\Tenant\Contacts\Domain\Repositories\ContactRepositoryInterface;
use App\Modules\Tenant\Contacts\Domain\Events\ContactPortalDisabled;

/**
 * DisablePortalAccessUseCase
 * 
 * Disables customer portal access for a contact.
 * 
 * Business Rule: Portal record preserved but deactivated (isactive = 0)
 * 
 * Database Side-Effects:
 * - Update vtiger_portalinfo (set isactive = 0)
 * - Update vtiger_customerdetails (set portal = 0)
 * 
 * Emits: ContactPortalDisabled event
 */
class DisablePortalAccessUseCase
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

        // Disable portal access
        $contact->disablePortalAccess();

        // Save to repository
        $this->contactRepository->disablePortalAccess($contactId);

        // Emit domain event
        event(new ContactPortalDisabled($contactId));
    }
}
