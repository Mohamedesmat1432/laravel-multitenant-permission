<?php

namespace Esmat\MultiTenantPermission\Models;

use Esmat\MultiTenantPermission\Contracts\Tenant as TenantContract;
use Esmat\MultiTenantPermission\Exceptions\InvalidTenantException;
use Esmat\MultiTenantPermission\Services\TenantDatabaseManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Esmat\MultiTenantPermission\Events\TenantCreated;
use Esmat\MultiTenantPermission\Events\TenantUpdated;
use Esmat\MultiTenantPermission\Events\TenantDeleted;

class Tenant extends BaseModel implements TenantContract
{
    protected $fillable = ['name', 'domain', 'database', 'settings'];
    
    protected $casts = [
        'settings' => 'array',
    ];
    
    protected $table = 'tenants';
    
    /**
     * The connection name for the model.
     */
    protected $connection = 'central';
    
    /**
     * Configure the database connection for this tenant
     */
    public function configure(): self
    {
        $connection = config('multitenant-permission.tenant_connection');
        
        config([
            "database.connections.{$connection}.database" => $this->database,
        ]);
        
        // Purge the connection to ensure the new config is used
        app('db')->purge($connection);
        
        return $this;
    }
    
    /**
     * Set this tenant as the current tenant
     */
    public function use(): self
    {
        app()->singleton('currentTenant', function () {
            return $this;
        });
        
        return $this;
    }
    
    /**
     * Get the current tenant
     */
    public static function current(): ?self
    {
        return app('currentTenant');
    }
    
    /**
     * Identify tenant by ID
     */
    public static function identifyById(int $id): ?self
    {
        return static::find($id);
    }
    
    /**
     * Identify tenant by domain
     */
    public static function identifyByDomain(string $domain): ?self
    {
        return static::where('domain', $domain)->first();
    }
    
    /**
     * Get all users for this tenant
     */
    public function users()
    {
        return $this->hasMany(config('multitenant-permission.user_model'));
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
    
    /**
     * Check if a feature is enabled for this tenant
     */
    public function featureEnabled(string $feature): bool
    {
        return $this->getSetting("features.{$feature}", false);
    }
    
    /**
     * Enable a feature for this tenant
     */
    public function enableFeature(string $feature): void
    {
        $this->setSetting("features.{$feature}", true);
    }
    
    /**
     * Disable a feature for this tenant
     */
    public function disableFeature(string $feature): void
    {
        $this->setSetting("features.{$feature}", false);
    }
    
    /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        parent::booted();
        
        static::created(function ($tenant) {
            Event::dispatch(new TenantCreated($tenant));
        });
        
        static::updated(function ($tenant) {
            Event::dispatch(new TenantUpdated($tenant, $tenant->getOriginal(), $tenant->getChanges()));
        });
        
        static::deleted(function ($tenant) {
            Event::dispatch(new TenantDeleted($tenant));
        });
    }
}
