<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Template;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

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

            $this->updateTailwindSafelist(
                $this->extractTailwindClasses($html)
            );

            return $html;
        });
    }

    private function extractTailwindClasses(string $html): array
    {
        preg_match_all('/class=["\']([^"\']+)["\']/', $html, $matches);

        return collect($matches[1])
            ->flatMap(fn($classes) => preg_split('/\s+/', trim($classes)))
            ->map(fn($class) => trim($class))
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }

    private function updateTailwindSafelist(array $classes)
    {
        $filePath = base_path('tailwind-safelist.json');

        $existing = file_exists($filePath)
            ? json_decode(file_get_contents($filePath), true) ?? []
            : [];

        $merged = collect($existing)
            ->merge($classes)
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        file_put_contents($filePath, json_encode($merged, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    public function render()
    {
        return view('livewire.show-templates', [
            'html' => $this->html
        ]);
    }
}
