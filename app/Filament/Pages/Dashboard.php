<?php

namespace App\Filament\Pages;

use App\Filament\Actions\ManualVerifyAction;
use App\Filament\Resources\OrdersResource;
use App\Filament\Widgets\LastAttendance;
use App\Filament\Widgets\LatestOrders;
use App\Filament\Widgets\StatsOverview;
use App\Models\Order;
use Filament\Actions\CreateAction;
use Filament\Actions\ViewAction;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static string $routePath = 'dashboard';

    protected function getHeaderActions(): array
    {
        $user = auth()->user();

        if ($user->isClient()) {
            $actions = [
                ManualVerifyAction::make('manual_verify'),

                ViewAction::make('scan_qrcode')
                    ->label('Scan QRCode')
                    ->color('primary')
                    ->modalWidth('md')
                    ->modalHeading('Scan QRCode')
                    ->modalContent(view('livewire.scan-qr-code'))
            ];
        } else {
            $actions = [
                CreateAction::make('new_order')
                    ->label('New Order')
                    ->url(OrdersResource::getUrl('create'))
                    ->visible(fn() => $user->can('create', Order::class))
            ];
        }

        return $actions;
    }

    public function getWidgets(): array
    {
        $user = auth()->user();

        if ($user->isClient()) {
            $widgets = [
                StatsOverview::class,
                LastAttendance::class,
            ];
        } else {
            $widgets = [
                StatsOverview::class,
                LatestOrders::class,
            ];
        }

        return $widgets;
    }
}
