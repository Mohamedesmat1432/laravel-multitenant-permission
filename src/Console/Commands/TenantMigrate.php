<?php

namespace Esmat\MultiTenantPermission\Console\Commands;

use Illuminate\Console\Command;
use Esmat\MultiTenantPermission\Models\Tenant;
use Esmat\MultiTenantPermission\Services\TenantDatabaseManager;

class TenantMigrate extends Command
{
    protected $signature = 'tenant:migrate {--tenant= : The ID of the tenant to migrate (optional)}';
    
    protected $description = 'Run migrations for tenant(s)';
    
    public function handle()
    {
        $tenantId = $this->option('tenant');
        $tenantDatabaseManager = app(TenantDatabaseManager::class);
        
        if ($tenantId) {
            // Migrate specific tenant
            $tenant = Tenant::find($tenantId);
            
            if (!$tenant) {
                $this->error("Tenant with ID {$tenantId} not found");
                return 1;
            }
            
            $this->info("Migrating tenant: {$tenant->name} (ID: {$tenant->id})");
            $tenantDatabaseManager->migrate($tenant);
            $this->info("Migration completed for tenant: {$tenant->name}");
        } else {
            // Migrate all tenants
            $this->info("Migrating all tenants");
            $tenantDatabaseManager->migrateAllTenants();
            $this->info("Migration completed for all tenants");
        }
        
        return 0;
    }
}
