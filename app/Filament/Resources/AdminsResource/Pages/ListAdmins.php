<?php

namespace App\Filament\Resources\AdminsResource\Pages;

use App\Filament\Resources\AdminsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAdmins extends ListRecords
{
    protected static string $resource = AdminsResource::class;

    protected static ?string $title = 'Admins';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('New Admin'),
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }
}
