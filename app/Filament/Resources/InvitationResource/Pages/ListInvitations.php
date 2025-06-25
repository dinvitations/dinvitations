<?php

namespace App\Filament\Resources\InvitationResource\Pages;

use App\Filament\Resources\InvitationResource;
use Filament\Resources\Pages\ListRecords;

class ListInvitations extends ListRecords
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

        if (!$invitation) {
            $this->redirect(InvitationResource::getUrl('create'));
            return;
        }

        if ($invitation->published_at === null) {
            $this->redirect(InvitationResource::getUrl('edit', ['record' => $invitation]));
            return;
        }

        $this->redirect(InvitationResource::getUrl('view', ['record' => $invitation]));
    }
}
