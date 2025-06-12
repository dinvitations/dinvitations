<?php

namespace App\Filament\Resources\CustomersResource\Pages;

use App\Filament\Resources\CustomersResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Alignment;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class CreateCustomers extends CreateRecord
{
    protected static string $resource = CustomersResource::class;

    protected static bool $canCreateAnother = false;

    protected bool $creatingClientRole = false;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->creatingClientRole = true;

        return $data;
    }

    protected function afterCreate(): void
    {
        if ($this->creatingClientRole && $this->record) {
            $this->record->assignRole('client');
        }

        if ($this->record instanceof MustVerifyEmail && !$this->record->hasVerifiedEmail()) {
            $this->record->sendEmailVerificationNotification();
        }
    }
    
    public function getBreadcrumbs(): array
    {
        return [
            $this->getResource()::getUrl('index') => $this->getResource()::$breadcrumb,
            null => static::$breadcrumb ?? $this->getBreadcrumb(),
        ];
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->icon('heroicon-s-check-circle')
            ->title('Sucessfully')
            ->body('Customer placed successfully');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCancelFormAction(),
            $this->getSubmitFormAction(),
        ];
    }

    public static string | Alignment $formActionsAlignment = Alignment::Between;
}
