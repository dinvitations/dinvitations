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
            $this->getSubmitFormAction(),
        ];
    }

    protected function afterCreate(): void
    {
        $this->createTemplateView($this->record, $this->data);
    }

    private function createTemplateView(Template $record, array $data)
    {
        $disk = 'minio';
        $uuid = Str::uuid();

        $htmlContent = $data['template_builder'];

        $htmlPath = "{$uuid}.html";
        Storage::disk($disk)->put($htmlPath, $htmlContent);
        if (!Storage::disk($disk)->exists($htmlPath)) {
            throw new \Exception("Failed to store HTML file at {$htmlPath}");
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
    }

    public static string | Alignment $formActionsAlignment = Alignment::Between;
}
