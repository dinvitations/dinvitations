<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Template;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Livewire;

#[Layout('components.layouts.show-templates')]
class ShowTemplates extends Component
{
    public ?Template $record = null;
    public string $html = '';

    public function mount(string $slug)
    {
        $this->record = Template::where('slug', $slug)->firstOrFail();

        $cacheKey = "template_html_{$this->record->id}";
        $this->html = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($slug) {
            $view = $this->record->viewHtml;

            if (!$view || !$view->file || !Storage::disk($view->file->disk)->exists($view->file->path)) {
                abort(Response::HTTP_NOT_FOUND, 'Template HTML view not found');
            }

            $html = Storage::disk($view->file->disk)->get($view->file->path);

            return $html;
        });
    }

    public function render()
    {
        return view('livewire.show-templates', [
            'html' => $this->html
        ]);
    }
}
