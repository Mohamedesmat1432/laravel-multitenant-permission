<?php

namespace Esmat\MultiTenantPermission\Tests\Feature;

use Esmat\MultiTenantPermission\Models\User;
use Esmat\MultiTenantPermission\Models\Role;
use Esmat\MultiTenantPermission\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PermissionTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_user_has_role()
    {
        $user = $this->createUser('admin');
        
        $this->assertTrue($user->hasRole('admin'));
        $this->assertFalse($user->hasRole('user'));
    }
    
    public function test_user_has_permission()
    {
        $user = $this->createUser('admin');
        
        $this->assertTrue($user->hasPermission('manage-users'));
        $this->assertFalse($user->hasPermission('non-existent-permission'));
    }
    
    public function test_wildcard_permissions()
    {
        $role = $this->createRole('manager', ['users.*']);
        $user = $this->createUser();
        $user->assignRole($role);
        
        $this->assertTrue($user->hasPermission('users.view'));
        $this->assertTrue($user->hasPermission('users.create'));
        $this->assertTrue($user->hasPermission('users.update'));
        $this->assertTrue($user->hasPermission('users.delete'));
        $this->assertFalse($user->hasPermission('posts.view'));
    }
    
    public function test_role_permission_assignment()
    {
        $role = $this->createRole('editor');
        
        // Assign a permission
        $role->givePermissionTo('edit-posts');
        $this->assertTrue($role->hasPermission('edit-posts'));
        
        // Assign multiple permissions
        $role->syncPermissions(['edit-posts', 'publish-posts']);
        $this->assertTrue($role->hasPermission('edit-posts'));
        $this->assertTrue($role->hasPermission('publish-posts'));
        
        // Revoke a permission
        $role->revokePermissionTo('edit-posts');
        $this->assertFalse($role->hasPermission('edit-posts'));
        $this->assertTrue($role->hasPermission('publish-posts'));
    }
    
    public function test_user_role_assignment()
    {
        $user = $this->createUser();
        
        // Assign a role
        $user->assignRole('editor');
        $this->assertTrue($user->hasRole('editor'));
        
        // Assign multiple roles
        $user->syncRoles(['editor', 'moderator']);
        $this->assertTrue($user->hasRole('editor'));
        $this->assertTrue($user->hasRole('moderator'));
        
        // Remove a role
        $user->removeRole('editor');
        $this->assertFalse($user->hasRole('editor'));
        $this->assertTrue($user->hasRole('moderator'));
    }
    
    public function test_permission_caching()
    {
        $user = $this->createUser('admin');
        
        // First call should hit the database
        $this->assertTrue($user->hasPermission('manage-users'));
        
        // Subsequent calls should use cache
        $this->assertTrue($user->hasPermission('manage-users'));
        
        // Clear cache and check again
        $user->clearPermissionCache();
        $this->assertTrue($user->hasPermission('manage-users'));
    }
}
