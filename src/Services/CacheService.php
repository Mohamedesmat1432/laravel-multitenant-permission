<?php

namespace Esmat\MultiTenantPermission\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class CacheService
{
    /**
     * Get a cached value or compute and store it
     */
    public function remember(string $key, int $ttl, \Closure $callback)
    {
        if (config('multitenant-permission.cache.driver') === 'redis') {
            return Redis::remember($key, $ttl, $callback);
        }
        
        return Cache::remember($key, $ttl, $callback);
    }
    
    /**
     * Forget a cached value
     */
    public function forget(string $key): bool
    {
        if (config('multitenant-permission.cache.driver') === 'redis') {
            return Redis::del($key);
        }
        
        return Cache::forget($key);
    }
    
    /**
     * Clear all cache with a prefix
     */
    public function clearPrefix(string $prefix): void
    {
        if (config('multitenant-permission.cache.driver') === 'redis') {
            $keys = Redis::keys($prefix . '*');
            
            if (!empty($keys)) {
                Redis::del($keys);
            }
        } else {
            // For other cache drivers, we need to track keys
            // This is a simplified implementation
            Cache::flush();
        }
    }
    
    /**
     * Get the cache prefix
     */
    public function getPrefix(): string
    {
        return config('multitenant-permission.cache.prefix', 'multitenant:');
    }
}
