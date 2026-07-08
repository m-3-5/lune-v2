<?php

namespace App\Services;

use App\Mail\AdminTeamAlertMail;
use App\Support\AppSettings;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AdminEmailNotifier
{
    public function send(
        string $title,
        string $body,
        ?string $url = null,
        bool $force = false,
        ?string $attachmentPath = null,
        ?string $attachmentName = null,
    ): void {
        if (! $force && (! AppSettings::adminNotificationsEnabled() || ! AppSettings::adminEmailNotificationsEnabled())) {
            return;
        }

        if (! AppSettings::mailSmtpReady()) {
            Log::debug('Admin email: SMTP non configurato (Canali di invio)');

            return;
        }

        $recipients = AppSettings::adminEmails();

        if ($recipients === []) {
            Log::debug('Admin email: nessun destinatario configurato');

            return;
        }

        foreach ($recipients as $email) {
            try {
            Mail::to($email)->send(new AdminTeamAlertMail($title, $body, $url, $attachmentPath, $attachmentName));
            } catch (\Throwable $e) {
                Log::error('Admin email fallita', [
                    'to' => $email,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
