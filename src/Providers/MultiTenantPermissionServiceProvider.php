<?php

namespace Esmat\MultiTenantPermission\Providers;

use Illuminate\Support\ServiceProvider;
use Esmat\MultiTenantPermission\Services\TenantDatabaseManager;
use Esmat\MultiTenantPermission\Services\PermissionService;
use Esmat\MultiTenantPermission\Contracts\Tenant as TenantContract;
use Esmat\MultiTenantPermission\Contracts\PermissionService as PermissionServiceContract;
use Esmat\MultiTenantPermission\Models\Tenant;
use Esmat\MultiTenantPermission\Models\User;
use Esmat\MultiTenantPermission\Models\Role;
use Esmat\MultiTenantPermission\Models\Permission;
use Esmat\MultiTenantPermission\Strategies\TenantIdentification\TenantIdentificationContext;
use Esmat\MultiTenantPermission\Strategies\TenantIdentification\HeaderStrategy;
use Esmat\MultiTenantPermission\Strategies\TenantIdentification\DomainStrategy;
use Esmat\MultiTenantPermission\Strategies\TenantIdentification\SubdomainStrategy;
use Esmat\MultiTenantPermission\Repositories\TenantRepository;
use Esmat\MultiTenantPermission\Factories\TenantFactory;
use Esmat\MultiTenantPermission\Services\EncryptionService;
use Esmat\MultiTenantPermission\Services\AuditLogService;
use Esmat\MultiTenantPermission\Services\CacheService;

class MultiTenantPermissionServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/multitenant-permission.php', 'multitenant-permission');
        
        // Register singleton for current tenant
        $this->app->singleton('currentTenant', function () {
            return null;
        });
        
        // Register contracts
        $this->app->bind(TenantContract::class, config('multitenant-permission.tenant_model'));
        $this->app->singleton(PermissionServiceContract::class, PermissionService::class);
        
        // Register services
        $this->app->singleton(TenantDatabaseManager::class, function ($app) {
            return new TenantDatabaseManager();
        });
        
        $this->app->singleton(TenantRepository::class, function ($app) {
            return new TenantRepository();
        });
        
        $this->app->singleton(TenantFactory::class, function ($app) {
            return new TenantFactory($app->make(TenantDatabaseManager::class));
        });
        
        $this->app->singleton(EncryptionService::class, function ($app) {
            return new EncryptionService();
        });
        
        $this->app->singleton(AuditLogService::class, function ($app) {
            return new AuditLogService();
        });
        
        $this->app->singleton(CacheService::class, function ($app) {
            return new CacheService();
        });
        
        // Register tenant identification context
        $this->app->singleton(TenantIdentificationContext::class, function ($app) {
            $context = new TenantIdentificationContext();
            $tenantRepository = $app->make(TenantRepository::class);
            
            // Register identification strategies
            $config = config('multitenant-permission.tenant_identification');
            
            if ($config['header']) {
                $context->addStrategy(new HeaderStrategy($tenantRepository, $config['header']));
            }
            
            if ($config['domain']) {
                $context->addStrategy(new DomainStrategy($tenantRepository));
            }
            
            if ($config['subdomain']) {
                $context->addStrategy(new SubdomainStrategy($tenantRepository));
            }
            
            return $context;
        });
    }

    public function boot()
    {
        // Register middleware
        $this->app['router']->aliasMiddleware('tenant', \Esmat\MultiTenantPermission\Http\Middleware\IdentifyTenant::class);
        $this->app['router']->aliasMiddleware('permission', \Esmat\MultiTenantPermission\Http\Middleware\CheckPermission::class);
        
        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Esmat\MultiTenantPermission\Console\Commands\CreateTenant::class,
                \Esmat\MultiTenantPermission\Console\Commands\TenantMigrate::class,
                \Esmat\MultiTenantPermission\Console\Commands\TenantSeed::class,
            ]);
        }
        
        // Publish assets
        $this->publishes([
            __DIR__.'/../../config/multitenant-permission.php' => config_path('multitenant-permission.php'),
        ], 'config');
        
        $this->publishes([
            __DIR__.'/../Database/Migrations' => database_path('migrations'),
        ], 'migrations');
        
        // Register routes
        $this->loadRoutesFrom(__DIR__.'/../../routes/api.php');
        
        // Configure database connections
        $this->configureDatabase();
        
        // Register event listeners
        $this->registerEventListeners();
    }
    
    protected function configureDatabase()
    {
        // Set up tenant database connection
        config([
            'database.connections.'.config('multitenant-permission.tenant_connection') => [
                'driver' => 'mysql',
                'host' => env('DB_HOST', '127.0.0.1'),
                'port' => env('DB_PORT', '3306'),
                'database' => env('DB_DATABASE', 'forge'),
                'username' => env('DB_USERNAME', 'forge'),
                'password' => env('DB_PASSWORD', ''),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => true,
                'engine' => null,
            ],
        ]);
    }
    
    protected function registerEventListeners()
    {
        $events = config('multitenant-permission.events');
        
        foreach ($events as $event => $listeners) {
            foreach ($listeners as $listener) {
                \Event::listen($event, $listener);
            }
        }
    }
}
