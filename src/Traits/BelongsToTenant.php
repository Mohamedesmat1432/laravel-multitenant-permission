<?php

namespace Elgaml\MultiTenancyRbac\Traits;

use Elgaml\MultiTenancyRbac\Facades\Tenancy;

trait BelongsToTenant
{
    public static function bootBelongsToTenant()
    {
        static::creating(function ($model) {
            if (Tenancy::check()) {
                $model->tenant_id = tenant('id');
            }
        });
        
        static::addGlobalScope('tenant', function ($builder) {
            if (Tenancy::check()) {
                $builder->where('tenant_id', tenant('id'));
            }
        });
    }
    
    public function tenant()
    {
        $tenantModel = config('multi-tenancy-rbac.models.tenant', \Elgaml\MultiTenancyRbac\Models\Tenant::class);
        return $this->belongsTo($tenantModel);
    }
}
