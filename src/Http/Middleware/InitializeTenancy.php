<?php

namespace Elgaml\MultiTenancyRbac\Http\Middleware;

use Closure;
use Elgaml\MultiTenancyRbac\Services\TenantService;
use Elgaml\MultiTenancyRbac\Exceptions\TenantCouldNotBeIdentifiedException;

class InitializeTenancy
{
    protected $tenantService;
    
    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }
    
    public function handle($request, Closure $next)
    {
        try {
            $tenant = $this->tenantService->identify();
            
            if (!$tenant->is_active) {
                return response()->json([
                    'message' => 'Tenant is not active',
                    'error' => 'tenant_inactive',
                ], 403);
            }
            
            // Initialize tenancy
            $this->tenantService->setCurrentTenant($tenant);
            
            return $next($request);
        } catch (TenantCouldNotBeIdentifiedException $e) {
            return response()->json([
                'message' => 'Tenant not found or not accessible',
                'error' => 'tenant_not_found',
            ], 404);
        }
    }
}
