<?php

namespace Database\Seeders;

use App\Enums\SystemPermission;
use App\Enums\SystemRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Creates the permissions and roles the application relies on.
 * Safe to run repeatedly: it only adds what is missing and never removes
 * permissions granted through the admin panel.
 */
class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $registrar = app(PermissionRegistrar::class);
        $registrar->forgetCachedPermissions();

        foreach (SystemPermission::cases() as $permission) {
            Permission::findOrCreate($permission->value, 'web');
        }

        $registrar->forgetCachedPermissions();

        foreach (SystemRole::cases() as $systemRole) {
            $role = Role::findOrCreate($systemRole->value, 'web');

            $role->givePermissionTo(array_map(
                fn (SystemPermission $permission) => $permission->value,
                $systemRole->defaultPermissions(),
            ));
        }
    }
}
