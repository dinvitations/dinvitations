<?php

namespace App\Livewire;

use App\Models\Guest;
use App\Models\Invitation;
use App\Models\InvitationTemplateView;
use Livewire\Component;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

#[Layout('components.layouts.show-invitation')]
class ShowInvitation extends Component
{
    public ?Model $record = null;

    public array $data = [
        'html' => '',
        'css' => '',
        'js' => '',
    ];

    public function mount(string $slug)
    {
        $this->record = Invitation::where('slug', $slug)->firstOrFail();

        $cacheKey = "invitation_view_data_{$this->record->id}";

        $this->data = Cache::remember($cacheKey, now()->addMinutes(10), function () {
            $types = InvitationTemplateView::getTypes();

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

        $guestId = request()->query('guest_id');
        if ($guestId) {
            $guest = Guest::findOrFail($guestId);

            $qrPayload = [
                'id' => $guest->id,
                'type' => 'souvenir',
            ];
            $qrCode = base64_encode(
                QrCode::format('png')->size(160)->generate(json_encode($qrPayload))
            );
            $this->data['qrcode'] = $qrCode;
        }
        
    }

    public function render()
    {
        return view('livewire.show-invitation', [
            'html' => $this->data['html'],
            'css' => $this->data['css'],
            'js' => $this->data['js'],
            'qrcode' => $this->data['qrcode'] ?? null,
        ]);
    }
}
