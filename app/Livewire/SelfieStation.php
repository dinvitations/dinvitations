<?php

namespace App\Livewire;

use App\Models\Invitation;
use App\Models\InvitationGuest;
use Illuminate\Http\Response;
use Livewire\Component;

class SelfieStation extends Component
{
    protected $guestId;
    protected $userId;

    public function mount($guestId = null)
    {
        $userId = auth()?->user()?->id;

        $invitation = Invitation::whereNotNull('published_at')
                ->whereHas('order', function ($q) use ($userId) {
                    $q->where('status', 'active')->where('user_id', $userId);
                })->first();

        if (!$invitation) {
            abort(Response::HTTP_NOT_FOUND, 'No active invitation found for this user.');
        }
        
        $invitationGuest = InvitationGuest::where('invitation_id', $invitation->id)
            ->orderByDesc('attended_at')
            ->first();

        $this->guestId = $guestId ?? $invitationGuest->id;
        $this->userId = $userId;
    }

    public function render()
    {
        return view('livewire.selfie-station', [
            'userId' => $this->userId,
            'guestId' => $this->guestId,
        ]);
    }
}
