<?php

namespace App\Filament\Pages;

use App\Filament\Resources\OrdersResource;
use App\Filament\Widgets\LastAttendance;
use App\Filament\Widgets\LatestOrders;
use App\Filament\Widgets\StatsOverview;
use Filament\Actions\Action;
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
                ViewAction::make('scan_qrcode')
                    ->label('Scan QRCode')
                    ->color('primary')
                    ->modalWidth('md')
                    ->modalHeading('Scan QRCode')
                    ->modalContent(view('filament.partials.scan-qr-code'))
            ];
        } else {
            $actions = [
                Action::make('new_order')
                    ->label('New Order')
                    ->url(OrdersResource::getUrl('create')),
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
