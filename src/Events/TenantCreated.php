<?php

namespace Esmat\MultiTenantPermission\Events;

use Esmat\MultiTenantPermission\Models\Tenant;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TenantCreated
{
    use Dispatchable, SerializesModels;
    
    public $tenant;
    
    public function __construct(Tenant $tenant)
    {
        $this->tenant = $tenant;
    }
}
