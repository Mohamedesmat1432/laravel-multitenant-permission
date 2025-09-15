<?php

namespace Esmat\MultiTenantPermission\Tests;

use Esmat\MultiTenantPermission\Models\Tenant;
use Esmat\MultiTenantPermission\Models\User;
use Esmat\MultiTenantPermission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    
    protected $tenant;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test tenant
        $this->tenant = Tenant::create([
            'name' => 'Test Tenant',
            'domain' => 'test.example.com',
            'database' => 'test_tenant_' . time(),
        ]);
        
        // Configure the tenant
        $this->tenant->configure()->use();
        
        // Run migrations for the tenant
        $this->artisan('migrate', [
            '--path' => 'vendor/esmat/laravel-multitenant-permission/database/migrations/tenant',
            '--database' => 'tenant',
        ])->run();
    }
    
    protected function tearDown(): void
    {
        // Clean up the test tenant database
        DB::statement("DROP DATABASE IF EXISTS `{$this->tenant->database}`");
        
        // Delete the tenant record
        $this->tenant->delete();
        
        parent::tearDown();
    }
    
    /**
     * Create a test user with the given role
     */
    protected function createUser(string $role = 'user'): User
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
        
        $user->assignRole($role);
        
        return $user;
    }
    
    /**
     * Create a test role with the given permissions
     */
    protected function createRole(string $name, array $permissions = []): Role
    {
        $role = Role::create([
            'name' => $name,
            'description' => "Test {$name} role",
        ]);
        
        foreach ($permissions as $permission) {
            $role->givePermissionTo($permission);
        }
        
        return $role;
    }
}
