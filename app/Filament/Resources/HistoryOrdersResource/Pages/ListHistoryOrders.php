<?php

namespace App\Filament\Resources\HistoryOrdersResource\Pages;

use App\Filament\Resources\HistoryOrdersResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHistoryOrders extends ListRecords
{
    protected static string $resource = HistoryOrdersResource::class;

    public function getBreadcrumbs(): array
    {
        return [];
    }
}
