<?php

namespace App\Filament\Resources\InvitationResource\Pages;

use App\Filament\Resources\InvitationResource;
use App\Models\Invitation;
use Filament\Infolists\Components\Actions;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewInvitation extends ViewRecord
{
    protected static string $resource = InvitationResource::class;

    protected static ?string $title = "Invitation Details";

    public function getBreadcrumbs(): array
    {
        return [];
    }

    public function mount($record): void
    {
        parent::mount($record);

        if ($this->record->published_at === null) {
            $this->redirect(InvitationResource::getUrl('edit', ['record' => $this->record]));
        }
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('event_name')
                                    ->label('Event Name'),
                                TextEntry::make('date_start')
                                    ->label('Event Date')
                                    ->dateTime('M d, Y'),
                                TextEntry::make('organizer_name')
                                    ->label("Organizer’s Name"),
                                TextEntry::make('date_start_time')
                                    ->label('Time Date')
                                    ->state(fn(Invitation $record) => $record->date_start?->format('h:i A')),
                                TextEntry::make('phone_number')
                                    ->label('Whatsapp Number'),
                                TextEntry::make('slug')
                                    ->label('Slug')
                                    ->suffixAction(
                                        Action::make('preview')
                                            ->icon('heroicon-m-arrow-top-right-on-square')
                                            ->url(fn ($record) => url('/invitation/' . $record->slug))
                                            ->openUrlInNewTab()
                                    ),
                                TextEntry::make('location')
                                    ->label('Address'),
                                TextEntry::make('published_at')
                                    ->label('Published at')
                                    ->dateTime('M d, Y')
                                    ->badge()
                                    ->color(fn ($state) => $state ? 'success' : 'secondary'),
                        ]),
                    ]),

                Section::make('Message Content')->schema([
                    TextEntry::make('message')
                        ->label('')
                        ->html()
                        ->columnSpanFull(),
                ])->collapsible(),

                Actions::make([
                    Actions\Action::make('edit')
                        ->label('Edit')
                        ->url(InvitationResource::getUrl('edit', ['record' => $this->record])),
                ])
                ->columnSpanFull()
                ->alignEnd(),
        ]);
    }
}
