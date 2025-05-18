<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomersResource\Pages;
use App\Filament\Resources\CustomersResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\HtmlString;

class CustomersResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $modelLabel = 'Customer';
    protected static ?string $pluralModelLabel = 'Customers';

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Shop';
    protected static ?int $navigationSort = 3;

    public static ?string $breadcrumb = 'Customers';

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
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->unique(ignoreRecord: true)
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->label(fn(string $context) => $context === 'create' ? 'Password' : 'New Password')
                            ->dehydrateStateUsing(fn($state) => filled($state) ? Hash::make($state) : null)
                            ->dehydrated(fn($state) => filled($state))
                            ->required(fn(string $context) => $context === 'create')
                            ->minLength(8)
                            ->revealable()
                            ->helperText('Please enter minimum 8 characters'),
                    ])
                    ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading('No customers yet')
            ->emptyStateDescription('Start by adding your first one!')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email Address')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('M d, Y')
                    ->searchable()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->modalHeading('Delete')
                    ->modalDescription(
                        new HtmlString(
                            'Deleting this customer will also remove their order.<br>
                            Are you sure you want to delete?'
                        )
                    )
                    ->modalSubmitActionLabel('Delete')
                    ->successNotification(null)
                    ->after(function () {
                        Notification::make()
                            ->success()
                            ->icon('heroicon-o-check-circle')
                            ->title('Sucessfully')
                            ->body('Customer deleted successfully')
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->role('client');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomers::route('/create'),
            'edit' => Pages\EditCustomers::route('/{record}/edit'),
        ];
    }
}
