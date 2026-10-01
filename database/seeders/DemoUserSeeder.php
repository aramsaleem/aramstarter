<?php

namespace Database\Seeders;

use App\Enums\SystemRole;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Local development accounts - only seeded when APP_ENV=local.
 * Every demo account uses the password "password".
 * In production create your first admin with: php artisan app:create-super-admin
 */
class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['name' => 'Super Admin', 'email' => 'superadmin@example.com', 'role' => SystemRole::SuperAdmin],
            ['name' => 'Admin', 'email' => 'admin@example.com', 'role' => SystemRole::Admin],
            ['name' => 'Test User', 'email' => 'user@example.com', 'role' => SystemRole::User],
        ];

        foreach ($accounts as $account) {
            $user = User::where('email', $account['email'])->first()
                ?? User::factory()->create(['name' => $account['name'], 'email' => $account['email']]);

            $user->syncRoles([$account['role']->role()]);
        }

        if (User::count() < 25) {
            User::factory(20)->create()->each(fn (User $user) => $user->assignRole(SystemRole::User->role()));
            User::factory(4)->unverified()->create()->each(fn (User $user) => $user->assignRole(SystemRole::User->role()));
        }
    }
}
