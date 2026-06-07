<?php

namespace App\Services;

use App\Support\AppSettings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TwilioWhatsAppService
{
    public function isReady(): bool
    {
        return AppSettings::twilioAccountSid() !== ''
            && AppSettings::twilioAuthToken() !== null
            && AppSettings::twilioWhatsAppFrom() !== '';
    }

    /**
     * @return array{ok: bool, error?: string, sid?: string}
     */
    public function send(string $toPhone, string $message): array
    {
        if (! $this->isReady()) {
            Log::warning('Twilio WhatsApp: credenziali incomplete (Canali di invio)');

            return ['ok' => false, 'error' => 'Credenziali Twilio incomplete'];
        }

        $accountSid = AppSettings::twilioAccountSid();
        $to = $this->toWhatsAppAddress($toPhone);
        $from = $this->toWhatsAppAddress(AppSettings::twilioWhatsAppFrom());

        try {
            $response = Http::withBasicAuth($accountSid, AppSettings::twilioAuthToken())
                ->timeout(15)
                ->asForm()
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json", [
                    'From' => $from,
                    'To' => $to,
                    'Body' => $message,
                ]);

            if ($response->successful()) {
                $sid = $response->json('sid');

                Log::info('Twilio WhatsApp inviato', [
                    'to' => $to,
                    'sid' => $sid,
                ]);

                return ['ok' => true, 'sid' => is_string($sid) ? $sid : null];
            }

            $error = $response->json('message') ?? $response->body();

            Log::warning('Twilio WhatsApp errore', [
                'to' => $to,
                'status' => $response->status(),
                'error' => $error,
            ]);

            return ['ok' => false, 'error' => is_string($error) ? $error : 'Errore Twilio'];
        } catch (\Throwable $e) {
            Log::error('Twilio WhatsApp eccezione: '.$e->getMessage());

            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    public function toWhatsAppAddress(string $phone): string
    {
        $phone = trim($phone);

        if (str_starts_with(strtolower($phone), 'whatsapp:')) {
            $normalized = $this->normalizePhone(substr($phone, 9));

            return 'whatsapp:'.$normalized;
        }

        $normalized = $this->normalizePhone($phone);

        return 'whatsapp:'.$normalized;
    }

    public function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        if (strlen($digits) === 10 && str_starts_with($digits, '3')) {
            $digits = '39'.$digits;
        }

        return '+'.$digits;
    }
}
