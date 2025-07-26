<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feature;
use App\Models\Invitation;
use App\Models\InvitationGuest;
use App\Support\InvitationHelper;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Spatie\PdfToImage\Pdf as PdfToImage;

class QRCodeController extends Controller
{
    /**
     * Handle QR scan and update attendance or souvenir.
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'qrPayload' => 'required|array',
                'qrPayload.id' => 'required|string|exists:invitation_guests,id',
                'qrPayload.type' => 'required|string|in:attendance,souvenir',
                'guestCount' => 'sometimes|integer|min:1',
            ]);

            $userId = auth()?->user()?->id;

            $qrPayload = $request->input('qrPayload');
            $guestId = $qrPayload['id'];
            $type = $qrPayload['type'];
            $guestCount = (int) $request->input('guestCount', 1);

            $invitation = $this->getActiveInvitationForUser($userId);
            if (!$invitation) {
                return $this->error('No active invitation found for this user.', Response::HTTP_NOT_FOUND);
            }

            $now = now();
            if ($now->lt(Carbon::parse($invitation->date_start))) {
                return $this->error('Event has not started yet.', Response::HTTP_BAD_REQUEST);
            }
            if ($now->gt(Carbon::parse($invitation->date_end))) {
                return $this->error('Event has already ended.', Response::HTTP_BAD_REQUEST);
            }

            $guest = InvitationGuest::where('id', $guestId)
                ->where('invitation_id', $invitation->id)
                ->first();

            if (!$guest) {
                return $this->error('Guest not found for this event.', Response::HTTP_NOT_FOUND);
            }

            return $type === 'attendance'
                ? $this->handleAttendance($guest, $guestCount, $userId)
                : $this->handleSouvenir($guest, $invitation);
        } catch (Exception $e) {
            Log::error('QR Code processing error', ['error' => $e->getMessage(), 'request' => $request->all()]);
            return $this->error(
                app()->environment('production')
                    ? 'Something went wrong while processing the QR code.'
                    : $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Display QR PDF/Image view.
     */
    public function view(Request $request)
    {
        $guest = null;

        try {
            if (!$request->hasValidSignature() || !$request->has(['user', 'qr'])) {
                abort(Response::HTTP_FORBIDDEN, 'The link has expired or is invalid.');
            }

            $qrPayload = json_decode(base64_decode($request->query('qr')), true);

            if (!is_array($qrPayload) || !isset($qrPayload['id'], $qrPayload['type'], $qrPayload['path'])) {
                abort(Response::HTTP_BAD_REQUEST, 'Invalid QR code data.');
            }

            $invitation = $this->getActiveInvitationForUser($request->query('user'));
            if (!$invitation) {
                abort(Response::HTTP_NOT_FOUND, 'No active invitation found for this user.');
            }

            $guest = InvitationGuest::where('id', $qrPayload['id'])
                ->where('invitation_id', $invitation->id)
                ->first();

            if (!$guest) {
                abort(Response::HTTP_NOT_FOUND, 'Guest not found for this event.');
            }

            $fileName = "invitation_qrcode_{$guest->id}_{$qrPayload['type']}";
            $pdfPath = "souvenir-qr/{$guest->invitation_id}/pdf/{$fileName}.pdf";
            $imagePath = "souvenir-qr/{$guest->invitation_id}/jpg/{$fileName}.jpg";
            $disk = Storage::disk('minio');

            if (!$disk->exists($pdfPath) || !$disk->exists($imagePath)) {
                abort(Response::HTTP_NOT_FOUND, 'QR code file not found.');
            }

            return view('livewire.view-qr-code', [
                'pdfUrl' => URL::signedRoute('qrcode.print', [
                    'path' => $pdfPath,
                ]),
                'imageUrl' => $disk->temporaryUrl($imagePath, now()->addMinutes(5)),
                'hasSelfieFeature' => auth()->user()->hasFeature(Feature::FEATURES['selfie']),
            ]);
        } catch (Exception $e) {
            Log::error('QR Code view error', ['error' => $e->getMessage(), 'request' => $request->all()]);

            if (!empty($guest?->attended_at)) {
                $guest->attended_at = null;
                $guest->save();
            }

            abort(Response::HTTP_INTERNAL_SERVER_ERROR, app()->isProduction()
                ? 'Something went wrong while generating the QR code.'
                : 'Failed to generate QR: ' . $e->getMessage());
        }
    }

