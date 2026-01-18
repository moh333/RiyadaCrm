<?php

namespace App\Modules\Tenant\Contacts\Domain\ValueObjects;

/**
 * EmailAddress Value Object
 * 
 * Represents a contact's email address with opt-out flag.
 * 
 * Business Rule: Email is used for portal login (portal username = email)
 * Business Rule: emailoptout flag controls automated email sending
 * Database: Maps to vtiger_contactdetails (email, otheremail, secondaryemail, emailoptout)
 */
final class EmailAddress
{
    private string $email;
    private bool $optOut;

    private function __construct(string $email, bool $optOut = false)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email address format');
        }

        $this->email = $email;
        $this->optOut = $optOut;
    }

    public static function create(string $email, bool $optOut = false): self
    {
        return new self($email, $optOut);
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function isOptedOut(): bool
    {
        return $this->optOut;
    }

    public function optOut(): self
    {
        return new self($this->email, true);
    }

    public function optIn(): self
    {
        return new self($this->email, false);
    }

    /**
     * Check if automated emails can be sent
     * Business Rule: No automated emails if emailoptout = 1
     */
    public function canReceiveAutomatedEmails(): bool
    {
        return !$this->optOut;
    }

    public function equals(EmailAddress $other): bool
    {
        return strtolower($this->email) === strtolower($other->email);
    }

    public function __toString(): string
    {
        return $this->email;
    }
}
