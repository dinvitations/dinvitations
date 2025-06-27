<?php

namespace App\Filament\Resources\InvitationResource\Pages;

use App\Filament\Resources\InvitationResource;
use App\Models\Order;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Alignment;

class CreateInvitation extends CreateRecord
{
    protected static string $resource = InvitationResource::class;

    protected static ?string $title = "Event Details";

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->icon('heroicon-s-check-circle')
            ->title('Sucessfully')
            ->body('Event Details placed successfully');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSubmitFormAction(),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $order = Order::where('status', 'active')
            ->where('user_id', auth()->user()->id)
            ->first();

        $data['order_id'] = $order?->id;

        return $data;
    }

    public static string | Alignment $formActionsAlignment = Alignment::End;
}
