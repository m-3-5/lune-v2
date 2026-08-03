<?php

namespace App\Services;

use App\Models\GuestNotification;
use App\Models\Reservation;
use App\Support\AppSettings;
use App\Support\NotificationUrls;

/**
 * Tutti i messaggi verso l'ospite (in-app, email, WhatsApp) passano da qui.
 */
class ClientOutboundNotificationService
{
    public const CHANNEL_IN_APP = 'in_app';

    public const CHANNEL_EMAIL = 'email';

    public const CHANNEL_WHATSAPP = 'whatsapp';

    public function __construct(
        protected GuestNotificationService $guestNotifications,
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
}
