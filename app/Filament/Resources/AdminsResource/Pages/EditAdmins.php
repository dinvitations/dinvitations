<?php

namespace App\Filament\Resources\AdminsResource\Pages;

use App\Filament\Resources\AdminsResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;


class EditAdmins extends EditRecord
{
    protected static string $resource = AdminsResource::class;

    protected static ?string $title = 'Edit Admin';

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
}
