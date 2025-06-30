<?php

namespace Database\Seeders;

use App\Models\Guest;
use App\Models\GuestGroup;
use App\Models\Invitation;
use App\Models\InvitationGuest;
use App\Models\Order;
use App\Models\Role;
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
        $client = User::role(Role::ROLES['client'])->inRandomOrder()->first() ?? User::factory()->create()->assignRole(Role::ROLES['client']);

        // Create 5 orders with descending creation dates
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
            if ($order->id === $latestOrder->id) {
                $invitation = Invitation::factory()->create([
                    'order_id' => $order->id,
                    'published_at' => now()->subDays(30),
                ]);
            } else {
                $dateStart = now()->subDays(rand(10, 30));
                $dateEnd = (clone $dateStart)->addHours(rand(4, 72));

                $invitation = Invitation::factory()->create([
                    'order_id' => $order->id,
                    'date_start' => $dateStart,
                    'date_end' => $dateEnd,
                ]);
            }

            $invitations->push($invitation);
        }


        // Create 5-10 guest groups for the client
        $guestGroups = GuestGroup::factory(rand(5, 10))
            ->create(['customer_id' => $client->id]);

        // Create guests for each group
        $guests = collect();
        foreach ($guestGroups as $guestGroup) {
            $groupGuests = Guest::factory(rand(10, 20))
                ->create([
                    'guest_group_id' => $guestGroup->id,
                ]);
            $guests = $guests->merge($groupGuests);
        }

        // Attach 50-100 guests to each invitation
        foreach ($invitations as $invitation) {
            $randomGuests = $guests->random(rand(50, 100));
            foreach ($randomGuests as $guest) {
                InvitationGuest::factory()
                    ->forInvitationWithTimestamps($invitation)
                    ->create([
                        'guest_id' => $guest->id
                    ]);
            }
        }
    }
}
