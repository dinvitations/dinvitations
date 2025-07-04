<?php

namespace App\Filament\Resources\AdminsResource\Pages;

use App\Filament\Resources\AdminsResource;
use App\Models\User;
use DB;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Alignment;

class CreateAdmins extends CreateRecord
{
    protected static string $resource = AdminsResource::class;

    protected static ?string $title = 'Create Admin';

    protected static bool $canCreateAnother = false;

    public static string|Alignment $formActionsAlignment = Alignment::Between;

    public function getBreadcrumbs(): array
    {
        return [
            $this->getResource()::getUrl('index') => $this->getResource()::$breadcrumb,
            null => static::$breadcrumb ?? $this->getBreadcrumb(),
        ];
    }

    protected function handleRecordCreation(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = static::getModel()::create($data);
            $user->assignRole($data['role']);
            $user->markEmailAsVerified();
            return $user;
        });
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
            ->body('Admin placed successfully');
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCancelFormAction(),
            $this->getSubmitFormAction(),
        ];
    }
}
