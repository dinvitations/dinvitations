<?php

namespace Database\Factories;

use App\Models\Package;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        /* Use this to create a random package if needed */
        // $package = Package::inRandomOrder()->first() ?? Package::factory()->create();

        $package = null;

        return [
            'name' => Str::title(fake()->unique()->word()),
            'description' => fake()->sentence(),
            'package_id' => $package?->id,
        ];
    }
}
