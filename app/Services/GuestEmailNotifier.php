<?php

namespace App\Services;

use App\Mail\GuestAlertMail;
use App\Models\Reservation;
use App\Support\AppSettings;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class GuestEmailNotifier
{
    public function send(Reservation $reservation, string $title, string $body, ?string $url = null, bool $force = false): void
    {
        if (! $force && (! AppSettings::guestNotificationsEnabled() || ! AppSettings::guestEmailNotificationsEnabled())) {
            return;
        }

        if (! AppSettings::mailSmtpReady()) {
            Log::debug('Guest email: SMTP non configurato (Canali di invio)');

            return;
        }

        if ($reservation->is_test && ! $force) {
            Log::debug('Guest email: saltata su prenotazione TEST', ['id' => $reservation->id]);

            return;
        }

        $email = trim((string) $reservation->guest_email);

        if ($email === '') {
            return;
        }

        try {
            Mail::to($email)->send(new GuestAlertMail($title, $body, $url));
        } catch (\Throwable $e) {
            Log::error('Guest email fallita', [
                'to' => $email,
                'reservation_id' => $reservation->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
