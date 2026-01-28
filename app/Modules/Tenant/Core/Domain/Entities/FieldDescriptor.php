<?php

namespace App\Modules\Tenant\Core\Domain\Entities;

class FieldDescriptor
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $column,
        public readonly string $label,
        public readonly string $table,
        public readonly int $uiType,
        public readonly string $typeofData,
        public readonly bool $isMandatory,
        public readonly bool $isCustomField,
        public readonly int $blockId,
        public readonly int $presence,
        public readonly ?string $blockLabel = null,
        public readonly array $picklistValues = []
    ) {
    }
}
