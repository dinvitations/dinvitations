<?php

namespace App\Filament\Resources;

use App\Exports\GuestBookExport;
use App\Filament\Resources\HistoryOrdersResource\Pages;
use App\Models\InvitationGuest;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;

class HistoryOrdersResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $modelLabel = 'History Orders';
    protected static ?string $pluralModelLabel = 'History Orders';

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationGroup = 'Manage';
    protected static ?int $navigationSort = 3;

    public static function canAccess(): bool
    {
        return auth()->user()->isClient();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading('No history order yet')
            ->emptyStateDescription('You dont have any history order yet.')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Order Date')
                    ->dateTime('M d, Y')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('invitation.date_start')
                    ->label('Event Date')
                    ->dateTime('M d, Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('invitation.name')
                    ->label('Event Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('package.name')
                    ->label('Packages')
                    ->formatStateUsing(fn($state) => ucfirst($state))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Price')
                    ->summarize([
                        Sum::make()
                            ->label('Total Price')
                            ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ])
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('guest_book')
                    ->label('Guest Book')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->default('Download')
                    ->action(
                        ViewAction::make('download')
                            ->action(function ($record) {
                                $invitation = $record->invitation;

                                if (!$invitation) {
                                    abort(404, 'Invitation not found.');
                                }

                                $guests = InvitationGuest::with('guest')
                                    ->where('invitation_id', $invitation->id)
                                    ->get()
                                    ->map(function ($entry) {
                                        $attendedAt = $entry->attended_at;

                                        return [
                                            'name'            => $entry->guest->name ?? '',
                                            'type'            => match ($entry->type) {
                                                'reg'  => 'General',
                                                'vip'  => 'VIP',
                                                'vvip' => 'VVIP',
                                                default => strtoupper($entry->type ?? '')
                                            },
                                            'rsvp'            => $entry->rsvp ?? '',
                                            'attended_at'     => $attendedAt ? $attendedAt->format('h.i A') : '-',
                                            'souvenir_at'     => $entry->souvenir_at ? $entry->souvenir_at->format('h.i A') : '-',
                                            'selfie_at'       => $entry->selfie_at ? $entry->selfie_at->format('h.i A') : '-',
                                            'attendance_date' => $attendedAt ? $attendedAt->toDateString() : null,
                                        ];
                                    })
                                    ->sortBy(function ($guest) {
                                        return $guest['attendance_date'] ?? '9999-12-31';
                                    })
                                    ->groupBy('attendance_date');

                                $pdf = Pdf::loadView('pdf.guestbook', [
                                    'invitation' => $invitation,
                                    'guestsByDate' => $guests,
                                    'dateStart' => $invitation->date_start,
                                    'dateEnd' => $invitation->date_end,
                                ])->setPaper('a4', 'landscape');

                                return response()->streamDownload(
                                    fn() => print($pdf->stream()),
                                    'guestbook_' . $record->order_number . '.pdf'
                                );
                            })
                            ->after(function ($record) {
                                Notification::make()
                                    ->success()
                                    ->icon('heroicon-o-check-circle')
                                    ->title('Sucessfully')
                                    ->body('Guest Book - ' . $record->order_number . ' downloaded successfully')
                                    ->sendToDatabase(auth()->user(), isEventDispatched: true);
                            })
                    )
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('invitation')
            ->where('status', 'inactive');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHistoryOrders::route('/'),
        ];
    }
}
