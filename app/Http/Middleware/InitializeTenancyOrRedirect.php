<?php

namespace App\Http\Middleware;

use Closure;
use Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedOnDomainException;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain as BaseInitialize;

class InitializeTenancyOrRedirect
{
    public function handle($request, Closure $next)
    {
        try {
            return app(BaseInitialize::class)->handle($request, $next);
        } catch (TenantCouldNotBeIdentifiedOnDomainException $e) {
            return redirect(env("APP_URL"));
        }
    }
}
