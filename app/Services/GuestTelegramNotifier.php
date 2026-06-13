<?php

namespace App\Services;

use App\Models\Reservation;
use App\Support\AppSettings;
use Illuminate\Support\Facades\Log;

class GuestTelegramNotifier
{
    public function __construct(
        protected TelegramNotifier $telegram,
    ) {}

    public function send(Reservation $reservation, string $title, string $body, ?string $url = null, bool $force = false): bool
    {
        if (! $force && (! AppSettings::guestNotificationsEnabled() || ! AppSettings::guestTelegramNotificationsEnabled())) {
            return false;
        }

        if (! $this->telegram->isConfigured()) {
            Log::debug('Guest Telegram: bot non configurato');

            return false;
        }

        if ($reservation->is_test && ! $force && ! $reservation->notifications_pilot) {
            Log::debug('Guest Telegram: saltata su prenotazione TEST', ['id' => $reservation->id]);

            return false;
        }

        if (blank($reservation->telegram_chat_id)) {
            return false;
        }

        $message = '<b>'.$this->telegram->escape($title)."</b>\n\n".$this->telegram->escape($body);
        if ($url) {
            $message .= "\n\n".$url;
        }

        return $this->telegram->notifyGuest($reservation->telegram_chat_id, $message);
    }
}
