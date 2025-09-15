<?php

namespace Esmat\MultiTenantPermission\Console\Commands;

use Illuminate\Console\Command;
use Esmat\MultiTenantPermission\Models\Tenant;
use Esmat\MultiTenantPermission\Exceptions\InvalidTenantException;

class CreateTenant extends Command
{
    protected $signature = 'tenant:create 
                           {name : The name of the tenant} 
                           {domain : The domain of the tenant} 
                           {database? : The database name (optional, will be generated if not provided)}';
    
    protected $description = 'Create a new tenant with database';
    
    public function handle()
    {
        $name = $this->argument('name');
        $domain = $this->argument('domain');
        $database = $this->argument('database') ?? 'tenant_' . strtolower(str_replace(' ', '_', $name)) . '_' . time();
        
        try {
            $this->info("Creating tenant: {$name}");
            
            // Create tenant with database
            $tenant = Tenant::createWithDatabase([
                'name' => $name,
                'domain' => $domain,
                'database' => $database,
                'settings' => [
                    'created_at' => now()->toISOString(),
                ],
            ]);
            
            $this->info("Tenant created successfully!");
            $this->info("Tenant ID: {$tenant->id}");
            $this->info("Domain: {$tenant->domain}");
            $this->info("Database: {$tenant->database}");
            
            return 0;
        } catch (InvalidTenantException $e) {
            $this->error("Error creating tenant: {$e->getMessage()}");
            return 1;
        } catch (\Exception $e) {
            $this->error("An unexpected error occurred: {$e->getMessage()}");
            return 1;
        }
    }
}
