<?php

namespace App\Modules\Tenant\Contacts\Domain\Events;

/**
 * ContactAccountUnlinked Domain Event
 * 
 * Emitted when a contact is unlinked from an account.
 * 
 * Database Side-Effects:
 * - Update vtiger_contactdetails (set accountid = NULL)
 */
final class ContactAccountUnlinked
{
    private int $contactId;
    private int $previousAccountId;
    private \DateTimeImmutable $occurredAt;

    public function __construct(int $contactId, int $previousAccountId)
    {
        $this->contactId = $contactId;
        $this->previousAccountId = $previousAccountId;
        $this->occurredAt = new \DateTimeImmutable();
    }

    public function getContactId(): int
    {
        return $this->contactId;
    }

    public function getPreviousAccountId(): int
    {
        return $this->previousAccountId;
    }

    public function getOccurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
