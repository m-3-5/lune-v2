<?php

namespace App\Services;

use App\Models\GuestNotification;
use App\Models\Reservation;
use App\Support\AppSettings;
use Illuminate\Support\Facades\Log;

/**
 * Tutti i messaggi verso l'ospite (in-app, email, WhatsApp) passano da qui.
 * In modalità «app in costruzione» non raggiungono il cliente: anteprima agli admin.
 */
class ClientOutboundNotificationService
{
    public const CHANNEL_IN_APP = 'in_app';

    public const CHANNEL_EMAIL = 'email';

    public const CHANNEL_WHATSAPP = 'whatsapp';

    public function __construct(
        protected GuestNotificationService $guestNotifications,
        protected AdminNotificationService $adminNotifications,
        protected TeamAlertNotifier $teamAlerts,
        protected AdminPushNotifier $guestPush,
    ) {}

    public function deliver(
        Reservation $reservation,
        string $type,
        string $title,
        ?string $body = null,
        ?string $actionUrl = null,
        int $dedupeHours = 24
    ): ?GuestNotification {
        if (AppSettings::underConstruction()) {
            $this->previewToAdmins($reservation, $type, $title, $body, $actionUrl);

            return null;
        }

        return $this->guestNotifications->createInApp(
            $reservation,
            $type,
            $title,
            $body,
            $actionUrl,
            $dedupeHours
        );
    }

    protected function previewToAdmins(
        Reservation $reservation,
        string $type,
        string $title,
        ?string $body,
        ?string $actionUrl
    ): void {
        $reservation->loadMissing('apartment');
        $guest = $reservation->guestDisplayName();
        $code = $reservation->booking_code ?? '#'.$reservation->checkfront_booking_id;
        $apt = $reservation->apartment?->name ?? '—';

        $emails = AppSettings::adminEmails();
        $phones = AppSettings::adminPhones();

        $previewBody = implode("\n", array_filter([
            'Modalità «App in costruzione» attiva: questa notifica NON è stata inviata all\'ospite.',
            '',
            'Prenotazione: '.$code.' · '.$apt,
            'Ospite: '.$guest,
            'Tipo: '.$type,
            '',
            '--- Testo che avrebbe ricevuto il cliente ---',
            $title,
            $body,
            $actionUrl ? 'Link: '.$actionUrl : null,
            '',
            '--- Canali bloccati ---',
            '• Campanella area ospite (in-app)',
            '• Email al cliente (non inviata)',
            '• WhatsApp al cliente (non inviata)',
            '',
            '--- Quando attiverete i canali, arriverebbe agli admin ---',
            'Email admin: '.($emails !== [] ? implode(', ', $emails) : '(nessuna configurata)'),
            'WhatsApp admin: '.($phones !== [] ? implode(', ', $phones) : '(nessun numero configurato)'),
        ]));

        $adminNotification = $this->adminNotifications->clientNotificationPreview(
            $reservation,
            $title,
            $previewBody,
        );

        if ($adminNotification->wasRecentlyCreated) {
            $this->teamAlerts->clientPreview(
                $reservation,
                $title,
                $body,
            );
        }

        Log::info('[Jlune anteprima notifica cliente]', [
            'booking' => $code,
            'type' => $type,
            'title' => $title,
            'admin_emails' => $emails,
            'admin_phones' => $phones,
        ]);
    }
}
