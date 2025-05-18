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

    protected bool $hasSummary = false;

    public function table(Table $table): Table
    {
        $query = Order::query()
            ->with(['package', 'customer'])
            ->latest();

        return $table
            ->query($query)
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading('No orders yet')
            ->emptyStateDescription('Start by creating an order.')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Order Date')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('package.name')
                    ->label('Package')
                    ->formatStateUsing(function ($state) {
                        return ucfirst($state);
                    })
                    ->sortable()
                    ->searchable(),

                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->icon(
                        fn(string $state): string => $state === 'processing'
                            ? 'heroicon-s-check-circle'
                            : 'heroicon-o-minus'
                    )
                    ->colors([
                        'success' => 'processing',
                        'primary' => ['delivered', 'cancelled'],
                    ])
                    ->formatStateUsing(fn(string $state): string => $state === 'processing' ? 'Active' : 'Inactive')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('price')
                    ->label('Total Price')
                    ->formatStateUsing(function ($state) {
                        return number_format($state, 0, ',', '.');
                    })
                    ->sortable(),
            ])
            ->actions([
                Action::make('open')
                    // ->url(fn(Order $record): string => route('filament.resources.orders.view', $record->id))
                    ->label('Open')
                    ->color('primary')
                    ->openUrlInNewTab(),
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
