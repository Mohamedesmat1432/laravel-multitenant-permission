<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tenant Model
    |--------------------------------------------------------------------------
    */
    'tenant_model' => \Esmat\MultiTenantPermission\Models\Tenant::class,

    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    */
    'user_model' => \Esmat\MultiTenantPermission\Models\User::class,

    /*
    |--------------------------------------------------------------------------
    | Role Model
    |--------------------------------------------------------------------------
    */
    'role_model' => \Esmat\MultiTenantPermission\Models\Role::class,

    /*
    |--------------------------------------------------------------------------
    | Permission Model
    |--------------------------------------------------------------------------
    */
    'permission_model' => \Esmat\MultiTenantPermission\Models\Permission::class,

    /*
    |--------------------------------------------------------------------------
    | Tenant Database Connection
    |--------------------------------------------------------------------------
    */
    'tenant_connection' => 'tenant',

    /*
    |--------------------------------------------------------------------------
    | Tenant Identification Methods
    |--------------------------------------------------------------------------
    */
    'tenant_identification' => [
        'header' => 'X-Tenant-ID',
        'domain' => false, // Set to true to use domain-based identification
        'subdomain' => false, // Set to true to use subdomain-based identification
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Roles and Permissions
    |--------------------------------------------------------------------------
    */
    'default_roles' => [
        'super-admin' => [
            'permissions' => ['*'], // Wildcard for all permissions
            'description' => 'Super Administrator with all permissions'
        ],
        'admin' => [
            'permissions' => [
                'manage-users', 'manage-roles', 'manage-permissions',
                'view-audit-logs', 'manage-tenant-settings'
            ],
            'description' => 'Administrator with elevated permissions'
        ],
        'manager' => [
            'permissions' => [
                'view-users', 'create-users', 'edit-users',
                'view-reports', 'manage-inventory'
            ],
            'description' => 'Manager with department-level permissions'
        ],
        'user' => [
            'permissions' => [
                'view-profile', 'edit-profile'
            ],
            'description' => 'Regular user with basic permissions'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'enabled' => true,
        'ttl' => 3600, // 1 hour
        'prefix' => 'multitenant:',
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    */
    'security' => [
        'strict_tenant_isolation' => true, // Prevent cross-tenant data access
        'permission_cache' => true, // Cache user permissions
        'rate_limiting' => [
            'enabled' => true,
            'attempts' => 60,
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
        'middleware' => ['api', 'tenant'],
        'throttle' => 'api', // Throttle middleware name
    ],
];
