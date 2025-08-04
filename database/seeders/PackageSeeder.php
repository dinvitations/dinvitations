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
            ->get();

        $packages = [
            [
                'name' => Package::NAMES['basic'],
                'price' => 1999000,
                'features' => $features
                    ->whereIn('name', [
                        Feature::FEATURES['scan'],
                    ])
                    ->pluck('id')
                    ->values()
                    ->toArray()
            ],
            [
                'name' => Package::NAMES['medium'],
                'price' => 3999000,
                'features' => $features
                    ->whereIn('name', [
                        Feature::FEATURES['scan'],
                        Feature::FEATURES['selfie'],
                    ])
                    ->pluck('id')
                    ->values()
                    ->toArray()
            ],
            [
                'name' => Package::NAMES['premium'],
                'price' => 5999000,
                'features' => $features
                    ->whereIn('name', [
                        Feature::FEATURES['scan'],
                        Feature::FEATURES['selfie'],
                        Feature::FEATURES['display'],
                    ])
                    ->pluck('id')
                    ->values()
                    ->toArray()
            ],
            [
                'name' => Package::NAMES['luxury'],
                'price' => 7999000,
                'features' => $features->map->id->toArray(),
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
