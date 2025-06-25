<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Package;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $package = Package::firstOrCreate([
            'name' => Package::NAMES['basic'],
        ], [
            'price' => 1999000,
        ]);

        $events = [
            [
                'name' => Event::NAMES['wedding'],
                'description' => 'A beautiful wedding event.',
            ],
            [
                'name' => Event::NAMES['open_house'],
                'description' => 'An open house event for showcasing properties.',
            ],
            [
                'name' => Event::NAMES['seminar'],
                'description' => 'A seminar event for knowledge sharing.',
            ],
        ];

        foreach ($events as $event) {
            Event::firstOrCreate([
                'name' => $event['name'],
            ], [
                'package_id' => $package->id,
                'description' => $event['description'],
            ]);
        }
    }
}
