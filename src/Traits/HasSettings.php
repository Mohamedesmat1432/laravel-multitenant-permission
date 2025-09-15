<?php

namespace Esmat\MultiTenantPermission\Traits;

trait HasSettings
{
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
     * Get all settings
     */
    public function getSettings(): array
    {
        return $this->settings ?? [];
    }
    
    /**
     * Update multiple settings at once
     */
    public function updateSettings(array $settings): void
    {
        $currentSettings = $this->settings ?? [];
        $updatedSettings = array_merge($currentSettings, $settings);
        
        $this->settings = $updatedSettings;
        $this->save();
    }
    
    /**
     * Remove a setting
     */
    public function removeSetting(string $key): void
    {
        $settings = $this->settings ?? [];
        unset($settings[$key]);
        
        $this->settings = $settings;
        $this->save();
    }
}
