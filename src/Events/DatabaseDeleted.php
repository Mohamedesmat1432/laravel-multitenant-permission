<?php

namespace Esmat\MultiTenantPermission\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DatabaseDeleted
{
    use Dispatchable, SerializesModels;
    
    public $databaseName;
    
    public function __construct(string $databaseName)
    {
        $this->databaseName = $databaseName;
    }
}
