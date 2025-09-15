<?php

namespace Elgaml\MultiTenancyRbac\Auth\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Elgaml\MultiTenancyRbac\Services\RbacService;

class EnsurePermission
{
    protected $rbacService;
    
    public function __construct(RbacService $rbacService)
    {
        $this->rbacService = $rbacService;
    }
    
    public function handle($request, Closure $next, $permission, $requireAll = false)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated',
                'error' => 'unauthenticated',
            ], 401);
        }
        
        if (!$this->rbacService->can($user, $permission, $requireAll)) {
            return response()->json([
                'message' => 'Insufficient permissions',
                'error' => 'permission_denied',
                'required' => $permission,
            ], 403);
        }
        
        return $next($request);
    }
}
