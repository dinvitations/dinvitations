<?php

namespace App\Support;

use App\Models\Guest;
use App\Models\Invitation;
use Carbon\Carbon;
use Illuminate\Support\Str;

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

        $message = strip_tags(str_replace(
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

        return self::formatForWhatsApp($message);
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

        $message = strip_tags(str_replace(
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

        return self::formatForWhatsApp($message);
    }

    /**
     * Convert Markdown syntax to WhatsApp formatting.
     */
    protected static function formatForWhatsApp(string $text): string
    {
        // Convert bold: **text** -> *text*
        $text = preg_replace('/\*\*(.*?)\*\*/s', '*$1*', $text);

        // Convert italic: *text* (if not part of **bold**) -> _text_
        $text = preg_replace('/(?<!\*)\*(?!\*)(.*?)\*(?<!\*)/s', '_$1_', $text);

        // Convert strikethrough: ~~text~~ -> ~text~
        $text = preg_replace('/~~(.*?)~~/s', '~$1~', $text);

        return $text;
    }

    public static function getHtml($html, $replacements = []): string
    {
        libxml_use_internal_errors(true);

        $dom = new \DOMDocument();
        $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        $xpath = new \DOMXPath($dom);

        foreach ($replacements as $key => $value) {
            $slugKey = \Illuminate\Support\Str::slug($key, '_');

            $selectors = [
                "//*[@title='[{$key}]']",
                "//*[@alt='[{$key}]']",
                "//*[@name='[{$key}]']",
                "//*[@id='{$slugKey}']",
            ];

            foreach ($selectors as $selector) {
                $nodes = $xpath->query($selector);

                foreach ($nodes as $node) {
                    if ($node->nodeName === 'img' && $node->hasAttribute('alt')) {
                        if (!empty($value)) {
                            $node->setAttribute('src', $value);
                        }
                        continue;
                    }

                    if ($node->nodeName === 'a' && $node->hasAttribute('href')) {
                        if (!empty($value)) {
                            $node->setAttribute('href', $value);
                        }
                        continue;
                    }

                    if (in_array($node->nodeName, ['input', 'textarea', 'select']) && $node->hasAttribute('placeholder')) {
                        if (!empty($value)) {
                            $node->setAttribute('placeholder', $value);
                        }
                        continue;
                    }

                    if (!empty($value)) {
                        while ($node->hasChildNodes()) {
                            $node->removeChild($node->firstChild);
                        }

                        $tmpDoc = new \DOMDocument();
                        libxml_use_internal_errors(true);
                        $tmpDoc->loadHTML(mb_convert_encoding('<body>' . $value . '</body>', 'HTML-ENTITIES', 'UTF-8'));
                        libxml_clear_errors();

                        $body = $tmpDoc->getElementsByTagName('body')->item(0);
                        if ($body) {
                            foreach ($body->childNodes as $child) {
                                $imported = $dom->importNode($child, true);
                                $node->appendChild($imported);
                            }
                        }
                    }
                }
            }
        }

        return $dom->saveHTML();
    }
}
