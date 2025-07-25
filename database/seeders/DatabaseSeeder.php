<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            AdminUserSeeder::class,
            // ClientUserSeeder::class,
            FeatureSeeder::class,
            EventSeeder::class,
            PackageSeeder::class,

            // GuestGroupSeeder::class,
            // GuestSeeder::class,
        ]);
    }
}
