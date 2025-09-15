<?php

namespace Esmat\MultiTenantPermission\Services;

use Esmat\MultiTenantPermission\Models\User;
use Esmat\MultiTenantPermission\Contracts\PermissionService as PermissionServiceContract;

class PermissionService implements PermissionServiceContract
{
    /**
     * Check if a user has a specific permission
     */
    public function userHasPermission(User $user, string $permission): bool
    {
        // Super admin check
        if ($user->hasRole('super-admin')) {
            return true;
        }
        
        // Check each role for the permission
        foreach ($user->roles as $role) {
            if ($role->hasPermission($permission)) {
                return true;
            }
            
            // Check for wildcard permissions
            $permissionParts = explode('.', $permission);
            $wildcardPermission = $permissionParts[0] . '.*';
            
            if ($role->hasPermission($wildcardPermission)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get all permissions for a user
     */
    public function getUserPermissions(User $user): array
    {
        $permissions = [];
        
        // Super admin has all permissions
        if ($user->hasRole('super-admin')) {
            $permissionModel = config('multitenant-permission.permission_model');
            return $permissionModel::pluck('name')->toArray();
        }
        
        // Collect permissions from all roles
        foreach ($user->roles as $role) {
            foreach ($role->permissions as $permission) {
                $permissions[] = $permission->name;
                
                // Add wildcard permissions
                $permissionParts = explode('.', $permission->name);
                if (count($permissionParts) > 1) {
                    $wildcardPermission = $permissionParts[0] . '.*';
                    $permissions[] = $wildcardPermission;
                }
            }
        }
        
        return array_unique($permissions);
    }
}
