<?php

namespace Database\Seeders;

use App\Models\Guest;
use App\Models\Invitation;
use App\Models\InvitationGuest;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HistoryOrdersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create a client user
        $client = User::role('client')->inRandomOrder()->first() ?? User::factory()->create()->assignRole('client');

        // Create 5 orders with descending creation dates
        $orders = collect();
        for ($i = 4; $i >= 0; $i--) {
            $orders->push(Order::factory()->create([
                'user_id' => $client->id,
                'created_at' => now()->subMonths($i + 1), // Older first
                'status' => 'inactive',
            ]));
        }

        // Set the latest (newest) order as active
        $latestOrder = $orders->sortByDesc('created_at')->first();
        $latestOrder->update(['status' => 'active']);

        $invitations = collect();

        // Create invitations for each order
        foreach ($orders as $order) {
            $invitation = Invitation::factory()->create([
                'order_id' => $order->id,
                'published_at' => now()->subDays(rand(1, 30)), // All are published
                'date_end' => now()->addDays(rand(5, 30)),
            ]);

            $invitations->push($invitation);
        }

        // Create 30–50 guests
        $guests = Guest::factory()
            ->count(rand(30, 50))
            ->create(['user_id' => $client->id]);

        // Attach guests and mark some as attended
        foreach ($invitations as $invitation) {
            $randomGuests = $guests->random(rand(10, 20));

            foreach ($randomGuests as $guest) {
                InvitationGuest::factory()->create([
                    'guest_id' => $guest->id,
                    'invitation_id' => $invitation->id,
                    'attended_at' => rand(0, 1) ? now()->subDays(rand(1, 15)) : null, // 50% chance attended
                ]);
            }
        }
    }
}
