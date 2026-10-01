<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Model events stay enabled here: laravel-permission relies on them to refresh its cache.
     */
    public function run(): void
    {
        $this->call([RolesAndPermissionsSeeder::class, ContentSeeder::class]);

        if (app()->isLocal()) {
            $this->call(DemoUserSeeder::class);
        }
    }
}
