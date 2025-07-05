<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Template;
use App\Models\TemplateView;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.show-template')]
class ShowTemplate extends Component
{
    public ?Model $record = null;

    public array $data = [
        'html' => '',
        'css' => '',
        'js' => '',
    ];

    public function mount(string $slug)
    {
        $this->record = Template::where('slug', $slug)->firstOrFail();

        $cacheKey = "template_view_data_{$this->record->id}";

        $this->data = Cache::remember($cacheKey, now()->addMinutes(10), function () {
            $types = TemplateView::getTypes();

            $views = $this->record->views()
                ->with('file')
                ->whereIn('type', array_keys($types))
                ->get()
                ->keyBy('type');

            $getContent = function ($view): string {
                if (!$view || !$view->file) {
                    return '';
                }

                $disk = $view->file->disk;
                $path = $view->file->path;

                return Storage::disk($disk)->exists($path)
                    ? Storage::disk($disk)->get($path)
                    : '';
            };

            return [
                'html' => $getContent($views->get('html')),
                'css' => $getContent($views->get('css')),
                'js' => $getContent($views->get('js')),
                'grapesjs' => [
                    'projectData' => $getContent($views->get('grapesjs.projectData')),
                    'components' => $getContent($views->get('grapesjs.components')),
                    'style' => $getContent($views->get('grapesjs.style')),
                ],
            ];
        });
    }

    public function render()
    {
        return view('livewire.show-template', [
            'html' => $this->data['html'],
            'css' => $this->data['css'],
            'js' => $this->data['js'],
        ]);
    }
}
