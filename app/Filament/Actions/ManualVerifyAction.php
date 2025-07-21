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
use Illuminate\Support\Facades\Storage;

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
            ->action(function (array $data, $livewire) {
                try {
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
                        });

                        // Generate QR code for QR souvenir in cloud storage
                        $souvenirQrPath = InvitationHelper::generateSouvenirQr($invitationGuest);

                        // Generate QR pdf for souvenir in local storage
                        $diskMinio = 'minio';
                        // $diskLocal = 'public';
                        // $pdf = Pdf::loadView('pdf.qrcode', [
                        //     'guest' => $invitationGuest,
                        //     'qrCode' => base64_encode(Storage::disk($diskMinio)->get($souvenirQrPath)),
                        //     'type' => 'souvenir',
                        // ]);
                        // $pdf->setPaper([0, 0, 164.4, 113.4], 'portrait');
                        // $pdfPath = "$souvenirQrPath.pdf";

                        // // Save souvenir QR pdf to local storage, because PdfToImage cannot access cloud storage
                        // Storage::disk($diskLocal)->put($pdfPath, $pdf->download()->getOriginalContent());

                        // if (Storage::disk($diskLocal)->exists($pdfPath)) {
                        //     // Generate souvenir QR image from pdf
                        //     (new PdfToImage(Storage::disk($diskLocal)->path($pdfPath)))
                        //         ->format(\Spatie\PdfToImage\Enums\OutputFormat::Png)
                        //         ->save(Storage::disk($diskMinio)->path($souvenirQrPath));

                        //     // Move QR pdf to cloud storage and delete local version
                        //     Storage::disk($diskMinio)->put($pdfPath, Storage::disk($diskLocal)->get($pdfPath));
                        //     Storage::disk($diskLocal)->delete($pdfPath);
                        // }

                        // Show success notification with QR opening script
                        Notification::make()
                            ->title('Guest Verified Successfully')
                            ->body("Guest {$invitationGuest->guest?->name} has been marked as attended.")
                            ->success()
                            ->send();

                        // Redirect to souvenir QR page
                        $qrUrl = Storage::disk($diskMinio)->temporaryUrl($souvenirQrPath, now()->addMinutes(5));
                        $livewire->js("window.open('$qrUrl', '_blank')");
                    } else {
                        Notification::make()
                            ->title('Error: Guest not found.')
                            ->danger()
                            ->send();
                    }
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Error: Verification failed.')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                    throw ($e);
                }
            });
    }
}
