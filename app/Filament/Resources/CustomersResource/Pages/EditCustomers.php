<?php

namespace App\Filament\Resources\CustomersResource\Pages;

use App\Filament\Resources\CustomersResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Alignment;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class EditCustomers extends EditRecord
{
    protected static string $resource = CustomersResource::class;

    public function getBreadcrumbs(): array
    {
        return [
            $this->getResource()::getUrl('index') => $this->getResource()::$breadcrumb,
            null => static::$breadcrumb ?? $this->getBreadcrumb(),
        ];
    }

    protected function beforeSave(): void
    {
        if ($this->record instanceof MustVerifyEmail && $this->record->email !== $this->data['email']) {
            $this->record->email_verified_at = null;
            $this->record->save();
        }
    }

    protected function afterSave(): void
    {
        if ($this->record instanceof MustVerifyEmail && !$this->record->hasVerifiedEmail()) {
            $this->record->sendEmailVerificationNotification();
        }
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->icon('heroicon-s-check-circle')
            ->title('Sucessfully')
            ->body('Customer updated successfully');
    }

    protected function getRedirectUrl(): string
    {
        return '';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\RestoreAction::make(),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCancelFormAction(),
            $this->getSubmitFormAction()
                ->label('Update'),
        ];
    }

    public static string | Alignment $formActionsAlignment = Alignment::Between;
}
