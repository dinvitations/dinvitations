<?php

namespace App\Filament\Resources\InvitationResource\Pages;

use App\Filament\Resources\InvitationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInvitation extends CreateRecord
{
    protected static string $resource = InvitationResource::class;

    protected static ?string $title = "Invitation Details";

    public function getBreadcrumbs(): array
    {
        return [];
    }
    
    public function mount(): void
    {
        parent::mount();

        $invitation = InvitationResource::getEloquentQuery()->first();

        if ($invitation && $invitation->published_at === null) {
            $this->redirect(InvitationResource::getUrl('edit', ['record' => $invitation]));
        }

        if ($invitation) {
            $this->redirect(InvitationResource::getUrl('index'));
        }
    }

    protected function getFormActions(): array
    {
        return [];
    }
}
