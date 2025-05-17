<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Template;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Customers', User::role('client')->count()),
            Stat::make('Total Templates', Template::count()),
            Stat::make('Total Orders', Order::count()),
        ];
    }
}
