<?php

namespace Esmat\MultiTenantPermission\Listeners;

use Esmat\MultiTenantPermission\Events\TenantCreated;
use Esmat\MultiTenantPermission\Events\TenantUpdated;
use Esmat\MultiTenantPermission\Events\TenantDeleted;
use Illuminate\Support\Facades\Log;

class LogTenantActivity
{
    public function handleTenantCreated(TenantCreated $event)
    {
        $tenant = $event->tenant;
        
        Log::info("Tenant created: {$tenant->name} (ID: {$tenant->id})");
    }
    
    public function handleTenantUpdated(TenantUpdated $event)
    {
        $tenant = $event->tenant;
        $changes = $event->changes;
        
        Log::info("Tenant updated: {$tenant->name} (ID: {$tenant->id})", [
            'changes' => $changes
        ]);
    }
    
    public function handleTenantDeleted(TenantDeleted $event)
    {
        $tenant = $event->tenant;
        
        Log::info("Tenant deleted: {$tenant->name} (ID: {$tenant->id})");
    }
    
    public function subscribe($events)
    {
        $events->listen(
            TenantCreated::class,
            [LogTenantActivity::class, 'handleTenantCreated']
        );
        
        $events->listen(
            TenantUpdated::class,
            [LogTenantActivity::class, 'handleTenantUpdated']
        );
        
        $events->listen(
            TenantDeleted::class,
            [LogTenantActivity::class, 'handleTenantDeleted']
        );
    }
}
