<?php

namespace App\Filament\Resources;

use App\Enums\PermissionsEnum;
use App\Filament\Resources\OrdersResource\Pages;
use App\Models\Order;
use App\Models\Package;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrdersResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationGroup = ' ';
    protected static ?int $navigationSort = 2;

    public static ?string $breadcrumb = 'Orders';

    public static function canAccess(): bool
    {
        return auth()->user()->can(PermissionsEnum::MANAGE_ORDERS);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Name / Email Address')
                            ->placeholder('Choose a customer email')
                            ->required()
                            ->searchable()
                            ->getSearchResultsUsing(function (string $search): array {
                                return User::role('client')
                                    ->where(function ($query) use ($search) {
                                        $query->where('name', 'ilike', "%{$search}%");
                                        $query->orWhere('email', 'ilike', "%{$search}%");
                                    })
                                    ->limit(5)
                                    ->get()
                                    ->mapWithKeys(function ($user) {
                                        return [$user->id => "{$user->name} ({$user->email})"];
                                    })
                                    ->toArray();
                            })
                            ->getOptionLabelUsing(function ($value): ?string {
                                $user = User::role('client')->find($value);
                                return $user ? "{$user->name} ({$user->email})" : null;
                            }),
                        Forms\Components\Select::make('package_id')
                            ->label('Package')
                            ->placeholder('- Select a package -')
                            ->required()
                            ->options(Package::pluck('name', 'id'))
                            ->native(false)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $package = Package::find($state);
                                if ($package) {
                                    $set('price', number_format($package->price, 0, ',', '.'));
                                }
                            }),
                        Forms\Components\TextInput::make('price')
                            ->mask(RawJs::make(<<<'JS'
                            $input => {
                                let number = $input.replace(/\D/g, '');
                                return number.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                            }
                        JS))
                            ->stripCharacters('.')
                            ->numeric()
                            ->disabled()
                            ->dehydrated()
                            ->helperText(function (string $context) {
                                return $context === 'edit' ? 'To change prices, go to the package menu' : null;
                            }),
                        Forms\Components\ToggleButtons::make('status')
                            ->dehydrated(false)
                            ->disabled()
                            ->default('active')
                            ->options([
                                'inactive' => 'Inactive',
                                'active' => 'Active',
                            ])
                            ->icons([
                                'inactive' => 'heroicon-o-minus',
                                'active' => 'heroicon-s-check-circle',
                            ])
                            ->inline(),
                    ])
                    ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading('No orders yet')
            ->emptyStateDescription('Start by creating an order.')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Order Date')
                    ->dateTime('M d, Y')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('package.name')
                    ->label('Package')
                    ->formatStateUsing(function ($state) {
                        return ucfirst($state);
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.organizer.name')
                    ->label('Organizer')
                    ->searchable()
                    ->sortable()
                    ->visible(fn() => auth()->user()->isManager()),
                Tables\Columns\TextColumn::make('price')
                    ->label('Total Price')
                    ->formatStateUsing(function ($state) {
                        return number_format($state, 0, ',', '.');
                    })
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->modalHeading('Delete')
                    ->modalDescription('Are you sure you want to delete?')
                    ->modalSubmitActionLabel('Delete')
                    ->successNotification(function ($livewire) {
                        Notification::make()
                            ->success()
                            ->icon('heroicon-s-check-circle')
                            ->title('Sucessfully')
                            ->body('Order deleted successfully')
                            ->send();

                        $livewire->resetTable();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->authorize(fn() => auth()->user()->can(PermissionsEnum::DELETE_ORDERS))
                        ->modalHeading('Delete')
                        ->modalDescription('Are you sure you want to delete?')
                        ->modalSubmitActionLabel('Delete')
                        ->successNotification(function ($livewire) {
                            Notification::make()
                                ->success()
                                ->icon('heroicon-s-check-circle')
                                ->title('Sucessfully')
                                ->body('Orders deleted successfully')
                                ->send();

                            $livewire->resetTable();
                        }),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('customer.organizer')
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->when(auth()->user()->isOrganizer(), function (Builder $query) {
                $query->whereRelation('customer.organizer', 'id', auth()->user()->id);
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrders::route('/create'),
            'edit' => Pages\EditOrders::route('/{record}/edit'),
        ];
    }
}
