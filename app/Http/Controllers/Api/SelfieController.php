<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\Invitation;
use App\Models\InvitationGuest;
use Filament\Notifications\Notification;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class SelfieController extends Controller
{
    public function upload(Request $request)
    {
        try {
            $data = $request->validate([
                'photoData' => 'required|string',
                'guestId' => 'required|string|exists:invitation_guests,id',
            ]);

            $userId = auth()?->user()?->id;

            $photoData = $data['photoData'];
            $guestId = $data['guestId'];

            $invitation = Invitation::whereNotNull('published_at')
                ->whereHas('order', function ($q) use ($userId) {
                    $q->where('status', 'active')->where('user_id', $userId);
                })->first();

            if (!$invitation) {
                return response()->json([
                    'message' => 'No active invitation found for this user.',
                ], Response::HTTP_NOT_FOUND);
            }

            $guest = InvitationGuest::where('id', $guestId)
                ->where('invitation_id', $invitation->id)
                ->first();

            if (!$guest) {
                return response()->json(['message' => 'Guest not found for this event.'], Response::HTTP_NOT_FOUND);
            }

            $guest->selfie_at = now();
            $guest->save();

            // Prepare image data
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

            $disk = 'minio';
            $uuid = Str::uuid();
            $filename = "{$uuid}.{$extension}";
            $relativePath = "selfie/{$invitation->id}/{$filename}";

            Storage::disk($disk)->put($relativePath, $image);

            if (!Storage::disk($disk)->exists($relativePath)) {
                throw new \Exception("Failed to store selfie at {$relativePath}");
            }

            $size = Storage::disk($disk)->size($relativePath);
            $mime = "image/{$extension}";

            $file = File::create([
                'fileable_type' => InvitationGuest::class,
                'fileable_id' => $guest->id,
                'name' => "Selfie {$guest->id}",
                'original_name' => $filename,
                'filename' => pathinfo($filename, PATHINFO_FILENAME),
                'path' => $relativePath,
                'disk' => $disk,
                'extension' => $extension,
                'type' => 'image',
                'size' => $size,
                'mime_type' => $mime,
                'status' => 'uploaded',
                'visibility' => 'public',
            ]);

            Notification::make()
                ->title('Selfie saved successfully')
                ->body('Guest selfie has been saved.')
                ->success()
                ->send();

            return response()->json([
                'message' => 'Selfie uploaded successfully!',
                'path' => Storage::disk($disk)->temporaryUrl($relativePath, now()->addMinutes(5)),
                'guestId' => $guestId,
            ]);
        } catch (Throwable $e) {
            Log::error('Selfie upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'message' => 'Something went wrong while processing the selfie.',
                'error' => app()->isProduction() ? null : $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
