<?php

namespace App\Http\Controllers\Api\MultiTenancyRbac;

use App\Http\Controllers\Controller;
use App\Models\MultiTenancyRbac\Role;
use Elgaml\MultiTenancyRbac\Services\RbacService;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    protected $rbacService;
    
    public function __construct(RbacService $rbacService)
    {
        $this->rbacService = $rbacService;
    }
    
    public function index()
    {
        $this->authorize('viewAny', Role::class);
        
        return Role::all();
    }
    
    public function store(Request $request)
    {
        $this->authorize('create', Role::class);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'display_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);
        
        $role = $this->rbacService->createRole($validated);
        
        return response()->json($role, 201);
    }
    
    public function show($id)
    {
        $role = Role::findOrFail($id);
        $this->authorize('view', $role);
        
        return $role;
    }
    
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        $this->authorize('update', $role);
        
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'display_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);
        
        $role->update($validated);
        
        return $role;
    }
    
    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        $this->authorize('delete', $role);
        
        $role->delete();
        
        return response()->json(null, 204);
    }
    
    public function assignPermission(Request $request, $roleId)
    {
        $role = Role::findOrFail($roleId);
        $this->authorize('update', $role);
        
        $validated = $request->validate([
            'permission_id' => 'required|exists:permissions,id',
        ]);
        
        $this->rbacService->assignPermissionToRole($role, $validated['permission_id']);
        
        return response()->json(['message' => 'Permission assigned successfully']);
    }
    
    public function revokePermission(Request $request, $roleId)
    {
        $role = Role::findOrFail($roleId);
        $this->authorize('update', $role);
        
        $validated = $request->validate([
            'permission_id' => 'required|exists:permissions,id',
        ]);
        
        $this->rbacService->removePermissionFromRole($role, $validated['permission_id']);
        
        return response()->json(['message' => 'Permission revoked successfully']);
    }
}
