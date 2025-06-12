<?php

namespace Database\Factories;

use App\Models\Guest;
use App\Models\Invitation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InvitationGuest>
 */
class InvitationGuestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $guest = Guest::inRandomOrder()->first() ?? Guest::factory()->create();
        $invitation = Invitation::inRandomOrder()->first() ?? Invitation::factory()->create();

        return [
            'guest_id' => $guest->id,
            'invitation_id' => $invitation->id,
            'type' => fake()->randomElement(['reg', 'vip', 'vvip']),
            'rsvp' => fake()->boolean(80),
            'attended_at' => fake()->optional(0.7)->dateTimeBetween('-1 week', 'now'),
            'souvenir_at' => fake()->optional(0.5)->dateTimeBetween('-1 week', 'now'),
            'selfie_at' => fake()->optional(0.4)->dateTimeBetween('-1 week', 'now'),
        ];
    }
}
