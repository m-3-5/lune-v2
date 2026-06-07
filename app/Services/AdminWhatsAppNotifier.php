<?php

namespace App\Services;

use App\Support\AppSettings;
use App\Support\NotificationUrls;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AdminWhatsAppNotifier
{
    public function __construct(
        protected TwilioWhatsAppService $twilio,
    ) {}

    public function send(string $title, string $body, ?string $url = null, bool $force = false): void
    {
        if (! $force && (! AppSettings::adminNotificationsEnabled() || ! AppSettings::adminWhatsAppNotificationsEnabled())) {
            return;
        }

        $phones = AppSettings::adminPhones();
        $message = trim($title."\n\n".$body);

        if ($url !== null && $url !== '' && ! str_contains($message, $url)) {
            $message = NotificationUrls::appendLinkLine($message, $url, 'Apri in admin');
        }

        if ($phones === []) {
            Log::debug('Admin WhatsApp: nessun numero configurato');

            return;
        }

        match (AppSettings::whatsappProvider()) {
            'twilio' => $this->sendViaTwilio($message, $phones),
            'callmebot' => $this->sendViaCallMeBot($message, $phones),
            default => $this->logPreview($message, $phones),
        };
    }

    /**
     * @param  array<int, string>  $phones
     */
    protected function sendViaTwilio(string $message, array $phones): void
    {
        if (! $this->twilio->isReady()) {
            Log::warning('Twilio WhatsApp: configura Account SID, Auth Token e numero From in Canali di invio');

            return;
        }

        foreach ($phones as $phone) {
            $this->twilio->send($phone, $message);
        }
    }

    /**
     * @param  array<int, string>  $phones
     */
    protected function sendViaCallMeBot(string $message, array $phones): void
    {
        $keys = AppSettings::whatsappCallMeBotKeys();

        if ($keys === []) {
            Log::warning('CallMeBot: nessuna apikey in Canali di invio');

            return;
        }

        foreach ($keys as $apiKey) {
            try {
                $response = Http::timeout(10)->get('https://api.callmebot.com/whatsapp.php', [
                    'text' => $message,
                    'apikey' => $apiKey,
                ]);

                if (! $response->successful()) {
                    Log::warning('CallMeBot risposta non OK', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('CallMeBot eccezione: '.$e->getMessage());
            }
        }
    }

    /**
     * @param  array<int, string>  $phones
     */
    protected function logPreview(string $message, array $phones): void
    {
        foreach ($phones as $phone) {
            Log::info('[Jlune WhatsApp admin — anteprima log]', [
                'phone' => $phone,
                'message' => $message,
                'hint' => 'Configura Twilio o CallMeBot in Admin → Canali di invio.',
            ]);
        }
    }
}
