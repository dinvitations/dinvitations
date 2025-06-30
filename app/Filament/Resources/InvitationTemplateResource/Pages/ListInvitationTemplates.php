<?php

namespace App\Filament\Resources\InvitationTemplateResource\Pages;

use App\Filament\Resources\InvitationTemplateResource;
use App\Models\Event;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListInvitationTemplates extends ListRecords
{
    protected static string $resource = InvitationTemplateResource::class;

    protected static ?string $breadcrumb = 'Choose Template';

    public function getTabs(): array
    {
        if (auth()->user()->organizer->isWO() || empty(Event::count())) {
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
