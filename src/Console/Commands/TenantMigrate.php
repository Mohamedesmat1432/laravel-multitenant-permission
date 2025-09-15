<?php

namespace Esmat\MultiTenantPermission\Console\Commands;

use Illuminate\Console\Command;
use Esmat\MultiTenantPermission\Models\Tenant;
use Esmat\MultiTenantPermission\Services\TenantDatabaseManager;
use Illuminate\Support\Facades\Log;

class TenantMigrate extends Command
{
    protected $signature = 'tenant:migrate 
                           {--tenant= : The ID of the tenant to migrate (optional)}
                           {--path= : The path to the migration files}
                           {--force : Force the operation to run when in production}
                           {--step : Force the migrations to be run so they can be rolled back individually}';
    
    protected $description = 'Run migrations for tenant(s)';
    
    public function handle()
    {
        $tenantId = $this->option('tenant');
        $path = $this->option('path');
        $force = $this->option('force');
        $step = $this->option('step');
        
        $tenantDatabaseManager = app(TenantDatabaseManager::class);
        
        if ($tenantId) {
            // Migrate specific tenant
            $tenant = Tenant::find($tenantId);
            
            if (!$tenant) {
                $this->error("Tenant with ID {$tenantId} not found");
                return 1;
            }
            
            $this->info("Migrating tenant: {$tenant->name} (ID: {$tenant->id})");
            
            $tenant->configure()->use();
            
            $this->call('migrate', [
                '--path' => $path ?? 'vendor/esmat/laravel-multitenant-permission/database/migrations/tenant',
                '--database' => config('multitenant-permission.tenant_connection'),
                '--force' => $force,
                '--step' => $step,
            ]);
            
            $this->info("Migration completed for tenant: {$tenant->name}");
        } else {
            // Migrate all tenants
            $this->info("Migrating all tenants");
            
            $tenants = Tenant::all();
            $failedTenants = [];
            
            foreach ($tenants as $tenant) {
                try {
                    $this->info("Migrating tenant: {$tenant->name} (ID: {$tenant->id})");
                    
                    $tenant->configure()->use();
                    
                    $this->call('migrate', [
                        '--path' => $path ?? 'vendor/esmat/laravel-multitenant-permission/database/migrations/tenant',
                        '--database' => config('multitenant-permission.tenant_connection'),
                        '--force' => $force,
                        '--step' => $step,
                    ]);
                    
                    $this->info("Migration completed for tenant: {$tenant->name}");
                } catch (\Exception $e) {
                    $this->error("Failed to migrate tenant {$tenant->name}: {$e->getMessage()}");
                    $failedTenants[] = $tenant->id;
                    
                    Log::error("Failed to migrate tenant {$tenant->name}: {$e->getMessage()}", [
                        'exception' => $e,
                    ]);
                }
            }
            
            if (!empty($failedTenants)) {
                $this->error("Failed to migrate " . count($failedTenants) . " tenant(s): " . implode(', ', $failedTenants));
                return 1;
            }
            
            $this->info("Migration completed for all tenants");
        }
        
        return 0;
    }
}
