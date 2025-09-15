<?php

namespace Esmat\MultiTenantPermission\Exceptions;

use Exception;

class PermissionDeniedException extends Exception
{
    protected $code = 403;
    
    public function render()
    {
        return response()->json([
            'error' => 'Permission denied',
            'message' => $this->getMessage(),
        ], $this->getCode());
    }
}
