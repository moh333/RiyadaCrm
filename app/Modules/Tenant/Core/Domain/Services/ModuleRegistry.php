<?php

namespace App\Modules\Tenant\Core\Domain\Services;

use App\Modules\Tenant\Core\Domain\Repositories\ModuleMetadataRepositoryInterface;

class ModuleRegistry
{
    protected array $cache = [];

    public function __construct(
        protected ModuleMetadataRepositoryInterface $repo
    ) {
    }

    public function all(): array
    {
        return $this->repo->getAllModules();
    }

    public function get(string $name): ?object
    {
        if (!isset($this->cache[$name])) {
            $module = $this->repo->getModuleByName($name);
            if (!$module)
                return null;

            $this->cache[$name] = (object) [
                'metadata' => $module,
                'fields' => fn() => $this->repo->getFieldsByModule($module->id),
                'relations' => fn() => $this->repo->getRelationshipsByModule($module->name)
            ];
        }
        return $this->cache[$name];
    }
}
