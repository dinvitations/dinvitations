<?php

namespace App\Filament\Resources\PackagesResource\Pages;

use App\Filament\Resources\PackagesResource;
use Filament\Resources\Pages\ListRecords;

class ListPackages extends ListRecords
{
    protected static string $resource = PackagesResource::class;

    protected static ?string $title = 'Packages';

    public function getBreadcrumbs(): array
    {
        return [];
    }
}
