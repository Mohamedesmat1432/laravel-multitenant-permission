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
    | Table Names
    |--------------------------------------------------------------------------
    */
    'table_names' => [
        'tenants' => 'tenants',
        'users' => 'users',
        'roles' => 'roles',
        'permissions' => 'permissions',
        'role_user' => 'role_user',
        'permission_role' => 'permission_role',
    ],

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
        'domain' => false,
        'subdomain' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Roles and Permissions
    |--------------------------------------------------------------------------
    */
    'default_roles' => [
        'super-admin' => [
            'permissions' => ['*'],
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
        'ttl' => 3600,
        'prefix' => 'multitenant:',
        'driver' => 'redis',
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    */
    'security' => [
        'strict_tenant_isolation' => true,
        'permission_cache' => true,
        'rate_limiting' => [
            'enabled' => true,
            'attempts' => 60,
            'decay_minutes' => 1,
        ],
        'encryption' => [
            'enabled' => true,
            'key' => env('APP_KEY'),
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
        'throttle' => 'api',
        'pagination' => [
            'default_per_page' => 15,
            'max_per_page' => 100,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Configuration
    |--------------------------------------------------------------------------
    */
    'database' => [
        'prefix' => 'tenant_',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'engine' => 'InnoDB',
        'backup' => [
            'enabled' => false,
            'path' => storage_path('app/backups'),
            'schedule' => '0 0 * * *',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Event Listeners
    |--------------------------------------------------------------------------
    */
    'events' => [
        'tenant_created' => [
            \Esmat\MultiTenantPermission\Listeners\SendTenantCreatedNotification::class,
            \Esmat\MultiTenantPermission\Listeners\LogTenantActivity::class,
        ],
        'tenant_updated' => [
            \Esmat\MultiTenantPermission\Listeners\LogTenantActivity::class,
        ],
        'tenant_deleted' => [
            \Esmat\MultiTenantPermission\Listeners\LogTenantActivity::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    */
    'features' => [
        'audit_logs' => true,
        'two_factor_auth' => false,
        'sso' => false,
        'api_rate_limiting' => true,
    ],
];
