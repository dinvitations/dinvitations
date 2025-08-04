<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feature;
use App\Models\Invitation;
use App\Models\InvitationGuest;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Log;

class GreetingController extends Controller
{
    public function upload(Request $request)
    {
        // Validate request
        $request->validate([
            'greeting' => ['nullable', 'string'],
            'guest_id' => ['required', 'exists:invitation_guests,id'],
        ]);

        // Validate active invitation
        $userId = auth()?->user()?->id;
        $invitation = Invitation::whereNotNull('published_at')
            ->whereHas('order', function ($query) use ($userId) {
                $query->where('status', 'active')
                    ->where('user_id', $userId)
                    ->whereHas('package.features', function ($featureQuery) {
                        $featureQuery->where('name', Feature::FEATURES['greeting']);
                    });
            })
            ->first();

        if (!$invitation) {
            return response()->json([
                'message' => 'No active invitation found for this user.',
            ], Response::HTTP_NOT_FOUND);
        }

        // Validate guest
        $guest = InvitationGuest::where('id', $request->guest_id)
            ->where('invitation_id', $invitation->id)
            ->first();

        if (!$guest)
            return response()->json(['message' => 'Guest not found for this event.'], Response::HTTP_NOT_FOUND);

        try {
            // Parse base64 string if not empty
            if (!empty($request->greeting)) {
                $photoData = $request->greeting;
                if (preg_match('/^data:image\/(\w+);base64,/', $photoData, $type)) {
                    $photoData = substr($photoData, strpos($photoData, ',') + 1);
                    $extension = strtolower($type[1]);
                } else {
                    throw new \Exception('Invalid image data format.');
                }

                $image = base64_decode($photoData);

                if ($image === false) {
                    throw new \Exception('Base64 decoding failed.');
                }

                // Generate unique filename
                $disk = 'minio';
                $uuid = Str::uuid();
                $filename = "{$uuid}.{$extension}";
                $relativePath = "greeting/{$invitation->id}/{$filename}";

                // Save to storage
                Storage::disk($disk)->put($relativePath, $image);

                if (!Storage::disk($disk)->exists($relativePath)) {
                    throw new \Exception("Failed to store greeting at {$relativePath}");
                }
            } else {
                $relativePath = '';
            }

            $guest->greeting_wall_image_url = $relativePath;
            $guest->save();

            Notification::make()
                ->title('Greeting saved successfully')
                ->body('Guest greeting has been saved.')
                ->success()
                ->send();

            return redirect()->route('greeting.display');
        } catch (\Throwable $th) {
            Log::error('Selfie upload failed', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Something went wrong while processing the greeting.',
                'error' => app()->isProduction() ? null : $th->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
