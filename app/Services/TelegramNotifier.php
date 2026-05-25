<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramNotifier
{
    public function isConfigured(): bool
    {
        return config('telegram.enabled')
            && filled(config('telegram.bot_token'))
            && config('telegram.notify_chat_ids') !== [];
    }

    public function notifyAdmins(string $message): void
    {
        if (! $this->isConfigured()) {
            Log::debug('Telegram: skip (non configurato)', ['preview' => mb_substr($message, 0, 120)]);

            return;
        }

        $token = config('telegram.bot_token');
        $url = "https://api.telegram.org/bot{$token}/sendMessage";

        foreach (config('telegram.notify_chat_ids') as $chatId) {
            try {
                $response = Http::timeout(15)->post($url, [
                    'chat_id' => $chatId,
                    'text' => $message,
                    'parse_mode' => 'HTML',
                    'disable_web_page_preview' => true,
                ]);

                if (! $response->successful()) {
                    Log::warning('Telegram sendMessage fallito', [
                        'chat_id' => $chatId,
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('Telegram eccezione: '.$e->getMessage(), ['chat_id' => $chatId]);
            }
        }
    }

    public function escape(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
