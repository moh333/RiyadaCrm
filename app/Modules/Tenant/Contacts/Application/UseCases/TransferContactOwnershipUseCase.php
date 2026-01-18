<?php

namespace App\Modules\Tenant\Contacts\Application\UseCases;

use App\Modules\Tenant\Contacts\Domain\Repositories\ContactRepositoryInterface;

/**
 * TransferContactOwnershipUseCase
 * 
 * Transfers contact ownership to a new user/group.
 * 
 * Business Rule: All related records move to new owner
 * 
 * Database Side-Effects:
 * - Update vtiger_crmentity.smownerid
 * - Transfer relationships for: Potentials, Activities, Emails, Tickets, Quotes,
 *   POs, SOs, Products, Documents, Campaigns, Invoices, Service Contracts, Projects, Assets, Vendors
 * - Duplicate prevention during transfer
 * 
 * Emits: ContactUpdated event
 */
class TransferContactOwnershipUseCase
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
     * @param int $newOwnerId
     * @return void
     * @throws \DomainException if contact not found
     */
    public function execute(int $contactId, int $newOwnerId): void
    {
        // Find contact
        $contact = $this->contactRepository->findById($contactId);

        if (!$contact) {
            throw new \DomainException("Contact with ID {$contactId} not found");
        }

        // Transfer ownership (repository handles relationship transfer)
        $this->contactRepository->transferOwnership($contactId, $newOwnerId);

        // Note: Repository implementation handles all relationship transfers
        // as per vtiger's transferRelatedRecords() business logic
    }
}
