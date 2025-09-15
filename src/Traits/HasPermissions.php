<?php

namespace Esmat\MultiTenantPermission\Traits;

use Esmat\MultiTenantPermission\Models\Permission;
use Esmat\MultiTenantPermission\Services\PermissionService;
use Illuminate\Support\Collection;

trait HasPermissions
{
    /**
     * Check if user has a specific permission
     */
    public function hasPermission(string $permission): bool
    {
        return app(PermissionService::class)->userHasPermission($this, $permission);
    }
    
    /**
     * Check if user has any of the given permissions
     */
    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if user has all of the given permissions
     */
    public function hasAllPermissions(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Get all permissions for the user
     */
    public function getAllPermissions(): array
    {
        return app(PermissionService::class)->getUserPermissions($this);
    }
    
    /**
     * Get all permissions as a collection
     */
    public function getPermissions(): Collection
    {
        $permissions = [];
        
        foreach ($this->roles as $role) {
            foreach ($role->permissions as $permission) {
                $permissions[] = $permission;
            }
        }
        
        return collect($permissions)->unique('id');
    }
}
