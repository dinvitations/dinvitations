<?php

namespace App\Filament\Resources\SelfieStationResource\Pages;

use App\Filament\Resources\SelfieStationResource;
use App\Models\Feature;
use App\Models\Invitation;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSelfieStations extends ListRecords
{
    protected static string $resource = SelfieStationResource::class;

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
                        $featureQuery->where('name', Feature::FEATURES['selfie']);
                    });
            }, '=', 1)
            ->first();

        return [
            Actions\ViewAction::make()
                ->label('Display Screen')
                ->icon('heroicon-o-tv')
                ->color('primary')
                ->visible($invitation !== null)
                ->url(function () {
                    return route('selfie.display');
                })
                ->openUrlInNewTab(),
        ];
    }
}
