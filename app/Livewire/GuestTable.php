<?php

namespace App\Livewire;

use App\Models\Guest;
use Filament\Forms\Components\{Grid, Select, TextInput};
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Support\RawJs;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\{Table, Columns, Actions, Filters};
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class GuestTable extends Component implements HasTable, HasForms
{
    use InteractsWithTable, InteractsWithForms;

    public ?string $groupId = null, $groupName = null;

    public function mount(string $groupId, string $groupName): void
    {
        $this->groupId = $groupId;
        $this->groupName = $groupName;
    }

    public function table(Table $table): Table
    {
        return $table->heading($this->groupName ?? 'Guests')
            ->query(
                Guest::query()
                    ->where('guest_group_id', $this->groupId)
                    ->with([
                        'invitationGuests' => fn($q) => $q
                            ->whereHas('invitation', function ($qI) {
                                $qI->whereNotNull('published_at')
                                    ->whereHas('order', function ($qO) {
                                        $qO->where('status', 'active')
                                            ->where('user_id', auth()->user()->id);
                                    }, '=', 1);
                            })
                            ->with([
                                'invitation' => function ($qI) {
                                    $qI->whereNotNull('published_at')
                                        ->whereHas('order', function ($qO) {
                                            $qO->where('status', 'active')
                                                ->where('user_id', auth()->user()->id);
                                        }, '=', 1);
                                },
                            ])
                    ])
            )
            ->columns([
                Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable(),

                Columns\TextColumn::make('type_default')
                    ->label('Category')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'reg' => 'General',
                        'vip' => 'VIP',
                        'vvip' => 'VVIP',
                        default => $state,
                    }),

                Columns\TextColumn::make('phone_number')
                    ->label('Whatsapp Number')
                    ->formatStateUsing(fn($state) => "+62 $state")
                    ->placeholder('No Data'),

                Columns\TextColumn::make('status')
                    ->state(function ($record) {
                        if ($record->invitationGuests->isEmpty())
                            return 'Not Sent';

                        return match ($record->invitationGuests->first()?->rsvp) {
                            true => 'Attending',
                            false => 'Not Attending',
                            default => 'No Response',
                        };
                    }),
            ])
            ->emptyStateHeading('No guests yet')
            ->emptyStateDescription('Start by adding your first one!')
            ->filters([
                Filters\SelectFilter::make('type_default')
                    ->label('Category')
                    ->placeholder('Choose guest category')
                    ->native(false)
                    ->options([
                        'reg' => 'General',
                        'vip' => 'VIP',
                        'vvip' => 'VVIP',
                    ]),

                Filters\Filter::make('status')
                    ->form([
                        Select::make('status')
                            ->label('Status')
                            ->placeholder('Choose guest status')
                            ->options([
                                'Not Sent' => 'Not Sent',
                                'Attending' => 'Attending',
                                'Not Attending' => 'Not Attending',
                                'No Response' => 'No Response',
                            ])
                            ->native(false)
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['status'])) {
                            return $query;
                        }

                        return match ($data['status']) {
                            'Not Sent' => $query->doesntHave('invitationGuests'),
                            'Attending' => $query->whereRelation('invitationGuests', 'rsvp', true),
                            'Not Attending' => $query->whereRelation('invitationGuests', 'rsvp', false),
                            'No Response' => $query->whereRelation('invitationGuests', 'rsvp', null),
                        };
                    })
                    ->indicateUsing(function (array $data): ?string {
                        return blank($data['status']) ? null : 'Status: ' . $data['status'];
                    })
            ])
            ->actions([
                Actions\Action::make('send')
                    ->icon('heroicon-o-paper-airplane')
                    ->label('Send')
                    ->visible(function (Guest $guest) {
                        return auth()->user()->orders()->where('status', 'active')
                            ->whereHas('invitation', function ($query) {
                                $query->whereNotNull('published_at');
                            })->exists()
                            && $guest->invitationGuests->isEmpty();
                    })
                    ->action(function (Guest $record, Actions\Action $action) {
                        $order = auth()->user()->orders()->firstWhere('status', 'active');
                        if (!$order || !$order->invitation) {
                            Notification::make()
                                ->title('Action Failed')
                                ->body('You do not have any active invitation.')
                                ->danger()
                                ->send();

                            $action->halt();
                        }

                        DB::transaction(function () use ($record, $order) {
                            $record->invitationGuests()->create([
                                'invitation_id' => $order->invitation?->id,
                                'type' => $record->type_default,
                            ]);

                            // TODO: Send WhatsApp message here

                            Notification::make()
                                ->title('Success')
                                ->body('Invitation sent successfully')
                                ->success()
                                ->send();
                        });
                    }),

                Actions\Action::make('copy')
                    ->icon('heroicon-o-paper-airplane')
                    ->label('Copy')
                    ->visible(fn(Guest $guest) => $guest->invitationGuests->isNotEmpty())
                    ->action(null),

                Actions\EditAction::make()
                    ->form([
                        TextInput::make('name')
                            ->required()
                            ->placeholder('E.g. John Doe'),
                        Grid::make(2)
                            ->schema([
                                Select::make('type_default')
                                    ->required()
                                    ->label('Category')
                                    ->placeHolder('- Choose guest category -')
                                    ->options([
                                        'reg' => 'General',
                                        'vip' => 'VIP',
                                        'vvip' => 'VVIP',
                                    ])
                                    ->native(false),
                                TextInput::make('phone_number')
                                    ->label('Whatsapp Number')
                                    ->tel()
                                    ->prefix('+62')
                                    ->placeholder('Ex: 812-3456-7890')
                                    ->helperText('Make sure to enter a reachable phone number.')
                                    ->mask(RawJs::make(<<<'JS'
                                        $input => {
                                            let numbers = $input.replace(/\D/g, '').replace(/^0+/, '');
                                            numbers = numbers.slice(0, 13);

                                            const parts = [];
                                            if (numbers.length <= 3) {
                                                parts.push(numbers);
                                            } else if (numbers.length <= 7) {
                                                parts.push(numbers.slice(0, 3), numbers.slice(3));
                                            } else if (numbers.length <= 11) {
                                                parts.push(numbers.slice(0, 3), numbers.slice(3, 7), numbers.slice(7));
                                            } else {
                                                parts.push(numbers.slice(0, 3), numbers.slice(3, 7), numbers.slice(7, 11), numbers.slice(11));
                                            }

                                            return parts.join('-');
                                        }
                                    JS))
                                    ->afterStateHydrated(function ($state, Set $set) {
                                        if (!$state)
                                            return;

                                        $digits = preg_replace('/\D+/', '', $state);
                                        $local = preg_replace('/^(62|0)/', '', $digits);

                                        $set('phone_number', $local);
                                    })
                                    ->rule(fn() => function ($attribute, $value, $fail) {
                                        $digits = ltrim(preg_replace('/\D+/', '', $value), '0');
                                        $length = strlen($digits);

                                        if ($length < 7 || $length > 13) {
                                            $fail('The WhatsApp number must be between 7 and 13 digits.');
                                        }
                                    }),
                            ])

                    ])
                    ->after(function (Guest $guest, array $data) {
                        $invitationGuest = $guest->invitationGuests->first();
                        if ($invitationGuest)
                            $invitationGuest->update([
                                'type' => $data['type_default'],
                            ]);
                    })
                    ->modalHeading('Edit Guest')
                    ->modalFooterActions(fn(Actions\EditAction $action) => [
                        $action->getModalSubmitAction()->label('Update'),
                        $action->getModalCancelAction(),
                    ])
                    ->successNotification(function () {
                        return Notification::make()
                            ->success()
                            ->icon('heroicon-s-check-circle')
                            ->title('Successfully')
                            ->body('Guest updated successfully');
                    }),
                Actions\DeleteAction::make()
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
            ])
            ->bulkActions([
                Actions\DeleteBulkAction::make()
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
            ])
            ->headerActions([
                Actions\CreateAction::make()
                    ->model(Guest::class)
                    ->label('New Guest')
                    ->form([
                        TextInput::make('name')
                            ->required()
                            ->placeholder('E.g. John Doe'),

                        Grid::make(2)
                            ->schema([
                                Select::make('type_default')
                                    ->required()
                                    ->label('Category')
                                    ->placeholder('- Choose guest category -')
                                    ->options([
                                        'reg' => 'General',
                                        'vip' => 'VIP',
                                        'vvip' => 'VVIP',
                                    ])
                                    ->default('reg')
                                    ->native(false),

                                TextInput::make('phone_number')
                                    ->label('Whatsapp Number')
                                    ->tel()
                                    ->prefix('+62')
                                    ->placeholder('Ex: 812-3456-7890')
                                    ->helperText('Make sure to enter a reachable phone number.')
                                    ->mask(RawJs::make(<<<'JS'
                                        $input => {
                                            let numbers = $input.replace(/\D/g, '').replace(/^0+/, '');
                                            numbers = numbers.slice(0, 13);

                                            const parts = [];
                                            if (numbers.length <= 3) {
                                                parts.push(numbers);
                                            } else if (numbers.length <= 7) {
                                                parts.push(numbers.slice(0, 3), numbers.slice(3));
                                            } else if (numbers.length <= 11) {
                                                parts.push(numbers.slice(0, 3), numbers.slice(3, 7), numbers.slice(7));
                                            } else {
                                                parts.push(numbers.slice(0, 3), numbers.slice(3, 7), numbers.slice(7, 11), numbers.slice(11));
                                            }

                                            return parts.join('-');
                                        }
                                    JS))
                                    ->afterStateHydrated(function ($state, Set $set) {
                                        if (!$state)
                                            return;

                                        $digits = preg_replace('/\D+/', '', $state);
                                        $local = preg_replace('/^(62|0)/', '', $digits);

                                        $set('phone_number', $local);
                                    })
                                    ->rule(fn() => function ($attribute, $value, $fail) {
                                        $digits = ltrim(preg_replace('/\D+/', '', $value), '0');
                                        $length = strlen($digits);

                                        if ($length < 7 || $length > 13) {
                                            $fail('The WhatsApp number must be between 7 and 13 digits.');
                                        }
                                    }),
                            ]),
                    ])
                    ->mutateFormDataUsing(fn($data) => array_merge($data, [
                        'guest_group_id' => $this->groupId,
                    ]))
                    ->modalHeading('Create New Guest')
                    ->successNotification(
                        fn() => Notification::make()
                            ->success()
                            ->icon('heroicon-s-check-circle')
                            ->title('Successfully')
                            ->body('Guest created successfully')
                    ),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public function render()
    {
        return view('livewire.guest-table');
    }
}
