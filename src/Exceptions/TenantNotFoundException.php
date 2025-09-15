<?php

namespace Esmat\MultiTenantPermission\Exceptions;

use Exception;

class TenantNotFoundException extends Exception
{
    protected $message = 'Tenant not found';
    
    protected $code = 404;
    
    public function render()
    {
        return response()->json([
            'error' => 'Tenant not found',
            'message' => $this->getMessage(),
        ], $this->getCode());
    }
}
