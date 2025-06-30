<?php

namespace Database\Seeders;

use App\Models\GuestGroup;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class GuestGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = User::role(Role::ROLES['client'])
            ->oldest()
            ->limit(10) // Total customers seeded in ClientUserSeeder
            ->get();

        if ($customers->isEmpty()) {
            $this->call(ClientUserSeeder::class);

            $customers = User::role(Role::ROLES['client'])
                ->oldest()
                ->limit(10)
                ->get();
        }

    foreach ($customers as $customer) {
            GuestGroup::factory(2)
                ->create([
                    'customer_id' => $customer->id,
                ]);
        }
    }
}
