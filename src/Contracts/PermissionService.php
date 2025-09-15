<?php

namespace Esmat\MultiTenantPermission\Contracts;

use Esmat\MultiTenantPermission\Models\User;

interface PermissionService
{
    public function userHasPermission(User $user, string $permission): bool;
    public function getUserPermissions(User $user): array;
}
