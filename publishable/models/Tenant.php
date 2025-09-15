<?php

namespace App\Models\MultiTenancyRbac;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Elgaml\MultiTenancyRbac\Traits\HasSettings;
use Elgaml\MultiTenancyRbac\Traits\HasFeatureFlags;
use Elgaml\MultiTenancyRbac\Events\TenantCreated;
use Elgaml\MultiTenancyRbac\Events\TenantUpdated;
use Elgaml\MultiTenancyRbac\Events\TenantDeleted;

class Tenant extends Model
{
    use SoftDeletes, HasSettings, HasFeatureFlags;
    
    protected $fillable = [
        'name',
        'domain',
        'database_name',
        'is_active',
        'settings',
    ];
    
    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];
    
    protected $dispatchesEvents = [
        'created' => TenantCreated::class,
        'updated' => TenantUpdated::class,
        'deleted' => TenantDeleted::class,
    ];
    
    public function domains()
    {
        return $this->hasMany(TenantDomain::class);
    }
    
    public function users()
    {
        return $this->hasMany(User::class);
    }
    
    public function roles()
    {
        return $this->hasMany(Role::class);
    }
    
    public function permissions()
    {
        return $this->hasMany(Permission::class);
    }
    
    public function createDatabase()
    {
        $databaseName = $this->database_name ?: config('multi-tenancy-rbac.database.prefix') . $this->id;
        
        // Create database
        \DB::statement("CREATE DATABASE IF NOT EXISTS `{$databaseName}`");
        
        $this->database_name = $databaseName;
        $this->save();
        
        return $databaseName;
    }
    
    public function configure()
    {
        // Set tenant database connection
        config([
            'database.connections.tenant.database' => $this->database_name,
        ]);
        
        \DB::purge('tenant');
        \DB::reconnect('tenant');
        
        return $this;
    }
    
    public function runMigrations()
    {
        $this->configure();
        
        $path = config('multi-tenancy-rbac.database.migration_path');
        
        \Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => $path,
            '--force' => true,
        ]);
        
        return $this;
    }
}
