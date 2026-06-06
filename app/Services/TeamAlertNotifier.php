<?php

namespace App\Services;

use App\Models\Reservation;

/**
 * Notifiche «telefono che vibra» per il team (Telegram + Web Push PWA admin).
 */
class TeamAlertNotifier
{
    public function __construct(
        protected TelegramNotifier $telegram,
        protected AdminPushNotifier $push,
    ) {}

    public function alert(string $title, string $body, ?string $url = null, string $tag = 'team'): void
    {
        $safeTitle = $this->telegram->escape($title);
        $safeBody = $this->telegram->escape($body);
        $html = "<b>{$safeTitle}</b>\n{$safeBody}";

        $this->telegram->notifyAdmins($html);
        $this->push->notifyAdmins($title."\n".$body, $tag, $url ?? url('/admin/arrivi'));
    }

    public function forReservation(Reservation $reservation, string $title, string $body, string $tag = 'team'): void
    {
        $reservation->loadMissing('apartment');
        if ($reservation->is_test) {
            $title = '[TEST] '.$title;
        }
        $code = $reservation->booking_code ?? '#'.$reservation->checkfront_booking_id;
        $apt = $reservation->apartment?->name ?? '—';
        $prefix = "{$code} · {$apt}\n";
        $url = url('/admin/arrivi/'.$reservation->id);

        $this->alert($title, $prefix.$body, $url, $tag);
    }

    public function clientPreview(Reservation $reservation, string $clientTitle, ?string $clientBody = null): void
    {
        $reservation->loadMissing('apartment');
        $guest = $reservation->guestDisplayName();

        $this->forReservation(
            $reservation,
            '[Anteprima ospite] '.$clientTitle,
            "Ospite: {$guest}\n".($clientBody ?: 'Messaggio non inviato al cliente (app in costruzione).'),
            'client_preview',
        );
    }
}
