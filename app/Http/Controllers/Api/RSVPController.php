<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InvitationGuest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class RSVPController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'guest_id' => 'required|uuid|exists:invitation_guests,id',
                'rsvp' => 'nullable|boolean',
            ]);

            $guest = InvitationGuest::find($validated['guest_id']);

            $guest->rsvp = $validated['rsvp'];
            $guest->save();

            return response()->json([
                'status' => 'success',
                'rsvp' => $guest->rsvp,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Throwable $e) {
            Log::error('RSVP submission failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong. Please try again later.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
