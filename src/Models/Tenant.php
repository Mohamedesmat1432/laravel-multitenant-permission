<?php

namespace Esmat\MultiTenantPermission\Models;

use Illuminate\Database\Eloquent\Model;
use Esmat\MultiTenantPermission\Contracts\Tenant as TenantContract;
use Esmat\MultiTenantPermission\Exceptions\InvalidTenantException;
use Esmat\MultiTenantPermission\Services\TenantDatabaseManager;
use Illuminate\Support\Facades\Cache;

class Tenant extends Model implements TenantContract
{
    protected $fillable = ['name', 'domain', 'database', 'settings'];
    
    protected $casts = [
        'settings' => 'array',
    ];
    
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
        return Cache::remember(
            config('multitenant-permission.cache.prefix') . "tenant:{$id}",
            config('multitenant-permission.cache.ttl'),
            function () use ($id) {
                return static::find($id);
            }
        );
    }
    
    /**
     * Identify tenant by domain
     */
    public static function identifyByDomain(string $domain): ?self
    {
        return Cache::remember(
            config('multitenant-permission.cache.prefix') . "tenant:domain:{$domain}",
            config('multitenant-permission.cache.ttl'),
            function () use ($domain) {
                return static::where('domain', $domain)->first();
            }
        );
    }
    
    /**
     * Create a new tenant with database
     */
    public static function createWithDatabase(array $attributes): self
    {
        $tenantDatabaseManager = app(TenantDatabaseManager::class);
        
        // Validate attributes
        if (empty($attributes['database'])) {
            throw new InvalidTenantException('Database name is required');
        }
        
        // Create database
        $tenantDatabaseManager->createDatabase($attributes['database']);
        
        // Create tenant record
        $tenant = static::create($attributes);
        
        // Run migrations for tenant
        $tenantDatabaseManager->migrate($tenant);
        
        // Seed default data
        $tenantDatabaseManager->seed($tenant);
        
        return $tenant;
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
        $this->settings = array_merge($this->settings ?? [], [$key => $value]);
        $this->save();
    }
    
    /**
     * Clear tenant cache
     */
    public function clearCache(): void
    {
        Cache::forget(config('multitenant-permission.cache.prefix') . "tenant:{$this->id}");
        Cache::forget(config('multitenant-permission.cache.prefix') . "tenant:domain:{$this->domain}");
    }
    
    /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        static::updated(function ($tenant) {
            $tenant->clearCache();
        });
        
        static::deleted(function ($tenant) {
            $tenant->clearCache();
            app(TenantDatabaseManager::class)->deleteDatabase($tenant->database);
        });
    }
}
