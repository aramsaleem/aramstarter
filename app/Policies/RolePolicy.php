<?php

namespace App\Policies;

use App\Enums\SystemPermission;
use App\Enums\SystemRole;
use App\Models\User;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(SystemPermission::ViewRoles->value);
    }

    public function create(User $user): bool
    {
        return $user->can(SystemPermission::CreateRoles->value);
    }

    /**
     * The Super Admin role has every permission implicitly, so there is nothing to edit.
     * Roles that grant more than you hold are off limits too.
     */
    public function update(User $user, Role $role): bool
    {
        return $user->can(SystemPermission::UpdateRoles->value)
            && $role->name !== SystemRole::SuperAdmin->value
            && $user->canGrantPermissions($role->permissions->pluck('name'));
    }

    public function delete(User $user, Role $role): bool
    {
        return $user->can(SystemPermission::DeleteRoles->value)
            && ! SystemRole::isProtectedName($role->name)
            && $user->canGrantPermissions($role->permissions->pluck('name'));
    }
}