    /**
     * Print QR code PDF.
     */
    public function print(Request $request)
    {
        if (!$request->hasValidSignature() || !$request->has('path')) {
            abort(Response::HTTP_FORBIDDEN, 'The link has expired or is invalid.');
        }

        $path = $request->query('path');
        $disk = Storage::disk('minio');

        if (!Str::startsWith($path, 'souvenir-qr/') || !$disk->exists($path)) {
            abort(Response::HTTP_NOT_FOUND, 'File not found or access denied.');
        }

        $mimeType = $disk->mimeType($path);
        $filename = basename($path);
        $stream = $disk->readStream($path);

        return response()->stream(function () use ($stream) {
            fpassthru($stream);
            fclose($stream);
        }, Response::HTTP_OK, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }

    protected function handleAttendance(InvitationGuest $guest, int $guestCount, string $userId)
    {
        // if ($guest->attended_at) {
        //     return $this->error('Guest has already checked in.', Response::HTTP_BAD_REQUEST);
        // }

        $guest->update([
            'attended_at' => now(),
            'guest_count' => $guestCount,
        ]);

        $souvenirQrPath = InvitationHelper::generateSouvenirQr($guest);
        $fileName = "invitation_qrcode_{$guest->id}_souvenir";
        $pdfPath = "souvenir-qr/{$guest->invitation_id}/pdf/{$fileName}.pdf";
        $imagePath = "souvenir-qr/{$guest->invitation_id}/jpg/{$fileName}.jpg";

        $disk = Storage::disk('minio');
        $qrBinary = $disk->get($souvenirQrPath);

        if (!$disk->exists($pdfPath)) {
            $pdf = Pdf::loadView('pdf.qrcode', [
                'guest' => $guest,
                'qrCode' => base64_encode($qrBinary),
                'type' => 'souvenir',
            ])->setPaper([0, 0, 164.4, 113.4], 'portrait');
            $disk->put($pdfPath, $pdf->output());
        }

        if (!$disk->exists($imagePath)) {
            $this->generateQrImage($pdfPath, $imagePath, $fileName);
        }

        $payload = base64_encode(json_encode([
            'id' => $guest->id,
            'type' => 'souvenir',
            'path' => $souvenirQrPath,
        ]));

        $signedUrl = URL::signedRoute('qrcode.view', [
            'qr' => $payload,
            'user' => $userId,
        ]);

        return response()->json([
            'message' => 'Check-in successful.',
            'guest_id' => $guest->id,
            'qrcode_view_url' => $signedUrl,
        ]);
    }

    protected function handleSouvenir(InvitationGuest $guest, Invitation $invitation)
    {
        // if ($guest->souvenir_at) {
        //     return $this->error('Souvenir already taken.', Response::HTTP_BAD_REQUEST);
        // }

        if (($invitation->availableSouvenirStock()) <= 0) {
            return $this->error('No more souvenir stock available.', Response::HTTP_BAD_REQUEST);
        }

        $guest->update([
            'souvenir_at' => now(),
            'left_at' => now(),
        ]);

        return response()->json([
            'message' => 'Souvenir pickup recorded.',
            'guest_id' => $guest->id,
        ]);
    }

    protected function getActiveInvitationForUser(string $userId): ?Invitation
    {
        return Invitation::whereNotNull('published_at')
            ->whereHas('order', function ($query) use ($userId) {
                $query->where('status', 'active')
                    ->where('user_id', $userId);
            }, '=', 1)
            ->first();
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

    protected function error(string $message, int $status = Response::HTTP_BAD_REQUEST)
    {
        return response()->json(['message' => $message], $status);
    }
}
