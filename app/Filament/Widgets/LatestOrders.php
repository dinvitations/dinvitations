<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;

class LatestOrders extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(Order::query())
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading('No orders yet')
            ->emptyStateDescription('Start by creating an order.')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Order Date')
                    ->dateTime('M d, Y')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('package.name')
                    ->label('Package')
                    ->formatStateUsing(function ($state) {
                        return ucfirst($state);
                    })
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->icon(
                        fn(string $state): string => $state === 'delivered'
                            ? 'heroicon-s-check-circle'
                            : 'heroicon-o-minus'
                    )
                    ->colors([
                        'success' => 'delivered',
                        'primary' => ['processing', 'closed', 'cancelled'],
                    ])
                    ->formatStateUsing(fn(string $state): string => $state === 'delivered' ? 'Active' : 'Inactive')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('price')
                    ->label('Total Price')
                    ->formatStateUsing(function ($state) {
                        return number_format($state, 0, ',', '.');
                    })
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Action::make('open')
                    ->label('Open')
                    // ->url(fn(Order $record): string => route('filament.resources.orders.view', $record->id)),
            ]);
    }
    
    protected function paginateTableQuery(Builder $query): Paginator | CursorPaginator
    {
        return $query->paginate(
            perPage: ($this->getTableRecordsPerPage() === 'all') ? $query->count() : $this->getTableRecordsPerPage(),
            pageName: $this->getTablePaginationPageName(),
        );
    }
}
