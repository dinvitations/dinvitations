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
        return [
            'order_id' => Order::factory(),
            'template_id' => Template::factory(),
            'name' => fake()->words(3, true),
            'slug' => Str::slug(fake()->unique()->words(2, true)),
            'date_start' => now(),
            'date_end' => now()->addDays(30),
            'whatsapp_message' => fake()->sentence(),
            'location' => fake()->address(),
            'location_latlong' => fake()->latitude() . ',' . fake()->longitude(),
            'published_at' => now(),
        ];
    }
}
