<?php

namespace App\Modules\Core\VtigerModules\Contracts;

use App\Modules\Core\VtigerModules\Domain\ModuleDefinition;
use Illuminate\Support\Collection;

/**
 * ModuleRegistryInterface
 * 
 * Public API for accessing vtiger module metadata.
 * 
 * This is the main entry point for consumers who need module information.
 * Implementations should provide caching for performance.
 * 
 * Usage example:
 * 
 * $registry = app(ModuleRegistryInterface::class);
 * $contacts = $registry->get('Contacts');
 * $fields = $contacts->fields();
 */
interface ModuleRegistryInterface
{
    /**
     * Get all modules
     * 
     * @return Collection<ModuleDefinition>
     */
    public function all(): Collection;

    /**
     * Get a specific module by name
     * 
     * @param string $moduleName Module name (e.g., "Contacts", "Accounts")
     * @return ModuleDefinition
     * @throws \InvalidArgumentException if module not found
     */
    public function get(string $moduleName): ModuleDefinition;

    /**
     * Check if a module exists
     * 
     * @param string $moduleName Module name
     * @return bool
     */
    public function has(string $moduleName): bool;

    /**
     * Get only active (visible) modules
     * 
     * Filters modules where presence = 0
     * 
     * @return Collection<ModuleDefinition>
     */
    public function getActive(): Collection;

    /**
     * Refresh the registry cache
     * 
     * Forces reload of all module metadata from database
     * 
     * @return void
     */
    public function refresh(): void;
}
