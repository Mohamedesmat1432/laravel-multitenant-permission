<?php

namespace Esmat\MultiTenantPermission\Exceptions;

use Exception;

class InvalidTenantException extends Exception
{
    protected $code = 422;
    
    public function render()
    {
        return response()->json([
            'error' => 'Invalid tenant',
            'message' => $this->getMessage(),
        ], $this->getCode());
    }
}
