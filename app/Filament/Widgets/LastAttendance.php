<?php

namespace App\Filament\Widgets;

use App\Models\InvitationGuest;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;

class LastAttendance extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                InvitationGuest::query()
                    ->whereNotNull('attended_at')
                    ->latest('attended_at')
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
                    ->state(fn(InvitationGuest $record) => $record->attended_at?->format('h:i A'))
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
                    ->formatStateUsing(fn($state) => strtoupper($state)),
            ])
            ->actions([
                ViewAction::make('open')
                    ->label('Open')
                    ->modalHeading('Last Attendance')
                    ->modalContent(function (InvitationGuest $record) {
                        return view('filament.widgets.partials.last-attendance-modal', [
                            'guest' => $record->guest->name,
                            'category' => strtoupper($record->type),
                            'attendedAt' => $record->attended_at?->format('M d, Y \a\t h:i A'),
                        ]);
                    }),
            ])
            ->defaultSort('attended_at', 'desc');
    }

    protected function paginateTableQuery(Builder $query): Paginator
    {
        return $query->paginate(
            perPage: ($this->getTableRecordsPerPage() === 'all') ? $query->count() : $this->getTableRecordsPerPage(),
            pageName: $this->getTablePaginationPageName(),
        );
    }
}
