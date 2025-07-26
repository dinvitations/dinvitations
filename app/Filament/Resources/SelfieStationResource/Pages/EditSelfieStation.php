<?php

namespace App\Filament\Resources\SelfieStationResource\Pages;

use App\Filament\Resources\SelfieStationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSelfieStation extends EditRecord
{
    protected static string $resource = SelfieStationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
