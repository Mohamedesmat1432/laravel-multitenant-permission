<?php

namespace Elgaml\MultiTenancyRbac\Console\Commands;

use Illuminate\Console\Command;
use Elgaml\MultiTenancyRbac\Facades\Tenancy;

class TenantMigrate extends Command
{
    protected $signature = 'tenant:migrate 
                            {tenant? : The ID of the tenant} 
                            {--path= : The path to migration files}';
    
    protected $description = 'Run migrations for a specific tenant or all tenants';
    
    public function handle()
    {
        $tenantId = $this->argument('tenant');
        $path = $this->option('path') ?? config('multi-tenancy-rbac.database.migration_path');
        
        if ($tenantId) {
            $tenantModel = config('multi-tenancy-rbac.models.tenant', \Elgaml\MultiTenancyRbac\Models\Tenant::class);
            $tenant = $tenantModel::find($tenantId);
            
            if (!$tenant) {
                $this->error("Tenant with ID {$tenantId} not found");
                return 1;
            }
            
            $this->info("Running migrations for tenant: {$tenant->name}");
            $tenant->runMigrations();
            
            $this->info("Migrations completed for tenant: {$tenant->name}");
        } else {
            $tenantModel = config('multi-tenancy-rbac.models.tenant', \Elgaml\MultiTenancyRbac\Models\Tenant::class);
            $tenants = $tenantModel::all();
            
            $this->info("Running migrations for all tenants");
            
            foreach ($tenants as $tenant) {
                $this->info("Running migrations for tenant: {$tenant->name}");
                $tenant->runMigrations();
            }
            
            $this->info("Migrations completed for all tenants");
        }
        
        return 0;
    }
}
