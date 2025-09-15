<?php

namespace Esmat\MultiTenantPermission\Tests\Feature;

use Esmat\MultiTenantPermission\Models\Tenant;
use Esmat\MultiTenantPermission\Models\User;
use Tests\TestCase;

class MultiTenantTest extends TestCase
{
    public function test_tenant_creation()
    {
        $tenant = Tenant::createWithDatabase([
            'name' => 'Test Tenant',
            'domain' => 'test.example.com',
            'database' => 'test_db_' . time()
        ]);

        $this->assertDatabaseHas('tenants', [
            'name' => 'Test Tenant'
        ]);
        
        $this->assertNotNull($tenant->id);
    }
    
    public function test_tenant_identification_by_id()
    {
        $tenant = Tenant::createWithDatabase([
            'name' => 'Test Tenant',
            'domain' => 'test.example.com',
            'database' => 'test_db_' . time()
        ]);
        
        $identifiedTenant = Tenant::identifyById($tenant->id);
        
        $this->assertEquals($tenant->id, $identifiedTenant->id);
    }
    
    public function test_tenant_identification_by_domain()
    {
        $tenant = Tenant::createWithDatabase([
            'name' => 'Test Tenant',
            'domain' => 'test.example.com',
            'database' => 'test_db_' . time()
        ]);
        
        $identifiedTenant = Tenant::identifyByDomain('test.example.com');
        
        $this->assertEquals($tenant->id, $identifiedTenant->id);
    }
    
    public function test_user_has_role()
    {
        $tenant = Tenant::createWithDatabase([
            'name' => 'Test Tenant',
            'domain' => 'test.example.com',
            'database' => 'test_db_' . time()
        ]);
        
        $tenant->configure()->use();
        
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password')
        ]);
        
        $user->assignRole('admin');
        
        $this->assertTrue($user->hasRole('admin'));
        $this->assertFalse($user->hasRole('user'));
    }
    
    public function test_user_has_permission()
    {
        $tenant = Tenant::createWithDatabase([
            'name' => 'Test Tenant',
            'domain' => 'test.example.com',
            'database' => 'test_db_' . time()
        ]);
        
        $tenant->configure()->use();
        
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password')
        ]);
        
        $user->assignRole('admin');
        
        $this->assertTrue($user->hasPermission('manage-users'));
        $this->assertFalse($user->hasPermission('non-existent-permission'));
    }
    
    public function test_api_authentication()
    {
        $tenant = Tenant::createWithDatabase([
            'name' => 'Test Tenant',
            'domain' => 'test.example.com',
            'database' => 'test_db_' . time()
        ]);
        
        $tenant->configure()->use();
        
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password')
        ]);
        
        $user->assignRole('admin');
        
        $response = $this->withHeaders([
            'X-Tenant-ID' => $tenant->id,
        ])->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);
        
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'access_token',
                     'token_type',
                     'user' => [
                         'id',
                         'name',
                         'email',
                         'roles',
                         'permissions'
                     ]
                 ]);
    }
}
