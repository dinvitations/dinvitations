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
        // Create or get a client user
        $client = User::role('client')->inRandomOrder()->first() ?? User::factory()->client()->create();

        // Create 5 historical orders for the client
        $orders = Order::factory()
            ->count(5)
            ->create([
                'user_id' => $client->id,
                'created_at' => now()->subMonths(rand(1, 12)),
            ]);

        $invitations = collect();

        // Create one invitation per order
        foreach ($orders as $order) {
            $invitation = Invitation::factory()->create([
                'order_id' => $order->id,
                'date_end' => now()->addDays(rand(5, 30)),
            ]);

            $invitations->push($invitation);
        }

        // Create 30–50 guests
        $guests = Guest::factory()
            ->count(rand(30, 50))
            ->create([
                'user_id' => $client->id,
            ]);

        // Attach 10–20 guests to each invitation
        foreach ($invitations as $invitation) {
            $randomGuests = $guests->random(rand(10, 20));
            foreach ($randomGuests as $guest) {
                InvitationGuest::factory()->create([
                    'guest_id' => $guest->id,
                    'invitation_id' => $invitation->id,
                ]);
            }
        }
    }
}
