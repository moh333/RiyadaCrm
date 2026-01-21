<?php

namespace App\Modules\Tenant\Contacts\Application\DTOs;

use App\Modules\Tenant\Contacts\Domain\Enums\CustomFieldType;

/**
 * CreateCustomFieldDTO
 * 
 * Data Transfer Object for creating a custom field
 */
class CreateCustomFieldDTO
{
    public function __construct(
        public readonly int $tabId,
        public readonly string $moduleName,
        public readonly string $fieldName,
        public readonly string $fieldLabelEn,
        public readonly ?string $fieldLabelAr,
        public readonly CustomFieldType $uitype,
        public readonly int $block,
        public readonly string $typeOfData = 'V~O',
        public readonly bool $quickCreate = false,
        public readonly ?string $helpInfo = null,
        public readonly ?string $defaultValue = null,
        public readonly array $picklistValues = [],
        public readonly ?int $length = null,
    ) {
    }

    /**
     * Create from request data
     */
    public static function fromRequest(array $data): self
    {
        return new self(
            tabId: (int) $data['tabid'],
            moduleName: $data['module_name'],
            fieldName: $data['fieldname'],
            fieldLabelEn: $data['fieldlabel_en'],
            fieldLabelAr: $data['fieldlabel_ar'] ?? null,
            uitype: CustomFieldType::from((int) $data['uitype']),
            block: (int) $data['block'],
            typeOfData: $data['typeofdata'] ?? 'V~O',
            quickCreate: (bool) ($data['quickcreate'] ?? false),
            helpInfo: $data['helpinfo'] ?? null,
            defaultValue: $data['defaultvalue'] ?? null,
            picklistValues: isset($data['picklist_values'])
            ? array_filter(array_map('trim', explode("\n", $data['picklist_values'])))
            : [],
            length: isset($data['length']) ? (int) $data['length'] : null,
        );
    }

    /**
     * Generate column name from field name
     */
    public function getColumnName(): string
    {
        if (str_starts_with(strtolower($this->fieldName), 'cf_')) {
            return strtolower($this->fieldName);
        }
        return 'cf_' . strtolower(preg_replace('/[^a-z0-9_]/i', '', $this->fieldName));
    }

    /**
     * Get table name for custom fields
     */
    public function getTableName(): string
    {
        return "vtiger_" . strtolower($this->moduleName) . "cf";
    }
}
