<?php

namespace Esmat\MultiTenantPermission\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Esmat\MultiTenantPermission\Models\User;

class AuthController extends Controller
{
    /**
     * Handle a login request to the application.
     */
    public function login(Request $request)
    {
        // Validate request
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        
        // Check rate limiting
        if (config('multitenant-permission.security.rate_limiting.enabled')) {
            $this->checkRateLimit($request);
        }
        
        // Find user
        $user = User::where('email', $request->email)->first();
        
        // Verify credentials
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => [trans('auth.failed')],
            ]);
        }
        
        // Create token
        $token = $user->createToken('auth_token')->plainTextToken;
        
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name'),
                'permissions' => $user->getAllPermissions(),
            ],
        ]);
    }
    
    /**
     * Handle a logout request to the application.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        
        return response()->json(['message' => 'Successfully logged out']);
    }
    
    /**
     * Get the authenticated user.
     */
    public function user(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->roles->pluck('name'),
            'permissions' => $user->getAllPermissions(),
        ]);
    }
    
    /**
     * Check the rate limit for the login request.
     */
    protected function checkRateLimit(Request $request)
    {
        $key = 'login_attempt:' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($key, config('multitenant-permission.security.rate_limiting.attempts'))) {
            $seconds = RateLimiter::availableIn($key);
            
            throw ValidationException::withMessages([
                'email' => [trans('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ])],
            ]);
        }
        
        RateLimiter::hit($key, config('multitenant-permission.security.rate_limiting.decay_minutes') * 60);
    }
}
