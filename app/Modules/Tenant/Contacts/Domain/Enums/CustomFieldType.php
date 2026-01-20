<?php

namespace App\Modules\Tenant\Contacts\Domain\Enums;

/**
 * CustomFieldType Enum
 * 
 * Maps to vtiger uitype values for field types
 * These MUST match vtiger's exact uitype system for compatibility
 * 
 * Reference: vtiger CRM UI Types System
 * See: docs/custom-fields-and-uitypes.md
 */
enum CustomFieldType: int
{
    // Text and String Types
    case TEXT = 1;              // Single line text
    case TEXT_SPECIAL = 2;      // Single line text with special handling (e.g., Last Name)
    case TEXT_LARGE = 19;       // Text field (larger)
    case TEXTAREA = 21;         // Multi-line text

    // Numeric Types
    case INTEGER = 7;           // Whole numbers
    case DECIMAL = 71;          // Decimal number
    case CURRENCY = 72;         // Currency with conversion
    case PERCENT = 9;           // Percentage value

    // Date and Time Types
    case DATE = 5;              // Date picker
    case DATETIME = 6;          // Date and time picker
    case DATETIME_MODIFIED = 50; // Date and time (for modified time)
    case TIME = 14;             // Time picker

    // Contact Information Types
    case EMAIL = 13;            // Email address
    case PHONE = 11;            // Phone number
    case URL = 17;              // Website URL
    case SKYPE = 85;            // Skype ID

    // Selection Types
    case PICKLIST = 15;         // Dropdown (role-based)
    case PICKLIST_READONLY = 16; // Dropdown (non-role-based/system)
    case MULTIPICKLIST = 33;    // Multiple selection dropdown

    // Boolean and Special Types
    case CHECKBOX = 56;         // Boolean/Checkbox
    case SALUTATION = 55;       // Name prefix (Mr., Mrs., Dr.)

    // Reference and Relationship Types
    case REFERENCE = 10;        // Relation to other modules
    case OWNER = 52;            // User/Group assignment
    case OWNER_USER = 53;       // User assignment only

    // File and Media Types
    case IMAGE = 69;            // Image upload
    case FILE = 28;             // File upload

    // Currency System
    case CURRENCY_LIST = 117;   // Currency selector

    /**
     * Get field type label
     */
    public function label(): string
    {
        return match ($this) {
            self::TEXT => 'Text',
            self::TEXT_SPECIAL => 'Text (Special)',
            self::TEXT_LARGE => 'Text (Large)',
            self::EMAIL => 'Email',
            self::PHONE => 'Phone',
            self::PICKLIST, self::PICKLIST_READONLY => 'Picklist',
            self::TEXTAREA => 'Text Area',
            self::DATE => 'Date',
            self::DATETIME, self::DATETIME_MODIFIED => 'Date & Time',
            self::CHECKBOX => 'Checkbox',
            self::INTEGER => 'Number',
            self::DECIMAL => 'Decimal',
            self::URL => 'URL',
            self::MULTIPICKLIST => 'Multi Select',
            self::CURRENCY => 'Currency',
            self::PERCENT => 'Percent',
            self::SKYPE => 'Skype',
            self::TIME => 'Time',
            self::SALUTATION => 'Salutation',
            self::REFERENCE => 'Reference',
            self::OWNER => 'Owner',
            self::OWNER_USER => 'Owner (User)',
            self::IMAGE => 'Image',
            self::FILE => 'File',
            self::CURRENCY_LIST => 'Currency List',
        };
    }

    /**
     * Get database column type for migration
     */
    public function columnType(): string
    {
        return match ($this) {
            self::TEXT, self::TEXT_SPECIAL, self::TEXT_LARGE,
            self::EMAIL, self::PHONE, self::URL, self::SKYPE => 'string',
            self::PICKLIST, self::PICKLIST_READONLY, self::SALUTATION => 'string',
            self::TEXTAREA => 'text',
            self::DATE => 'date',
            self::DATETIME, self::DATETIME_MODIFIED => 'datetime',
            self::TIME => 'time',
            self::CHECKBOX => 'boolean',
            self::INTEGER, self::REFERENCE, self::OWNER, self::OWNER_USER => 'integer',
            self::DECIMAL, self::CURRENCY, self::PERCENT => 'decimal',
            self::MULTIPICKLIST => 'text',
            self::IMAGE, self::FILE => 'string',
            self::CURRENCY_LIST => 'integer',
        };
    }

