<?php

namespace Esmat\MultiTenantPermission\Database\Seeders;

use Illuminate\Database\Seeder;
use Esmat\MultiTenantPermission\Models\Permission;
use Esmat\MultiTenantPermission\Models\Role;

class TenantDatabaseSeeder extends Seeder
{
    public function run()
    {
        // Create permissions
        $permissions = [
            ['name' => 'manage-users', 'description' => 'Manage users', 'group' => 'User Management'],
            ['name' => 'manage-roles', 'description' => 'Manage roles', 'group' => 'Role Management'],
            ['name' => 'manage-permissions', 'description' => 'Manage permissions', 'group' => 'Permission Management'],
            ['name' => 'view-audit-logs', 'description' => 'View audit logs', 'group' => 'System'],
            ['name' => 'manage-tenant-settings', 'description' => 'Manage tenant settings', 'group' => 'System'],
            ['name' => 'view-users', 'description' => 'View users', 'group' => 'User Management'],
            ['name' => 'create-users', 'description' => 'Create users', 'group' => 'User Management'],
            ['name' => 'edit-users', 'description' => 'Edit users', 'group' => 'User Management'],
            ['name' => 'view-reports', 'description' => 'View reports', 'group' => 'Reporting'],
            ['name' => 'manage-inventory', 'description' => 'Manage inventory', 'group' => 'Inventory'],
            ['name' => 'view-profile', 'description' => 'View profile', 'group' => 'Profile'],
            ['name' => 'edit-profile', 'description' => 'Edit profile', 'group' => 'Profile'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }

        // Create roles and assign permissions
        $roles = config('multitenant-permission.default_roles');

        foreach ($roles as $roleName => $roleData) {
            $role = Role::create([
                'name' => $roleName,
                'description' => $roleData['description'] ?? null,
            ]);

            if ($roleData['permissions'] === ['*']) {
                // Super admin gets all permissions
                $role->permissions()->attach(Permission::all()->pluck('id'));
            } else {
                $permissionIds = Permission::whereIn('name', $roleData['permissions'])->pluck('id');
                $role->permissions()->attach($permissionIds);
            }
        }
    }
}
