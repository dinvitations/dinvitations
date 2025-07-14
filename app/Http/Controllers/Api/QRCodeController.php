<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\InvitationGuest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Response;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Throwable;

class QRCodeController extends Controller
{
    /**
     * Handle QR scan and update attendance or souvenir status.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'qrPayload' => 'required|array',
                'qrPayload.id' => 'required|string|exists:invitation_guests,id',
                'qrPayload.type' => 'required|string|in:attendance,souvenir',
                'userId' => 'required|string|exists:users,id',
                'guestCount' => 'sometimes|integer|min:1',
            ]);

            $qrPayload = $request->input('qrPayload');
            $guestId = $qrPayload['id'];
            $type = $qrPayload['type'];
            $userId = $request->input('userId');
            $guestCount = (int) $request->input('guestCount', 1);

            $invitation = Invitation::whereNotNull('published_at')
                ->whereHas('order', function ($q) use ($userId) {
                    $q->where('status', 'active')->where('user_id', $userId);
                })->first();

            if (!$invitation) {
                return response()->json([
                    'message' => 'No active invitation found for this user.',
                ], Response::HTTP_NOT_FOUND);
            }

            $now = now();
            $eventStart = Carbon::parse($invitation->date_start);
            $eventEnd = Carbon::parse($invitation->date_end);

            if ($now->lt($eventStart)) {
                return response()->json(['message' => 'Event has not started yet.'], 400);
            }

            if ($now->gt($eventEnd)) {
                return response()->json(['message' => 'Event has already ended.'], 400);
            }

            $guest = InvitationGuest::where('id', $guestId)
                ->where('invitation_id', $invitation->id)
                ->first();

            if (!$guest) {
                return response()->json(['message' => 'Guest not found for this event.'], 404);
            }

            if ($type === 'attendance') {
                if ($guest->attended_at) {
                    return response()->json(['message' => 'Guest already checked in.'], 400);
                }

                $guest->attended_at = $now;
                $guest->guest_count = $guestCount;
                $guest->save();

                $pdfPayload = base64_encode(json_encode([
                    'id' => $guest->id,
                    'type' => 'souvenir',
                ]));

                $signedUrl = URL::signedRoute('api.qr_pdf', [
                    'qr' => $pdfPayload,
                    'user' => $userId,
                ]);

                return response()->json([
                    'message' => 'Check-in successful.',
                    'guest_id' => $guest->id,
                    'pdf_url' => $signedUrl,
                ]);
            }

            if ($type === 'souvenir') {
                if ($guest->souvenir_at) {
                    return response()->json(['message' => 'Souvenir already taken.'], 400);
                }

                $claimed = InvitationGuest::where('invitation_id', $invitation->id)
                    ->whereNotNull('souvenir_at')->count();

                $available = $invitation->souvenir_stock - $claimed;

                if ($available <= 0) {
                    return response()->json(['message' => 'No more souvenir stock available.'], 400);
                }

                $guest->souvenir_at = $now;
                $guest->left_at = $now;
                $guest->save();

                return response()->json([
                    'message' => 'Souvenir pickup recorded.',
                    'guest_id' => $guest->id,
                ]);
            }

            return response()->json(['message' => 'Unsupported QR type.'], 400);

        } catch (Throwable $e) {
            Log::error('QR Code processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'message' => 'Something went wrong while processing the QR code.',
                'error' => app()->isProduction() ? null : $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate and stream a QR code PDF.
     */
    public function view(Request $request)
    {
        $guest = null;

        try {
            if (!$request->hasValidSignature() || !$request->has(['user', 'qr'])) {
                abort(403, 'The link has expired or is invalid.');
            }

            $userId = $request->query('user');
            $decoded = base64_decode($request->query('qr'), true);
            $qrPayload = json_decode($decoded, true);

            if (!is_array($qrPayload) || !isset($qrPayload['id'], $qrPayload['type'])) {
                abort(400, 'Invalid QR code data.');
            }

            $invitation = Invitation::whereNotNull('published_at')
                ->whereHas('order', function ($q) use ($userId) {
                    $q->where('status', 'active')->where('user_id', $userId);
                })->first();

            if (!$invitation) {
                abort(404, 'No active invitation found for this user.');
            }

            $guest = InvitationGuest::where('id', $qrPayload['id'])
                ->where('invitation_id', $invitation->id)
                ->first();

            if (!$guest) {
                return response()->json(['message' => 'Guest not found for this event.'], 404);
            }

            $qrCode = base64_encode(
                QrCode::format('png')->size(160)->generate(json_encode($qrPayload))
            );

            $pdf = Pdf::loadView('pdf.qrcode', [
                'guest' => $guest,
                'qrCode' => $qrCode,
                'type' => $qrPayload['type'],
            ]);
            $pdf->setPaper([0, 0, 164.4, 113.4], 'portrait');

            return $pdf->stream("invitation_qrcode_{$guest->id}_{$qrPayload['type']}.pdf");
        } catch (Throwable $e) {
            if ($guest && $guest->attended_at) {
                $guest->attended_at = null;
                $guest->save();
            }

            abort(500, app()->isProduction()
                ? 'Something went wrong while generating the QR code.'
                : 'Failed to generate PDF: ' . $e->getMessage()
            );
        }
    }
}
