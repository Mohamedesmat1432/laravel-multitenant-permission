<?php

namespace Elgaml\MultiTenancyRbac\Services;

use Illuminate\Support\Facades\Cache;
use Elgaml\MultiTenancyRbac\Models\User;

class RbacService
{
    protected $cacheTtl;
    protected $cacheStore;
    
    public function __construct()
    {
        $this->cacheTtl = config('multi-tenancy-rbac.rbac.cache.ttl', 3600);
        $this->cacheStore = config('multi-tenancy-rbac.rbac.cache.store', 'redis');
    }
    
    public function getUserPermissions(User $user)
    {
        $cacheKey = "user_permissions_{$user->id}_".tenant('id');
        
        return Cache::store($this->cacheStore)->remember($cacheKey, $this->cacheTtl, function () use ($user) {
            $permissions = $user->permissions->pluck('name');
            
            // Get permissions from roles
            foreach ($user->roles as $role) {
                $permissions = $permissions->merge($role->permissions->pluck('name'));
            }
            
            return $permissions->unique();
        });
    }
    
    public function getUserRoles(User $user)
    {
        $cacheKey = "user_roles_{$user->id}_".tenant('id');
        
        return Cache::store($this->cacheStore)->remember($cacheKey, $this->cacheTtl, function () use ($user) {
            return $user->roles->pluck('name');
        });
    }
    
    public function can(User $user, $permission, $requireAll = false)
    {
        if (is_string($permission)) {
            $permission = [$permission];
        }
        
        $userPermissions = $this->getUserPermissions($user);
        
        // Check for super admin role
        if ($user->hasRole(config('multi-tenancy-rbac.rbac.super_admin_role'))) {
            return true;
        }
        
        $hasPermission = true;
        
        foreach ($permission as $perm) {
            // Check for exact match
            $exactMatch = $userPermissions->contains($perm);
            
            // Check for wildcard permissions
            $wildcardMatch = false;
            if (config('multi-tenancy-rbac.rbac.wildcards')) {
                $wildcardPermission = substr($perm, 0, strrpos($perm, '.') + 1) . '*';
                $wildcardMatch = $userPermissions->contains($wildcardPermission);
            }
            
            if (!$exactMatch && !$wildcardMatch) {
                if ($requireAll) {
                    return false;
                } else {
                    $hasPermission = false;
                }
            }
        }
        
        return $hasPermission;
    }
    
    public function hasRole(User $user, $roles, $requireAll = false)
    {
        if (is_string($roles)) {
            $roles = [$roles];
        }
        
        $userRoles = $this->getUserRoles($user);
        
        $hasRole = true;
        
        foreach ($roles as $role) {
            if (!$userRoles->contains($role)) {
                if ($requireAll) {
                    return false;
                } else {
                    $hasRole = false;
                }
            }
        }
        
        return $hasRole;
    }
    
    public function createPermission(array $attributes)
    {
        $attributes['tenant_id'] = tenant('id');
        $permissionModel = config('multi-tenancy-rbac.models.permission', \Elgaml\MultiTenancyRbac\Models\Permission::class);
        return $permissionModel::create($attributes);
    }
    
    public function createRole(array $attributes)
    {
        $attributes['tenant_id'] = tenant('id');
        $roleModel = config('multi-tenancy-rbac.models.role', \Elgaml\MultiTenancyRbac\Models\Role::class);
        return $roleModel::create($attributes);
    }
    
    public function assignPermissionToRole($role, $permission)
    {
        if (is_string($role)) {
            $roleModel = config('multi-tenancy-rbac.models.role', \Elgaml\MultiTenancyRbac\Models\Role::class);
            $role = $roleModel::whereName($role)->firstOrFail();
        }
        
        if (is_string($permission)) {
            $permissionModel = config('multi-tenancy-rbac.models.permission', \Elgaml\MultiTenancyRbac\Models\Permission::class);
            $permission = $permissionModel::whereName($permission)->firstOrFail();
        }
        
        $role->givePermissionTo($permission);
        $this->clearCache();
        
        return $role;
    }
    
    public function assignRoleToUser($user, $role)
    {
        if (is_string($role)) {
            $roleModel = config('multi-tenancy-rbac.models.role', \Elgaml\MultiTenancyRbac\Models\Role::class);
            $role = $roleModel::whereName($role)->firstOrFail();
        }
        
        $user->roles()->syncWithoutDetaching($role);
        $this->clearCache();
        
        return $user;
    }
    
    public function removePermissionFromRole($role, $permission)
    {
        if (is_string($role)) {
            $roleModel = config('multi-tenancy-rbac.models.role', \Elgaml\MultiTenancyRbac\Models\Role::class);
            $role = $roleModel::whereName($role)->firstOrFail();
        }
        
        if (is_string($permission)) {
            $permissionModel = config('multi-tenancy-rbac.models.permission', \Elgaml\MultiTenancyRbac\Models\Permission::class);
            $permission = $permissionModel::whereName($permission)->firstOrFail();
        }
        
        $role->revokePermissionTo($permission);
        $this->clearCache();
        
        return $role;
    }
    
    public function removeRoleFromUser($user, $role)
    {
        if (is_string($role)) {
            $roleModel = config('multi-tenancy-rbac.models.role', \Elgaml\MultiTenancyRbac\Models\Role::class);
            $role = $roleModel::whereName($role)->firstOrFail();
        }
        
        $user->roles()->detach($role);
        $this->clearCache();
        
        return $user;
    }
    
    public function clearCache()
    {
        Cache::store($this->cacheStore)->flush();
    }
    
    public function getPermissionsTree()
    {
        $permissionModel = config('multi-tenancy-rbac.models.permission', \Elgaml\MultiTenancyRbac\Models\Permission::class);
        $permissions = $permissionModel::all();
        $tree = [];
        
        foreach ($permissions as $permission) {
            $parts = explode('.', $permission->name);
            $current = &$tree;
            
            foreach ($parts as $part) {
                if (!isset($current[$part])) {
                    $current[$part] = [
                        'name' => $part,
                        'children' => [],
                    ];
                }
                $current = &$current[$part]['children'];
            }
        }
        
        return $tree;
    }
}
