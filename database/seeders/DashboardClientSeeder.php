<?php

namespace Database\Seeders;

use App\Models\Guest;
use App\Models\Invitation;
use App\Models\InvitationGuest;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DashboardClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Use an existing client or create a new one with the 'client' role
        $client = User::role('client')->inRandomOrder()->first() ?? User::factory()->client()->create();

        // Create a random number of orders (e.g. 3–5), all inactive by default
        $orders = Order::factory()
            ->count(rand(3, 5))
            ->create([
                'user_id' => $client->id,
                'status' => 'inactive',
            ]);

        // Get the latest order by created_at and set it as active
        $latestOrder = $orders->sortByDesc('created_at')->first();
        $latestOrder->update(['status' => 'active']);

        $invitations = collect();

        foreach ($orders as $order) {
            $invitation = Invitation::factory()->create([
                'order_id' => $order->id,
                'published_at' => now(),
            ]);

            $invitations->push($invitation);
        }

        // Create 20–30 guests for the client
        $guests = Guest::factory()
            ->count(rand(20, 30))
            ->create(['user_id' => $client->id]);

        // Attach 10–15 guests to each invitation
        foreach ($invitations as $invitation) {
            $randomGuests = $guests->random(rand(10, 15));
            foreach ($randomGuests as $guest) {
                InvitationGuest::factory()->create([
                    'guest_id' => $guest->id,
                    'invitation_id' => $invitation->id,
                ]);
            }
        }
    }
}
