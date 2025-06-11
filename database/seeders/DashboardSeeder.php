<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Package;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DashboardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create the 'client' role if it doesn't exist
        Role::findOrCreate('client');

        // Create sample packages
        $packages = Package::factory()->count(5)->create();

        // Create 5 users with the 'client' role
        $clients = User::factory()->count(5)->create()->each(function ($user) {
            $user->assignRole('client');
        });

        DB::transaction(function () use ($clients, $packages) {
            foreach ($clients as $client) {
                Order::factory()->count(2)->create([
                    'user_id' => $client->id,
                    'package_id' => $packages->random()->id,
                ]);
            }
        });

    }
}
