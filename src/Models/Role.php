<?php

namespace Esmat\MultiTenantPermission\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Role extends Model
{
    protected $fillable = ['name', 'description', 'settings'];
    
    protected $casts = [
        'settings' => 'array',
    ];
    
    /**
     * Get the permissions that belong to the role
     */
    public function permissions()
    {
        return $this->belongsToMany(config('multitenant-permission.permission_model'))
            ->withTimestamps();
    }
    
    /**
     * Get the users that belong to the role
     */
    public function users()
    {
        return $this->belongsToMany(config('multitenant-permission.user_model'))
            ->withTimestamps();
    }
    
    /**
     * Check if role has a specific permission
     */
    public function hasPermission(string $permission): bool
    {
        return $this->permissions()->where('name', $permission)->exists();
    }
    
    /**
     * Give a permission to the role
     */
    public function givePermissionTo($permission): self
    {
        if (is_string($permission)) {
            $permission = config('multitenant-permission.permission_model')::where('name', $permission)->firstOrFail();
        }
        
        $this->permissions()->syncWithoutDetaching([$permission->id]);
        
        return $this;
    }
    
    /**
     * Revoke a permission from the role
     */
    public function revokePermissionTo($permission): self
    {
        if (is_string($permission)) {
            $permission = config('multitenant-permission.permission_model')::where('name', $permission)->firstOrFail();
        }
        
        $this->permissions()->detach($permission->id);
        
        return $this;
    }
    
    /**
     * Sync permissions for the role
     */
    public function syncPermissions(array $permissions): self
    {
        $permissionIds = [];
        
        foreach ($permissions as $permission) {
            if (is_string($permission)) {
                $permission = config('multitenant-permission.permission_model')::where('name', $permission)->firstOrFail();
            }
            $permissionIds[] = $permission->id;
        }
        
        $this->permissions()->sync($permissionIds);
        
        return $this;
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
     * Clear role cache
     */
    public function clearCache(): void
    {
        // Clear all user permission caches that have this role
        foreach ($this->users as $user) {
            $user->clearPermissionCache();
        }
    }
    
    /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        static::updated(function ($role) {
            $role->clearCache();
        });
        
        static::deleted(function ($role) {
            $role->clearCache();
        });
    }
}
