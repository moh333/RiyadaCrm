<?php

namespace App\Modules\Master;

use App\Modules\Master\Domain\Repositories\MasterRepositoryInterface;
use App\Modules\Master\Infrastructure\Repositories\EloquentMasterRepository;
use App\Modules\Master\Domain\Repositories\TenantManagerRepositoryInterface;
use App\Modules\Master\Infrastructure\Repositories\EloquentTenantManagerRepository;
use Illuminate\Support\ServiceProvider;

class MasterServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            MasterRepositoryInterface::class,
            EloquentMasterRepository::class
        );
        $this->app->bind(
            TenantManagerRepositoryInterface::class,
            EloquentTenantManagerRepository::class
        );
    }

    public function boot()
    {
        // Load module specific migrations or views if needed
        $this->loadViewsFrom(__DIR__ . '/Presentation/Views', 'master');
    }
}
