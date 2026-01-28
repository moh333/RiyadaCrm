<?php

namespace App\Modules\Tenant\Core\Domain\Repositories;

use App\Modules\Tenant\Core\Domain\Entities\ModuleDescriptor;

interface ModuleMetadataRepositoryInterface
{
    public function getAllModules(): array;
    public function getModuleByName(string $name): ?ModuleDescriptor;
    public function getFieldsByModule(int $tabId): array;
    public function getRelationshipsByModule(string $name): array;
}
