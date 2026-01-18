<?php

namespace App\Modules\Tenant\Contacts\Domain\Events;

/**
 * ContactPortalDisabled Domain Event
 * 
 * Emitted when portal access is disabled for a contact.
 * 
 * Business Rule: Portal record is preserved but deactivated (isactive = 0)
 * 
 * Database Side-Effects:
 * - Update vtiger_portalinfo (set isactive = 0)
 * - Update vtiger_customerdetails (set portal = 0)
 */
final class ContactPortalDisabled
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
