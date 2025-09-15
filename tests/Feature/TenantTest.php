<?php

namespace Esmat\MultiTenantPermission\Tests\Feature;

use Esmat\MultiTenantPermission\Models\Tenant;
use Esmat\MultiTenantPermission\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TenantTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_tenant_creation()
    {
        $tenant = Tenant::create([
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
        $tenant = Tenant::create([
            'name' => 'Test Tenant',
            'domain' => 'test.example.com',
            'database' => 'test_db_' . time()
        ]);
        
        $identifiedTenant = Tenant::identifyById($tenant->id);
        
        $this->assertEquals($tenant->id, $identifiedTenant->id);
    }
    
    public function test_tenant_identification_by_domain()
    {
        $tenant = Tenant::create([
            'name' => 'Test Tenant',
            'domain' => 'test.example.com',
            'database' => 'test_db_' . time()
        ]);
        
        $identifiedTenant = Tenant::identifyByDomain('test.example.com');
        
        $this->assertEquals($tenant->id, $identifiedTenant->id);
    }
    
    public function test_tenant_configuration()
    {
        $tenant = Tenant::create([
            'name' => 'Test Tenant',
            'domain' => 'test.example.com',
            'database' => 'test_db_' . time()
        ]);
        
        // Configure the tenant
        $configuredTenant = $tenant->configure();
        
        $this->assertSame($tenant, $configuredTenant);
        
        // Check that the database connection is configured
        $connection = config('database.connections.' . config('multitenant-permission.tenant_connection'));
        $this->assertEquals($tenant->database, $connection['database']);
    }
    
    public function test_tenant_settings()
    {
        $tenant = Tenant::create([
            'name' => 'Test Tenant',
            'domain' => 'test.example.com',
            'database' => 'test_db_' . time()
        ]);
        
        // Test getting a setting that doesn't exist
        $this->assertNull($tenant->getSetting('non_existent'));
        $this->assertEquals('default', $tenant->getSetting('non_existent', 'default'));
        
        // Test setting a value
        $tenant->setSetting('test_key', 'test_value');
        $this->assertEquals('test_value', $tenant->getSetting('test_key'));
        
        // Test updating a setting
        $tenant->setSetting('test_key', 'updated_value');
        $this->assertEquals('updated_value', $tenant->getSetting('test_key'));
        
        // Test features
        $this->assertFalse($tenant->featureEnabled('new_dashboard'));
        $tenant->enableFeature('new_dashboard');
        $this->assertTrue($tenant->featureEnabled('new_dashboard'));
        $tenant->disableFeature('new_dashboard');
        $this->assertFalse($tenant->featureEnabled('new_dashboard'));
    }
}
