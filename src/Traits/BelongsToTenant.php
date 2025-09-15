<?php

namespace Elgaml\MultiTenancyRbac\Traits;

use Elgaml\MultiTenancyRbac\Models\Tenant;

trait BelongsToTenant
{
    public static function bootBelongsToTenant()
    {
        static::creating(function ($model) {
            if (tenancy()->check()) {
                $model->tenant_id = tenant('id');
            }
        });
        
        static::addGlobalScope('tenant', function ($builder) {
            if (tenancy()->check()) {
                $builder->where('tenant_id', tenant('id'));
            }
        });
    }
    
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
