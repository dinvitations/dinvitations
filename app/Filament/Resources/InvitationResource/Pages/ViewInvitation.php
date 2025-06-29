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

    protected static ?string $title = "Event Details";

    public function getBreadcrumbs(): array
    {
        return [];
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
                                TextEntry::make('event_date')
                                    ->label('Event Date')
                                    ->getStateUsing(function (Invitation $record) {
                                        if (!$record->date_start) {
                                            return null;
                                        }

                                        $start = $record->date_start->format('M j');
                                        $end = $record->date_end?->format('M j, Y');

                                        if (!$record->date_end || $record->date_start->isSameDay($record->date_end)) {
                                            return $record->date_start->format('M j, Y');
                                        }

                                        if ($record->date_start->format('M') === $record->date_end->format('M')) {
                                            return $start . ' to ' . $record->date_end->format('j, Y');
                                        }

                                        return $record->date_start->format('M j') . ' to ' . $end;
                                    }),
                                TextEntry::make('organizer_name')
                                    ->label("Organizer’s Name"),
                                TextEntry::make('event_time')
                                    ->label('Time Date')
                                    ->getStateUsing(function (Invitation $record) {
                                        if (!$record->date_start) {
                                            return null;
                                        }

                                        $startTime = $record->date_start->format('h:i A');

                                        if (!$record->date_end || $record->date_start->format('H:i') === $record->date_end->format('H:i')) {
                                            return $startTime;
                                        }

                                        $endTime = $record->date_end->format('h:i A');
                                        return $startTime . ' to ' . $endTime;
                                    }),
                                TextEntry::make('phone_number')
                                    ->label('Whatsapp Number'),
                                TextEntry::make('slug')
                                    ->label('Slug')
                                    ->suffixAction(
                                        Action::make('preview')
                                            ->icon('heroicon-m-arrow-top-right-on-square')
                                            ->url(fn ($record) => url('/invitation/' . $record->slug))
                                            ->openUrlInNewTab()
                                    )
                                    ->visible(fn (Invitation $record) => $record->published_at !== null),
                                TextEntry::make('location')
                                    ->label('Address'),
                                TextEntry::make('published_at')
                                    ->label('Published at')
                                    ->dateTime('M d, Y')
                                    ->badge()
                                    ->color('success')
                                    ->visible(fn (Invitation $record) => $record->published_at !== null),
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
