<?php

namespace App\Filament\Resources;

use App\Enums\PermissionsEnum;
use App\Filament\Resources\CustomersResource\Pages;
use App\Models\Role;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\HtmlString;

class CustomersResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $modelLabel = 'Customer';
    protected static ?string $pluralModelLabel = 'Customers';

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = ' ';
    protected static ?int $navigationSort = 3;

    public static ?string $breadcrumb = 'Customers';

    public static function canAccess(): bool
    {
        return auth()->user()->can(PermissionsEnum::MANAGE_CUSTOMERS);
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

                        Forms\Components\Hidden::make('organizer_id')
                            ->default(auth()->user()->id),
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

                Tables\Columns\TextColumn::make('organizer.name')
                    ->label('Organizer')
                    ->searchable()
                    ->sortable()
                    ->visible(fn() => auth()->user()->isManager()),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('M d, Y')
                    ->searchable()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
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
                    ->after(function ($livewire) {
                        Notification::make()
                            ->success()
                            ->icon('heroicon-s-check-circle')
                            ->title('Sucessfully')
                            ->body('Customer deleted successfully')
                            ->send();

                        $livewire->resetTable();
                    }),
                Tables\Actions\RestoreAction::make()
                    ->modalHeading('Restore')
                    ->modalDescription(
                        new HtmlString(
                            'Are you sure you want to restore?<br>
                            You will need to fill the email address after restoring'
                        )
                    )
                    ->successNotification(null)
                    ->after(function ($record) {
                        Notification::make()
                            ->success()
                            ->icon('heroicon-s-check-circle')
                            ->title('Sucessfully')
                            ->body('Customer restored successfully')
                            ->send();

                        return redirect(static::getUrl('edit', ['record' => $record]));
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->authorize(fn() => auth()->user()->hasPermissionTo(PermissionsEnum::DELETE_CUSTOMERS))
                        ->modalHeading('Delete')
                        ->modalDescription(
                            new HtmlString(
                                'Deleting these customers will also remove their order.<br>
                                Are you sure you want to delete?'
                            )
                        )
                        ->modalSubmitActionLabel('Delete')
                        ->successNotification(null)
                        ->after(function ($livewire) {
                            Notification::make()
                                ->success()
                                ->icon('heroicon-s-check-circle')
                                ->title('Sucessfully')
                                ->body('Customers deleted successfully')
                                ->send();

                            $livewire->resetTable();
                        }),
                    Tables\Actions\RestoreBulkAction::make()
                        ->modalHeading('Restore')
                        ->modalDescription(
                            new HtmlString(
                                'Are you sure you want to restore?<br>
                                You will need to fill the email addresses after restoring'
                            )
                        )
                        ->successNotification(null)
                        ->after(function ($livewire) {
                            Notification::make()
                                ->success()
                                ->icon('heroicon-s-check-circle')
                                ->title('Sucessfully')
                                ->body('Customers restored successfully')
                                ->send();

                            $livewire->resetTable();
                        }),
                ]),
            ]);
    }

    public static function canCreate(): bool
    {
        return auth()->user()->hasPermissionTo(PermissionsEnum::CREATE_CUSTOMERS);
    }

    public static function canView(Model $customer): bool
    {
        return auth()->user()->hasPermissionTo(PermissionsEnum::VIEW_CUSTOMERS)
            && $customer->organizer_id === auth()->user()->id;
    }

    public static function canEdit(Model $customer): bool
    {
        return auth()->user()->hasPermissionTo(PermissionsEnum::EDIT_CUSTOMERS)
            && $customer->organizer_id === auth()->user()->id;
    }

    public static function canDelete(Model $customer): bool
    {
        return auth()->user()->hasPermissionTo(PermissionsEnum::DELETE_CUSTOMERS)
            && $customer->organizer_id === auth()->user()->id;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->role(Role::ROLES['client'])
            ->when(auth()->user()->isOrganizer(), function (Builder $query) {
                $query->whereRelation('organizer', 'id', auth()->user()->id);
            })
            ->latest('updated_at');
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
