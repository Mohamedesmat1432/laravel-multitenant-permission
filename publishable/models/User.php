<?php

namespace App\Models\MultiTenancyRbac;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Elgaml\MultiTenancyRbac\Traits\HasRoles;
use Elgaml\MultiTenancyRbac\Traits\BelongsToTenant;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, HasRoles, BelongsToTenant;
    
    protected $fillable = [
        'name',
        'email',
        'password',
        'tenant_id',
    ];
    
    protected $hidden = [
        'password',
        'remember_token',
    ];
    
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
    
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
    
    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }
}
