<?php

namespace Database\Factories;

use App\Models\Package;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $package = Package::inRandomOrder()->first() ?? Package::factory()->create();

        return [
            'order_number' => Str::upper(Str::random(10)),
            'status' => 'inactive',
            'price' => $package->price,
            'user_id' => User::factory(),
            'package_id' => $package->id,
            'created_at' => fake()->dateTimeBetween('-1 month'),
        ];
    }
}
