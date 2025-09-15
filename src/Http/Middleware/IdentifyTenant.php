<?php

namespace Esmat\MultiTenantPermission\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Esmat\MultiTenantPermission\Exceptions\TenantNotFoundException;
use Esmat\MultiTenantPermission\Models\Tenant;

class IdentifyTenant
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $tenant = null;
        $config = config('multitenant-permission.tenant_identification');
        
        // Try to identify tenant by header
        if ($config['header'] && $request->hasHeader($config['header'])) {
            $tenantId = $request->header($config['header']);
            $tenant = Tenant::identifyById($tenantId);
        }
        
        // Try to identify tenant by domain
        if (!$tenant && $config['domain']) {
            $domain = $request->getHost();
            $tenant = Tenant::identifyByDomain($domain);
        }
        
        // Try to identify tenant by subdomain
        if (!$tenant && $config['subdomain']) {
            $domain = $request->getHost();
            $subdomain = explode('.', $domain)[0];
            $tenant = Tenant::where('domain', $subdomain)->first();
        }
        
        if (!$tenant) {
            throw new TenantNotFoundException('Tenant could not be identified');
        }
        
        // Configure and use the tenant
        $tenant->configure()->use();
        
        // Add tenant to request for easy access
        $request->merge(['tenant' => $tenant]);
        
        return $next($request);
    }
}
