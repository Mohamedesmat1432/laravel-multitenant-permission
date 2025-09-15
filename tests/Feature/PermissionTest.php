<?php

namespace Esmat\MultiTenantPermission\Tests\Feature;

use Esmat\MultiTenantPermission\Models\Tenant;
use Esmat\MultiTenantPermission\Models\User;
use Tests\TestCase;

class PermissionTest extends TestCase
{
    public function test_permission_middleware()
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
        
        $user->assignRole('user');
        
        $token = $user->createToken('test-token')->plainTextToken;
        
        $response = $this->withHeaders([
            'X-Tenant-ID' => $tenant->id,
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/users');
        
        $response->assertStatus(403);
    }
    
    public function test_role_permission_assignment()
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
        
        $user->assignRole('user');
        $this->assertTrue($user->hasRole('user'));
        
        $user->assignRole('admin');
        $this->assertTrue($user->hasRole('admin'));
        
        $user->removeRole('user');
        $this->assertFalse($user->hasRole('user'));
    }
    
    public function test_wildcard_permissions()
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
        
        $this->assertTrue($user->hasPermission('users.view'));
        $this->assertTrue($user->hasPermission('users.create'));
        $this->assertTrue($user->hasPermission('users.*'));
    }
}
