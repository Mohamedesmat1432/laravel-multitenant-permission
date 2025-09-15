<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tenant Identification Methods
    |--------------------------------------------------------------------------
    */
    'identification_methods' => [
        'header' => [
            'enabled' => true,
            'header_name' => 'X-Tenant-ID',
        ],
        'domain' => [
            'enabled' => true,
        ],
        'subdomain' => [
            'enabled' => true,
        ],
        'path' => [
            'enabled' => false,
            'path_segment' => 1,
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Database Configuration
    |--------------------------------------------------------------------------
    */
    'database' => [
        'prefix' => 'tenant_',
        'auto_create' => true,
        'migration_path' => database_path('migrations/tenant'),
        'central_connection' => 'mysql',
        'tenant_connection_template' => 'tenant',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | RBAC Configuration
    |--------------------------------------------------------------------------
    */
    'rbac' => [
        'cache' => [
            'enabled' => true,
            'ttl' => 3600,
            'store' => 'redis',
        ],
        'hierarchical' => true,
        'wildcards' => true,
        'super_admin_role' => 'super-admin',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    */
    'security' => [
        'audit_logging' => true,
        'rate_limiting' => [
            'enabled' => true,
            'attempts' => 5,
            'decay_minutes' => 1,
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    */
    'api' => [
        'prefix' => 'api',
        'middleware' => ['api', 'auth:sanctum'],
        'throttle' => '60,1',
    ],
];
