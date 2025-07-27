<?php

namespace App\Filament\Resources\GreetingWallResource\Pages;

use App\Filament\Resources\GreetingWallResource;
use App\Models\Feature;
use App\Models\Invitation;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGreetingWalls extends ListRecords
{
    protected static string $resource = GreetingWallResource::class;

    public function getBreadcrumbs(): array
    {
        return [];
    }

    protected function getHeaderActions(): array
    {
        $invitation = Invitation::whereNotNull('published_at')
            ->whereHas('order', function ($query) {
                $query->where('status', 'active')
                    ->where('user_id', auth()->user()->id)
                    ->whereHas('package.features', function ($featureQuery) {
                        $featureQuery->where('name', Feature::FEATURES['greeting']);
                    });
            }, '=', 1)
            ->first();

        return [
            Actions\ViewAction::make()
                ->label('Display Screen')
                ->color('primary')
                ->visible($invitation !== null)
                ->url(function () {
                    return route('greeting.display');
                })
                ->openUrlInNewTab(),
        ];
    }
}
