<?php

namespace App\Support;

use App\Models\Guest;
use App\Models\Invitation;
use Carbon\Carbon;

class InvitationHelper
{
    /**
     * Get the WhatsApp message for a guest based on their invitation.
     *
     * @param Invitation $invitation
     * @param Guest $guest
     * @param $invitationGuestId
     * @return string
     */
    public static function getMessage(Invitation $invitation, Guest $guest, string $invitationGuestId): string
    {
        Carbon::setLocale('id');

        return strip_tags(str_replace(
            [
                '[Guest Name]',
                '[Event Name]',
                '[Start Date]',
                '[End Date]',
                '[Start Time]',
                '[End Time]',
                '[Event Location]',
                '[Link Invitation]',
                '[Organizer Name]',
            ],
            [
                $guest->name,
                $invitation->event_name,
                $invitation->date_start->translatedFormat('l, j F Y'),
                $invitation->date_end->translatedFormat('l, j F Y'),
                $invitation->date_start->format('H:i'),
                $invitation->date_end->format('H:i'),
                $invitation->location,
                config('app.url') . '/' . $invitation->slug . "?id=$invitationGuestId",
                $invitation->organizer_name,
            ],
            $invitation->message
        ));
    }

    /**
     * Get the WhatsApp message for a guest based on their invitation.
     *
     * @param Invitation $invitation
     * @param Guest $guest
     * @param $invitationGuestId
     * @return string
     */
    public static function getMessageWaMe(Invitation $invitation, Guest $guest, string $invitationGuestId): string
    {
        Carbon::setLocale('id');

        return strip_tags(str_replace(
            [
                '\n',
                '[Guest Name]',
                '[Event Name]',
                '[Start Date]',
                '[End Date]',
                '[Start Time]',
                '[End Time]',
                '[Event Location]',
                '[Link Invitation]',
                '[Organizer Name]',
            ],
            [
                '%0a',
                $guest->name,
                $invitation->event_name,
                $invitation->date_start->translatedFormat('l, j F Y'),
                $invitation->date_end->translatedFormat('l, j F Y'),
                $invitation->date_start->format('H:i'),
                $invitation->date_end->format('H:i'),
                $invitation->location,
                config('app.url') . '/' . $invitation->slug . "?id=$invitationGuestId",
                $invitation->organizer_name,
            ],
            $invitation->message
        ));
    }

    public static function getInvitation($data, $guest = null): string
    {
        if ($guest) {
            return str_replace('[Guest Name]', $guest, $data);
        }

        return $data;
    }
}

