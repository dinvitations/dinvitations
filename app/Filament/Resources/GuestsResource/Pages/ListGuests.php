<?php

namespace App\Filament\Resources\GuestsResource\Pages;

use App\Filament\Resources\GuestsResource;
use App\Models\Guest;
use App\Models\GuestGroup;
use Filament\Notifications\Notification;
use Filament\Pages\Actions\CreateAction;
use Filament\Resources\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\{Builder, Collection};

class ListGuests extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = GuestsResource::class;
    protected static string $view = 'filament.resources.guests-resource.pages.list-guests';

    public static ?string $title = 'Guests';

    public ?GuestGroup $selectedGroup = null;

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
            ->paginationPageOptions([3, 5, 10, 20])
            ->actions([
                Tables\Actions\Action::make('choose')
                    ->icon('heroicon-s-eye')
                    ->label(fn($record) => $record->id === $this->selectedGroup?->id ? 'Chosen' : 'Choose')
                    ->action(function (GuestGroup $record) {
                        $this->selectedGroup = $record;
                    }),
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
                    })
                    ->after(function (GuestGroup $record) {
                        if ($this->selectedGroup?->is($record))
                            $this->selectedGroup = null;
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
                        })
                        ->after(function (Collection $records) {
                            if (!$this->selectedGroup)
                                return;

                            if ($records->contains($this->selectedGroup))
                                $this->selectedGroup = null;
                        }),
                ]),
            ]);
    }

    public function getGuestsQuery(): Builder
    {
        return Guest::query()
            ->where('guest_group_id', $this->selectedGroup->id)
            ->with([
                'invitationGuests' => function ($query) {
                    $query
                        ->whereHas('invitation', function ($query) {
                            $query->where('date_end', '<=', now());
                        })
                        ->with([
                            'invitation' => function ($query) {
                                $query->where('date_end', '<=', now());
                            }
                        ]);
                }
            ]);
    }

    public function guestsTable(): ?Table
    {
        if (!$this->selectedGroup)
            return null;

        return Table::make($this)
            ->query($this->getGuestsQuery())
            ->defaultSort('updated_at', 'desc')
            ->heading($this->selectedGroup?->name)
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Category')
                    ->sortable()
                    ->formatStateUsing(fn($state, $record) => strtoupper($record)),
                Tables\Columns\TextColumn::make('phone_number')
                    ->label('Whatsapp Number'),
                Tables\Columns\TextColumn::make('barcode')
                    ->label('Barcode'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->sortable()
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->invitationGuests->isEmpty())
                            return 'Not Sent';

                        return match ($record->invitationGuests->first()?->rsvp) {
                            true => 'Attending',
                            false => 'Not Attending',
                            default => 'No Response',
                        };
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('send')
                    ->label('Send')
                    ->icon('heroicon-s-paper-airplane')
                    ->action(function ($record) {
                        // Logic kirim WhatsApp
                        Notification::make()
                            ->success()
                            ->title('Sent')
                            ->body("Message sent to {$record->name}")
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->model(Guest::class)
                    ->label('New Guest')
                    ->mutateFormDataUsing(fn($data) => array_merge($data, [
                        'guest_group_id' => $this->selectedGroup->id,
                    ])),
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
