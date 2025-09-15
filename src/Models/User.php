<?php

namespace Esmat\MultiTenantPermission\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Esmat\MultiTenantPermission\Services\PermissionService;
use Illuminate\Support\Facades\Cache;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;
    
    protected $fillable = ['name', 'email', 'password', 'settings'];
    
    protected $hidden = ['password', 'remember_token'];
    
    protected $casts = [
        'settings' => 'array',
    ];
    
    /**
     * Get the roles that belong to the user
     */
    public function roles()
    {
        return $this->belongsToMany(config('multitenant-permission.role_model'))
            ->withTimestamps();
    }
    
    /**
     * Get the tenant that owns the user
     */
    public function tenant()
    {
        return $this->belongsTo(config('multitenant-permission.tenant_model'));
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
     * Check if user has a specific permission
     */
    public function hasPermission(string $permission): bool
    {
        if (config('multitenant-permission.security.permission_cache')) {
            return Cache::remember(
                config('multitenant-permission.cache.prefix') . "user:{$this->id}:permissions",
                config('multitenant-permission.cache.ttl'),
                function () use ($permission) {
                    return app(PermissionService::class)->userHasPermission($this, $permission);
                }
            );
        }
        
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
     * Assign a role to the user
     */
    public function assignRole($role): self
    {
        if (is_string($role)) {
            $role = config('multitenant-permission.role_model')::where('name', $role)->firstOrFail();
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
            $role = config('multitenant-permission.role_model')::where('name', $role)->firstOrFail();
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
                $role = config('multitenant-permission.role_model')::where('name', $role)->firstOrFail();
            }
            $roleIds[] = $role->id;
        }
        
        $this->roles()->sync($roleIds);
        
        // Clear permission cache
        $this->clearPermissionCache();
        
        return $this;
    }
    
    /**
     * Get all permissions for the user
     */
    public function getAllPermissions(): array
    {
        return app(PermissionService::class)->getUserPermissions($this);
    }
    
    /**
     * Get a setting value
     */
    public function getSetting(string $key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }
    
    /**
     * Set a setting value
     */
    public function setSetting(string $key, $value): void
    {
        $this->settings = array_merge($this->settings ?? [], [$key => $value]);
        $this->save();
    }
    
    /**
     * Clear permission cache
     */
    public function clearPermissionCache(): void
    {
        Cache::forget(config('multitenant-permission.cache.prefix') . "user:{$this->id}:permissions");
    }
    
    /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        static::updated(function ($user) {
            $user->clearPermissionCache();
        });
        
        static::deleted(function ($user) {
            $user->clearPermissionCache();
        });
    }
}
