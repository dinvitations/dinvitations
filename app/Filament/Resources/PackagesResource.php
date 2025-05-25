<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PackagesResource\Pages;
use App\Models\Feature;
use App\Models\Package;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PackagesResource extends Resource
{
    protected static ?string $model = Package::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox';
    protected static ?string $navigationLabel = 'Packages';
    protected static ?string $navigationGroup = 'Manage';
    protected static ?int $navigationSort = 2;

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
                            ->required(),

                        Forms\Components\TagsInput::make('features')
                            ->label('Features')
                            ->required()
                            ->color('success')
                            ->suggestions(
                                Feature::where('status', 'active')
                                    ->orderBy('name')
                                    ->pluck('name')->toArray()
                            )
                            ->placeholder('Enter the name')
                            ->helperText('Choose one or more features to include in this package.')
                            ->columnSpan(2),
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
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->action(fn($record) => $record->forceDelete())
                    ->modalHeading('Delete')
                    ->modalSubheading('Are you sure you want to delete?')
                    ->modalButton('Delete'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No packages yet')
            ->emptyStateDescription('Start by adding your first one!')
            ->defaultPaginationPageOption(5);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPackages::route('/'),
            'create' => Pages\CreatePackages::route('/create'),
            'edit' => Pages\EditPackages::route('/{record}/edit'),
        ];
    }
}
