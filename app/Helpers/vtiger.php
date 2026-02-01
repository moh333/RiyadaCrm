<?php

if (!function_exists('vtranslate')) {
    /**
     * Translate string using vtiger-style language files correctly.
     * 
     * @param string $key
     * @param string|object $moduleOrName
     * @return string
     */
    function vtranslate(string $key, $moduleOrName = 'Vtiger')
    {
        static $translationsCache = [];
        $locale = app()->getLocale();

        $moduleName = $moduleOrName;
        if (is_object($moduleOrName) && method_exists($moduleOrName, 'getName')) {
            $moduleName = $moduleOrName->getName();
        } elseif (!is_string($moduleName)) {
            $moduleName = 'Vtiger';
        }

        $cacheKey = "{$locale}_{$moduleName}";

        if (!isset($translationsCache[$cacheKey])) {
            $translationsCache[$cacheKey] = loadVtigerLanguageFile($locale, $moduleName);
        }

        if (isset($translationsCache[$cacheKey][$key])) {
            return $translationsCache[$cacheKey][$key];
        }

        // Fallback to Vtiger module
        if ($moduleName !== 'Vtiger') {
            $vtigerCacheKey = "{$locale}_Vtiger";
            if (!isset($translationsCache[$vtigerCacheKey])) {
                $translationsCache[$vtigerCacheKey] = loadVtigerLanguageFile($locale, 'Vtiger');
            }
            if (isset($translationsCache[$vtigerCacheKey][$key])) {
                return $translationsCache[$vtigerCacheKey][$key];
            }
        }

        // Final fallback: standard Laravel translation if key starts with namespace
        if (str_contains($key, '::')) {
            return __($key);
        }

        return $key;
    }

    /**
     * Internal loader for vtiger language files
     */
    function loadVtigerLanguageFile($locale, $moduleName)
    {
        $path = app_path("Modules/Tenant/Resources/Lang/{$locale}/modules/{$moduleName}.php");

        // Handle nested modules (e.g. Settings/Webforms)
        if (!file_exists($path)) {
            // Try common locations if moduleName contains slash
            $moduleName = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $moduleName);
            $path = app_path("Modules/Tenant/Resources/Lang/{$locale}/modules/{$moduleName}.php");
        }

        if (!file_exists($path)) {
            return [];
        }

        // We use an anonymous function to avoid polluting global scope or current scope
        $loader = function ($filePath) {
            $languageStrings = [];
            $jsLanguageStrings = [];
            include $filePath;
            return $languageStrings;
        };

        return $loader($path);
    }
    /**
     * Get the next CRM ID from vtiger_crmentity_seq
     * 
     * @param string $connection
     * @return int
     */
    function vtiger_next_id(string $connection = 'tenant'): int
    {
        return \Illuminate\Support\Facades\DB::connection($connection)->transaction(function () use ($connection) {
            $query = \Illuminate\Support\Facades\DB::connection($connection)->table('vtiger_crmentity_seq')->lockForUpdate();
            $result = $query->first();

            if (!$result) {
                $maxId = \Illuminate\Support\Facades\DB::connection($connection)->table('vtiger_crmentity')->max('crmid') ?? 1000;
                $nextId = $maxId + 1;
                \Illuminate\Support\Facades\DB::connection($connection)->table('vtiger_crmentity_seq')->insert(['id' => $nextId]);
                return (int) $nextId;
            }

            $nextId = $result->id + 1;
            \Illuminate\Support\Facades\DB::connection($connection)->table('vtiger_crmentity_seq')->update(['id' => $nextId]);

            return (int) $nextId;
        });
    }

    /**
     * Get the next module sequence number from vtiger_modentity_num
     * 
     * @param string $module
     * @param string $connection
     * @return string|null
     */
    function vtiger_next_no(string $module, string $connection = 'tenant'): ?string
    {
        return \Illuminate\Support\Facades\DB::connection($connection)->transaction(function () use ($module, $connection) {
            $query = \Illuminate\Support\Facades\DB::connection($connection)->table('vtiger_modentity_num')
                ->where('semodule', $module)
                ->lockForUpdate();

            $row = $query->first();

            if (!$row) {
                // Initialize if missing (Vtiger behavior)
                $prefix = strtoupper(substr($module, 0, 3));
                $nextSequence = 1;
                \Illuminate\Support\Facades\DB::connection($connection)->table('vtiger_modentity_num')->insert([
                    'semodule' => $module,
                    'prefix' => $prefix,
                    'start_id' => 1,
                    'cur_id' => 1,
                    'active' => '1',
                ]);
                return $prefix . $nextSequence;
            }

            $currentId = (int) $row->cur_id;
            $nextSequence = $currentId + 1;

            \Illuminate\Support\Facades\DB::connection($connection)->table('vtiger_modentity_num')
                ->where('num_id', $row->num_id)
                ->update(['cur_id' => $nextSequence]);

            return $row->prefix . $nextSequence;
        });
    }
}
