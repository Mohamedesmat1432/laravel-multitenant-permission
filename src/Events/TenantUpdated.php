<?php

namespace Esmat\MultiTenantPermission\Events;

use Esmat\MultiTenantPermission\Models\Tenant;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TenantUpdated
{
    use Dispatchable, SerializesModels;
    
    public $tenant;
    public $original;
    public $changes;
    
    public function __construct(Tenant $tenant, array $original, array $changes)
    {
        $this->tenant = $tenant;
        $this->original = $original;
        $this->changes = $changes;
    }
}
