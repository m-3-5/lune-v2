<?php

namespace App\Services;

use App\Models\GuestNotification;
use App\Models\Reservation;
use App\Support\AppSettings;
use App\Support\NotificationUrls;
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
        protected GuestOutboundNotifier $guestOutbound,
    ) {}

    public function deliver(
        Reservation $reservation,
        string $type,
        string $title,
        ?string $body = null,
        ?string $actionUrl = null,
        int $dedupeHours = 24
    ): ?GuestNotification {
        if (AppSettings::underConstruction() && ! $reservation->allowsGuestNotificationsDelivery()) {
            $this->previewToAdmins($reservation, $type, $title, $body, $actionUrl);

            return null;
        }

        if (! AppSettings::guestNotificationsEnabled()) {
            return null;
        }

        $actionUrl = NotificationUrls::absolute($actionUrl, $reservation);
        $bodyWithLink = NotificationUrls::appendLinkLine($body ?? '', $actionUrl);

        $notification = $this->guestNotifications->createInApp(
            $reservation,
            $type,
            $title,
            $bodyWithLink,
            $actionUrl,
            $dedupeHours
        );

        if ($notification !== null) {
            $this->guestOutbound->notify(
                $reservation,
                $title,
                $bodyWithLink,
                $actionUrl,
            );
        }

        return $notification;
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

        $guestUrl = NotificationUrls::absolute($actionUrl, $reservation);

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
            'Link ospite: '.$guestUrl,
            '',
            '--- Canali ospite (stato attuale) ---',
            '• In-app: '.(AppSettings::guestNotificationsEnabled() ? 'attivo se disattivi «app in costruzione»' : 'DISATTIVO'),
            '• Email ospite: '.(AppSettings::guestEmailNotificationsEnabled() ? 'attivo' : 'disattivo'),
            '• WhatsApp ospite: '.(AppSettings::guestWhatsAppNotificationsEnabled() ? 'attivo' : 'disattivo'),
            '• Push ospite: '.(AppSettings::guestPushNotificationsEnabled() ? 'attivo' : 'disattivo'),
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
                $guestUrl,
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
