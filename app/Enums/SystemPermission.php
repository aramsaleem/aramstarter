<?php

namespace App\Enums;

/**
 * Permissions the application relies on in code (routes, policies, views).
 * They are seeded by RolesAndPermissionsSeeder and cannot be renamed or
 * deleted from the admin panel. Custom permissions can still be added there.
 */
enum SystemPermission: string
{
    case AccessAdminPanel = 'admin.access';

    case ViewUsers = 'users.view';
    case CreateUsers = 'users.create';
    case UpdateUsers = 'users.update';
    case DeleteUsers = 'users.delete';

    /** Sign in as another user to see what they see. Every session is recorded in the activity log. */
    case ImpersonateUsers = 'users.impersonate';

    case ViewRoles = 'roles.view';
    case CreateRoles = 'roles.create';
    case UpdateRoles = 'roles.update';
    case DeleteRoles = 'roles.delete';

    case ViewPermissions = 'permissions.view';
    case CreatePermissions = 'permissions.create';
    case UpdatePermissions = 'permissions.update';
    case DeletePermissions = 'permissions.delete';

    /** Edit the public website: texts, features, pricing and FAQ. */
    case ManageContent = 'content.manage';

    /** Read the security audit trail. */
    case ViewActivity = 'activity.view';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function isSystem(string $name): bool
    {
        return self::tryFrom($name) !== null;
    }
}
