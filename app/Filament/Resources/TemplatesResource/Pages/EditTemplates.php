<?php

namespace App\Filament\Resources\TemplatesResource\Pages;

use App\Filament\Resources\TemplatesResource;
use App\Models\File;
use App\Models\Template;
use App\Models\TemplatePreview;
use App\Models\TemplateView;
use Exception;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Alignment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File as FacadesFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Browsershot\Browsershot;

class EditTemplates extends EditRecord
{
    protected static string $resource = TemplatesResource::class;

    public function getBreadcrumbs(): array
    {
        return [
            $this->getResource()::getUrl('index') => $this->getResource()::$breadcrumb,
            null => static::$breadcrumb ?? $this->getBreadcrumb(),
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->icon('heroicon-s-check-circle')
            ->title('Sucessfully')
            ->body('Template updated successfully');
    }

    protected function getRedirectUrl(): string
    {
        return '';
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCancelFormAction(),
            $this->getSubmitFormAction()
                ->label('Update'),
        ];
    }

    protected function afterSave(): void
    {
        $this->updateTemplateView($this->record, $this->data);
        $this->updateTemplatePreview($this->record, $this->data);
    }

    private function updateTemplateView(Template $record, array $data)
    {
        $disk = 'minio';
        $uuid = Str::uuid();

        if ($record?->viewHtml) {
            $existingTemplateView = $record->viewHtml;
            if ($existingTemplateView) {
                $existingTemplateViewFile = $existingTemplateView->file;

                if (Storage::disk($existingTemplateViewFile->disk)->exists($existingTemplateViewFile->path)) {
                    Storage::disk($existingTemplateViewFile->disk)->delete($existingTemplateViewFile->path);
                }
                $existingTemplateViewFile->delete();
                $existingTemplateView->delete();
            }
        }

        $htmlContent = $data['template_builder'];

        $htmlPath = "{$uuid}.html";
        Storage::disk($disk)->put($htmlPath, $htmlContent);
        if (!Storage::disk($disk)->exists($htmlPath)) {
            throw new Exception("Failed to store HTML file at {$htmlPath}", Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $size = Storage::disk($disk)->size($htmlPath);
        $htmlFile = File::create([
            'fileable_type' => TemplateView::class,
            'fileable_id' => $record->id,
            'name' => "HTML TemplateView {$record->id}",
            'original_name' => "{$uuid}.html",
            'filename' => $uuid,
            'path' => $htmlPath,
            'disk' => $disk,
            'extension' => 'html',
            'type' => 'other',
            'size' => $size,
            'mime_type' => 'text/html',
            'status' => 'uploaded',
            'visibility' => 'public',
        ]);

        TemplateView::create([
            'template_id' => $record->id,
            'file_id' => $htmlFile->id,
            'type' => 'html',
        ]);

        $cacheKey = "template_html_{$record->id}";
        Cache::forget($cacheKey);
    }

    private function updateTemplatePreview(Template $record, array $data)
    {
        $htmlContent = $data['template_builder'];

        $previewDisk = 'minio';
        $previewTypes = [
            'web' => [1920, 1080],
            'mobile' => [1080, 1920],
        ];

        foreach ($previewTypes as $type => [$width, $height]) {
            $previewUuid = Str::uuid();
            $previewFilename = "{$previewUuid}.png";
            $localPreviewPath = storage_path("app/template-previews/{$previewFilename}");

            $html = view('template-preview', [
                'html' => $htmlContent,
            ])->render();

            Browsershot::html($html)
                ->setRemoteInstance('172.22.0.100', '9222')
                ->windowSize($width, $height)
                ->waitUntilNetworkIdle()
                ->showBackground()
                ->save($localPreviewPath);

            if (!FacadesFile::exists($localPreviewPath)) {
                throw new \Exception("Screenshot failed for template {$record->id} ({$type})");
            }

            $remotePreviewPath = "template-previews/{$previewFilename}";

            $existingPreview = $record->previews()->where('type', $type)->first();
            if ($existingPreview) {
                $existingFile = $existingPreview->file;
                if ($existingFile && Storage::disk($existingFile->disk)->exists($existingFile->path)) {
                    Storage::disk($existingFile->disk)->delete($existingFile->path);
                }
                $existingFile?->delete();
                $existingPreview->delete();
            }

            Storage::disk($previewDisk)->put(
                $remotePreviewPath,
                file_get_contents($localPreviewPath)
            );

            $previewFile = File::create([
                'fileable_type' => TemplatePreview::class,
                'fileable_id' => $record->id,
                'name' => "Preview Image {$record->id} ({$type})",
                'original_name' => $previewFilename,
                'filename' => $previewUuid,
                'path' => $remotePreviewPath,
                'disk' => $previewDisk,
                'extension' => 'png',
                'type' => 'image',
                'size' => Storage::disk($previewDisk)->size($remotePreviewPath),
                'mime_type' => 'image/png',
                'status' => 'uploaded',
                'visibility' => 'public',
            ]);

            TemplatePreview::create([
                'template_id' => $record->id,
                'file_id' => $previewFile->id,
                'type' => $type,
            ]);
        }
    }

    public static string | Alignment $formActionsAlignment = Alignment::Between;
}
