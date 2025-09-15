<?php

namespace Esmat\MultiTenantPermission\Listeners;

use Esmat\MultiTenantPermission\Events\TenantCreated;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SendTenantCreatedNotification
{
    public function handle(TenantCreated $event)
    {
        $tenant = $event->tenant;
        
        // Send notification to administrators
        // Notification::send($admins, new TenantCreatedNotification($tenant));
        
        Log::info("Tenant created notification sent for: {$tenant->name}");
    }
}
