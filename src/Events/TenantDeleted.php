<?php

namespace Elgaml\MultiTenancyRbac\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TenantDeleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    
    public $tenant;
    
    public function __construct($tenant)
    {
        $this->tenant = $tenant;
    }
}
