<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Package;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

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
        $user = User::role(Role::ROLES['client'])->inRandomOrder()->first() ?? User::factory()->client()->create();

        return [
            'order_number' => Order::generateOrderNumber(),
            'status' => 'inactive',
            'price' => $package->price,
            'user_id' => $user->id,
            'package_id' => $package->id,
            'created_at' => fake()->dateTimeBetween('-1 month'),
        ];
    }
}
