<?php

namespace App\Modules\Tenant\Contacts;

use Illuminate\Support\ServiceProvider;
use App\Modules\Tenant\Contacts\Domain\Repositories\ContactRepositoryInterface;
use App\Modules\Tenant\Contacts\Infrastructure\EloquentContactRepository;
use App\Modules\Tenant\Contacts\Application\UseCases\CreateContactUseCase;
use App\Modules\Tenant\Contacts\Application\UseCases\UpdateContactUseCase;
use App\Modules\Tenant\Contacts\Application\UseCases\DeleteContactUseCase;
use App\Modules\Tenant\Contacts\Application\UseCases\EnablePortalAccessUseCase;
use App\Modules\Tenant\Contacts\Application\UseCases\DisablePortalAccessUseCase;
use App\Modules\Tenant\Contacts\Application\UseCases\LinkContactToAccountUseCase;
use App\Modules\Tenant\Contacts\Application\UseCases\UnlinkContactFromAccountUseCase;
use App\Modules\Tenant\Contacts\Application\UseCases\TransferContactOwnershipUseCase;
use App\Modules\Tenant\Contacts\Application\UseCases\UploadContactImageUseCase;
use App\Modules\Tenant\Contacts\Application\UseCases\ConvertLeadToContactUseCase;

class ContactsModuleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind repository interface to implementation
        $this->app->bind(
            ContactRepositoryInterface::class,
            EloquentContactRepository::class
        );

        // Register use cases as singletons
        $this->app->singleton(CreateContactUseCase::class);
        $this->app->singleton(UpdateContactUseCase::class);
        $this->app->singleton(DeleteContactUseCase::class);
        $this->app->singleton(EnablePortalAccessUseCase::class);
        $this->app->singleton(DisablePortalAccessUseCase::class);
        $this->app->singleton(LinkContactToAccountUseCase::class);
        $this->app->singleton(UnlinkContactFromAccountUseCase::class);
        $this->app->singleton(TransferContactOwnershipUseCase::class);
        $this->app->singleton(UploadContactImageUseCase::class);
        $this->app->singleton(ConvertLeadToContactUseCase::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load module views - use unique namespace if possible, but 'tenant' might be shared
        $this->loadViewsFrom(__DIR__ . '/Presentation/Views', 'contacts_module');

        // Load module translations - use unique namespace to avoid collisions
        $this->loadTranslationsFrom(__DIR__ . '/Resources/Lang', 'contacts');
    }
}
