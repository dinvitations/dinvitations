<?php

namespace App\Filament\Resources\SelfieStationResource\Pages;

use App\Filament\Resources\SelfieStationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSelfieStations extends ListRecords
{
    protected static string $resource = SelfieStationResource::class;

    public function getBreadcrumbs(): array
    {
        return [];
    }
}
