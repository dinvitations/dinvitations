<?php

namespace App\Filament\Resources\TemplatesResource\Pages;

use App\Filament\Resources\TemplatesResource;
use App\Models\File;
use App\Models\Template;
use App\Models\TemplateView;
use Exception;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Alignment;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CreateTemplates extends CreateRecord
{
    protected static string $resource = TemplatesResource::class;

    protected static bool $canCreateAnother = false;

    public function getBreadcrumbs(): array
    {
        return [
            $this->getResource()::getUrl('index') => $this->getResource()::$breadcrumb,
            null => static::$breadcrumb ?? $this->getBreadcrumb(),
        ];
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->icon('heroicon-s-check-circle')
            ->title('Sucessfully')
            ->body('Template placed successfully');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCancelFormAction(),
            $this->getSubmitFormAction()
        ];
    }

    protected function afterCreate(): void
    {
        $this->createTemplateView($this->record, $this->data);
    }

    private function createTemplateView(Template $record, array $data)
    {
        $viewTypes = TemplateView::getTypes();
        $disk = 'minio';
        $uuid = Str::uuid();
        $folderPath = "template-views/{$uuid}";

        foreach ($viewTypes as $type => $meta) {
            $content = data_get($data['template_builder'], $type, '');

            if (is_array($content)) {
                $content = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            }

            if (empty($content)) {
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
    }

    public static string|Alignment $formActionsAlignment = Alignment::Between;
}
