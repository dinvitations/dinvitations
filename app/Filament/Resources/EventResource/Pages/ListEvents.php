<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEvents extends ListRecords
{
    protected static string $resource = EventResource::class;

    protected static ?string $title = 'Event Categories';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('New Category'),
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }
}
