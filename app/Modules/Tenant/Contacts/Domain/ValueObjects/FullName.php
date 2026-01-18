<?php

namespace App\Modules\Tenant\Contacts\Domain\ValueObjects;

/**
 * FullName Value Object
 * 
 * Represents a contact's full name with salutation, first name, and last name.
 * 
 * Business Rule: lastname is MANDATORY
 * Database: Maps to vtiger_contactdetails (salutation, firstname, lastname)
 */
final class FullName
{
    private ?string $salutation;
    private ?string $firstName;
    private string $lastName;

    private function __construct(?string $salutation, ?string $firstName, string $lastName)
    {
        if (empty(trim($lastName))) {
            throw new \InvalidArgumentException('Last name is mandatory');
        }

        $this->salutation = $salutation;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }

    public static function create(?string $salutation, ?string $firstName, string $lastName): self
    {
        return new self($salutation, $firstName, $lastName);
    }

    public function getSalutation(): ?string
    {
        return $this->salutation;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * Get display name for vtiger_crmentity.label
     * Format: "FirstName LastName" or just "LastName"
     */
    public function getDisplayName(): string
    {
        if ($this->firstName) {
            return trim($this->firstName . ' ' . $this->lastName);
        }
        return $this->lastName;
    }

    /**
     * Get full name with salutation
     * Format: "Salutation FirstName LastName"
     */
    public function getFullNameWithSalutation(): string
    {
        $parts = array_filter([$this->salutation, $this->firstName, $this->lastName]);
        return implode(' ', $parts);
    }

    public function equals(FullName $other): bool
    {
        return $this->salutation === $other->salutation
            && $this->firstName === $other->firstName
            && $this->lastName === $other->lastName;
    }
}
