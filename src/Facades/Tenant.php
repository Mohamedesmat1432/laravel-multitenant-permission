<?php

namespace Esmat\MultiTenantPermission\Facades;

use Illuminate\Support\Facades\Facade;

class Tenant extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'currentTenant';
    }
}
