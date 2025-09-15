<?php

namespace Elgaml\MultiTenancyRbac\Traits;

use Elgaml\MultiTenancyRbac\Facades\Rbac;
use Elgaml\MultiTenancyRbac\Models\Permission;

trait HasPermissions
{
    public function permissions()
    {
        $permissionModel = config('multi-tenancy-rbac.models.permission', \Elgaml\MultiTenancyRbac\Models\Permission::class);
        return $this->belongsToMany($permissionModel);
    }
    
    public function hasPermission($permissions, $requireAll = false)
    {
        return Rbac::can($this, $permissions, $requireAll);
    }
    
    public function can($abilities, $arguments = [])
    {
        return Rbac::can($this, $abilities, false);
    }
}
