<?php

namespace Esmat\MultiTenantPermission\Models;

use Esmat\MultiTenantPermission\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;

class Permission extends BaseModel
{
    protected $fillable = ['name', 'description', 'group'];
    
    /**
     * The connection name for the model.
     */
    protected $connection = 'tenant';
    
    /**
     * Get the roles that belong to the permission
     */
    public function roles()
    {
        return $this->belongsToMany(config('multitenant-permission.role_model'))
            ->withTimestamps();
    }
}
