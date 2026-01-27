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
            $centralDomain = parse_url(config('app.url'), PHP_URL_HOST);
            if ($request->getHost() !== $centralDomain) {
                return redirect(config('app.url'));
            }
            throw $e;
        }
    }
}
