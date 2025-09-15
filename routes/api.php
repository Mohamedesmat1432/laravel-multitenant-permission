<?php

use Illuminate\Support\Facades\Route;
use Elgaml\MultiTenancyRbac\Http\Controllers\AuthController;
use Elgaml\MultiTenancyRbac\Http\Controllers\Api\TenantController;
use Elgaml\MultiTenancyRbac\Http\Controllers\Api\RoleController;
use Elgaml\MultiTenancyRbac\Http\Controllers\Api\PermissionController;
use Elgaml\MultiTenancyRbac\Http\Controllers\Api\UserController;

// Authentication routes
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('me', [AuthController::class, 'me'])->middleware('auth:sanctum');

// Tenant routes
Route::apiResource('tenants', TenantController::class)->middleware(['auth:sanctum', 'tenancy.initialize']);

// Role routes
Route::apiResource('roles', RoleController::class)->middleware(['auth:sanctum', 'tenancy.initialize']);
Route::post('roles/{roleId}/permissions', [RoleController::class, 'assignPermission'])->middleware(['auth:sanctum', 'tenancy.initialize']);
Route::delete('roles/{roleId}/permissions', [RoleController::class, 'revokePermission'])->middleware(['auth:sanctum', 'tenancy.initialize']);

// Permission routes
Route::apiResource('permissions', PermissionController::class)->middleware(['auth:sanctum', 'tenancy.initialize']);
Route::get('permissions/tree', [PermissionController::class, 'tree'])->middleware(['auth:sanctum', 'tenancy.initialize']);

// User routes
Route::apiResource('users', UserController::class)->middleware(['auth:sanctum', 'tenancy.initialize']);
Route::post('users/{userId}/roles', [UserController::class, 'assignRole'])->middleware(['auth:sanctum', 'tenancy.initialize']);
Route::delete('users/{userId}/roles', [UserController::class, 'revokeRole'])->middleware(['auth:sanctum', 'tenancy.initialize']);
Route::get('users/{userId}/permissions', [UserController::class, 'permissions'])->middleware(['auth:sanctum', 'tenancy.initialize']);
Route::get('users/{userId}/roles', [UserController::class, 'roles'])->middleware(['auth:sanctum', 'tenancy.initialize']);
