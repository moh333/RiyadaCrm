<?php

namespace App\Modules\Core\VtigerModules\Domain;

/**
 * FieldDefinition
 * 
 * Value object representing a field in vtiger_field table.
 * 
 * vtiger_field contains metadata about all fields in the CRM:
 * - Standard fields (firstname, lastname, email, etc.)
 * - Custom fields (cf_* fields created by users)
 * - System fields (createdtime, modifiedtime, etc.)
 * 
 * Key vtiger concepts:
 * - uitype: Determines field type and behavior (1=text, 13=email, 15=picklist, etc.)
 * - typeofdata: Validation rules (V~M = varchar mandatory, I~O = integer optional, etc.)
 * - generatedtype: 1=system field, 2=custom field
 * - presence: 0=visible everywhere, 1=hidden, 2=readonly
 * - displaytype: 1=visible, 2=hidden, 3=readonly, 4=readonly in edit
 */
class FieldDefinition
{
    private function __construct(
        private readonly int $id,
        private readonly string $module,
        private readonly string $fieldName,
        private readonly string $columnName,
        private readonly string $tableName,
        private readonly string $label,
        private readonly int $uitype,
        private readonly string $typeofdata,
        private readonly int $blockId,
        private readonly int $presence,
        private readonly int $displayType,
        private readonly int $sequence,
        private readonly int $generatedType,
        private readonly ?string $defaultValue,
        private readonly ?int $maximumLength,
        private readonly bool $quickCreate,
        private readonly ?string $helpInfo,
    ) {
    }

    /**
     * Create a new FieldDefinition
     * 
     * @param int $id vtiger_field.fieldid
     * @param string $module Module name
     * @param string $fieldName vtiger_field.fieldname (e.g., "email")
     * @param string $columnName vtiger_field.columnname (e.g., "email")
     * @param string $tableName vtiger_field.tablename (e.g., "vtiger_contactdetails")
     * @param string $label vtiger_field.fieldlabel (e.g., "LBL_EMAIL")
     * @param int $uitype vtiger_field.uitype (field type: 1=text, 13=email, etc.)
     * @param string $typeofdata vtiger_field.typeofdata (validation: V~M, I~O, etc.)
     * @param int $blockId vtiger_field.block (block this field belongs to)
     * @param int $presence vtiger_field.presence (0=visible, 1=hidden, 2=readonly)
     * @param int $displayType vtiger_field.displaytype (1=visible, 2=hidden, etc.)
     * @param int $sequence vtiger_field.sequence (display order)
     * @param int $generatedType vtiger_field.generatedtype (1=system, 2=custom)
     * @param string|null $defaultValue vtiger_field.defaultvalue
     * @param int|null $maximumLength vtiger_field.maximumlength
     * @param bool $quickCreate vtiger_field.quickcreate
     * @param string|null $helpInfo vtiger_field.helpinfo
     */
    public static function create(
        int $id,
        string $module,
        string $fieldName,
        string $columnName,
        string $tableName,
        string $label,
        int $uitype,
        string $typeofdata,
        int $blockId,
        int $presence = 2,
        int $displayType = 1,
        int $sequence = 0,
        int $generatedType = 1,
        ?string $defaultValue = null,
        ?int $maximumLength = null,
        bool $quickCreate = false,
        ?string $helpInfo = null,
    ): self {
        return new self(
            id: $id,
            module: $module,
            fieldName: $fieldName,
            columnName: $columnName,
            tableName: $tableName,
            label: $label,
            uitype: $uitype,
            typeofdata: $typeofdata,
            blockId: $blockId,
            presence: $presence,
            displayType: $displayType,
            sequence: $sequence,
            generatedType: $generatedType,
            defaultValue: $defaultValue,
            maximumLength: $maximumLength,
            quickCreate: $quickCreate,
            helpInfo: $helpInfo,
        );
    }

    // Getters
    public function getId(): int
    {
        return $this->id;
    }

    public function getModule(): string
    {
        return $this->module;
    }

    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    public function getColumnName(): string
    {
        return $this->columnName;
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getUitype(): int
    {
        return $this->uitype;
    }

    public function getUitypeEnum(): \App\Modules\Tenant\Contacts\Domain\Enums\CustomFieldType
    {
        return \App\Modules\Tenant\Contacts\Domain\Enums\CustomFieldType::from($this->uitype);
    }

    public function getTypeofdata(): string
    {
        return $this->typeofdata;
    }

    public function getBlockId(): int
    {
        return $this->blockId;
    }

    public function getPresence(): int
    {
        return $this->presence;
    }

    public function getDisplayType(): int
    {
        return $this->displayType;
    }

    public function getSequence(): int
    {
        return $this->sequence;
    }

    public function getGeneratedType(): int
    {
        return $this->generatedType;
    }

    public function getDefaultValue(): ?string
    {
        return $this->defaultValue;
    }

    public function getMaximumLength(): ?int
    {
        return $this->maximumLength;
    }

    public function isQuickCreate(): bool
    {
        return $this->quickCreate;
    }

    public function getHelpInfo(): ?string
    {
        return $this->helpInfo;
    }

    /**
     * Check if field is mandatory
     * 
     * Parses typeofdata string (e.g., "V~M" means varchar mandatory)
     * 
     * @return bool
     */
    public function isMandatory(): bool
    {
        return str_contains($this->typeofdata, '~M');
    }

    /**
     * Check if field is visible
     * 
     * Based on presence and displaytype flags
     * 
     * @return bool
     */
    public function isVisible(): bool
    {
        return $this->presence !== 1 && $this->displayType !== 2;
    }

    /**
     * Check if field is editable
     * 
     * Based on displaytype flag
     * 
     * @return bool
     */
    public function isEditable(): bool
    {
        return $this->displayType === 1;
    }

    /**
     * Check if field is a custom field (user-created)
     * 
     * generatedtype = 2 means custom field
     * 
     * @return bool
     */
    public function isCustomField(): bool
    {
        return $this->generatedType === 2;
    }

    /**
     * Check if field is readonly
     * 
     * @return bool
     */
    public function isReadonly(): bool
    {
        return $this->displayType === 3 || $this->displayType === 4;
    }

    /**
     * Get field type category based on uitype
     * 
     * @return string
     */
    public function getFieldType(): string
    {
        $enum = \App\Modules\Tenant\Contacts\Domain\Enums\CustomFieldType::tryFrom($this->uitype);
        if ($enum) {
            return $enum->label();
        }

        return match ($this->uitype) {
            4 => 'Contact Name',
            10 => 'Reference',
            23 => 'Date',
            50 => 'Date & Time',
            51 => 'User',
            70 => 'Date & Time',
            default => 'System (' . $this->uitype . ')',
        };
    }
}
