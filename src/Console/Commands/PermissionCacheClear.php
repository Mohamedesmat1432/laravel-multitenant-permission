<?php

namespace Elgaml\MultiTenancyRbac\Console\Commands;

use Illuminate\Console\Command;
use Elgaml\MultiTenancyRbac\Facades\Rbac;

class PermissionCacheClear extends Command
{
    protected $signature = 'rbac:cache-clear';
    
    protected $description = 'Clear the RBAC cache';
    
    public function handle()
    {
        Rbac::clearCache();
        
        $this->info('RBAC cache cleared successfully');
        
        return 0;
    }
}