    /**
     * Get column length for string types
     */
    public function columnLength(): ?int
    {
        return match ($this) {
            self::TEXT, self::TEXT_SPECIAL, self::TEXT_LARGE,
            self::EMAIL, self::PHONE, self::URL, self::SKYPE => 255,
            self::PICKLIST, self::PICKLIST_READONLY => 200,
            self::SALUTATION => 100,
            self::MULTIPICKLIST => 65535,
            self::IMAGE, self::FILE => 255,
            default => null,
        };
    }

    /**
     * Get decimal precision for numeric types
     */
    public function decimalPrecision(): array
    {
        return match ($this) {
            self::DECIMAL => [10, 2],
            self::CURRENCY => [25, 8],
            self::PERCENT => [5, 2],
            default => [10, 2],
        };
    }

    /**
     * Check if field type supports picklist values
     */
    public function hasPicklistValues(): bool
    {
        return in_array($this, [
            self::PICKLIST,
            self::PICKLIST_READONLY,
            self::MULTIPICKLIST,
            self::SALUTATION
        ]);
    }

    /**
     * Check if field is a reference/relationship type
     */
    public function isReferenceType(): bool
    {
        return in_array($this, [
            self::REFERENCE,
            self::OWNER,
            self::OWNER_USER
        ]);
    }

    /**
     * Check if field is a file/media type
     */
    public function isFileType(): bool
    {
        return in_array($this, [
            self::IMAGE,
            self::FILE
        ]);
    }

    /**
     * Check if field is a date/time type
     */
    public function isDateTimeType(): bool
    {
        return in_array($this, [
            self::DATE,
            self::DATETIME,
            self::DATETIME_MODIFIED,
            self::TIME
        ]);
    }

    /**
     * Get validation type for vtiger typeofdata field
     * Format: {DataType}~{Mandatory}
     */
    public function getTypeOfData(bool $mandatory = false): string
    {
        $mandatoryFlag = $mandatory ? 'M' : 'O';

        $dataType = match ($this) {
            self::TEXT, self::TEXT_SPECIAL, self::TEXT_LARGE,
            self::TEXTAREA, self::URL, self::SKYPE => 'V',
            self::EMAIL => 'E',
            self::PHONE => 'V',
            self::INTEGER => 'I',
            self::DECIMAL, self::CURRENCY, self::PERCENT => 'N',
            self::DATE => 'D',
            self::DATETIME, self::DATETIME_MODIFIED => 'DT',
            self::TIME => 'T',
            self::CHECKBOX => 'C',
            self::PICKLIST, self::PICKLIST_READONLY,
            self::MULTIPICKLIST, self::SALUTATION => 'V',
            self::REFERENCE, self::OWNER, self::OWNER_USER => 'V',
            self::IMAGE, self::FILE => 'V',
            self::CURRENCY_LIST => 'I',
        };

        return "{$dataType}~{$mandatoryFlag}";
    }

    /**
     * Get all UI types suitable for custom field creation
     * (Excludes system-only types and PICKLIST_READONLY which is selected via checkbox)
     */
    public static function getCustomFieldTypes(): array
    {
        return [
            self::TEXT,
            self::EMAIL,
            self::PHONE,
            self::PICKLIST,  // Role-based checkbox will determine if uitype 15 or 16
            self::TEXTAREA,
            self::DATE,
            self::DATETIME,
            self::CHECKBOX,
            self::INTEGER,
            self::DECIMAL,
            self::URL,
            self::MULTIPICKLIST,
            self::CURRENCY,
            self::PERCENT,
            self::TIME,
        ];
    }
}
