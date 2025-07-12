<?php

namespace App\Livewire;

use App\Models\Invitation;
use App\Models\InvitationGuest;
use App\Models\InvitationTemplateView;
use App\Support\InvitationHelper;
use Livewire\Component;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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

        $guestId = request()->query('id');
        if ($guestId && Str::isUuid($guestId)) {
            $guest = InvitationGuest::find($guestId);

            if ($guest) {
                $this->data['guest'] = [
                    'id' => $guest->id,
                    'guest_name' => $guest->guest?->name,
                    'qrcode' => Storage::disk('minio')->exists($guest->qr_code_path)
                        ? Storage::disk('minio')->temporaryUrl(
                            $guest->qr_code_path,
                            now()->addMinutes(10)
                        ) : null,
                    'rsvp' => $guest->rsvp
                ];
            }
        }
    }

    public function render()
    {
        $locationUrl = $this->record->location_latlng
            ? 'https://maps.google.com/?q=' . $this->record->location_latlng
            : '#';

        $formattedEventDate = '';
        if (!empty($this->record->date_start)) {
            $start = \Carbon\Carbon::parse($this->record->date_start);
            $formattedEventDate = $start->translatedFormat('l, d F Y');

            if (!empty($this->record->date_end)) {
                $end = \Carbon\Carbon::parse($this->record->date_end);
                $formattedEventDate .= '<br>' . $start->format('H.i') . ' s/d ' . $end->format('H.i');
            }
        }

        $guest = $this->data['guest'] ?? [];

        $replacements = [
            'Guest Name' => $guest['guest_name'] ?? 'Guest',
            'Event Date' => $formattedEventDate,
            'Loc Maps' => $locationUrl,
            'RSVP' => $guest['rsvp'] ?? null,
            'QR Code' => $guest['qrcode'] ?? null,
        ];

        $html = InvitationHelper::getHtml($this->data['html'], $replacements);

        return view('livewire.show-invitation', [
            'html' => $html,
            'css' => $this->data['css'],
            'js' => $this->data['js'],
            'guest' => $this->data['guest'] ?? []
        ]);
    }

    public function rsvp(string $guestId)
    {
        if (!Str::isUuid($guestId)) {
            return;
        }
        
        $guest = InvitationGuest::find($guestId);

        if ($guest) {
            $guest->rsvp = true;
            $guest->save();

            if (isset($this->data['guest']) && $this->data['guest']['id'] == $guestId) {
                $this->data['guest']['rsvp'] = false;
            }
        }
    }
}
