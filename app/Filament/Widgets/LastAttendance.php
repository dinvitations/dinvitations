<?php

namespace App\Filament\Widgets;

use App\Models\Invitation;
use App\Models\InvitationGuest;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class LastAttendance extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected function getTableHeading(): string | Htmlable | null
    {
        $invitation = Invitation::whereNotNull('published_at')
            ->whereHas('order', function ($query) {
                $query->where('status', 'active')
                    ->where('user_id', auth()->user()->id);
            }, '=', 1)
            ->first();

        $heading = static::$heading ?? (string) str(class_basename(static::class))
                    ->beforeLast('Widget')
                    ->kebab()
                    ->replace('-', ' ')
                    ->title();

        return $invitation?->event_name ? $heading . ' - ' . $invitation?->event_name : $heading;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                InvitationGuest::query()
                    ->whereHas('invitation', function ($query) {
                        $query->whereNotNull('published_at')
                            ->whereHas('order', function ($subQuery) {
                                $subQuery->where('status', 'active');
                                $subQuery->where('user_id', auth()->user()->id);
                            }, '=', 1);
                    })
                    ->whereNotNull('attended_at')
                    ->orderByRaw('left_at IS NOT NULL')
            )
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading('No attendance yet')
            ->emptyStateDescription('Attendance data is not available yet.')
            ->columns([
                TextColumn::make('attended_at')
                    ->label('Date')
                    ->date('M d, Y')
                    ->sortable(),

                TextColumn::make('attended_at_time')
                    ->label('Time')
                    ->state(fn ($record) => $record->attended_at)
                    ->time('h:i A')
                    ->sortable(),

                TextColumn::make('guest_count')
                    ->label('Total Guests')
                    ->sortable(),

                TextColumn::make('guest.name')
                    ->label('Guest')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Category')
                    ->badge()
                    ->colors([
                        'gray' => 'reg',
                        'warning' => 'vip',
                        'danger' => 'vvip',
                    ])
                    ->formatStateUsing(fn($state) => match ($state) {
                        'reg' => 'General',
                        'vip' => 'VIP',
                        'vvip' => 'VVIP',
                        default => strtoupper($state),
                    }),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make('open')
                        ->label('Open')
                        ->modalHeading('Last Attendance')
                        ->modalContent(function (InvitationGuest $record) {
                            return view('filament.widgets.partials.last-attendance-modal', [
                                'guest' => $record->guest->name,
                                'category' => strtoupper($record->type),
                                'attendedAt' => $record->attended_at?->format('M d, Y \a\t h:i A'),
                                'souvenirAt' => $record->souvenir_at?->format('M d, Y \a\t h:i A'),
                                'leftAt' => $record->left_at?->format('M d, Y \a\t h:i A'),
                                'guestCount' => $record->guest_count,
                                'souvenirQrPath' => Storage::disk('minio')->exists($record->souvenir_qr_path)
                                    ? Storage::disk('minio')->temporaryUrl($record->souvenir_qr_path, now()->addMinutes(5))
                                    : null,
                            ]);
                        }),
                    Action::make('claimSouvenir')
                        ->icon('heroicon-m-gift')
                        ->label('Claim Souvenir')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->modalHeading('Confirm Souvenir Claim')
                        ->modalDescription(fn (InvitationGuest $record) => "Are you sure you want to claim a souvenir for {$record->guest->name}?")
                        ->action(function (InvitationGuest $record) {
                            if ($record->souvenir_at) {
                                Notification::make()
                                    ->title('Souvenir already claimed.')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            $record->souvenir_at = now();
                            $record->save();

                            Notification::make()
                                ->title('Souvenir claimed successfully.')
                                ->success()
                                ->send();
                        })
                        ->hidden(fn (InvitationGuest $record) => filled($record->souvenir_at)),
                    Action::make('markLeft')
                        ->icon('heroicon-m-arrow-right-on-rectangle')
                        ->label('Mark as Left')
                        ->requiresConfirmation()
                        ->modalHeading('Confirm Guest Left')
                        ->modalDescription(fn (InvitationGuest $record) => "Are you sure you want to mark {$record->guest->name} as left?")
                        ->action(function (InvitationGuest $record) {
                            if ($record->left_at) {
                                Notification::make()
                                    ->title('Guest already marked as left.')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            $record->left_at = now();
                            $record->save();

                            Notification::make()
                                ->title('Guest marked as left successfully.')
                                ->success()
                                ->send();
                        })
                        ->hidden(fn (InvitationGuest $record) => filled($record->left_at)),
                ]),
            ])
            ->defaultSort('attended_at', 'desc')
            ->poll('5s');
    }

    protected function paginateTableQuery(Builder $query): Paginator
    {
        return $query->paginate(
            perPage: ($this->getTableRecordsPerPage() === 'all') ? $query->count() : $this->getTableRecordsPerPage(),
            pageName: $this->getTablePaginationPageName(),
        );
    }
}
