<?php

namespace App\Models\MultiTenancyRbac;

use Illuminate\Database\Eloquent\Model;

class TenantDomain extends Model
{
    protected $fillable = [
        'tenant_id',
        'domain',
    ];
    
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
