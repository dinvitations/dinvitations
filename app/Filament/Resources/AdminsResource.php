<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdminsResource\Pages;
use App\Models\User;
use Filament\Forms\Form;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AdminsResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationGroup = 'Manage';

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Admins';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Name')
                            ->placeholder('Masukkan nama Admin')
                            ->required(),

                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->label('Email')
                            ->placeholder('Cth: example@mail.com')
                            ->required()
                            ->unique('users', 'email', ignoreRecord: true),

                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->label('Password')
                            ->placeholder('Masukkan password')
                            ->dehydrated(fn($state) => filled($state))
                            ->required(fn(string $context): bool => $context === 'create')
                            ->minLength(8),
                    ])
                    ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('M d, Y')
                    ->label('Last Update'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->modalHeading('Delete')
                    ->modalSubheading('Are you sure you want to delete?')
                    ->modalButton('Delete'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No admins yet')
            ->emptyStateDescription('Start by adding your first one!');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdmins::route('/'),
            'create' => Pages\CreateAdmins::route('/create'),
            'edit' => Pages\EditAdmins::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereRelation('roles', 'name', 'admin');
    }
}
