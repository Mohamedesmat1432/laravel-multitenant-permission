<?php

namespace Esmat\MultiTenantPermission\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

abstract class BaseModel extends Model
{
    /**
     * Cache key prefix
     */
    protected $cachePrefix = 'model:';
    
    /**
     * Cache TTL in seconds
     */
    protected $cacheTtl = 3600;
    
    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();
        
        static::saved(function ($model) {
            $model->clearCache();
        });
        
        static::deleted(function ($model) {
            $model->clearCache();
        });
    }
    
    /**
     * Get cache key for this model
     */
    public function getCacheKey(): string
    {
        return $this->cachePrefix . $this->getTable() . ':' . $this->getKey();
    }
    
    /**
     * Clear cache for this model
     */
    public function clearCache(): void
    {
        Cache::forget($this->getCacheKey());
    }
    
    /**
     * Get a cached attribute or compute and cache it
     */
    protected function getCachedAttribute(string $attribute, \Closure $callback)
    {
        $key = $this->getCacheKey() . ':' . $attribute;
        
        return Cache::remember($key, $this->cacheTtl, $callback);
    }
}
