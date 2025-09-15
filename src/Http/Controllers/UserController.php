<?php

namespace Esmat\MultiTenantPermission\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Esmat\MultiTenantPermission\Models\User;
use Esmat\MultiTenantPermission\Http\Resources\UserResource;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::query();
        
        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        // Filter by role
        if ($request->has('role')) {
            $role = $request->role;
            $query->whereHas('roles', function ($q) use ($role) {
                $q->where('name', $role);
            });
        }
        
        // Pagination
        $users = $query->latest()->paginate($request->per_page ?? 15);
        
        return UserResource::collection($users);
    }
    
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
        ]);
        
        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);
        
        // Assign roles
        if ($request->has('roles')) {
            $user->roles()->sync($request->roles);
        }
        
        return new UserResource($user);
    }
    
    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return new UserResource($user);
    }
    
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:8|confirmed',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
        ]);
        
        // Update user
        $user->update($request->only(['name', 'email']));
        
        // Update password if provided
        if ($request->filled('password')) {
            $user->update(['password' => bcrypt($request->password)]);
        }
        
        // Sync roles
        if ($request->has('roles')) {
            $user->roles()->sync($request->roles);
        }
        
        return new UserResource($user);
    }
    
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // Prevent deletion of the last admin
        if ($user->hasRole('super-admin') && User::role('super-admin')->count() === 1) {
            return response()->json([
                'message' => 'Cannot delete the last super admin',
            ], 422);
        }
        
        $user->delete();
        
        return response()->json(null, 204);
    }
    
    /**
     * Assign a role to a user.
     */
    public function assignRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|exists:roles,name',
        ]);
        
        $user->assignRole($request->role);
        
        return response()->json([
            'message' => 'Role assigned successfully',
            'user' => new UserResource($user),
        ]);
    }
    
    /**
     * Remove a role from a user.
     */
    public function removeRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|exists:roles,name',
        ]);
        
        // Prevent removing the last super admin role
        if ($request->role === 'super-admin' && $user->hasRole('super-admin') && User::role('super-admin')->count() === 1) {
            return response()->json([
                'message' => 'Cannot remove the last super admin role',
            ], 422);
        }
        
        $user->removeRole($request->role);
        
        return response()->json([
            'message' => 'Role removed successfully',
            'user' => new UserResource($user),
        ]);
    }
}
