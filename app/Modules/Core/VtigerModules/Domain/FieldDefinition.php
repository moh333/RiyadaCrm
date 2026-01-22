<?php

namespace App\Modules\Core\VtigerModules\Domain;

/**
 * FieldDefinition
 * 
 * Value object representing a field in vtiger_field table.
 */
class FieldDefinition
{
    private function __construct(
        private readonly int $id,
        private readonly string $module,
        private readonly string $fieldName,
        private readonly string $columnName,
        private readonly string $tableName,
        private readonly string $labelEn,
        private readonly ?string $labelAr,
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
        private readonly bool $allowMultipleFiles = false,
        private readonly ?string $acceptableFileTypes = null,
    ) {
    }

    /**
     * Create a new FieldDefinition
     */
    public static function create(
        int $id,
        string $module,
        string $fieldName,
        string $columnName,
        string $tableName,
        string $labelEn,
        ?string $labelAr,
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
        bool $allowMultipleFiles = false,
        ?string $acceptableFileTypes = null,
    ): self {
        return new self(
            id: $id,
            module: $module,
            fieldName: $fieldName,
            columnName: $columnName,
            tableName: $tableName,
            labelEn: $labelEn,
            labelAr: $labelAr,
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
            allowMultipleFiles: $allowMultipleFiles,
            acceptableFileTypes: $acceptableFileTypes,
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
        $locale = app()->getLocale();
        if ($locale === 'ar' && $this->labelAr) {
            return $this->labelAr;
        }
        return $this->labelEn;
    }

    public function getOriginalLabel(): string
    {
        return $this->labelEn;
    }

    public function getLabelEn(): string
    {
        return $this->labelEn;
    }

    public function getLabelAr(): ?string
    {
        return $this->labelAr;
    }

    public function getUitype(): int
    {
        return $this->uitype;
    }

    public function getUitypeEnum(): \App\Modules\Tenant\Contacts\Domain\Enums\CustomFieldType
    {
        return \App\Modules\Tenant\Contacts\Domain\Enums\CustomFieldType::tryFrom($this->uitype)
            ?? \App\Modules\Tenant\Contacts\Domain\Enums\CustomFieldType::SYSTEM;
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

    public function getAllowMultipleFiles(): bool
    {
        return $this->allowMultipleFiles;
    }

    public function getAcceptableFileTypes(): ?string
    {
        return $this->acceptableFileTypes;
    }

    public function isMandatory(): bool
    {
        return str_contains($this->typeofdata, '~M');
    }

    public function isVisible(): bool
    {
        return $this->presence !== 1 && $this->displayType !== 2;
    }

    public function isEditable(): bool
    {
        return $this->displayType === 1;
    }

    public function isCustomField(): bool
    {
        return $this->generatedType === 2;
    }

    public function isReadonly(): bool
    {
        return $this->displayType === 3 || $this->displayType === 4;
    }

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
