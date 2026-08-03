<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramNotifier
{
    public function isConfigured(): bool
    {
        return config('telegram.enabled')
            && filled(config('telegram.bot_token'));
    }

    public function isAdminConfigured(): bool
    {
        return $this->isConfigured()
            && config('telegram.notify_chat_ids') !== [];
    }

    public function botUsername(): string
    {
        return ltrim((string) config('telegram.bot_username', ''), '@');
    }

    public function deepLink(string $startPayload): string
    {
        return 'https://t.me/'.$this->botUsername().'?start='.urlencode($startPayload);
    }

    public function notifyAdmins(string $message): void
    {
        if (! $this->isAdminConfigured()) {
            Log::debug('Telegram admin: skip (non configurato)', ['preview' => mb_substr($message, 0, 120)]);

            return;
        }

        foreach (config('telegram.notify_chat_ids') as $chatId) {
            $this->sendToChat((string) $chatId, $message);
        }
    }

    public function notifyGuest(?string $chatId, string $message): bool
    {
        if (! $this->isConfigured() || blank($chatId)) {
            return false;
        }

        return $this->sendToChat($chatId, $message);
    }

    public function sendToChat(string $chatId, string $message): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        $token = config('telegram.bot_token');
        $url = "https://api.telegram.org/bot{$token}/sendMessage";

        try {
            $response = Http::timeout(15)->post($url, [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => false,
            ]);

            if (! $response->successful()) {
                Log::warning('Telegram sendMessage fallito', [
                    'chat_id' => $chatId,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('Telegram eccezione: '.$e->getMessage(), ['chat_id' => $chatId]);

            return false;
        }
    }

    public function escape(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
