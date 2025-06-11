<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Package;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Template>
 */
class TemplateFactory extends Factory
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

        $event = Event::inRandomOrder()->first() ?? Event::factory()->create();
        $name = fake()->words(3, true);

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'package_id' => $package?->id,
            'event_id' => $event->id,
            'preview_url' => fake()->imageUrl(640, 480, 'invitations')
        ];
    }
}
