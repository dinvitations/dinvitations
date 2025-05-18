<?php

namespace App\Filament\Resources\AdminsResource\Pages;

use App\Filament\Resources\AdminsResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;


class EditAdmins extends EditRecord
{
    protected static string $resource = AdminsResource::class;

    protected static ?string $title = 'Admin';

    public function getBreadcrumbs(): array
    {
        return [self::$title, parent::getBreadcrumb()];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Successfully')
            ->body('Admin updated successfully');
    }
}
