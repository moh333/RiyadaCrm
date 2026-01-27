<?php

namespace App\Modules\Tenant\Users\Domain\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class PermissionService
{
    /**
     * Check if user has permission for a specific action on a module
     *
     * @param int $userId
     * @param string $moduleName
     * @param string $action (view, create, edit, delete)
     * @return bool
     */
    public function hasPermission(int $userId, string $moduleName, string $action): bool
    {
        $cacheKey = "user_permission_{$userId}_{$moduleName}_{$action}";

        // Check if cached
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // Get user's role
        $userRole = DB::connection('tenant')
            ->table('vtiger_user2role')
            ->where('userid', $userId)
            ->first();

        if (!$userRole) {
            Cache::put($cacheKey, false, 300);
            return false;
        }

        // Get profile(s) for this role
        $profiles = DB::connection('tenant')
            ->table('vtiger_role2profile')
            ->where('roleid', $userRole->roleid)
            ->pluck('profileid');

        if ($profiles->isEmpty()) {
            Cache::put($cacheKey, false, 300);
            return false;
        }

        // Get module tabid
        $module = DB::connection('tenant')
            ->table('vtiger_tab')
            ->where('name', $moduleName)
            ->first();

        if (!$module) {
            Cache::put($cacheKey, false, 300);
            return false;
        }

        // Check module access (view permission)
        $hasModuleAccess = DB::connection('tenant')
            ->table('vtiger_profile2tab')
            ->whereIn('profileid', $profiles)
            ->where('tabid', $module->tabid)
            ->where('permissions', 0) // 0 = enabled
            ->exists();

        if (!$hasModuleAccess && $action !== 'view') {
            Cache::put($cacheKey, false, 300);
            return false;
        }

        if ($action === 'view') {
            Cache::put($cacheKey, $hasModuleAccess, 300);
            return $hasModuleAccess;
        }

        // Map action to operation
        $operationMap = [
            'create' => 0,
            'edit' => 1,
            'delete' => 2,
        ];

        if (!isset($operationMap[$action])) {
            Cache::put($cacheKey, false, 300);
            return false;
        }

        // Check standard permissions
        $hasPermission = DB::connection('tenant')
            ->table('vtiger_profile2standardpermissions')
            ->whereIn('profileid', $profiles)
            ->where('tabid', $module->tabid)
            ->where('operation', $operationMap[$action])
            ->where('permissions', 0) // 0 = allowed
            ->exists();

        Cache::put($cacheKey, $hasPermission, 300);
        return $hasPermission;
    }

    /**
     * Check if user has permission for a specific tool/utility
     *
     * @param int $userId
     * @param string $moduleName
     * @param string $tool (Import, Export, Merge, DuplicatesHandling)
     * @return bool
     */
    public function hasToolPermission(int $userId, string $moduleName, string $tool): bool
    {
        $cacheKey = "user_tool_permission_{$userId}_{$moduleName}_{$tool}";

        // Check if cached
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // Get user's role
        $userRole = DB::connection('tenant')
            ->table('vtiger_user2role')
            ->where('userid', $userId)
            ->first();

        if (!$userRole) {
            Cache::put($cacheKey, false, 300);
            return false;
        }

        // Get profile(s) for this role
        $profiles = DB::connection('tenant')
            ->table('vtiger_role2profile')
            ->where('roleid', $userRole->roleid)
            ->pluck('profileid');

        if ($profiles->isEmpty()) {
            Cache::put($cacheKey, false, 300);
            return false;
        }

        // Get module tabid
        $module = DB::connection('tenant')
            ->table('vtiger_tab')
            ->where('name', $moduleName)
            ->first();

        if (!$module) {
            Cache::put($cacheKey, false, 300);
            return false;
        }

        // Map tool to activity ID
        $activityMap = [
            'Import' => 4,
            'Export' => 3,
            'Merge' => 8,
            'DuplicatesHandling' => 10,
        ];

        if (!isset($activityMap[$tool])) {
            Cache::put($cacheKey, false, 300);
            return false;
        }

        // Check tool permissions
        $hasPermission = DB::connection('tenant')
            ->table('vtiger_profile2utility')
            ->whereIn('profileid', $profiles)
            ->where('tabid', $module->tabid)
            ->where('activityid', $activityMap[$tool])
            ->where('permission', 0) // 0 = allowed
            ->exists();

        Cache::put($cacheKey, $hasPermission, 300);
        return $hasPermission;
    }

    /**
     * Check if user has permission for a specific field
     *
     * @param int $userId
     * @param int $fieldId
     * @param string $access (view, edit)
     * @return bool
     */
    public function hasFieldPermission(int $userId, int $fieldId, string $access = 'view'): bool
    {
        $cacheKey = "user_field_permission_{$userId}_{$fieldId}_{$access}";

        // Check if cached
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // Get user's role
        $userRole = DB::connection('tenant')
            ->table('vtiger_user2role')
            ->where('userid', $userId)
            ->first();

        if (!$userRole) {
            Cache::put($cacheKey, true, 300); // Default to allow if no role
            return true;
        }

        // Get profile(s) for this role
        $profiles = DB::connection('tenant')
            ->table('vtiger_role2profile')
            ->where('roleid', $userRole->roleid)
            ->pluck('profileid');

        if ($profiles->isEmpty()) {
            Cache::put($cacheKey, true, 300); // Default to allow if no profile
            return true;
        }

        // Check field permissions
        $fieldPerm = DB::connection('tenant')
            ->table('vtiger_profile2field')
            ->whereIn('profileid', $profiles)
            ->where('fieldid', $fieldId)
            ->first();

        if (!$fieldPerm) {
            // If no specific permission, allow by default
            Cache::put($cacheKey, true, 300);
            return true;
        }

        // visible: 0 = visible, 1 = hidden
        // readonly: 0 = editable, 1 = readonly
        $result = false;
        if ($access === 'view') {
            $result = $fieldPerm->visible == 0;
        } elseif ($access === 'edit') {
            $result = $fieldPerm->visible == 0 && $fieldPerm->readonly == 0;
        }

        Cache::put($cacheKey, $result, 300);
        return $result;
    }

    /**
     * Get all permissions for a user on a module
     *
     * @param int $userId
     * @param string $moduleName
     * @return array
     */
    public function getModulePermissions(int $userId, string $moduleName): array
    {
        return [
            'view' => $this->hasPermission($userId, $moduleName, 'view'),
            'create' => $this->hasPermission($userId, $moduleName, 'create'),
            'edit' => $this->hasPermission($userId, $moduleName, 'edit'),
            'delete' => $this->hasPermission($userId, $moduleName, 'delete'),
            'import' => $this->hasToolPermission($userId, $moduleName, 'Import'),
            'export' => $this->hasToolPermission($userId, $moduleName, 'Export'),
            'merge' => $this->hasToolPermission($userId, $moduleName, 'Merge'),
            'duplicates' => $this->hasToolPermission($userId, $moduleName, 'DuplicatesHandling'),
        ];
    }

    /**
     * Clear permission cache for a user
     *
     * @param int $userId
     * @return void
     */
    public function clearUserPermissionCache(int $userId): void
    {
        // Cache will expire naturally after 5 minutes (300 seconds)
        // To manually clear specific user cache, you would need to track all cache keys
        // For now, we rely on TTL expiration
    }
}
