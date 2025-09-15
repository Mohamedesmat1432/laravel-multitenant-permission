<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\MultiTenancyRbac\RegisterController;
use App\Http\Controllers\Auth\MultiTenancyRbac\LoginController;
use App\Http\Controllers\Auth\MultiTenancyRbac\LogoutController;
use App\Http\Controllers\Auth\MultiTenancyRbac\ProfileController;
use App\Http\Controllers\Auth\MultiTenancyRbac\TokenController;
use App\Http\Controllers\Api\MultiTenancyRbac\TenantController;
use App\Http\Controllers\Api\MultiTenancyRbac\RoleController;
use App\Http\Controllers\Api\MultiTenancyRbac\PermissionController;
use App\Http\Controllers\Api\MultiTenancyRbac\UserController;

// Public routes
Route::post('register', [RegisterController::class, 'register']);
Route::post('login', [LoginController::class, 'login']);

// Authenticated routes
Route::middleware(['auth:sanctum', 'tenancy.initialize'])->group(function () {
    // Authentication routes
    Route::post('logout', [LogoutController::class, 'logout']);
    Route::get('profile', [ProfileController::class, 'profile']);
    Route::put('profile', [ProfileController::class, 'update']);
    
    // Token management routes
    Route::get('tokens', [TokenController::class, 'tokens']);
    Route::post('tokens', [TokenController::class, 'createToken']);
    Route::delete('tokens/{tokenId}', [TokenController::class, 'deleteToken']);
    Route::delete('tokens', [TokenController::class, 'deleteAllTokens']);
    
    // Tenant routes
    Route::apiResource('tenants', TenantController::class);
    
    // Role routes
    Route::apiResource('roles', RoleController::class);
    Route::post('roles/{roleId}/permissions', [RoleController::class, 'assignPermission']);
    Route::delete('roles/{roleId}/permissions', [RoleController::class, 'revokePermission']);
    
    // Permission routes
    Route::apiResource('permissions', PermissionController::class);
    Route::get('permissions/tree', [PermissionController::class, 'tree']);
    
    // User routes
    Route::apiResource('users', UserController::class);
    Route::post('users/{userId}/roles', [UserController::class, 'assignRole']);
    Route::delete('users/{userId}/roles', [UserController::class, 'revokeRole']);
    Route::get('users/{userId}/permissions', [UserController::class, 'permissions']);
    Route::get('users/{userId}/roles', [UserController::class, 'roles']);
});
