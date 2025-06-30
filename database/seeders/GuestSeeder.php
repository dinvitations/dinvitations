<?php

namespace Database\Seeders;

use App\Models\Guest;
use App\Models\GuestGroup;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GuestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Assuming
        $guestGroups = GuestGroup::query()
            ->limit(20) // Total guest group seeded in GuestGroupSeeder
            ->oldest()
            ->get();
        if ($guestGroups->isEmpty()) {
            $this->call(GuestGroupSeeder::class);
            $guestGroups = GuestGroup::limit(20)->oldest()->get();
        }

        foreach ($guestGroups as $guestGroup) {
            Guest::factory(10)
                ->create([
                    'guest_group_id' => $guestGroup->id,
                ]);
        }
    }
}
