<?php

namespace App\Modules\Tenant\Contacts\Domain;

use App\Modules\Tenant\Contacts\Domain\Enums\CustomFieldType;

/**
 * CustomField Value Object
 * 
 * Represents a custom field definition in vtiger_field table
 */
class CustomField
{
    private function __construct(
        private int $fieldId,
        private int $tabId,
        private string $columnName,
        private string $tableName,
        private CustomFieldType $uitype,
        private string $fieldName,
        private string $labelEn,
        private ?string $labelAr,
        private bool $readonly,
        private int $presence,
        private int $generatedType,
        private ?string $defaultValue,
        private ?int $maximumLength,
        private ?int $sequence,
        private ?int $block,
        private int $displayType,
        private string $typeOfData,
        private bool $quickCreate,
        private ?int $quickCreateSequence,
        private bool $massEditable,
        private ?string $helpInfo,
        private bool $summaryField,
        private bool $isUnique,
        private bool $allowMultipleFiles = false,
        private ?string $acceptableFileTypes = null,
    ) {
    }

    /**
     * Create new custom field definition
     */
    public static function create(
        int $fieldId,
        int $tabId,
        string $columnName,
        string $tableName,
        CustomFieldType $uitype,
        string $fieldName,
        string $labelEn,
        ?string $labelAr,
        int $block,
        string $typeOfData = 'V~O',
        ?string $defaultValue = null,
        ?int $maximumLength = null,
        bool $allowMultipleFiles = false,
        ?string $acceptableFileTypes = null,
    ): self {
        return new self(
            fieldId: $fieldId,
            tabId: $tabId,
            columnName: $columnName,
            tableName: $tableName,
            uitype: $uitype,
            fieldName: $fieldName,
            labelEn: $labelEn,
            labelAr: $labelAr,
            readonly: false,
            presence: 2,
            generatedType: 2,
            defaultValue: $defaultValue,
            maximumLength: $maximumLength ?? $uitype->columnLength(),
            sequence: null,
            block: $block,
            displayType: 1,
            typeOfData: $typeOfData,
            quickCreate: false,
            quickCreateSequence: null,
            massEditable: true,
            helpInfo: null,
            summaryField: false,
            isUnique: false,
            allowMultipleFiles: $allowMultipleFiles,
            acceptableFileTypes: $acceptableFileTypes,
        );
    }

    /**
     * Reconstruct from database
     */
    public static function fromDatabase(array $data): self
    {
        return new self(
            fieldId: $data['fieldid'],
            tabId: $data['tabid'],
            columnName: $data['columnname'],
            tableName: $data['tablename'],
            uitype: CustomFieldType::tryFrom((int) $data['uitype']) ?? CustomFieldType::SYSTEM,
            fieldName: $data['fieldname'],
            labelEn: $data['fieldlabel_en'] ?? $data['fieldlabel'] ?? '', // Fallback for transition
            labelAr: $data['fieldlabel_ar'] ?? null,
            readonly: (bool) $data['readonly'],
            presence: $data['presence'],
            defaultValue: $data['defaultvalue'],
            maximumLength: $data['maximumlength'],
            sequence: $data['sequence'],
            generatedType: (int) $data['generatedtype'],
            block: $data['block'],
            displayType: $data['displaytype'],
            typeOfData: $data['typeofdata'],
            quickCreate: (bool) $data['quickcreate'],
            quickCreateSequence: $data['quickcreatesequence'],
            massEditable: (bool) $data['masseditable'],
            helpInfo: $data['helpinfo'],
            summaryField: (bool) $data['summaryfield'],
            isUnique: (bool) $data['isunique'],
            allowMultipleFiles: (bool) ($data['allow_multiple_files'] ?? false),
            acceptableFileTypes: $data['acceptable_file_types'] ?? null,
        );
    }

    // Getters
    public function getFieldId(): int
    {
        return $this->fieldId;
    }
    public function getTabId(): int
    {
        return $this->tabId;
    }
    public function getColumnName(): string
    {
        return $this->columnName;
    }
    public function getTableName(): string
    {
        return $this->tableName;
    }
    public function getUitype(): CustomFieldType
    {
        return $this->uitype;
    }
    public function getFieldName(): string
    {
        return $this->fieldName;
    }
    public function getLabelEn(): string
    {
        return $this->labelEn;
    }
    public function getLabelAr(): ?string
    {
        return $this->labelAr;
    }
    public function getFieldLabel(): string
    {
        return $this->labelEn;
    } // Alias for backward compatibility if needed

