<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\ClientUserSeeder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Guest>
 */
class GuestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone_number' => fake()->e164PhoneNumber(),
            'type_default' => fake()->randomElement(['reg', 'vip', 'vvip']),
        ];
    }
}
