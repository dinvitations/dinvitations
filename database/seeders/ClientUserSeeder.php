<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ClientUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create or update the client user
        $client = User::updateOrCreate(
            ['email' => 'restuedosetiaji@gmail.com'],
            [
                'name' => 'Restu Edo Setiaji',
                'password' => Hash::make('Edo998877!'),
                'email_verified_at' => now(),
            ]
        );

        // Create 'client' role if it doesn't exist
        $clientRole = Role::firstOrCreate(['name' => 'client']);

        // Assign the role to the client user
        $client->assignRole($clientRole);
    }
}
