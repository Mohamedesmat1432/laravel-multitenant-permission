<?php

namespace Elgaml\MultiTenancyRbac\Traits;

trait HasSettings
{
    public function getSetting($key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }
    
    public function setSetting($key, $value)
    {
        $settings = $this->settings ?? [];
        $settings[$key] = $value;
        
        $this->settings = $settings;
        $this->save();
        
        return $this;
    }
    
    public function removeSetting($key)
    {
        $settings = $this->settings ?? [];
        unset($settings[$key]);
        
        $this->settings = $settings;
        $this->save();
        
        return $this;
    }
}
