<?php

namespace Esmat\MultiTenantPermission\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Esmat\MultiTenantPermission\Exceptions\PermissionDeniedException;

class CheckPermission
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $permission)
    {
        $user = $request->user();
        
        if (!$user) {
            throw new PermissionDeniedException('User not authenticated');
        }
        
        if (!$user->hasPermission($permission)) {
            throw new PermissionDeniedException("User does not have the required permission: {$permission}");
        }
        
        return $next($request);
    }
}
