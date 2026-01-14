<?php

namespace App\Modules\Tenant;

use App\Modules\Tenant\Domain\Repositories\TenantRepositoryInterface;
use App\Modules\Tenant\Infrastructure\Repositories\EloquentTenantRepository;
use Illuminate\Support\ServiceProvider;

class TenantServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            TenantRepositoryInterface::class,
            EloquentTenantRepository::class
        );
    }

    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/Presentation/Views', 'tenant');
    }
}
