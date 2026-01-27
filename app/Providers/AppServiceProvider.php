<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register custom Blade directives for permission checks
        \Illuminate\Support\Facades\Blade::directive('canModule', function ($expression) {
            return "<?php if(app(\App\Modules\Tenant\Users\Domain\Services\PermissionService::class)->hasPermission(auth('tenant')->id(), {$expression})): ?>";
        });

        \Illuminate\Support\Facades\Blade::directive('cannotModule', function ($expression) {
            return "<?php if(!app(\App\Modules\Tenant\Users\Domain\Services\PermissionService::class)->hasPermission(auth('tenant')->id(), {$expression})): ?>";
        });

        \Illuminate\Support\Facades\Blade::directive('endcanModule', function () {
            return "<?php endif; ?>";
        });

        \Illuminate\Support\Facades\Blade::directive('endcannotModule', function () {
            return "<?php endif; ?>";
        });

        \Illuminate\Support\Facades\Blade::directive('canTool', function ($expression) {
            return "<?php if(app(\App\Modules\Tenant\Users\Domain\Services\PermissionService::class)->hasToolPermission(auth('tenant')->id(), {$expression})): ?>";
        });

        \Illuminate\Support\Facades\Blade::directive('endcanTool', function () {
            return "<?php endif; ?>";
        });
    }
}
