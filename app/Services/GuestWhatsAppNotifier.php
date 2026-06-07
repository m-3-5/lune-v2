<?php

namespace App\Services;

use App\Models\Reservation;
use App\Support\AppSettings;
use Illuminate\Support\Facades\Log;

class GuestWhatsAppNotifier
{
    public function __construct(
        protected TwilioWhatsAppService $twilio,
    ) {}

    public function send(Reservation $reservation, string $title, string $body, ?string $url = null, bool $force = false): void
    {
        if (! $force && (! AppSettings::guestNotificationsEnabled() || ! AppSettings::guestWhatsAppNotificationsEnabled())) {
            return;
        }

        if ($reservation->is_test && ! $force) {
            Log::debug('Guest WhatsApp: saltata su prenotazione TEST', ['id' => $reservation->id]);

            return;
        }

        $phone = $this->twilio->normalizePhone((string) $reservation->guest_phone);

        if ($phone === '') {
            return;
        }

        $message = trim($title."\n\n".$body.($url ? "\n\n".$url : ''));

        if (AppSettings::whatsappProvider() === 'twilio') {
            if (! $this->twilio->isReady()) {
                Log::warning('Twilio WhatsApp ospite: credenziali incomplete');

                return;
            }

            $this->twilio->send($phone, $message);

            return;
        }

        Log::info('[Jlune WhatsApp ospite — anteprima log]', [
            'phone' => $phone,
            'reservation_id' => $reservation->id,
            'message' => $message,
            'hint' => 'Attiva provider Twilio in Canali di invio per inviare agli ospiti.',
        ]);
    }
}
