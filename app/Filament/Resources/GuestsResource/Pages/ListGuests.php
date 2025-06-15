<?php

namespace App\Filament\Resources\GuestsResource\Pages;

use App\Filament\Resources\GuestsResource;
use App\Models\GuestGroup;
use Filament\Notifications\Notification;
use Filament\Pages\Actions\CreateAction;
use Filament\Resources\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ListGuests extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = GuestsResource::class;
    protected static string $view = 'filament.resources.guests-resource.pages.list-guests';

    public static ?string $title = 'Guests';

    public function getBreadcrumbs(): array
    {
        return [];
    }

    public function query(): Builder
    {
        return GuestGroup::query()
            ->when(auth()->user()->isClient(), function ($query) {
                return $query->where('customer_id', auth()->user()->id);
            });
    }

    public function getGuestGroupsTableQuery()
    {
        return GuestGroup::query();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->query())
            ->emptyStateHeading('No guest group yet')
            ->emptyStateDescription('Please create a guest group before adding guests.')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->sortable()
                    ->searchable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->defaultPaginationPageOption(3)
            ->paginationPageOptions([3, 5, 10, 20])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalHeading('Edit Guest Group')
                    ->model(GuestGroup::class)
                    ->form([
                        \Filament\Forms\Components\TextInput::make('name')
                            ->required(),
                    ])
                    ->modalActions([
                        \Filament\Actions\Action::make('submit')
                            ->label('Update')
                            ->submit('update'),

                        \Filament\Actions\Action::make('cancel')
                            ->label('Cancel')
                            ->color('gray')
                            ->close(),
                    ])
                    ->successNotification(function () {
                        return Notification::make()
                            ->success()
                            ->icon('heroicon-s-check-circle')
                            ->title('Successfully')
                            ->body('Guest group updated successfully');
                    }),
                Tables\Actions\DeleteAction::make()
                    ->modalHeading('Delete')
                    ->modalSubheading(fn($record) => 'This action will permanently delete all guests in this group. Are you sure you want to delete?')
                    ->modalButton('Delete')
                    ->successNotification(notification: function () {
                        return Notification::make()
                            ->success()
                            ->icon('heroicon-s-check-circle')
                            ->title('Successfully')
                            ->body('Guest group deleted successfully');
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Delete Selected')
                        ->modalHeading('Delete Selected Guest Groups')
                        ->modalSubheading('Are you sure you want to delete the selected guest groups?')
                        ->modalButton('Delete')
                        ->successNotification(function () {
                            return Notification::make()
                                ->success()
                                ->icon('heroicon-s-check-circle')
                                ->title('Successfully')
                                ->body('Selected guest groups deleted successfully');
                        }),
                ]),
            ]);
    }

    public function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New Group')
                ->modalHeading('Create New Guest Group')
                ->model(GuestGroup::class)
                ->createAnother(false)
                ->form([
                    \Filament\Forms\Components\TextInput::make('name')
                        ->required(),
                ])
                ->mutateFormDataUsing(function (array $data): array {
                    $data['customer_id'] = auth()->user()->id;
                    return $data;
                })
                ->successNotification(notification: function () {
                    return Notification::make()
                        ->success()
                        ->icon('heroicon-s-check-circle')
                        ->title('Successfully')
                        ->body('Guest group placed successfully');
                }),
        ];
    }
}
