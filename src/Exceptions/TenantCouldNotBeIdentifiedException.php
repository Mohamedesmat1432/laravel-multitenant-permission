<?php

namespace Elgaml\MultiTenancyRbac\Exceptions;

use Exception;

class TenantCouldNotBeIdentifiedException extends Exception
{
    public function __construct()
    {
        parent::__construct('Tenant could not be identified with any of the configured methods.');
    }
}
