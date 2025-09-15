<?php

namespace Elgaml\MultiTenancyRbac\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Elgaml\MultiTenancyRbac\Exceptions\TenantCouldNotBeIdentifiedException;

class TenantService
{
    protected $currentTenant = null;
    protected $request;
    
    public function __construct(Request $request = null)
    {
        $this->request = $request ?? request();
    }
    
    public function identify()
    {
        if ($this->currentTenant) {
            return $this->currentTenant;
        }
        
        $methods = config('multi-tenancy-rbac.identification_methods');
        
        // Try each identification method
        if ($methods['header']['enabled']) {
            $tenant = $this->identifyByHeader();
            if ($tenant) return $this->setCurrentTenant($tenant);
        }
        
        if ($methods['domain']['enabled']) {
            $tenant = $this->identifyByDomain();
            if ($tenant) return $this->setCurrentTenant($tenant);
        }
        
        if ($methods['subdomain']['enabled']) {
            $tenant = $this->identifyBySubdomain();
            if ($tenant) return $this->setCurrentTenant($tenant);
        }
        
        if ($methods['path']['enabled']) {
            $tenant = $this->identifyByPath();
            if ($tenant) return $this->setCurrentTenant($tenant);
        }
        
        throw new TenantCouldNotBeIdentifiedException();
    }
    
    protected function identifyByHeader()
    {
        $headerName = config('multi-tenancy-rbac.identification_methods.header.header_name');
        $tenantId = $this->request->header($headerName);
        
        if (!$tenantId) {
            return null;
        }
        
        return $this->getTenantById($tenantId);
    }
    
    protected function identifyByDomain()
    {
        $domain = $this->request->getHost();
        $tenantModel = config('multi-tenancy-rbac.models.tenant', \Elgaml\MultiTenancyRbac\Models\Tenant::class);
        
        return $tenantModel::where('domain', $domain)->first();
    }
    
    protected function identifyBySubdomain()
    {
        $host = $this->request->getHost();
        $parts = explode('.', $host);
        
        if (count($parts) > 2) {
            $subdomain = $parts[0];
            $tenantModel = config('multi-tenancy-rbac.models.tenant', \Elgaml\MultiTenancyRbac\Models\Tenant::class);
            return $tenantModel::where('domain', $subdomain)->first();
        }
        
        return null;
    }
    
    protected function identifyByPath()
    {
        $pathSegment = config('multi-tenancy-rbac.identification_methods.path.path_segment');
        $segments = $this->request->segments();
        
        if (isset($segments[$pathSegment - 1])) {
            $tenantId = $segments[$pathSegment - 1];
            return $this->getTenantById($tenantId);
        }
        
        return null;
    }
    
    protected function getTenantById($id)
    {
        $cacheKey = "tenant_{$id}";
        $tenantModel = config('multi-tenancy-rbac.models.tenant', \Elgaml\MultiTenancyRbac\Models\Tenant::class);
        
        return Cache::remember($cacheKey, now()->addHour(), function () use ($id, $tenantModel) {
            return $tenantModel::find($id);
        });
    }
    
    public function setCurrentTenant($tenant)
    {
        $this->currentTenant = $tenant;
        
        // Configure tenant connection
        if (method_exists($tenant, 'configure')) {
            $tenant->configure();
        }
        
        return $tenant;
    }
    
    public function current()
    {
        return $this->currentTenant;
    }
    
    public function check($tenantId = null)
    {
        try {
            $current = $this->identify();
            return $tenantId ? $current->id == $tenantId : true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    public function forgetCurrent()
    {
        $this->currentTenant = null;
        return $this;
    }
    
    public function all()
    {
        $tenantModel = config('multi-tenancy-rbac.models.tenant', \Elgaml\MultiTenancyRbac\Models\Tenant::class);
        return $tenantModel::all();
    }
    
    public function create(array $attributes)
    {
        $tenantModel = config('multi-tenancy-rbac.models.tenant', \Elgaml\MultiTenancyRbac\Models\Tenant::class);
        $tenant = $tenantModel::create($attributes);
        
        if (config('multi-tenancy-rbac.database.auto_create')) {
            if (method_exists($tenant, 'createDatabase')) {
                $tenant->createDatabase();
            }
            if (method_exists($tenant, 'runMigrations')) {
                $tenant->runMigrations();
            }
        }
        
        return $tenant;
    }
}
