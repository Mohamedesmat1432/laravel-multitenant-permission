<?php

namespace Esmat\MultiTenantPermission\Services;

use Esmat\MultiTenantPermission\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Esmat\MultiTenantPermission\Exceptions\InvalidTenantException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Event;
use Esmat\MultiTenantPermission\Events\DatabaseCreated;
use Esmat\MultiTenantPermission\Events\DatabaseMigrated;
use Esmat\MultiTenantPermission\Events\DatabaseSeeded;
use Esmat\MultiTenantPermission\Events\DatabaseDeleted;

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
        
        // Check if database already exists
        if ($this->databaseExists($databaseName)) {
            throw new InvalidTenantException("Database {$databaseName} already exists.");
        }
        
        // Create database
        DB::statement("CREATE DATABASE `{$databaseName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        Log::info("Database created: {$databaseName}");
        
        // Dispatch event
        Event::dispatch(new DatabaseCreated($databaseName));
    }
    
    /**
     * Delete a tenant's database
     */
    public function deleteDatabase(string $databaseName): void
    {
        if (!$this->databaseExists($databaseName)) {
            Log::warning("Attempted to delete non-existent database: {$databaseName}");
            return;
        }
        
        // Drop database
        DB::statement("DROP DATABASE IF EXISTS `{$databaseName}`");
        
        Log::info("Database deleted: {$databaseName}");
        
        // Dispatch event
        Event::dispatch(new DatabaseDeleted($databaseName));
    }
    
    /**
     * Check if a database exists
     */
    public function databaseExists(string $databaseName): bool
    {
        $result = DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?", [$databaseName]);
        
        return !empty($result);
    }
    
    /**
     * Run migrations for a tenant
     */
    public function migrate(Tenant $tenant): void
    {
        $tenant->configure()->use();
        
        Artisan::call('migrate', [
            '--path' => 'vendor/esmat/laravel-multitenant-permission/database/migrations/tenant',
            '--database' => config('multitenant-permission.tenant_connection'),
            '--force' => true,
        ]);
        
        Log::info("Migrations completed for tenant: {$tenant->name} (ID: {$tenant->id})");
        
        // Dispatch event
        Event::dispatch(new DatabaseMigrated($tenant));
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
        
        Log::info("Seeding completed for tenant: {$tenant->name} (ID: {$tenant->id})");
        
        // Dispatch event
        Event::dispatch(new DatabaseSeeded($tenant));
    }
    
    /**
     * Run migrations for all tenants
     */
    public function migrateAllTenants(): void
    {
        $tenantModel = config('multitenant-permission.tenant_model');
        $tenants = $tenantModel::all();
        
        foreach ($tenants as $tenant) {
            try {
                $this->migrate($tenant);
            } catch (\Exception $e) {
                Log::error("Failed to migrate tenant {$tenant->name}: {$e->getMessage()}");
            }
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
            try {
                $this->seed($tenant);
            } catch (\Exception $e) {
                Log::error("Failed to seed tenant {$tenant->name}: {$e->getMessage()}");
            }
        }
    }
    
    /**
     * Backup a tenant database
     */
    public function backupDatabase(Tenant $tenant, string $destination): void
    {
        $database = $tenant->database;
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');
        
        $command = "mysqldump --user={$username} --password={$password} --host={$host} {$database} > {$destination}";
        
        exec($command);
        
        Log::info("Database backup completed for tenant: {$tenant->name} (ID: {$tenant->id})");
    }
    
    /**
     * Restore a tenant database from backup
     */
    public function restoreDatabase(Tenant $tenant, string $source): void
    {
        $database = $tenant->database;
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');
        
        $command = "mysql --user={$username} --password={$password} --host={$host} {$database} < {$source}";
        
        exec($command);
        
        Log::info("Database restore completed for tenant: {$tenant->name} (ID: {$tenant->id})");
    }
}
