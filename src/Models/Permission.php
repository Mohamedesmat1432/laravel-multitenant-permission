<?php

namespace Elgaml\MultiTenancyRbac\Models;

use Illuminate\Database\Eloquent\Model;
use Elgaml\MultiTenancyRbac\Traits\BelongsToTenant;

class Permission extends Model
{
    use BelongsToTenant;
    
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'tenant_id',
    ];
    
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
    
    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
