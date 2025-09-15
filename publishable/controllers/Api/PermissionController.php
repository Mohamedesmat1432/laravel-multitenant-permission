<?php

namespace App\Http\Controllers\Api\MultiTenancyRbac;

use App\Http\Controllers\Controller;
use App\Models\MultiTenancyRbac\Permission;
use Elgaml\MultiTenancyRbac\Services\RbacService;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    protected $rbacService;
    
    public function __construct(RbacService $rbacService)
    {
        $this->rbacService = $rbacService;
    }
    
    public function index()
    {
        $this->authorize('viewAny', Permission::class);
        
        return Permission::all();
    }
    
    public function store(Request $request)
    {
        $this->authorize('create', Permission::class);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'display_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);
        
        $permission = $this->rbacService->createPermission($validated);
        
        return response()->json($permission, 201);
    }
    
    public function show($id)
    {
        $permission = Permission::findOrFail($id);
        $this->authorize('view', $permission);
        
        return $permission;
    }
    
    public function update(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);
        $this->authorize('update', $permission);
        
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'display_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);
        
        $permission->update($validated);
        
        return $permission;
    }
    
    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);
        $this->authorize('delete', $permission);
        
        $permission->delete();
        
        return response()->json(null, 204);
    }
    
    public function tree()
    {
        $this->authorize('viewAny', Permission::class);
        
        return $this->rbacService->getPermissionsTree();
    }
}
