<?php

namespace App\Services;

use App\Models\Reservation;
use App\Support\AppSettings;
use App\Support\NotificationUrls;

/**
 * Notifiche «telefono che vibra» per il team (Telegram + Web Push PWA admin + email/WhatsApp).
 */
class TeamAlertNotifier
{
    public function __construct(
        protected TelegramNotifier $telegram,
        protected AdminPushNotifier $push,
        protected AdminOutboundNotifier $outbound,
    ) {}

    public function alert(string $title, string $body, ?string $url = null, string $tag = 'team', ?Reservation $reservation = null): void
    {
        if ($reservation?->is_test && ! str_starts_with($title, '[TEST]')) {
            $title = '[TEST] '.$title;
        }

        $bodyWithLink = $url
            ? NotificationUrls::appendLinkLine($body, $url, 'Apri in admin')
            : $body;

        $safeTitle = $this->telegram->escape($title);
        $safeBody = $this->telegram->escape($bodyWithLink);
        $html = "<b>{$safeTitle}</b>\n{$safeBody}";

        $this->telegram->notifyAdmins($html);
        $this->push->notifyAdmins($bodyWithLink, $tag, $url ?? url('/admin/arrivi'));
        $this->outbound->notify($title, $bodyWithLink, $url, $reservation);
    }

    public function forReservation(Reservation $reservation, string $title, string $body, string $tag = 'team'): void
    {
        $reservation->loadMissing('apartment');
        $code = $reservation->booking_code ?? '#'.$reservation->checkfront_booking_id;
        $apt = $reservation->apartment?->name ?? '—';
        $prefix = "{$code} · {$apt}\n";
        $url = NotificationUrls::adminReservation($reservation);

        $this->alert($title, $prefix.$body, $url, $tag, $reservation);
    }

    public function clientPreview(Reservation $reservation, string $clientTitle, ?string $clientBody = null, ?string $guestActionUrl = null): void
    {
        $reservation->loadMissing('apartment');
        $guest = $reservation->guestDisplayName();
        $guestUrl = $guestActionUrl
            ? NotificationUrls::absolute($guestActionUrl, $reservation)
            : NotificationUrls::guestPortal($reservation);

        $body = "Ospite: {$guest}\n".($clientBody ?: 'Messaggio non inviato al cliente (app in costruzione).');
        $body = NotificationUrls::appendLinkLine($body, $guestUrl, 'Link che avrebbe ricevuto l\'ospite');

        $this->forReservation(
            $reservation,
            '[Anteprima ospite] '.$clientTitle,
            $body,
            'client_preview',
        );
    }
}
