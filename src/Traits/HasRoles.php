<?php

namespace Elgaml\MultiTenancyRbac\Traits;

use Elgaml\MultiTenancyRbac\Facades\Rbac;
use Elgaml\MultiTenancyRbac\Models\Role;

trait HasRoles
{
    public function roles()
    {
        $roleModel = config('multi-tenancy-rbac.models.role', \Elgaml\MultiTenancyRbac\Models\Role::class);
        return $this->belongsToMany($roleModel);
    }
    
    public function hasRole($roles, $requireAll = false)
    {
        return Rbac::hasRole($this, $roles, $requireAll);
    }
    
    public function assignRole($role)
    {
        return Rbac::assignRoleToUser($this, $role);
    }
    
    public function removeRole($role)
    {
        return Rbac::removeRoleFromUser($this, $role);
    }
}
