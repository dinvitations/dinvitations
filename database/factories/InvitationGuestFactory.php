<?php

namespace Database\Factories;

use App\Models\Guest;
use App\Models\Invitation;
use Carbon\Carbon;
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
            'attended_at' => null,
            'souvenir_at' => null,
            'selfie_at' => null,
        ];
    }

    public function forInvitationWithTimestamps(Invitation $invitation): self
    {
        return $this->state(function () use ($invitation) {
            [$attendedAt, $souvenirAt, $selfieAt, $leftAt] = $this->generateAttendanceTimestamps($invitation);

            return [
                'invitation_id' => $invitation->id,
                'attended_at'   => $attendedAt,
                'souvenir_at'   => $souvenirAt,
                'selfie_at'     => $selfieAt,
                'left_at'       => $leftAt,
                'guest_count'    => fake()->numberBetween(1, 5),
            ];
        });
    }

    private function generateAttendanceTimestamps(Invitation $invitation): array
    {
        $start = Carbon::parse($invitation->date_start);
        $end = Carbon::parse($invitation->date_end);

        $attendedAt = fake()->optional(0.7)->dateTimeBetween($start, $end);

        $souvenirAt = null;
        if ($attendedAt) {
            $souvenirAt = fake()->optional(0.5)->dateTimeBetween(
                Carbon::parse($attendedAt)->copy()->addMinute(),
                $end
            );
        }

        $selfieAt = null;
        if ($souvenirAt) {
            $selfieAt = fake()->optional(0.4)->dateTimeBetween(
                Carbon::parse($souvenirAt)->copy()->addMinute(),
                $end
            );
        }

        $leftBase = $selfieAt ?? $souvenirAt ?? $attendedAt;
        $leftAt = null;
        if ($leftBase) {
            $leftAt = fake()->optional(0.7)->dateTimeBetween(
                Carbon::parse($leftBase)->copy()->addMinute(),
                $end
            );
        }

        return [$attendedAt, $souvenirAt, $selfieAt, $leftAt];
    }
}
