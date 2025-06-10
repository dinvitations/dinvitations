<?php

namespace App\Exports;

use App\Models\InvitationGuest;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class GuestBookExport implements FromCollection, WithHeadings
{
    protected $invitationId;

    public function __construct(string $invitationId)
    {
        $this->invitationId = $invitationId;
    }

    public function collection()
    {
        return InvitationGuest::with('guest')
            ->where('invitation_id', $this->invitationId)
            ->get()
            ->map(function ($entry) {
                return [
                    'Name'        => $entry->guest->name ?? '',
                    'Type'        => $entry->type ?? '',
                    'RSVP'        => $entry->rsvp ?? '',
                    'Attended At' => $entry->attended_at ?? '',
                    'Souvenir At' => $entry->souvenir_at ?? '',
                    'Selfie At'   => $entry->selfie_at ?? '',
                ];
            });
    }

    public function headings(): array
    {
        return ['Name', 'Type', 'RSVP', 'Attended At', 'Souvenir At', 'Selfie At'];
    }
}

