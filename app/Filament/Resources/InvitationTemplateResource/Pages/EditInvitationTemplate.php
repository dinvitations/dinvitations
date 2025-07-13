<?php

namespace App\Filament\Resources\InvitationTemplateResource\Pages;

use App\Filament\Resources\InvitationTemplateResource;
use App\Models\File;
use App\Models\Invitation;
use App\Models\InvitationTemplateView;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Alignment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class EditInvitationTemplate extends EditRecord
{
    protected static string $resource = InvitationTemplateResource::class;

    protected static ?string $breadcrumb = 'Template Details';
    protected static ?string $title = 'Templates';

    public ?string $previousUrl = null;

    public function mount($record): void
    {
        parent::mount($record);
        $this->previousUrl = url()->previous();
    }

    protected function getSavedNotification(): ?Notification
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
            Action::make('cancel')
                ->label('Cancel')
                ->color('gray')
                ->requiresConfirmation()
                ->modalIconColor('warning')
                ->modalHeading('Discard Changes')
                ->modalDescription(new HtmlString(
                    'All the data you have entered will be cleared.<br>Are you sure you want to discard?'
                ))
                ->modalSubmitActionLabel('Discard')
                ->action(fn () => redirect($this->previousUrl ?? static::getResource()::getUrl())),
            $this->getSubmitFormAction()
                ->label(fn($record) => $record->published_at ? 'Update' : 'Create'),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $invitation = Invitation::whereHas('order', function ($query) {
                $query->where('status', 'active')
                    ->where('user_id', auth()->user()->id);
            }, '=', 1)
            ->first();

        if (!$invitation) {
            abort(Response::HTTP_NOT_FOUND, 'No active invitation found for this user.');
        }

        $invitation->fill([
            'slug' => $data['slug'],
            'published_at' => $data['published_at'],
            'template_id' => $data['template_id'],
        ])->save();

        return $invitation;
    }

    protected function afterSave(): void
    {
        $this->updateInvitationView($this->record, $this->data);
    }

    private function updateInvitationView(Invitation $record, array $data)
    {
        $viewTypes = InvitationTemplateView::getTypes();
        $disk = 'minio';
        $uuid = Str::uuid();
        $folderPath = "invitation-views/{$uuid}";

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
            $content = data_get($data['template_editor'], $type, '');
            
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
                'fileable_type' => InvitationTemplateView::class,
                'fileable_id' => $record->id,
                'name' => strtoupper($type) . " InvitationTemplateView {$record->id}",
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

            InvitationTemplateView::create([
                'invitation_id' => $record->id,
                'template_id' => $record->template_id,
                'file_id' => $file->id,
                'type' => $type,
            ]);
        }

        Cache::forget("invitation_view_data_{$record->id}");
    }

    public static string | Alignment $formActionsAlignment = Alignment::Between;
}
