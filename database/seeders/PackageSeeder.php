<?php

namespace Database\Seeders;

use App\Models\Feature;
use App\Models\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $features = Feature::query()
            ->whereIn('name', Feature::FEATURES)
            ->get()
            ->pluck('id')
            ->toArray();

        $packages = [
            [
                'name' => 'Basic',
                'price' => 1999000,
                'features' => [
                    $features[0],
                ],
            ],
            [
                'name' => 'Medium',
                'price' => 3999000,
                'features' => [
                    $features[0], $features[1],
                ],
            ],
            [
                'name' => 'Premium',
                'price' => 5999000,
                'features' => [
                    $features[0], $features[2]
                ],
            ],
            [
                'name' => 'Luxury',
                'price' => 7999000,
                'features' => $features,
            ],
        ];

        foreach ($packages as $package) {
            Package::firstOrCreate([
                'name' => $package['name'],
            ], [
                'price' => $package['price'],
            ])->features()->sync($package['features']);
        }
    }
}
