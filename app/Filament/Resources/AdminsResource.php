<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdminsResource\Pages;
use App\Models\Role;
use App\Models\User;
use Filament\Forms\Form;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Str;

class AdminsResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationGroup = 'Manage';

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Admins';

    public static ?string $breadcrumb = 'Admin';

    public static function getBreadcrumb(): string
    {
        return '';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Name')
                            ->placeholder('Enter the name')
                            ->required(),

                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->label('Email Address')
                            ->placeholder('Ex: example@mail.com')
                            ->required()
                            ->unique('users', 'email', ignoreRecord: true),

                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->label(fn(string $context) => $context === 'create' ? 'Password' : 'New Password')
                            ->placeholder(fn(string $context) => $context === 'create' ? 'Enter password' : 'Enter new password')
                            ->dehydrated(fn($state) => filled($state))
                            ->required(fn(string $context): bool => $context === 'create')
                            ->minLength(8)
                            ->helperText('Please enter minimum 8 characters'),

                        Forms\Components\Select::make('role')
                            ->label('Role')
                            ->required()
                            ->relationship('roles', 'name')
                            ->options(
                                Role::query()
                                    ->whereIn('name', ['manager', 'event_organizer', 'wedding_organizer'])
                                    ->pluck('name', 'id')
                                    ->map(fn($name) => Str::headline($name))
                                    ->toArray()
                            )
                            ->placeholder('Choose the role')
                            ->searchable()
                            ->reactive()
                    ])
                    ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email Address')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->formatStateUsing(fn($state) => Str::headline($state ?? '-'))
                    ->label('Role')
                    ->searchable()
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
                    Tables\Actions\DeleteBulkAction::make()
                        ->action(fn($records) => $records->each->forceDelete()),
                ]),
            ])
            ->emptyStateHeading('No admins yet')
            ->emptyStateDescription('Start by adding your first one!');
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
        return parent::getEloquentQuery()->role(['manager', 'event_organizer', 'wedding_organizer']);
    }
}
