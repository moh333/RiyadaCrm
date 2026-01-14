<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\TenancyServiceProvider::class,
    App\Providers\RouteServiceProvider::class,
    App\Modules\Landing\LandingServiceProvider::class,
    App\Modules\Master\MasterServiceProvider::class,
    App\Modules\Tenant\TenantServiceProvider::class,
];
