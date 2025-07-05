<?php

namespace Database\Factories;

use App\Models\Invitation;
use App\Models\Order;
use App\Models\Template;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invitation>
 */
class InvitationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $dateStart = now();
        $dateEnd = $dateStart->copy()->addHours(rand(4, 72));
        $firstName = fake()->unique()->firstName();
        $secondName = fake()->unique()->firstName();
        $souvenirStock = fake()->numberBetween(0, 1000);

        return [
            'order_id' => Order::factory(),
            'template_id' => Template::factory(),
            'event_name' => Str::title(fake()->words(3, true)),
            'organizer_name' => "{$firstName} & {$secondName}",
            'slug' => Str::slug(fake()->unique()->words(2, true)),
            'date_start' => $dateStart,
            'date_end' => $dateEnd,
            'phone_number' => fake()->e164PhoneNumber(),
            'souvenir_stock' => $souvenirStock,
            'total_seats' => fake()->numberBetween($souvenirStock + 1, $souvenirStock + 500),
            'message' => Invitation::MESSAGE,
            'location' => fake()->address(),
            'location_latlng' => fake()->latitude() . ',' . fake()->longitude(),
            'published_at' => now()->subDays(30),
        ];
    }

    public function empty(): Factory
    {
        return $this->state([
            'template_id' => null,
            'event_name' => null,
            'organizer_name' => null,
            'slug' => null,
            'date_start' => null,
            'date_end' => null,
            'phone_number' => null,
            'souvenir_stock' => null,
            'total_seats' => null,
            'message' => null,
            'location' => null,
            'location_latlng' => null,
            'published_at' => null,
        ]);
    }
}
