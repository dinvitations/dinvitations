<?php

namespace App\Filament\Actions;

use App\Models\Guest;
use App\Models\InvitationGuest;
use App\Support\InvitationHelper;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Alignment;
use Filament\Support\RawJs;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Spatie\PdfToImage\Pdf as PdfToImage;

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

                    if (!$invitationGuest) {
                        Notification::make()
                            ->title('Error: Guest not found.')
                            ->danger()
                            ->send();
                            
                        return;
                    }

                    DB::transaction(function () use ($invitationGuest, $data) {
                        $invitationGuest->update([
                            'attended_at' => now(),
                            'guest_count' => (int) str_replace('.', '', $data['guest_count']),
                        ]);
                    });

                    // Generate QR code for QR souvenir in cloud storage
                    $souvenirQrPath = InvitationHelper::generateSouvenirQr($invitationGuest);
                    $fileName = "invitation_qrcode_{$invitationGuest->id}_souvenir";
                    $pdfPath = "souvenir-qr/{$invitationGuest->invitation_id}/pdf/{$fileName}.pdf";
                    $imagePath = "souvenir-qr/{$invitationGuest->invitation_id}/jpg/{$fileName}.jpg";

                    $disk = Storage::disk('minio');
                    $qrBinary = $disk->get($souvenirQrPath);

                    if (!$disk->exists($pdfPath)) {
                        $pdf = Pdf::loadView('pdf.qrcode', [
                            'guest' => $invitationGuest,
                            'qrCode' => base64_encode($qrBinary),
                            'type' => 'souvenir',
                        ])->setPaper([0, 0, 164.4, 113.4], 'portrait');
                        $disk->put($pdfPath, $pdf->output());
                    }

                    if (!$disk->exists($imagePath)) {
                        $this->generateQrImage($pdfPath, $imagePath, $fileName);
                    }

                    // Show success notification with QR opening script
                    Notification::make()
                        ->title('Guest Verified Successfully')
                        ->body("Guest {$invitationGuest->guest?->name} has been marked as attended.")
                        ->success()
                        ->send();

                    $payload = base64_encode(json_encode([
                        'id' => $invitationGuest->id,
                        'type' => 'souvenir',
                        'path' => $souvenirQrPath,
                    ]));
                    
                    $signedUrl = URL::signedRoute('qrcode.view', [
                        'qr' => $payload,
                        'user' => auth()?->user()?->id,
                    ]);

                    $livewire->js("window.open('$signedUrl', '_blank')");
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

    protected function generateQrImage(string $pdfPath, string $imagePath, string $fileName): void
    {
        $disk = Storage::disk('minio');
        $tempPdf = storage_path("app/public/{$fileName}.pdf");
        $tempImage = storage_path("app/public/{$fileName}.jpg");

        file_put_contents($tempPdf, $disk->get($pdfPath));
        $pdfImage = new PdfToImage($tempPdf);
        $pdfImage->save($tempImage);
        $disk->put($imagePath, file_get_contents($tempImage));

        @unlink($tempPdf);
        @unlink($tempImage);
    }
}