    public function isReadonly(): bool
    {
        return $this->readonly;
    }
    public function getPresence(): int
    {
        return $this->presence;
    }
    public function getDefaultValue(): ?string
    {
        return $this->defaultValue;
    }
    public function getMaximumLength(): ?int
    {
        return $this->maximumLength;
    }
    public function getSequence(): ?int
    {
        return $this->sequence;
    }
    public function getBlock(): ?int
    {
        return $this->block;
    }
    public function getDisplayType(): int
    {
        return $this->displayType;
    }
    public function getGeneratedType(): int
    {
        return $this->generatedType;
    }
    public function getTypeOfData(): string
    {
        return $this->typeOfData;
    }
    public function isQuickCreate(): bool
    {
        return $this->quickCreate;
    }
    public function getQuickCreateSequence(): ?int
    {
        return $this->quickCreateSequence;
    }
    public function isMassEditable(): bool
    {
        return $this->massEditable;
    }
    public function getHelpInfo(): ?string
    {
        return $this->helpInfo;
    }
    public function isSummaryField(): bool
    {
        return $this->summaryField;
    }

    public function getAllowMultipleFiles(): bool
    {
        return $this->allowMultipleFiles;
    }

    public function getAcceptableFileTypes(): ?string
    {
        return $this->acceptableFileTypes;
    }

    public function setLabelEn(string $label): void
    {
        $this->labelEn = $label;
    }
    public function setLabelAr(?string $label): void
    {
        $this->labelAr = $label;
    }

    /**
     * Update field metadata
     */
    public function updateMetadata(
        ?string $labelEn = null,
        ?string $labelAr = null,
        ?int $sequence = null,
        ?bool $quickCreate = null,
        ?string $helpInfo = null,
        ?bool $allowMultipleFiles = null,
        ?string $acceptableFileTypes = null,
    ): void {
        if ($labelEn !== null)
            $this->labelEn = $labelEn;
        if ($labelAr !== null)
            $this->labelAr = $labelAr;
        if ($sequence !== null)
            $this->sequence = $sequence;
        if ($quickCreate !== null)
            $this->quickCreate = $quickCreate;
        if ($helpInfo !== null)
            $this->helpInfo = $helpInfo;
        if ($allowMultipleFiles !== null)
            $this->allowMultipleFiles = $allowMultipleFiles;
        if ($acceptableFileTypes !== null)
            $this->acceptableFileTypes = $acceptableFileTypes;
    }

    public function setBlock(int $block): void
    {
        $this->block = $block;
    }
    public function setTypeOfData(string $typeOfData): void
    {
        $this->typeOfData = $typeOfData;
    }
    public function setUitype(CustomFieldType $uitype): void
    {
        $this->uitype = $uitype;
    }
    public function setMaximumLength(?int $maximumLength): void
    {
        $this->maximumLength = $maximumLength;
    }
    public function setDefaultValue(?string $defaultValue): void
    {
        $this->defaultValue = $defaultValue;
    }

    public function toArray(): array
    {
        return [
            'fieldid' => $this->fieldId,
            'tabid' => $this->tabId,
            'columnname' => $this->columnName,
            'tablename' => $this->tableName,
            'generatedtype' => $this->generatedType,
            'uitype' => $this->uitype->value,
            'fieldname' => $this->fieldName,
            'fieldlabel_en' => $this->labelEn,
            'fieldlabel_ar' => $this->labelAr,
            'readonly' => $this->readonly ? 1 : 0,
            'presence' => $this->presence,
            'defaultvalue' => $this->defaultValue,
            'maximumlength' => $this->maximumLength,
            'sequence' => $this->sequence,
            'block' => $this->block,
            'displaytype' => $this->displayType,
            'typeofdata' => $this->typeOfData,
            'quickcreate' => $this->quickCreate ? 1 : 0,
            'quickcreatesequence' => $this->quickCreateSequence,
            'info_type' => 'BAS',
            'masseditable' => $this->massEditable ? 1 : 0,
            'helpinfo' => $this->helpInfo,
            'summaryfield' => $this->summaryField ? 1 : 0,
            'headerfield' => 0,
            'isunique' => $this->isUnique ? 1 : 0,
            'allow_multiple_files' => $this->allowMultipleFiles ? 1 : 0,
            'acceptable_file_types' => $this->acceptableFileTypes,
        ];
    }
}
