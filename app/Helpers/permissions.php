<?php

if (!function_exists('canModule')) {
    /**
     * Check if the current user has permission for a module action
     *
     * @param string $moduleName
     * @param string $action
     * @return bool
     */
    function canModule(string $moduleName, string $action): bool
    {
        try {
            $user = auth('tenant')->user();
            if (!$user) {
                return false;
            }

            $permissionService = app(\App\Modules\Tenant\Users\Domain\Services\PermissionService::class);
            return $permissionService->hasPermission($user->id, $moduleName, $action);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Permission check failed: ' . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('canTool')) {
    /**
     * Check if the current user has permission for a module tool
     *
     * @param string $moduleName
     * @param string $tool
     * @return bool
     */
    function canTool(string $moduleName, string $tool): bool
    {
        try {
            $user = auth('tenant')->user();
            if (!$user) {
                return false;
            }

            $permissionService = app(\App\Modules\Tenant\Users\Domain\Services\PermissionService::class);
            return $permissionService->hasToolPermission($user->id, $moduleName, $tool);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Tool permission check failed: ' . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('canField')) {
    /**
     * Check if the current user has permission for a field
     *
     * @param int $fieldId
     * @param string $access
     * @return bool
     */
    function canField(int $fieldId, string $access = 'view'): bool
    {
        try {
            $user = auth('tenant')->user();
            if (!$user) {
                return false;
            }

            $permissionService = app(\App\Modules\Tenant\Users\Domain\Services\PermissionService::class);
            return $permissionService->hasFieldPermission($user->id, $fieldId, $access);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Field permission check failed: ' . $e->getMessage());
            return false;
        }
    }
}
