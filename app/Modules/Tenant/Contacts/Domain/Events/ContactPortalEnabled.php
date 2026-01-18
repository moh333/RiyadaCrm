<?php

namespace App\Modules\Tenant\Contacts\Domain\Events;

use App\Modules\Tenant\Contacts\Domain\ValueObjects\PortalCredentials;

/**
 * ContactPortalEnabled Domain Event
 * 
 * Emitted when portal access is enabled for a contact.
 * 
 * Business Rules:
 * - Portal can only be enabled if contact has email
 * - Portal username = contact's email address
 * - System generates random password
 * - Email with credentials sent if emailoptout = 0
 * 
 * Database Side-Effects:
 * - Insert into vtiger_portalinfo (if new) OR Update (if exists)
 * - Update vtiger_customerdetails (set portal = 1)
 * - Send portal credentials email (if emailoptout = 0)
 */
final class ContactPortalEnabled
{
    private int $contactId;
    private PortalCredentials $credentials;
    private string $plainPassword; // For email notification
    private bool $shouldSendEmail;
    private \DateTimeImmutable $occurredAt;

    public function __construct(
        int $contactId,
        PortalCredentials $credentials,
        string $plainPassword,
        bool $shouldSendEmail = true
    ) {
        $this->contactId = $contactId;
        $this->credentials = $credentials;
        $this->plainPassword = $plainPassword;
        $this->shouldSendEmail = $shouldSendEmail;
        $this->occurredAt = new \DateTimeImmutable();
    }

    public function getContactId(): int
    {
        return $this->contactId;
    }

    public function getCredentials(): PortalCredentials
    {
        return $this->credentials;
    }

    public function getPlainPassword(): string
    {
        return $this->plainPassword;
    }

    public function shouldSendEmail(): bool
    {
        return $this->shouldSendEmail;
    }

    public function getOccurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
