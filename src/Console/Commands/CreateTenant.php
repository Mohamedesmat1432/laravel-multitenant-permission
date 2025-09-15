<?php

namespace Esmat\MultiTenantPermission\Console\Commands;

use Illuminate\Console\Command;
use Esmat\MultiTenantPermission\Models\Tenant;
use Esmat\MultiTenantPermission\Factories\TenantFactory;
use Esmat\MultiTenantPermission\Exceptions\InvalidTenantException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class CreateTenant extends Command
{
    protected $signature = 'tenant:create 
                           {name : The name of the tenant} 
                           {domain : The domain of the tenant} 
                           {database? : The database name (optional)}
                           {--settings= : JSON string of tenant settings}
                           {--no-seed : Skip seeding the tenant database}
                           {--force : Force creation even if validation fails}';
    
    protected $description = 'Create a new tenant with database';
    
    public function handle()
    {
        $name = $this->argument('name');
        $domain = $this->argument('domain');
        $database = $this->argument('database');
        $settings = $this->option('settings');
        $noSeed = $this->option('no-seed');
        $force = $this->option('force');
        
        try {
            $this->info("Creating tenant: {$name}");
            
            // Validate input
            if (!$force) {
                $validator = Validator::make([
                    'name' => $name,
                    'domain' => $domain,
                    'database' => $database,
                ], [
                    'name' => 'required|string|max:255',
                    'domain' => 'required|string|max:255|unique:tenants,domain',
                    'database' => 'nullable|string|max:255|regex:/^[a-zA-Z0-9_]+$/|unique:tenants,database',
                ]);
                
                if ($validator->fails()) {
                    $this->error('Validation failed:');
                    foreach ($validator->errors()->all() as $error) {
                        $this->error("- {$error}");
                    }
                    return 1;
                }
            }
            
            // Parse settings
            $parsedSettings = [];
            if ($settings) {
                $parsedSettings = json_decode($settings, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->error('Invalid JSON in settings parameter');
                    return 1;
                }
            }
            
            // Create tenant
            $tenantFactory = app(TenantFactory::class);
            $tenant = $tenantFactory->create([
                'name' => $name,
                'domain' => $domain,
                'database' => $database,
                'settings' => $parsedSettings,
            ]);
            
            // Seed database if not skipped
            if (!$noSeed) {
                $this->info("Seeding tenant database...");
                app('multitenant.tenant_database_manager')->seed($tenant);
            }
            
            $this->info("Tenant created successfully!");
            $this->info("Tenant ID: {$tenant->id}");
            $this->info("Domain: {$tenant->domain}");
            $this->info("Database: {$tenant->database}");
            
            return 0;
        } catch (InvalidTenantException $e) {
            $this->error("Error creating tenant: {$e->getMessage()}");
            return 1;
        } catch (\Exception $e) {
            $this->error("An unexpected error occurred: {$e->getMessage()}");
            Log::error("Tenant creation failed: {$e->getMessage()}", [
                'exception' => $e,
            ]);
            return 1;
        }
    }
}
