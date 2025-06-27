<?php

namespace App\Filament\Resources\OrdersResource\Pages;

use App\Filament\Resources\OrdersResource;
use App\Models\Invitation;
use App\Models\Order;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Alignment;

class CreateOrders extends CreateRecord
{
    protected static string $resource = OrdersResource::class;
    
    public function getBreadcrumbs(): array
    {
        return [
            $this->getResource()::getUrl('index') => $this->getResource()::$breadcrumb,
            null => static::$breadcrumb ?? $this->getBreadcrumb(),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status'] = 'active';

        return $data;
    }

    protected function beforeCreate(): void
    {
        $hasActiveOrder = Order::where('user_id', $this->data['user_id'])
            ->where('status', 'active')
            ->exists();

        if ($hasActiveOrder) {
            Notification::make()
                ->danger()
                ->icon('heroicon-s-x-circle')
                    ->title('Failed')
                    ->body('Customer already have an active order')
                    ->send();

            $this->halt();
        }
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->icon('heroicon-s-check-circle')
            ->title('Sucessfully')
            ->body('Order placed successfully');
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
