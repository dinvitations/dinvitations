<?php

namespace App\Filament\Resources\InvitationResource\Pages;

use App\Filament\Resources\InvitationResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Alignment;

class EditInvitation extends EditRecord
{
    protected static string $resource = InvitationResource::class;

    protected static ?string $title = "Event Details";

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($data['unlock_souvenir_stock'] && $this->record) {
            $data['souvenir_stock'] = $this->record->souvenir_stock + ($data['souvenir_stock'] - $this->record->availableSouvenirStock());
        }

        return $data;
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->icon('heroicon-s-check-circle')
            ->title('Sucessfully')
            ->body('Event Details updated successfully');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSubmitFormAction()
                ->label('Update'),
        ];
    }

    public static string | Alignment $formActionsAlignment = Alignment::End;
}
