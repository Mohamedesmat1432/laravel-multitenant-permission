<?php

namespace Esmat\MultiTenantPermission\Services;

use Esmat\MultiTenantPermission\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    /**
     * Log an action
     */
    public function log(string $action, string $description, array $data = []): void
    {
        if (!config('multitenant-permission.features.audit_logs')) {
            return;
        }
        
        $tenant = \Esmat\MultiTenantPermission\Facades\Tenant::current();
        $user = Auth::user();
        
        AuditLog::create([
            'tenant_id' => $tenant ? $tenant->id : null,
            'user_id' => $user ? $user->id : null,
            'action' => $action,
            'description' => $description,
            'data' => json_encode($data),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
    
    /**
     * Log a tenant-related action
     */
    public function logTenantAction(string $action, string $description, array $data = []): void
    {
        $this->log($action, $description, array_merge($data, [
            'type' => 'tenant',
        ]));
    }
    
    /**
     * Log a user-related action
     */
    public function logUserAction(string $action, string $description, array $data = []): void
    {
        $this->log($action, $description, array_merge($data, [
            'type' => 'user',
        ]));
    }
    
    /**
     * Log a permission-related action
     */
    public function logPermissionAction(string $action, string $description, array $data = []): void
    {
        $this->log($action, $description, array_merge($data, [
            'type' => 'permission',
        ]));
    }
}
