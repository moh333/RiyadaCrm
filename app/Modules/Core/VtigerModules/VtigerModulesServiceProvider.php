<?php

namespace App\Modules\Core\VtigerModules;

use App\Modules\Core\VtigerModules\Contracts\ModuleMetadataRepositoryInterface;
use App\Modules\Core\VtigerModules\Contracts\ModuleRegistryInterface;
use App\Modules\Core\VtigerModules\Infrastructure\VtigerModuleMetadataRepository;
use App\Modules\Core\VtigerModules\Services\ModuleRegistry;
use Illuminate\Support\ServiceProvider;

/**
 * VtigerModulesServiceProvider
 * 
 * Laravel service provider for the Vtiger Module Management Engine.
 * 
 * Registers:
 * - ModuleMetadataRepositoryInterface → VtigerModuleMetadataRepository
 * - ModuleRegistryInterface → ModuleRegistry (singleton)
 * 
 * Usage in your code:
 * 
 * $registry = app(ModuleRegistryInterface::class);
 * $contacts = $registry->get('Contacts');
 */
class VtigerModulesServiceProvider extends ServiceProvider
{
    /**
     * Register services
     * 
     * @return void
     */
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../../../../config/vtiger-modules.php',
            'vtiger-modules'
        );

        // Bind repository interface to implementation
        $this->app->bind(
            ModuleMetadataRepositoryInterface::class,
            VtigerModuleMetadataRepository::class
        );

        // Bind registry interface to implementation (singleton for caching)
        $this->app->singleton(
            ModuleRegistryInterface::class,
            ModuleRegistry::class
        );
    }

    /**
     * Bootstrap services
     * 
     * @return void
     */
    public function boot(): void
    {
        // Publish configuration file
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../../../config/vtiger-modules.php' => config_path('vtiger-modules.php'),
            ], 'vtiger-modules-config');
        }
    }
}
