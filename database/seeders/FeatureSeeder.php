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
        foreach (Feature::FEATURES as $feature) {
            Feature::firstOrCreate([
                'name' => $feature,
            ]);
        }
    }
}
