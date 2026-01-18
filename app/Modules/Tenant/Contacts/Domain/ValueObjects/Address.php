<?php

namespace App\Modules\Tenant\Contacts\Domain\ValueObjects;

/**
 * Address Value Object
 * 
 * Represents mailing or alternate address for a contact.
 * 
 * Business Rule: Contact addresses auto-populate inventory documents (Quotes, Orders, Invoices)
 * Database: Maps to vtiger_contactaddress (mailing* and other* fields)
 * 
 * Mailing Address fields: mailingstreet, mailingcity, mailingstate, mailingzip, mailingcountry, mailingpobox
 * Alternate Address fields: otherstreet, othercity, otherstate, otherzip, othercountry, otherpobox
 */
final class Address
{
    private ?string $street;
    private ?string $city;
    private ?string $state;
    private ?string $zip;
    private ?string $country;
    private ?string $poBox;
    private string $type;

    public const TYPE_MAILING = 'mailing';
    public const TYPE_ALTERNATE = 'alternate';

    private function __construct(
        ?string $street,
        ?string $city,
        ?string $state,
        ?string $zip,
        ?string $country,
        ?string $poBox,
        string $type
    ) {
        $this->street = $street;
        $this->city = $city;
        $this->state = $state;
        $this->zip = $zip;
        $this->country = $country;
        $this->poBox = $poBox;
        $this->type = $type;
    }

    public static function mailing(
        ?string $street = null,
        ?string $city = null,
        ?string $state = null,
        ?string $zip = null,
        ?string $country = null,
        ?string $poBox = null
    ): self {
        return new self($street, $city, $state, $zip, $country, $poBox, self::TYPE_MAILING);
    }

    public static function alternate(
        ?string $street = null,
        ?string $city = null,
        ?string $state = null,
        ?string $zip = null,
        ?string $country = null,
        ?string $poBox = null
    ): self {
        return new self($street, $city, $state, $zip, $country, $poBox, self::TYPE_ALTERNATE);
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }
    public function getCity(): ?string
    {
        return $this->city;
    }
    public function getState(): ?string
    {
        return $this->state;
    }
    public function getZip(): ?string
    {
        return $this->zip;
    }
    public function getCountry(): ?string
    {
        return $this->country;
    }
    public function getPoBox(): ?string
    {
        return $this->poBox;
    }
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get formatted address string
     */
    public function getFormattedAddress(): string
    {
        $parts = array_filter([
            $this->street,
            $this->city,
            $this->state,
            $this->zip,
            $this->country
        ]);

        return implode(', ', $parts);
    }

    /**
     * Check if address is empty
     */
    public function isEmpty(): bool
    {
        return empty($this->street)
            && empty($this->city)
            && empty($this->state)
            && empty($this->zip)
            && empty($this->country)
            && empty($this->poBox);
    }

    public function equals(Address $other): bool
    {
        return $this->street === $other->street
            && $this->city === $other->city
            && $this->state === $other->state
            && $this->zip === $other->zip
            && $this->country === $other->country
            && $this->poBox === $other->poBox
            && $this->type === $other->type;
    }
}
