<?php

namespace App\Models\MultiTenancyRbac;

use Illuminate\Database\Eloquent\Model;

class FeatureFlag extends Model
{
    protected $fillable = [
        'tenant_id',
        'key',
        'enabled',
    ];
    
    protected $casts = [
        'enabled' => 'boolean',
    ];
    
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
