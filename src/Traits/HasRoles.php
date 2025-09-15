<?php

namespace Esmat\MultiTenantPermission\Traits;

use Esmat\MultiTenantPermission\Models\Role;
use Illuminate\Support\Collection;

trait HasRoles
{
    /**
     * The roles that belong to the user.
     */
    public function roles()
    {
        return $this->belongsToMany(config('multitenant-permission.role_model'))
            ->withTimestamps();
    }
    
    /**
     * Assign a role to the user
     */
    public function assignRole($role): self
    {
        if (is_string($role)) {
            $role = Role::where('name', $role)->firstOrFail();
        }
        
        $this->roles()->syncWithoutDetaching([$role->id]);
        
        // Clear permission cache
        $this->clearPermissionCache();
        
        return $this;
    }
    
    /**
     * Remove a role from the user
     */
    public function removeRole($role): self
    {
        if (is_string($role)) {
            $role = Role::where('name', $role)->firstOrFail();
        }
        
        $this->roles()->detach($role->id);
        
        // Clear permission cache
        $this->clearPermissionCache();
        
        return $this;
    }
    
    /**
     * Sync roles for the user
     */
    public function syncRoles(array $roles): self
    {
        $roleIds = [];
        
        foreach ($roles as $role) {
            if (is_string($role)) {
                $role = Role::where('name', $role)->firstOrFail();
            }
            $roleIds[] = $role->id;
        }
        
        $this->roles()->sync($roleIds);
        
        // Clear permission cache
        $this->clearPermissionCache();
        
        return $this;
    }
    
    /**
     * Check if user has a specific role
     */
    public function hasRole(string $role): bool
    {
        return $this->roles()->where('name', $role)->exists();
    }
    
    /**
     * Check if user has any of the given roles
     */
    public function hasAnyRole(array $roles): bool
    {
        return $this->roles()->whereIn('name', $roles)->exists();
    }
    
    /**
     * Check if user has all of the given roles
     */
    public function hasAllRoles(array $roles): bool
    {
        return $this->roles()->whereIn('name', $roles)->count() === count($roles);
    }
    
    /**
     * Get all user roles as a collection
     */
    public function getRoles(): Collection
    {
        return $this->roles;
    }
    
    /**
     * Get all user role names as an array
     */
    public function getRoleNames(): array
    {
        return $this->roles->pluck('name')->toArray();
    }
    
    /**
     * Clear permission cache
     */
    protected function clearPermissionCache(): void
    {
        $this->clearCache();
    }
}
