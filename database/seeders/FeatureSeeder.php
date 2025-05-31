<?php

namespace Database\Seeders;

use App\Models\Feature;
use Illuminate\Database\Seeder;

class FeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $features = [
            'Scan & Redeem Station',
            'Digital Greeting Wall',
            'Digital Selfie Station',
        ];

        foreach ($features as $feature) {
            Feature::firstOrCreate([
                'name' => $feature,
            ]);
        }
    }
}
