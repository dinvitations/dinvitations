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
        // Create or get a client user
        $client = User::role('client')->inRandomOrder()->first() ?? User::factory()->client()->create();

        // Create orders for the client
        $orders = Order::factory()
            ->count(3)
            ->create(['user_id' => $client->id]);

        $invitations = collect();
        $guests = collect();

        // For each order, create an invitation
        foreach ($orders as $order) {
            $invitation = Invitation::factory()
                ->create(['order_id' => $order->id]);

            $invitations = $invitations->push($invitation);
        }

        // Create 20–30 guests for the client
        $guests = Guest::factory()
            ->count(rand(20, 30))
            ->create(['user_id' => $client->id]);

        // For each invitation, attach 10–15 random guests
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
