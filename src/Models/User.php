<?php

namespace Esmat\MultiTenantPermission\Models;

use Esmat\MultiTenantPermission\Models\BaseModel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Esmat\MultiTenantPermission\Traits\HasRoles;
use Esmat\MultiTenantPermission\Traits\HasPermissions;
use Esmat\MultiTenantPermission\Traits\HasSettings;
use Illuminate\Support\Facades\Hash;
use Esmat\MultiTenantPermission\Events\UserCreated;
use Esmat\MultiTenantPermission\Events\UserUpdated;
use Esmat\MultiTenantPermission\Events\UserDeleted;

class User extends BaseModel
{
    use HasApiTokens, Notifiable, HasRoles, HasPermissions, HasSettings;
    
    protected $fillable = ['name', 'email', 'password', 'settings'];
    
    protected $hidden = ['password', 'remember_token'];
    
    protected $casts = [
        'settings' => 'array',
    ];
    
    /**
     * The connection name for the model.
     */
    protected $connection = 'tenant';
    
    /**
     * The attributes that should be mutated to dates.
     */
    protected $dates = ['email_verified_at'];
    
    /**
     * Get the tenant that owns the user
     */
    public function tenant()
    {
        return $this->belongsTo(config('multitenant-permission.tenant_model'));
    }
    
    /**
     * Hash password automatically
     */
    public function setPasswordAttribute($value)
    {
        if ($value) {
            $this->attributes['password'] = Hash::make($value);
        }
    }
    
    /**
     * Check if user is a super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super-admin');
    }
    
    /**
     * Check if user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin') || $this->isSuperAdmin();
    }
    
    /**
     * Get user's full name
     */
    public function getFullNameAttribute(): string
    {
        return $this->name;
    }
    
    /**
     * Get user's avatar URL
     */
    public function getAvatarUrlAttribute(): string
    {
        return $this->getSetting('avatar_url', 'https://ui-avatars.com/api/?name=' . urlencode($this->name));
    }
    
    /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        parent::booted();
        
        static::created(function ($user) {
            Event::dispatch(new UserCreated($user));
        });
        
        static::updated(function ($user) {
            Event::dispatch(new UserUpdated($user, $user->getOriginal(), $user->getChanges()));
        });
        
        static::deleted(function ($user) {
            Event::dispatch(new UserDeleted($user));
        });
    }
}
