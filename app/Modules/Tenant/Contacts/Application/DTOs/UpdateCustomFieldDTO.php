<?php

namespace App\Modules\Tenant\Contacts\Application\DTOs;

/**
 * UpdateCustomFieldDTO
 * 
 * Data Transfer Object for updating a custom field
 */
class UpdateCustomFieldDTO
{
    public function __construct(
        public readonly int $fieldId,
        public readonly string $fieldLabel,
        public readonly int $block,
        public readonly string $typeOfData = 'V~O',
        public readonly bool $quickCreate = false,
        public readonly ?string $helpInfo = null,
        public readonly ?string $defaultValue = null,
        public readonly array $picklistValues = [],
        public readonly ?int $uitype = null,
    ) {
    }

    /**
     * Create from request data
     */
    public static function fromRequest(int $id, array $data): self
    {
        return new self(
            fieldId: $id,
            fieldLabel: $data['fieldlabel'],
            block: (int) $data['block'],
            typeOfData: $data['typeofdata'] ?? 'V~O',
            quickCreate: (bool) ($data['quickcreate'] ?? false),
            helpInfo: $data['helpinfo'] ?? null,
            defaultValue: $data['defaultvalue'] ?? null,
            picklistValues: isset($data['picklist_values'])
            ? array_filter(array_map('trim', explode("\n", $data['picklist_values'])))
            : [],
            uitype: isset($data['uitype']) ? (int) $data['uitype'] : null,
        );
    }
}
