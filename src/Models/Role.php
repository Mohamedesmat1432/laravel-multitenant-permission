<?php

namespace Elgaml\MultiTenancyRbac\Models;

use Illuminate\Database\Eloquent\Model;
use Elgaml\MultiTenancyRbac\Traits\BelongsToTenant;
use Elgaml\MultiTenancyRbac\Traits\HasPermissions;

class Role extends Model
{
    use BelongsToTenant, HasPermissions;
    
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'tenant_id',
    ];
    
    public function users()
    {
        return $this->belongsToMany(User::class);
    }
    
    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }
    
    public function givePermissionTo($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::whereName($permission)->firstOrFail();
        }
        
        $this->permissions()->syncWithoutDetaching($permission);
        
        return $this;
    }
    
    public function revokePermissionTo($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::whereName($permission)->firstOrFail();
        }
        
        $this->permissions()->detach($permission);
        
        return $this;
    }
    
    public function syncPermissions($permissions)
    {
        $this->permissions()->sync($permissions);
        
        return $this;
    }
}
