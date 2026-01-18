<?php

namespace App\Modules\Tenant\Contacts\Domain\Events;

use App\Modules\Tenant\Contacts\Domain\Contact;

/**
 * ContactUpdated Domain Event
 * 
 * Emitted when a contact is updated.
 * 
 * Database Side-Effects:
 * - Update vtiger_crmentity (modifiedtime, modifiedby, label)
 * - Update vtiger_contactdetails
 * - Conditional updates to vtiger_contactsubdetails, vtiger_contactaddress, vtiger_contactscf
 */
final class ContactUpdated
{
    private Contact $contact;
    private array $changedFields;
    private \DateTimeImmutable $occurredAt;

    public function __construct(Contact $contact, array $changedFields = [])
    {
        $this->contact = $contact;
        $this->changedFields = $changedFields;
        $this->occurredAt = new \DateTimeImmutable();
    }

    public function getContact(): Contact
    {
        return $this->contact;
    }

    public function getChangedFields(): array
    {
        return $this->changedFields;
    }

    public function getOccurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
