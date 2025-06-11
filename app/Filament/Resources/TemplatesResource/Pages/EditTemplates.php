<?php

namespace App\Filament\Resources\TemplatesResource\Pages;

use App\Filament\Resources\TemplatesResource;
use App\Models\File;
use App\Models\Template;
use App\Models\TemplateView;
use Exception;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Alignment;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
    }

    private function updateTemplateView(Template $record, array $data)
    {
        $viewTypes = TemplateView::getTypes();
        $disk = 'minio';
        $uuid = Str::uuid();
        $folderPath = "template-views/{$uuid}";

        $record->views()
            ->whereIn('type', array_keys($viewTypes))
            ->each(function ($view) {
                if ($view->file && Storage::disk($view->file->disk)->exists($view->file->path)) {
                    Storage::disk($view->file->disk)->delete($view->file->path);
                }
                $view->file?->delete();
                $view->delete();
            });

        foreach ($viewTypes as $type => $meta) {
            $content = data_get($data['template_builder'], $type, '');
            
            if (is_array($content)) {
                $content = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            }

            if (empty($content) || trim($content) === '') {
                continue;
            }

            $relativePath = "{$folderPath}/{$meta['filename']}";
            $filename = "{$uuid}_" . str_replace('.', '_', $type);

            Storage::disk($disk)->put($relativePath, $content);

            if (!Storage::disk($disk)->exists($relativePath)) {
                throw new \Exception("Failed to store {$type} file at {$relativePath}", Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $size = Storage::disk($disk)->size($relativePath);

            $file = File::create([
                'fileable_type' => TemplateView::class,
                'fileable_id' => $record->id,
                'name' => strtoupper($type) . " TemplateView {$record->id}",
                'original_name' => $meta['filename'],
                'filename' => $filename,
                'path' => $relativePath,
                'disk' => $disk,
                'extension' => $meta['extension'],
                'type' => 'other',
                'size' => $size,
                'mime_type' => $meta['mime'],
                'status' => 'uploaded',
                'visibility' => 'public',
            ]);

            TemplateView::create([
                'template_id' => $record->id,
                'file_id' => $file->id,
                'type' => $type,
            ]);
        }

        Cache::forget("template_builder_data_{$record->id}");
    }

    public static string | Alignment $formActionsAlignment = Alignment::Between;
}
