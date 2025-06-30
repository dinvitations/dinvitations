<?php

namespace App\Support;

use App\Models\Guest;
use App\Models\Invitation;

class InvitationHelper
{
    /**
     * Get the WhatsApp message for a guest based on their invitation.
     *
     * @param Invitation $invitation
     * @param Guest $guest
     * @return string
     */
    public static function getMessage(Invitation $invitation, Guest $guest): string
    {
        return str_replace(
            [
                '{guest_name}',
                '{invitation_date}'
            ],
            [
                $guest->name,
                $invitation->date_end->format('d F Y')
            ],
            $invitation->message
        );
    }

    /**
     * Get the WhatsApp message for a guest based on their invitation.
     *
     * @param Invitation $invitation
     * @param Guest $guest
     * @return string
     */
    public static function getMessageWaMe(Invitation $invitation, Guest $guest): string
    {
        return str_replace(
            [
                '\n',
                '{guest_name}',
                '{invitation_date}'
            ],
            [
                '%0a',
                $guest->name,
                $invitation->date_end->format('d F Y')
            ],
            $invitation->message
        );
    }
}

