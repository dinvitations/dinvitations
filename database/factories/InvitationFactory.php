<?php

namespace Database\Factories;

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
        $start = now()->addDays(30);
        $end = (clone $start)->addHours(fake()->numberBetween(4, 72));

        return [
            'order_id' => Order::factory(),
            'template_id' => Template::factory(),
            'name' => Str::title(fake()->words(3, true)),
            'slug' => Str::slug(fake()->unique()->words(2, true)),
            'date_start' => $start,
            'date_end' => $end,
            'whatsapp_message' => fake()->sentence(),
            'location' => fake()->address(),
            'location_latlong' => fake()->latitude() . ',' . fake()->longitude(),
            'published_at' => now(),
        ];
    }

    public function empty(): Factory
    {
        return $this->state([
            'template_id' => null,
            'name' => null,
            'slug' => null,
            'date_start' => null,
            'date_end' => null,
            'whatsapp_message' => null,
            'location' => null,
            'location_latlong' => null,
            'published_at' => null,
        ]);
    }
}
