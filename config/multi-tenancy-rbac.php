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
        'tenant' => \App\Models\MultiTenancyRbac\Tenant::class,
        'user' => \App\Models\MultiTenancyRbac\User::class,
        'role' => \App\Models\MultiTenancyRbac\Role::class,
        'permission' => \App\Models\MultiTenancyRbac\Permission::class,
        'tenant_domain' => \App\Models\MultiTenancyRbac\TenantDomain::class,
        'feature_flag' => \App\Models\MultiTenancyRbac\FeatureFlag::class,
    ],
];
