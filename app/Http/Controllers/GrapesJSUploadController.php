<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GrapesJSUploadController extends Controller
{
    public function upload(Request $request)
    {
        try {
            $request->validate([
                'files.*' => 'required|file|max:10240',
            ]);

            $uploaded = [];

            $user = User::find($request->header('X-USER-ID'));
            if (!$user) {
                return response()->json(['message' => 'Invalid user.'], 403);
            }

            foreach ($request->file('files', []) as $upload) {
                $uuid = (string) Str::uuid();
                $extension = $upload->getClientOriginalExtension();
                $filename = pathinfo($upload->getClientOriginalName(), PATHINFO_FILENAME);
                $disk = 'uploads';
                $path = "{$uuid}.{$extension}";

                Storage::disk($disk)->put($path, file_get_contents($upload));

                if (!Storage::disk($disk)->exists($path)) {
                    Log::warning("Upload failed: file not found after put", [
                        'path' => $path,
                        'disk' => $disk,
                        'filename' => $upload->getClientOriginalName(),
                    ]);
                    continue;
                }

                $file = File::create([
                    'fileable_type' => User::class,
                    'fileable_id' => $user->id,
                    'name' => $filename,
                    'original_name' => $upload->getClientOriginalName(),
                    'filename' => $uuid,
                    'path' => $path,
                    'disk' => $disk,
                    'extension' => $extension,
                    'type' => $this->detectFileType($upload->getMimeType()),
                    'size' => $upload->getSize(),
                    'mime_type' => $upload->getMimeType(),
                    'status' => 'uploaded',
                    'visibility' => 'public',
                ]);

                $uploaded[] = [
                    'src' => Storage::disk($disk)->url($file->path),
                    'name' => $file->original_name ?? $file->filename,
                ];
            }

            return response()->json([
                'data' => $uploaded
            ]);
        } catch (\Throwable $e) {
            Log::error('GrapesJS upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $userId,
                'request' => $request->all(),
            ]);

            return response()->json(['message' => 'Upload failed.'], 500);
        }
    }

    protected function detectFileType($mime)
    {
        return match (true) {
            str_starts_with($mime, 'image/') => 'image',
            str_starts_with($mime, 'video/') => 'video',
            str_starts_with($mime, 'audio/') => 'audio',
            str_starts_with($mime, 'application/') => 'document',
            default => 'other',
        };
    }
}
