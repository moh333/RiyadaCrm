<?php

namespace App\Modules\Tenant\Contacts\Domain\Enums;

/**
 * CustomFieldType Enum
 * 
 * Maps to vtiger uitype values for field types
 * These MUST match vtiger's exact uitype system for compatibility
 */
enum CustomFieldType: int
{
    case TEXT = 1;              // Single line text
    case EMAIL = 13;            // Email address
    case PHONE = 11;            // Phone number
    case PICKLIST = 15;         // Dropdown/Select
    case TEXTAREA = 21;         // Multi-line text
    case DATE = 5;              // Date picker
    case DATETIME = 50;         // Date and time
    case CHECKBOX = 56;         // Boolean/Checkbox
    case INTEGER = 7;           // Integer number
    case DECIMAL = 71;          // Decimal number
    case URL = 17;              // Website URL
    case MULTIPICKLIST = 33;    // Multi-select dropdown
    case CURRENCY = 72;         // Currency field
    case PERCENT = 9;           // Percentage
    case SKYPE = 85;            // Skype ID
    case TIME = 14;             // Time picker

    /**
     * Get field type label
     */
    public function label(): string
    {
        return match ($this) {
            self::TEXT => 'Text',
            self::EMAIL => 'Email',
            self::PHONE => 'Phone',
            self::PICKLIST => 'Picklist',
            self::TEXTAREA => 'Text Area',
            self::DATE => 'Date',
            self::DATETIME => 'Date & Time',
            self::CHECKBOX => 'Checkbox',
            self::INTEGER => 'Number',
            self::DECIMAL => 'Decimal',
            self::URL => 'URL',
            self::MULTIPICKLIST => 'Multi Select',
            self::CURRENCY => 'Currency',
            self::PERCENT => 'Percent',
            self::SKYPE => 'Skype',
            self::TIME => 'Time',
        };
    }

    /**
     * Get database column type for migration
     */
    public function columnType(): string
    {
        return match ($this) {
            self::TEXT, self::EMAIL, self::PHONE, self::URL, self::SKYPE => 'string',
            self::PICKLIST => 'string',
            self::TEXTAREA => 'text',
            self::DATE => 'date',
            self::DATETIME => 'datetime',
            self::TIME => 'time',
            self::CHECKBOX => 'boolean',
            self::INTEGER => 'integer',
            self::DECIMAL, self::CURRENCY, self::PERCENT => 'decimal',
            self::MULTIPICKLIST => 'text',
        };
    }

    /**
     * Get column length for string types
     */
    public function columnLength(): ?int
    {
        return match ($this) {
            self::TEXT, self::EMAIL, self::PHONE, self::URL, self::SKYPE => 255,
            self::PICKLIST => 100,
            self::MULTIPICKLIST => 65535,
            default => null,
        };
    }

    /**
     * Check if field type supports picklist values
     */
    public function hasPicklistValues(): bool
    {
        return in_array($this, [self::PICKLIST, self::MULTIPICKLIST]);
    }
}
