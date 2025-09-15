<?php

namespace Esmat\MultiTenantPermission\Models;

use Esmat\MultiTenantPermission\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;

class Role extends BaseModel
{
    protected $fillable = ['name', 'description', 'settings'];
    
    protected $casts = [
        'settings' => 'array',
    ];
    
    /**
     * The connection name for the model.
     */
    protected $connection = 'tenant';
    
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
        $settings = $this->settings ?? [];
        $settings[$key] = $value;
        
        $this->settings = $settings;
        $this->save();
    }
}
