<?php

namespace App\Modules\Landing;

use Illuminate\Support\ServiceProvider;

class LandingServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Bindings
    }

    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/Presentation/Views', 'landing');
    }

}
