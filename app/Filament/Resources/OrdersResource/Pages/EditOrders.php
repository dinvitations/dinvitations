<?php

namespace App\Filament\Resources\OrdersResource\Pages;

use App\Filament\Resources\OrdersResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Alignment;

class EditOrders extends EditRecord
{
    protected static string $resource = OrdersResource::class;

    public function getBreadcrumbs(): array
    {
        return [
            $this->getResource()::getUrl('index') => $this->getResource()::$breadcrumb,
            null => static::$breadcrumb ?? $this->getBreadcrumb(),
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->icon('heroicon-s-check-circle')
            ->title('Sucessfully')
            ->body('Order updated successfully');
    }

    protected function getRedirectUrl(): string
    {
        return '';
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
