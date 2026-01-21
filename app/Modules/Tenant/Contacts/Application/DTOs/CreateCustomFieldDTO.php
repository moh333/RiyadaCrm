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
        public readonly string $fieldLabel,
        public readonly CustomFieldType $uitype,
        public readonly int $block,
        public readonly string $typeOfData = 'V~O', // V=varchar, O=optional, M=mandatory
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
            fieldLabel: $data['fieldlabel'],
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
     * vtiger convention: cf_{fieldname}
     */
    public function getColumnName(): string
    {
        // If it starts with cf_, use it as is, otherwise prefix it
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
        // vtiger convention: vtiger_{modulename}cf
        $name = strtolower($this->moduleName);

        // Special case for some modules if needed, but standard is:
        return "vtiger_{$name}cf";
    }
}
