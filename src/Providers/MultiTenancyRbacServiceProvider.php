<?php

namespace Elgaml\MultiTenancyRbac\Providers;

use Illuminate\Support\ServiceProvider;
use Elgaml\MultiTenancyRbac\Services\TenantService;
use Elgaml\MultiTenancyRbac\Services\RbacService;
use Elgaml\MultiTenancyRbac\Console\Commands\TenantCreate;
use Elgaml\MultiTenancyRbac\Console\Commands\TenantMigrate;
use Elgaml\MultiTenancyRbac\Console\Commands\PermissionCacheClear;

class MultiTenancyRbacServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/multi-tenancy-rbac.php', 'multi-tenancy-rbac');
        
        $this->app->singleton(TenantService::class, function ($app) {
            return new TenantService();
        });
        
        $this->app->singleton(RbacService::class, function ($app) {
            return new RbacService();
        });
        
        $this->app->alias(TenantService::class, 'tenancy');
        $this->app->alias(RbacService::class, 'rbac');
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/multi-tenancy-rbac.php' => config_path('multi-tenancy-rbac.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../../database/migrations' => database_path('migrations'),
        ], 'migrations');

        $this->publishes([
            __DIR__.'/../../routes/api.php' => routes_path('api.php'),
        ], 'routes');
        
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        
        if ($this->app->runningInConsole()) {
            $this->commands([
                TenantCreate::class,
                TenantMigrate::class,
                PermissionCacheClear::class,
            ]);
        }
        
        $this->registerApiRoutes();
    }
    
    protected function registerApiRoutes()
    {
        $this->app->router->group([
            'prefix' => 'api',
            'middleware' => ['api', 'auth:sanctum'],
        ], function ($router) {
            require __DIR__.'/../../routes/api.php';
        });
    }
}
