<?php

namespace App\Modules\Tenant\Contacts\Domain\Events;

/**
 * ContactAccountLinked Domain Event
 * 
 * Emitted when a contact is linked to an account.
 * 
 * Business Rule: Contact can be linked to at most ONE Account
 * 
 * Database Side-Effects:
 * - Update vtiger_contactdetails (set accountid)
 */
final class ContactAccountLinked
{
    private int $contactId;
    private int $accountId;
    private \DateTimeImmutable $occurredAt;

    public function __construct(int $contactId, int $accountId)
    {
        $this->contactId = $contactId;
        $this->accountId = $accountId;
        $this->occurredAt = new \DateTimeImmutable();
    }

    public function getContactId(): int
    {
        return $this->contactId;
    }

    public function getAccountId(): int
    {
        return $this->accountId;
    }

    public function getOccurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
