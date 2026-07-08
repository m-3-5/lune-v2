<?php

namespace App\Services;

use App\Models\Reservation;
use App\Support\AppSettings;

/**
 * Email e WhatsApp verso i contatti admin configurati in Progetto.
 * Vale per prenotazioni reali e prove TEST ([TEST] nel titolo).
 */
class AdminOutboundNotifier
{
    public function __construct(
        protected AdminEmailNotifier $email,
        protected AdminWhatsAppNotifier $whatsapp,
    ) {}

    public function notify(
        string $title,
        string $body,
        ?string $url = null,
        ?Reservation $reservation = null,
        bool $force = false,
        ?string $attachmentPath = null,
        ?string $attachmentName = null,
    ): void {
        if (! $force && ! AppSettings::adminNotificationsEnabled()) {
            return;
        }

        if ($reservation?->is_test && ! str_starts_with($title, '[TEST]')) {
            $title = '[TEST] '.$title;
        }

        $this->email->send($title, $body, $url, $force, $attachmentPath, $attachmentName);
        $this->whatsapp->send($title, $body, $url, $force);
    }
}
