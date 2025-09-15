<?php

namespace Esmat\MultiTenantPermission\Services;

use Esmat\MultiTenantPermission\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Esmat\MultiTenantPermission\Exceptions\InvalidTenantException;

class TenantDatabaseManager
{
    /**
     * Create a new database for a tenant
     */
    public function createDatabase(string $databaseName): void
    {
        // Validate database name
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $databaseName)) {
            throw new InvalidTenantException('Invalid database name. Only alphanumeric characters and underscores are allowed.');
        }
        
        // Create database
        DB::statement("CREATE DATABASE `{$databaseName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    }
    
    /**
     * Delete a tenant's database
     */
    public function deleteDatabase(string $databaseName): void
    {
        DB::statement("DROP DATABASE IF EXISTS `{$databaseName}`");
    }
    
    /**
     * Run migrations for a tenant
     */
    public function migrate(Tenant $tenant): void
    {
        $tenant->configure()->use();
        
        Artisan::call('migrate', [
            '--path' => 'vendor/Esmat/laravel-multitenant-permission/database/migrations/tenant',
            '--database' => config('multitenant-permission.tenant_connection'),
            '--force' => true,
        ]);
    }
    
    /**
     * Seed a tenant's database
     */
    public function seed(Tenant $tenant): void
    {
        $tenant->configure()->use();
        
        Artisan::call('db:seed', [
            '--class' => 'Esmat\\MultiTenantPermission\\Database\\Seeders\\TenantDatabaseSeeder',
            '--database' => config('multitenant-permission.tenant_connection'),
            '--force' => true,
        ]);
    }
    
    /**
     * Run migrations for all tenants
     */
    public function migrateAllTenants(): void
    {
        $tenantModel = config('multitenant-permission.tenant_model');
        $tenants = $tenantModel::all();
        
        foreach ($tenants as $tenant) {
            $this->migrate($tenant);
        }
    }
    
    /**
     * Seed all tenants
     */
    public function seedAllTenants(): void
    {
        $tenantModel = config('multitenant-permission.tenant_model');
        $tenants = $tenantModel::all();
        
        foreach ($tenants as $tenant) {
            $this->seed($tenant);
        }
    }
}
