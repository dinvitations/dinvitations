<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PackagesResource\Pages;
use App\Models\Feature;
use App\Models\Package;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;

class PackagesResource extends Resource
{
    protected static ?string $model = Package::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox';
    protected static ?string $navigationLabel = 'Package Categories';
    protected static ?string $navigationGroup = 'Manage';
    protected static ?int $navigationSort = 2;

    public static function shouldRegisterNavigation(): bool
    {
        return !auth()->user()->role('client');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Package')
                            ->required(),

                        Forms\Components\TextInput::make('price')
                            ->numeric()
                            ->minValue(0)
                            ->required()
                            ->mask(RawJs::make(<<<'JS'
                                $input => {
                                    let number = $input.replace(/\D/g, '');
                                    return number.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                                }
                            JS))
                            ->stripCharacters('.'),

                        Forms\Components\Select::make('features')
                            ->label('Features')
                            ->disabled()
                            ->multiple()
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->reactive()
                            ->options(function ($get) {
                                return Feature::query()
                                    ->active()
                                    ->whereNotIn('name', $get('features') ?? [])
                                    ->orderBy('name')
                                    ->limit(5)
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->placeholder('Enter the name')
                            ->helperText('Choose one or more features to include in this package.')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Package')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Price')
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('M d, Y')
                    ->label('Last Updated')
                    ->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make()
            ])
            ->bulkActions([])
            ->emptyStateHeading('No packages yet')
            ->emptyStateDescription('Start by adding your first one!')
            ->defaultPaginationPageOption(5);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPackages::route('/'),
            'edit' => Pages\EditPackages::route('/{record}/edit'),
        ];
    }
}
