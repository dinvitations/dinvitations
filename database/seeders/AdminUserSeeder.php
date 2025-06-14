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
        $admins = [
            [
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'password' => 'p4s5w0rd',
                'email_verified_at' => now(),
                'roles' => Role::ROLES['manager'],
            ],
            [
                'name' => 'Admin EO',
                'email' => 'eo@example.com',
                'password' => 'p4s5w0rd',
                'email_verified_at' => now(),
                'roles' => Role::ROLES['event_organizer'],
            ],
            [
                'name' => 'Admin WO',
                'email' => 'wo@example.com',
                'password' => 'p4s5w0rd',
                'email_verified_at' => now(),
                'roles' => Role::ROLES['wedding_organizer'],
            ],
        ];

        foreach ($admins as $adminData) {
            $admin = User::updateOrCreate(
                ['email' => $adminData['email']],
                [
                    'name' => $adminData['name'],
                    'password' => Hash::make($adminData['password']),
                    'email_verified_at' => $adminData['email_verified_at'],
                ]
            );

            $admin->syncRoles($adminData['roles']);
        }
    }
}
