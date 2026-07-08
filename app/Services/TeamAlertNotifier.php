<?php

namespace App\Services;

use App\Models\Reservation;
use App\Support\AppSettings;
use App\Support\NotificationUrls;
use Illuminate\Support\Facades\Log;

/**
 * Notifiche «telefono che vibra» per il team (Telegram + Web Push PWA admin + email/WhatsApp).
 */
class TeamAlertNotifier
{
    public function __construct(
        protected TelegramNotifier $telegram,
        protected AdminPushNotifier $push,
        protected AdminOutboundNotifier $outbound,
        protected TestReservationService $testReservations,
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

        $bodyWithGuestLink = NotificationUrls::appendLinkLine($prefix.$body, NotificationUrls::guestPortal($reservation), 'Link ospite (check-in)');

        $this->alert($title, $bodyWithGuestLink, $url, $tag, $reservation);
    }

    /**
     * Solo per la prenotazione appena sincronizzata da Checkfront (una volta a prenotazione
     * reale). A sito in manutenzione, aggiunge anche: una copia di PROVA collegata (stesso
     * appartamento/date, dati finti, cancellata da sola dopo 5 giorni — vedi
     * jlune:cleanup-auto-test-bookings) e il link di accesso di Serenella (rinnovato ogni volta).
     * A manutenzione disattivata questi extra spariscono da soli: nessuna modifica da fare qui.
     */
    public function forNewBooking(Reservation $reservation, string $title, string $body): void
    {
        $reservation->loadMissing('apartment');
        $code = $reservation->booking_code ?? '#'.$reservation->checkfront_booking_id;
        $apt = $reservation->apartment?->name ?? '—';
        $prefix = "{$code} · {$apt}\n";
        $url = NotificationUrls::adminReservation($reservation);

        $bodyWithLinks = NotificationUrls::appendLinkLine($prefix.$body, NotificationUrls::guestPortal($reservation), 'Link ospite (check-in)');

        if (! $reservation->is_test && AppSettings::siteMaintenanceOn()) {
            $bodyWithLinks = rtrim($bodyWithLinks)."\n\n".
                '⚠️ Sito in manutenzione straordinaria: qui sotto trovi anche un link di PROVA per '.
                "verificare subito come funziona l'app — inserisci pure documenti finti, è una copia ".
                "isolata che NON tocca questa prenotazione vera e si cancella da sola dopo 5 giorni. ".
                'Quando la manutenzione sarà disattivata questi link extra non arriveranno più.';

            if ($testCopy = $this->ensureTestCopy($reservation)) {
                $bodyWithLinks = NotificationUrls::appendLinkLine(
                    $bodyWithLinks,
                    NotificationUrls::guestPortal($testCopy),
                    'Link PROVA per te (dati finti, non tocca la prenotazione vera, sparisce dopo 5 giorni)'
                );
            }

            AppSettings::refreshSerenellaAccessToken();
            $bodyWithLinks = NotificationUrls::appendLinkLine(
                $bodyWithLinks,
                url('/accesso/'.AppSettings::serenellaAccessToken()),
                'Tuo accesso app (si rinnova a ogni prenotazione)'
            );
        }

        $this->alert($title, $bodyWithLinks, $url, 'booking_synced', $reservation);
    }

    protected function ensureTestCopy(Reservation $reservation): ?Reservation
    {
        $existing = Reservation::where('source_reservation_id', $reservation->id)->first();

        if ($existing) {
            return $existing;
        }

        try {
            $copy = $this->testReservations->create([
                'apartment_id' => $reservation->apartment_id,
                'guest_name' => 'Prova',
                'guest_cognome' => 'Serenella',
                'check_in' => $reservation->check_in->format('Y-m-d'),
                'check_out' => $reservation->check_out->format('Y-m-d'),
                'adults' => $reservation->adults ?: 1,
                'children' => $reservation->children ?: 0,
                'is_paid' => true,
                'test_notes' => "Copia prova automatica della prenotazione reale {$reservation->booking_code}.",
            ]);
            $copy->update(['source_reservation_id' => $reservation->id]);

            return $copy;
        } catch (\Throwable $e) {
            Log::error('Copia prova automatica fallita', ['reservation_id' => $reservation->id, 'error' => $e->getMessage()]);

            return null;
        }
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
