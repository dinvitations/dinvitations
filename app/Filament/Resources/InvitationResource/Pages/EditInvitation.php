<?php

namespace App\Filament\Resources\InvitationResource\Pages;

use App\Filament\Resources\InvitationResource;
use Filament\Resources\Pages\EditRecord;

class EditInvitation extends EditRecord
{
    protected static string $resource = InvitationResource::class;

    protected static ?string $title = "Invitation Details";

    public function getBreadcrumbs(): array
    {
        return [];
    }
    
    public function mount($record): void
    {
        parent::mount($record);
    }

    protected function getFormActions(): array
    {
        return [];
    }

}
