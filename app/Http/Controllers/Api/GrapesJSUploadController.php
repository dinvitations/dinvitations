<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class GrapesJSUploadController extends Controller
{
    public function upload(Request $request)
    {
        $user = null;

        try {
            $request->validate([
                'files.*' => 'required|file|max:10240', // 10MB per file
            ]);

            $userId = auth()?->user()?->id;

            $uploads = $request->file('files', []);
            if (empty($uploads)) {
                return response()->json(['message' => 'No files uploaded.'], Response::HTTP_NOT_FOUND);
            }

            $uploaded = [];
            $disk = 'uploads';

            foreach ($uploads as $upload) {
                $uuid = (string) Str::uuid();
                $extension = $upload->getClientOriginalExtension();
                $originalName = $upload->getClientOriginalName();
                $filename = Str::slug(pathinfo($originalName, PATHINFO_FILENAME));
                $path = "{$uuid}.{$extension}";

                Storage::disk($disk)->put($path, file_get_contents($upload));

                if (!Storage::disk($disk)->exists($path)) {
                    Log::warning("Upload failed: file not found after put", [
                        'path' => $path,
                        'disk' => $disk,
                        'filename' => $originalName,
                        'user_id' => $userId,
                    ]);
                    continue;
                }

                $file = File::create([
                    'fileable_type' => User::class,
                    'fileable_id' => $userId,
                    'name' => $filename,
                    'original_name' => $originalName,
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

            return response()->json(['data' => $uploaded]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Throwable $e) {
            Log::error('GrapesJS upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $userId,
                'request' => $request->all(),
            ]);

            return response()->json(['message' => 'Upload failed.'], Response::HTTP_INTERNAL_SERVER_ERROR);
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
