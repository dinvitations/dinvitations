<?php

namespace App\Livewire;

use App\Models\Guest;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Tables\Contracts\HasTable;
use Livewire\Component;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Concerns\InteractsWithTable;

class GuestTable extends Component implements HasTable, HasForms
{
    use InteractsWithTable, InteractsWithForms;

    public ?string $groupId = null;

    public function mount(string $groupId): void
    {
        $this->groupId = $groupId;
    }

    protected function getTableQuery(): Builder
    {
        return Guest::query()
            ->where('guest_group_id', $this->groupId)
            ->with([
                'invitationGuests' => function ($query) {
                    $query
                        ->whereHas('invitation', function ($query) {
                            $query->where('date_end', '<=', now());
                        })
                        ->with(
                            [
                                'invitation' => function ($query) {
                                    $query->where('date_end', '<=', now());
                                }
                            ]
                        );
                }
            ]);
    }

    public function getTableHeading(): string
    {
        return $this->guestGroup?->name ?? 'Guests';
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('name')
                ->label('Name')
                ->sortable()
                ->searchable(),
            Tables\Columns\TextColumn::make('type_default')
                ->label('Category')
                ->formatStateUsing(fn($state) => match ($state) {
                    'reg' => 'General',
                    'vip' => 'VIP',
                    'vvip' => 'VVIP',
                }),
            Tables\Columns\TextColumn::make('phone_number')
                ->label('Whatsapp Number'),
            Tables\Columns\TextColumn::make('barcode')
                ->label('Barcode'),
            Tables\Columns\TextColumn::make('status')
                ->label('Status')
                ->getStateUsing(function ($record) {
                    if ($record->invitationGuests->isEmpty())
                        return 'Not Sent';

                    return match ($record->invitationGuests->first()?->rsvp) {
                        true => 'Attending',
                        false => 'Not Attending',
                        default => 'No Response',
                    };
                }),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Tables\Actions\Action::make('send')
                ->label('Send')
                ->icon('heroicon-s-paper-airplane')
                ->action(function ($record) {
                    \Filament\Notifications\Notification::make()
                        ->success()
                        ->title('Sent')
                        ->body("Message sent to {$record->name}")
                        ->send();
                }),
            Tables\Actions\EditAction::make()
                ->model(Guest::class)
                ->form([
                    \Filament\Forms\Components\TextInput::make('name')
                        ->label('Name')
                        ->required()
                        ->placeholder('E.g. John Doe'),

                    \Filament\Forms\Components\Grid::make(2)
                        ->schema([
                            \Filament\Forms\Components\Select::make('type_default')
                                ->required()
                                ->label('Category')
                                ->placeholder('Choose guest category')
                                ->options([
                                    'reg' => 'General',
                                    'vip' => 'VIP',
                                    'vvip' => 'VVIP',
                                ])
                                ->default('reg'),

                            \Filament\Forms\Components\TextInput::make('phone_number')
                                ->tel()
                                ->label('Whatsapp Number')
                                ->prefix('+62')
                                ->placeholder('E.g 812-...')
                                ->helperText('Make sure to enter a reachable phone number.'),
                        ]),
                ])
                ->modalHeading('Edit Guest')
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
                        ->body('Guest updated successfully');
                }),
            Tables\Actions\DeleteAction::make()
                ->modalHeading('Delete')
                ->modalSubheading(fn($record) => 'Are you sure you want to delete?')
                ->modalButton('Delete')
                ->successNotification(notification: function () {
                    return Notification::make()
                        ->success()
                        ->icon('heroicon-s-check-circle')
                        ->title('Successfully')
                        ->body('Guest deleted successfully');
                }),
        ];
    }

    protected function getTableBulkActions(): array
    {
        return [
            Tables\Actions\DeleteBulkAction::make()
                ->label('Delete Selected')
                ->modalHeading('Delete Selected Guests')
                ->modalSubheading('Are you sure you want to delete the selected guests?')
                ->modalButton('Delete')
                ->successNotification(function () {
                    return Notification::make()
                        ->success()
                        ->icon('heroicon-s-check-circle')
                        ->title('Success')
                        ->body('Selected guests deleted successfully.');
                }),
        ];
    }

    protected function getTableHeaderActions(): array
    {
        return [
            Tables\Actions\CreateAction::make()
                ->model(Guest::class)
                ->label('Create New Guest')
                ->form([
                    \Filament\Forms\Components\TextInput::make('name')
                        ->placeholder('E.g. John Doe')
                        ->required(),

                    \Filament\Forms\Components\Grid::make(2)
                        ->schema([
                            \Filament\Forms\Components\Select::make('type_default')
                                ->required()
                                ->label('Category')
                                ->placeholder('Choose guest category')
                                ->options([
                                    'reg' => 'General',
                                    'vip' => 'VIP',
                                    'vvip' => 'VVIP',
                                ])
                                ->default('reg'),

                            \Filament\Forms\Components\TextInput::make('phone_number')
                                ->tel()
                                ->label('Whatsapp Number')
                                ->prefix('+62')
                                ->placeholder('E.g 812-...')
                                ->helperText('Make sure to enter a reachable phone number.'),
                        ]),
                ])
                ->mutateFormDataUsing(fn($data) => array_merge($data, [
                    'guest_group_id' => $this->groupId,
                ]))
                ->modalHeading('Create New Guest'),
        ];
    }

    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [10, 25, 50, 100];
    }

    protected function getDefaultTableSortColumn(): ?string
    {
        return 'updated_at';
    }

    protected function getDefaultTableSortDirection(): ?string
    {
        return 'desc';
    }


    public function render()
    {
        return view('livewire.guest-table');
    }
}
