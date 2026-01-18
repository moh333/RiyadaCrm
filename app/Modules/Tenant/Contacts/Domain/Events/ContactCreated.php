<?php

namespace App\Modules\Tenant\Contacts\Domain\Events;

use App\Modules\Tenant\Contacts\Domain\Contact;

/**
 * ContactCreated Domain Event
 * 
 * Emitted when a new contact is created.
 * 
 * Database Side-Effects:
 * - Insert into vtiger_crmentity
 * - Insert into vtiger_contactdetails
 * - Insert into vtiger_contactsubdetails
 * - Insert into vtiger_contactaddress
 * - Insert into vtiger_contactscf
 * - Insert into vtiger_customerdetails
 */
final class ContactCreated
{
    private Contact $contact;
    private \DateTimeImmutable $occurredAt;

    public function __construct(Contact $contact)
    {
        $this->contact = $contact;
        $this->occurredAt = new \DateTimeImmutable();
    }

    public function getContact(): Contact
    {
        return $this->contact;
    }

    public function getOccurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
