<?php

namespace App\Modules\Tenant\Contacts\Domain\ValueObjects;

/**
 * PhoneNumber Value Object
 * 
 * Represents various phone number types for a contact.
 * 
 * Database: Maps to vtiger_contactdetails (phone, mobile, fax)
 *           and vtiger_contactsubdetails (homephone, otherphone, assistantphone)
 */
final class PhoneNumber
{
    private string $number;
    private string $type;

    public const TYPE_OFFICE = 'office';
    public const TYPE_MOBILE = 'mobile';
    public const TYPE_HOME = 'home';
    public const TYPE_FAX = 'fax';
    public const TYPE_ASSISTANT = 'assistant';
    public const TYPE_OTHER = 'other';

    private function __construct(string $number, string $type = self::TYPE_OFFICE)
    {
        if (empty(trim($number))) {
            throw new \InvalidArgumentException('Phone number cannot be empty');
        }

        $this->number = $number;
        $this->type = $type;
    }

    public static function office(string $number): self
    {
        return new self($number, self::TYPE_OFFICE);
    }

    public static function mobile(string $number): self
    {
        return new self($number, self::TYPE_MOBILE);
    }

    public static function home(string $number): self
    {
        return new self($number, self::TYPE_HOME);
    }

    public static function fax(string $number): self
    {
        return new self($number, self::TYPE_FAX);
    }

    public static function assistant(string $number): self
    {
        return new self($number, self::TYPE_ASSISTANT);
    }

    public static function other(string $number): self
    {
        return new self($number, self::TYPE_OTHER);
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function equals(PhoneNumber $other): bool
    {
        return $this->number === $other->number && $this->type === $other->type;
    }

    public function __toString(): string
    {
        return $this->number;
    }
}
