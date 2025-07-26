<?php

namespace App\Support;

use App\Models\Guest;
use App\Models\Invitation;
use App\Models\InvitationGuest;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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
        if (trim($html) === '') {
            return '';
        }

        libxml_use_internal_errors(true);

        $dom = new \DOMDocument();
        $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        $xpath = new \DOMXPath($dom);

        if (isset($replacements['RSVP']) && isset($replacements['RSVP']['id'])) {
            $guestId = $replacements['RSVP']['id'];
            $rsvpValue = $replacements['RSVP']['rsvp'] ?? null;

            $containers = $xpath->query("//*[@data-rsvp-block-id]");

            foreach ($containers as $container) {
                $container->setAttribute('data-guest-id', $guestId);

                if ($rsvpValue === true || $rsvpValue === false) {
                    $container->setAttribute('data-rsvp', $rsvpValue ? 'true' : 'false');
                } else {
                    $container->setAttribute('data-rsvp', 'null');
                }
            }
        }


        foreach ($replacements as $key => $value) {
            if ($key === 'RSVP') continue;

            $slugKey = \Illuminate\Support\Str::slug($key, '_');

            $selectors = [
                "//*[@title='[{$key}]']",
                "//*[@alt='[{$key}]']",
                "//*[@name='[{$key}]']",
                "//*[@id='{$slugKey}']",
            ];

            $classNodes = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' {$slugKey} ')]");
            if ($classNodes->length > 0) {
                foreach ($classNodes as $node) {
                    self::replaceNodeContent($dom, $node, $value);
                }
            }

            foreach ($selectors as $selector) {
                $nodes = $xpath->query($selector);
                foreach ($nodes as $node) {
                    self::replaceNodeContent($dom, $node, $value);
                }
            }
        }

        return $dom->saveHTML();
    }

    private static function replaceNodeContent(\DOMDocument $dom, \DOMElement $node, $value)
    {
        if ($node->nodeName === 'img' && $node->hasAttribute('alt')) {
            if (!empty($value)) {
                $node->setAttribute('src', $value);
            }
            return;
        }

        if ($node->nodeName === 'a' && $node->hasAttribute('href')) {
            if (!empty($value)) {
                $node->setAttribute('href', $value);
            }
            return;
        }

        if (in_array($node->nodeName, ['input', 'textarea', 'select']) && $node->hasAttribute('placeholder')) {
            if (!empty($value)) {
                $node->setAttribute('placeholder', $value);
            }
            return;
        }

        if (!empty($value)) {
            while ($node->hasChildNodes()) {
                $node->removeChild($node->firstChild);
            }

            $tmpDoc = new \DOMDocument();
            $tmpDoc->loadHTML(mb_convert_encoding('<body>' . $value . '</body>', 'HTML-ENTITIES', 'UTF-8'));

            $body = $tmpDoc->getElementsByTagName('body')->item(0);
            if ($body) {
                foreach ($body->childNodes as $child) {
                    $imported = $dom->importNode($child, true);
                    $node->appendChild($imported);
                }
            }
        }
    }

    /**
     * Generate souvenir QR and save it to storage
     *
     * @throws \Throwable
     */
    public static function generateSouvenirQr(InvitationGuest $invitationGuest): ?string
    {
        $disk = 'minio';
        $path = implode('', [
            'qr-codes/',
            'souvenir_',
            "{$invitationGuest->invitation?->slug}_",
            "{$invitationGuest->guest?->id}.png"
        ]);

        try {
            if (Storage::disk($disk)->exists($path)) {
                if ($invitationGuest->souvenir_qr_path !== $path) {
                    $invitationGuest->updateQuietly([
                        'souvenir_qr_path' => $path,
                    ]);
                }
                return $path;
            }

            $qrContent = json_encode([
                'id' => $invitationGuest->id,
                'type' => 'souvenir',
            ]);

            $qrCodePng = QrCode::format('png')->size(250)->generate($qrContent);
            Storage::disk($disk)->put($path, $qrCodePng);

            if (!Storage::disk($disk)->exists($path)) {
                throw new \Exception("Failed to store QR file at $path", Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            DB::transaction(function () use ($invitationGuest, $path) {
                $invitationGuest->update([
                    'souvenir_qr_path' => $path,
                ]);
            });

            return $path;
        } catch (\Throwable $th) {
            Log::error("Failed to store QR file at $path", [
                'exception' => $th,
            ]);
            throw $th;
        }
    }
}
