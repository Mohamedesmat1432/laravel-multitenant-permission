<?php

namespace Esmat\MultiTenantPermission\Services;

use Esmat\MultiTenantPermission\Models\User;
use Esmat\MultiTenantPermission\Contracts\PermissionService as PermissionServiceContract;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PermissionService implements PermissionServiceContract
{
    protected $cachePrefix = 'permission:';
    protected $cacheTtl = 3600; // 1 hour
    
    /**
     * Check if a user has a specific permission
     */
    public function userHasPermission(User $user, string $permission): bool
    {
        // Super admin check
        if ($user->hasRole('super-admin')) {
            return true;
        }
        
        // Check cache
        $cacheKey = $this->cachePrefix . "user:{$user->id}:has_permission:{$permission}";
        
        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($user, $permission) {
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
        });
    }
    
    /**
     * Get all permissions for a user
     */
    public function getUserPermissions(User $user): array
    {
        // Super admin has all permissions
        if ($user->hasRole('super-admin')) {
            $permissionModel = config('multitenant-permission.permission_model');
            return $permissionModel::pluck('name')->toArray();
        }
        
        // Check cache
        $cacheKey = $this->cachePrefix . "user:{$user->id}:all_permissions";
        
        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($user) {
            $permissions = [];
            
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
        });
    }
    
    /**
     * Clear permission cache for a user
     */
    public function clearUserPermissionCache(User $user): void
    {
        $pattern = $this->cachePrefix . "user:{$user->id}:*";
        
        Cache::getStore()->flushPrefix($pattern);
        
        Log::info("Permission cache cleared for user: {$user->id}");
    }
    
    /**
     * Clear all permission cache
     */
    public function clearAllPermissionCache(): void
    {
        $pattern = $this->cachePrefix . "*";
        
        Cache::getStore()->flushPrefix($pattern);
        
        Log::info("All permission cache cleared");
    }
}
