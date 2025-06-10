<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Alignment;

class CreateEvent extends CreateRecord
{
    protected static string $resource = EventResource::class;

    protected static ?string $title = "Create Event Category";
    protected static bool $canCreateAnother = false;

    public static string|Alignment $formActionsAlignment = Alignment::Between;

    public function getBreadcrumbs(): array
    {
        return ['Event Categories', parent::getBreadcrumb()];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->icon('heroicon-s-check-circle')
            ->title('Successfully')
            ->body('Event Category placed successfully');
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCancelFormAction(),
            $this->getSubmitFormAction(),
        ];
    }
}
