<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Alignment;

class EditEvent extends EditRecord
{
    protected static string $resource = EventResource::class;
    protected static ?string $title = "Edit Event Category";
    public static string|Alignment $formActionsAlignment = Alignment::Between;

    public function getBreadcrumbs(): array
    {
        return ['Event Categories', parent::getBreadcrumb()];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->icon('heroicon-s-check-circle')
            ->title('Successfully')
            ->body('Event Category updated successfully');
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCancelFormAction(),
            $this->getSubmitFormAction()
                ->label('Update'),
        ];
    }
}
