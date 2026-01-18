<?php

namespace App\Modules\Tenant\Contacts\Domain\Events;

/**
 * ContactDeleted Domain Event
 * 
 * Emitted when a contact is soft-deleted.
 * 
 * Database Side-Effects:
 * - Update vtiger_crmentity (set deleted = 1)
 * - Soft-delete related Potentials (where Contact is related_to)
 * - Unlink from Tickets (set contact_id = 0)
 * - Unlink from Purchase Orders (set contactid = 0)
 * - Unlink from Sales Orders (set contactid = 0)
 * - Unlink from Quotes (set contactid = 0)
 * - Delete from vtiger_portalinfo
 * - Update vtiger_customerdetails (set portal = 0, clear support dates)
 * - Insert backup records into vtiger_relatedlists_rb
 */
final class ContactDeleted
{
    private int $contactId;
    private \DateTimeImmutable $occurredAt;

    public function __construct(int $contactId)
    {
        $this->contactId = $contactId;
        $this->occurredAt = new \DateTimeImmutable();
    }

    public function getContactId(): int
    {
        return $this->contactId;
    }

    public function getOccurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
