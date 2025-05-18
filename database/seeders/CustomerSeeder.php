<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create the 'client' role if it doesn't exist
        Role::findOrCreate('client');

        // Create 10 users with the 'client' role
        User::factory()->count(10)->create()->each(function (User $user) {
            $user->assignRole('client');
        });
    }
}
