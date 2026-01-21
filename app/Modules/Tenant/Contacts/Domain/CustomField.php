<?php

namespace App\Modules\Tenant\Contacts\Domain;

use App\Modules\Tenant\Contacts\Domain\Enums\CustomFieldType;

/**
 * CustomField Value Object
 * 
 * Represents a custom field definition in vtiger_field table
 * This is the metadata about a field, not the actual data
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
        private string $fieldLabel,
        private bool $readonly,
        private int $presence,
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
        string $fieldLabel,
        int $block,
        string $typeOfData = 'V~O',
        ?string $defaultValue = null,
        ?int $maximumLength = null,
    ): self {
        return new self(
            fieldId: $fieldId,
            tabId: $tabId,
            columnName: $columnName,
            tableName: $tableName,
            uitype: $uitype,
            fieldName: $fieldName,
            fieldLabel: $fieldLabel,
            readonly: false,
            presence: 2, // 0=active everywhere, 1=inactive, 2=active but not in quick create
            defaultValue: $defaultValue,
            maximumLength: $maximumLength ?? $uitype->columnLength(),
            sequence: null,
            block: $block,
            displayType: 1, // 1=visible, 2=hidden, 3=readonly, 4=readonly in edit
            typeOfData: $typeOfData,
            quickCreate: false,
            quickCreateSequence: null,
            massEditable: true,
            helpInfo: null,
            summaryField: false,
            isUnique: false,
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
            uitype: CustomFieldType::from((int) $data['uitype']),
            fieldName: $data['fieldname'],
            fieldLabel: $data['fieldlabel'],
            readonly: (bool) $data['readonly'],
            presence: $data['presence'],
            defaultValue: $data['defaultvalue'],
            maximumLength: $data['maximumlength'],
            sequence: $data['sequence'],
            block: $data['block'],
            displayType: $data['displaytype'],
            typeOfData: $data['typeofdata'],
            quickCreate: (bool) $data['quickcreate'],
            quickCreateSequence: $data['quickcreatesequence'],
            massEditable: (bool) $data['masseditable'],
            helpInfo: $data['helpinfo'],
            summaryField: (bool) $data['summaryfield'],
            isUnique: (bool) $data['isunique'],
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
    public function getFieldLabel(): string
    {
        return $this->fieldLabel;
    }
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
    public function isUnique(): bool
    {
        return $this->isUnique;
    }

    /**
     * Check if field is mandatory based on typeofdata
     */
    public function isMandatory(): bool
    {
        return str_contains($this->typeOfData, '~M');
    }

    /**
     * Update field metadata
     */
    public function updateMetadata(
        ?string $fieldLabel = null,
        ?int $sequence = null,
        ?bool $quickCreate = null,
        ?string $helpInfo = null,
    ): void {
        if ($fieldLabel !== null) {
            $this->fieldLabel = $fieldLabel;
        }
        if ($sequence !== null) {
            $this->sequence = $sequence;
        }
        if ($quickCreate !== null) {
            $this->quickCreate = $quickCreate;
        }
        if ($helpInfo !== null) {
            $this->helpInfo = $helpInfo;
        }
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

    /**
     * Convert to array for database storage
     */
    public function toArray(): array
    {
        return [
            'fieldid' => $this->fieldId,
            'tabid' => $this->tabId,
            'columnname' => $this->columnName,
            'tablename' => $this->tableName,
            'generatedtype' => 2, // 1=system, 2=custom
            'uitype' => $this->uitype->value,
            'fieldname' => $this->fieldName,
            'fieldlabel' => $this->fieldLabel,
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
        ];
    }
}
