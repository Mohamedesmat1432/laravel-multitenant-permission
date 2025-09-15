<?php

namespace Esmat\MultiTenantPermission\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Esmat\MultiTenantPermission\Models\Role;
use Esmat\MultiTenantPermission\Http\Resources\RoleResource;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Role::query();
        
        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Pagination
        $roles = $query->latest()->paginate($request->per_page ?? 15);
        
        return RoleResource::collection($roles);
    }
    
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles',
            'description' => 'nullable|string|max:255',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);
        
        // Create role
        $role = Role::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);
        
        // Assign permissions
        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        }
        
        return new RoleResource($role);
    }
    
    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        return new RoleResource($role);
    }
    
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255|unique:roles,name,' . $role->id,
            'description' => 'nullable|string|max:255',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);
        
        // Update role
        $role->update($request->only(['name', 'description']));
        
        // Sync permissions
        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        }
        
        return new RoleResource($role);
    }
    
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        // Prevent deletion of system roles
        if (in_array($role->name, ['super-admin', 'admin', 'manager', 'user'])) {
            return response()->json([
                'message' => 'Cannot delete a system role',
            ], 422);
        }
        
        // Check if role has users
        if ($role->users()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete a role that has users assigned',
            ], 422);
        }
        
        $role->delete();
        
        return response()->json(null, 204);
    }
    
    /**
     * Give a permission to a role.
     */
    public function givePermission(Request $request, Role $role)
    {
        $request->validate([
            'permission' => 'required|exists:permissions,name',
        ]);
        
        $role->givePermissionTo($request->permission);
        
        return response()->json([
            'message' => 'Permission granted successfully',
            'role' => new RoleResource($role),
        ]);
    }
    
    /**
     * Revoke a permission from a role.
     */
    public function revokePermission(Request $request, Role $role)
    {
        $request->validate([
            'permission' => 'required|exists:permissions,name',
        ]);
        
        $role->revokePermissionTo($request->permission);
        
        return response()->json([
            'message' => 'Permission revoked successfully',
            'role' => new RoleResource($role),
        ]);
    }
}
