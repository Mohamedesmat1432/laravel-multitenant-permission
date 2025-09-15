<?php

namespace Elgaml\MultiTenancyRbac\Traits;

use Elgaml\MultiTenancyRbac\Models\Permission;
use Elgaml\MultiTenancyRbac\Services\RbacService;

trait HasPermissions
{
    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }
    
    public function hasPermission($permissions, $requireAll = false)
    {
        return app(RbacService::class)->can($this, $permissions, $requireAll);
    }
    
    public function can($abilities, $arguments = [])
    {
        return app(RbacService::class)->can($this, $abilities, false);
    }
}
