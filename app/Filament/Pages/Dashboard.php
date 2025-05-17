<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static string $view = 'filament.pages.dashboard';

    protected static string $routePath = 'dashboard';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?string $title = 'Dashboard';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('new_order')
                ->label('New Order')
                // ->url(route('filament.resources.orders.create'))
                ->button()
                ->color('primary'),
        ];
    }
}
