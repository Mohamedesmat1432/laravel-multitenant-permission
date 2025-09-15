<?php

use Illuminate\Support\Facades\Route;
use Esmat\MultiTenantPermission\Http\Controllers\AuthController;
use Esmat\MultiTenantPermission\Http\Controllers\UserController;
use Esmat\MultiTenantPermission\Http\Controllers\RoleController;

// Public routes
Route::prefix(config('multitenant-permission.api.prefix'))->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    
    // Protected routes
    Route::middleware(config('multitenant-permission.api.middleware'))->group(function () {
        // Authentication
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('user', [AuthController::class, 'user']);
        
        // User management
        Route::apiResource('users', UserController::class);
        Route::post('users/{user}/assign-role', [UserController::class, 'assignRole']);
        Route::post('users/{user}/remove-role', [UserController::class, 'removeRole']);
        
        // Role management
        Route::apiResource('roles', RoleController::class);
        Route::post('roles/{role}/give-permission', [RoleController::class, 'givePermission']);
        Route::post('roles/{role}/revoke-permission', [RoleController::class, 'revokePermission']);
    });
});
