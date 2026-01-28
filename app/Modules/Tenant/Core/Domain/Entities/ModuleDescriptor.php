<?php

namespace App\Modules\Tenant\Core\Domain\Entities;

class ModuleDescriptor
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $label,
        public readonly string $baseTable,
        public readonly string $baseTableIndex,
        public readonly bool $isEntity,
        public readonly int $presence,
        public readonly string $appName = 'OTHERS',
        public readonly ?string $customFieldTable = null
    ) {
    }
}
