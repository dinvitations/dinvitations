<?php

namespace App\Filament\Resources\AdminsResource\Pages;

use App\Filament\Resources\AdminsResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Alignment;


class EditAdmins extends EditRecord
{
    protected static string $resource = AdminsResource::class;

    protected static ?string $title = 'Edit Admin';

    public static string|Alignment $formActionsAlignment = Alignment::Between;

    public function getBreadcrumbs(): array
    {
        return ['Admin', parent::getBreadcrumb()];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->icon('heroicon-s-check-circle')
            ->title('Successfully')
            ->body('Admin updated successfully');
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
