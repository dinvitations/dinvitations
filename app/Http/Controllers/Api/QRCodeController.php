<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use Illuminate\Http\Request;
use App\Models\InvitationGuest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\URL;

class QRCodeController extends Controller
{
    /**
     * Handle QR scan and update attendance.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'qrPayload' => 'required|array',
                'qrPayload.id' => 'required|string|exists:invitation_guests,id',
                'qrPayload.type' => 'required|string|in:attendance',
                'userId' => 'required|string|exists:users,id',
            ]);

            $qrPayload = $request->input('qrPayload');
            $guestId = $qrPayload['id'];

            $userId = $request->input('userId');

            $invitation = Invitation::whereNotNull('published_at')
                ->whereHas('order', function ($subQuery) use ($userId) {
                    $subQuery->where('status', 'active')->where('user_id', $userId);
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
                return response()->json([
                    'message' => 'Event has not started yet.',
                ], Response::HTTP_BAD_REQUEST);
            }

            if ($now->gt($eventEnd)) {
                return response()->json([
                    'message' => 'Event has already ended.',
                ], Response::HTTP_BAD_REQUEST);
            }

            $guest = InvitationGuest::where('id', $guestId)
                ->where('invitation_id', $invitation->id)
                ->firstOrFail();

            if ($guest->attended_at) {
                return response()->json([
                    'message' => 'This guest has already checked in.',
                    'guest_id' => $guest->id,
                ], Response::HTTP_BAD_REQUEST);
            }

            $guest->attended_at = $now;
            $guest->save();

            $qrPayload = base64_encode(json_encode([
                'id' => $guest->id,
                'type' => 'souvenir',
            ]));

            $signedUrl = URL::signedRoute('api.qr_pdf', [
                'qr' => $qrPayload,
                'user' => $userId,
            ]);

            return response()->json([
                'message' => 'Check-in successful.',
                'guest_id' => $guest->id,
                'pdf_url' => $signedUrl,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Guest not found or does not belong to your invitation.'
            ], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Something went wrong while processing the QR code. Please try again.',
                'error' => app()->environment('production') ? null : $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Generate and return a PDF with the QR code.
     */
    public function view(Request $request)
    {
        try {
            if (!$request->hasValidSignature() || !$request->has('user') || !$request->has('qr')) {
                abort(Response::HTTP_FORBIDDEN, 'The link has expired or is invalid.');
            }

            $userId = $request->query('user');
            $encodedQr = $request->query('qr');
            $decodedQr = base64_decode($encodedQr, true);
            $qrPayload = json_decode($decodedQr, true);

            if (!is_array($qrPayload) || !isset($qrPayload['id'], $qrPayload['type'])) {
                abort(Response::HTTP_BAD_REQUEST, 'Invalid QR code data.');
            }

            $invitation = Invitation::whereNotNull('published_at')
                ->whereHas('order', function ($subQuery) use ($userId) {
                    $subQuery->where('status', 'active')->where('user_id', $userId);
                })
                ->first();

            if (!$invitation) {
                abort(Response::HTTP_NOT_FOUND, 'No active invitation found for this user.');
            }

            $guest = InvitationGuest::where('id', $qrPayload['id'])
                ->where('invitation_id', $invitation->id)
                ->firstOrFail();

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
        } catch (ModelNotFoundException $e) {
            abort(Response::HTTP_NOT_FOUND, 'Guest not found or you do not have access.');
        } catch (Exception $e) {
            if (!empty($guest) && $guest?->attended_at) {
                $guest->attended_at = null;
                $guest->save();
            }

            abort(Response::HTTP_INTERNAL_SERVER_ERROR, app()->environment('production')
                ? 'Something went wrong while generating the QR code.'
                : 'Failed to generate PDF: ' . $e->getMessage());
        }
    }
}
