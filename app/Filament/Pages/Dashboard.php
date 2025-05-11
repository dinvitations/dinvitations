<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static string $view = 'filament.pages.dashboard';

    protected static string $routePath = 'dashboard';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?string $title = 'Dashboard';

}
