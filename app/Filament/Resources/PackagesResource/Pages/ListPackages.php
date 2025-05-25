<?php

namespace App\Filament\Resources\PackagesResource\Pages;

use App\Filament\Resources\PackagesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPackages extends ListRecords
{
    protected static string $resource = PackagesResource::class;

    protected static ?string $title = 'Packages';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('New Package'),
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }
}
