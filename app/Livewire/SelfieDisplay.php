<?php

namespace App\Livewire;

use App\Models\Feature;
use App\Models\Invitation;
use App\Models\InvitationGuest;
use Illuminate\Http\Response;
use Livewire\Component;

class SelfieDisplay extends Component
{
    protected $invitation;
     public $guestId = null;

    public function mount($guestId = null)
    {
        $this->guestId = $guestId;

        $userId = auth()?->user()?->id;

        $invitation = Invitation::whereNotNull('published_at')
            ->whereHas('order', function ($query) use ($userId) {
                $query->where('status', 'active')
                    ->where('user_id', $userId)
                    ->whereHas('package.features', function ($featureQuery) {
                        $featureQuery->where('name', Feature::FEATURES['selfie']);
                    });
            })->first();

        if (!$invitation) {
            abort(Response::HTTP_NOT_FOUND, 'No active invitation found for this user.');
        }

        $this->invitation = $invitation;
    }

    public function render()
    {
        $invitation = $this->invitation;

        $guest = null;
        if ($this->guestId) {
            $guest = InvitationGuest::find($this->guestId);
            if (!$guest || $guest->invitation_id !== $invitation->id) {
                abort(Response::HTTP_NOT_FOUND, 'Guest not found for this invitation.');
            }
        } else {
            $guest = InvitationGuest::where('invitation_id', $invitation->id)
                ->whereNotNull('selfie_at')
                ->orderByDesc('selfie_at')
                ->first();
        }

        $eventName = $invitation->event_name;
        $guestName = $guest?->guest?->name ?? 'All Our Dear Guests';
        $eventDate = $this->formatEventDate($invitation);
        $address = $invitation->location;
        $backgroundUrl = $invitation->template?->display_background_landscape_url ?? null;

        return view('livewire.selfie-display', [
            'event_name' => $eventName,
            'guest_name' => $guestName,
            'event_date' => $eventDate,
            'address' => $address,
            'background_url' => $backgroundUrl,
        ]);
    }

    private function formatEventDate($invitation)
    {
        if (!$invitation->date_start || !$invitation->date_end) {
            return null;
        }

        $startDate = $invitation->date_start;
        $endDate = $invitation->date_end ?? $startDate;

        $startTime = \Carbon\Carbon::parse($invitation->date_start)->format('h:i A');
        $endTime = \Carbon\Carbon::parse($invitation->date_end)->format('h:i A');

        if ($startDate->isSameDay($endDate)) {
            return $startDate->format('M j') . ", $startTime to $endTime";
        }

        if ($startDate->format('Y-m') === $endDate->format('Y-m')) {
            return $startDate->format('M j') . ' to ' . $endDate->format('j') . ", $startTime to $endTime";
        }

        return $startDate->format('M j') . ' to ' . $endDate->format('M j') . ", $startTime to $endTime";
    }

}
