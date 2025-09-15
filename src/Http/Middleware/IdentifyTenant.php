<?php

namespace Esmat\MultiTenantPermission\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Esmat\MultiTenantPermission\Exceptions\TenantNotFoundException;
use Esmat\MultiTenantPermission\Strategies\TenantIdentification\TenantIdentificationContext;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class IdentifyTenant
{
    protected $tenantIdentificationContext;
    
    public function __construct(TenantIdentificationContext $tenantIdentificationContext)
    {
        $this->tenantIdentificationContext = $tenantIdentificationContext;
    }
    
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            // Apply rate limiting to tenant identification
            if ($this->isRateLimited($request)) {
                return response()->json([
                    'error' => 'Too many requests',
                    'message' => 'Too many tenant identification attempts. Please try again later.',
                ], 429);
            }
            
            // Identify tenant
            $tenant = $this->tenantIdentificationContext->identify($request);
            
            if (!$tenant) {
                throw new TenantNotFoundException('Tenant could not be identified');
            }
            
            // Configure and use the tenant
            $tenant->configure()->use();
            
            // Add tenant to request for easy access
            $request->merge(['tenant' => $tenant]);
            
            // Log tenant identification
            Log::info("Tenant identified: {$tenant->name} (ID: {$tenant->id})");
            
            return $next($request);
        } catch (TenantNotFoundException $e) {
            Log::warning("Tenant identification failed: {$e->getMessage()}");
            
            return response()->json([
                'error' => 'Tenant not found',
                'message' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            Log::error("Tenant identification error: {$e->getMessage()}");
            
            return response()->json([
                'error' => 'Internal server error',
                'message' => 'An error occurred while identifying the tenant.',
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
        
        $key = 'tenant_identification:' . $request->ip();
        
        return RateLimiter::tooManyAttempts(
            $key,
            config('multitenant-permission.security.rate_limiting.attempts')
        );
    }
}
