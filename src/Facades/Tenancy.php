<?php

namespace Elgaml\MultiTenancyRbac\Facades;

use Illuminate\Support\Facades\Facade;

class Tenancy extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'tenancy';
    }
}
