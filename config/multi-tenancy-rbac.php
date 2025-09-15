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
        'middleware' => ['api'],
        'throttle' => '60,1',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Sanctum Configuration
    |--------------------------------------------------------------------------
    */
    'sanctum' => [
        'expiration' => null, // Token expiration in minutes
        'middleware' => 'auth:sanctum',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Published Models
    |--------------------------------------------------------------------------
    |
    | When you publish the models, you can specify the namespace here.
    | This allows you to easily extend the models in your own application.
    |
    */
    'models' => [
        'tenant' => \Elgaml\MultiTenancyRbac\Models\Tenant::class,
        'user' => \Elgaml\MultiTenancyRbac\Models\User::class,
        'role' => \Elgaml\MultiTenancyRbac\Models\Role::class,
        'permission' => \Elgaml\MultiTenancyRbac\Models\Permission::class,
        'tenant_domain' => \Elgaml\MultiTenancyRbac\Models\TenantDomain::class,
        'feature_flag' => \Elgaml\MultiTenancyRbac\Models\FeatureFlag::class,
    ],
];
