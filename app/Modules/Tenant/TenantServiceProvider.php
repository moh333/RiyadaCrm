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

        $this->app->bind(
            \App\Modules\Tenant\ModComments\Domain\Repositories\CommentRepositoryInterface::class,
            \App\Modules\Tenant\ModComments\Infrastructure\EloquentCommentRepository::class
        );

        $this->app->bind(
            \App\Modules\Tenant\Users\Domain\Repositories\UserRepositoryInterface::class,
            \App\Modules\Tenant\Users\Infrastructure\Repositories\EloquentUserRepository::class
        );
    }

    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/Presentation/Views', 'tenant');
        $this->loadViewsFrom(__DIR__ . '/Users/Presentation/Views', 'tenant');
        $this->loadTranslationsFrom(__DIR__ . '/Resources/Lang', 'tenant');
    }
}
