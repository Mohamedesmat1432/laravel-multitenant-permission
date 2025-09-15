<?php

namespace Esmat\MultiTenantPermission\Console\Commands;

use Illuminate\Console\Command;
use Esmat\MultiTenantPermission\Models\Tenant;
use Esmat\MultiTenantPermission\Services\TenantDatabaseManager;

class TenantSeed extends Command
{
    protected $signature = 'tenant:seed {--tenant= : The ID of the tenant to seed (optional)}';
    
    protected $description = 'Seed tenant(s) with default data';
    
    public function handle()
    {
        $tenantId = $this->option('tenant');
        $tenantDatabaseManager = app(TenantDatabaseManager::class);
        
        if ($tenantId) {
            // Seed specific tenant
            $tenant = Tenant::find($tenantId);
            
            if (!$tenant) {
                $this->error("Tenant with ID {$tenantId} not found");
                return 1;
            }
            
            $this->info("Seeding tenant: {$tenant->name} (ID: {$tenant->id})");
            $tenantDatabaseManager->seed($tenant);
            $this->info("Seeding completed for tenant: {$tenant->name}");
        } else {
            // Seed all tenants
            $this->info("Seeding all tenants");
            $tenantDatabaseManager->seedAllTenants();
            $this->info("Seeding completed for all tenants");
        }
        
        return 0;
    }
}
