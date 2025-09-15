<?php

namespace Elgaml\MultiTenancyRbac\Console\Commands;

use Illuminate\Console\Command;
use Elgaml\MultiTenancyRbac\Services\TenantService;

class TenantCreate extends Command
{
    protected $signature = 'tenant:create 
                            {name : The name of the tenant} 
                            {domain : The domain of the tenant} 
                            {--database= : The database name} 
                            {--active=1 : Whether the tenant is active}';
    
    protected $description = 'Create a new tenant';
    
    public function handle()
    {
        $name = $this->argument('name');
        $domain = $this->argument('domain');
        $database = $this->option('database');
        $active = $this->option('active');
        
        $tenant = app(TenantService::class)->create([
            'name' => $name,
            'domain' => $domain,
            'database_name' => $database,
            'is_active' => $active,
        ]);
        
        $this->info("Tenant '{$name}' created successfully with ID: {$tenant->id}");
        
        return 0;
    }
}
