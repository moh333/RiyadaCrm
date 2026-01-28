<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Blade;
use App\Listeners\AuthEventListener;

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
        // Register Auth Event Listener
        Event::subscribe(AuthEventListener::class);

        // Register custom Blade directives for permission checks
        Blade::directive('canModule', function ($expression) {
            return "<?php if(app(\App\Modules\Tenant\Users\Domain\Services\PermissionService::class)->hasPermission(auth('tenant')->id(), {$expression})): ?>";
        });

        Blade::directive('cannotModule', function ($expression) {
            return "<?php if(!app(\App\Modules\Tenant\Users\Domain\Services\PermissionService::class)->hasPermission(auth('tenant')->id(), {$expression})): ?>";
        });

        Blade::directive('endcanModule', function () {
            return "<?php endif; ?>";
        });

        Blade::directive('endcannotModule', function () {
            return "<?php endif; ?>";
        });

        Blade::directive('canTool', function ($expression) {
            return "<?php if(app(\App\Modules\Tenant\Users\Domain\Services\PermissionService::class)->hasToolPermission(auth('tenant')->id(), {$expression})): ?>";
        });

        Blade::directive('endcanTool', function () {
            return "<?php endif; ?>";
        });
    }
}
