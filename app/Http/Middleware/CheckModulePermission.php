<?php

namespace App\Http\Middleware;

use App\Modules\Tenant\Users\Domain\Services\PermissionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModulePermission
{
    protected PermissionService $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $module, string $action = 'view'): Response
    {
        $user = auth('tenant')->user();

        if (!$user) {
            abort(401, 'Unauthorized');
        }

        if (!$this->permissionService->hasPermission($user->id, $module, $action)) {
            abort(403, 'You do not have permission to ' . $action . ' ' . $module);
        }

        return $next($request);
    }
}
