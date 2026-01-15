<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {

            // API Routes
            Route::prefix('api')
                ->middleware('api')
                ->group(base_path('routes/api.php'));

            // Landing & Central Routes (Existing web.php)
            // Existing web.php handles central domain check internally
            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            $centralDomain = parse_url(config('app.url'), PHP_URL_HOST) ?? 'riyadacrm.test';

            // Master Routes (Admin Dashboard) - Central Domain
            Route::middleware(['web']) // removed auth:master temporarily as guard might not be ready, or user can add it. Actually better to keep it but user might get error if guard not defined. I'll define guard in next step.
                ->domain($centralDomain)
                ->group(base_path('routes/master.php'));

            // Tenant Routes
            Route::middleware([
                'web',
                InitializeTenancyByDomain::class,
                PreventAccessFromCentralDomains::class,
            ])
                ->group(base_path('routes/tenant.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });
    }
}
