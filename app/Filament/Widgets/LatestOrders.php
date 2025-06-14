<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\OrdersResource;
use App\Models\Order;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;

class LatestOrders extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->when(auth()->user()->isOrganizer(), function (Builder $query) {
                        $query->whereRelation('customer.organizer', 'id', auth()->user()->id);
                    })
            )
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading('No orders yet')
            ->emptyStateDescription('Start by creating an order.')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Order Date')
                    ->dateTime('M d, Y')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer.organizer.name')
                    ->label('Organizer')
                    ->searchable()
                    ->sortable()
                    ->visible(fn() => auth()->user()->isManager()),
                TextColumn::make('package.name')
                    ->label('Package')
                    ->formatStateUsing(function ($state) {
                        return ucfirst($state);
                    })
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
            ->filters([
                SelectFilter::make('status')
                    ->native(false)
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive'
                    ])
                    ->selectablePlaceholder(false)
                    ->default('active')
            ])
            ->actions([
                Action::make('open')
                    ->label('Open')
                    ->url(fn(Order $record): string => OrdersResource::getUrl('edit', ['record' => $record]))
                    ->visible(fn(Order $order) => auth()->user()->can('view', $order)),
            ])
            ->poll();
    }

    protected function paginateTableQuery(Builder $query): Paginator|CursorPaginator
    {
        return $query->paginate(
            perPage: ($this->getTableRecordsPerPage() === 'all') ? $query->count() : $this->getTableRecordsPerPage(),
            pageName: $this->getTablePaginationPageName(),
        );
    }
}
