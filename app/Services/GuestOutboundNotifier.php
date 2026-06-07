<?php

namespace App\Services;

use App\Models\Reservation;
use App\Support\AppSettings;

class GuestOutboundNotifier
{
    public function __construct(
        protected GuestEmailNotifier $email,
        protected GuestWhatsAppNotifier $whatsapp,
        protected AdminPushNotifier $push,
    ) {}

    public function notify(Reservation $reservation, string $title, string $body, ?string $url = null, bool $force = false): void
    {
        if (! $force && ! AppSettings::guestNotificationsEnabled()) {
            return;
        }

        $this->email->send($reservation, $title, $body, $url, $force);
        $this->whatsapp->send($reservation, $title, $body, $url, $force);

        if ($force || AppSettings::guestPushNotificationsEnabled()) {
            $this->push->notifyGuest($reservation->id, $title, $url, $force);
        }
    }
}
