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
        $this->configurePublishing();
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
    
    protected function configurePublishing()
    {
        // Config
        $this->publishes([
            __DIR__.'/../../config/multi-tenancy-rbac.php' => config_path('multi-tenancy-rbac.php'),
        ], 'config');
        
        // Routes
        $this->publishes([
            __DIR__.'/../../publishable/routes/api.php' => base_path('routes/multi-tenancy-rbac.php'),
        ], 'routes');
        
        // Controllers
        $this->publishes([
            __DIR__.'/../../publishable/controllers/Api' => app_path('Http/Controllers/Api/MultiTenancyRbac'),
        ], 'controllers');
        
        // Models
        $this->publishes([
            __DIR__.'/../../publishable/models' => app_path('Models/MultiTenancyRbac'),
        ], 'models');
    }
    
    protected function registerApiRoutes()
    {
        // Check if the user has published the routes file
        $routesPath = base_path('routes/multi-tenancy-rbac.php');
        
        if (file_exists($routesPath)) {
            $this->loadRoutesFrom($routesPath);
        } else {
            // Load the default routes from the package
            $this->app->router->group([
                'prefix' => 'api',
                'middleware' => ['api', 'auth:sanctum'],
            ], function ($router) {
                require __DIR__.'/../../publishable/routes/api.php';
            });
        }
    }
}
