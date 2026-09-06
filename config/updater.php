<?php

return [
    /*
    |--------------------------------------------------------------------------
    | BugFinder Product Information
    |--------------------------------------------------------------------------
    |
    | Information identifying the specific CodeCanyon product.
    |
    */
    'product_id' => env('BUGFINDER_PRODUCT_ID', 'bugfinder-app'),
    'product_name' => env('BUGFINDER_PRODUCT_NAME', 'BugFinder Application'),
    'current_version' => env('APP_VERSION', '1.0.0'),

    /*
    |--------------------------------------------------------------------------
    | Remote Licensing & Update Server Config
    |--------------------------------------------------------------------------
    |
    | Base URL of the central BugFinder license and update server.
    |
    */
    'server_url' => env('BUGFINDER_UPDATE_SERVER_URL', 'https://bugfinder.net/api'),
    'purchase_code' => env('BUGFINDER_PURCHASE_CODE', ''),
    'domain_override' => env('BUGFINDER_TEST_DOMAIN', null),
    'version_override' => env('BUGFINDER_TEST_VERSION', null),

    /*
    |--------------------------------------------------------------------------
    | Database Table Settings (Basic Control)
    |--------------------------------------------------------------------------
    |
    | Automatically queries BugFinder's `basic_controls` table for app version
    | and purchase code.
    |
    */
    'database' => [
        'table' => 'basic_controls',
        'version_column' => 'app_version',        // or 'version'
        'purchase_code_column' => 'purchase_code', // or 'license_code'
    ],

    /*
    |--------------------------------------------------------------------------
    | Route & Middleware Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the URL prefix, middleware, and auth guards applied to updater Web UI.
    |
    */
    'route_prefix' => 'admin/updater',
    'middleware' => ['web', \BugFinder\Updater\Http\Middleware\VerifyAdminAccess::class],
    'auth_guards' => ['admin', 'web'],

    /*
    |--------------------------------------------------------------------------
    | Protected Files and Directories
    |--------------------------------------------------------------------------
    |
    | Paths relative to base_path() that should NEVER be overwritten during an
    | automated update extraction.
    |
    */
    'protected_files' => [
        '.env',
        '.htaccess',
        'storage',
        'public/uploads',
        'public/storage',
        'public/assets/custom',
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Directories for Updates & Backups
    |--------------------------------------------------------------------------
    |
    */
    'backup_path' => storage_path('app/updater-backups'),
    'update_temp_path' => storage_path('app/updater-temp'),

    /*
    |--------------------------------------------------------------------------
    | Pre-flight Environment Requirements
    |--------------------------------------------------------------------------
    |
    */
    'requirements' => [
        'php_version' => '8.1.0',
        'extensions' => [
            'zip',
            'curl',
            'pdo',
            'mbstring',
            'fileinfo',
            'openssl',
        ],
        'writable_paths' => [
            'storage',
            'bootstrap/cache',
        ],
    ],
];
