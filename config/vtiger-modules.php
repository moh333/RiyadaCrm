<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Database Connection
    |--------------------------------------------------------------------------
    |
    | The database connection to use for reading vtiger metadata.
    | In multi-tenant systems, this should be 'tenant'.
    |
    */
    'connection' => env('VTIGER_MODULES_CONNECTION', 'tenant'),

    /*
    |--------------------------------------------------------------------------
    | Cache TTL
    |--------------------------------------------------------------------------
    |
    | How long to cache module metadata (in seconds).
    | Module metadata rarely changes, so a long TTL is recommended.
    | Set to 0 to disable caching.
    |
    */
    'cache_ttl' => env('VTIGER_MODULES_CACHE_TTL', 3600),

    /*
    |--------------------------------------------------------------------------
    | Excluded Modules
    |--------------------------------------------------------------------------
    |
    | Modules to exclude from the registry.
    | Useful for hiding internal or deprecated modules.
    |
    */
    'excluded_modules' => [
        'Migration',
        'ModComments',
        'Webforms',
    ],

    /*
    |--------------------------------------------------------------------------
    | Load Relations
    |--------------------------------------------------------------------------
    |
    | Whether to automatically load module relations.
    | Set to false to improve performance if relations are not needed.
    |
    */
    'load_relations' => env('VTIGER_MODULES_LOAD_RELATIONS', true),

    /*
    |--------------------------------------------------------------------------
    | Load Custom Fields Only
    |--------------------------------------------------------------------------
    |
    | If true, only load custom fields (generatedtype = 2).
    | Useful for custom field management UIs.
    |
    */
    'custom_fields_only' => false,
];
