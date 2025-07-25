<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HistoryOrdersResource\Pages;
use App\Models\Feature;
use App\Models\InvitationGuest;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

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

                Tables\Columns\TextColumn::make('invitation.event_name')
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
                        Action::make('download')
                            ->action(function ($record) {
                                $invitation = $record->invitation;

                                if (!$invitation) {
                                    abort(Response::HTTP_NOT_FOUND, 'Invitation not found.');
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
                                            'selfie_photo_url' => $entry->selfie_photo_url ?? null,
                                        ];
                                    });

                                $guestByDate = $guests->sortBy(function ($guest) {
                                        return $guest['attendance_date'] ?? '9999-12-31';
                                    })
                                    ->groupBy('attendance_date');

                                $tempSelfiePaths = [];
                                $selfieBookPath = null;
                                $hasSelfie = $invitation->hasFeature(Feature::FEATURES['selfie']);
                                if ($hasSelfie) {
                                    $guestSelfie = $guests->whereNotNull('selfie_photo_url')
                                        ->map(function ($guest) use (&$tempSelfiePaths) {
                                            $path = $guest['selfie_photo_url'];

                                            if (Storage::disk('minio')->exists($path)) {
                                                $imageContents = Storage::disk('minio')->get($path);
                                                $tempFilename = 'temp_selfie_' . Str::uuid() . '.png';
                                                $tempPath = storage_path('app/public/' . $tempFilename);

                                                file_put_contents($tempPath, $imageContents);
                                                $guest['selfie_photo_url'] = $tempPath;
                                                $tempSelfiePaths[] = $tempPath;
                                            } else {
                                                $guest['selfie_photo_url'] = null;
                                            }

                                            return $guest;
                                        })
                                        ->sortBy('selfie_at');

                                    $selfieBookPath = storage_path('app/public/selfiebook_' . $record->order_number . '.pdf');
                                    Pdf::loadView('pdf.selfiebook', [
                                        'invitation' => $invitation,
                                        'guestSelfie' => $guestSelfie,
                                        'dateStart' => $invitation->date_start,
                                        'dateEnd' => $invitation->date_end,
                                    ])->setPaper('a4', 'landscape')->save($selfieBookPath);
                                }

                                $guestBookPath = storage_path('app/public/guestbook_' . $record->order_number . '.pdf');
                                Pdf::loadView('pdf.guestbook', [
                                    'invitation' => $invitation,
                                    'guestsByDate' => $guestByDate,
                                    'dateStart' => $invitation->date_start,
                                    'dateEnd' => $invitation->date_end,
                                ])->setPaper('a4', 'landscape')->save($guestBookPath);

                                register_shutdown_function(function () use ($guestBookPath, $selfieBookPath, $tempSelfiePaths) {
                                    @unlink($guestBookPath);
                                    if ($selfieBookPath) {
                                        @unlink($selfieBookPath);
                                    }
                                    foreach ($tempSelfiePaths as $tempPath) {
                                        @unlink($tempPath);
                                    }
                                });

                                if ($hasSelfie && $selfieBookPath && file_exists($selfieBookPath)) {
                                    $zipPath = storage_path('app/public/books_' . $record->order_number . '.zip');
                                    $zip = new ZipArchive();
                                    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
                                        $zip->addFile($guestBookPath, 'guestbook.pdf');
                                        $zip->addFile($selfieBookPath, 'selfiebook.pdf');
                                        $zip->close();
                                    }

                                    return response()->download($zipPath)->deleteFileAfterSend(true);
                                }

                                return response()->download($guestBookPath)->deleteFileAfterSend(true);
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
