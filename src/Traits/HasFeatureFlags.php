<?php

namespace Elgaml\MultiTenancyRbac\Traits;

use Elgaml\MultiTenancyRbac\Models\FeatureFlag;

trait HasFeatureFlags
{
    public function featureFlags()
    {
        return $this->hasMany(FeatureFlag::class);
    }
    
    public function hasFeatureFlag($key)
    {
        return $this->featureFlags()->where('key', $key)->where('enabled', true)->exists();
    }
    
    public function enableFeatureFlag($key)
    {
        $flag = $this->featureFlags()->updateOrCreate(['key' => $key], ['enabled' => true]);
        
        return $flag;
    }
    
    public function disableFeatureFlag($key)
    {
        $flag = $this->featureFlags()->where('key', $key)->first();
        
        if ($flag) {
            $flag->enabled = false;
            $flag->save();
        }
        
        return $flag;
    }
}
