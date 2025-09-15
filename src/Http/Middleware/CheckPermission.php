<?php

namespace Esmat\MultiTenantPermission\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Esmat\MultiTenantPermission\Exceptions\PermissionDeniedException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class CheckPermission
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $permission)
    {
        try {
            // Apply rate limiting to permission checks
            if ($this->isRateLimited($request)) {
                return response()->json([
                    'error' => 'Too many requests',
                    'message' => 'Too many permission check attempts. Please try again later.',
                ], 429);
            }
            
            $user = $request->user();
            
            if (!$user) {
                throw new PermissionDeniedException('User not authenticated');
            }
            
            if (!$user->hasPermission($permission)) {
                Log::warning("Permission denied: User {$user->id} attempted to access resource requiring '{$permission}' permission");
                
                throw new PermissionDeniedException("User does not have the required permission: {$permission}");
            }
            
            Log::info("Permission granted: User {$user->id} accessed resource with '{$permission}' permission");
            
            return $next($request);
        } catch (PermissionDeniedException $e) {
            return response()->json([
                'error' => 'Permission denied',
                'message' => $e->getMessage(),
            ], 403);
        } catch (\Exception $e) {
            Log::error("Permission check error: {$e->getMessage()}");
            
            return response()->json([
                'error' => 'Internal server error',
                'message' => 'An error occurred while checking permissions.',
            ], 500);
        }
    }
    
    /**
     * Check if the request is rate limited
     */
    protected function isRateLimited(Request $request): bool
    {
        if (!config('multitenant-permission.security.rate_limiting.enabled')) {
            return false;
        }
        
        $key = 'permission_check:' . $request->ip();
        
        return RateLimiter::tooManyAttempts(
            $key,
            config('multitenant-permission.security.rate_limiting.attempts')
        );
    }
}
