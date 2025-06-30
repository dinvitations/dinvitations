<?php

namespace App\Filament\Resources\TemplatesResource\Pages;

use App\Filament\Resources\TemplatesResource;
use App\Models\Event;
use App\Models\Role;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListTemplates extends ListRecords
{
    protected static string $resource = TemplatesResource::class;

    public function getBreadcrumbs(): array
    {
        return [];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Template'),
        ];
    }

    public function getTabs(): array
    {
        if (auth()->user()->isWO() || empty(Event::count())) {
            return [];
        }

        $tabs = [
            'all' => Tab::make(),
        ];

        foreach (Event::all() as $event) {
            $tabs[strtolower($event->name)] = Tab::make($event->name)
                ->query(fn(Builder $query): Builder => $query->where('event_id', $event->id));
        }

        return $tabs;
    }
}
