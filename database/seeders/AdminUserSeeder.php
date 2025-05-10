<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create or update the admin user
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('p4s5w0rd'),
                'has_access' => true,
            ]
        );

        // Create 'admin' role if it doesn't exist
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        // Create or fetch all permissions and assign them to the role
        $allPermissions = Permission::all();

        if ($allPermissions->isEmpty()) {
            // If no permissions exist, you can optionally define them here
            // or let your app auto-discover them via policies/gates.
            // Example:
            // Permission::create(['name' => 'manage users']);
        }

        $adminRole->syncPermissions(Permission::all());

        // Assign the role to the admin user
        $admin->assignRole($adminRole);
    }
}
