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
}
