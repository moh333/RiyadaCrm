<?php

namespace App\Modules\Tenant\Reports;

use Illuminate\Support\ServiceProvider;

class ReportsModuleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind repositories if any, for now we use models directly or a service
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/Presentation/Views', 'reports');
        $this->loadTranslationsFrom(__DIR__ . '/Resources/Lang', 'reports');

        // Define module prefix for namespacing routes or similar if needed
    }
}
