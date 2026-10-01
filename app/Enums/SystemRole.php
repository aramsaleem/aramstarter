<?php

namespace App\Enums;

use Spatie\Permission\Contracts\Role as RoleContract;
use Spatie\Permission\Models\Role;

enum SystemRole: string
{
    /** Passes every permission check through Gate::after - see AppServiceProvider. */
    case SuperAdmin = 'Super Admin';

    case Admin = 'Admin';

    /** Assigned to everyone who registers. */
    case User = 'User';

    /**
     * Protected roles are referenced by name in code, so they can't be renamed or deleted.
     */
    public function isProtected(): bool
    {
        return $this !== self::Admin;
    }

    /**
     * Permissions granted when the role is seeded.
     *
     * @return list<SystemPermission>
     */
    public function defaultPermissions(): array
    {
        return match ($this) {
            self::SuperAdmin, self::User => [],
            self::Admin => [
                SystemPermission::AccessAdminPanel,
                SystemPermission::ViewUsers,
                SystemPermission::CreateUsers,
                SystemPermission::UpdateUsers,
                SystemPermission::DeleteUsers,
                SystemPermission::ImpersonateUsers,
                SystemPermission::ViewRoles,
                SystemPermission::ViewPermissions,
                SystemPermission::ManageContent,
                SystemPermission::ViewActivity,
            ],
        };
    }

    public static function isProtectedName(string $name): bool
    {
        return self::tryFrom($name)?->isProtected() ?? false;
    }

    /**
     * The role's database record, created on the fly if the seeder hasn't run yet.
     */
    public function role(): RoleContract
    {
        return Role::findOrCreate($this->value);
    }
}
