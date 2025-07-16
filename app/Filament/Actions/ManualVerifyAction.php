<?php

namespace App\Filament\Actions;

use App\Models\Guest;
use App\Models\InvitationGuest;
use App\Support\InvitationHelper;
use Filament\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Alignment;
use Filament\Support\RawJs;
use Illuminate\Support\Facades\DB;

class ManualVerifyAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'manual_verify';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Manual Verify')
            ->color('gray')
            ->modalHeading('Search Guest')
            ->modalButton('Submit')
            ->modalCancelAction(false)
            ->modalFooterActionsAlignment(Alignment::End)
            ->form([
                Grid::make(2)->schema([
                    Select::make('guest_id')
                        ->label('Name')
                        ->searchable()
                        ->required()
                        ->live()
                        ->getSearchResultsUsing(
                            fn(string $search) =>
                            Guest::where('name', 'ILIKE', "%$search%")
                                ->whereHas('invitationGuests', function ($query) {
                                    $query->whereNull('attended_at')
                                        ->whereHas('invitation.order', function ($qO) {
                                            $qO->where('status', 'active');
                                        });
                                })
                                ->orderBy('name')
                                ->pluck('name', 'id')
                        )
                        ->getOptionLabelUsing(
                            fn($value): ?string =>
                            Guest::find($value)?->name
                        ),

                    TextInput::make('guest_count')
                        ->label('Total Guests')
                        ->required()
                        ->default(1)
                        ->rules(['integer', 'min:1'])
                        ->mask(RawJs::make(<<<'JS'
                            $input => {
                                let number = $input.replace(/\D/g, '');
                                return number.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                            }
                        JS))
                ])
            ])
            ->action(function (array $data) {
                $invitationGuest = InvitationGuest::query()
                    ->where('guest_id', $data['guest_id'])
                    ->latest()
                    ->with(['guest', 'invitation'])
                    ->first();

                if ($invitationGuest) {
                    DB::transaction(function () use ($invitationGuest, $data) {
                        $invitationGuest->update([
                            'attended_at' => now(),
                            'guest_count' => (int) str_replace('.', '', $data['guest_count']),
                        ]);

                        Notification::make()
                            ->title('Guest Verified Successfully')
                            ->body("Guest {$invitationGuest->guest?->name} has been marked as attended.")
                            ->success()
                            ->send();
                    });

                    // Generate QR code for QR souvenir
                    InvitationHelper::generateSouvenirQr($invitationGuest);
                } else {
                    Notification::make()
                        ->title('Error: Guest not found.')
                        ->danger()
                        ->send();
                }
            });
    }
}
