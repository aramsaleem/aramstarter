<?php

namespace App\Policies;

use App\Enums\SystemPermission;
use App\Models\User;
use Spatie\Permission\Models\Permission;

class PermissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(SystemPermission::ViewPermissions->value);
    }

    public function create(User $user): bool
    {
        return $user->can(SystemPermission::CreatePermissions->value);
    }

    /**
     * System permissions are referenced in code, so they can't be renamed.
     */
    public function update(User $user, Permission $permission): bool
    {
        return $user->can(SystemPermission::UpdatePermissions->value)
            && ! SystemPermission::isSystem($permission->name);
    }

    public function delete(User $user, Permission $permission): bool
    {
        return $user->can(SystemPermission::DeletePermissions->value)
            && ! SystemPermission::isSystem($permission->name);
    }
}
