<?php

namespace App\Filament\Resources\AdminsResource\Pages;

use App\Filament\Resources\AdminsResource;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateAdmins extends CreateRecord
{
    protected static string $resource = AdminsResource::class;

    protected static ?string $title = 'Admin';

    public function getBreadcrumbs(): array
    {
        return [self::$title, parent::getBreadcrumb()];
    }

    protected function handleRecordCreation(array $data): User
    {
        $user = static::getModel()::create($data);
        $user->assignRole('admin');
        return $user;
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
            ->body('Admin placed successfully');
    }
}
