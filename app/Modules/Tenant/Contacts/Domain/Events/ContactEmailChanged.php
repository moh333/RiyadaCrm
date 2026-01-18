<?php

namespace App\Modules\Tenant\Contacts\Domain\Events;

use App\Modules\Tenant\Contacts\Domain\ValueObjects\EmailAddress;

/**
 * ContactEmailChanged Domain Event
 * 
 * Emitted when a contact's email address changes.
 * 
 * Business Rules:
 * - If portal is enabled, portal username must be updated to new email
 * - New portal password generated and credentials email sent
 * 
 * Database Side-Effects:
 * - Update vtiger_contactdetails (email field)
 * - If portal enabled: Update vtiger_portalinfo (user_name, user_password)
 * - If portal enabled: Send new credentials email (if emailoptout = 0)
 */
final class ContactEmailChanged
{
    private int $contactId;
    private ?EmailAddress $oldEmail;
    private EmailAddress $newEmail;
    private bool $portalEnabled;
    private \DateTimeImmutable $occurredAt;

    public function __construct(
        int $contactId,
        ?EmailAddress $oldEmail,
        EmailAddress $newEmail,
        bool $portalEnabled
    ) {
        $this->contactId = $contactId;
        $this->oldEmail = $oldEmail;
        $this->newEmail = $newEmail;
        $this->portalEnabled = $portalEnabled;
        $this->occurredAt = new \DateTimeImmutable();
    }

    public function getContactId(): int
    {
        return $this->contactId;
    }

    public function getOldEmail(): ?EmailAddress
    {
        return $this->oldEmail;
    }

    public function getNewEmail(): EmailAddress
    {
        return $this->newEmail;
    }

    public function isPortalEnabled(): bool
    {
        return $this->portalEnabled;
    }

    public function getOccurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
