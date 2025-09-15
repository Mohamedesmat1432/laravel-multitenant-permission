<?php

namespace Esmat\MultiTenantPermission\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = ['name', 'description', 'group'];
    
    /**
     * Get the roles that belong to the permission
     */
    public function roles()
    {
        return $this->belongsToMany(config('multitenant-permission.role_model'))
            ->withTimestamps();
    }
}
