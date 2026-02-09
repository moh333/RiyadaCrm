<?php

namespace App\Modules\Core\VtigerModules\Services;

use App\Modules\Core\VtigerModules\Contracts\ModuleMetadataRepositoryInterface;
use App\Modules\Core\VtigerModules\Contracts\ModuleRegistryInterface;
use App\Modules\Core\VtigerModules\Domain\ModuleDefinition;
use Illuminate\Support\Collection;

/**
 * ModuleRegistry
 * 
 * Cached registry service for fast module metadata access.
 * 
 * This service sits between consumers and the repository, providing:
 * - In-memory caching of loaded modules
 * - Lazy loading (load on first access)
 * - Fast lookups by module name
 * - Cache invalidation support
 * 
 * Usage:
 * 
 * $registry = app(ModuleRegistryInterface::class);
 * $contacts = $registry->get('Contacts'); // First call loads from DB
 * $contacts = $registry->get('Contacts'); // Second call uses cache
 */
class ModuleRegistry implements ModuleRegistryInterface
{
    /**
     * In-memory cache of loaded modules
     * 
     * @var Collection<string, ModuleDefinition>|null
     */
    private ?Collection $modules = null;

    public function __construct(
        private ModuleMetadataRepositoryInterface $repository
    ) {
    }

    /**
     * Get all modules
     * 
     * Loads from database on first call, then caches in memory
     * 
     * @return Collection<ModuleDefinition>
     */
    public function all(): Collection
    {
        if ($this->modules === null) {
            $this->loadModules();
        }

        return $this->modules;
    }

    /**
     * Get a specific module by name
     * 
     * @param string $moduleName Module name (e.g., "Contacts")
     * @return ModuleDefinition
     * @throws \InvalidArgumentException if module not found
     */
    public function get(string $moduleName): ModuleDefinition
    {
        if ($this->modules === null) {
            $this->loadModules();
        }

        $module = $this->modules->get($moduleName);

        if (!$module) {
            // Fallback: try to find by base table name (e.g. vtiger_contactdetails)
            $module = $this->modules->first(fn(ModuleDefinition $m) => $m->getBaseTable() === $moduleName);
        }

        if (!$module) {
            throw new \InvalidArgumentException("Module '{$moduleName}' not found");
        }

        return $module;
    }

    /**
     * Check if a module exists
     * 
     * @param string $moduleName Module name
     * @return bool
     */
    public function has(string $moduleName): bool
    {
        if ($this->modules === null) {
            $this->loadModules();
        }

        if ($this->modules->has($moduleName)) {
            return true;
        }

        // Fallback: check by base table name
        return $this->modules->contains(fn(ModuleDefinition $m) => $m->getBaseTable() === $moduleName);
    }

    /**
     * Get only active (visible) modules
     * 
     * Filters modules where presence = 0
     * 
     * @return Collection<ModuleDefinition>
     */
    public function getActive(): Collection
    {
        return $this->all()->filter(fn(ModuleDefinition $module) => $module->isActive());
    }

    /**
     * Refresh the registry cache
     * 
     * Forces reload of all module metadata from database
     * 
     * @return void
     */
    public function refresh(): void
    {
        $this->modules = null;
        $this->loadModules();
    }

    /**
     * Load all modules from repository and cache them
     * 
     * @return void
     */
    private function loadModules(): void
    {
        $modules = $this->repository->loadAllModules();

        // Index by module name for fast lookups
        $this->modules = $modules->keyBy(fn(ModuleDefinition $module) => $module->getName());
    }

    /**
     * Get modules by custom filter
     * 
     * @param callable $filter Filter function
     * @return Collection<ModuleDefinition>
     */
    public function filter(callable $filter): Collection
    {
        return $this->all()->filter($filter);
    }

    /**
     * Get custom modules only
     * 
     * @return Collection<ModuleDefinition>
     */
    public function getCustomModules(): Collection
    {
        return $this->all()->filter(fn(ModuleDefinition $module) => $module->isCustom());
    }

    /**
     * Get standard (non-custom) modules only
     * 
     * @return Collection<ModuleDefinition>
     */
    public function getStandardModules(): Collection
    {
        return $this->all()->filter(fn(ModuleDefinition $module) => !$module->isCustom());
    }
}
