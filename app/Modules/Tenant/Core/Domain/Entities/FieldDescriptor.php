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
        public readonly array $picklistValues = [],
        public readonly ?string $helpInfo = null,
        public readonly bool $allowMultipleFiles = false,
        public readonly ?string $acceptableFileTypes = null,
        public readonly bool $readonly = false,
    ) {
    }

    public function getLabel(string $moduleName = 'Vtiger'): string
    {
        if (function_exists('vtranslate')) {
            return vtranslate($this->label, $moduleName);
        }
        return $this->label;
    }
}
