<?php

namespace Elgaml\MultiTenancyRbac\Auth\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Elgaml\MultiTenancyRbac\Services\RbacService;

class EnsureRole
{
    protected $rbacService;
    
    public function __construct(RbacService $rbacService)
    {
        $this->rbacService = $rbacService;
    }
    
    public function handle($request, Closure $next, $role, $requireAll = false)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated',
                'error' => 'unauthenticated',
            ], 401);
        }
        
        if (!$this->rbacService->hasRole($user, $role, $requireAll)) {
            return response()->json([
                'message' => 'Insufficient role',
                'error' => 'role_denied',
                'required' => $role,
            ], 403);
        }
        
        return $next($request);
    }
}
