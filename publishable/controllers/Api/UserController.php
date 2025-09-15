<?php

namespace App\Http\Controllers\Api\MultiTenancyRbac;

use App\Http\Controllers\Controller;
use App\Models\MultiTenancyRbac\User;
use Elgaml\MultiTenancyRbac\Services\RbacService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    protected $rbacService;
    
    public function __construct(RbacService $rbacService)
    {
        $this->rbacService = $rbacService;
    }
    
    public function index()
    {
        $this->authorize('viewAny', User::class);
        
        return User::all();
    }
    
    public function store(Request $request)
    {
        $this->authorize('create', User::class);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);
        
        $validated['password'] = Hash::make($validated['password']);
        $validated['tenant_id'] = tenant('id');
        
        $user = User::create($validated);
        
        return response()->json($user, 201);
    }
    
    public function show($id)
    {
        $user = User::findOrFail($id);
        $this->authorize('view', $user);
        
        return $user;
    }
    
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $this->authorize('update', $user);
        
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,'.$id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);
        
        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }
        
        $user->update($validated);
        
        return $user;
    }
    
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $this->authorize('delete', $user);
        
        $user->delete();
        
        return response()->json(null, 204);
    }
    
    public function assignRole(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        $this->authorize('update', $user);
        
        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);
        
        $this->rbacService->assignRoleToUser($user, $validated['role_id']);
        
        return response()->json(['message' => 'Role assigned successfully']);
    }
    
    public function revokeRole(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        $this->authorize('update', $user);
        
        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);
        
        $this->rbacService->removeRoleFromUser($user, $validated['role_id']);
        
        return response()->json(['message' => 'Role revoked successfully']);
    }
    
    public function permissions($userId)
    {
        $user = User::findOrFail($userId);
        $this->authorize('view', $user);
        
        return $this->rbacService->getUserPermissions($user);
    }
    
    public function roles($userId)
    {
        $user = User::findOrFail($userId);
        $this->authorize('view', $user);
        
        return $this->rbacService->getUserRoles($user);
    }
}
