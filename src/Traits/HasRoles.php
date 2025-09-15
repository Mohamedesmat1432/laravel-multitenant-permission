<?php

namespace Elgaml\MultiTenancyRbac\Traits;

use Elgaml\MultiTenancyRbac\Models\Role;
use Elgaml\MultiTenancyRbac\Services\RbacService;

trait HasRoles
{
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
    
    public function hasRole($roles, $requireAll = false)
    {
        return app(RbacService::class)->hasRole($this, $roles, $requireAll);
    }
    
    public function assignRole($role)
    {
        return app(RbacService::class)->assignRoleToUser($this, $role);
    }
    
    public function removeRole($role)
    {
        return app(RbacService::class)->removeRoleFromUser($this, $role);
    }
}
